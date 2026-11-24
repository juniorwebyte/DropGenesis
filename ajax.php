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

use DropGenesis\Core\Database;
use DropGenesis\Core\Engine;

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Registra autoloader PSR-4 dinâmico
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

/**
 * @param array<string,mixed> $payload
 */
function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$engine = new Engine(dirname(__DIR__), __FILE__);

// Estes endpoints só fazem sentido enquanto a instalação não terminou. Depois
// dela, continuavam abertos: qualquer visitante podia usar `test_db` como
// sonda de credenciais de MySQL contra hosts arbitrários.
if (\DropGenesis\Core\InstallState::isInstalled()) {
    respond(['success' => false, 'valid' => false, 'message' => 'Instalador desativado.'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    respond(['success' => false, 'valid' => false, 'message' => 'Método não permitido.'], 405);
}

// Validação de CSRF. Sem `consume`: estas são ações de leitura, e invalidar o
// token aqui derrubava o envio do formulário logo em seguida.
try {
    $submittedToken = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $engine->validateCsrfToken((string) $submittedToken);
} catch (\RuntimeException $e) {
    respond([
        'success' => false,
        'valid'   => false,
        'message' => 'Sessão expirada. Recarregue a página do instalador e tente novamente.',
    ], 419);
}

$action = (string) ($_POST['action'] ?? '');

if ($action === 'test_db') {
    $host = (string) ($_POST['host'] ?? '');
    $user = (string) ($_POST['user'] ?? '');
    $pass = (string) ($_POST['pass'] ?? '');
    $name = trim((string) ($_POST['name'] ?? ''));

    try {
        // Mesmo parser de host da instalação: o teste passa a cobrir
        // `host:porta` e socket unix, que antes acusavam falha de conexão
        // aqui e só funcionavam (ou não) na hora de instalar.
        $conn = Database::connect($host, $user, $pass);
    } catch (\RuntimeException $e) {
        respond(['success' => false, 'message' => $e->getMessage()]);
    }

    $serverVersion = $conn->server_info;

    // Banco que não atende o esquema é recusado AQUI, no teste, e não no meio
    // da importação.
    $problemaServidor = Database::serverProblem($conn);
    if ($problemaServidor !== null) {
        $conn->close();
        respond(['success' => false, 'message' => $problemaServidor]);
    }

    if ($name !== '') {
        if (!@$conn->select_db($name)) {
            $conn->close();
            respond([
                'success' => true,
                'message' => 'Conexão OK (MySQL ' . $serverVersion . '). O banco "' . $name . '" ainda não existe e será criado na instalação.',
            ]);
        }

        // Saber de antemão que o banco não está vazio evita descobrir isso só
        // depois de preencher o formulário inteiro e clicar em instalar.
        $tables = Database::tables($conn);
        $temDrop = in_array('api_admins', $tables, true) || in_array('api_licenses', $tables, true);
        $conn->close();

        if ($tables !== [] && $temDrop) {
            // Banco com o Drop (importado pelo phpMyAdmin, ou de outra máquina):
            // o assistente conecta a ele em vez de recusar — ver index.php.
            respond([
                'success' => true,
                'message' => 'Conexão OK (MySQL ' . $serverVersion . '). O banco "' . $name . '" já tem uma instalação do Drop ('
                    . count($tables) . ' tabelas): o assistente vai CONECTAR a ela, sem importar nem apagar nada. '
                    . 'Em "Administrador", informe o usuário e a senha de um administrador que já existe nesse banco.',
            ]);
        }

        respond([
            'success' => $tables === [],
            'message' => $tables === []
                ? 'Conexão OK (MySQL ' . $serverVersion . '). O banco "' . $name . '" existe e está vazio — pronto para instalar.'
                : 'Conexão OK, mas o banco "' . $name . '" já possui ' . count($tables) . ' tabelas de outra aplicação. Use um banco vazio para instalar com segurança.',
        ]);
    }

    $conn->close();
    respond(['success' => true, 'message' => 'Conexão com o servidor MySQL ' . $serverVersion . ' estabelecida com sucesso!']);
}

if ($action === 'verify_license') {
    // Implementação única em \DropGenesis\Core\License. Este arquivo mantinha
    // a sua própria cópia, com outra regra de montagem de URL e outro texto de
    // erro do que o index.php usava no envio final — dava para ver "licença
    // válida" no botão de testar e "licença recusada" ao instalar.
    $result = $engine->getLicense()->verify((string) ($_POST['key'] ?? ''));

    respond($result);
}

respond(['success' => false, 'valid' => false, 'message' => 'Ação inválida.'], 400);
