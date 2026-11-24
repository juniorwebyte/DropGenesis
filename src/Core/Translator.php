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

class Translator
{
    private array $translations = [];
    private string $currentLocale = 'pt';

    /** Supported locales — extend this array to add more languages. */
    private const SUPPORTED = ['pt', 'en', 'es', 'fr'];

    /**
     * Dicionários já carregados nesta requisição.
     *
     * @var array<string,array<string,string>>
     */
    private static array $loaded = [];

    public function __construct(string $locale = '')
    {
        if (empty($locale)) {
            $locale = $this->detectBrowserLanguage();
        }
        $this->setLocale($locale);
    }

    public function detectBrowserLanguage(): string
    {
        $langHeader = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (empty($langHeader)) {
            return 'pt';
        }

        preg_match_all('/([a-z]{2})/i', $langHeader, $langs);
        foreach ($langs[1] as $lang) {
            $lang = strtolower($lang);
            if (in_array($lang, self::SUPPORTED, true)) {
                return $lang;
            }
        }

        return 'pt';
    }

    /**
     * Carrega o dicionário do idioma.
     *
     * As traduções eram copiadas inteiras para $_SESSION. Cada arquivo de
     * idioma tem ~15 KB, então a sessão passava a carregar (serializar,
     * gravar, ler e desserializar) esse volume a cada requisição — para
     * substituir um `require` de arquivo que o OPcache já mantém compilado em
     * memória. Trocar sessão por cache estático por processo tira a gravação
     * em disco do caminho e ainda faz o instalador refletir na hora qualquer
     * ajuste nos arquivos de idioma.
     */
    public function setLocale(string $locale): void
    {
        $locale = preg_replace('/[^a-z]/', '', strtolower($locale)) ?? '';
        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'pt';
        }

        if (isset(self::$loaded[$locale])) {
            $this->translations  = self::$loaded[$locale];
            $this->currentLocale = $locale;
            return;
        }

        // Tenta o idioma pedido; cai para português se o arquivo não existir.
        $file = dirname(__DIR__) . '/Languages/' . $locale . '.php';
        if (!is_file($file)) {
            $locale = 'pt';
            $file   = dirname(__DIR__) . '/Languages/pt.php';
        }

        $loaded = is_file($file) ? require $file : [];

        $this->translations  = is_array($loaded) ? $loaded : [];
        $this->currentLocale = $locale;

        self::$loaded[$locale] = $this->translations;
    }

    public function getLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * Return the list of languages currently supported.
     * @return string[]
     */
    public function getSupportedLocales(): array
    {
        return self::SUPPORTED;
    }

    public function translate(string $key, array $replace = []): string
    {
        $text = $this->translations[$key] ?? $key;
        foreach ($replace as $k => $v) {
            $text = str_replace(':' . $k, (string) $v, $text);
        }
        return $text;
    }
}
