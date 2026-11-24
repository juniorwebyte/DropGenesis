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

return [
    'welcome' => 'Welcome',
    'welcome_desc' => 'Let\'s install your app in a few steps.',
    'welcome_sub' => 'To start securely (database, base URL, admin, and migrations), use the automatic installer. It works on both VPS and XAMPP subfolders.',
    'requirements' => 'Requirements Verification',
    'requirements_desc' => 'Verify if your server meets the minimum requirements.',
    'req_met' => 'All requirements met. Ready to configure.',
    'req_unmet' => 'Some requirements were not met. Please fix them before proceeding.',
    'next' => 'Next',
    'configure' => 'System Configuration',
    'back' => 'Back',
    'db_config' => 'Database Configuration',
    'admin_config' => 'Administrative Configuration',
    'finish' => 'Finish',
    'license_verification' => 'License Verification',
    'license_code' => 'License Key',
    'license_desc' => "Enter your purchase key or development serial ('dev') to validate.",
    'db_host' => 'DB Host',
    'db_user' => 'DB User',
    'db_pass' => 'DB Password',
    'db_name' => 'DB Name',
    'admin_name' => 'Admin User',
    'admin_email' => 'Admin Email',
    'admin_pass' => 'Admin Password',
    'btn_verify_license' => 'Validate via API',
    'btn_test_db' => 'Test Connection & DB',
    'btn_install' => 'Start Installation',
    'btn_finish' => 'Finish and Access Core',
    'installation_completed' => 'Installation successfully completed!',
    'first_run' => 'First run detected',
    'guided_install' => 'Guided installation in :path',
    'next_step' => 'Next step',
    'start_install_desc' => 'Click the button below to open the installer.',
    'go_installer' => 'Go to Installer',
    'auto_configure' => 'Auto-Configure (Check)',
    'lock_hint' => 'Tip: after installation, the :path folder is automatically locked (except diagnostics).',
    'where_installing' => 'Where am I installing?',
    'base_detected' => 'Base detected:',
    'vps_hint' => 'If on a VPS, prefer the root of the domain. In XAMPP, a subfolder is normal.',
    'api_hub' => 'API Hub Infrastructure',
    'api_hub_desc' => 'Manage licenses, billing, auditing, and releases in a centralized way.',
    'access_api' => 'Access API Dashboard',
    'api_installed' => 'API Hub: Installed (Access)',
    'standalone_hint' => 'Standalone Environment: No front-end application detected in the root.',

    // Configuration step keys
    'btn_health_check' => 'View full Health Check',
    'btn_back_core' => 'Back to Core',
    'local_install' => 'Local installation?',
    'local_install_desc' => 'Click to automatically fill with default XAMPP configurations (localhost, root with no password).',
    'btn_use_xampp' => 'Use XAMPP (localhost)',
    'subfolder_detected' => 'Subfolder installation detected:',
    'subfolder_warn' => 'It is recommended to install on the domain root. Check the confirmation below if you wish to proceed anyway.',
    'system_base_url' => 'System Base URL',
    'auto_detected' => 'Automatically detected — project in subfolder',
    'url_locked_hint' => 'URL locked to prevent broken links.',
    'auto_detected_hint' => 'Detected: :url — you can change manually if needed.',
    'confirm_subfolder' => 'I confirm that I want to install in <code>:path</code> anyway.',
    'confirm_subfolder_production' => 'Not recommended for production environments',
    'environment' => 'Environment',
    'site_name' => 'Site Name',
    'db_pass_placeholder' => '(blank for XAMPP root)',
    'admin_pass_placeholder' => 'Minimum 8 characters',
    'admin_pass_confirm' => 'Confirm Admin Password',
    'admin_pass_confirm_placeholder' => 'Repeat password',
    'apply_hardening' => 'Apply performance and security migration (hardening)',
    'apply_hardening_sub' => 'Recommended for production environments',
    'apply_schema' => 'Apply SQL structuring (indexes + DECIMAL + schema adjustments)',
    'apply_schema_sub' => 'Recommended: syncs database with code hardening',
    'apply_phase2' => 'Apply Phase 2 (quota normalization with <code>order_number_list</code>)',
    'apply_phase2_sub' => 'Enables uniqueness by product+quota in DB (anti-duplication)',
    'btn_install_now' => 'Install Now',
    'installing' => 'Installing...',
    'installing_desc' => 'Please wait while we configure the database and system.',
    'installer_security_note' => 'WebyteHub Installer — This installer must be removed after installation for security reasons.',

    // Lock
    'locked_title' => 'Installer locked',
    'locked_desc' => 'The application has already been installed. For security, the installer remains locked.',
    'post_check_diagnostics' => 'Post-installation diagnostics',
    'health_check' => 'Health check',
    'admin_panel' => 'Admin panel',
    'reinstall_hint' => 'To reinstall, remove <code>storage/installed.lock</code> and <code>config/local.php</code>.',
    'reinstall_title' => 'To reinstall',
    'reinstall_intro' => 'Remove the files below. They are what marks this installation as complete:',
    'reinstall_db_warning' => 'The database is not dropped along with them. For a clean install, use an empty database or remove the existing tables — the installer refuses to write over a database in use.',
    'reinstall_none_found' => 'No installation files were found at the known paths. Check read permissions on the project folder.',

    // Diagnostic keys
    'health_check_title' => 'Server Health Check',
    'health_check_meta_desc' => 'Verify if your environment meets all requirements',
    'system_reqs' => 'System Requirements',
    'installed_security_warn' => 'Installation already completed. For security, use :link and remove the :dir folder.',
    'ready_to_install' => 'Ready to install.',
    'blocks_found' => 'Blocks were found. Please fix red items before installing.',
    'btn_back_installer' => 'Back to Installer',
    'btn_post_diagnostics' => 'Post-installation diagnostics',
    'installer_security_remove_note' => 'WebyteHub — remove the :path folder after installation.',
    'post_check_title' => 'Post-Installation Diagnostics',
    'post_check_desc' => 'Verify environment integrity after installation',

    // Audit & Master Sync keys
    'audit_title' => 'DROP Health Audit',
    'audit_desc' => 'Ecosystem & Routes Analysis',
    'audit_htaccess_ok' => '[OK] .htaccess file configured in root.',
    'audit_htaccess_warn' => '[WARNING] .htaccess file not found in root.',
    'audit_gateway_critical' => '[CRITICAL] Root index.php does not contain Drop ecosystem.',
    'audit_gateway_ok' => '[OK] Root index.php gateway configured correctly.',
    'scanning_directories' => 'Scanning directories...',
    'project_detected' => 'Project detected: :project',
    'auto_configure_btn' => 'Auto-Configure',
    'permissions_title' => 'Permissions',
    'btn_installer' => 'Installer',
    'update_central' => 'Update Center',
    'access' => 'Access',
    'sync' => 'Sync',
    'success' => 'Success',
    'unlock_system' => 'Unlock System',
    'license_placeholder' => 'LICENSE-WEBYTEHUB',
    'sync_required' => 'Sync Required',
    'ecosystem_active' => 'Ecosystem Active',
    'all_ready' => 'All Ready!',
    'update_success_desc' => 'Your WebyteHub ecosystem is fully synced and optimized across all instances.',
    'back_to_panel' => 'Back to Panel',
    'view_logs' => 'View Logs',
    'disconnect' => 'Disconnect',
    'check_updates' => 'Check for Updates',
    'safety_snapshot' => 'Safety Snapshot Engine',
    'protection_active' => 'Active Protection',
    'safety_desc' => 'A restore point will be created automatically before sync. In case of anomaly, you can roll back the entire system to the current state in less than 30 seconds.',
    'btn_view_snapshots' => 'View last snapshots',
    'btn_integrity_validated' => 'Integrity validated',

    // index.php additions
    'subtitle_text' => 'Microservices activation & synchronization engine',
    'symphony_active' => 'Symphony Active',
    'details' => 'Details',

    // post-check additions
    'env_status' => 'Environment Status',
    'attention_points' => 'Attention points were found. Please correct red items.',
    'item' => 'Item',
    'how_to_fix' => 'How to Fix',
    'final_recommendations' => 'Final Recommendations',

    'fail' => 'FAIL',
    'extension' => 'Extension',
    'write_permission' => 'Write permission on',
    'sql_base_file' => 'Base SQL File',

    // post-check dynamic checks
    'check_lock_exists' => 'Installation detected (installed.lock)',
    'not_found' => 'Not found',
    'fix_installed_lock' => 'Finish the installation at /DropGenesis/index.php',
    'check_config_exists' => 'config/local.php exists',
    'fix_config_local' => 'Reinstall or generate config/local.php using the installer.',
    'check_storage_write' => 'Write permission on /storage',
    'fix_write_storage' => 'Adjust folder permissions (chmod 755 or via hosting control panel).',
    'check_logs_write' => 'Write permission on /storage/logs',
    'fix_write_logs' => 'Adjust folder permissions.',
    'check_root_htaccess' => '.htaccess in root exists',
    'fix_root_htaccess' => 'Upload .htaccess file to project root.',
    'check_drop_htaccess' => '.htaccess in /DropGenesis exists',
    'fix_drop_htaccess' => 'DropGenesis/.htaccess blocks access after installation.',
    'check_mod_rewrite' => 'mod_rewrite active (Apache)',
    'detect_warn_htaccess' => 'Not detectable automatically (normal on Nginx or Apache without apache_get_modules).',
    'active' => 'Active',
    'disabled' => 'Disabled',
    'fix_mod_rewrite' => 'On XAMPP: enable mod_rewrite and AllowOverride All in httpd.conf.',
    'check_base_url_consist' => 'BASE_URL matches folder path',
    'detect_base_url_details' => 'Detected: :det | Configured: :conf',
    'fix_base_url' => 'Reinstall or edit APP_BASE_URL in config/local.php.',
    'check_db_conn' => 'Database connection',
    'db_conn_failed' => 'Connection failed (:err).',
    'db_test_error' => 'Error testing database: :err',
    'db_credentials_missing' => 'Database credentials not found in config/local.php.',
    'fix_db_conn' => 'Verify DB_* in installer and config/local.php.',
    'check_key_tables' => 'Key tables present',
    'key_tables_ok' => 'All key tables were found.',
    'key_tables_missing' => 'Missing: :missing',
    'db_not_connected' => 'DB not connected.',
    'fix_key_tables' => 'Reinstall on an empty database and run schema SQL.',
    'check_quota_uniqueness' => 'Quota uniqueness (order_number_list)',
    'quota_uniqueness_ok' => 'Unique index uniq_product_lucky_number active.',
    'quota_uniqueness_missing' => 'Unique index missing/incomplete in order_number_list.',
    'dep_conn_tables' => 'Dependent on database connection/tables.',
    'fix_quota_uniqueness' => 'Apply migration 2026_04_06_phase2_normalization_order_numbers.sql.',
    'check_status_column' => 'Status column in order_number_list',
    'status_column_ok' => 'Status column found in order_number_list.',
    'status_column_missing' => 'Status column missing in order_number_list (inconsistency with migrations/runtime bug).',
    'fix_status_column' => 'Adjust migration/hardening to create order_number_list.status (e.g. 2026_03_30_hardening.sql).',

    // Recommendations & titles
    'env_validated' => 'Environment validated. Ready for operation.',
    'url_detected' => 'URL Detected',
    'url_configured' => 'URL Configured',
    'admin_access' => 'Admin Access',
    'php_error_log' => 'PHP Error Log',
    'rec_remove_folder' => 'Remove the <code>/DropGenesis</code> folder after completing the installation.',
    'rec_https' => 'Enable HTTPS in production and ensure <code>APP_BASE_URL</code> matches your domain.',
    'rec_payment_errors' => 'If payment/webhook errors occur, check <code>admin/?page=logs</code> and <code>storage/logs/php-error.log</code>.',
    'rec_backup' => 'Perform periodic backups of the database and <code>config/local.php</code> and <code>storage/</code> files.',
    'rec_vps_perms' => 'In VPS environments, set permissions to <code>644</code> for files and <code>755</code> for folders.',
    'refresh' => 'Refresh',

    // Health check specific translations
    'additional_structure_file' => 'Additional Structure (structure.sql)',
    'phase2_normalization_file' => 'Normalization Phase 2 (phase2.sql)',
    'optional_but_recommended' => 'Optional but recommended',
    'optional_not_found' => 'Optional (not found)',
];
