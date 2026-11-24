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
$lockFile = $root . '/storage/installed.lock';
$isInstalled = is_file($lockFile);
$checks = [];

function add_check(array &$checks, string $name, bool $ok, string $details = ''): void
{
  $checks[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

add_check($checks, 'PHP >= 8.2', version_compare(PHP_VERSION, '8.2.0', '>='), 'PHP ' . PHP_VERSION);
add_check($checks, 'Extensão mysqli', extension_loaded('mysqli'));
add_check($checks, 'Extensão curl', extension_loaded('curl'));
add_check($checks, 'Extensão openssl', extension_loaded('openssl'));
add_check($checks, 'Extensão mbstring', extension_loaded('mbstring'));
add_check($checks, 'Extensão gd (Imagens)', extension_loaded('gd'));
add_check($checks, 'Extensão bcmath (Financeiro)', extension_loaded('bcmath'));
add_check($checks, 'Extensão zip', extension_loaded('zip'), extension_loaded('zip') ? 'OK' : 'Opcional mas recomendada');
add_check($checks, 'Arquivo SQL Base', is_file($root . '/database/base.sql'), 'database/base.sql');
add_check($checks, 'Estrutura Adicional (structure.sql)', true, is_file($root . '/database/structure.sql') ? 'Encontrado' : 'Opcional (não encontrado)');
add_check($checks, 'Normalização Fase 2 (phase2.sql)', true, is_file($root . '/database/phase2.sql') ? 'Encontrado' : 'Opcional (não encontrado)');

if (!is_dir($root . '/storage'))
  @mkdir($root . '/storage', 0755, true);
if (!is_dir($root . '/config'))
  @mkdir($root . '/config', 0755, true);
add_check($checks, 'Escrita em /storage', is_writable($root . '/storage'), $root . '/storage');
add_check($checks, 'Escrita em /config', is_writable($root . '/config'), $root . '/config');

$allOk = array_reduce($checks, fn($c, $r) => $c && $r['ok'], true);

function h(string $v): string
{
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

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
  <title>Health Check – WebyteHub</title>
  <meta name="robots" content="noindex,nofollow">
  <!-- A URL de CDN que estava aqui responde 404: o pacote npm `geist` não
       publica esse arquivo. Toda a tipografia caía no sans-serif do sistema, e
       ainda assim uma requisição externa era feita e esperada a cada carga. O
       arquivo local já existia em src/Css/ e não estava ligado a lugar nenhum. -->
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

    .req-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .req-table th,
    .req-table td {
      padding: 12px 10px;
      text-align: left;
      font-size: 13px;
      border-bottom: 1px solid var(--border);
    }

    .req-table th {
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
          <h1><?= __('health_check_title') ?></h1>
          <p><?= __('health_check_meta_desc') ?></p>
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
          <div class="sec-icon"><?= Icons::render('search', 16) ?></div> <?= __('system_reqs') ?>
        </div>

        <?php if ($isInstalled): ?>
          <div class="alert alert-info">
            <span><?= Icons::render('info', 17) ?></span>
            <div><?= __('installed_security_warn', ['link' => '<a href="post-check.php" style="color:#a5b4fc">post-check.php</a>', 'dir' => '<code>' . h('/' . Paths::installerName()) . '</code>']) ?></div>
          </div>
        <?php endif; ?>

        <div class="alert <?= $allOk ? 'alert-success' : 'alert-danger' ?>">
          <span><?= $allOk ? Icons::render('check-circle', 17) : Icons::render('x-circle', 17) ?></span>
          <div>
            <?= $allOk ? __('ready_to_install') : __('blocks_found') ?>
          </div>
        </div>


        <table class="req-table">
          <thead>
            <tr>
              <th><?= __('requirements') ?></th>
              <th>Status</th>
              <th><?= __('details') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($checks as $c):
              // Translate check name
              $name = $c['name'];
              if ($name === 'Arquivo SQL Base') {
                  $name = __('sql_base_file');
              } elseif ($name === 'Estrutura Adicional (structure.sql)') {
                  $name = __('additional_structure_file');
              } elseif ($name === 'Normalização Fase 2 (phase2.sql)') {
                  $name = __('phase2_normalization_file');
              } elseif (strpos($name, 'Extensão') !== false) {
                  $name = str_replace('Extensão', __('extension'), $name);
              } elseif (strpos($name, 'Escrita em') !== false) {
                  $name = str_replace('Escrita em', __('write_permission'), $name);
              }

              // Translate check details
              $details = $c['details'];
              if ($details === 'Opcional mas recomendada') {
                  $details = __('optional_but_recommended');
              } elseif ($details === 'Encontrado') {
                  $details = __('found');
              } elseif ($details === 'Opcional (não encontrado)') {
                  $details = __('optional_not_found');
              }
            ?>
              <tr>
                <td><?= h($name) ?></td>
                <td class="<?= $c['ok'] ? 'ok' : 'fail' ?>"><?= $c['ok'] ? Icons::render('check', 14) . ' OK' : Icons::render('close', 14) . ' ' . __('fail') ?></td>
                <td style="font-size:12px;color:var(--muted)"><?= h($details) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="btn-row">
          <a class="btn btn-primary" href="index.php"><?= Icons::render('arrow-left', 15) ?> <?= __('btn_back_installer') ?></a>
          <a class="btn btn-ghost" href="post-check.php"><?= __('btn_post_diagnostics') ?></a>
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
