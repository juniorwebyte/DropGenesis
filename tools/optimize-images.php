<?php

/*
 * ==========================================================================
 * DropGenesis — Otimizador de imagens (CLI)
 * ==========================================================================
 *
 * Copyright (c) Webyte Desenvolvimentos. All rights reserved.
 * https://webytehub.com/licencas
 *
 * --------------------------------------------------------------------------
 *
 * Gera um irmão `.webp` para cada imagem do instalador, ao lado do original.
 *
 * As 13 imagens do fundo em scroll somam 28,5 MB em PNG. São capturas de tela
 * de 1905x911 — conteúdo que o PNG codifica muito mal e o WebP muito bem. Nada
 * é apagado nem redimensionado: o PNG continua no lugar como alternativa, e
 * `\DropGenesis\Core\Assets` só serve o `.webp` quando o arquivo existe E o
 * navegador o anuncia no cabeçalho Accept. A imagem exibida é a mesma.
 *
 * Uso:
 *   php DropGenesis/tools/optimize-images.php
 *   php DropGenesis/tools/optimize-images.php --quality=88 --force
 *
 * Opções:
 *   --quality=N  Qualidade WebP (1–100). Padrão: 82.
 *   --force      Regera mesmo que o .webp já esteja atualizado.
 *   --dry-run    Apenas relata; não escreve nada.
 * ==========================================================================
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este utilitário só roda pela linha de comando.\n");
}

if (!function_exists('imagewebp')) {
    fwrite(STDERR, "A extensão GD com suporte a WebP não está disponível neste PHP.\n");
    fwrite(STDERR, "No XAMPP/Windows: habilite `extension=gd` no php.ini, ou rode com `php -d extension=gd`.\n");
    exit(1);
}

$options = getopt('', ['quality::', 'force', 'dry-run']);
$quality = (int) ($options['quality'] ?? 82);
$force   = array_key_exists('force', $options);
$dryRun  = array_key_exists('dry-run', $options);

if ($quality < 1 || $quality > 100) {
    fwrite(STDERR, "Qualidade deve estar entre 1 e 100.\n");
    exit(1);
}

$installerDir = dirname(__DIR__);
$targets = [
    $installerDir . '/src/Images/scroll',
    $installerDir . '/src/Images/componentes',
];

$totalBefore = 0;
$totalAfter  = 0;
$converted   = 0;
$skipped     = 0;

foreach ($targets as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    echo "\n" . str_replace($installerDir, '.', $dir) . "\n";
    echo str_repeat('-', 64) . "\n";

    foreach (scan_images($dir) as $source) {
        $webp = preg_replace('/\.[^.]+$/', '.webp', $source);
        if ($webp === null || $webp === $source) {
            continue;
        }

        $sourceSize = (int) filesize($source);

        // Regera quando o original é mais novo que o .webp — assim trocar uma
        // imagem de fundo não deixa a versão otimizada desatualizada em
        // silêncio.
        $upToDate = is_file($webp) && filemtime($webp) >= filemtime($source);
        if ($upToDate && !$force) {
            $totalBefore += $sourceSize;
            $totalAfter  += (int) filesize($webp);
            $skipped++;
            printf("  %-18s já otimizado\n", basename($source));
            continue;
        }

        $image = load_image($source);
        if ($image === null) {
            printf("  %-18s ignorado (formato não suportado)\n", basename($source));
            continue;
        }

        if ($dryRun) {
            printf("  %-18s %8.2f MB → (dry-run)\n", basename($source), $sourceSize / 1048576);
            continue;
        }

        // Preserva transparência (os emojis e a logo são PNG com alfa).
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        if (!imagewebp($image, $webp, $quality)) {
            fwrite(STDERR, "  Falha ao gravar " . basename($webp) . "\n");
            continue;
        }

        $webpSize = (int) filesize($webp);

        // Um WebP maior que o original não tem serventia — só ocuparia disco e
        // seria servido preferencialmente.
        if ($webpSize >= $sourceSize) {
            unlink($webp);
            printf("  %-18s mantido em PNG (WebP ficaria maior)\n", basename($source));
            $totalBefore += $sourceSize;
            $totalAfter  += $sourceSize;
            continue;
        }

        $totalBefore += $sourceSize;
        $totalAfter  += $webpSize;
        $converted++;

        printf(
            "  %-18s %8.2f MB → %7.2f MB  (-%d%%)\n",
            basename($source),
            $sourceSize / 1048576,
            $webpSize / 1048576,
            (int) round((1 - $webpSize / $sourceSize) * 100)
        );
    }
}

echo "\n" . str_repeat('=', 64) . "\n";
printf("Convertidas: %d   |   Já otimizadas: %d\n", $converted, $skipped);
printf(
    "Peso total:  %.2f MB → %.2f MB  (-%d%%)\n",
    $totalBefore / 1048576,
    $totalAfter / 1048576,
    $totalBefore > 0 ? (int) round((1 - $totalAfter / $totalBefore) * 100) : 0
);
echo "Os originais foram preservados como alternativa para navegadores sem WebP.\n";

/**
 * @return array<int,string>
 */
function scan_images(string $dir): array
{
    $files = [];
    foreach ((array) scandir($dir) as $entry) {
        if (!is_string($entry) || $entry === '.' || $entry === '..') {
            continue;
        }
        $path = $dir . '/' . $entry;
        if (!is_file($path)) {
            continue;
        }
        if (preg_match('/\.(png|jpe?g)$/i', $entry) !== 1) {
            continue;
        }
        $files[] = $path;
    }

    natcasesort($files);

    return array_values($files);
}

/**
 * Abre a imagem pelo tipo REAL do arquivo, não pela extensão.
 *
 * `src/Images/componentes/ideia.png` e `relogio.png` são JPEG com extensão
 * .png. O navegador aceita porque olha o conteúdo, mas qualquer ferramenta que
 * confie no nome falha neles — e falhava: eram os dois únicos arquivos que
 * ficavam de fora da otimização, somando 1,6 MB não convertidos.
 */
function load_image(string $path): ?\GdImage
{
    $info = @getimagesize($path);
    if ($info === false) {
        return null;
    }

    $image = match ($info[2]) {
        IMAGETYPE_PNG  => @imagecreatefrompng($path),
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_GIF  => @imagecreatefromgif($path),
        IMAGETYPE_WEBP => @imagecreatefromwebp($path),
        default        => false,
    };

    return $image instanceof \GdImage ? $image : null;
}
