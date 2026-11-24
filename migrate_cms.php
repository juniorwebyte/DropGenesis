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

declare(strict_types=1);

/*
 * A autorização precisa vir ANTES de qualquer require.
 *
 * A ordem estava invertida: o `require_once api/config.php` era a primeira
 * linha executável e a checagem de sessão vinha depois dele. Duas consequências
 * reais, ambas verificadas:
 *
 *  - Antes da instalação, `api/config.php` não existe. O require falhava com
 *    fatal error impresso na resposta HTTP, revelando o caminho absoluto do
 *    servidor — e isso acontecia sem passar por controle algum.
 *  - A condição só exigia sessão quando `installed.lock` JÁ existia. Na janela
 *    entre gravar a configuração e gravar a trava, e em qualquer instalação
 *    onde a trava não tenha sido criada, este script executava CREATE TABLE e
 *    INSERT no banco para qualquer visitante anônimo.
 *
 * Agora a regra é única e não depende de estado de arquivo: pela web, exige a
 * sessão autenticada do atualizador; pelo CLI, é sempre permitido.
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$runningFromCli = (PHP_SAPI === 'cli');

if (!$runningFromCli) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if (($_SESSION['updater_authorized'] ?? false) !== true) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit("Forbidden: autentique-se no atualizador (DropGenesis/update/) antes de rodar a migração.\n");
    }
}

$apiConfig = dirname(__DIR__) . '/api/config.php';
if (!is_file($apiConfig)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit("A aplicação ainda não foi instalada: api/config.php não existe.\n");
}

require_once $apiConfig;
require_once dirname(__DIR__) . '/api/libs/db_helpers.php';

try {
    $conn = drop_db_connect();

    // 1. Criar Tabelas
    $tables = [
        "CREATE TABLE IF NOT EXISTS `api_landing_sections` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type` varchar(50) NOT NULL,
            `name` varchar(100) NOT NULL,
            `content_json` json NULL,
            `display_order` int(11) DEFAULT 0,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "CREATE TABLE IF NOT EXISTS `api_ads_campaigns` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `ad_type` varchar(50) NOT NULL,
            `ad_code` text NULL,
            `image_url` varchar(255) NULL,
            `link_url` varchar(255) NULL,
            `target_device` enum('all','desktop','mobile') DEFAULT 'all',
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "CREATE TABLE IF NOT EXISTS `api_landing_design_tokens` (
            `token_key` varchar(50) NOT NULL,
            `token_value` varchar(255) NOT NULL,
            PRIMARY KEY (`token_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
    ];

    foreach ($tables as $sql) {
        $conn->query($sql);
    }

    echo "Tabelas estruturais criadas com sucesso.\n";

    // Ler as configurações atuais para preencher as seções
    $landing_settings = [];
    $qs = $conn->query("SHOW TABLES LIKE 'api_landing_settings'");
    if ($qs && $qs->num_rows > 0) {
        $res = $conn->query("SELECT * FROM api_landing_settings");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $landing_settings[$r['setting_key']] = $r['setting_value'];
            }
        }
    }

    // Se já existem seções, não recriar.
    // O CREATE TABLE acima é executado com o retorno descartado; se ele falhar
    // (permissão, engine indisponível), esta consulta devolve `false` e o
    // `->fetch_assoc()` seguinte era um fatal error sem mensagem útil.
    $check_sec = $conn->query("SELECT count(*) as c FROM api_landing_sections");
    if (!$check_sec) {
        throw new RuntimeException('Tabela api_landing_sections indisponível: ' . $conn->error);
    }
    $row = $check_sec->fetch_assoc();
    if ($row['c'] == 0) {

        $hero_json = json_encode([
            'hero_tag' => $landing_settings['hero_tag'] ?? 'Fragmentos da Realidade',
            'hero_title' => $landing_settings['hero_title'] ?? 'A verdadeira <span>Frequência.</span>',
            'hero_subtitle' => $landing_settings['hero_subtitle'] ?? 'Bem vindo ao ecossistema',
            'hero_desc' => $landing_settings['hero_desc'] ?? 'Gerencie suas aplicações.',
        ]);

        $sobre_json = json_encode([
            'sobre_tag' => $landing_settings['sobre_tag'] ?? 'A Origem',
            'sobre_title' => $landing_settings['sobre_title'] ?? 'O Ecossistema Drop',
            'sobre_desc' => $landing_settings['sobre_desc'] ?? 'A união definitiva.',
            'sobre_p1' => $landing_settings['sobre_p1'] ?? '',
            'sobre_p2' => $landing_settings['sobre_p2'] ?? '',
            'sobre_p3' => $landing_settings['sobre_p3'] ?? '',
            'sobre_quote' => $landing_settings['sobre_quote'] ?? ''
        ]);

        $recursos_json = json_encode([
            'recursos_title' => $landing_settings['recursos_title'] ?? 'Essência Pura',
            'recursos_desc' => $landing_settings['recursos_desc'] ?? 'Nossa infraestrutura transcende...',
            'cerebro_tag' => $landing_settings['cerebro_tag'] ?? 'Produto Estrela',
            'cerebro_title' => $landing_settings['cerebro_title'] ?? 'Drop Converse AI',
            'cerebro_subtitle' => $landing_settings['cerebro_subtitle'] ?? 'Cérebro Digital.',
            'cerebro_desc' => $landing_settings['cerebro_desc'] ?? '',
            'ploutos_tag' => $landing_settings['ploutos_tag'] ?? 'Módulo Financeiro',
            'ploutos_title' => $landing_settings['ploutos_title'] ?? 'PloutosLedger<br><span style=\"color: #10b981;\">clareza do caixa à venda.</span>',
            'ploutos_subtitle' => $landing_settings['ploutos_subtitle'] ?? 'clareza do caixa à venda.',
            'ploutos_desc1' => $landing_settings['ploutos_desc1'] ?? '',
            'ploutos_desc2' => $landing_settings['ploutos_desc2'] ?? ''
        ]);

        $planos_json = json_encode([
            'planos_title' => $landing_settings['planos_title'] ?? 'O Custo da Verdade',
            'planos_desc' => $landing_settings['planos_desc'] ?? 'Escolha o seu ciclo e desbloqueie o potencial máximo da WebyteHub Engineering.'
        ]);

        $faq_json = json_encode([
            'faq_title' => $landing_settings['faq_title'] ?? 'Perguntas Frequentes',
            'faq_desc' => $landing_settings['faq_desc'] ?? 'Respostas rápidas para...'
        ]);

        $contato_json = json_encode([
            'contato_title' => $landing_settings['contato_title'] ?? 'Abra uma Frequência.',
            'contato_desc' => $landing_settings['contato_desc'] ?? 'Inicie um diálogo direto...',
            'footer_title' => $landing_settings['footer_title'] ?? 'Abra uma <span style=\"color: var(--primary);\">Frequência.</span>',
            'footer_desc' => $landing_settings['footer_desc'] ?? 'Inicie um diálogo direto.',
            'footer_copyright' => $landing_settings['footer_copyright'] ?? '2026 WebyteHub • Todos os direitos reservados.'
        ]);

        $inserts = [
            ["hero", "Sessão Hero (Principal)", $hero_json, 1],
            ["sobre", "Sessão Sobre (Origem)", $sobre_json, 2],
            ["recursos", "Sessão de Recursos (Essência)", $recursos_json, 3],
            ["planos", "Sessão de Planos", $planos_json, 4],
            ["faq", "Sessão FAQ", $faq_json, 5],
            ["contato", "Sessão de Contato/Footer", $contato_json, 6],
            ["cards", "Componente Dinâmico: Cards", json_encode([]), 7],
            ["ticker", "Componente Dinâmico: Ticker", json_encode([]), 8],
            ["testimonials", "Componente Dinâmico: Depoimentos", json_encode([]), 9]
        ];

        $stmt = $conn->prepare("INSERT INTO api_landing_sections (type, name, content_json, display_order) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            foreach ($inserts as $item) {
                $stmt->bind_param("sssi", $item[0], $item[1], $item[2], $item[3]);
                $stmt->execute();
            }
            $stmt->close();
            echo "Dados legados convertidos com sucesso para a tabela de seções.\n";
        }
    } else {
        echo "Tabela api_landing_sections já possui dados. Ignorando migração inicial.\n";
    }

    $conn->close();
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
