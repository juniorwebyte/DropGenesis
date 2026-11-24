<?php

/*
 * ==========================================================================
 *
 *   ██████╗ ██████╗  ██████╗ ██████╗      ██████╗ ███████╗███╗   ██╗███████╗███████╗██╗███████╗
 *   ██╔══██╗██╔══██╗██╔═══██╗██╔══██╗    ██╔════╝ ██╔════╝████╗  ██║██╔════╝██╔════╝██║██╔════╝
 *   ██║  ██║██████╔╝██║   ██║██████╔╝    ██║  ███╗█████╗  ██╔██╗ ██║█████╗  ███████╗██║███████╗
 *   ██║  ██║██╔══██╗██║   ██║██╔═══╝     ██║   ██║██╔══╝  ██║╚██╗██║██╔══╝  ╚════██║██║╚════██║
 *   ██████╔╝██║  ██║╚██████╔╝██║         ╚██████╔╝███████╗██║ ╚████║███████╗███████║██║███████║
 *   ╚═════╝ ╚═╝  ╚═╝ ╚═════╝ ╚═╝          ╚═════╝ ╚══════╝╚═╝  ╚═══╝╚══════╝╚═╝╚══════╝
 *
 * ==========================================================================
 *
 * DropGenesis
 * Universal PHP Installation Engine
 *
 * Developed and maintained by Webyte Desenvolvimentos.
 *
 * Author:
 *   Júnior Alves
 *
 * Website:
 *   https://webytehub.com
 *
 * ==========================================================================
 * LICENSE
 * ==========================================================================
 *
 * Copyright (c) Webyte Desenvolvimentos.
 * All rights reserved.
 *
 * For license information, please visit:
 * https://webytehub.com/licencas
 *
 * ==========================================================================
 */

declare(strict_types=1);

namespace DropGenesis\Core;

use mysqli;
use RuntimeException;

/**
 * Acesso ao MySQL durante a instalação.
 *
 * Concentra três coisas que estavam repetidas e divergentes entre
 * `index.php`, `check/index.php` e o `NativeDriver`: abrir a conexão,
 * executar um arquivo .sql e desfazer o que foi criado quando algo falha.
 */
final class Database
{
    /**
     * Abre a conexão aceitando as formas de endereço que aparecem em produção.
     *
     * O campo "host" do formulário ia direto para o construtor do mysqli, então
     * `db.exemplo.com:3307` (porta não padrão, comum em hospedagem
     * compartilhada) e `localhost:/caminho/mysql.sock` (Docker, cPanel)
     * simplesmente não conectavam.
     */
    public static function connect(string $host, string $user, string $pass, ?string $database = null): mysqli
    {
        mysqli_report(MYSQLI_REPORT_OFF);

        [$hostname, $port, $socket] = self::parseHost($host);

        $conn = @new mysqli($hostname, $user, $pass, $database ?? '', $port, $socket);

        if ($conn->connect_error) {
            throw new RuntimeException('Erro de conexão no banco: ' . $conn->connect_error);
        }

        $conn->set_charset('utf8mb4');

        return $conn;
    }

    /**
     * Divide o endereço em host, porta e socket.
     *
     * @return array{0:string,1:int,2:?string}
     */
    public static function parseHost(string $host): array
    {
        $host = trim($host);
        if ($host === '') {
            $host = 'localhost';
        }

        // IPv6 entre colchetes, com porta opcional: [::1]:3307
        if (preg_match('/^\[(?<ip>[0-9a-fA-F:]+)\](?::(?<port>\d+))?$/', $host, $m) === 1) {
            return [$m['ip'], (int) ($m['port'] ?? 0) ?: 3306, null];
        }

        // Socket unix: localhost:/var/run/mysqld/mysqld.sock
        if (preg_match('#^(?<host>[^:]+):(?<socket>/.+)$#', $host, $m) === 1) {
            return [$m['host'], 3306, $m['socket']];
        }

        // host:porta
        if (preg_match('/^(?<host>[^:]+):(?<port>\d+)$/', $host, $m) === 1) {
            return [$m['host'], (int) $m['port'], null];
        }

        return [$host, (int) Env::get('DB_PORT', '3306') ?: 3306, null];
    }

