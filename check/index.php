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

use DropGenesis\Core\Assets;
use DropGenesis\Core\Icons;
use DropGenesis\Core\Paths;

// Registra autoloader PSR-4 dinâmico para evitar requires manuais
spl_autoload_register(function (string $class) {
    $prefix = 'DropGenesis\\';
    $baseDir = dirname(__DIR__) . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$engine = new \DropGenesis\Core\Engine(dirname(dirname(__DIR__)), __FILE__);

function __($key, $replace = []) {
    global $engine;
    return $engine->getTranslator()->translate($key, $replace);
}

/**
 * WebyteHub - System Check & Auto-Prepare v2.0
 * Analisa o projeto raiz e prepara o motor Drop
 */

error_reporting(E_ALL);
// Erros vão para o log, não para a tela. Esta página é acessível sem
// autenticação antes da instalação, e `display_errors = 1` expunha caminhos
// absolutos do servidor e credenciais em mensagens de erro do MySQL.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$root = Paths::appRoot();
$projectName = basename($root);

// O require era incondicional: numa instalação autônoma do motor o arquivo não
// existe e a página morria com fatal error.
$infrastructureLib = Paths::appFile('api/libs/infrastructure.php');
if (is_file($infrastructureLib)) {
    require_once $infrastructureLib;
}

function db_exec_sql_file(mysqli $conn, string $file): void
{
    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('Falha ao ler o arquivo SQL.');
    }
    if (!$conn->multi_query($sql)) {
        throw new RuntimeException('Erro ao importar SQL: ' . $conn->error);
    }
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
        if ($conn->errno) {
            throw new RuntimeException('Erro ao executar SQL no arquivo: ' . basename($file) . ' — ' . $conn->error);
        }
    } while ($conn->more_results() && $conn->next_result());
}

function db_has_tables(mysqli $conn, string $dbName): bool
{
    $safeDb = $conn->real_escape_string($dbName);
    $res = $conn->query("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = '{$safeDb}'");
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['c']) && (int) $row['c'] > 0;
}

// Fundo em scroll — rotas absolutas resolvidas por Assets. Os caminhos eram
// relativos ('../src/Images/...'), o que só funcionava porque esta página está
// exatamente um nível abaixo; e a ordem vinha crua do scandir, colocando a
// sequência em 1, 10, 11, 12, 13, 2, 3…
$scrollImages = Assets::scrollImages();
$defaultBg = Assets::firstScrollImage();
$bgOverlay = Assets::OVERLAY;

// Auditoria de Rotas (.htaccess)
$routeLog = [];
$htaccessFile = $root . '/.htaccess';
if (is_file($htaccessFile)) {
    $routeLog[] = __('audit_htaccess_ok');
} else {
    $routeLog[] = __('audit_htaccess_warn');
}

// Auditoria de Gateway (index.php)
$indexFile = $root . '/index.php';
if (is_file($indexFile)) {
    $content = file_get_contents($indexFile);
    if (strpos($content, 'ecossistema Drop') === false && strpos($content, 'DropGenesis') === false) {
        $routeLog[] = __('audit_gateway_critical');
    } else {
        $routeLog[] = __('audit_gateway_ok');
    }
}

// Resultados da auditoria
$results = [
    'project' => [
        'name' => $projectName,
        'path' => $root,
        'detected' => true
    ],
    'environment' => [
        'php_version' => PHP_VERSION,
        'php_ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
        'extensions' => [
            'mysqli' => extension_loaded('mysqli'),
            'curl' => extension_loaded('curl'),
            'openssl' => extension_loaded('openssl'),
            'gd' => extension_loaded('gd'),
        ],
    ],
    'permissions' => [
        'storage' => is_writable($root . '/storage'),
        'config' => is_writable($root . '/config'),
    ],
    'structure' => [
        'has_config' => is_file($root . '/config/local.php'),
    ]
];

$allOk = $results['environment']['php_ok'] &&
    $results['environment']['extensions']['mysqli'] &&
    $results['permissions']['storage'];

