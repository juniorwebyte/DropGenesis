<?php

namespace DropGenesis\Core;

/**
 * Variável de ambiente, para o motor do instalador.
 *
 * O motor roda antes de a aplicação existir e não pode exigir o
 * `api/libs/env.php` dela. Mas, quando ele já está carregado, `drop_env()` é a
 * fonte certa: é ela que enxerga o `.env` numa hospedagem que desliga
 * `putenv()` (a InfinityFree desliga), onde `getenv()` não enxerga nada.
 */
final class Env
{
    public static function get(string $chave, string $padrao = ''): string
    {
        if (function_exists('drop_env')) {
            $valor = \drop_env($chave);
            return $valor === null ? $padrao : (string) $valor;
        }

        $valor = getenv($chave);
        if ($valor !== false && $valor !== '') {
            return $valor;
        }

        $valor = $_ENV[$chave] ?? '';
        return is_string($valor) && $valor !== '' ? $valor : $padrao;
    }
}
