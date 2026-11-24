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
    'welcome' => 'Bem-vindo',
    'welcome_desc' => 'Vamos instalar seu app em poucos passos.',
    'welcome_sub' => 'Para começar com segurança (banco, URL base, admin e migrações), use o instalador automático. Ele funciona tanto em VPS, Docker quanto em subpasta no XAMPP.',
    'requirements' => 'Verificação de Requisitos',
    'requirements_desc' => 'Verifique se seu servidor atende aos requisitos mínimos.',
    'req_met' => 'Todos os requisitos atendidos. Pronto para configurar.',
    'req_unmet' => 'Alguns requisitos não foram atendidos. Corrija antes de prosseguir.',
    'next' => 'Próximo',
    'configure' => 'Configuração do Sistema',
    'back' => 'Voltar',
    'db_config' => 'Configuração de Banco de Dados',
    'admin_config' => 'Configuração Administrativa',
    'finish' => 'Finalização',
    'license_verification' => 'Verificação de Licença',
    'license_code' => 'Chave de Licença',
    'license_desc' => "Informe sua chave de compra ou serial de desenvolvimento ('dev') para validar.",
    'db_host' => 'DB Host',
    'db_user' => 'DB Usuário',
    'db_pass' => 'DB Senha',
    'db_name' => 'DB Nome',
    'admin_name' => 'Admin Usuário',
    'admin_email' => 'Admin E-mail',
    'admin_pass' => 'Senha Admin',
    'btn_verify_license' => 'Validar via API',
    'btn_test_db' => 'Testar Conexão e Banco',
    'btn_install' => 'Iniciar Instalação',
    'btn_finish' => 'Finalizar e Acessar Core',
    'installation_completed' => 'Instalação concluída com sucesso!',
    'first_run' => 'Primeira execução detectada',
    'guided_install' => 'Instalação guiada em :path',
    'next_step' => 'Próximo passo',
    'start_install_desc' => 'Clique no botão abaixo para abrir o instalador.',
    'go_installer' => 'Ir para o instalador',
    'auto_configure' => 'Auto-Configurar (Check)',
    'lock_hint' => 'Dica: depois de instalar, a pasta :path fica bloqueada automaticamente (exceto diagnóstico).',
    'where_installing' => 'Onde estou instalando?',
    'base_detected' => 'Base detectada:',
    'vps_hint' => 'Se estiver em VPS, prefira a raiz do domínio. Em XAMPP, subpasta é normal.',
    'api_hub' => 'Infraestrutura API Hub',
    'api_hub_desc' => 'Gerencie licenças, faturamento, auditoria e releases de forma centralizada.',
    'access_api' => 'Acessar Painel da API',
    'api_installed' => 'API Hub: Instalada (Acessar)',
    'standalone_hint' => 'Ambiente Standalone: Nenhuma aplicação de front-end detectada na raiz.',

    // Configuração e Bloqueio
    'btn_health_check' => 'Ver Health Check completo',
    'btn_back_core' => 'Voltar ao Core',
    'local_install' => 'Instalação local?',
    'local_install_desc' => 'Clique para preencher automaticamente com as configurações padrão do XAMPP (localhost, root sem senha).',
    'btn_use_xampp' => 'Usar XAMPP (localhost)',
    'subfolder_detected' => 'Instalação em subpasta detectada:',
    'subfolder_warn' => 'O recomendado é instalar na raiz do domínio. Marque a confirmação abaixo se deseja continuar assim mesmo.',
    'system_base_url' => 'URL Base do Sistema',
    'auto_detected' => 'Detectado automaticamente — projeto em subpasta',
    'url_locked_hint' => 'URL bloqueada para evitar links quebrados.',
    'auto_detected_hint' => 'Detectado: :url — você pode alterar manualmente se necessário.',
    'confirm_subfolder' => 'Confirmo que desejo instalar em <code>:path</code> mesmo assim.',
    'confirm_subfolder_production' => 'Não recomendado para ambientes de produção',
    'environment' => 'Ambiente',
    'site_name' => 'Nome do Site',
    'db_pass_placeholder' => '(em branco para root XAMPP)',
    'admin_pass_placeholder' => 'Mínimo 8 caracteres',
    'admin_pass_confirm' => 'Confirmar Senha Admin',
    'admin_pass_confirm_placeholder' => 'Repita a senha',
    'apply_hardening' => 'Aplicar migração de performance e segurança (hardening)',
    'apply_hardening_sub' => 'Recomendado para ambientes de produção',
    'apply_schema' => 'Aplicar estruturação SQL (índices + DECIMAL + ajustes de schema)',
    'apply_schema_sub' => 'Recomendado: sincroniza banco com hardening do código',
    'apply_phase2' => 'Aplicar Fase 2 (normalização de cotas com <code>order_number_list</code>)',
    'apply_phase2_sub' => 'Ativa unicidade por produto+cota no banco (anti-duplicidade)',
    'btn_install_now' => 'Instalar Agora',
    'installing' => 'Instalando...',
    'installing_desc' => 'Aguarde enquanto configuramos o banco de dados e o sistema.',
    'installer_security_note' => 'WebyteHub Installer — Este instalador deve ser removido após a instalação por questões de segurança.',
    'locked_title' => 'Instalador bloqueado',
    'locked_desc' => 'A aplicação já foi instalada. Por segurança, o instalador fica bloqueado.',
    'post_check_diagnostics' => 'Diagnóstico pós-instalação',
    'health_check' => 'Health check',
    'admin_panel' => 'Painel admin',
    // O texto antigo listava dois caminhos fixos. Como o ecossistema tem dois
    // instaladores, que gravam em lugares diferentes, a lista agora é montada
    // em tempo de execução a partir do que existe em disco.
    'reinstall_hint' => 'Para reinstalar, remova <code>storage/installed.lock</code> e <code>config/local.php</code>.',
    'reinstall_title' => 'Para reinstalar',
    'reinstall_intro' => 'Remova os arquivos abaixo. Eles são os que marcam esta instalação como concluída:',
    'reinstall_db_warning' => 'O banco de dados não é apagado junto. Para uma instalação limpa, use um banco vazio ou remova as tabelas existentes — o instalador se recusa a gravar sobre um banco em uso.',
    'reinstall_none_found' => 'Nenhum arquivo de instalação foi encontrado nos caminhos conhecidos. Confira as permissões de leitura da pasta do projeto.',

    // Chaves de Diagnóstico
    'health_check_title' => 'Health Check do Servidor',
    'health_check_meta_desc' => 'Verifique se o ambiente atende todos os requisitos',
    'system_reqs' => 'Requisitos do Sistema',
    'installed_security_warn' => 'Instalação já concluída. Por segurança, use :link e remova a pasta :dir.',
    'ready_to_install' => 'Tudo pronto para instalar.',
    'blocks_found' => 'Foram encontrados bloqueios. Corrija os itens em vermelho antes de instalar.',
    'btn_back_installer' => 'Voltar ao Instalador',
    'btn_post_diagnostics' => 'Diagnóstico pós-instalação',
    'installer_security_remove_note' => 'WebyteHub — remova a pasta :path após a instalação.',
    'post_check_title' => 'Diagnóstico Pós-Instalação',
    'post_check_desc' => 'Verifique a integridade do ambiente após a instalação',

    // Seções adicionadas nas telas secundárias (Audit e Master Sync)
    'audit_title' => 'DROP Health Audit',
    'audit_desc' => 'Análise de Ecossistema & Rotas',
    'audit_htaccess_ok' => '[OK] Arquivo .htaccess configurado na raiz.',
    'audit_htaccess_warn' => '[WARNING] Arquivo .htaccess não encontrado na raiz.',
    'audit_gateway_critical' => '[CRITICAL] O index.php da raiz não possui o ecossistema Drop.',
    'audit_gateway_ok' => '[OK] Gateway index.php raiz configurado corretamente.',
    'scanning_directories' => 'Escaneando diretórios...',
    'project_detected' => 'Projeto detectado: :project',
    'auto_configure_btn' => 'Auto-Configurar',
    'permissions_title' => 'Permissões',
    'btn_installer' => 'Instalador',
    'update_central' => 'Central de Atualizações',
    'access' => 'Acesso',
    'sync' => 'Sincronia',
    'success' => 'Sucesso',
    'unlock_system' => 'Desbloquear Sistema',
    'license_placeholder' => 'LICENÇA-WEBYTEHUB',
    'sync_required' => 'Sync Requerido',
    'ecosystem_active' => 'Ecossistema Ativo',
    'all_ready' => 'Tudo Pronto!',
    'update_success_desc' => 'Seu ecossistema WebyteHub está totalmente sincronizado e otimizado em todas as instâncias.',
    'back_to_panel' => 'Voltar ao Panel',
    'view_logs' => 'Ver Logs',
    'disconnect' => 'Desconectar',
    'check_updates' => 'Buscar Atualizações',
    'safety_snapshot' => 'Safety Snapshot Engine',
    'protection_active' => 'Proteção Ativa',
    'safety_desc' => 'Um ponto de restauração será criado automaticamente antes da sincronização. Em caso de anomalia, você poderá reverter todo o sistema para o estado atual em menos de 30 segundos.',
    'btn_view_snapshots' => 'Ver últimos snapshots',
    'btn_integrity_validated' => 'Integridade validada',

    // index.php adicionais
    'subtitle_text' => 'Motor de ativação e sincronia de microsserviços',
    'symphony_active' => 'Sinfonia Ativa',
    'details' => 'Detalhes',

    // post-check adicionais
    'env_status' => 'Status do Ambiente',
    'attention_points' => 'Foram encontrados pontos de atenção. Corrija os itens em vermelho.',
    'item' => 'Item',
    'how_to_fix' => 'Como Corrigir',
    'final_recommendations' => 'Recomendações Finais',

    'fail' => 'FALHA',
    'extension' => 'Extensão',
    'write_permission' => 'Escrita em',
    'sql_base_file' => 'Arquivo SQL Base',

    // post-check dynamic checks
    'check_lock_exists' => 'Instalação detectada (installed.lock)',
    'not_found' => 'Não encontrado',
    'fix_installed_lock' => 'Finalize a instalação em /DropGenesis/index.php',
    'check_config_exists' => 'config/local.php existe',
    'fix_config_local' => 'Reinstale ou gere config/local.php pelo instalador.',
    'check_storage_write' => 'Escrita em /storage',
    'fix_write_storage' => 'Ajuste permissões da pasta (chmod 755 ou via painel de hospedagem).',
    'check_logs_write' => 'Escrita em /storage/logs',
    'fix_write_logs' => 'Ajuste permissões da pasta.',
    'check_root_htaccess' => '.htaccess na raiz existe',
    'fix_root_htaccess' => 'Faça upload do arquivo .htaccess para a raiz do projeto.',
    'check_drop_htaccess' => '.htaccess em /DropGenesis existe',
    'fix_drop_htaccess' => 'O DropGenesis/.htaccess bloqueia acesso após a instalação.',
    'check_mod_rewrite' => 'mod_rewrite ativo (Apache)',
    'detect_warn_htaccess' => 'Não detectável automaticamente (normal em Nginx ou Apache sem apache_get_modules).',
    'active' => 'Ativo',
    'disabled' => 'Desativado',
    'fix_mod_rewrite' => 'No XAMPP: habilite mod_rewrite e AllowOverride All no httpd.conf.',
    'check_base_url_consist' => 'BASE_URL consistente com a pasta',
    'detect_base_url_details' => 'Detectado: :det | Configurado: :conf',
    'fix_base_url' => 'Reinstale ou edite APP_BASE_URL em config/local.php.',
    'check_db_conn' => 'Conexão com banco de dados',
    'db_conn_failed' => 'Falha de conexão (:err).',
    'db_test_error' => 'Erro ao testar BD: :err',
    'db_credentials_missing' => 'Credenciais de BD não encontradas em config/local.php.',
    'fix_db_conn' => 'Verifique DB_* no instalador e em config/local.php.',
    'check_key_tables' => 'Tabelas-chave presentes',
    'key_tables_ok' => 'Todas as tabelas-chave foram encontradas.',
    'key_tables_missing' => 'Faltando: :missing',
    'db_not_connected' => 'BD não conectado.',
    'fix_key_tables' => 'Reinstale em um banco vazio e rode o SQL de schema.',
    'check_quota_uniqueness' => 'Unicidade de cotas (order_number_list)',
    'quota_uniqueness_ok' => 'Índice único uniq_product_lucky_number ativo.',
    'quota_uniqueness_missing' => 'Índice único ausente/incompleto em order_number_list.',
    'dep_conn_tables' => 'Dependente de conexão/tabelas.',
    'fix_quota_uniqueness' => 'Aplique a migração 2026_04_06_phase2_normalization_order_numbers.sql.',
    'check_status_column' => 'Coluna status em order_number_list',
    'status_column_ok' => 'Coluna status encontrada em order_number_list.',
    'status_column_missing' => 'Coluna status ausente em order_number_list (inconsistência com migrations/bug de runtime).',
    'fix_status_column' => 'Ajuste a migração/hardening para criar order_number_list.status (ex.: 2026_03_30_hardening.sql).',

    // Recommendations & titles
    'env_validated' => 'Ambiente validado. Pronto para operação.',
    'url_detected' => 'URL Detectada',
    'url_configured' => 'URL Configurada',
    'admin_access' => 'Acesso Admin',
    'php_error_log' => 'Log de Erros PHP',
    'rec_remove_folder' => 'Remova a pasta <code>/DropGenesis</code> após concluir a instalação.',
    'rec_https' => 'Ative HTTPS em produção e certifique-se que <code>APP_BASE_URL</code> corresponde ao domínio.',
    'rec_payment_errors' => 'Se ocorrer erro de pagamento/webhook, verifique <code>admin/?page=logs</code> e <code>storage/logs/php-error.log</code>.',
    'rec_backup' => 'Faça backup periódico do banco de dados e dos arquivos <code>config/local.php</code> e <code>storage/</code>.',
    'rec_vps_perms' => 'Em ambiente VPS, defina permissões <code>644</code> para arquivos e <code>755</code> para diretórios.',
    'refresh' => 'Atualizar',

    // Health check specific translations
    'additional_structure_file' => 'Estrutura Adicional (structure.sql)',
    'phase2_normalization_file' => 'Normalização Fase 2 (phase2.sql)',
    'optional_but_recommended' => 'Opcional mas recomendada',
    'optional_not_found' => 'Opcional (não encontrado)',
];
