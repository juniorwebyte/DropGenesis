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

namespace DropGenesis\Drivers;

use DropGenesis\Contracts\DriverInterface;
use DropGenesis\Core\Database;
use DropGenesis\Core\License;
use DropGenesis\Core\ManifestParser;
use DropGenesis\Core\Paths;

class NativeDriver implements DriverInterface
{
    private string $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = rtrim($rootPath, '/\\');
    }

    // -------------------------------------------------------------------------
    // DriverInterface — capabilities
    // -------------------------------------------------------------------------

    public function getCapabilities(): array
    {
        return [
            'database'        => true,
            'license_verify'  => true,
            'bootstrap'       => true,
            'multi_db'        => false,
        ];
    }

    // -------------------------------------------------------------------------
    // URL detection
    // -------------------------------------------------------------------------

    public function detectDatabaseUrl(bool $isHttps): string
    {
        // A detecção mora em Paths. A versão anterior removia
        // `/(drop/)?DropGenesis/index.php` do SCRIPT_NAME com o nome da pasta
        // escrito na expressão regular, então qualquer implantação que não
        // usasse exatamente esses nomes produzia uma URL base errada — e a URL
        // base é gravada em config/local.php, isto é, o erro sobrevivia à
        // instalação.
        return Paths::baseUrl();
    }

    // -------------------------------------------------------------------------
    // License verification
    // -------------------------------------------------------------------------

    /**
     * Delegado para \DropGenesis\Core\License.
     *
     * Esta função tinha uma porta dos fundos: toda chave começada em "WEBYTE"
     * ou igual a "TESTE" era declarada válida sem consultar coisa alguma, em
     * código distribuído ao cliente final. E o endpoint montado logo abaixo
     * concatenava `/drop/api/v1/verify.php` sobre um caminho base do qual o
     * segmento `drop` já havia sido removido, gerando `/drop/drop/api/...`.
     */
    public function verifyLicense(string $key, string $domain): array
    {
        $license = new License(new ManifestParser(Paths::installerFile('drop.json')));

        return $license->verify($key, $domain);
    }

    // -------------------------------------------------------------------------
    // Database installation
    // -------------------------------------------------------------------------

    public function installDatabase(\mysqli $conn, string $sqlFile): void
    {
        Database::execSqlFile($conn, $sqlFile);
    }

    // -------------------------------------------------------------------------
    // Bootstrap / config writing
    // -------------------------------------------------------------------------

    /**
     * Grava a configuração da aplicação.
     *
     * A ordem importa: primeiro o `.env`, depois o `api/config.php` — que é
     * derivado dele. O `.env` é a única fonte de verdade, o que mantém a mesma
     * configuração válida nos três alvos do projeto (Docker via variáveis do
     * compose, VPS via painel, hospedagem compartilhada via arquivo).
     *
     * @param array<string,string> $config
     */
    public function bootstrapApplication(array $config): void
    {
        $this->writeEnv($config);
        $this->writeApiConfig($config);
    }

    /**
     * Atualiza o .env preservando comentários, ordem e chaves não relacionadas.
     *
     * @param array<string,string> $config
     */
    private function writeEnv(array $config): void
    {
        $envPath = $this->rootPath . '/.env';

        if (!is_file($envPath)) {
            // Sem .env não há de onde a aplicação ler a configuração. Partir do
            // .env.example preserva os comentários e as chaves que o instalador
            // não conhece; antes o arquivo simplesmente não era criado e a
            // instalação terminava "com sucesso" sem configuração alguma.
            $example = $this->rootPath . '/.env.example';
            if (is_file($example)) {
                @copy($example, $envPath);
            } else {
                @file_put_contents($envPath, '');
            }
        }

        $rawLines = is_file($envPath) ? file($envPath, FILE_IGNORE_NEW_LINES) : [];
        if ($rawLines === false) {
            throw new \RuntimeException('Falha ao ler o .env — verifique as permissões da raiz do projeto.');
        }

        $keyToLine = [];
        foreach ($rawLines as $i => $line) {
            if (str_contains($line, '=') && !str_starts_with(ltrim($line), '#')) {
                [$k] = explode('=', $line, 2);
                $keyToLine[trim($k)] = $i;
            }
        }

        foreach ($config as $key => $val) {
            $key  = (string) $key;
            $line = $key . '=' . self::escapeEnvValue((string) $val);

            if (isset($keyToLine[$key])) {
                $rawLines[$keyToLine[$key]] = $line;
            } else {
                $rawLines[] = $line;
            }
        }

        if (file_put_contents($envPath, implode("\n", $rawLines) . "\n") === false) {
            throw new \RuntimeException('Falha ao gravar o .env — verifique as permissões da raiz do projeto.');
        }
    }

    /**
     * Materializa `api/config.php`.
     *
     * Todo o diretório `api/` depende das constantes DB_HOST/DB_USER/DB_PASS/
     * DB_NAME definidas nesse arquivo, e o gateway da raiz decide entre "mostrar
     * o instalador" e "abrir a aplicação" pela existência dele. O instalador
     * gravava apenas `config/local.php` e o `.env`, nunca este arquivo: ao
     * terminar a instalação com sucesso, a aplicação continuava exibindo a tela
     * de instalação, e `api/v1/verify.php` respondia "API não instalada".
     *
     * @param array<string,string> $config os mesmos valores gravados no .env
     */
    private function writeApiConfig(array $config): void
    {
        $apiDir = $this->rootPath . '/api';
        if (!is_dir($apiDir)) {
            return; // Instalação autônoma do motor, sem a camada de API.
        }

        $configFile = $apiDir . '/config.php';

        if (is_file($configFile)) {
            @copy($configFile, $configFile . '.bak-' . date('YmdHis'));
        }

        if (!is_writable(is_file($configFile) ? $configFile : $apiDir)) {
            throw new \RuntimeException('Sem permissão de escrita em api/ — não foi possível gravar api/config.php.');
        }

        $hasEnvLib = is_file($apiDir . '/libs/env.php');

        if ($hasEnvLib) {
            // Config derivada do .env: uma única fonte de verdade, e o mesmo
            // arquivo funciona no Docker (onde DB_HOST=db) e fora dele.
            $contents = <<<'PHP'
<?php

/**
 * Gerado por DropGenesis durante a instalação.
 *
 * Os valores vêm do .env na raiz do projeto — edite lá, não aqui.
 */

declare(strict_types=1);

// Antes de tudo: em PHP velho, explica o que trocar no painel em vez de morrer.
require_once __DIR__ . '/libs/versao_php.php';
require_once __DIR__ . '/libs/env.php';
// Falha de banco vira tela que diz a causa (e não "connection lost" ou erro 500).
require_once __DIR__ . '/libs/banco_indisponivel.php';

drop_load_env(dirname(__DIR__) . '/.env');

define('DB_HOST', drop_resolve_db_host(drop_env('DB_HOST', '127.0.0.1') ?? '127.0.0.1'));
define('DB_USER', drop_env('DB_USER', 'root') ?? 'root');
define('DB_PASS', drop_env('DB_PASS', '') ?? '');
define('DB_NAME', drop_env('DB_NAME', '') ?? '');
define('BACKUP_TOKEN', drop_env('BACKUP_TOKEN', '') ?? '');

// Sem configuração que conecte, a tela diz isso e aponta o instalador.
drop_banco_exigir_configuracao();

// "Forçar HTTPS" das Configurações Gerais. Só consulta o banco em pedido que
// chegou por http:// — ver libs/forcar_https.php.
if (is_file(__DIR__ . '/libs/forcar_https.php')) {
    require_once __DIR__ . '/libs/forcar_https.php';
    drop_forcar_https();
}

PHP;
        } else {
            // Projeto Rifa usa DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME.
            //
            // Os valores vêm do que ACABOU de ser gravado no .env. Eram lidos
            // com getenv(), que só enxerga o .env depois de alguém carregá-lo —
            // e ninguém o carregava aqui: o config nascia com 'localhost' e
            // credenciais vazias.
            // Primeiro valor preenchido entre as chaves informadas.
            $v = static function (string ...$chaves) use ($config): string {
                foreach ($chaves as $chave) {
                    if ((string) ($config[$chave] ?? '') !== '') {
                        return (string) $config[$chave];
                    }
                }
                return '';
            };
            $contents = "<?php\n\n"
                . "/** Gerado por DropGenesis durante a instalação. */\n\n"
                . "declare(strict_types=1);\n\n"
                . "define('DB_HOST', " . var_export($v('DB_HOST', 'DB_SERVER') ?: 'localhost', true) . ");\n"
                . "define('DB_USER', " . var_export($v('DB_USER', 'DB_USERNAME'), true) . ");\n"
                . "define('DB_PASS', " . var_export($v('DB_PASS', 'DB_PASSWORD'), true) . ");\n"
                . "define('DB_NAME', " . var_export($v('DB_NAME'), true) . ");\n"
                . "define('DB_SERVER', " . var_export($v('DB_SERVER', 'DB_HOST') ?: 'localhost', true) . ");\n"
                . "define('DB_USERNAME', " . var_export($v('DB_USERNAME', 'DB_USER'), true) . ");\n"
                . "define('DB_PASSWORD', " . var_export($v('DB_PASSWORD', 'DB_PASS'), true) . ");\n";
        }

        if (file_put_contents($configFile, $contents) === false) {
            throw new \RuntimeException('Falha ao gravar api/config.php — verifique as permissões de api/.');
        }

        /*
         * Fecha também o instalador da camada de API.
         *
         * `api/install.php` só se bloqueia quando encontra `api/install.lock`
         * (ou quando detecta produção). Este assistente provisionava o
         * `api/config.php` mas não deixava a trava, então o outro instalador
         * seguia aberto sobre uma instalação já concluída. Os dois passam a
         * concordar sobre o estado do sistema.
         */
        $apiLock = $apiDir . '/install.lock';
        if (!is_file($apiLock)) {
            @file_put_contents($apiLock, 'installed by DropGenesis:' . date('c') . PHP_EOL);
        }
    }

    /**
     * Envolve em aspas os valores que precisam.
     *
     * Uma senha de banco com espaço ou `#` era gravada crua e chegava truncada à
     * aplicação. O leitor do .env (`drop_env_valor_da_linha()` em
     * `api/libs/env.php`) devolve LITERALMENTE o que está entre o primeiro e o
     * último delimitador, então não há escape: basta escolher um delimitador —
     * aspas simples, ou duplas se o valor tiver aspas simples. Com as duplas de
     * sempre, uma senha terminada em espaço ou em aspas perdia essas pontas.
     * Mesma regra de `drop_env_formatar_valor()`; duplicada porque o motor do
     * instalador não depende da aplicação que instala.
     */
    private static function escapeEnvValue(string $value): string
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9_\-\.\/:@]+$/', $value) === 1) {
            return $value;
        }

        return str_contains($value, "'") ? '"' . $value . '"' : "'" . $value . "'";
    }
}
