<?php

/*
 * ==========================================================================
 *
 *   ██████╗ ██████╗  ██████╗ ██████╗      ██████╗ ███████╗███╗   ██╗███████╗███████╗██╗███████╗
 *   ██╔══██╗██╔══██╗██╔═══██╗██╔══██╗    ██╔════╝ ██╔════╝████╗  ██║██╔════╝██╔════╝██║██╔════╝
 *   ██║  ██║██████╔╝██║   ██║██████╔╝    ██║  ███╗█████╗  ██╔██╗ ██║█████╗  ███████╗██║███████╗
 *   ██║  ██║██╔══██╗██║   ██║██╔═══╝     ██║   ██║██╔══╝  ██║╚██╗██║██╔══╝  ╚════██║██║╚════██║
 *   ██████╔╝██║  ██║╚██████╔╝██║         ╚██████╔╝███████╗██║ ╚████║███████╗███████║██║███████║
 *   ╚═════╝ ╚═╝  ╚═╝ ╚═════╝ ╚═╝          ╚═════╝ ╚══════╝╚═╝  ╚═══╝╚══════╝╚══════╝╚═╝╚══════╝
 *
 * ==========================================================================
 *
 * DropGenesis
 * Universal PHP Installation Engine
 *
 * Developed and maintained by Webyte Desenvolvimentos.
 *
 * Author:
 *   Júnior Alves
 *
 * Website:
 *   https://webytehub.com
 *
 * ==========================================================================
 * LICENSE
 * ==========================================================================
 *
 * Copyright (c) Webyte Desenvolvimentos.
 * All rights reserved.
 *
 * For license information, please visit:
 * https://webytehub.com/licencas
 *
 * ==========================================================================
 */

declare(strict_types=1);

namespace DropGenesis\Core;

/**
 * Catálogo dos assets do instalador.
 *
 * Todas as páginas repetiam o mesmo bloco de `scandir()` com um prefixo
 * relativo diferente (`src/Images/scroll/` numa, `../src/Images/scroll/` noutra)
 * e ordenavam pelo retorno cru do sistema de arquivos — o que coloca a
 * sequência em 1, 10, 11, 12, 13, 2, 3… Aqui a listagem é única, em ordem
 * natural, e devolvida como rota absoluta que funciona de qualquer página.
 */
