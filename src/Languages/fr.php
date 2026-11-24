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
    'welcome' => 'Bienvenue',
    'welcome_desc' => 'Installons votre application en quelques étapes.',
    'welcome_sub' => 'Pour démarrer en toute sécurité (base de données, URL de base, administrateur et migrations), utilisez l\'installateur automatique. Il fonctionne aussi bien sur VPS que dans les sous-dossiers XAMPP.',
    'requirements' => 'Vérification des Prérequis',
    'requirements_desc' => 'Vérifiez si votre serveur répond aux prérequis minimaux.',
    'req_met' => 'Tous les prérequis sont satisfaits. Prêt à configurer.',
    'req_unmet' => 'Certains prérequis ne sont pas satisfaits. Veuillez les corriger avant de continuer.',
    'next' => 'Suivant',
    'configure' => 'Configuration du Système',
    'back' => 'Retour',
    'db_config' => 'Configuration de la Base de Données',
    'admin_config' => 'Configuration Administrative',
    'finish' => 'Finalisation',
    'license_verification' => 'Vérification de la Licence',
    'license_code' => 'Clé de Licence',
    'license_desc' => "Entrez votre clé d'achat ou votre numéro de série de développement ('dev') pour valider.",
    'db_host' => 'DB Host',
    'db_user' => 'DB Utilisateur',
    'db_pass' => 'DB Mot de passe',
    'db_name' => 'DB Nom',
    'admin_name' => 'Admin Utilisateur',
    'admin_email' => 'Admin E-mail',
    'admin_pass' => 'Mot de passe Admin',
    'btn_verify_license' => 'Valider via API',
    'btn_test_db' => 'Tester Connexion & BD',
    'btn_install' => 'Démarrer l\'Installation',
    'btn_finish' => 'Terminer et Accéder au Core',
    'installation_completed' => 'Installation réussie !',
    'first_run' => 'Première exécution détectée',
    'guided_install' => 'Installation guidée dans :path',
    'next_step' => 'Étape suivante',
    'start_install_desc' => 'Cliquez le bouton ci-dessous pour ouvrir l\'installateur.',
    'go_installer' => 'Aller à l\'Installateur',
    'auto_configure' => 'Auto-Configurer (Check)',
    'lock_hint' => 'Astuce : après l\'installation, le dossier :path est automatiquement verrouillé (sauf diagnostics).',
    'where_installing' => 'Où est-ce que j\'installe ?',
    'base_detected' => 'Base détectée :',
    'vps_hint' => 'Si vous êtes sur un VPS, préférez la racine du domaine. Sur XAMPP, un sous-dossier est normal.',
    'api_hub' => 'Infrastructure API Hub',
    'api_hub_desc' => 'Gérez les licences, la facturation, l\'audit et les versions de manière centralisée.',
    'access_api' => 'Accéder au Tableau de Bord de l\'API',
    'api_installed' => 'API Hub : Installé (Accéder)',
    'standalone_hint' => 'Environnement Standalone : Aucune application front-end détectée à la racine.',

    // Configuration step keys
    'btn_health_check' => 'Voir Health Check complet',
    'btn_back_core' => 'Retourner au Core',
    'local_install' => 'Installation locale ?',
    'local_install_desc' => 'Cliquez pour remplir automatiquement avec les configurations XAMPP par défaut (localhost, root sans mot de passe).',
    'btn_use_xampp' => 'Utiliser XAMPP (localhost)',
    'subfolder_detected' => 'Installation dans un sous-dossier détectée :',
    'subfolder_warn' => 'Il est recommandé d\'installer à la racine du domaine. Cochez la confirmation ci-dessous si vous souhaitez continuer quand même.',
    'system_base_url' => 'URL de Base du Système',
    'auto_detected' => 'Détecté automatiquement — projet dans un sous-dossier',
    'url_locked_hint' => 'URL verrouillée pour éviter les liens brisés.',
    'auto_detected_hint' => 'Détecté : :url — vous pouvez modifier manuellement si nécessaire.',
    'confirm_subfolder' => 'Je confirme vouloir installer dans <code>:path</code> quand même.',
    'confirm_subfolder_production' => 'Non recommandé pour les environnements de production',
    'environment' => 'Environnement',
    'site_name' => 'Nom du Site',
    'db_pass_placeholder' => '(vide pour root XAMPP)',
    'admin_pass_placeholder' => 'Minimum 8 caractères',
    'admin_pass_confirm' => 'Confirmer le Mot de passe Admin',
    'admin_pass_confirm_placeholder' => 'Répétez le mot de passe',
    'apply_hardening' => 'Appliquer la migration de performance et de sécurité (endurcissement)',
    'apply_hardening_sub' => 'Recommandé pour les environnements de production',
    'apply_schema' => 'Appliquer la structuration SQL (index + DECIMAL + ajustements de schéma)',
    'apply_schema_sub' => 'Recommandé : synchronise la base de données avec l\'endurcissement du code',
    'apply_phase2' => 'Appliquer la Phase 2 (normalisation des quotas avec <code>order_number_list</code>)',
    'apply_phase2_sub' => 'Active l\'unicité par produit+quota dans la base de données (anti-duplication)',
    'btn_install_now' => 'Installer Maintenant',
    'installing' => 'Installation...',
    'installing_desc' => 'Veuillez patienter pendant la configuration de la base de données et du système.',
    'installer_security_note' => 'WebyteHub Installer — Cet installateur doit être supprimé après l\'installation pour des raisons de sécurité.',

    // Lock
    'locked_title' => 'Installateur verrouillé',
    'locked_desc' => 'L\'application a déjà été installée. Pour sécurité, l\'installateur reste verrouillé.',
    'post_check_diagnostics' => 'Diagnostics post-installation',
    'health_check' => 'Bilan de santé',
    'admin_panel' => 'Panneau d\'administration',
    'reinstall_hint' => 'Pour réinstaller, supprimez <code>storage/installed.lock</code> et <code>config/local.php</code>.',
    'reinstall_title' => 'Pour réinstaller',
    'reinstall_intro' => 'Supprimez les fichiers ci-dessous. Ce sont eux qui marquent cette installation comme terminée :',
    'reinstall_db_warning' => 'La base de données n\'est pas supprimée avec eux. Pour une installation propre, utilisez une base vide ou supprimez les tables existantes — l\'installateur refuse d\'écrire sur une base en cours d\'utilisation.',
    'reinstall_none_found' => 'Aucun fichier d\'installation trouvé aux emplacements connus. Vérifiez les droits de lecture du dossier du projet.',

    // Diagnostic keys
    'health_check_title' => 'Bilan de Santé du Serveur',
    'health_check_meta_desc' => 'Vérifiez si l\'environnement répond à toutes les exigences',
    'system_reqs' => 'Exigences du Système',
    'installed_security_warn' => 'Installation déjà terminée. Par sécurité, utilisez :link et supprimez le dossier :dir.',
    'ready_to_install' => 'Tout est prêt pour l\'installation.',
    'blocks_found' => 'Des blocages ont été trouvés. Veuillez corriger les éléments en rouge avant d\'installer.',
    'btn_back_installer' => 'Retour à l\'Installateur',
    'btn_post_diagnostics' => 'Diagnostics post-installation',
    'installer_security_remove_note' => 'WebyteHub — supprimez le dossier :path après l\'installation.',
    'post_check_title' => 'Diagnostics Post-Installation',
    'post_check_desc' => 'Vérifiez l\'intégrité de l\'environnement après l\'installation',

    // Audit & Master Sync keys
    'audit_title' => 'DROP Health Audit',
    'audit_desc' => 'Analyse de l\'Écosystème & des Routes',
    'audit_htaccess_ok' => '[OK] Fichier .htaccess configuré à la racine.',
    'audit_htaccess_warn' => '[WARNING] Fichier .htaccess introuvable à la racine.',
    'audit_gateway_critical' => '[CRITICAL] Le fichier index.php à la racine ne contient pas l\'écosystème Drop.',
    'audit_gateway_ok' => '[OK] Passerelle index.php à la racine configurée correctement.',
    'scanning_directories' => 'Analyse des répertoires...',
    'project_detected' => 'Projet détecté : :project',
    'auto_configure_btn' => 'Auto-Configurer',
    'permissions_title' => 'Autorisations',
    'btn_installer' => 'Installateur',
    'update_central' => 'Centre des Mises à Jour',
    'access' => 'Accès',
    'sync' => 'Synchronisation',
    'success' => 'Succès',
    'unlock_system' => 'Déverrouiller le Système',
    'license_placeholder' => 'LICENCE-WEBYTEHUB',
    'sync_required' => 'Sync Requise',
    'ecosystem_active' => 'Écosystème Actif',
    'all_ready' => 'Tout est Prêt !',
    'update_success_desc' => 'Votre écosystème WebyteHub est entièrement synchronisé et optimisé sur toutes les instances.',
    'back_to_panel' => 'Retour au Panneau',
    'view_logs' => 'Voir les Logs',
    'disconnect' => 'Se déconnecter',
    'check_updates' => 'Rechercher des Mises à Jour',
    'safety_snapshot' => 'Safety Snapshot Engine',
    'protection_active' => 'Protection Active',
    'safety_desc' => 'Un point de restauration sera créé automatiquement avant la synchronisation. En cas d\'anomalie, vous pouvez restaurer l\'intégralité du système à son état actuel en moins de 30 secondes.',
    'btn_view_snapshots' => 'Voir les derniers snapshots',
    'btn_integrity_validated' => 'Intégrité validée',

    // index.php additions
    'subtitle_text' => 'Moteur d\'activation et de synchronisation des microservices',
    'symphony_active' => 'Symphonie Active',
    'details' => 'Détails',

    // post-check additions
    'env_status' => 'Statut de l\'Environnement',
    'attention_points' => 'Des points d\'attention ont été trouvés. Veuillez corriger les éléments en rouge.',
    'item' => 'Élément',
    'how_to_fix' => 'Comment Corriger',
    'final_recommendations' => 'Recommandations Finales',

    'fail' => 'ÉCHEC',
    'extension' => 'Extension',
    'write_permission' => 'Écriture sur',
    'sql_base_file' => 'Fichier SQL de base',

    // post-check dynamic checks
    'check_lock_exists' => 'Installation détectée (installed.lock)',
    'not_found' => 'Non trouvé',
    'fix_installed_lock' => 'Terminez l\'installation sur /DropGenesis/index.php',
    'check_config_exists' => 'config/local.php existe',
    'fix_config_local' => 'Réinstallez ou générez config/local.php avec l\'installateur.',
    'check_storage_write' => 'Écriture sur /storage',
    'fix_write_storage' => 'Ajustez les autorisations du dossier (chmod 755 ou via le panneau de contrôle de l\'hébergeur).',
    'check_logs_write' => 'Écriture sur /storage/logs',
    'fix_write_logs' => 'Ajustez les autorisations du dossier.',
    'check_root_htaccess' => '.htaccess à la racine existe',
    'fix_root_htaccess' => 'Téléversez le fichier .htaccess à la racine du projet.',
    'check_drop_htaccess' => '.htaccess dans /DropGenesis existe',
    'fix_drop_htaccess' => 'DropGenesis/.htaccess bloque l\'accès après l\'installation.',
    'check_mod_rewrite' => 'mod_rewrite actif (Apache)',
    'detect_warn_htaccess' => 'Non détectable automatiquement (normal sur Nginx ou Apache sans apache_get_modules).',
    'active' => 'Actif',
    'disabled' => 'Désactivé',
    'fix_mod_rewrite' => 'Sur XAMPP : activez mod_rewrite et AllowOverride All dans httpd.conf.',
    'check_base_url_consist' => 'BASE_URL correspond au chemin',
    'detect_base_url_details' => 'Détecté : :det | Configuré : :conf',
    'fix_base_url' => 'Réinstallez ou modifiez APP_BASE_URL dans config/local.php.',
    'check_db_conn' => 'Connexion à la base de données',
    'db_conn_failed' => 'Échec de la connexion (:err).',
    'db_test_error' => 'Erreur lors du test de la BD : :err',
    'db_credentials_missing' => 'Identifiants de BD introuvables dans config/local.php.',
    'fix_db_conn' => 'Vérifiez DB_* dans l\'installateur et dans config/local.php.',
    'check_key_tables' => 'Tables clés présentes',
    'key_tables_ok' => 'Toutes les tables clés ont été trouvées.',
    'key_tables_missing' => 'Manquant : :missing',
    'db_not_connected' => 'BD non connectée.',
    'fix_key_tables' => 'Réinstallez sur une base de données vide et exécutez le SQL du schéma.',
    'check_quota_uniqueness' => 'Unicité des quotas (order_number_list)',
    'quota_uniqueness_ok' => 'Index unique uniq_product_lucky_number actif.',
    'quota_uniqueness_missing' => 'Index unique manquant/incomplet dans order_number_list.',
    'dep_conn_tables' => 'Dépendant de la connexion/tables.',
    'fix_quota_uniqueness' => 'Appliquez la migration 2026_04_06_phase2_normalization_order_numbers.sql.',
    'check_status_column' => 'Colonne status dans order_number_list',
    'status_column_ok' => 'Colonne status trouvée dans order_number_list.',
    'status_column_missing' => 'Colonne status manquante dans order_number_list (incohérence avec les migrations/erreur de runtime).',
    'fix_status_column' => 'Ajustez la migration/durcissement pour créer order_number_list.status (par exemple, 2026_03_30_hardening.sql).',

    // Recommendations & titles
    'env_validated' => 'Environnement validé. Prêt pour l\'opération.',
    'url_detected' => 'URL Détectée',
    'url_configured' => 'URL Configurée',
    'admin_access' => 'Accès Admin',
    'php_error_log' => 'Journal d\'erreurs PHP',
    'rec_remove_folder' => 'Supprimez le dossier <code>/DropGenesis</code> après avoir terminé l\'installation.',
    'rec_https' => 'Activez le protocole HTTPS en production et assurez-vous que <code>APP_BASE_URL</code> correspond à votre domaine.',
    'rec_payment_errors' => 'Si des erreurs de paiement/webhook surviennent, vérifiez <code>admin/?page=logs</code> et <code>storage/logs/php-error.log</code>.',
    'rec_backup' => 'Sauvegardez périodiquement la base de données et les fichiers <code>config/local.php</code> et <code>storage/</code>.',
    'rec_vps_perms' => 'En environnement VPS, définissez les autorisations à <code>644</code> pour les fichiers et <code>755</code> pour les dossiers.',
    'refresh' => 'Rafraîchir',

    // Health check specific translations
    'additional_structure_file' => 'Structure Additionnelle (structure.sql)',
    'phase2_normalization_file' => 'Normalisation Phase 2 (phase2.sql)',
    'optional_but_recommended' => 'Optionnel mais recommandé',
    'optional_not_found' => 'Optionnel (non trouvé)',
];
