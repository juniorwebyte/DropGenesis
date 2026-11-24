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
 * A universal installation, provisioning and bootstrap engine for PHP
 * applications.
 *
 * Developed and maintained by Webyte Desenvolvimentos.
 *
 * Author:
 *   Júnior Alves
 *
 * Website:
 *   https://webytehub.com
 *
 * Documentation:
 *   https://docs.webytehub.com
 *
 * Repository:
 *   https://github.com/juniorwebyte/dropgenesis
 *
 * Version:
 *   @version
 *
 * ==========================================================================
 * LICENSE
 * ==========================================================================
 *
 * Copyright (c) Webyte Desenvolvimentos.
 * All rights reserved.
 *
 * This software is protected by copyright laws and international treaties.
 * It may only be used, copied, modified or redistributed in accordance
 * with the terms of its respective license.
 *
 * Unauthorized reproduction, distribution or commercialization of this
 * software, in whole or in part, is strictly prohibited.
 *
 * For license information, please visit:
 * https://webytehub.com/licencas
 *
 * ==========================================================================
 *
 * "Every great application deserves a great beginning."
 *
 * Powered by DropGenesis.
 *
 * ==========================================================================
 */

namespace DropGenesis\Core;

use DropGenesis\Contracts\DriverInterface;
use DropGenesis\Drivers\NativeDriver;

class Engine
{
    private string $rootPath;
    private ManifestParser $manifest;
    private ?DriverInterface $driver = null;
    private Translator $translator;
    private EventDispatcher $events;
    private ?License $license = null;

