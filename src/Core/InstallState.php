<?php

/*
 * ==========================================================================
 *
 *   ██████╗ ██████╗  ██████╗ ██████╗      ██████╗ ███████╗███╗   ██╗███████╗███████╗██╗███████╗
 *   ██╔══██╗██╔══██╗██╔═══██╗██╔══██╗    ██╔════╝ ██╔════╝████╗  ██║██╔════╝██╔════╝██║██╔════╝
 *   ██║  ██║██████╔╝██║   ██║██████╔╝    ██║  ███╗█████╗  ██╔██╗ ██║█████╗  ███████╗██║███████╗
 *   ██║  ██║██╔══██╗██║   ██║██╔═══╝     ██║   ██║██╔══╝  ██║╚██╗██║██╔══╝  ╚════██║██║╚════██║
 *   ██████╔╝██║  ██║╚██████╔╝██║         ╚██████╔╝███████╗██║ ╚████║███████╗███████║██║███████║
 *   ╚═════╝ ╚═╝  ╚═╝ ╚═════╝ ╚═╝          ╚═════╝ ╚══════╝╚═╝  ╚═══╝╚══════╝╚═╝╚══════╝
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
 * Inventário dos artefatos que marcam o ecossistema como instalado.
 *
 * O ecossistema tem DOIS instaladores, e cada um deixa o seu próprio rastro:
 *
 *   /DropGenesis/      → storage/installed.lock, config/local.php,
 *                        .env e api/config.php
 *   /api/install.php   → api/install.lock, api/config.php e .env
 *
 * A tela de "instalador bloqueado" mandava remover `storage/installed.lock` e
 * `config/local.php` — dois nomes escritos à mão no código. Quem tivesse
 * instalado pelo instalador da API procurava esses arquivos, não encontrava
 * nenhum dos dois, e ficava sem saber o que apagar para reinstalar: os
 * arquivos dele estavam em `api/`.
 *
 * Esta classe olha o disco e responde o que existe DE FATO, para que a tela
 * liste os arquivos reais daquela instalação em vez de um palpite.
 */
final class InstallState
{
    /** Rastro deixado pelo assistente do DropGenesis. */
    public const OWNER_GENESIS = 'DropGenesis';

    /** Rastro deixado pelo instalador da camada de API. */
    public const OWNER_API = 'API';

    /**
     * Artefatos conhecidos, na ordem em que devem ser removidos.
     *
     * @var array<int,array{path:string,owner:string,role:string}>
     */
    private const KNOWN = [
        ['path' => 'storage/installed.lock', 'owner' => self::OWNER_GENESIS, 'role' => 'lock'],
        ['path' => 'config/local.php',       'owner' => self::OWNER_GENESIS, 'role' => 'config'],
        ['path' => 'api/install.lock',       'owner' => self::OWNER_API,     'role' => 'lock'],
        ['path' => 'api/config.php',         'owner' => self::OWNER_API,     'role' => 'config'],
    ];

    /**
     * Todos os artefatos conhecidos, com o estado atual de cada um.
     *
     * @return array<int,array{path:string,owner:string,role:string,exists:bool,absolute:string}>
     */
    public static function artifacts(): array
    {
        $out = [];
        foreach (self::KNOWN as $item) {
            $absolute      = Paths::appFile($item['path']);
            $item['exists']   = is_file($absolute);
            $item['absolute'] = $absolute;
            $out[] = $item;
        }
        return $out;
    }

    /**
     * Apenas os artefatos presentes em disco — o que o operador precisa
     * remover para poder reinstalar.
     *
     * @return array<int,array{path:string,owner:string,role:string,exists:bool,absolute:string}>
     */
    public static function present(): array
    {
        return array_values(array_filter(
            self::artifacts(),
            static fn(array $a): bool => $a['exists']
        ));
    }

