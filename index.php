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
use DropGenesis\Core\Database;
use DropGenesis\Core\Engine;
use DropGenesis\Core\Env;
use DropGenesis\Core\Icons;
use DropGenesis\Core\InstallState;
use DropGenesis\Core\Paths;

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

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

$engine = new Engine(dirname(__DIR__), __FILE__);

/**
 * Sincronização de esquema do ecossistema DROP.
 *
 * O require era incondicional. Numa instalação autônoma do motor — cenário que
 * o próprio gateway da raiz detecta e trata — o arquivo não existe e o
 * instalador morria com fatal error antes de desenhar um pixel.
 */
$infrastructureLib = Paths::appFile('api/libs/infrastructure.php');
if (is_file($infrastructureLib)) {
    require_once $infrastructureLib;
}

$root = Paths::appRoot();
$storageDir = $root . '/storage';
$lockFile = $storageDir . '/installed.lock';
$configDir = $root . '/config';
$configFile = $configDir . '/local.php';
$sqlFile = $root . '/' . ltrim((string) ($engine->getManifest()->getDatabaseConfig()['sql_file'] ?? 'database/base.sql'), '/');

function __($key, $replace = []) {
    global $engine;
    return $engine->getTranslator()->translate($key, $replace);
}

if (!is_dir($storageDir)) {
  @mkdir($storageDir, 0755, true);
}
if (!is_dir($configDir)) {
  @mkdir($configDir, 0755, true);
}

// Considera a trava de QUALQUER um dos dois instaladores. Antes olhava só a
// própria: instalando pelo /api/install.php, este assistente continuava
// aberto ao público sobre um sistema já instalado.
$alreadyInstalled = InstallState::isInstalled();
$allowAfterInstall = (isset($_GET['allow']) && $_GET['allow'] === '1');

