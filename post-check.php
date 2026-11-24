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
use DropGenesis\Core\Env;
use DropGenesis\Core\Icons;
use DropGenesis\Core\Paths;

// Registra autoloader PSR-4 dinâmico para evitar requires manuais
spl_autoload_register(function (string $class) {
    $prefix = 'DropGenesis\\';
    $baseDir = __DIR__ . '/src/';

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

$engine = new \DropGenesis\Core\Engine(dirname(__DIR__), __FILE__);

function __($key, $replace = []) {
    global $engine;
    return $engine->getTranslator()->translate($key, $replace);
}

header('Content-Type: text/html; charset=utf-8');

$root = Paths::appRoot();
$lockFile = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'installed.lock';
$configFile = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'local.php';
$rootHtaccess = $root . DIRECTORY_SEPARATOR . '.htaccess';
$installHtacc = __DIR__ . DIRECTORY_SEPARATOR . '.htaccess';
$logFile = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'php-error.log';

function h(string $v): string
{
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

/**
 * Mantida como fachada da detecção compartilhada.
 *
 * A regex anterior removia `/(drop/)?DropGenesis/post-check.php` do
 * SCRIPT_NAME — com os nomes de pasta escritos à mão, e diferentes dos usados
 * nos outros arquivos. O diagnóstico pós-instalação, cujo trabalho é
 * justamente conferir se as rotas ficaram certas, calculava a URL base por uma
 * quarta regra distinta das outras três.
 */
function detect_base_url(bool $isHttps): string
{
  return Paths::baseUrl();
}

function read_local_config(string $path): array
{
  if (!is_file($path))
    return [];
  $loaded = require $path;
  return is_array($loaded) ? $loaded : [];
}

function add_check(array &$checks, string $name, bool $ok, string $details = '', string $fix = ''): void
{
  $checks[] = ['name' => $name, 'ok' => $ok, 'details' => $details, 'fix' => $fix];
}

$checks = [];
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
  || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
  || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$detectedBaseUrl = detect_base_url($isHttps);
$cfg = read_local_config($configFile);

add_check(
  $checks,
  __('check_lock_exists'),
  is_file($lockFile),
  is_file($lockFile) ? 'OK' : __('not_found'),
  __('fix_installed_lock')
);

add_check(
  $checks,
  __('check_config_exists'),
  is_file($configFile),
  is_file($configFile) ? 'OK' : __('not_found'),
  __('fix_config_local')
);

add_check(
  $checks,
  __('check_storage_write'),
  is_dir($root . '/storage') && is_writable($root . '/storage'),
  $root . '/storage',
  __('fix_write_storage')
);

add_check(
  $checks,
  __('check_logs_write'),
  is_dir($root . '/storage/logs') ? is_writable($root . '/storage/logs') : (bool) @mkdir($root . '/storage/logs', 0755, true),
  $root . '/storage/logs',
  __('fix_write_logs')
);

add_check($checks, __('check_root_htaccess'), is_file($rootHtaccess), $rootHtaccess, __('fix_root_htaccess'));
add_check($checks, __('check_drop_htaccess'), is_file($installHtacc), $installHtacc, __('fix_drop_htaccess'));

$apacheRewrite = null;
if (function_exists('apache_get_modules')) {
  $mods = apache_get_modules();
  $apacheRewrite = is_array($mods) ? in_array('mod_rewrite', $mods, true) : null;
}
add_check(
  $checks,
  __('check_mod_rewrite'),
  $apacheRewrite === null ? true : $apacheRewrite,
  $apacheRewrite === null
    ? __('detect_warn_htaccess')
    : ($apacheRewrite ? __('active') : __('disabled')),
  __('fix_mod_rewrite')
);

$baseUrlSaved = (string) ($cfg['APP_BASE_URL'] ?? '');
if ($baseUrlSaved !== '') {
  $okBase = rtrim($baseUrlSaved, '/') . '/' === $detectedBaseUrl;
  add_check(
    $checks,
    __('check_base_url_consist'),
    $okBase,
    __('detect_base_url_details', ['det' => $detectedBaseUrl, 'conf' => rtrim($baseUrlSaved, '/') . '/']),
    __('fix_base_url')
  );
}

/* DB checks */
$dbOk = false;
$tablesOk = false;
$dbDetails = '';
$tablesDetails = '';
$requiredTables = ['system_info', 'users', 'product_list', 'order_list', 'customer_list', 'order_number_list'];
$orderNumberUniqueOk = false;
$orderNumberUniqueDetails = '';
$orderNumberStatusOk = false;
$orderNumberStatusDetails = '';

$dbHost = (string) ($cfg['DB_SERVER'] ?? Env::get('DB_SERVER'));
$dbUser = (string) ($cfg['DB_USERNAME'] ?? Env::get('DB_USERNAME'));
$dbPass = (string) ($cfg['DB_PASSWORD'] ?? Env::get('DB_PASSWORD'));
$dbName = (string) ($cfg['DB_NAME'] ?? Env::get('DB_NAME'));

if ($dbHost && $dbUser && $dbName) {
  // Mesma rotina de conexão da instalação, para que o diagnóstico enxergue
  // exatamente o que a aplicação enxerga — inclusive host com porta e socket
  // unix, que a chamada direta ao mysqli tratava de forma diferente.
  $conn = null;
  try {
    $conn = \DropGenesis\Core\Database::connect($dbHost, $dbUser, $dbPass, $dbName);
    $dbOk = true;
  } catch (Throwable $e) {
    $dbDetails = __('db_conn_failed', ['err' => $e->getMessage()]);
  }

  if ($conn !== null) {
    try {
      $missing = [];
      foreach ($requiredTables as $t) {
        $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'");
        if (!$res || $res->num_rows === 0)
          $missing[] = $t;
      }
      $tablesOk = count($missing) === 0;
      $tablesDetails = $tablesOk ? __('key_tables_ok') : __('key_tables_missing', ['missing' => implode(', ', $missing)]);

      if ($tablesOk) {
        $idxRes = $conn->query("SHOW INDEX FROM `order_number_list` WHERE Key_name = 'uniq_product_lucky_number'");
        if ($idxRes && $idxRes->num_rows >= 2) {
          $hasProduct = false;
          $hasLucky = false;
          while ($idxRow = $idxRes->fetch_assoc()) {
            $col = (string)($idxRow['Column_name'] ?? '');
            if ($col === 'product_id')
              $hasProduct = true;
            if ($col === 'lucky_number')
              $hasLucky = true;
          }
          $orderNumberUniqueOk = $hasProduct && $hasLucky;
        }
        $orderNumberUniqueDetails = $orderNumberUniqueOk
          ? __('quota_uniqueness_ok')
          : __('quota_uniqueness_missing');

        // Valida coluna status (o PHP e sincronização dependem disso)
        $colRes = $conn->query("SHOW COLUMNS FROM `order_number_list` LIKE 'status'");
        if ($colRes && $colRes->num_rows >= 1) {
          $orderNumberStatusOk = true;
          $orderNumberStatusDetails = __('status_column_ok');
        } else {
          $orderNumberStatusOk = false;
          $orderNumberStatusDetails = __('status_column_missing');
        }
      }
    } catch (Throwable $e) {
      $dbDetails = __('db_test_error', ['err' => $e->getMessage()]);
    } finally {
      $conn->close();
    }
  }
} else {
  $dbDetails = __('db_credentials_missing');
}

add_check(
  $checks,
  __('check_db_conn'),
  $dbOk,
  $dbOk ? 'OK' : $dbDetails,
  __('fix_db_conn')
);

add_check(
  $checks,
  __('check_key_tables'),
  $dbOk ? $tablesOk : false,
  $dbOk ? $tablesDetails : __('db_not_connected'),
  __('fix_key_tables')
);

add_check(
  $checks,
  __('check_quota_uniqueness'),
  ($dbOk && $tablesOk) ? $orderNumberUniqueOk : false,
  ($dbOk && $tablesOk) ? $orderNumberUniqueDetails : __('dep_conn_tables'),
  __('fix_quota_uniqueness')
);

add_check(
  $checks,
  __('check_status_column'),
  ($dbOk && $tablesOk) ? $orderNumberStatusOk : false,
  ($dbOk && $tablesOk) ? $orderNumberStatusDetails : __('dep_conn_tables'),
  __('fix_status_column')
);

$allOk = array_reduce($checks, fn($c, $r) => $c && $r['ok'], true);

$logoRelPath = Assets::logo();
$logoExists = Assets::logoExists();

// Fundo em scroll — rotas absolutas e em ordem natural, resolvidas por Assets.
$scrollImages = Assets::scrollImages();
$defaultBg = Assets::firstScrollImage();
$bgOverlay = Assets::OVERLAY;
?>
<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Diagnóstico Pós-Instalação – WebyteHub</title>
  <meta name="robots" content="noindex,nofollow">
  <!-- Folha local: a URL de CDN anterior responde 404 (o pacote npm `geist`
       não publica esse arquivo), então nenhuma fonte carregava. -->
  <link rel="icon" href="<?= h(Assets::favicon()) ?>" sizes="any">
  <link rel="stylesheet" href="<?= h(Assets::geistCss()) ?>">
  <style>
    :root {
      --primary: #8b5cf6;
      --accent: #06b6d4;
      --bg: #030406;
      --card: rgba(255, 255, 255, 0.02);
      --border: rgba(139, 92, 246, 0.15);
      --border2: rgba(139, 92, 246, 0.25);
      --text: #f8fafc;
      --muted: #64748b;
      --success: #10b981;
      --danger: #ef4444;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: var(--bg);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--border);
      border-radius: 10px;
      transition: 0.3s;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--primary);
    }

    body {
      font-family: 'Geist Sans', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      font-size: 14px;
      min-height: 100vh;
      margin: 0;
      position: relative;
    }

    /* --- MASTER BACKGROUND (ARTISTIC) --- */
    /* Duas camadas com geometria e filtro idênticos: a de cima recebe a
       próxima imagem do scroll e sobe a opacidade. */
<?= Assets::backgroundCss($bgOverlay) ?>

    .bgGlow {
      position: fixed;
      inset: -50%;
      background: radial-gradient(circle at 50% 50%, rgba(139, 92, 246, 0.1), transparent 70%);
      z-index: -9;
      filter: blur(120px);
    }

    .bgHappiness {
      position: fixed;
      bottom: -20%;
      right: -20%;
      width: 60%;
      height: 60%;
      background: radial-gradient(circle, rgba(6, 182, 212, 0.03), transparent 70%);
      z-index: -8;
      filter: blur(100px);
      animation: breatheJoy 8s infinite alternate;
    }

    @keyframes breatheJoy {
      from {
        opacity: 0.2;
        transform: scale(1);
      }

      to {
        opacity: 0.5;
        transform: scale(1.1);
      }
    }

    #particleCanvas {
      position: fixed;
      inset: 0;
      z-index: -7;
      opacity: 0.15;
      pointer-events: none;
    }

    #golden-ratio {
      position: fixed;
      inset: 0;
      pointer-events: none;
      z-index: -1;
      opacity: 0.05;
      background-image: repeating-linear-gradient(rgba(251, 191, 36, 0.1) 0 1px, transparent 1px 61.8%),
        repeating-linear-gradient(90deg, rgba(251, 191, 36, 0.1) 0 1px, transparent 1px 61.8%);
    }

    .page {
      position: relative;
      z-index: 10;
      width: 95%;
      max-width: 900px;
      margin: 0 auto;
      padding: 40px 16px 80px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .card {
      width: 100%;
      background: var(--card);
      backdrop-filter: blur(20px);
      border: 1px solid var(--border);
      border-radius: 20px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      display: flex;
      flex-direction: column;
      animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUp {
      from {
        transform: translateY(30px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .card-header {
      display: flex;
      align-items: center;
      gap: 18px;
      padding: 20px 25px;
      border-bottom: 1px solid var(--border);
      background: rgba(255, 255, 255, 0.01);
    }

    .logo-wrap img {
      height: 40px;
      width: auto;
      object-fit: contain;
      display: block;
    }

    .logo-fallback {
      font-size: 18px;
      font-weight: 800;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .header-meta h1 {
      font-size: 18px;
      font-weight: 800;
      letter-spacing: -0.5px;
    }

    .header-meta p {
      font-size: 10px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
      margin-top: 2px;
    }

    .card-body {
      padding: 25px;
    }

    .sec-title {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid var(--border);
    }

    .sec-icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      background: rgba(139, 92, 246, 0.1);
      border: 1px solid rgba(139, 92, 246, 0.2);
    }

    .alert {
      padding: 15px;
      border-radius: 12px;
      font-size: 13px;
      line-height: 1.5;
      margin-bottom: 16px;
      display: flex;
      gap: 8px;
      align-items: flex-start;
    }

    .alert-success {
      background: rgba(16, 185, 129, 0.05);
      border: 1px solid rgba(16, 185, 129, 0.2);
      color: #34d399;
    }

    .alert-danger {
      background: rgba(239, 68, 68, 0.05);
      border: 1px solid rgba(239, 68, 68, 0.2);
      color: #f87171;
    }

    .alert-info {
      background: rgba(139, 92, 246, 0.05);
      border: 1px solid rgba(139, 92, 246, 0.2);
      color: #c084fc;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 18px;
    }

    .info-item {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 12px 16px;
    }

    .info-item label {
      font-size: 10px;
      font-weight: 800;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .04em;
      display: block;
      margin-bottom: 4px;
    }

    .info-item code {
      font-size: 12px;
      word-break: break-all;
      background: transparent;
      border: none;
      padding: 0;
    }

    .check-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .check-table th,
    .check-table td {
      padding: 12px 10px;
      text-align: left;
      font-size: 13px;
      border-bottom: 1px solid var(--border);
      vertical-align: top;
    }

    .check-table th {
      font-size: 11px;
      font-weight: 800;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .ok {
      color: var(--success);
      font-weight: 700;
    }

    .fail {
      color: var(--danger);
      font-weight: 700;
    }

    code {
      font-family: monospace;
      background: rgba(255, 255, 255, .04);
      border: 1px solid var(--border);
      padding: 2px 6px;
      border-radius: 5px;
      font-size: 12px;
    }

    a {
      color: #a5b4fc;
    }

    .recs {
      background: rgba(255, 255, 255, 0.01);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 16px;
      margin-top: 20px;
    }

    .recs h3 {
      font-size: 13px;
      font-weight: 800;
      margin-bottom: 10px;
      color: var(--text);
    }

    .recs ul {
      padding-left: 20px;
    }

    .recs li {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 6px;
      line-height: 1.5;
    }

    .btn-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 20px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 14px 28px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 800;
      border: none;
      cursor: pointer;
      font-family: inherit;
      text-decoration: none;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      transition: 0.3s cubic-bezier(0.19, 1, 0.22, 1);
    }

    .btn:hover {
      transform: translateY(-2px);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--accent));
      color: #fff;
      box-shadow: 0 10px 20px rgba(139, 92, 246, 0.15);
    }

    .btn-primary:hover {
      box-shadow: 0 15px 30px rgba(139, 92, 246, 0.3);
    }

    .btn-ghost {
      background: rgba(255, 255, 255, 0.03);
      color: var(--text);
      border: 1px solid var(--border);
    }

    .btn-ghost:hover {
      background: rgba(255, 255, 255, 0.06);
      border-color: var(--border2);
    }

    @media(max-width:600px) {
      .info-grid {
        grid-template-columns: 1fr;
      }

      .card-body {
        padding: 16px;
      }
    }
    select option {
      background-color: #111827;
      color: #fff;
    }
  </style>
</head>

<body>
  <div id="db-master-bg"></div>
  <div class="bgGlow"></div>
  <div class="bgHappiness"></div>
  <div id="golden-ratio"></div>
  <canvas id="particleCanvas"></canvas>

  <div class="page">
    <div class="card">
      <div class="card-header" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <div class="logo-wrap">
          <?php if ($logoExists): ?>
            <img src="<?= h($logoRelPath) ?>" alt="WebyteHub Logo">
          <?php else: ?>
            <span class="logo-fallback">WebyteHub</span>
          <?php endif; ?>
        </div>
        <div class="header-meta">
          <h1><?= __('post_check_title') ?></h1>
          <p><?= __('post_check_desc') ?></p>
        </div>
        <div style="margin-left: auto; display: flex; align-items: center; gap: 8px;">
          <form method="GET" style="margin: 0; display: flex; align-items: center;">
            <select name="lang" onchange="this.form.submit()" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-size: 13px; cursor: pointer; outline: none;">
              <option value="pt" <?= $engine->getTranslator()->getLocale() === 'pt' ? 'selected' : '' ?>>PT (Português)</option>
              <option value="en" <?= $engine->getTranslator()->getLocale() === 'en' ? 'selected' : '' ?>>EN (English)</option>
              <option value="es" <?= $engine->getTranslator()->getLocale() === 'es' ? 'selected' : '' ?>>ES (Español)</option>
              <option value="fr" <?= $engine->getTranslator()->getLocale() === 'fr' ? 'selected' : '' ?>>FR (Français)</option>
            </select>
          </form>
        </div>
      </div>

      <div class="card-body">
        <div class="sec-title">
          <div class="sec-icon"><?= Icons::render('activity', 16) ?></div> <?= __('env_status') ?>
        </div>

        <div class="alert <?= $allOk ? 'alert-success' : 'alert-danger' ?>">
          <span><?= $allOk ? Icons::render('check-circle', 17) : Icons::render('alert', 17) ?></span>
          <div>
            <?= $allOk ? __('env_validated') : __('attention_points') ?>
          </div>
        </div>

        <!-- Info cards -->
        <div class="info-grid">
          <div class="info-item">
            <label><?= __('url_detected') ?></label>
            <code><?= h($detectedBaseUrl) ?></code>
          </div>
          <div class="info-item">
            <label><?= __('url_configured') ?></label>
            <code><?= $baseUrlSaved !== '' ? h($baseUrlSaved) : '—' ?></code>
          </div>
          <div class="info-item">
            <label><?= __('admin_access') ?></label>
            <code><?= h(parse_url($detectedBaseUrl, PHP_URL_PATH) ?: '/') ?>api/login.php</code>
          </div>
          <div class="info-item">
            <label><?= __('php_error_log') ?></label>
            <code><?= h($logFile) ?></code>
          </div>
        </div>

        <table class="check-table">
          <thead>
            <tr>
              <th><?= __('item') ?></th>
              <th>Status</th>
              <th><?= __('details') ?></th>
              <th><?= __('how_to_fix') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($checks as $c): ?>
              <tr>
                <td><?= h($c['name']) ?></td>
                <td class="<?= $c['ok'] ? 'ok' : 'fail' ?>"><?= $c['ok'] ? Icons::render('check', 14) . ' OK' : Icons::render('close', 14) . ' ' . __('fail') ?></td>
                <td style="font-size:12px;color:var(--muted)"><?= h($c['details']) ?></td>
                <td style="font-size:12px;color:var(--muted)"><?= h($c['fix']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="recs">
          <h3><?= Icons::render('clipboard', 16) ?> <?= __('final_recommendations') ?></h3>
          <ul>
            <li><?= __('rec_remove_folder') ?></li>
            <li><?= __('rec_https') ?></li>
            <li><?= __('rec_payment_errors') ?></li>
            <li><?= __('rec_backup') ?></li>
            <li><?= __('rec_vps_perms') ?></li>
          </ul>
        </div>

        <div class="btn-row">
          <a class="btn btn-primary" href="index.php"><?= Icons::render('arrow-left', 15) ?> <?= __('btn_back_installer') ?></a>
          <a class="btn btn-ghost" href="health-check.php"><?= __('health_check') ?></a>
        </div>
      </div>
    </div>

    <p style="text-align:center;margin-top:16px;font-size:11px;color:var(--muted)">
      <?= __('installer_security_remove_note', ['path' => '<code>/DropGenesis</code>']) ?>
    </p>
  </div>

  <!-- Partículas Dinâmicas -->
  <script>
    (function() {
      const canvas = document.getElementById('particleCanvas');
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      let pts = [];

      function resz() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
      }
      window.addEventListener('resize', resz);
      resz();
      class Pt {
        constructor() {
          this.x = Math.random() * canvas.width;
          this.y = Math.random() * canvas.height;
          this.s = Math.random() * 1.5;
          this.vx = Math.random() * 0.2 - 0.1;
          this.vy = Math.random() * 0.2 - 0.1;
        }
        upd() {
          this.x += this.vx;
          this.y += this.vy;
          if (this.x > canvas.width) this.x = 0;
          if (this.y > canvas.height) this.y = 0;
        }
        drw() {
          ctx.fillStyle = 'rgba(251,191,36,0.2)';
          ctx.beginPath();
          ctx.arc(this.x, this.y, this.s, 0, Math.PI * 2);
          ctx.fill();
        }
      }
      for (let i = 0; i < 40; i++) pts.push(new Pt());

      function anim() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pts.forEach(p => {
          p.upd();
          p.drw();
        });
        requestAnimationFrame(anim);
      }
      anim();
    })();
  </script>

  <script>
    window.DROP_SCROLL = <?= json_encode(
      Assets::scrollPayload($bgOverlay) + ['mode' => 'single', 'base' => '#db-master-bg'],
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) ?>;
  </script>
  <script src="<?= h(Assets::scrollBgJs()) ?>" defer></script>
</body>

</html>