    /**
     * Instaladores que deixaram rastro.
     *
     * @return array<int,string>
     */
    public static function installers(): array
    {
        $owners = array_map(
            static fn(array $a): string => $a['owner'],
            self::present()
        );

        return array_values(array_unique($owners));
    }

    /** A trava específica do assistente do DropGenesis está presente? */
    public static function genesisLocked(): bool
    {
        return is_file(Paths::appFile('storage/installed.lock'));
    }

    /**
     * O ecossistema já foi instalado por QUALQUER um dos dois instaladores?
     *
     * Cada assistente olhava apenas para a própria trava. Na prática isso
     * deixava o outro aberto: quem instalasse pelo `/api/install.php` ficava
     * com `/DropGenesis/` servindo o formulário de instalação para qualquer
     * visitante, e vice-versa. Um instalador acessível depois da instalação é
     * porta de entrada, mesmo quando as validações internas impedem estragos.
     */
    public static function isInstalled(): bool
    {
        $travado = false;
        foreach (self::KNOWN as $item) {
            if ($item['role'] === 'lock' && is_file(Paths::appFile($item['path']))) {
                $travado = true;
                break;
            }
        }

        return $travado && !self::semConfiguracaoDoServidor();
    }

    /**
     * Há trava de instalação, mas nenhuma configuração de banco DESTE servidor?
     *
     * É o retrato da pasta de trabalho enviada inteira para a hospedagem. Em
     * 27/09/2026 o site no ar (InfinityFree) tinha `api/install.lock`,
     * `api/config.php` e o `config/local.php` da máquina do desenvolvedor — o
     * sistema se dizia instalado, este assistente respondia "instalador
     * bloqueado", e a aplicação tentava o MySQL em "localhost", onde a
     * hospedagem não tem banco. Sem como configurar, o site ficou parado.
     *
     * "Sem configuração" exige as três coisas:
     *   - não há .env na raiz;
     *   - não há DB_NAME no ambiente do servidor (Docker, SetEnv);
     *   - e a configuração que a aplicação montaria assim mesmo não conecta
     *     (o instalador antigo da API embutia credencial no api/config.php).
     * Com qualquer uma delas, a instalação é deste servidor e o assistente
     * continua fechado — inclusive com o banco fora do ar: reabrir o instalador
     * numa queda do MySQL entregaria o site a quem chegasse primeiro. Sem
     * nenhuma, o site não tem banco e não serve nada: é o mesmo estado de uma
     * instalação nova.
     */
    public static function semConfiguracaoDoServidor(): bool
    {
        static $resposta = null;
        if ($resposta !== null) {
            return $resposta;
        }
        if (is_file(Paths::appFile('.env')) || Env::get('DB_NAME') !== '') {
            return $resposta = false;
        }
        return $resposta = !self::configDaAplicacaoConecta();
    }

    /** A configuração que a aplicação montaria hoje (api/config.php) conecta num banco? */
    private static function configDaAplicacaoConecta(): bool
    {
        $config = Paths::appFile('api/config.php');
        if (!is_file($config) || !function_exists('mysqli_init')) {
            return false;
        }
        try {
            if (!defined('DB_HOST')) {
                // O config mostraria a tela de "servidor não configurado" e encerraria:
                // aqui a pergunta é só se ele conecta.
                if (!defined('DROP_INSTALADOR')) {
                    define('DROP_INSTALADOR', true);
                }
                require_once $config;
            }
            if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_NAME')) {
                return false;
            }
            $conn = mysqli_init();
            if ($conn === false) {
                return false;
            }
            $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
            $conectou = @$conn->real_connect(
                (string) constant('DB_HOST'),
                (string) constant('DB_USER'),
                defined('DB_PASS') ? (string) constant('DB_PASS') : '',
                (string) constant('DB_NAME')
            );
            if ($conectou) {
                $conn->close();
            }
            return (bool) $conectou;
        } catch (\Throwable) {
            return false;
        }
    }
}