if ($alreadyInstalled && !$allowAfterInstall) {
  http_response_code(403);
  header('Content-Type: text/html; charset=utf-8');
  // Os links eram fixos em "/DropGenesis/…" e "/admin/login.php" a partir da
  // raiz do domínio: em subpasta apontavam todos para lugar nenhum.
  $installerPath = Paths::installerPath();
  $base = Paths::baseUrl();
  $lockedOverlay = Assets::OVERLAY_DEEP;
  $lockLinks = [
    ['label' => __('post_check_diagnostics'), 'href' => $installerPath . 'post-check.php', 'shown' => $installerPath . 'post-check.php'],
    ['label' => __('health_check'),           'href' => $installerPath . 'health-check.php', 'shown' => $installerPath . 'health-check.php'],
    ['label' => __('admin_panel'),            'href' => $base . 'api/login.php', 'shown' => Paths::basePath() . '/api/login.php'],
  ];

  // Arquivos que REALMENTE existem nesta instalação. O texto fixo anterior
  // mandava remover storage/installed.lock e config/local.php mesmo quando a
  // instalação tinha sido feita pelo instalador da API — que grava em api/ e
  // não cria nenhum dos dois.
  $lockArtifacts = InstallState::present();
  $lockInstallers = InstallState::installers();
  ?>
<!doctype html>
<html lang="<?= h($engine->getTranslator()->getLocale()) ?>">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h(__('locked_title')) ?> — WebyteHub</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="icon" href="<?= h(Assets::favicon()) ?>" sizes="any">
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
      --primary-h: #a78bfa;
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

    <?= Assets::backgroundCss($lockedOverlay) ?>

    .bg-glow {
      position: fixed;
      inset: -30%;
      background: radial-gradient(circle at 15% 20%, rgba(139, 92, 246, .14), transparent 55%),
        radial-gradient(circle at 85% 80%, rgba(6, 182, 212, .10), transparent 55%);
      filter: blur(100px);
      z-index: -9;
      pointer-events: none;
    }

    .card {
      max-width: 540px;
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
      gap: 14px;
      padding: 20px 26px;
      border-bottom: 1px solid var(--border);
      background: var(--surface2);
    }

    .card-head img { height: 34px; width: auto; display: block; }

    .card-head h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 17px;
      font-weight: 800;
      letter-spacing: -.02em;
      line-height: 1.25;
    }

    .card-head p { font-size: 12px; color: var(--muted); margin-top: 2px; }

    .card-body { padding: 24px 26px 26px; }

    .links { list-style: none; display: grid; gap: 8px; margin: 18px 0 20px; }

    .links a {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 11px 14px;
      border: 1px solid var(--border);
      border-radius: 10px;
      background: var(--surface2);
      color: var(--text);
      text-decoration: none;
      font-weight: 500;
      transition: border-color .2s, transform .2s, background .2s;
    }

    .links a:hover {
      border-color: rgba(139, 92, 246, .45);
      background: rgba(139, 92, 246, .08);
      transform: translateX(2px);
    }

    .links span { font-size: 12px; color: var(--muted); font-family: ui-monospace, Menlo, Consolas, monospace; }

    .note {
      font-size: 12px;
      color: var(--muted);
      border-top: 1px solid var(--border);
      padding-top: 16px;
    }

    .note strong {
      display: block;
      color: var(--text);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .note p { margin-bottom: 10px; }

    .files { list-style: none; display: grid; gap: 6px; margin-bottom: 12px; }

    .files li {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      background: rgba(255, 255, 255, .04);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 7px 11px;
    }

    .files .tag {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: #a78bfa;
      white-space: nowrap;
    }

    .hint { font-size: 11.5px; opacity: .85; margin-bottom: 0 !important; }

    code {
      font-family: ui-monospace, Menlo, Consolas, monospace;
      background: rgba(255, 255, 255, .07);
      padding: 2px 6px;
      border-radius: 5px;
      font-size: 12px;
      color: #cbd5e1;
    }

    @media (max-width: 480px) {
      .card-head, .card-body { padding-left: 18px; padding-right: 18px; }
      .links a { flex-direction: column; align-items: flex-start; gap: 4px; }
    }
  </style>
</head>

<body>
  <div id="db-master-bg"></div>
  <div class="bg-glow"></div>

  <div class="card">
    <div class="card-head">
      <?php if (Assets::logoExists()): ?>
        <img src="<?= h(Assets::logo()) ?>" alt="WebyteHub">
      <?php endif; ?>
      <div>
        <h1><?= h(__('locked_title')) ?></h1>
        <p><?= h(__('locked_desc')) ?></p>
      </div>
    </div>

    <div class="card-body">
      <ul class="links">
        <?php foreach ($lockLinks as $link): ?>
          <li>
            <a href="<?= h($link['href']) ?>">
              <?= h($link['label']) ?>
              <span><?= h($link['shown']) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="note">
        <strong><?= h(__('reinstall_title')) ?></strong>
        <p><?= h(__('reinstall_intro')) ?></p>

        <?php if ($lockArtifacts !== []): ?>
          <ul class="files">
            <?php foreach ($lockArtifacts as $artifact): ?>
              <li>
                <code><?= h($artifact['path']) ?></code>
                <span class="tag"><?= h($artifact['owner']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <p class="hint">
            <?= h(__('reinstall_db_warning')) ?>
          </p>
        <?php else: ?>
          <p class="hint"><?= h(__('reinstall_none_found')) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    window.DROP_SCROLL = <?= json_encode(
      Assets::scrollPayload($lockedOverlay) + ['mode' => 'single', 'base' => '#db-master-bg'],
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) ?>;
  </script>
  <script src="<?= h(Assets::scrollBgJs()) ?>" defer></script>
</body>

</html>
  <?php
  exit;
}

/* ── helpers ─────────────────────────────────────────────────────── */
function h(string $v): string
{
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function app_url_parts(string $url): array
{
  $url = trim($url);
  if ($url === '')
    return ['', ''];
  $url = rtrim($url, '/');
  return [$url . '/', $url];
}

/**
 * Cria (ou atualiza) o administrador principal.
 *
 * A versão anterior desistia em silêncio quando `api_admins` não existia:
 * `return` sem aviso. O instalador então declarava sucesso e o operador
 * descobria o problema só na tela de login, sem credencial nenhuma para
 * entrar. Se a tabela não está lá, o SQL base não foi aplicado — isso é uma
 * falha de instalação e precisa interromper.
 */
/** Quantos valores de api_settings estão cifrados (prefixo enc:v1:, ver api/libs/crypto.php). */
function segredos_cifrados(mysqli $conn): int
{
  if (!Database::tableExists($conn, 'api_settings')) {
    return 0;
  }
  $res = $conn->query("SELECT COUNT(*) FROM api_settings WHERE setting_value LIKE 'enc:v1:%'");
  return $res ? (int) ($res->fetch_row()[0] ?? 0) : 0;
}

/** Quantos administradores o banco já tem. */
function admins_existentes(mysqli $conn): int
{
  $res = $conn->query('SELECT COUNT(*) FROM api_admins');
  return $res ? (int) ($res->fetch_row()[0] ?? 0) : 0;
}

/** Usuário e senha conferem com um administrador que já existe no banco? */
function admin_existente_confere(mysqli $conn, string $username, string $password): bool
{
  $stmt = $conn->prepare('SELECT password FROM api_admins WHERE username = ? LIMIT 1');
  if (!$stmt) {
    return false;
  }
  $username = trim($username);
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res ? $res->fetch_assoc() : null;
  $stmt->close();

  return is_array($row) && is_string($row['password'] ?? null) && password_verify($password, $row['password']);
}

function ensure_admin(mysqli $conn, string $username, string $password, string $email): void
{
  $username = trim($username);
  if ($username === '')
    throw new RuntimeException('Usuário admin é obrigatório.');

  if (!Database::tableExists($conn, 'api_admins')) {
    throw new RuntimeException(
      'A tabela api_admins não foi criada pelo SQL base — não é possível provisionar o administrador. '
      . 'Verifique o conteúdo de database/base.sql.'
    );
  }

  if (strlen($password) < 8)
    throw new RuntimeException('Senha admin deve ter ao menos 8 caracteres.');
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $name = 'Admin Principal';

  $stmt = $conn->prepare('SELECT id FROM api_admins WHERE username = ? LIMIT 1');
  if (!$stmt)
    throw new RuntimeException('Erro ao preparar SELECT admin: ' . $conn->error);
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res ? $res->fetch_assoc() : null;
  $stmt->close();

  if ($row && isset($row['id'])) {
    $id = (int) $row['id'];
    $up = $conn->prepare('UPDATE api_admins SET password = ?, email = ? WHERE id = ?');
    if (!$up)
      throw new RuntimeException('Erro ao preparar UPDATE admin: ' . $conn->error);
    $up->bind_param('ssi', $hash, $email, $id);
    $up->execute();
    $up->close();
    return;
  }

  $ins = $conn->prepare(
    'INSERT INTO api_admins (name, username, password, email, created_at)
         VALUES (?, ?, ?, ?, NOW())'
  );
  if (!$ins)
    throw new RuntimeException('Erro ao preparar INSERT admin: ' . $conn->error);
  $ins->bind_param('ssss', $name, $username, $hash, $email);
  $ins->execute();
  $ins->close();
}

/**
 * Grava em api_settings.base_url a URL que o assistente perguntou — só se ela
 * estiver vazia.
 *
 * O assistente pedia a URL do site e a guardava apenas em config/local.php
 * (APP_BASE_URL), que nenhum arquivo da aplicação lê. Os links de e-mail saem de
 * api_settings.base_url (api/libs/url_helpers.php), e ela nascia vazia: depois de
 * instalar, cobrança, renovação e suporte saíam sem botão, e a recuperação de
 * senha montava o link com o cabeçalho Host. A forma gravada é a do diretório
 * da API, a mesma que o painel e o link de afiliado esperam. Num banco que já
 * tinha o Drop, a URL configurada lá é de quem a configurou e fica como está.
 */
function update_base_url(mysqli $conn, string $baseUrl): void
{
  $baseUrl = rtrim(trim($baseUrl), '/');
  if ($baseUrl === '' || !preg_match('#^https?://#i', $baseUrl) || !Database::tableExists($conn, 'api_settings')) {
    return;
  }
  $api = str_ends_with($baseUrl, '/api') ? $baseUrl : $baseUrl . '/api';

  // Pelo leitor e pelo gravador centrais (api/libs/db_helpers.php): eles
  // decifram, cifram o que for segredo e apagam o cache settings.json — um
  // INSERT direto aqui deixaria o painel lendo a URL vazia do cache.
  $helpers = Paths::appFile('api/libs/db_helpers.php');
  if (!is_file($helpers)) {
    return;
  }
  require_once $helpers;
  if (trim((string) (drop_settings_load($conn)['base_url'] ?? '')) !== '') {
    return;
  }
  drop_setting_save($conn, 'base_url', $api);
}

function update_system_name(mysqli $conn, string $siteName): void
{
  $siteName = trim($siteName);
  if ($siteName === '')
    return;

  // `SHOW TABLES LIKE` devolve false em erro, e `false->num_rows` é fatal.
  if (!Database::tableExists($conn, 'system_info')) {
    return;
  }

  foreach ([['name', $siteName], ['short_name', $siteName]] as $r) {
    $stmt = $conn->prepare('UPDATE system_info SET meta_value = ? WHERE meta_field = ?');
    if ($stmt) {
      $stmt->bind_param('ss', $r[1], $r[0]);
      $stmt->execute();
      $stmt->close();
    }
  }
}

/* ── Environment detection ─────────────────────────────────────── */
$detectedBasePath = Paths::basePath();
$defaultBaseUrl = Paths::baseUrl();
$lockBaseUrl = ($detectedBasePath !== '');
$host = Paths::host();
$hostLower = strtolower($host);
$isLocalHost = in_array($hostLower, ['localhost', '127.0.0.1', '::1', '[::1]'], true)
  || str_starts_with($hostLower, 'localhost:')
  || str_starts_with($hostLower, '127.0.0.1:');
$isSubfolderInstall = ($detectedBasePath !== '');
$requireSubfolderConfirm = (!$isLocalHost && $isSubfolderInstall);

/* ── Assets do instalador ───────────────────────────────────────── */
$scrollImages = Assets::scrollImages();
$defaultBg = Assets::firstScrollImage();

/* ── Migrações opcionais realmente disponíveis ──────────────────── */
$optionalMigrations = $engine->getManifest()->getOptionalMigrations($root);

/* ── Form state ─────────────────────────────────────────────────── */
$manifestDefaults = $engine->getManifest()->getDefaults();

$form = [
  'base_url' => $defaultBaseUrl,
  'app_env' => 'production',
  'site_name' => $manifestDefaults['site_name'] ?? 'Drop',
  'db_host' => Env::get('DB_HOST', 'localhost'),
  'db_name' => Env::get('DB_NAME', (string) ($manifestDefaults['db_name'] ?? 'dropsync_api')),
  'db_user' => Env::get('DB_USER', 'root'),
  // A senha do banco NÃO volta preenchida. Ela era reimpressa no atributo
  // `value` de um input, ou seja, ficava em texto claro no HTML e no cache do
  // navegador de qualquer máquina que abrisse o instalador.
  'db_pass' => '',
  'admin_user' => $manifestDefaults['admin_user'] ?? 'admin',
  'admin_email' => '',
  'migrations' => array_column($optionalMigrations, 'file'),
  'confirm_subfolder_install' => 0,
  'license_key' => '',
];

$errors = [];
$success = '';
$warnings = [];
$migrationAppliedMsg = '';
/** Ação adiada para depois de a página ser entregue (autodesativação). */
$disableInstallerAfterRender = false;

/* ── POST (install) ─────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyInstalled) {
  $dbHost = trim((string) ($_POST['db_host'] ?? 'localhost'));
  $dbUser = trim((string) ($_POST['db_user'] ?? ''));
  $dbPass = (string) ($_POST['db_pass'] ?? '');
  $dbName = trim((string) ($_POST['db_name'] ?? ''));
  $baseUrl = trim((string) ($_POST['base_url'] ?? ''));
  $appEnv = trim((string) ($_POST['app_env'] ?? 'production'));
  $adminUser = trim((string) ($_POST['admin_user'] ?? 'admin'));
  $adminPass = (string) ($_POST['admin_pass'] ?? '');
  $adminPassCf = (string) ($_POST['admin_pass_confirm'] ?? '');
  $adminEmail = trim((string) ($_POST['admin_email'] ?? ''));
  $siteName = trim((string) ($_POST['site_name'] ?? ($manifestDefaults['site_name'] ?? 'Drop')));
  $confirmSubfolder = isset($_POST['confirm_subfolder_install']) ? 1 : 0;
  $licenseKey = trim((string) ($_POST['license_key'] ?? ''));

  // Migrações escolhidas, restritas às que o manifesto declara E existem em
  // disco: o valor do POST não decide sozinho qual arquivo .sql é executado.
  $postedMigrations = (array) ($_POST['migrations'] ?? []);
  $selectedMigrations = array_values(array_filter(
    $optionalMigrations,
    static fn(array $m): bool => in_array($m['file'], $postedMigrations, true)
  ));

  // Lock base_url to detected path when running in sub-folder
  if ($lockBaseUrl) {
    $baseUrl = $defaultBaseUrl;
  }
  $form['base_url'] = $baseUrl !== '' ? $baseUrl : $defaultBaseUrl;
  $form['app_env'] = $appEnv;
  $form['site_name'] = $siteName;
  $form['db_host'] = $dbHost;
  $form['db_name'] = $dbName;
  $form['db_user'] = $dbUser;
  $form['admin_user'] = $adminUser;
  $form['admin_email'] = $adminEmail;
  $form['migrations'] = array_column($selectedMigrations, 'file');
  $form['confirm_subfolder_install'] = $confirmSubfolder;
  $form['license_key'] = $licenseKey;

  /* ── Validações — todas ANTES de encostar no banco ─────────────
     O instalador validava o tamanho da senha do admin lá dentro de
     ensure_admin(), depois de já ter importado o schema inteiro. Uma senha de
     7 caracteres, portanto, disparava o rollback de uma instalação que só
     falhou por um campo de formulário. */

  try {
    $engine->validateCsrfToken((string) ($_POST['_csrf'] ?? ''), true);
  } catch (RuntimeException $e) {
    $errors[] = 'Sessão expirada ou inválida. Recarregue a página e envie o formulário novamente.';
  }

  $licenseResult = null;
  if ($licenseKey === '') {
    $errors[] = 'Preencha a Chave de Licença.';
  } else {
    $licenseResult = $engine->getLicense()->verify($licenseKey, $host);
    if (!$licenseResult['valid']) {
      $errors[] = 'Licença recusada: ' . $licenseResult['message'];
    } elseif (!$licenseResult['verified']) {
      $warnings[] = $licenseResult['message'];
    }
  }

  if ($dbUser === '' || $dbName === '')
    $errors[] = 'Preencha DB usuário e DB nome.';
  if ($dbName !== '' && preg_match('/^[A-Za-z0-9_\-\$]+$/', $dbName) !== 1)
    $errors[] = 'Nome de banco inválido: use apenas letras, números, hífen, cifrão e sublinhado.';
  if ($baseUrl === '')
    $errors[] = 'Preencha a URL base.';
  if ($requireSubfolderConfirm && $confirmSubfolder !== 1) {
    $errors[] = 'Instalação em subpasta em servidor detectada. Recomendado usar raiz do domínio. Marque a confirmação para continuar.';
  }
  if ($adminUser === '')
    $errors[] = 'Preencha o usuário administrador.';
  if (strlen($adminPass) < 8)
    $errors[] = 'A senha do administrador deve ter ao menos 8 caracteres.';
  if ($adminPass !== $adminPassCf)
    $errors[] = 'Confirmação de senha admin não confere.';
  if ($adminEmail !== '' && !filter_var($adminEmail, FILTER_VALIDATE_EMAIL))
    $errors[] = 'E-mail do administrador inválido.';
  // Os requisitos valem no servidor, não só no botão "Próximo" da tela: um POST
  // direto, ou uma aba aberta antes de a hospedagem mudar de PHP, instalava com
  // PHP velho, extensão faltando ou o pacote pela metade — e o defeito só
  // aparecia depois, como erro fatal numa tela qualquer.
  foreach ($engine->checkRequirements() as $requisito) {
    if (!$requisito['ok']) {
      $errors[] = 'Requisito não atendido: ' . $requisito['name'] . ' (' . $requisito['details'] . ').';
    }
  }
  if (!is_file($sqlFile))
    $errors[] = 'Arquivo SQL base não encontrado em ' . str_replace($root . '/', '', $sqlFile) . '.';
  if (!is_writable($configDir))
    $errors[] = 'Sem permissão de escrita em /config — ajuste as permissões antes de instalar.';
  if (!is_writable($storageDir))
    $errors[] = 'Sem permissão de escrita em /storage — ajuste as permissões antes de instalar.';

  if (!$errors) {
    /** @var array<int,string> Tabelas criadas nesta execução — o que o rollback pode desfazer. */
    $createdTables = [];
    $conn = null;

    try {
      // Conexão sem "auto-reparo". A versão anterior, ao falhar a conexão,
      // reconectava com a senha do .env e executava ALTER USER para trocar a
      // senha do MySQL pela digitada no formulário. Isso reescrevia a
      // credencial de um servidor compartilhado a partir de uma tela pública,
      // e falhava de forma silenciosa em qualquer provedor onde o usuário do
      // banco não tem GRANT — que é o caso da hospedagem compartilhada.
      $conn = Database::connect($dbHost, $dbUser, $dbPass);

      // Antes de criar qualquer tabela: um MySQL 5.7 parava a importação no
      // meio, com erro em inglês e tabelas pela metade para desfazer.
      $problemaServidor = Database::serverProblem($conn);
      if ($problemaServidor !== null) {
        throw new RuntimeException($problemaServidor);
      }

      Database::useDatabase($conn, $dbName);

      $existingTables = Database::tables($conn);

      /*
       * BANCO QUE JÁ TEM O DROP: conectar, em vez de recusar.
       *
       * O assistente só aceitava banco vazio. Quem importou o banco pelo
       * phpMyAdmin da hospedagem, ou reenviou os arquivos depois de uma
       * mudança de servidor, ficava sem saída: o banco estava certo, mas não
       * havia como apontar o site para ele — e foi assim que o site no ar ficou
       * parado em 27/09/2026. Agora, com um banco do Drop, o assistente só
       * grava a configuração e atualiza a estrutura, sem importar nada e sem
       * apagar nada. Havendo administradores, exige o usuário e a senha de um
       * deles: quem aponta o site para um banco precisa provar que é dele.
       * Banco sem administrador (só a estrutura importada) ganha o informado.
       *
       * Numa falha, o desfazer abaixo só remove o que ESTA execução criou — e
       * aqui ela não criou nada: `$createdTables` fica vazio de propósito.
       */
      $bancoExistente = false;
      $adminsExistentes = 0;
      if ($existingTables !== []) {
        $looksLikeDrop = in_array('api_admins', $existingTables, true)
          || in_array('api_licenses', $existingTables, true);

        if (!$looksLikeDrop) {
          throw new RuntimeException(
            'O banco "' . $dbName . '" já possui ' . count($existingTables) . ' tabelas e não pertence a esta aplicação. '
              . 'Nada foi alterado. Informe um banco vazio para instalar com segurança.'
          );
        }

        $adminsExistentes = in_array('api_admins', $existingTables, true) ? admins_existentes($conn) : 0;
        if ($adminsExistentes > 0 && !admin_existente_confere($conn, $adminUser, $adminPass)) {
          throw new RuntimeException(
            'O banco "' . $dbName . '" já tem uma instalação do DROP (' . count($existingTables) . ' tabelas). '
              . 'Para usar essa instalação — um banco importado pelo phpMyAdmin, por exemplo —, informe em '
              . '"Usuário" e "Senha" do administrador os de um administrador que já existe nela. Nada foi alterado.'
          );
        }
        $bancoExistente = true;
      }

      $driver = $engine->getDriver();

      if (!$bancoExistente) {
        $driver->installDatabase($conn, $sqlFile);
        $createdTables = Database::tables($conn);
      }

      // Sincronização via Engine Mestre (garante anomalias zero). Num banco que já
      // existia, é ela que traz a estrutura para a versão deste pacote.
      if (function_exists('ensure_drop_infrastructure')) {
        ensure_drop_infrastructure($conn);
        if (!$bancoExistente) {
          $createdTables = Database::tables($conn);
        }
      }

      if (!$bancoExistente) {
        foreach ($selectedMigrations as $migration) {
          Database::execSqlFile($conn, $migration['path']);
          $migrationAppliedMsg .= ' Migração ' . basename($migration['file']) . ' aplicada.';
        }
        $createdTables = Database::tables($conn);
      }

      // Administrador e nome do sistema: só onde não havia administrador. Num
      // banco em uso, o administrador que acabou de provar a senha fica como está.
      if ($adminsExistentes === 0) {
        ensure_admin($conn, $adminUser, $adminPass, $adminEmail);
        update_system_name($conn, $siteName);
      }
      // Fora do if acima de propósito: banco importado com a URL vazia também a ganha.
      update_base_url($conn, $baseUrl);
      if ($bancoExistente) {
        $migrationAppliedMsg .= ' Conectado ao banco existente "' . $dbName . '" (' . count($existingTables)
          . ' tabelas preservadas; estrutura atualizada).';
        // Banco trazido de outra máquina carrega os segredos do painel (SMTP,
        // Stripe, tokens) cifrados com a chave DE LÁ. Sem ela aqui, eles não
        // abrem — e nada no painel diria por quê.
        if (segredos_cifrados($conn) > 0 && Env::get('DROP_ENCRYPTION_KEY') === '') {
          $migrationAppliedMsg .= ' ATENÇÃO: este banco tem senhas do painel cifradas. Copie a linha DROP_ENCRYPTION_KEY '
            . 'do .env da máquina de onde o banco veio para o .env deste servidor; sem ela, SMTP, Stripe e demais '
            . 'integrações salvas no painel não funcionam.';
        }
      }

      [$baseUrlNorm, $baseRefNorm] = app_url_parts($baseUrl);
      $cfg = "<?php\nreturn [\n"
        . "    'APP_ENV'      => " . var_export($appEnv, true) . ",\n"
        . "    'APP_BASE_URL' => " . var_export($baseUrlNorm, true) . ",\n"
        . "    'APP_BASE_REF' => " . var_export($baseRefNorm, true) . ",\n"
        . "    'DB_SERVER'    => " . var_export($dbHost, true) . ",\n"
        . "    'DB_USERNAME'  => " . var_export($dbUser, true) . ",\n"
        . "    'DB_PASSWORD'  => " . var_export($dbPass, true) . ",\n"
        . "    'DB_NAME'      => " . var_export($dbName, true) . ",\n"
        . "    'LICENSE_KEY'  => " . var_export($licenseKey, true) . ",\n"
        . "];\n";

      if (file_put_contents($configFile, $cfg) === false) {
        throw new RuntimeException('Falha ao gravar config/local.php — verifique permissões de escrita.');
      }
      @chmod($configFile, 0640);

      // Provisiona o .env e o api/config.php pelo driver — é isso que faz a
      // aplicação enxergar a instalação. Antes, o instalador gravava apenas
      // config/local.php e o .env; o api/config.php nunca era criado, e o
      // gateway da raiz, que decide pela existência DELE, seguia mostrando a
      // tela de "instale o sistema" depois de uma instalação bem-sucedida.
      $driver->bootstrapApplication([
        'APP_ENV'      => $appEnv,
        'DB_HOST'      => $dbHost,
        'DB_USER'      => $dbUser,
        'DB_PASS'      => $dbPass,
        'DB_NAME'      => $dbName,
        'BACKUP_TOKEN' => resolve_backup_token(),
      ]);

      $conn->close();
      $conn = null;

      if (@file_put_contents($lockFile, date('c') . PHP_EOL) === false) {
        throw new RuntimeException('Falha ao gravar storage/installed.lock — verifique permissões de escrita.');
      }

      // O painel entra por api/login.php. `admin/login.php`, que estava aqui e em
      // outros quatro pontos deste arquivo, não existe: o operador terminava uma
      // instalação bem-sucedida e o botão "Painel" levava a um 404.
      $adminPathHint = $detectedBasePath . '/api/login.php';
      $alreadyInstalled = true;

      // A autodesativação é ADIADA para depois da resposta. Ela renomeava a
      // pasta aqui, antes de o HTML ser montado: a tela de sucesso saía sem
      // logo, sem fundo e com todos os links (post-check, health-check)
      // apontando para uma pasta que já não existia.
      $disableInstallerAfterRender = true;

      $success = 'Instalação concluída com sucesso!' . $migrationAppliedMsg
        . ' Acesse ' . $adminPathHint . ' para entrar.';
    } catch (Throwable $e) {
      // Rollback restrito ao que ESTA execução criou.
      if ($conn instanceof mysqli) {
        $orphans = array_values(array_intersect(Database::tables($conn), $createdTables));
        Database::dropTables($conn, $orphans);
        $conn->close();
      }

      $errors[] = $createdTables === []
        ? 'Falha na Instalação: ' . $e->getMessage()
        : 'Falha na Instalação (as tabelas criadas por esta tentativa foram removidas): ' . $e->getMessage();
    }
  }
}

/**
 * Token de backup do .env.
 *
 * O .env.example distribui o valor `altere_para_um_token_longo_aleatorio`, e
 * `api/config.example.php` cai no literal `drop_master_backup_secret`. Quem
 * instalasse sem reparar nisso ficava com um token de backup público, igual em
 * todas as instalações. Aqui um token forte é gerado quando o valor atual é
 * vazio ou ainda é o de exemplo.
 */
function resolve_backup_token(): string
{
  $current = trim(Env::get('BACKUP_TOKEN'));
  $placeholders = ['', 'altere_para_um_token_longo_aleatorio', 'drop_master_backup_secret'];

  if (!in_array($current, $placeholders, true)) {
    return $current;
  }

  return bin2hex(random_bytes(32));
}

/* ── Logo — rota absoluta, válida em qualquer subpasta ──────────── */
$logoRelPath = Assets::logo();
$logoExists = Assets::logoExists();

/* Gradiente de sobreposição do fundo. Fica numa variável porque a camada de
   transição do scroll precisa ser pintada com exatamente o mesmo valor — do
   contrário a troca de imagem mudaria o tom da tela. */
$bgOverlay = Assets::OVERLAY;
?>
<!doctype html>
<html lang="<?= h($engine->getTranslator()->getLocale()) ?>">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h(__('welcome')) ?> • WebyteHub</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="icon" href="<?= h(Assets::favicon()) ?>" sizes="any">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
  <style>
    h1, h2, h3, h4, h5, h6, strong, .brand {
      font-family: 'Outfit', sans-serif;
    }
    /* ── Reset & tokens ──────────────────────────────────────────── */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg: #030406;
      --surface: rgba(255, 255, 255, .02);
      --surface2: rgba(255, 255, 255, .05);
      --border: rgba(139, 92, 246, 0.15);
      --border2: rgba(139, 92, 246, 0.25);
      --text: #f8fafc;
      --muted: #64748b;
      --primary: #8b5cf6;
      --primary-h: #a78bfa;
      --accent: #06b6d4;
      --success: #10b981;
      --danger: #f43f5e;
      --warn: #fbbf24;
      --green-btn: #8b5cf6;
      --green-btn-h: #7c3aed;
      --radius: 16px;
      --radius-sm: 10px;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: var(--bg);
      color: var(--text);
      font-size: 14px;
      line-height: 1.6;
      min-height: 100vh;
      overflow-x: hidden;
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
      background: var(--primary-h);
    }

    code {
      font-family: 'Menlo', 'Consolas', 'Liberation Mono', monospace;
      background: rgba(255, 255, 255, .08);
      padding: 2px 6px;
      border-radius: 5px;
      font-size: 12px;
    }

    /* ── Background ─────────────────────────────────────────────── */
