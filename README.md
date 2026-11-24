# WebyteHub Drop — Enterprise SaaS Ecosystem

<div align="center">

```text
██████╗ ██████╗  ██████╗ ██████╗      ██████╗ ███████╗███╗   ██╗███████╗███████╗██╗███████╗
██╔══██╗██╔══██╗██╔═══██╗██╔══██╗    ██╔════╝ ██╔════╝████╗  ██║██╔════╝██╔════╝██║██╔════╝
██║  ██║██████╔╝██║   ██║██████╔╝    ██║  ███╗█████╗  ██╔██╗ ██║█████╗  ███████╗██║███████╗
██║  ██║██╔══██╗██║   ██║██╔═══╝     ██║   ██║██╔══╝  ██║╚██╗██║██╔══╝  ╚════██║██║╚════██║
██████╔╝██║  ██║╚██████╔╝██║         ╚██████╔╝███████╗██║ ╚████║███████╗███████║██║███████║
╚═════╝ ╚═╝  ╚═╝ ╚═════╝ ╚═╝          ╚═════╝ ╚══════╝╚═╝  ╚═══╝╚══════╝╚══════╝╚═╝╚══════╝
```

# DropGenesis

### Universal PHP Installation Engine

**A universal installation, provisioning and bootstrap engine for PHP applications.**

Developed and maintained by **Webyte Desenvolvimentos**.