final class Assets
{
    /** Diretório das imagens do scroll, relativo à pasta do instalador. */
    private const SCROLL_DIR = 'src/Images/scroll';

    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'avif'];

    /** @var array<string,array<int,string>> */
    private static array $cache = [];

    /**
     * Escurecimento aplicado sobre as imagens do fundo.
     *
     * Antes, cada tela empilhava um gradiente de 0.80–0.94 de opacidade E um
     * `filter: brightness(0.6)` por cima. Somados, sobrava por volta de 4% da
     * luminosidade original: a sequência de imagens rodava, trocava e
     * pré-carregava corretamente, mas era praticamente invisível — o efeito
     * prático para quem usava era o de um fundo preto chapado.
     *
     * Estes valores deixam a imagem legível e ainda garantem contraste de
     * texto sobre os cartões translúcidos. É o único lugar a ajustar caso
     * queira mais claro ou mais escuro.
     */
    public const OVERLAY = 'linear-gradient(rgba(3, 4, 6, 0.72), rgba(3, 4, 6, 0.84))';

    /** Variante levemente mais densa, para telas sem cartão sobre o fundo. */
    public const OVERLAY_DEEP = 'linear-gradient(rgba(3, 4, 6, 0.76), rgba(3, 4, 6, 0.87))';

    /**
     * Imagens do scroll em ordem natural, como rotas absolutas de URL.
     *
     * Quando existir um irmão `.webp` e o navegador anunciar suporte, ele é
     * servido no lugar do PNG original — a mesma imagem, uma fração do peso.
     * O `.webp` é gerado fora do repositório: o `tools/optimize-scroll-images.php`
     * que este comentário citava não existe.
     *
     * @return array<int,string>
     */
    public static function scrollImages(): array
    {
        $acceptsWebp = self::clientAcceptsWebp();
        $cacheKey    = 'scroll_' . ($acceptsWebp ? 'webp' : 'raw');

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $dir   = Paths::installerFile(self::SCROLL_DIR);
        $names = self::listImageFiles($dir);

        // `1.png` e `1.webp` são a MESMA tela em duas codificações. Agrupar
        // pelo nome sem extensão é o que impede o scroll de exibir cada imagem
        // duas vezes depois que tools/optimize-images.php roda.
        $variants = [];
        foreach ($names as $name) {
            $variants[(string) pathinfo($name, PATHINFO_FILENAME)][] = $name;
        }

        $images = [];
        foreach ($variants as $group) {
            $chosen = null;

            if ($acceptsWebp) {
                foreach ($group as $candidate) {
                    if (self::hasExtension($candidate, 'webp') || self::hasExtension($candidate, 'avif')) {
                        $chosen = $candidate;
                        break;
                    }
                }
            }

            if ($chosen === null) {
                foreach ($group as $candidate) {
                    if (!self::hasExtension($candidate, 'webp') && !self::hasExtension($candidate, 'avif')) {
                        $chosen = $candidate;
                        break;
                    }
                }
            }

            // Grupo só com formatos modernos e cliente que não os anuncia:
            // servir algo é melhor do que abrir um buraco na sequência.
            $chosen ??= $group[0];

            $images[] = Paths::asset(self::SCROLL_DIR . '/' . $chosen);
        }

        if ($images === []) {
            // Sem pasta de scroll o fundo não pode ficar vazio: a marca serve
            // de plano de fundo único, exatamente como antes.
            $images[] = self::logo();
        }

        return self::$cache[$cacheKey] = $images;
    }

    /**
     * Primeira imagem do scroll — a que entra no CSS e é a única no caminho
     * crítico de renderização. As demais são buscadas pelo JS sob demanda.
     */
    public static function firstScrollImage(): string
    {
        $images = self::scrollImages();
        return $images[0];
    }

    /**
     * Rota absoluta de uma imagem do instalador, preferindo o irmão `.webp`.
     *
     * A escolha é feita no servidor a partir do cabeçalho Accept, então o
     * HTML não muda: nenhuma tag `<picture>`, nenhum atributo novo. Quando o
     * `.webp` não existe (ninguém rodou `tools/optimize-images.php`) ou o
     * cliente não o anuncia, o original é servido inalterado.
     */
    public static function image(string $relative): string
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');

        if (self::clientAcceptsWebp() && !self::hasExtension($relative, 'webp')) {
            $webp = preg_replace('/\.[^.]+$/', '.webp', $relative) ?? '';
            if ($webp !== '' && is_file(Paths::installerFile($webp))) {
                return Paths::asset($webp);
            }
        }

        return Paths::asset($relative);
    }

    /** Rota absoluta da logomarca. */
    public static function logo(): string
    {
        return Paths::asset('src/Images/logo/logo.webp');
    }

    public static function logoExists(): bool
    {
        return is_file(Paths::installerFile('src/Images/logo/logo.webp'));
    }

    /**
     * Rota absoluta do favicon.
     *
     * Prefere o `.ico` (15 KB) ao `.png` (1,8 MB): é o mesmo ícone, e o PNG
     * sozinho pesava mais do que toda a primeira tela do instalador.
     */
    public static function favicon(): string
    {
        return is_file(Paths::installerFile('src/Images/logo/favicon.ico'))
            ? Paths::asset('src/Images/logo/favicon.ico')
            : Paths::asset('src/Images/logo/favicon.webp');
    }

    /**
     * CSS das duas camadas do fundo em scroll.
     *
     * Emitido a partir daqui para que as oito telas do instalador compartilhem
     * exatamente o mesmo tratamento. Antes o bloco estava copiado em cada
     * arquivo, com filtros divergentes, e acertar o brilho exigia editar todos.
     */
    public static function backgroundCss(string $overlay = self::OVERLAY): string
    {
        $first = htmlspecialchars(self::firstScrollImage(), ENT_QUOTES, 'UTF-8');

        return <<<CSS
    #db-master-bg,
    #db-master-bg-next {
      position: fixed;
      top: -12vh;
      left: -12vw;
      right: -12vw;
      bottom: -12vh;
      z-index: -10;
      /* Um desfoque leve mantém a imagem perfeitamente perceptível — cor,
         profundidade e movimento — sem que os detalhes finos dela disputem
         atenção com o texto do formulário logo à frente. */
      filter: contrast(1.04) saturate(1.05) blur(3px);
      transform: scale(1.03);
      transition: opacity 1.6s ease;
      pointer-events: none;
    }

    #db-master-bg {
      background: {$overlay},
        url('{$first}') center/cover no-repeat;
    }

    #db-master-bg-next {
      opacity: 0;
    }
CSS;
    }

    /** Rota absoluta da folha de estilo local das fontes Geist. */
    public static function geistCss(): string
    {
        return Paths::asset('src/Css/geist.css');
    }

    /** Rota absoluta do módulo de fundo animado. */
    public static function scrollBgJs(): string
    {
        return Paths::asset('src/Js/scroll-bg.js');
    }

    /**
     * Payload consumido por `src/Js/scroll-bg.js`.
     *
     * @param  string $overlay Gradiente de sobreposição da página, preservado
     *                         tal e qual para que a camada de transição fique
     *                         visualmente idêntica à camada base.
     * @return array<string,mixed>
     */
    public static function scrollPayload(string $overlay, int $intervalMs = 8000): array
    {
        return [
            'images'   => self::scrollImages(),
            'overlay'  => $overlay,
            'interval' => $intervalMs,
        ];
    }

    // ---------------------------------------------------------------------
    // Internos
    // ---------------------------------------------------------------------

    /**
     * Nomes de arquivo de imagem do diretório, em ordem natural.
     *
     * @return array<int,string>
     */
    private static function listImageFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $entries = @scandir($dir);
        if ($entries === false) {
            return [];
        }

        $files = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (!self::isImage($entry)) {
                continue;
            }
            if (!is_file($dir . '/' . $entry)) {
                continue;
            }
            $files[] = $entry;
        }

        // `natcasesort` é o que faz 2.png vir antes de 10.png. O retorno cru do
        // `scandir` é lexicográfico e embaralhava a sequência das telas.
        natcasesort($files);

        return array_values($files);
    }

    private static function isImage(string $name): bool
    {
        foreach (self::IMAGE_EXTENSIONS as $ext) {
            if (self::hasExtension($name, $ext)) {
                return true;
            }
        }
        return false;
    }

    private static function hasExtension(string $name, string $ext): bool
    {
        return strcasecmp((string) pathinfo($name, PATHINFO_EXTENSION), $ext) === 0;
    }

    private static function clientAcceptsWebp(): bool
    {
        return stripos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'image/webp') !== false;
    }
}