<?= Assets::backgroundCss($bgOverlay) ?>

    .bg-glow {
      position: fixed;
      top: -30%;
      left: -30%;
      right: -30%;
      bottom: -30%;
      background: radial-gradient(circle at 10% 20%, rgba(139, 92, 246, 0.15), transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(6, 182, 212, 0.1), transparent 50%);
      filter: blur(100px);
      z-index: -9;
      pointer-events: none;
    }

    .bg-happiness {
      position: fixed;
      bottom: -10%;
      right: -10%;
      width: 50%;
      height: 50%;
      background: radial-gradient(circle, rgba(139, 92, 246, 0.03), transparent 70%);
      filter: blur(100px);
      z-index: -8;
      pointer-events: none;
      animation: breatheJoy 12s infinite alternate;
    }

    #particleCanvas {
      position: fixed;
      inset: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      opacity: .4;
      z-index: 0;
    }

    /* ── Layout ─────────────────────────────────────────────────── */
    .page {
      position: relative;
      z-index: 1;
      max-width: 780px;
      margin: 0 auto;
      padding: 24px 16px 48px;
    }

    /* ── Card ───────────────────────────────────────────────────── */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 20px;
      backdrop-filter: blur(18px);
      box-shadow: 0 24px 64px rgba(0, 0, 0, .55);
      overflow: hidden;
    }

    /* ── Header ─────────────────────────────────────────────────── */
    .card-header {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 20px 24px 18px;
      border-bottom: 1px solid var(--border);
      background: var(--surface2);
    }

    .logo-wrap img {
      height: 38px;
      width: auto;
      display: block;
      object-fit: contain;
    }

    .logo-fallback {
      font-size: 20px;
      font-weight: 800;
      letter-spacing: -.02em;
      background: linear-gradient(135deg, var(--primary-h), #a5b4fc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .header-meta {
      flex: 1;
    }

    .header-meta h1 {
      font-size: 17px;
      font-weight: 700;
      letter-spacing: -.02em;
      line-height: 1.2;
    }

    .header-meta p {
      font-size: 12px;
      color: var(--muted);
      margin-top: 2px;
    }

    .badge-live {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 600;
      background: rgba(99, 102, 241, .15);
      border: 1px solid rgba(99, 102, 241, .3);
      color: #a5b4fc;
    }

    .dot-pulse {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--primary-h);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, .25);
      animation: pulse 1.8s ease-in-out infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        transform: scale(1);
        opacity: .9
      }

      50% {
        transform: scale(1.2);
        opacity: 1
      }
    }

    /* ── Step progress ──────────────────────────────────────────── */
    .steps-bar {
      display: flex;
      align-items: center;
      gap: 0;
      padding: 14px 24px 12px;
      border-bottom: 1px solid var(--border);
    }

    .step-item {
      display: flex;
      align-items: center;
      gap: 7px;
      flex: 1;
    }

    .step-item:not(:last-child)::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border2);
      margin: 0 6px;
    }

    .step-num {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
      border: 1.5px solid var(--border2);
      background: var(--surface2);
      color: var(--muted);
      flex-shrink: 0;
      transition: all .3s ease;
    }

    .step-item.active .step-num {
      background: var(--primary);
      border-color: var(--primary);
      color: #fff;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, .25);
    }

    .step-item.done .step-num {
      background: var(--success);
      border-color: var(--success);
      color: #fff;
    }

    .step-label {
      font-size: 12px;
      font-weight: 500;
      color: var(--muted);
      white-space: nowrap;
    }

    .step-item.active .step-label {
      color: var(--text);
    }

    .step-item.done .step-label {
      color: var(--success);
    }

    /* ── Body ───────────────────────────────────────────────────── */
    .card-body {
      padding: 22px 24px 24px;
    }

    /* ── Section title ──────────────────────────────────────────── */
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
      background: rgba(99, 102, 241, .15);
      border: 1px solid rgba(99, 102, 241, .25);
    }

    /* ── Grid form ──────────────────────────────────────────────── */
    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .grid .full {
      grid-column: 1/-1;
    }

    /* ── Field ──────────────────────────────────────────────────── */
    .field {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .field label {
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .field input,
    .field select {
      padding: 9px 11px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border2);
      background: rgba(255, 255, 255, .05);
      color: var(--text);
      font-size: 13px;
      font-family: inherit;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      width: 100%;
    }

    .field select option {
      background: #0b0d14;
      color: var(--text);
    }

    .field input:focus,
    .field select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, .18);
    }

    .field input[readonly] {
      opacity: .6;
      cursor: default;
    }

    .field .hint {
      font-size: 11px;
      color: var(--muted);
      line-height: 1.4;
    }

    /* ── Checkbox row ───────────────────────────────────────────── */
    .check-row {
      display: flex;
      align-items: flex-start;
      gap: 9px;
      padding: 10px 12px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border2);
      background: rgba(255, 255, 255, .03);
      cursor: pointer;
    }

    .check-row input[type="checkbox"] {
      width: 15px;
      height: 15px;
      flex-shrink: 0;
      margin-top: 2px;
      accent-color: var(--primary);
      cursor: pointer;
    }

    .check-row .check-text {
      font-size: 13px;
      line-height: 1.5;
    }

    .check-row .check-text small {
      display: block;
      font-size: 11px;
      color: var(--muted);
      margin-top: 2px;
    }

    /* ── Alert boxes ────────────────────────────────────────────── */
    .alert {
      padding: 11px 14px;
      border-radius: var(--radius-sm);
      font-size: 13px;
      line-height: 1.5;
      margin-bottom: 14px;
      display: flex;
      gap: 8px;
      align-items: flex-start;
    }

    .alert-icon {
      font-size: 15px;
      flex-shrink: 0;
      margin-top: 1px;
    }

    .alert-success {
      background: rgba(34, 197, 94, .1);
      border: 1px solid rgba(34, 197, 94, .25);
      color: #86efac;
    }

    .alert-danger {
      background: rgba(239, 68, 68, .1);
      border: 1px solid rgba(239, 68, 68, .25);
      color: #fca5a5;
    }

    .alert-warn {
      background: rgba(245, 158, 11, .1);
      border: 1px solid rgba(245, 158, 11, .25);
      color: #fcd34d;
    }

    .alert-info {
      background: rgba(99, 102, 241, .1);
      border: 1px solid rgba(99, 102, 241, .25);
      color: #c7d2fe;
    }

    /* ── Quick-preset highlight ─────────────────────────────────── */
    .xampp-preset-box {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      border-radius: var(--radius-sm);
      background: rgba(22, 163, 74, .1);
      border: 1px solid rgba(22, 163, 74, .3);
      margin-bottom: 16px;
    }

    .xampp-preset-box p {
      font-size: 12px;
      color: #86efac;
      flex: 1;
      line-height: 1.4;
    }

    /* ── Buttons ────────────────────────────────────────────────── */
    .btn-row {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 18px;
      align-items: center;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 18px;
      border-radius: var(--radius-sm);
      font-size: 13px;
      font-weight: 700;
      border: none;
      cursor: pointer;
      font-family: inherit;
      transition: transform .18s ease, filter .18s ease, opacity .18s ease;
      text-decoration: none;
      white-space: nowrap;
    }

    .btn:hover {
      transform: translateY(-1px) scale(1.02);
      filter: brightness(1.06);
    }

    .btn:active {
      transform: scale(.97);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary), #818cf8);
      color: #fff;
      box-shadow: 0 4px 18px rgba(99, 102, 241, .3);
    }

    .btn-green {
      background: linear-gradient(135deg, var(--green-btn), var(--green-btn-h));
      color: #fff;
      box-shadow: 0 4px 14px rgba(22, 163, 74, .3);
      font-size: 13px;
    }

    .btn-green .btn-icon {
      font-size: 15px;
    }

    .btn-ghost {
      background: var(--surface2);
      color: var(--text);
      border: 1px solid var(--border2);
    }

    .btn-danger {
      background: rgba(239, 68, 68, .15);
      color: #fca5a5;
      border: 1px solid rgba(239, 68, 68, .3);
    }

    .btn-link {
      background: none;
      border: none;
      color: var(--muted);
      padding: 0;
      font-size: 12px;
      cursor: pointer;
    }

    .btn-link:hover {
      color: var(--text);
      transform: none;
      filter: none;
    }

    /* ── Section divider ────────────────────────────────────────── */
    .divider {
      height: 1px;
      background: var(--border);
      margin: 20px 0;
    }

    /* ── Health check table ─────────────────────────────────────── */
    .req-table {
      width: 100%;
      border-collapse: collapse;
    }

    .req-table th,
    .req-table td {
      padding: 8px 10px;
      text-align: left;
      font-size: 13px;
      border-bottom: 1px solid var(--border);
    }

    .req-table th {
      font-size: 11px;
      font-weight: 600;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .req-ok {
      color: var(--success);
      font-weight: 700;
    }

    .req-fail {
      color: var(--danger);
      font-weight: 700;
    }

    /* ── Success panel ──────────────────────────────────────────── */
    .success-panel {
      text-align: center;
      padding: 24px 16px;
    }

    .success-icon {
      font-size: 48px;
      margin-bottom: 12px;
    }

    .success-panel h2 {
      font-size: 20px;
      font-weight: 800;
      margin-bottom: 6px;
    }

    .success-panel p {
      color: var(--muted);
      font-size: 13px;
      max-width: 400px;
      margin: 0 auto 18px;
    }

    .success-btn-row {
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
    }

    /* ── Collapse animation ─────────────────────────────────────── */
    .slide-in {
      animation: slideIn .4s cubic-bezier(.22, 1, .36, 1) both;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(16px)
      }

      to {
        opacity: 1;
        transform: translateY(0)
      }
    }

    /* ── Media ──────────────────────────────────────────────────── */
    @media (max-width: 768px) {
      .page {
        padding: 16px 12px 40px;
      }

      .card-header {
        padding: 14px 16px;
        flex-wrap: wrap;
        gap: 10px;
      }

      .badge-live {
        display: none;
      }

      .grid {
        grid-template-columns: 1fr !important;
      }

      .steps-bar {
        padding: 10px 14px;
        gap: 0;
        overflow-x: auto;
        scrollbar-width: none;
      }

      .steps-bar::-webkit-scrollbar {
        display: none;
      }

      .step-label {
        display: none;
      }

      .card-body {
        padding: 16px;
      }

      .btn-row {
        flex-direction: column;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }

      /* Touch targets mínimos WCAG */
      .field input,
      .field select,
      .btn {
        min-height: 44px;
      }
    }

    @media (max-width: 480px) {
      .page {
        padding: 10px 8px 30px;
      }

      .card {
        border-radius: 14px;
      }

      .card-header {
        padding: 12px 14px;
      }

      .header-meta h1 {
        font-size: 15px;
      }

      .card-body {
        padding: 14px;
      }

      .sec-title {
        font-size: 13px;
      }

      .field input,
      .field select {
        font-size: 16px;
        /* Evita zoom automático no iOS */
      }

      /* Partícula desabilitada em mobile muito pequeno */
      #particleCanvas {
        display: none;
      }
    }

    /* 4K / Ultrawide */
    @media (min-width: 2560px) {
      .page {
        max-width: 1000px;
      }

      .card {
        border-radius: 28px;
      }

      .card-body {
        padding: 32px;
      }

      .field input,
      .field select {
        padding: 13px 15px;
        font-size: 15px;
      }

      .btn {
        padding: 13px 24px;
        font-size: 15px;
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
  <div class="bg-glow"></div>
  <div class="bg-happiness"></div>
  <canvas id="particleCanvas" aria-hidden="true"></canvas>

  <div class="page">
    <!-- ── Main card ─────────────────────────────────────── -->
    <div class="card slide-in">

      <!-- Header -->
      <div class="card-header">
        <div class="logo-wrap">
          <?php if ($logoExists): ?>
            <img src="<?= h($logoRelPath) ?>" alt="WebyteHub Logo">
          <?php else: ?>
            <span class="logo-fallback">WebyteHub</span>
          <?php endif; ?>
        </div>
        <div class="header-meta">
          <h1>DROP Ecosystem Initializer</h1>
          <p><?= __('subtitle_text') ?></p>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; margin-left: auto;">
          <form method="GET" style="margin: 0; display: flex; align-items: center;">
            <?php if (isset($_GET['allow'])): ?>
              <input type="hidden" name="allow" value="1">
            <?php endif; ?>
            <select name="lang" onchange="this.form.submit()" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-size: 13px; cursor: pointer; outline: none;">
              <option value="pt" <?= $engine->getTranslator()->getLocale() === 'pt' ? 'selected' : '' ?>>PT (Português)</option>
              <option value="en" <?= $engine->getTranslator()->getLocale() === 'en' ? 'selected' : '' ?>>EN (English)</option>
              <option value="es" <?= $engine->getTranslator()->getLocale() === 'es' ? 'selected' : '' ?>>ES (Español)</option>
              <option value="fr" <?= $engine->getTranslator()->getLocale() === 'fr' ? 'selected' : '' ?>>FR (Français)</option>
            </select>
          </form>
          <div class="badge-live"><span class="dot-pulse"></span> <?= __('symphony_active') ?></div>
        </div>
      </div>

      <!-- Step progress bar -->
      <div class="steps-bar" id="stepsBar">
        <div class="step-item" id="step-ind-1">
          <div class="step-num">1</div>
          <span class="step-label"><?= __('requirements') ?></span>
          <span></span><!-- connector via CSS ::after -->
        </div>
        <div class="step-item" id="step-ind-2">
          <div class="step-num">2</div>
          <span class="step-label"><?= __('configure') ?></span>
          <span></span>
        </div>
        <div class="step-item" id="step-ind-3">
          <div class="step-num">3</div>
          <span class="step-label"><?= __('finish') ?></span>
        </div>
      </div>

      <!-- Body -->
      <div class="card-body">

        <?php if ($success !== ''): ?>
          <!-- ── SUCCESS ──────────────────────────────────── -->
          <?php
          $goBase = rtrim((string) $form['base_url'], '/') . '/';
          $adminUrl = $goBase . 'api/login.php';
          $siteUrl = $goBase;
          ?>
          <div class="success-panel slide-in">
            <div class="success-icon"><?= Icons::render('check-circle', 42) ?></div>
            <h2>Instalação concluída!</h2>
            <p><?= h($success) ?></p>

            <!-- Os avisos precisam sobreviver ao sucesso. Só eram exibidos no
                 passo 2 do formulário, então quem instalasse com licença aceita
                 sem verificação (modo offline) via a tela de "concluído" limpa e
                 saía convencido de que a chave tinha sido conferida. -->
            <?php foreach ($warnings as $warn): ?>
              <div class="alert alert-warn" style="text-align:left;margin:14px 0">
                <span class="alert-icon"><?= Icons::render('alert', 17) ?></span>
                <div><?= h($warn) ?></div>
              </div>
            <?php endforeach; ?>

            <div class="success-btn-row">
              <a class="btn btn-primary" href="<?= h($adminUrl) ?>"><?= Icons::render('shield', 16) ?> Ir para o painel Admin</a>
              <a class="btn btn-ghost" href="<?= h($siteUrl) ?>"><?= Icons::render('globe', 16) ?> Ver o site</a>
            </div>
            <p style="margin-top:16px;font-size:11px;color:var(--muted)">
              <?= Icons::render('alert', 13) ?> Por segurança, remova a pasta <code><?= h('/' . Paths::installerName()) ?></code> após a instalação.
            </p>
          </div>

        <?php elseif ($alreadyInstalled): ?>
          <!-- ── Already installed ────────────────────────── -->
          <div class="alert alert-info"><span class="alert-icon"><?= Icons::render('info', 17) ?></span>
            <div>
              <?= h(__('locked_desc')) ?>
              <?php $installedArtifacts = InstallState::present(); ?>
              <?php if ($installedArtifacts !== []): ?>
                <br><?= h(__('reinstall_intro')) ?>
                <?php foreach ($installedArtifacts as $i => $artifact): ?>
                  <?= $i > 0 ? ', ' : ' ' ?><code><?= h($artifact['path']) ?></code>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

        <?php else: ?>
          <!-- ── WIZARD STEPS ──────────────────────────────── -->
          <!-- Step 1: Requirements -->
          <div id="wizStep1">
            <div class="sec-title">
              <div class="sec-icon"><?= Icons::render('search', 16) ?></div>
              <?= __('requirements') ?>
            </div>

            <?php
            $reqs = $engine->checkRequirements();
            $reqs[] = [
              'name' => 'sql_base_file',
              'ok' => is_file($sqlFile),
              'details' => str_replace($root . '/', '', $sqlFile)
            ];

            // A camada /api só existe na instalação integrada. Quando existe,
            // ela precisa ser gravável: é ali que o api/config.php é criado, e
            // uma falha nesse ponto acontecia no meio da instalação — depois
            // do schema já importado — em vez de aqui, antes de começar.
            $apiDir = $root . '/api';
            if (is_dir($apiDir)) {
              $reqs[] = [
                'name' => 'writable: /api',
                'ok' => is_writable($apiDir),
                'details' => str_replace('\\', '/', $apiDir)
              ];
            }

            $allReqsOk = array_reduce($reqs, fn($carry, $r) => $carry && $r['ok'], true);

            // O que faltou chegar do pacote, arquivo por arquivo: a linha da
            // tabela só cabe os primeiros, e quem vai reenviar precisa da lista.
            $manifestoPacote = (string) ($engine->getManifest()->getRequirements()['package_manifest'] ?? '');
            $pacote = $manifestoPacote !== '' ? \DropGenesis\Core\Integrity::check($root, $manifestoPacote) : null;
            $pacoteFaltando = $pacote !== null ? array_merge($pacote['ausentes'], $pacote['incompletos']) : [];
            ?>

            <div class="alert <?= $allReqsOk ? 'alert-success' : 'alert-danger' ?>">
              <span class="alert-icon"><?= $allReqsOk ? Icons::render('check-circle', 17) : Icons::render('x-circle', 17) ?></span>
              <div>
                <?= $allReqsOk ? __('req_met') : __('req_unmet') ?>
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
                <?php foreach ($reqs as $r):
                  $translatedName = $r['name'];
                  if ($translatedName === 'sql_base_file') {
                      $translatedName = __('sql_base_file');
                  } elseif (strpos($translatedName, 'Extensão') !== false) {
                      $translatedName = str_replace('Extensão', __('extension'), $translatedName);
                  } elseif (strpos($translatedName, 'Escrita em') !== false) {
                      $translatedName = str_replace('Escrita em', __('write_permission'), $translatedName);
                  }
                ?>
                  <tr>
                    <td><?= h($translatedName) ?></td>
                    <td class="<?= $r['ok'] ? 'req-ok' : 'req-fail' ?>"><?= $r['ok'] ? Icons::render('check', 14) . ' OK' : Icons::render('close', 14) . ' ' . __('fail') ?></td>
                    <td style="font-size:12px;color:var(--muted)"><?= h($r['details'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <?php
            // Por que o assistente abriu mesmo com arquivos de "instalado" aqui:
            // vieram de outra máquina, sem a configuração deste servidor.
            $travasDeOutraMaquina = InstallState::present() !== [] && InstallState::semConfiguracaoDoServidor();
            // Pasta de desenvolvimento enviada inteira: funciona, mas leva logs,
            // testes, backups e o histórico do git para a hospedagem.
            $pastaDeDesenvolvimento = array_values(array_filter(
              ['tests', 'node_modules', '.git', '.claude', '.kilo'],
              static fn (string $pasta): bool => is_dir($root . '/' . $pasta)
            ));
            ?>
            <?php if ($travasDeOutraMaquina): ?>
              <div class="alert alert-warn">
                <span class="alert-icon"><?= Icons::render('alert', 17) ?></span>
                <div>
                  <strong>Esta pasta veio marcada como instalada, mas não tem a configuração deste servidor.</strong>
                  Os arquivos <?= h(implode(', ', array_column(InstallState::present(), 'path'))) ?> são de outra máquina, e
                  aqui não há <code>.env</code> nem banco que conecte com eles. Configure o banco desta hospedagem abaixo.
                  Se ele já tiver o Drop (importado pelo phpMyAdmin, por exemplo), o assistente só conecta — informe o
                  usuário e a senha de um administrador que já existe nele.
                </div>
              </div>
            <?php endif; ?>
            <?php if ($pastaDeDesenvolvimento !== []): ?>
              <div class="alert alert-warn">
                <span class="alert-icon"><?= Icons::render('alert', 17) ?></span>
                <div>
                  <strong>Esta é a pasta de desenvolvimento</strong> (tem <?= h(implode(', ', $pastaDeDesenvolvimento)) ?>).
                  Ela instala, mas leva para a hospedagem o que não deveria: testes, logs, backups e o histórico do git.
                  Prefira o pacote gerado por <code>scripts/gerar_pacote.ps1</code>, ou apague essas pastas do servidor.
                </div>
              </div>
            <?php endif; ?>

            <?php if ($pacoteFaltando !== []): ?>
              <div class="alert alert-danger">
                <span class="alert-icon"><?= Icons::render('x-circle', 17) ?></span>
                <div>
                  <strong>O pacote não chegou inteiro nesta hospedagem.</strong>
                  <?= count($pacote['ausentes']) ?> arquivo(s) faltando e <?= count($pacote['incompletos']) ?> pela metade.
                  Envie de novo o pacote (ou todas as partes) e extraia na mesma pasta, substituindo os arquivos;
                  depois recarregue esta página. Se a hospedagem limita o tamanho do arquivo (a InfinityFree aceita até 10 MB),
                  use o pacote em partes.
                  <ul style="margin:8px 0 0;padding-left:18px;font-size:12px;max-height:180px;overflow:auto">
                    <?php foreach (array_slice($pacoteFaltando, 0, 200) as $faltando): ?>
                      <li><code><?= h($faltando) ?></code></li>
                    <?php endforeach; ?>
                    <?php if (count($pacoteFaltando) > 200): ?>
                      <li>… e mais <?= count($pacoteFaltando) - 200 ?>.</li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            <?php endif; ?>

            <div class="btn-row">
              <button class="btn btn-primary" id="toStep2Btn" onclick="goStep(2)" <?= !$allReqsOk ? 'disabled style="opacity:.5;cursor:not-allowed"' : '' ?>>
                <?= __('next') ?>: <?= __('configure') ?> <?= Icons::render('arrow-right', 15) ?>
              </button>
              <a class="btn btn-ghost" href="health-check.php"><?= __('btn_health_check') ?></a>
              <a class="btn btn-ghost" href="../"><?= __('btn_back_core') ?></a>
            </div>
          </div>

          <!-- Step 2: Configuration -->
          <div id="wizStep2" style="display:none">
            <div class="sec-title">
              <div class="sec-icon"><?= Icons::render('settings', 16) ?></div>
              <?= __('configure') ?>
            </div>

            <!-- XAMPP preset – green, prominent -->
            <div class="xampp-preset-box">
              <div>
                <strong style="font-size:13px;color:#86efac"><?= Icons::render('zap', 14) ?> <?= __('local_install') ?></strong>
                <p><?= __('local_install_desc') ?></p>
              </div>
              <button class="btn btn-green" type="button" onclick="applyXamppPreset()">
                <span class="btn-icon"><?= Icons::render('monitor', 15) ?></span> <?= __('btn_use_xampp') ?>
              </button>
            </div>

            <?php if ($requireSubfolderConfirm): ?>
              <div class="alert alert-warn">
                <span class="alert-icon"><?= Icons::render('alert', 17) ?></span>
                <div>
                  <strong><?= __('subfolder_detected') ?></strong> <code><?= h($detectedBasePath) ?></code> em servidor.
                  <?= __('subfolder_warn') ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
              <?php foreach ($errors as $err): ?>
                <div class="alert alert-danger"><span class="alert-icon"><?= Icons::render('x-circle', 17) ?></span>
                  <div><?= h($err) ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($warnings)): ?>
              <?php foreach ($warnings as $warn): ?>
                <div class="alert alert-warn"><span class="alert-icon"><?= Icons::render('alert', 17) ?></span>
                  <div><?= h($warn) ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>

            <form method="post" id="installForm" onsubmit="return validateForm()">
              <?= $engine->csrfField() ?>
              <div class="grid">

                <!-- Licença -->
                <div class="field full">
                  <label><?= __('license_code') ?> <span id="licStatus" style="float:right"></span></label>
                  <div style="display:flex;gap:8px">
                    <input name="license_key" id="license_key" value="<?= h($form['license_key']) ?>" required
                      placeholder="Sua chave Premium / ex: WEBYTE-123" style="flex:1">
                    <button class="btn btn-ghost" type="button" id="btnVerifyLic" onclick="verifyLicense()"
                      style="white-space:nowrap;padding:0 14px;font-size:12px;height:auto"><?= __('btn_verify_license') ?></button>
                  </div>
                </div>

                <!-- URL Base -->
                <div class="field full">
                  <label><?= __('system_base_url') ?></label>
                  <?php if ($lockBaseUrl): ?>
                    <input name="base_url" id="base_url" value="<?= h($form['base_url']) ?>" readonly>
                    <span class="hint"><?= __('auto_detected') ?>
                      <code><?= h($detectedBasePath) ?></code>. <?= __('url_locked_hint') ?></span>
                  <?php else: ?>
                    <input name="base_url" id="base_url" value="<?= h($form['base_url']) ?>" required
                      placeholder="https://seudominio.com/">
                    <span class="hint"><?= __('auto_detected_hint', ['url' => h($defaultBaseUrl)]) ?></span>
                  <?php endif; ?>
                </div>

                <?php if ($requireSubfolderConfirm): ?>
                  <div class="full">
                    <label class="check-row">
                      <input type="checkbox" name="confirm_subfolder_install" value="1"
                        <?= (int) $form['confirm_subfolder_install'] === 1 ? 'checked' : '' ?>>
                      <span class="check-text">
                        <?= __('confirm_subfolder', ['path' => h($detectedBasePath)]) ?>
                        <small><?= __('confirm_subfolder_production') ?></small>
                      </span>
                    </label>
                  </div>
                <?php endif; ?>

                <!-- App env & site name -->
                <div class="field">
                  <label><?= __('environment') ?></label>
                  <select name="app_env" id="app_env">
                    <option value="production" <?= $form['app_env'] === 'production' ? 'selected' : '' ?>>Production
                    </option>
                    <option value="staging" <?= $form['app_env'] === 'staging' ? 'selected' : '' ?>>Staging</option>
                    <option value="development" <?= $form['app_env'] === 'development' ? 'selected' : '' ?>>Development
                    </option>
                  </select>
                </div>
                <div class="field">
                  <label><?= __('site_name') ?></label>
                  <input name="site_name" id="site_name" value="<?= h($form['site_name']) ?>" placeholder="Drop">
                </div>

                <div class="divider full"></div>

                <!-- DB -->
                <div class="field">
                  <label><?= __('db_host') ?></label>
                  <input name="db_host" id="db_host" value="<?= h($form['db_host']) ?>" required placeholder="localhost">
                </div>
                <div class="field">
                  <label><?= __('db_name') ?></label>
                  <input name="db_name" id="db_name" value="<?= h($form['db_name']) ?>" required placeholder="drop">
                </div>
                <div class="field">
                  <label><?= __('db_user') ?></label>
                  <input name="db_user" id="db_user" value="<?= h($form['db_user']) ?>" required placeholder="root">
                </div>
                <div class="field">
                  <label><?= __('db_pass') ?></label>
                  <!-- Sem `value`: a senha do banco não é devolvida ao HTML. -->
                  <input name="db_pass" id="db_pass" type="password" autocomplete="new-password" placeholder="<?= __('db_pass_placeholder') ?>">
                </div>
                <div class="field full" style="margin-top:-6px;text-align:right">
                  <button class="btn btn-ghost" type="button" id="btnTestDb" onclick="testDbConnection()"
                    style="font-size:12px;padding:6px 12px"><span class="btn-icon"><?= Icons::render('plug', 15) ?></span> <?= __('btn_test_db') ?></button>
                  <div id="dbStatusMsg"
                    style="font-size:12px;margin-top:4px;display:none;text-align:left;padding:8px;border-radius:var(--radius-sm);background:rgba(255,255,255,.05)">
                  </div>
                </div>

                <div class="divider full"></div>

                 <!-- Admin -->
                <div class="field">
                  <label><?= __('admin_name') ?></label>
                  <input name="admin_user" id="admin_user" value="<?= h($form['admin_user']) ?>" required
                    placeholder="admin">
                </div>
                <div class="field">
                  <label><?= __('admin_email') ?></label>
                  <input name="admin_email" id="admin_email" type="email" value="<?= h($form['admin_email']) ?>"
                    placeholder="admin@seudominio.com">
                </div>
                <div class="field">
                  <label><?= __('admin_pass') ?></label>
                  <input name="admin_pass" id="admin_pass" type="password" required placeholder="<?= __('admin_pass_placeholder') ?>"
                    onkeyup="checkStrength()">
                  <div
                    style="display:flex;height:4px;background:var(--surface2);border-radius:2px;margin-top:2px;overflow:hidden">
                    <div id="pwdStr1"
                      style="flex:1;background:transparent;border-right:1px solid var(--bg);transition:background .3s">
                    </div>
                    <div id="pwdStr2"
                      style="flex:1;background:transparent;border-right:1px solid var(--bg);transition:background .3s">
                    </div>
                    <div id="pwdStr3"
                      style="flex:1;background:transparent;border-right:1px solid var(--bg);transition:background .3s">
                    </div>
                  </div>
                  <div id="pwdStrText" style="font-size:10px;text-align:right;color:var(--muted);height:12px"></div>
                </div>
                <div class="field">
                  <label><?= __('admin_pass_confirm') ?></label>
                  <input name="admin_pass_confirm" id="admin_pass_confirm" type="password" required
                    placeholder="<?= __('admin_pass_confirm_placeholder') ?>">
                </div>

                <!-- Migrações opcionais.
                     Só entram na tela as que o manifesto declara E que existem
                     em disco. Antes, as três caixas apareciam sempre marcadas
                     para arquivos .sql ausentes do projeto: o operador
                     acreditava ter aplicado hardening, estrutura e fase 2, e
                     nada disso rodava. -->
                <?php foreach ($optionalMigrations as $migration): ?>
                  <div class="full">
                    <label class="check-row">
                      <input type="checkbox" name="migrations[]" value="<?= h($migration['file']) ?>"
                        <?= in_array($migration['file'], (array) $form['migrations'], true) ? 'checked' : '' ?>>
                      <span class="check-text">
                        <?= h(__($migration['label'])) ?>
                        <?php if ($migration['hint'] !== ''): ?>
                          <small><?= h(__($migration['hint'])) ?></small>
                        <?php endif; ?>
                      </span>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="btn-row">
                <button class="btn btn-ghost" type="button" onclick="goStep(1)"><?= Icons::render('arrow-left', 15) ?> <?= __('back') ?></button>
                <button class="btn btn-primary" type="submit" id="submitBtn"><?= Icons::render('rocket', 16) ?> <?= __('btn_install_now') ?></button>
              </div>
            </form>
          </div>

          <!-- Step 3: Installing (progress indicator shown via JS) -->
          <div id="wizStep3" style="display:none">
            <div style="text-align:center;padding:40px 20px">
              <div style="margin-bottom:14px;color:var(--primary-h)"><?= Icons::render('clock', 40) ?></div>
              <h2 style="font-size:18px;font-weight:700;margin-bottom:8px"><?= __('installing') ?></h2>
              <p style="color:var(--muted);font-size:13px"><?= __('installing_desc') ?></p>
              <div
                style="margin-top:20px;width:100%;height:4px;background:var(--surface2);border-radius:2px;overflow:hidden">
                <div id="progressBar"
                  style="height:100%;width:0%;background:linear-gradient(90deg,var(--primary),var(--primary-h));border-radius:2px;transition:width .5s ease">
                </div>
              </div>
            </div>
          </div>

        <?php endif; // not installed
        ?>
      </div><!-- /.card-body -->
    </div><!-- /.card -->

    <!-- Footer -->
    <p style="text-align:center;margin-top:16px;font-size:11px;color:var(--muted)">
      <?= __('installer_security_note') ?>
    </p>
  </div><!-- /.page -->

  <script>
    /* ─────────────────────────────────────────────────────────────────────
       Wizard State
    ───────────────────────────────────────────────────────────────────── */
    var currentStep = 1;

    function goStep(n) {
      document.getElementById('wizStep' + currentStep).style.display = 'none';
      document.getElementById('wizStep' + n).style.display = 'block';
      currentStep = n;
      updateStepBar(n);
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }

    function updateStepBar(active) {
      for (var i = 1; i <= 3; i++) {
        var el = document.getElementById('step-ind-' + i);
        if (!el) continue;
        el.classList.remove('active', 'done');
        if (i < active) el.classList.add('done');
        else if (i === active) el.classList.add('active');
      }
    }

    /* ─────────────────────────────────────────────────────────────────────
       Form validation (client-side)
    ───────────────────────────────────────────────────────────────────── */
    function validateForm() {
      var pass = document.getElementById('admin_pass');
      var passCf = document.getElementById('admin_pass_confirm');
      if (!pass || !passCf) return true;
      if (pass.value.length < 8) {
        alert('A senha do Admin deve ter ao menos 8 caracteres.');
        pass.focus();
        return false;
      }
      if (pass.value !== passCf.value) {
        alert('As senhas do Admin não coincidem.');
        passCf.focus();
        return false;
      }
      // Show step 3 loading indicator
      goStep(3);
      animateProgress();
      return true;
    }

    function animateProgress() {
      var bar = document.getElementById('progressBar');
      if (!bar) return;
      var p = 0;
      var iv = setInterval(function() {
        p += Math.random() * 18;
        if (p >= 90) {
          p = 90;
          clearInterval(iv);
        }
        bar.style.width = p + '%';
      }, 400);
    }

    /* ─────────────────────────────────────────────────────────────────────
       New Features JS
    ───────────────────────────────────────────────────────────────────── */
    /* O ajax.php exige o token CSRF. Ele nunca era enviado — nem existia no
       HTML — então TODA chamada de "Testar Conexão" e "Validar via API"
       respondia "CSRF Security Violation" desde sempre. O token sai do mesmo
       campo oculto do formulário de instalação. */
    function csrfToken() {
      var field = document.querySelector('#installForm input[name="_csrf"]');
      return field ? field.value : '';
    }

    /* Ícones do conjunto do produto, emitidos pelo PHP. */
    var DG_ICONS = <?= json_encode([
      'ok'   => Icons::render('check-circle', 14),
      'warn' => Icons::render('alert', 14),
      'err'  => Icons::render('x-circle', 14),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    /* O texto vindo do servidor entra como nó de texto, nunca como HTML:
       mensagens de erro do MySQL podem conter o que o usuário digitou. O
       ícone é marcação nossa, constante, então pode ir por innerHTML. */
    function setStatus(el, text, color, kind) {
      el.style.color = color;
      el.innerHTML = '';

      if (kind && DG_ICONS[kind]) {
        var holder = document.createElement('span');
        holder.style.cssText = 'display:inline-flex;vertical-align:-2px;margin-right:6px';
        holder.innerHTML = DG_ICONS[kind];
        el.appendChild(holder);
      }

      el.appendChild(document.createTextNode(text));
    }

    function verifyLicense() {
      var key = document.getElementById('license_key').value;
      var btn = document.getElementById('btnVerifyLic');
      var status = document.getElementById('licStatus');
      var label = btn.innerHTML;

      if (!key) {
        setStatus(status, 'Preencha a chave', 'var(--danger)', 'err');
        return;
      }

      btn.disabled = true;
      btn.innerText = 'Validando...';
      status.textContent = '';

      var fd = new FormData();
      fd.append('action', 'verify_license');
      fd.append('key', key);
      fd.append('_csrf', csrfToken());

      fetch('ajax.php', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(data => {
          btn.disabled = false;
          btn.innerHTML = label;
          if (data.valid && data.verified === false) {
            setStatus(status, data.message, 'var(--warn)', 'warn');
          } else if (data.valid) {
            setStatus(status, data.message, 'var(--success)', 'ok');
          } else {
            setStatus(status, data.message, 'var(--danger)', 'err');
          }
        })
        .catch(e => {
          btn.disabled = false;
          btn.innerHTML = label;
          setStatus(status, 'Erro ao acessar a verificação de licença', 'var(--warn)', 'warn');
        });
    }

    function testDbConnection() {
      var btn = document.getElementById('btnTestDb');
      var msg = document.getElementById('dbStatusMsg');
      var label = btn.innerHTML;

      btn.disabled = true;
      btn.innerHTML = 'Testando...';
      msg.style.display = 'none';

      var fd = new FormData();
      fd.append('action', 'test_db');
      fd.append('host', document.getElementById('db_host').value);
      fd.append('name', document.getElementById('db_name').value);
      fd.append('user', document.getElementById('db_user').value);
      fd.append('pass', document.getElementById('db_pass').value);
      fd.append('_csrf', csrfToken());

      fetch('ajax.php', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(data => {
          btn.disabled = false;
          btn.innerHTML = label;
          msg.style.display = 'block';
          setStatus(msg, data.message, data.success ? 'var(--success)' : 'var(--danger)', data.success ? 'ok' : 'err');
        })
        .catch(e => {
          btn.disabled = false;
          btn.innerHTML = label;
          msg.style.display = 'block';
          setStatus(msg, 'Erro na requisição AJAX.', 'var(--danger)', 'err');
        });
    }

    function checkStrength() {
      var val = document.getElementById('admin_pass').value;
      var p1 = document.getElementById('pwdStr1');
      var p2 = document.getElementById('pwdStr2');
      var p3 = document.getElementById('pwdStr3');
      var txt = document.getElementById('pwdStrText');
      var score = 0;
      if (val.length > 5) score++;
      if (val.length >= 8 && /[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
      if (score === 2 && /[^A-Za-z0-9]/.test(val)) score++;

      var cNone = 'transparent',
        cWeak = '#ef4444',
        cMed = '#f59e0b',
        cStrong = '#22c55e';

      p1.style.background = score >= 1 ? (score === 1 ? cWeak : (score === 2 ? cMed : cStrong)) : cNone;
      p2.style.background = score >= 2 ? (score === 2 ? cMed : cStrong) : cNone;
      p3.style.background = score >= 3 ? cStrong : cNone;

      if (val.length === 0) txt.innerText = '';
      else if (score === 0) txt.innerText = 'Muito Fraca';
      else if (score === 1) txt.innerText = 'Fraca';
      else if (score === 2) txt.innerText = 'Média';
      else if (score === 3) txt.innerText = 'Forte';
    }

    /* ─────────────────────────────────────────────────────────────────────
       XAMPP Preset
    ───────────────────────────────────────────────────────────────────── */
    function applyXamppPreset() {
      var get = function(id) {
        return document.getElementById(id);
      };
      var detectedUrl = <?= json_encode($defaultBaseUrl) ?>;
      var locked = <?= json_encode((bool) $lockBaseUrl) ?>;
      // Vem do drop.json. O valor estava fixo em 'webyplay_local', nome de
      // outro projeto: quem clicasse no atalho instalava num banco errado.
      var presetDbName = <?= json_encode($manifestDefaults['db_name'] ?? 'dropsync_api') ?>;
      var presetAdmin = <?= json_encode($manifestDefaults['admin_user'] ?? 'admin') ?>;

      if (!locked) {
        var el = get('base_url');
        if (el) el.value = detectedUrl;
      }
      if (get('app_env')) get('app_env').value = 'development';
      if (get('db_host')) get('db_host').value = 'localhost';
      if (get('db_name')) get('db_name').value = presetDbName;
      if (get('db_user')) get('db_user').value = 'root';
      if (get('db_pass')) get('db_pass').value = '';
      if (get('admin_user')) get('admin_user').value = presetAdmin;

      document.querySelectorAll('#installForm input[name="migrations[]"]').forEach(function(box) {
        box.checked = true;
      });
    }

    /* ─────────────────────────────────────────────────────────────────────
       Init
    ───────────────────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function() {
      updateStepBar(1);

      <?php if (!empty($errors) && !$success): ?>
        // If form submitted with errors, jump to config step
        goStep(2);
      <?php endif; ?>

      <?php if ($success !== ''): ?>
        updateStepBar(3);
        // Auto-redirect handling
        try {
          var params = new URLSearchParams(window.location.search || '');
          if (params.get('autoredirect') === '1') {
            var go = params.get('go');
            if (go === 'admin') window.location.href = <?= json_encode(rtrim((string) $form['base_url'], '/') . '/api/login.php') ?>;
            else if (go === 'site') window.location.href = <?= json_encode(rtrim((string) $form['base_url'], '/') . '/') ?>;
          }
        } catch (e) {}
      <?php endif; ?>

      /* Particle background (desktop) */
      var isMobile = matchMedia('(max-width: 768px)').matches;
      var canvas = document.getElementById('particleCanvas');
      if (isMobile || !canvas) return;
      var ctx = canvas.getContext('2d');
      var DPR = Math.min(2, window.devicePixelRatio || 1);
      var w = 0,
        h = 0,
        particles = [];
      var PCOUNT = 40,
        MAXD = 130;

      function resize() {
        w = canvas.clientWidth;
        h = canvas.clientHeight;
        canvas.width = Math.floor(w * DPR);
        canvas.height = Math.floor(h * DPR);
        ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
      }

      function rnd(mn, mx) {
        return Math.random() * (mx - mn) + mn;
      }

      function init() {
        particles = Array.from({
          length: PCOUNT
        }).map(() => ({
          x: rnd(0, w),
          y: rnd(0, h),
          vx: rnd(-.28, .28),
          vy: rnd(-.28, .28),
          r: rnd(1.1, 2)
        }));
      }

      function step() {
        ctx.clearRect(0, 0, w, h);
        particles.forEach(function(a, i) {
          a.x += a.vx;
          a.y += a.vy;
          if (a.x < 0 || a.x > w) a.vx *= -1;
          if (a.y < 0 || a.y > h) a.vy *= -1;
          for (var j = i + 1; j < particles.length; j++) {
            var b = particles[j],
              dx = a.x - b.x,
              dy = a.y - b.y,
              d = Math.hypot(dx, dy);
            if (d < MAXD) {
              ctx.strokeStyle = 'rgba(99,102,241,' + (((1 - d / MAXD) * .3)) + ')';
              ctx.lineWidth = .8;
              ctx.beginPath();
              ctx.moveTo(a.x, a.y);
              ctx.lineTo(b.x, b.y);
              ctx.stroke();
            }
          }
        });
        particles.forEach(function(p) {
          ctx.fillStyle = 'rgba(165,180,252,.8)';
          ctx.beginPath();
          ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
          ctx.fill();
        });
        requestAnimationFrame(step);
      }
      window.addEventListener('resize', function() {
        resize();
        init();
      }, {
        passive: true
      });
      resize();
      init();
      step();
    });
  </script>

  <!-- Fundo em scroll: a lista completa e o gradiente vêm do PHP; o módulo só
       cuida de pré-carregar e fazer a transição. -->
  <script>
    window.DROP_SCROLL = <?= json_encode(
      Assets::scrollPayload($bgOverlay) + ['mode' => 'single', 'base' => '#db-master-bg'],
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) ?>;
  </script>
  <script src="<?= h(Assets::scrollBgJs()) ?>" defer></script>
</body>

</html>
<?php

/* ── Autodesativação, depois da resposta ──────────────────────────────────
   Renomear a pasta enquanto a página ainda está sendo montada quebrava a
   própria tela de sucesso. Aqui a resposta já foi entregue ao navegador; só
   então o instalador se tranca. A trava real é o storage/installed.lock (já
   gravado e checado no topo deste arquivo) — a renomeação é a segunda
   camada, e em servidores onde ela não é permitida a instalação continua
   protegida. */
if ($disableInstallerAfterRender) {
  if (function_exists('fastcgi_finish_request')) {
    @fastcgi_finish_request();
  } else {
    @ob_end_flush();
    @flush();
  }

  $installerDir = Paths::installerDir();
  $disabledDir  = $installerDir . '_disabled_' . date('YmdHis');

  if (!@rename($installerDir, $disabledDir)) {
    error_log(
      '[DropGenesis] Não foi possível renomear a pasta do instalador após a instalação. '
      . 'O acesso segue bloqueado por storage/installed.lock, mas remova ou renomeie a pasta manualmente.'
    );
  }
}