[Website](https://webytehub.com) · [Documentation](https://docs.webytehub.com) · [Repository](https://github.com/juniorwebyte/dropgenesis)

</div>

---

## About DropGenesis

**DropGenesis** is a universal installation, provisioning and bootstrap engine designed for PHP applications.

It provides a standardized foundation for application initialization, environment validation, configuration, database provisioning, dependency verification and deployment preparation.

DropGenesis is designed to provide PHP applications with a reliable, secure and extensible installation lifecycle while remaining independent enough to be integrated into different application architectures.

### Core Purpose

* Application installation and bootstrap
* Environment validation
* Server requirement verification
* Database provisioning
* Initial application configuration
* Filesystem and permission validation
* Installation lifecycle management
* Deployment preparation
* Integration with existing PHP applications

---

## Project Information

| Information       | Details                                     |
| ----------------- | ------------------------------------------- |
| **Project**       | DropGenesis                                 |
| **Engine**        | Universal PHP Installation Engine           |
| **Developer**     | Webyte Desenvolvimentos                     |
| **Author**        | Júnior Alves                                |
| **Version**       | `@version`                                  |
| **Website**       | https://webytehub.com                       |
| **Documentation** | https://docs.webytehub.com                  |
| **Repository**    | https://github.com/juniorwebyte/dropgenesis |

---

## License

Copyright © Webyte Desenvolvimentos.
**All rights reserved.**

This software is protected by copyright laws and international treaties.

It may only be used, copied, modified or redistributed in accordance with the terms of its respective license.

Unauthorized reproduction, distribution or commercialization of this software, in whole or in part, is strictly prohibited.

For license information, visit:

**https://webytehub.com/licencas**

---

<div align="center">

### *"Every great application deserves a great beginning."*

**Powered by DropGenesis.**

Webyte Desenvolvimentos

</div>

Drop is a PHP/MySQL enterprise SaaS platform migrated from XAMPP to a Docker-based infrastructure. It governs licensing, billing, and the customer portal, while sub-systems handle POS/PDV, NFC-e fiscal, payment processing, and real-time synchronization.

## Ecosystem Overview

```
drop/
├── api/              # Legacy PHP API (80+ endpoints, full SaaS stack)
│   ├── v1/           # API v1 (license verify, contact, plans)
│   ├── components/   # Auth header/footer, guest bootstrap
│   ├── cron/         # Scheduled jobs (backfill, health, sync)
│   └── pages/        # Portal pages
├── src/              # Modern PHP MVC (PSR-4: Drop\)
│   ├── Controllers/  # Api & Web controllers
│   ├── Models/       # Account, Client, Invoice, License, Product, PdvSale, etc.
│   ├── Services/     # Client, Export, Finance, Product, Sale, View
│   ├── Middlewares/  # Auth, CORS, Pipeline
│   ├── Routes/       # api.php, web.php
│   └── DTOs/         # BaseDTO
├── ploutosledger/    # POS/PDV sub-application (ledger, inventory, NFC-e)
├── webytepay/        # Payment engine (Stripe, PIX, BaaS)
├── DropGenesis/      # CMS / admin panel
├── websocket/        # Node.js WebSocket + API server (real-time)
├── desktop/          # Ploutos Desktop (Electron/Node)
├── resources/        # Superfície Blade + Vite + Tailwind (views/, css/, js/)
├── docker-compose.yml# Orchestration (app, db, phpmyadmin, websocket)
├── Dockerfile        # PHP 8.2 Apache image
└── docs/             # Governance, audit, and operational documentation
```

## 🧰 Tecnologias

Tudo abaixo foi conferido contra `composer.json`, os `package.json`, o
`Dockerfile`, o `docker-compose.yml` e as chamadas no código — não é lista de
intenção.

### Runtime e infraestrutura

| Peça | Versão | Onde |
|---|---|---|
| PHP | 8.2 (Apache) | imagem `php:8.2-apache`, `Dockerfile` |
| MySQL | 8.0 | serviço `db` |
| Node.js | 18 (Alpine) | serviço `websocket` |
| phpMyAdmin | latest | serviço `phpmyadmin` |

Extensões PHP compiladas na imagem: `mysqli`, `pdo_mysql`, `gd`, `zip`,
`bcmath`, `intl`, `curl`.

`mysqli` e `pdo_mysql` convivem porque as três stacks convivem: o PDV e a API
legada usam mysqli procedural, o MVC usa Eloquent sobre PDO.

### Bibliotecas PHP (Composer)

| Pacote | Versão | Para quê |
|---|---|---|
| `nikic/fast-route` | ^1.3 | Roteamento do MVC. O dispatcher é montado em `api/index.php` |
| `illuminate/database` | ^12.62 | Eloquent, inicializado via Capsule em `src/bootstrap.php` |
| `illuminate/view` | ^12.62 | Blade, usado por `src/Services/View.php` |
| `php-di/php-di` | ^7.1 | Container de injeção que resolve os controllers |
| `vlucas/phpdotenv` | ^5.6 | Carrega o `.env` |

Em `require-dev`: PHPUnit ^11.5, PHPStan ^2.2, Psalm ^6.5, PHP_CodeSniffer ^4.0.
**Só o PHPUnit participa do portão de qualidade** — os outros três estão
instalados mas não têm arquivo de configuração no repositório.

O SDK do Stripe (**10.5.0**) não vem do Composer: está versionado em
`api/libs/stripe-php/`.

### JavaScript

| Contexto | Stack |
|---|---|
| `websocket/` | `ws` ^8.14.2, `nodemon` em dev — servidor nas portas 8089/9089 |
| `desktop/ploutos-desktop/` | Electron + `electron-builder`, `electron-log`, `electron-updater` (v1.1.12) |
| `resources/` | Vite ^8.1.3 + Tailwind CSS ^4.3.2, entrada `resources/css/app.css` e `resources/js/app.js` |

**O painel Drop e o PloutosLedger não usam framework de front-end.** São
renderizados no servidor por PHP, com módulos ES nativos (`assets/js/modules/`)
e CSS próprio. O Vite/Tailwind cobre apenas a superfície Blade em `resources/`,
que hoje tem quatro arquivos.

Dependências de terceiros do front (FontAwesome, Prism) são servidas
localmente, não por CDN — a CSP bloqueia origens externas de script.

### Serviços externos

| Serviço | Endpoint | Uso |
|---|---|---|
| OpenAI | `api.openai.com` | Drop Converse AI |
| Google Gemini | `generativelanguage.googleapis.com` | Drop Converse AI |
| Groq | `api.groq.com` | Drop Converse AI (chave no campo endpoint do agente) |
| Ollama | `localhost:11434` por padrão | Inferência local, configurável em `api_settings` |
| Stripe | `api.stripe.com` | Assinaturas e billing |
| CNPJ.ws / BrasilAPI | `publica.cnpj.ws`, `brasilapi.com.br` | Autopreenchimento de razão social e IE |
| ViaCEP | `viacep.com.br` | Autopreenchimento de endereço |

As três últimas precisam estar em `connect-src` no `.htaccess`. Quando a CSP
passou a bloquear elas ficaram de fora, e o autopreenchimento parou em onze
arquivos sem emitir erro visível. `tests/Unit/CspConnectSrcTest.php` varre os
`fetch()` do projeto e cobra cada origem na diretiva para isso não se repetir.

Os ids de modelo `claude-*` que aparecem em `api/libs/converse_ai.php` estão
numa lista de capacidade (`VISION_MODELS`), não há provedor Anthropic ligado.

### Segurança

CSP bloqueante no `.htaccess`, TOTP próprio (`api/libs/GoogleAuthenticator.php`)
com consumo de uso único, CSRF por token de sessão em todo método que altera
estado, e RBAC por perfil resolvido no banco. PIX gerado localmente por
`api/libs/PixGenerator.php`.

---

## Governance (CTO)

| Documento | Conteúdo |
|-----------|----------|
| [`docs/CTO_PRIORIDADES.md`](docs/CTO_PRIORIDADES.md) | Roadmap por ciclos (1–4) |
| [`docs/CICLO_2_PILOTO.md`](docs/CICLO_2_PILOTO.md) | Playbook do piloto SMB atual |
| [`docs/DEMO_ROTEIRO_15MIN.md`](docs/DEMO_ROTEIRO_15MIN.md) | Roteiro de demonstração |
| [`docs/ONE_PAGER_COMERCIAL.md`](docs/ONE_PAGER_COMERCIAL.md) | Material comercial (1 página) |
| [`docs/GO_LIVE_CHECKLIST.md`](docs/GO_LIVE_CHECKLIST.md) | Checklist antes de clientes reais |
| [`docs/SEGURANCA_SECRETS.md`](docs/SEGURANCA_SECRETS.md) | Rotação de secrets |
| [`docs/FISCAL_NFCE_SEFAZ.md`](docs/FISCAL_NFCE_SEFAZ.md) | NFC-e homologação vs produção |
| [`docs/QUICK_START.md`](docs/QUICK_START.md) | Guia rápido de implementação |
| [`desktop/ploutos-desktop/README.md`](desktop/ploutos-desktop/README.md) | Ploutos PDV Desktop — app, build, publicação e auto-update |
| [`ploutosledger/README.md`](ploutosledger/README.md) | PloutosLedger — uma instalação por loja, autorização e escopo de tenant |
| [`docs/EXCELENCIA_OPERACIONAL.md`](docs/EXCELENCIA_OPERACIONAL.md) | Excelência operacional |
| [`docs/INTEGRACAO_MELHORIAS_2026.md`](docs/INTEGRACAO_MELHORIAS_2026.md) | Integrações e melhorias |

**Quality gate (um comando):** `docker exec drop_app php /var/www/html/scripts/run_quality_gate.php`

**Seed demo loja:** `docker exec drop_app php /var/www/html/scripts/seed_demo_loja.php --with-sale`

CI: push/PR disparam `.github/workflows/quality.yml` (requer Docker no runner).

---

## 🚀 Como Iniciar o Projeto

### 1. Requisitos Prévios

- **Docker** e **Docker Compose** instalados.
- **Importante:** Certifique-se de que o **XAMPP (Apache/MySQL)** esteja **DESLIGADO** para evitar conflito nas portas (80, 8080, 3306).

### 2. Subindo os Containers

No terminal, na raiz do projeto, execute:

```bash
docker-compose up -d
```

### 3. Acessos Rápidos

| Serviço | URL |
|---------|-----|
| Aplicação/API | [http://localhost:8080](http://localhost:8080) |
| Documentação Técnica | [http://localhost:8080/api/docs.php](http://localhost:8080/api/docs.php) |
| Painel Administrativo | [http://localhost:8080/api/dashboard.php](http://localhost:8080/api/dashboard.php) |
| phpMyAdmin | [http://localhost:8081](http://localhost:8081) |
| WebSocket Health | [http://localhost:9089/health](http://localhost:9089/health) |

### 4. Operação Diária (Rotina)

Sempre que desejar iniciar o ambiente após ligar o computador:

- **Via Terminal:** Execute `docker-compose up -d` na pasta do projeto.
- **Via Interface:** Abra o Docker Desktop e clique em **Start** no grupo de containers do projeto.
- **Para parar:** Utilize `docker-compose stop` para economizar memória do seu PC quando não estiver usando.

---

## 🗄️ Gerenciamento do Banco de Dados

O banco de dados é inicializado automaticamente com o nome `dropsync_api`.

### Credenciais de Acesso (phpMyAdmin / API):

- **Host:** `db` (interno) ou `localhost` (externo)
- **Usuário:** `root`
- **Senha:** `root_password`
- **Porta Externa:** `3306`

### Como Sincronizar seu Banco de Dados:

Se você está começando agora ou precisa restaurar dados:

1. Acesse o [phpMyAdmin](http://localhost:8081).
2. Selecione o banco `dropsync_api`.
3. Vá na aba **Importar**.
4. Escolha um dos arquivos na pasta `/database/`:
    - **`database_dump.sql`**: Recomendado para carregar dados reais (Clientes, Licenças, Admins).
    - **`base.sql`**: Estrutura limpa da versão mais recente (v4.8.5).
    - **`dropsync_api.sql`**: Dump completo do schema.

---

## 📦 Componentes do Ecossistema

### API Layer (`api/`)

A API legada em PHP contém 80+ endpoints organizados em:

| Módulo | Arquivos Principais | Função |
|--------|---------------------|--------|
| Auth | `login.php`, `register.php`, `logout.php`, `forgot_password.php`, `reset_password.php` | Autenticação e gestão de contas |
| Portal | `portal.php`, `dashboard.php`, `landing_manager.php` | Portal do cliente e landing page |
| Licenças | `ploutos_entitlement.php`, `license.php` | Verificação e gestão de licenças |
| Billing | `checkout.php`, `webhook_stripe.php` | Checkout recorrente e webhooks Stripe |
| Fiscal | `invoice.php`, `converse_ai.php` | NFC-e, integração Focus/Nuvem Fiscal |
| Ploutos | `ploutosledger_about.php`, `setup_ploutos_marketplace.php` | Integração com Ploutos Ledger |
| Admin | `master_audit.php`, `sync_audit.php`, `sync_oracle.php` | Auditoria e sincronização |
| Backup | `backup.php`, `cron_backup.php` | Dump e restauração de banco |
| LGPD | `lgpd_export.php`, `lgpd_delete_account.php` | Conformidade LGPD |
| Vault | `vault.php`, `vault_export.php`, `vault_import.php` | Gerenciamento de secrets |
| v1 API | `v1/verify.php`, `v1/get_plans.php`, `v1/contact_submit.php` | API pública com rate limiting |
| Saúde | `health.php`, `health-pro.php`, `status.php` | Health checks e diagnóstico |

### Core App (`src/`) — PHP MVC (PSR-4: `Drop\`)

| Diretório | Descrição |
|-----------|-----------|
| `Controllers/Api` | Controladores de API REST |
| `Controllers/Web` | Controladores de páginas web |
| `Models/` | Entidades: Account, Client, Customer, Invoice, License, PdvSale, Product, Transaction, etc. |
| `Services/` | Lógica de negócio: ClientService, ExportService, FinanceService, ProductService, SaleService, PloutosDashboardService |
| `Middlewares/` | AuthMiddleware, CorsMiddleware, Pipeline, MiddlewareInterface |
| `Routes/` | `api.php` e `web.php` |
| `DTOs/` | Data Transfer Objects |

### Ploutos Ledger (`ploutosledger/`)

Sub-aplicação de PDV/POS com:
- Dashboard de vendas em tempo real
- Gestão de estoque e transferências
- Integração com WebSocket para sincronização
- Fechamento de caixa com breakdown (dinheiro/cartão/PIX)
- Autenticação de operadores com RBAC
- Migração de schema e dados PDV

### Webytepay (`webytepay/`)

Motor de pagamentos com:
- Processamento PIX e cartão
- BaaS (Backend-as-a-Service) via `BaaSClient`
- Ledger contábil (`Ledger.php`)
- Motor de transações (`TransactionEngine.php`)
- Webhook handling

### DropGenesis (`DropGenesis/`)

CMS e painel administrativo:
- Health check e post-deploy validation
- Endpoints AJAX para gestão
- Migração de CMS
- Configuração de drop.json

### WebSocket Server (`websocket/`)

Servidor Node.js para real-time:
- WebSocket server (porta 8089)
- API server (porta 9089)
- Ploutos realtime integration
- QueueManager para sincronização de pedidos
- Health check em `/health`

### Desktop App (`desktop/`)

Aplicação desktop Ploutos para terminais de loja:
- Scripts de build, sync e deploy via PowerShell
- Perfil de configuração por loja
- Instalação de CA e certificados


## 🔌 Integrações

| Serviço | Configuração | Variáveis de Ambiente |
|---------|-------------|----------------------|
| Stripe | Pagamentos recorrentes | `stripe_sec`, `stripe_wh_sec` |
| Focus NFe | NFC-e homologação | `FISCAL_API_PROVIDER=focus`, `FISCAL_API_URL`, `FISCAL_API_KEY` |
| Nuvem Fiscal | NFC-e produção | `FISCAL_API_PROVIDER=nuvemfiscal` |
| OpenAI | IA / assistentes | `OPENAI_API_KEY` |
| Gemini | IA / assistentes | `GEMINI_API_KEY` |
| Telegram | Notificações | `TELEGRAM_BOT_TOKEN` |
| Discord | Webhooks de notificação | `DISCORD_WEBHOOK_URL` |
| Ploutos Realtime | Sincronização em tempo real | `PLOUTOS_REALTIME_URL`, `PLOUTOS_REALTIME_SECRET` |

---

## 🛠️ Manutenção e Comandos Úteis

### Logs
```bash
docker-compose logs -f app
docker-compose logs -f websocket
docker-compose logs -f db
```

### Reiniciar tudo do zero (Limpar Dados):
```bash
docker-compose down -v
docker-compose up -d
```

### Rodar Quality Gate:
```bash
docker exec drop_app php /var/www/html/scripts/run_quality_gate.php
```

### Seed de Dados Demo:
```bash
docker exec drop_app php /var/www/html/scripts/seed_demo_loja.php --with-sale
```

### Instalar novas extensões PHP:
Edite o `Dockerfile` e rode `docker-compose build`.

### Tarefas agendadas (cron)

O agendamento é versionado em [`docker/crontab`](docker/crontab) e instalado na
imagem como `/etc/cron.d/drop`. O daemon sobe no boot do container, controlado
por `ENABLE_CRON` (padrão `1`).

Habilitada por padrão, apenas uma tarefa:

| Tarefa | Frequência | Efeito |
|---|---|---|
| `api/cron/process_subscriptions.php` | 03:00 diário | Aplica downgrades agendados, encerra assinaturas canceladas no fim do ciclo e alerta clientes que passaram de 80% de qualquer limite (e-mail ao cliente, WhatsApp ao time) |

As demais (`payment_health_monitor`, `cron_licenses`, `cron_webhooks`,
`cron_backup`, `cron_cleanup`) estão **comentadas** no `docker/crontab`: elas
enviam e-mail e WhatsApp a clientes reais, marcam faturas como vencidas ou
geram dump do banco. Habilitar é decisão de quem opera — descomente a linha e
reconstrua a imagem.

```bash
# ver o que rodou
docker exec drop_app cat /var/log/drop-cron.log

# rodar uma tarefa à mão (só CLI; via HTTP o cron_guard devolve 403)
docker exec drop_app php /var/www/html/api/cron/process_subscriptions.php

# desligar o agendador
ENABLE_CRON=0 docker-compose up -d app
```

Ao adicionar uma tarefa, carregue `/etc/drop/cron.env` antes do PHP — o cron
não herda o ambiente do container, e sem isso `APP_ENV` chega vazio, o que faz
`drop_is_production()` cair no fallback por host.

---

## 🧪 Testes e Qualidade

| Ferramenta | Comando | Descrição |
|------------|---------|-----------|
| PHPUnit | `vendor/bin/phpunit` | Testes unitários e de integração |
| PHPStan | `vendor/bin/phpstan analyse` | Análise estática de tipos |
| Psalm | `vendor/bin/psalm` | Verificação de tipos e segurança |
| PHPCS | `vendor/bin/phpcs` | Padrões de codificação |
| Vite | `npm run dev` / `npm run build` | Build frontend (TailwindCSS) |

---

## 📖 Documentação Adicional

Toda a documentação técnica está disponível no Oráculo:
👉 [http://localhost:8080/api/docs.php](http://localhost:8080/api/docs.php)

Documentação complementar em `docs/`:
- **QUICK_START.md** — Guia de implementação completa do sistema Drop
- **IMPLANTACAO_CAIXA_TERMINAIS.md** — Implantação de loja: servidor, caixa e terminais
- **AUDITORIA_COMPLETA_PLOUTOSLEDGER.md** — Auditoria do Ploutos Ledger
- **INTEGRACAO_MELHORIAS_2026.md** — Integrações e melhorias de 2026

---

## 🆘 Resolução de Problemas

### Erros de sincronização de banco de dados

Se você encontrar erros de "Undefined array key" ou colunas ausentes após importar um banco antigo:

1. Acesse o **phpMyAdmin** (localhost:8081).
2. Selecione o banco **`dropsync_api`**.
3. Clique na aba **SQL** no topo.
4. Para o erro específico de `price_yearly`, execute o comando:
   ```sql
   ALTER TABLE `api_plans` ADD COLUMN `price_yearly` DECIMAL(10,2) DEFAULT 0.00 AFTER `price`;
   ```
5. Clique em **Executar**.

### WebSocket não conecta
```bash
docker logs drop_websocket -f
curl http://localhost:9089/health
```

### Portas em conflito
Verifique se o XAMPP está desligado. As portas em uso são:
- `8080` — Aplicação principal
- `8081` — phpMyAdmin
- `3306` — MySQL
- `8089` — WebSocket
- `9089` — WebSocket API

---

## 📁 Estrutura de Diretórios Chave

| Diretório | Finalidade |
|-----------|-----------|
| `api/` | API legada PHP (80+ endpoints) |
| `src/` | Core PHP MVC (Drop namespace) |
| `ploutosledger/` | POS/PDV sub-app |
| `webytepay/` | Payment engine |
| `DropGenesis/` | CMS admin panel |
| `websocket/` | Node.js real-time server |
| `desktop/` | Desktop application |
| `database/` | SQL dumps e schema |
| `docs/` | Governance and operational docs |
| `scripts/` | Maintenance and setup scripts |
| `config/` | Database and local config |
| `resources/` | CSS, JS, Views |
| `public/` | Public assets (build output) |
| `storage/` | Framework storage (logs, cache) |
| `tests/` | Unit tests |
| `scratch/` | Development scratch files |
| `releases/` | Release artifacts |
| `backup/` | Backup vault |
| `vendor/` | Composer dependencies |
| `node_modules/` | NPM dependencies |

---

## Licença

MIT — Veja [`LICENSE`](LICENSE) para detalhes.