    public function __construct(string $rootPath, ?string $entryFile = null)
    {
        $this->rootPath = rtrim($rootPath, '/\\');

        Paths::boot($entryFile);

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Translator
        $locale = is_string($_GET['lang'] ?? null) ? $_GET['lang'] : (string) ($_SESSION['drop_lang'] ?? '');
        $this->translator = new Translator($locale);
        $_SESSION['drop_lang'] = $this->translator->getLocale();

        // Manifest — localizado pela pasta real do instalador. O caminho era
        // montado como "$rootPath/DropGenesis/drop.json", com o nome da pasta
        // escrito à mão: renomear a pasta (coisa que o próprio instalador faz
        // ao se autodesativar) derrubava todas as páginas com "Manifest file
        // not found".
        $this->manifest = new ManifestParser(Paths::installerFile('drop.json'));

        // Event dispatcher singleton
        $this->events = EventDispatcher::getInstance();
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getTranslator(): Translator
    {
        return $this->translator;
    }

    public function getManifest(): ManifestParser
    {
        return $this->manifest;
    }

    public function getEvents(): EventDispatcher
    {
        return $this->events;
    }

    public function getLicense(): License
    {
        return $this->license ??= new License($this->manifest);
    }

    public function getDriver(): DriverInterface
    {
        if ($this->driver === null) {
            $this->initDriver();
        }
        return $this->driver;
    }

    // -------------------------------------------------------------------------
    // Driver
    // -------------------------------------------------------------------------

    private function initDriver(): void
    {
        $driverName = strtolower($this->manifest->getDriver());
        if ($driverName === 'native') {
            $this->driver = new NativeDriver($this->rootPath);
        } elseif ($driverName === 'laravel') {
            $this->driver = new \DropGenesis\Drivers\LaravelDriver($this->rootPath);
        } elseif ($driverName === 'symfony') {
            $this->driver = new \DropGenesis\Drivers\SymfonyDriver($this->rootPath);
        } else {
            throw new \RuntimeException("Driver not supported: " . $driverName);
        }
    }

    // -------------------------------------------------------------------------
    // Requirements check (session-cached per install session)
    // -------------------------------------------------------------------------

    /**
     * Confere os requisitos de servidor declarados no drop.json.
     *
     * O resultado NÃO é cacheado entre requisições. Ele era guardado em
     * $_SESSION "para evitar checagens redundantes", mas o efeito prático era o
     * oposto do desejado: quem visse "✗ Escrita em /storage", corrigisse a
     * permissão e recarregasse a página continuava vendo a falha até trocar de
     * sessão — o botão de avançar seguia bloqueado sem motivo. O custo real
     * medido são meia dúzia de `is_writable()` e `extension_loaded()`, muito
     * abaixo do custo de gravar o arquivo de sessão.
     *
     * @return array<int,array{name:string,ok:bool,details:string}>
     */
    public function checkRequirements(bool $forceRefresh = false): array
    {
        $reqs    = $this->manifest->getRequirements();
        $results = [];

        // PHP version
        $minPhp      = $reqs['php'] ?? '8.0';
        $minPhpClean = preg_replace('/[^0-9.]/', '', $minPhp);
        $results[]   = [
            'name'    => 'PHP >= ' . $minPhpClean,
            'ok'      => version_compare(PHP_VERSION, $minPhpClean, '>='),
            'details' => 'PHP ' . PHP_VERSION,
        ];

        // PHP extensions
        foreach ((array) ($reqs['extensions'] ?? []) as $ext) {
            $results[] = [
                'name'    => 'ext-' . $ext,
                'ok'      => extension_loaded($ext),
                'details' => extension_loaded($ext) ? 'loaded' : 'missing',
            ];
        }

        // Writable directories
        foreach ((array) ($reqs['dirs_writable'] ?? []) as $dir) {
            $path      = $this->rootPath . '/' . $dir;
            $writable  = is_dir($path) && is_writable($path);
            $results[] = [
                'name'    => 'writable: /' . $dir,
                'ok'      => $writable,
                'details' => str_replace('\\', '/', $path),
            ];
        }

        // A proteção das pastas internas mora no .htaccess da raiz. Num Apache ou
        // LiteSpeed sem ele, logs, backups, scripts e vendor ficam públicos — era o
        // estado do site no ar em 27/09/2026 (o arquivo oculto não subiu), e nada
        // acusava. Em nginx o .htaccess não conta: a proteção é o modelo de conf.
        $servidorWeb = strtolower((string) ($_SERVER['SERVER_SOFTWARE'] ?? ''));
        if (str_contains($servidorWeb, 'apache') || str_contains($servidorWeb, 'litespeed')) {
            $temHtaccess = is_file($this->rootPath . '/.htaccess');
            $results[] = [
                'name'    => 'Arquivo .htaccess da raiz',
                'ok'      => $temHtaccess,
                'details' => $temHtaccess
                    ? 'presente'
                    : 'ausente — sem ele logs, backups, scripts e vendor ficam públicos. Envie o .htaccess da raiz '
                        . 'do pacote (é um arquivo oculto: ative "mostrar arquivos ocultos" no gerenciador ou no FTP).',
            ];
        }

        // O pacote chegou inteiro? Só quando há manifesto (instalação por pacote).
        $manifestoPacote = (string) ($reqs['package_manifest'] ?? '');
        if ($manifestoPacote !== '') {
            $pacote = Integrity::check($this->rootPath, $manifestoPacote);
            if ($pacote['manifesto']) {
                $faltam = array_merge($pacote['ausentes'], $pacote['incompletos']);
                $results[] = [
                    'name'    => 'Arquivos do pacote',
                    'ok'      => $faltam === [],
                    'details' => $faltam === []
                        ? $pacote['total'] . ' de ' . $pacote['total'] . ' conferidos'
                        : count($pacote['ausentes']) . ' ausente(s), ' . count($pacote['incompletos']) . ' incompleto(s): '
                            . implode(', ', array_slice($faltam, 0, 5)) . (count($faltam) > 5 ? ' …' : ''),
                ];
            }
        }

        return $results;
    }

    // -------------------------------------------------------------------------
    // CSRF protection helpers
    // -------------------------------------------------------------------------

    /**
     * Generate (or retrieve existing) CSRF token for the current session.
     * Stores it in $_SESSION['drop_csrf_token'].
     */
    public function generateCsrfToken(): string
    {
        if (empty($_SESSION['drop_csrf_token'])) {
            $_SESSION['drop_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['drop_csrf_token'];
    }

    /**
     * Confere o token CSRF enviado contra o guardado na sessão.
     * Usa hash_equals para não vazar informação por tempo de comparação.
     *
     * O token só é descartado quando `$consume` é verdadeiro. Antes ele era
     * invalidado em toda validação bem-sucedida, o que quebrava a própria tela
     * do instalador: "Testar Conexão" consumia o token e o clique seguinte em
     * "Validar Licença" — ou o envio do formulário — passava a ser recusado,
     * sem nenhuma forma de recuperar sem recarregar a página. Ações de leitura
     * (testar banco, conferir licença) validam sem consumir; o POST final da
     * instalação consome, que é onde a proteção contra reenvio importa.
     *
     * @throws \RuntimeException quando o token está ausente ou não confere.
     */
    public function validateCsrfToken(string $submitted, bool $consume = false): void
    {
        $stored = $_SESSION['drop_csrf_token'] ?? '';
        if ($stored === '' || !hash_equals($stored, $submitted)) {
            throw new \RuntimeException('Invalid CSRF token. Request rejected.');
        }
        if ($consume) {
            unset($_SESSION['drop_csrf_token']);
        }
    }

    /**
     * Convenience: render a hidden CSRF input field.
     */
    public function csrfField(): string
    {
        $token = htmlspecialchars($this->generateCsrfToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }
}
