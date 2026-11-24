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
use DropGenesis\Core\Paths;

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

Paths::boot(__FILE__);

/**
 * Endereço pedido, apenas para exibição.
 *
 * Vem do servidor mas reflete o que o visitante digitou: é escapado na saída e
 * truncado aqui para que uma URI muito longa não estoure o layout.
 */
$requestPath = (string) ($_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?? '/');
$requestPath = strtok(str_replace('\\', '/', $requestPath), '?');
$requestPath = ($requestPath === false || $requestPath === '') ? '/' : $requestPath;
if (strlen($requestPath) > 120) {
    $requestPath = substr($requestPath, 0, 117) . '…';
}

$homeUrl   = Paths::baseUrl();
$bgOverlay = Assets::OVERLAY_DEEP;

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Página não encontrada — WebyteHub</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="icon" href="<?= htmlspecialchars(Assets::favicon(), ENT_QUOTES, 'UTF-8') ?>" sizes="any">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: #030406;
      --surface: rgba(255, 255, 255, .02);
      --surface2: rgba(255, 255, 255, .05);
      --border: rgba(139, 92, 246, .15);
      --text: #f8fafc;
      --muted: #94a3b8;
      --primary: #8b5cf6;
      --primary-h: #7c3aed;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: var(--bg);
      color: var(--text);
      font-size: 14px;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
    }

<?= Assets::backgroundCss($bgOverlay) ?>

    .bg-glow {
      position: fixed;
      inset: -30%;
      background: radial-gradient(circle at 20% 25%, rgba(139, 92, 246, .13), transparent 55%),
        radial-gradient(circle at 80% 75%, rgba(6, 182, 212, .09), transparent 55%);
      filter: blur(100px);
      z-index: -9;
      pointer-events: none;
    }

    .card {
      max-width: 520px;
      width: 100%;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 20px;
      backdrop-filter: blur(18px);
      box-shadow: 0 24px 64px rgba(0, 0, 0, .55);
      overflow: hidden;
      animation: rise .5s ease both;
    }

    @keyframes rise {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: none; }
    }

    .card-head {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 18px 26px;
      border-bottom: 1px solid var(--border);
      background: var(--surface2);
    }

    .card-head img { height: 30px; width: auto; display: block; }

    .card-body { padding: 30px 26px 26px; }

    .code {
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: .16em;
      color: #a78bfa;
      text-transform: uppercase;
    }

    h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -.02em;
      margin: 6px 0 10px;
    }

    p { color: var(--muted); margin-bottom: 18px; }

    .path {
      display: block;
      font-family: ui-monospace, Menlo, Consolas, monospace;
      font-size: 12.5px;
      color: #cbd5e1;
      background: rgba(255, 255, 255, .05);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 10px 13px;
      margin-bottom: 22px;
      overflow-wrap: anywhere;
    }

    .actions { display: flex; gap: 10px; flex-wrap: wrap; }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 11px 20px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 600;
      text-decoration: none;
      border: 1px solid transparent;
      cursor: pointer;
      transition: background .2s, border-color .2s, transform .2s;
    }

    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-h); transform: translateY(-1px); }

    .btn-ghost { background: var(--surface2); border-color: var(--border); color: var(--text); }
    .btn-ghost:hover { border-color: rgba(139, 92, 246, .45); transform: translateY(-1px); }

    @media (max-width: 480px) {
      .card-head, .card-body { padding-left: 18px; padding-right: 18px; }
      .btn { width: 100%; }
    }
  </style>
</head>

<body>
  <div id="db-master-bg"></div>
  <div class="bg-glow"></div>

  <div class="card">
    <div class="card-head">
      <?php if (Assets::logoExists()): ?>
        <img src="<?= htmlspecialchars(Assets::logo(), ENT_QUOTES, 'UTF-8') ?>" alt="WebyteHub">
      <?php endif; ?>
    </div>

    <div class="card-body">
      <div class="code">Erro 404</div>
      <h1>Página não encontrada</h1>
      <p>O endereço solicitado não existe neste servidor. Confira o link ou volte para a página inicial.</p>

      <code class="path"><?= htmlspecialchars($requestPath, ENT_QUOTES, 'UTF-8') ?></code>

      <div class="actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>">Ir para a página inicial</a>
        <button class="btn btn-ghost" type="button" onclick="history.back()">Voltar</button>
      </div>
    </div>
  </div>

  <script>
    window.DROP_SCROLL = <?= json_encode(
      Assets::scrollPayload($bgOverlay) + ['mode' => 'single', 'base' => '#db-master-bg'],
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) ?>;
  </script>
  <script src="<?= htmlspecialchars(Assets::scrollBgJs(), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>

</html>
