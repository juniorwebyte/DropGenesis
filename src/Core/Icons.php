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
 * Ícones da interface do instalador.
 *
 * As telas usavam emoji do sistema como ícone (🔍, ⚙️, 🚀, 🎉, ⚠️, ❌…) e, na
 * tela de entrada, imagens de emoji 3D. Isso traz três problemas concretos
 * num produto vendido:
 *
 *  - O desenho é do sistema operacional, não do produto. O mesmo instalador
 *    aparece com um traço no Windows, outro no macOS, outro no Android — e em
 *    servidores Linux sem fonte de emoji instalada, com o retângulo vazio.
 *  - Não acompanham a cor do tema: ficam coloridos sobre uma interface que é
 *    monocromática com um acento roxo.
 *  - Emoji decorativo em painel de infraestrutura lê como improviso.
 *
 * Aqui são SVG traçados, herdando `currentColor`, alinhados ao mesmo peso de
 * linha. Sem requisição externa, sem fonte de ícone, sem dependência.
 */
final class Icons
{
    /** Peso de linha padrão, para o conjunto ficar visualmente coeso. */
    private const STROKE = '1.75';

    /** @var array<string,string> Conteúdo interno de cada `<svg>`. */
    private const PATHS = [
        // --- Estado -------------------------------------------------------
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5.5"/>',
        'x-circle'     => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>',
        'alert'        => '<path d="M12 3.6 21.4 20H2.6z"/><path d="M12 10v4"/><path d="M12 17.2h.01"/>',
        'info'         => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
        'check'        => '<path d="m4.5 12.5 5 5 10-11"/>',
        'close'        => '<path d="M5.5 5.5l13 13M18.5 5.5l-13 13"/>',

        // --- Navegação ----------------------------------------------------
        'arrow-right'  => '<path d="M4 12h15"/><path d="m13 6 6 6-6 6"/>',
        'arrow-left'   => '<path d="M20 12H5"/><path d="m11 6-6 6 6 6"/>',

        // --- Etapas do instalador ----------------------------------------
        'search'       => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/>',
        'settings'     => '<circle cx="12" cy="12" r="3.2"/><path d="M12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M5.2 5.2l2.1 2.1M16.7 16.7l2.1 2.1M18.8 5.2l-2.1 2.1M7.3 16.7l-2.1 2.1"/>',
        'clock'        => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.3l3.4 2"/>',
        'terminal'     => '<rect x="2.5" y="4" width="19" height="16" rx="2.5"/><path d="m7 9.5 3 2.5-3 2.5"/><path d="M12.5 15h5"/>',

        // --- Infraestrutura ----------------------------------------------
        'database'     => '<ellipse cx="12" cy="6" rx="8" ry="3.2"/><path d="M4 6v12c0 1.8 3.6 3.2 8 3.2s8-1.4 8-3.2V6"/><path d="M4 12c0 1.8 3.6 3.2 8 3.2s8-1.4 8-3.2"/>',
        'server'       => '<rect x="2.5" y="3.5" width="19" height="7" rx="2"/><rect x="2.5" y="13.5" width="19" height="7" rx="2"/><path d="M6.5 7h.01M6.5 17h.01"/>',
        'monitor'      => '<rect x="2.5" y="3.5" width="19" height="13" rx="2"/><path d="M8.5 20.5h7M12 16.5v4"/>',
        'globe'        => '<circle cx="12" cy="12" r="9"/><path d="M3.2 9.5h17.6M3.2 14.5h17.6"/><path d="M12 3c-2.4 2.4-3.6 5.4-3.6 9s1.2 6.6 3.6 9c2.4-2.4 3.6-5.4 3.6-9S14.4 5.4 12 3Z"/>',
        'plug'        => '<path d="M9 3v6M15 3v6"/><path d="M6.5 9h11v3a5.5 5.5 0 0 1-11 0z"/><path d="M12 17.5V21"/>',

        // --- Segurança ----------------------------------------------------
        'lock'         => '<rect x="4" y="10.5" width="16" height="10.5" rx="2.2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>',
        'shield'       => '<path d="M12 3 4.8 5.8V11c0 4.6 2.9 8.3 7.2 10 4.3-1.7 7.2-5.4 7.2-10V5.8z"/><path d="m9 12 2.2 2.2L15.2 10"/>',
        'key'          => '<circle cx="8" cy="15" r="4.2"/><path d="m11.2 12 8.3-8.3"/><path d="m16.5 6.5 2.2 2.2M14 9l2.2 2.2"/>',

        // --- Diagnóstico --------------------------------------------------
        'activity'     => '<path d="M2.5 12h4l2.5-7 4.5 14 2.5-7h5.5"/>',
        'clipboard'    => '<rect x="5" y="4.5" width="14" height="16.5" rx="2"/><path d="M9 4.5V3.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 3.5v1"/><path d="m9.5 13 2 2 3.5-4"/>',
        'refresh'      => '<path d="M20.5 11a8.5 8.5 0 0 0-14.6-5L3 9"/><path d="M3.5 13a8.5 8.5 0 0 0 14.6 5l2.9-3"/><path d="M3 4v5h5M21 20v-5h-5"/>',

        // --- Ambiente -----------------------------------------------------
        'rocket'       => '<path d="M12 2.5c3.2 2.4 5 5.9 5 9.8l-2.8 3.2H9.8L7 12.3c0-3.9 1.8-7.4 5-9.8Z"/><circle cx="12" cy="10" r="1.9"/><path d="M9.8 15.5 8 21l3-1.6 3 1.6-1.8-5.5"/>',
        'flask'        => '<path d="M10 3v6.2L4.6 18a2 2 0 0 0 1.7 3h11.4a2 2 0 0 0 1.7-3L14 9.2V3"/><path d="M8.5 3h7"/><path d="M7.4 14h9.2"/>',
        'wrench'       => '<path d="M15.5 3a5.5 5.5 0 0 0-5 7.7L3 18.2 5.8 21l7.5-7.5A5.5 5.5 0 0 0 20 8.5l-3 3-2.5-2.5 3-3A5.5 5.5 0 0 0 15.5 3Z"/>',
        'pin'          => '<path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11Z"/><circle cx="12" cy="10" r="2.6"/>',
        'bulb'         => '<path d="M9.2 17.5a6.5 6.5 0 1 1 5.6 0"/><path d="M9.5 20.5h5M10 17.5h4"/>',
        'zap'          => '<path d="M13.5 2.5 4.5 13.8h6.2L10.5 21.5l9-11.3h-6.2z"/>',
        'layers'       => '<path d="m12 3 9 4.6-9 4.6-9-4.6z"/><path d="m3 12.6 9 4.6 9-4.6"/><path d="m3 17.1 9 4.6 9-4.6"/>',
    ];

    /**
     * Devolve o SVG do ícone.
     *
     * @param string $name  Chave em self::PATHS.
     * @param int    $size  Lado em pixels.
     * @param string $class Classe CSS opcional.
     */
    public static function render(string $name, int $size = 18, string $class = ''): string
    {
        $inner = self::PATHS[$name] ?? null;
        if ($inner === null) {
            return '';
        }

        $classAttr = $class !== ''
            ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"'
            : '';

        return '<svg' . $classAttr
            . ' width="' . $size . '" height="' . $size . '"'
            . ' viewBox="0 0 24 24" fill="none" stroke="currentColor"'
            . ' stroke-width="' . self::STROKE . '" stroke-linecap="round" stroke-linejoin="round"'
            . ' aria-hidden="true" focusable="false">' . $inner . '</svg>';
    }

    /** O ícone existe no conjunto? */
    public static function has(string $name): bool
    {
        return isset(self::PATHS[$name]);
    }

    /**
     * Nomes disponíveis.
     *
     * @return array<int,string>
     */
    public static function names(): array
    {
        return array_keys(self::PATHS);
    }
}