$prepareMsg = '';
if (isset($_POST['action']) && $_POST['action'] === 'prepare') {
    try {
        // Esta ação grava config/local.php e pode criar um banco. Sem token,
        // uma página externa conseguia disparar a preparação do ambiente.
        $engine->validateCsrfToken((string) ($_POST['_csrf'] ?? ''), true);

        if (!is_dir($root . '/storage')) mkdir($root . '/storage', 0755, true);
        if (!is_dir($root . '/config')) mkdir($root . '/config', 0755, true);

        // A montagem manual retirava apenas '/check/index.php' do SCRIPT_NAME,
        // deixando '/DropGenesis' na URL base, e depois tentava compensar
        // removendo um sufixo '/drop' que quase nunca estava lá. O
        // config/local.php nascia com APP_BASE_URL apontando para dentro da
        // pasta do instalador.
        $baseUrl = Paths::baseUrl();

        if (!is_file($root . '/config/local.php')) {
            $dbName = strtolower($projectName);
            // Cada valor passa por var_export() para virar um literal PHP
            // seguro — $baseUrl deriva de HTTP_HOST, que é controlado pelo
            // cliente; concatenar cru injetaria PHP no arquivo gerado.
            $configContent = "<?php\nreturn [\n"
                . "    'APP_BASE_URL' => " . var_export($baseUrl, true) . ",\n"
                . "    'DB_SERVER' => " . var_export('localhost', true) . ",\n"
                . "    'DB_USERNAME' => " . var_export('root', true) . ",\n"
                . "    'DB_PASSWORD' => " . var_export('', true) . ",\n"
                . "    'DB_NAME' => " . var_export($dbName, true) . ",\n"
                . "];\n";
            file_put_contents($root . '/config/local.php', $configContent);
            $prepareMsg = 'Configurações geradas.';

            // Tentar Auto-Migração (Localhost Only)
            if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
                try {
                    $conn = @new mysqli('localhost', 'root', '');
                    if (!$conn->connect_error) {
                        $conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $conn->select_db($dbName);
                        if ($conn->select_db($dbName)) {
                            // Garante a infraestrutura unificada
                            ensure_drop_infrastructure($conn);
                            $prepareMsg .= ' Banco de dados sincronizado via Master Engine.';
                        }
                        $conn->close();
                    }
                } catch (Exception $dbE) {
                    $prepareMsg .= ' (Aviso: Banco de dados não pôde ser auto-configurado: ' . $dbE->getMessage() . ')';
                }
            }
        }

        // Configuração Automática do .htaccess
        $htaccessPath = $root . '/.htaccess';
        if (!is_file($htaccessPath)) {
            file_put_contents($htaccessPath, "DirectoryIndex index.php index.html\n<IfModule mod_rewrite.c>\nRewriteEngine On\n</IfModule>");
            $prepareMsg .= ' .htaccess criado.';
        } else {
            $prepareMsg .= ' .htaccess já configurado.';
        }

        // Configuração Automática do Gateway index.php na raiz (Mantém o core principal)
        $prepareMsg .= ' Gateway index.php raiz já validado.';

        if (!$prepareMsg) $prepareMsg = 'O sistema já está configurado.';
    } catch (Exception $e) {
        $prepareMsg = 'Erro: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check & Prepare | WebyteHub</title>
    <!-- Folha local das fontes: a URL de CDN que estava aqui responde 404.
         O Font Awesome que vinha na linha seguinte foi retirado — a página não
         usa nenhuma classe `fa-`, então eram ~30 KB e uma conexão a um terceiro
         a cada carga, sem nada em troca. -->
    <link rel="icon" href="<?= htmlspecialchars(Assets::favicon(), ENT_QUOTES, 'UTF-8') ?>" sizes="any">
    <link rel="stylesheet" href="<?= htmlspecialchars(Assets::geistCss(), ENT_QUOTES, 'UTF-8') ?>">
    <style>
        :root {
            --bg: #030406;
            --surface: rgba(8, 10, 18, 0.7);
            --primary: #8b5cf6;
            --accent: #06b6d4;
            --success: #10b981;
            --text: #f8fafc;
            --border: rgba(139, 92, 246, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Geist Sans', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Cinematic Background */
        .bg-layer {
            position: fixed;
            top: -5%;
            left: -5%;
            width: 110%;
            height: 110%;
            z-index: -10;
            background-position: center;
            background-size: cover;
            transition: opacity 1.5s ease;
        }

        #bg-f {
            z-index: -11;
            background-image: <?= $bgOverlay ?>, url('<?= htmlspecialchars($defaultBg, ENT_QUOTES, 'UTF-8') ?>');
        }

        #bg-t {
            z-index: -10;
            opacity: 0;
        }

        .main-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            width: 500px;
            backdrop-filter: blur(30px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        header img {
            height: 32px;
            margin-bottom: 0.5rem;
        }

        header h1 {
            font-size: 1.25rem;
            font-weight: 800;
        }

        .log-panel {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            font-family: 'Geist Mono', monospace;
            font-size: 11px;
            height: 120px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
        }

        .log-entry {
            margin-bottom: 4px;
        }

        .log-entry[data-type="OK"] {
            color: var(--success);
        }

        .log-entry[data-type="CRITICAL"] {
            color: #ef4444;
        }

        .check-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .mini-card {
            background: rgba(255, 255, 255, 0.03);
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .mini-card h4 {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .mini-card p {
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            flex: 1;
            padding: 0.75rem;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            text-align: center;
            transition: 0.3s;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            border: 1px solid var(--border);
        }

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        .smoke-wrap {
            position: fixed;
            inset: 0;
            z-index: -5;
            pointer-events: none;
            opacity: 0;
            transition: opacity 2s ease;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
        }
        select option {
            background-color: #111827;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="bg-layer" id="bg-f"></div>
    <div class="bg-layer" id="bg-t"></div>
    <div class="smoke-wrap" id="smoke"></div>

    <div class="main-card">
        <header style="position: relative;">
            <div style="position: absolute; right: 0; top: 0;">
              <form method="GET" style="margin: 0; display: flex; align-items: center;">
                <select name="lang" onchange="this.form.submit()" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 4px 8px; font-size: 11px; cursor: pointer; outline: none;">
                  <option value="pt" <?= $engine->getTranslator()->getLocale() === 'pt' ? 'selected' : '' ?>>PT (Português)</option>
                  <option value="en" <?= $engine->getTranslator()->getLocale() === 'en' ? 'selected' : '' ?>>EN (English)</option>
                  <option value="es" <?= $engine->getTranslator()->getLocale() === 'es' ? 'selected' : '' ?>>ES (Español)</option>
                  <option value="fr" <?= $engine->getTranslator()->getLocale() === 'fr' ? 'selected' : '' ?>>FR (Français)</option>
                </select>
              </form>
            </div>
            <img src="<?= htmlspecialchars(Assets::logo(), ENT_QUOTES, 'UTF-8') ?>" alt="DropGenesis Logo">
            <h1><?= __('audit_title') ?></h1>
            <p style="font-size: 11px; color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;"><?= __('audit_desc') ?></p>
        </header>

        <div class="log-panel">
            <?php foreach ($routeLog as $log):
                $type = (strpos($log, '[OK]') !== false || strpos($log, 'OK') !== false) ? 'OK' : ((strpos($log, '[CRITICAL]') !== false || strpos($log, 'CRITICAL') !== false) ? 'CRITICAL' : 'WARN');
            ?>
                <div class="log-entry" data-type="<?= $type ?>"><?= htmlspecialchars($log) ?></div>
            <?php endforeach; ?>
            <div class="log-entry">> <?= __('scanning_directories') ?></div>
            <div class="log-entry">> <?= __('project_detected', ['project' => $projectName]) ?></div>
        </div>

        <div class="check-grid">
            <div class="mini-card">
                <h4><?= __('environment') ?></h4>
                <p><span>PHP</span> <span><?= PHP_VERSION ?></span></p>
                <p><span>MySQLi</span> <span><?= extension_loaded('mysqli') ? Icons::render('check', 13) : Icons::render('close', 13) ?></span></p>
            </div>
            <div class="mini-card">
                <h4><?= __('permissions_title') ?></h4>
                <p><span>Storage</span> <span><?= $results['permissions']['storage'] ? Icons::render('check', 13) : Icons::render('close', 13) ?></span></p>
                <p><span>Config</span> <span><?= $results['permissions']['config'] ? Icons::render('check', 13) : Icons::render('close', 13) ?></span></p>
            </div>
        </div>

        <?php if ($prepareMsg): ?>
            <p style="text-align: center; color: var(--success); font-size: 12px; margin-bottom: 1rem;"><?= $prepareMsg ?></p>
        <?php endif; ?>

        <div class="actions">
            <?php if (!$results['structure']['has_config']): ?>
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="action" value="prepare">
                    <?= $engine->csrfField() ?>
                    <button type="submit" class="btn btn-success"><?= __('auto_configure_btn') ?></button>
                </form>
            <?php endif; ?>
            <a href="../index.php" class="btn btn-primary"><?= __('btn_installer') ?></a>
            <a href="../../index.php" class="btn btn-secondary"><?= __('back') ?></a>
        </div>
    </div>

    <!-- O carrossel que estava embutido aqui trocava a imagem sem esperar o
         download, então em conexão lenta a tela piscava para o gradiente vazio
         no meio da transição; o setInterval também seguia rodando com a aba em
         segundo plano, baixando megabytes que ninguém veria. O módulo
         compartilhado pré-carrega, decodifica e só então faz a troca. -->
    <script>
        window.DROP_SCROLL = <?= json_encode(
            Assets::scrollPayload($bgOverlay) + [
                'mode'  => 'dual',
                'base'  => '#bg-f',
                'next'  => '#bg-t',
                'smoke' => '#smoke',
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) ?>;
    </script>
    <script src="<?= htmlspecialchars(Assets::scrollBgJs(), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>

</html>
