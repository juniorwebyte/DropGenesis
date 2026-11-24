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
 * Resolução única de caminhos de disco e de URL do ecossistema.
 *
 * Antes desta classe, cada página do instalador reimplementava a detecção com
 * uma expressão regular própria — e todas com o nome da pasta escrito à mão
 * (`#/(drop/)?DropGenesis(/index\.php)?$#`). Isso quebrava em três cenários
 * reais do projeto: a raiz do domínio em VPS, qualquer subpasta com outro nome
 * em hospedagem compartilhada, e a própria pasta depois que o instalador se
 * autodesativa renomeando-se.
 *
 * Aqui o caminho é derivado de fatos verificáveis: o arquivo de entrada em
 * disco e o SCRIPT_NAME que o servidor publicou para ele. Nenhum nome de pasta
 * é presumido.
 */
final class Paths
{
    private static ?string $entryFile = null;
    private static ?string $basePath  = null;
    private static ?string $scheme    = null;
    private static ?string $host      = null;

    /**
     * Registra o arquivo de entrada da requisição (sempre `__FILE__` da página).
     *
     * É opcional: sem ele a classe cai em SCRIPT_FILENAME. Passar `__FILE__`
     * é preferível porque é exato mesmo quando o servidor reescreve a URI ou
     * quando o arquivo é incluído a partir de outro diretório.
     */
    public static function boot(?string $entryFile = null): void
    {
        if ($entryFile !== null && is_file($entryFile)) {
            self::$entryFile = self::normalize($entryFile);
            self::$basePath  = null; // força recálculo com a informação melhor
        }
    }

    // ---------------------------------------------------------------------
    // Caminhos em disco
    // ---------------------------------------------------------------------

    /** Pasta do instalador em disco (…/DropGenesis), qualquer que seja o nome. */
    public static function installerDir(): string
    {
        return self::normalize(dirname(__DIR__, 2));
    }

    /** Nome da pasta do instalador como está publicada. */
    public static function installerName(): string
    {
        return basename(self::installerDir());
    }

    /** Raiz da aplicação em disco (pasta que contém o instalador). */
    public static function appRoot(): string
    {
        return self::normalize(dirname(self::installerDir()));
    }

    // ---------------------------------------------------------------------
    // Caminhos de URL
    // ---------------------------------------------------------------------

    /**
     * Prefixo de URL da raiz da aplicação: '' no domínio raiz, '/drop' em
     * subpasta. Nunca termina com barra.
     */
    public static function basePath(): string
    {
        if (self::$basePath !== null) {
            return self::$basePath;
        }

        $scriptName = self::normalize((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $entry      = self::$entryFile ?? self::normalize((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
        $appRoot    = self::appRoot();

        // Caminho do arquivo de entrada relativo à raiz da aplicação.
        // Ex.: '/DropGenesis/check/index.php'
        $suffix = '';
        if ($entry !== '' && self::startsWithPath($entry, $appRoot)) {
            $suffix = substr($entry, strlen($appRoot));
        }

        if ($suffix !== '' && $scriptName !== '' && self::endsWithPath($scriptName, $suffix)) {
            // Caso exato: remove do SCRIPT_NAME o trecho que já sabemos ser
            // interno à aplicação; o que sobra é o prefixo público.
            $base = substr($scriptName, 0, strlen($scriptName) - strlen($suffix));
        } elseif ($suffix !== '' && $scriptName !== '') {
            // O servidor publicou um SCRIPT_NAME que não bate byte a byte com o
            // disco (alias, symlink, rewrite). Ainda assim sabemos a
            // profundidade do arquivo dentro da aplicação, então subimos o mesmo
            // número de segmentos a partir do diretório do script.
            $depth = substr_count(trim($suffix, '/'), '/');
            $base  = rtrim(dirname($scriptName), '/');
            for ($i = 0; $i < $depth; $i++) {
                $parent = rtrim(dirname($base), '/');
                $base   = ($parent === '.' || $parent === '\\') ? '' : $parent;
            }
        } else {
            $base = rtrim(dirname($scriptName), '/');
        }

        $base = rtrim($base, '/');
        if ($base === '.' || $base === '/') {
            $base = '';
        }

        return self::$basePath = $base;
    }

    /** Esquema da requisição, ciente de proxy reverso (Docker, Cloudflare, VPS). */
    public static function scheme(): string
    {
        if (self::$scheme !== null) {
            return self::$scheme;
        }

        $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
            || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
            || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')) === 'on')
            || (strtolower((string) ($_SERVER['HTTP_FRONT_END_HTTPS'] ?? '')) === 'on');

        return self::$scheme = $https ? 'https' : 'http';
    }

    /**
     * Host da requisição já higienizado.
     *
     * O Host é cabeçalho controlado pelo cliente e o valor termina gravado em
     * config/local.php e no .env, então qualquer caractere fora do conjunto
     * válido de host é descartado aqui, e não na hora de escrever o arquivo.
     */
    public static function host(): string
    {
        if (self::$host !== null) {
            return self::$host;
        }

        $raw = (string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');
        // X-Forwarded-Host pode vir como lista: fica o primeiro salto.
        $raw = trim(explode(',', $raw)[0]);
        $raw = preg_replace('/[^A-Za-z0-9\.\-\:\[\]]/', '', $raw) ?? '';

        return self::$host = ($raw !== '' ? $raw : 'localhost');
    }

    /** URL base pública da aplicação, sempre terminada em barra. */
    public static function baseUrl(): string
    {
        return self::scheme() . '://' . self::host() . self::basePath() . '/';
    }

    /** Prefixo de URL do instalador, sempre terminado em barra. */
    public static function installerPath(): string
    {
        return self::basePath() . '/' . self::installerName() . '/';
    }

    /**
     * Rota absoluta (relativa ao domínio) de um asset do instalador.
     *
     * Ex.: Paths::asset('src/Images/logo/logo.webp')
     *      → '/drop/DropGenesis/src/Images/logo/logo.webp'
     *
     * Absoluta de propósito: o mesmo asset é referenciado de `/DropGenesis/`,
     * de `/DropGenesis/check/` e de `/DropGenesis/errors/`, e caminhos
     * relativos com `../` quebravam em pelo menos um desses níveis.
     */
    public static function asset(string $relative): string
    {
        return self::installerPath() . ltrim(str_replace('\\', '/', $relative), '/');
    }

    /** Caminho em disco de um arquivo dentro do instalador. */
    public static function installerFile(string $relative): string
    {
        return self::installerDir() . '/' . ltrim(str_replace('\\', '/', $relative), '/');
    }

    /** Caminho em disco de um arquivo dentro da raiz da aplicação. */
    public static function appFile(string $relative): string
    {
        return self::appRoot() . '/' . ltrim(str_replace('\\', '/', $relative), '/');
    }

    // ---------------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------------

    private static function normalize(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    /** Comparação de caminho, insensível a caixa onde o sistema de arquivos é. */
    private static function startsWithPath(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }
        $needle = rtrim($needle, '/') . '/';
        return self::pathsEqual(substr($haystack, 0, strlen($needle)), $needle);
    }

    private static function endsWithPath(string $haystack, string $needle): bool
    {
        if ($needle === '' || strlen($needle) > strlen($haystack)) {
            return false;
        }
        return self::pathsEqual(substr($haystack, -strlen($needle)), $needle);
    }

    private static function pathsEqual(string $a, string $b): bool
    {
        return DIRECTORY_SEPARATOR === '\\'
            ? strcasecmp($a, $b) === 0
            : $a === $b;
    }
}
