========================================================================
DropGenesis — Universal Installation and Provisioning Engine
========================================================================
Developed by: Webyte Desenvolvimentos
Web: https://webytehub.com
Support: Envato Item Support Panel

Welcome to DropGenesis! This is a modern, modular, and premium
installation wizard engine for PHP applications.

------------------------------------------------------------------------
1. SYSTEM REQUIREMENTS
------------------------------------------------------------------------
- PHP >= 8.0 (PHP 8.2+ recommended)
- MySQL >= 5.7 or MariaDB >= 10.3
- Extensions: mysqli, curl, json
- Writable directories for installation state/config storage

------------------------------------------------------------------------
2. DIRECTORY STRUCTURE
------------------------------------------------------------------------
DropGenesis/
  ├── docs/            - Official offline documentation (HTML)
  ├── src/             - Core PHP files and drivers
  │   ├── Core/        - Engine, Paths, Assets, Database, License, ...
  │   ├── Css/         - Local font stylesheet (Geist)
  │   ├── Images/      - Logo, components and the scroll backgrounds
  │   └── Js/          - scroll-bg.js (shared animated background)
  ├── check/           - System diagnostics
  ├── update/          - Updater wizard
  ├── tools/           - CLI maintenance utilities
  ├── drop.json        - Application manifest configuration
  └── index.php        - Installer wizard index

------------------------------------------------------------------------
3. QUICK START INTEGRATION
------------------------------------------------------------------------
1. Copy the "DropGenesis/" folder to your project root.
2. Setup your application requirements in "DropGenesis/drop.json".
3. Place your base application SQL in "database/base.sql" (or as defined in drop.json).
4. Register the PSR-4 autoloader and require the Engine in your main index.php:

   require_once __DIR__ . '/DropGenesis/src/Core/EventDispatcher.php';
   require_once __DIR__ . '/DropGenesis/src/Core/Engine.php';
   $engine = new \DropGenesis\Core\Engine(__DIR__);

5. Open your browser and navigate to your website. The engine will
   automatically redirect to the installation wizard if not installed.

------------------------------------------------------------------------
3.1 LICENSE VERIFICATION
------------------------------------------------------------------------
The installer resolves its license verification endpoint in this order:

  1. Environment variable  DROP_LICENSE_URL
  2. drop.json             "license": { "verify_url": "..." }
  3. The local api/v1/verify.php — only when api/config.php already
     exists, since that endpoint needs an installed database.

When none of them resolves, the installer runs in OFFLINE mode: the key
is validated for format, recorded in config/local.php, and the screen
states plainly that it was NOT verified. It does not block installation,
because at install time there is no local database to verify against.

Set "verify_url" to your own licensing endpoint for a hard check. The
endpoint must accept POST (key, domain) and answer JSON:
    { "valid": true|false, "message": "..." }

TLS certificate verification is ON. Only for staging with a self-signed
certificate, set DROP_LICENSE_INSECURE_TLS=1.

------------------------------------------------------------------------
3.2 OPTIONAL MIGRATIONS
------------------------------------------------------------------------
Extra .sql files are declared under database.optional_migrations in
drop.json. Each entry only appears as a checkbox in the wizard when the
file actually exists on disk, so the installer never offers a migration
it cannot run.

------------------------------------------------------------------------
3.3 IMAGE OPTIMIZATION (recommended before shipping)
------------------------------------------------------------------------
    php DropGenesis/tools/optimize-images.php

Generates a .webp sibling next to each installer image. Originals are
kept and served untouched to browsers that do not announce WebP support,
so the visual result is identical either way. On the bundled artwork this
takes the installer assets from ~33 MB down to ~2 MB.

Run it again after replacing any background image.

------------------------------------------------------------------------
4. DOCUMENTATION
------------------------------------------------------------------------
Full documentation is included in HTML format. Open:
DropGenesis/docs/index.html

------------------------------------------------------------------------
5. SUPPORT & CUSTOMIZATION
------------------------------------------------------------------------
For bug reports, support tickets, and features requests, please open
a ticket through the Envato Market dashboard under your purchased items.
========================================================================