    /**
     * O servidor de banco atende o esquema? Devolve o motivo quando não atende.
     *
     * MySQL 5.7 — ainda comum em hospedagem compartilhada antiga — não tem
     * REGEXP_REPLACE, que a coluna gerada `document_normalized` da carteira de
     * clientes do PDV usa. A importação do base.sql morria no meio, com um
     * "FUNCTION regexp_replace does not exist" que não diz o que fazer. MariaDB
     * tem a função desde a 10.0.5; a 10.3 é o piso por colunas geradas e JSON.
     */
    public static function serverProblem(mysqli $conn): ?string
    {
        $res = $conn->query('SELECT VERSION()');
        $versao = $res ? (string) ($res->fetch_row()[0] ?? '') : '';
        if ($res instanceof \mysqli_result) {
            $res->free();
        }
        if ($versao === '' || preg_match('/^(\d+\.\d+(?:\.\d+)?)/', $versao, $m) !== 1) {
            return null;
        }

        if (stripos($versao, 'mariadb') !== false) {
            return version_compare($m[1], '10.3.0', '<')
                ? "Este servidor é MariaDB {$m[1]}; o sistema precisa de MariaDB 10.3 ou mais novo (ou MySQL 8). "
                    . 'No painel da hospedagem, peça ou escolha uma versão mais nova do banco.'
                : null;
        }

        return version_compare($m[1], '8.0.0', '<')
            ? "Este servidor é MySQL {$m[1]}; o sistema precisa de MySQL 8 ou MariaDB 10.3+ "
                . '(o MySQL 5.7 não tem a função REGEXP_REPLACE, que o banco usa). '
                . 'No painel da hospedagem, crie o banco num servidor MySQL 8 ou MariaDB, se houver a opção, ou peça ao suporte.'
            : null;
    }

    /** Cria o database (se necessário) e o seleciona. */
    public static function useDatabase(mysqli $conn, string $name): void
    {
        $escaped = $conn->real_escape_string($name);

        if (!$conn->query("CREATE DATABASE IF NOT EXISTS `{$escaped}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            throw new RuntimeException('Falha ao criar database: ' . $conn->error);
        }

        if (!$conn->select_db($name)) {
            throw new RuntimeException('Falha ao selecionar database: ' . $conn->error);
        }
    }

    /**
     * Tabelas existentes no database corrente.
     *
     * @return array<int,string>
     */
    public static function tables(mysqli $conn): array
    {
        $res = $conn->query('SHOW TABLES');
        if ($res === false) {
            return [];
        }

        $tables = [];
        while ($row = $res->fetch_array(MYSQLI_NUM)) {
            $tables[] = (string) $row[0];
        }
        $res->free();

        return $tables;
    }

    /** Existe a tabela informada no database corrente? */
    public static function tableExists(mysqli $conn, string $table): bool
    {
        $stmt = $conn->prepare(
            'SELECT 1 FROM information_schema.tables
              WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $table);
        $stmt->execute();
        $res    = $stmt->get_result();
        $exists = $res !== false && $res->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    /**
     * Executa um arquivo .sql.
     *
     * `multi_query` só reporta o erro do primeiro statement; os demais só
     * aparecem ao percorrer os resultados — daí o laço, que existia mas era
     * copiado com variações em cada arquivo.
     */
    public static function execSqlFile(mysqli $conn, string $file): void
    {
        if (!is_file($file)) {
            throw new RuntimeException('Arquivo SQL não encontrado: ' . basename($file));
        }

        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('Arquivo SQL vazio ou ilegível: ' . basename($file));
        }

        if (!$conn->multi_query($sql)) {
            throw new RuntimeException('Erro ao importar ' . basename($file) . ': ' . $conn->error);
        }

        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
            if ($conn->errno) {
                throw new RuntimeException('Erro ao executar ' . basename($file) . ': ' . $conn->error);
            }
        } while ($conn->more_results() && $conn->next_result());

        if ($conn->errno) {
            throw new RuntimeException('Erro ao executar ' . basename($file) . ': ' . $conn->error);
        }
    }

    /**
     * Remove apenas as tabelas informadas.
     *
     * O rollback anterior fazia `SHOW TABLES` e derrubava tudo o que
     * encontrasse. Como a checagem de "banco já possui tabelas" também termina
     * no mesmo `catch`, apontar o instalador para um banco em uso e clicar em
     * instalar apagava esse banco inteiro — inclusive quando a instalação foi
     * recusada justamente para proteger os dados.
     *
     * Aqui só some o que este instalador criou nesta execução.
     *
     * @param array<int,string> $tables
     */
    public static function dropTables(mysqli $conn, array $tables): void
    {
        if ($tables === []) {
            return;
        }

        @$conn->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            @$conn->query('DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`');
        }
        @$conn->query('SET FOREIGN_KEY_CHECKS = 1');
    }
}
