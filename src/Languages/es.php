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
    'welcome' => 'Bienvenido',
    'welcome_desc' => 'Vamos a instalar su aplicación en unos pocos pasos.',
    'welcome_sub' => 'Para comenzar de manera segura (base de datos, URL base, administración y migraciones), use el instalador automático. Funciona tanto en VPS como en subcarpetas de XAMPP.',
    'requirements' => 'Verificación de Requisitos',
    'requirements_desc' => 'Verifique si su servidor cumple con los requisitos mínimos.',
    'req_met' => 'Todos los requisitos cumplidos. Listo para configurar.',
    'req_unmet' => 'Algunos requisitos no se cumplieron. Por favor corríjalos antes de continuar.',
    'next' => 'Siguiente',
    'configure' => 'Configuración del Sistema',
    'back' => 'Volver',
    'db_config' => 'Configuración de Base de Datos',
    'admin_config' => 'Configuración Administrativa',
    'finish' => 'Finalización',
    'license_verification' => 'Verificación de Licencia',
    'license_code' => 'Clave de Licencia',
    'license_desc' => "Ingrese su clave de compra o serie de desarrollo ('dev') para validar.",
    'db_host' => 'DB Host',
    'db_user' => 'DB Usuario',
    'db_pass' => 'DB Contraseña',
    'db_name' => 'DB Nombre',
    'admin_name' => 'Admin Usuario',
    'admin_email' => 'Admin Correo',
    'admin_pass' => 'Contraseña Admin',
    'btn_verify_license' => 'Validar vía API',
    'btn_test_db' => 'Probar Conexión y BD',
    'btn_install' => 'Iniciar Instalación',
    'btn_finish' => 'Finalizar y Acceder al Core',
    'installation_completed' => '¡Instalación completada con éxito!',
    'first_run' => 'Primera ejecución detectada',
    'guided_install' => 'Instalación guiada en :path',
    'next_step' => 'Próximo paso',
    'start_install_desc' => 'Haga clic en el botón de abajo para abrir el instalador.',
    'go_installer' => 'Ir al Instalador',
    'auto_configure' => 'Auto-Configurar (Check)',
    'lock_hint' => 'Sugerencia: después de la instalación, la carpeta :path se bloquea automáticamente (excepto diagnóstico).',
    'where_installing' => '¿Dónde estoy instalando?',
    'base_detected' => 'Base detectada:',
    'vps_hint' => 'Si está en VPS, prefiera la raíz del dominio. En XAMPP, una subcarpeta es normal.',
    'api_hub' => 'Infraestructura API Hub',
    'api_hub_desc' => 'Administre licencias, facturación, auditoría y lanzamientos de forma centralizada.',
    'access_api' => 'Acceder al Panel de la API',
    'api_installed' => 'API Hub: Instalado (Acceder)',
    'standalone_hint' => 'Entorno Standalone: No se detectó ninguna aplicación front-end en la raíz.',

    // Configuration step keys
    'btn_health_check' => 'Ver Health Check completo',
    'btn_back_core' => 'Volver al Core',
    'local_install' => '¿Instalación local?',
    'local_install_desc' => 'Haga clic para completar automáticamente con la configuración predeterminada de XAMPP (localhost, root sin contraseña).',
    'btn_use_xampp' => 'Usar XAMPP (localhost)',
    'subfolder_detected' => 'Instalación en subcarpeta detectada:',
    'subfolder_warn' => 'Se recomienda instalar en la raíz del dominio. Marque la de confirmación a continuación si desea continuar de todos modos.',
    'system_base_url' => 'URL Base del Sistema',
    'auto_detected' => 'Detectado automáticamente — proyecto en subcarpeta',
    'url_locked_hint' => 'URL bloqueada para evitar enlaces rotos.',
    'auto_detected_hint' => 'Detectado: :url — puede cambiarlo manualmente si es necesario.',
    'confirm_subfolder' => 'Confirmo que deseo instalar en <code>:path</code> de todos modos.',
    'confirm_subfolder_production' => 'No recomendado para entornos de producción',
    'environment' => 'Ambiente',
    'site_name' => 'Nombre del Sitio',
    'db_pass_placeholder' => '(en blanco para root XAMPP)',
    'admin_pass_placeholder' => 'Mínimo 8 caracteres',
    'admin_pass_confirm' => 'Confirmar Contraseña Admin',
    'admin_pass_confirm_placeholder' => 'Repita la contraseña',
    'apply_hardening' => 'Aplicar migración de rendimiento y seguridad (hardening)',
    'apply_hardening_sub' => 'Recomendado para entornos de producción',
    'apply_schema' => 'Aplicar estructuración SQL (índices + DECIMAL + ajustes de esquema)',
    'apply_schema_sub' => 'Recomendado: sincroniza base de datos con endurecimiento de código',
    'apply_phase2' => 'Aplicar Fase 2 (normalización de cuotas con <code>order_number_list</code>)',
    'apply_phase2_sub' => 'Habilita la unicidad por producto+cuota en la base de datos (anti-duplicación)',
    'btn_install_now' => 'Instalar Ahora',
    'installing' => 'Instalando...',
    'installing_desc' => 'Por favor espere mientras configuramos la base de datos y el sistema.',
    'installer_security_note' => 'WebyteHub Installer — Este instalador debe retirarse después de la instalación por razones de seguridad.',

    // Lock
    'locked_title' => 'Instalador bloqueado',
    'locked_desc' => 'La aplicación ya ha sido instalada. Por seguridad, el instalador permanece bloqueado.',
    'post_check_diagnostics' => 'Diagnóstico post-instalación',
    'health_check' => 'Chequeo de salud',
    'admin_panel' => 'Panel de administración',
    'reinstall_hint' => 'Para reinstalar, elimine <code>storage/installed.lock</code> e <code>config/local.php</code>.',
    'reinstall_title' => 'Para reinstalar',
    'reinstall_intro' => 'Elimine los archivos siguientes. Son los que marcan esta instalación como completada:',
    'reinstall_db_warning' => 'La base de datos no se elimina con ellos. Para una instalación limpia, use una base vacía o elimine las tablas existentes — el instalador se niega a escribir sobre una base en uso.',
    'reinstall_none_found' => 'No se encontraron archivos de instalación en las rutas conocidas. Verifique los permisos de lectura de la carpeta del proyecto.',

    // Diagnostic keys
    'health_check_title' => 'Chequeo de Salud del Servidor',
    'health_check_meta_desc' => 'Verifique si el entorno cumple con todos los requisitos',
    'system_reqs' => 'Requisitos del Sistema',
    'installed_security_warn' => 'Instalação já concluída. Por segurança, use :link e remova a pasta :dir.',
    'ready_to_install' => 'Todo listo para instalar.',
    'blocks_found' => 'Se encontraron bloqueos. Corrija los elementos rojos antes de instalar.',
    'btn_back_installer' => 'Volver al Instalador',
    'btn_post_diagnostics' => 'Diagnóstico post-instalación',
    'installer_security_remove_note' => 'WebyteHub — elimine la carpeta :path después de la instalación.',
    'post_check_title' => 'Diagnóstico Post-Instalación',
    'post_check_desc' => 'Verifique la integridad del entorno después de la instalación',

    // Audit & Master Sync keys
    'audit_title' => 'DROP Health Audit',
    'audit_desc' => 'Análisis de Ecosistema & Rutas',
    'audit_htaccess_ok' => '[OK] Archivo .htaccess configurado en la raíz.',
    'audit_htaccess_warn' => '[WARNING] Archivo .htaccess no encontrado en la raíz.',
    'audit_gateway_critical' => '[CRITICAL] El index.php de la raíz no posee el ecosistema Drop.',
    'audit_gateway_ok' => '[OK] Gateway index.php raíz configurado correctamente.',
    'scanning_directories' => 'Escaneando directorios...',
    'project_detected' => 'Proyecto detectado: :project',
    'auto_configure_btn' => 'Auto-Configurar',
    'permissions_title' => 'Permisos',
    'btn_installer' => 'Instalador',
    'update_central' => 'Central de Actualizaciones',
    'access' => 'Acceso',
    'sync' => 'Sincronía',
    'success' => 'Éxito',
    'unlock_system' => 'Desbloquear Sistema',
    'license_placeholder' => 'LICENCIA-WEBYTEHUB',
    'sync_required' => 'Sync Requerido',
    'ecosystem_active' => 'Ecosistema Activo',
    'all_ready' => '¡Todo Listo!',
    'update_success_desc' => 'Su ecosistema WebyteHub está totalmente sincronizado y optimizado en todas las instancias.',
    'back_to_panel' => 'Volver al Panel',
    'view_logs' => 'Ver Logs',
    'disconnect' => 'Desconectar',
    'check_updates' => 'Buscar Actualizaciones',
    'safety_snapshot' => 'Safety Snapshot Engine',
    'protection_active' => 'Protección Activa',
    'safety_desc' => 'Se creará automáticamente un punto de restauración antes de la sincronización. En caso de anomalía, podrá revertir todo el sistema al estado actual en menos de 30 segundos.',
    'btn_view_snapshots' => 'Ver últimos snapshots',
    'btn_integrity_validated' => 'Integridad validada',

    // index.php additions
    'subtitle_text' => 'Motor de activación y sincronia de microservicios',
    'symphony_active' => 'Sintonía Activa',
    'details' => 'Detalles',

    // post-check additions
    'env_status' => 'Estado del Entorno',
    'attention_points' => 'Se encontraron puntos de atención. Corrija los elementos en rojo.',
    'item' => 'Elemento',
    'how_to_fix' => 'Cómo Corregir',
    'final_recommendations' => 'Recomendaciones Finales',

    'fail' => 'FALLO',
    'extension' => 'Extensión',
    'write_permission' => 'Escritura en',
    'sql_base_file' => 'Archivo SQL Base',

    // post-check dynamic checks
    'check_lock_exists' => 'Instalación detectada (installed.lock)',
    'not_found' => 'No encontrado',
    'fix_installed_lock' => 'Finalice la instalación en /DropGenesis/index.php',
    'check_config_exists' => 'config/local.php existe',
    'fix_config_local' => 'Reinstale o genere config/local.php con el instalador.',
    'check_storage_write' => 'Escritura en /storage',
    'fix_write_storage' => 'Ajuste permisos de la carpeta (chmod 755 o mediante panel de control).',
    'check_logs_write' => 'Escritura en /storage/logs',
    'fix_write_logs' => 'Ajuste permisos de la carpeta.',
    'check_root_htaccess' => '.htaccess en la raíz existe',
    'fix_root_htaccess' => 'Suba el archivo .htaccess a la raíz del proyecto.',
    'check_drop_htaccess' => '.htaccess en /DropGenesis existe',
    'fix_drop_htaccess' => 'DropGenesis/.htaccess bloquea el acceso después de la instalación.',
    'check_mod_rewrite' => 'mod_rewrite activo (Apache)',
    'detect_warn_htaccess' => 'No detectable automáticamente (normal en Nginx o Apache sin apache_get_modules).',
    'active' => 'Activo',
    'disabled' => 'Desactivado',
    'fix_mod_rewrite' => 'En XAMPP: habilite mod_rewrite y AllowOverride All en httpd.conf.',
    'check_base_url_consist' => 'BASE_URL coincide con la ruta',
    'detect_base_url_details' => 'Detectado: :det | Configurado: :conf',
    'fix_base_url' => 'Reinstale o edite APP_BASE_URL en config/local.php.',
    'check_db_conn' => 'Conexión a la base de datos',
    'db_conn_failed' => 'Fallo de conexión (:err).',
    'db_test_error' => 'Error al probar la BD: :err',
    'db_credentials_missing' => 'Credenciales de BD no encontradas en config/local.php.',
    'fix_db_conn' => 'Verifique DB_* en el instalador y en config/local.php.',
    'check_key_tables' => 'Tablas clave presentes',
    'key_tables_ok' => 'Se encontraron todas las tablas clave.',
    'key_tables_missing' => 'Faltante: :missing',
    'db_not_connected' => 'BD no conectada.',
    'fix_key_tables' => 'Reinstale en una base de datos vacía y ejecute el SQL de esquema.',
    'check_quota_uniqueness' => 'Unicidad de cuotas (order_number_list)',
    'quota_uniqueness_ok' => 'Índice único uniq_product_lucky_number activo.',
    'quota_uniqueness_missing' => 'Índice único ausente/incompleto en order_number_list.',
    'dep_conn_tables' => 'Dependente de conexión/tablas.',
    'fix_quota_uniqueness' => 'Aplique la migración 2026_04_06_phase2_normalization_order_numbers.sql.',
    'check_status_column' => 'Columna status en order_number_list',
    'status_column_ok' => 'Columna status encontrada en order_number_list.',
    'status_column_missing' => 'Columna status ausente en order_number_list (inconsistencia con migraciones/error de runtime).',
    'fix_status_column' => 'Ajuste la migración/hardening para crear order_number_list.status (por ejemplo, 2026_03_30_hardening.sql).',

    // Recommendations & titles
    'env_validated' => 'Entorno validado. Listo para la operación.',
    'url_detected' => 'URL Detectada',
    'url_configured' => 'URL Configurada',
    'admin_access' => 'Acceso Admin',
    'php_error_log' => 'Log de Errores PHP',
    'rec_remove_folder' => 'Elimine la carpeta <code>/DropGenesis</code> después de completar la instalación.',
    'rec_https' => 'Habilite HTTPS en producción y asegúrese de que <code>APP_BASE_URL</code> coincida con su dominio.',
    'rec_payment_errors' => 'Si ocurren errores de pago/webhook, verifique <code>admin/?page=logs</code> y <code>storage/logs/php-error.log</code>.',
    'rec_backup' => 'Realice copias de seguridad periódicas de la base de datos y de los archivos <code>config/local.php</code> y <code>storage/</code>.',
    'rec_vps_perms' => 'En entornos VPS, establezca permisos <code>644</code> para archivos y <code>755</code> para carpetas.',
    'refresh' => 'Actualizar',

    // Health check specific translations
    'additional_structure_file' => 'Estructura Adicional (structure.sql)',
    'phase2_normalization_file' => 'Normalización Fase 2 (phase2.sql)',
    'optional_but_recommended' => 'Opcional pero recomendada',
    'optional_not_found' => 'Opcional (no encontrado)',
];
