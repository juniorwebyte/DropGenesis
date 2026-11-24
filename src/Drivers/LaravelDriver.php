<?php

namespace DropGenesis\Drivers;

use DropGenesis\Contracts\DriverInterface;
use DropGenesis\Core\Paths;

class LaravelDriver implements DriverInterface
{
    private string $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = rtrim($rootPath, '/\\');
    }

    public function getCapabilities(): array
    {
        return [
            'database'        => true,
            'license_verify'  => true,
            'bootstrap'       => true,
            'multi_db'        => false,
        ];
    }

    public function detectDatabaseUrl(bool $isHttps): string
    {
        return Paths::baseUrl();
    }

    public function verifyLicense(string $key, string $domain): array
    {
        // Utiliza a validação nativa padrão
        $native = new NativeDriver($this->rootPath);
        return $native->verifyLicense($key, $domain);
    }

    public function installDatabase(\mysqli $conn, string $sqlFile): void
    {
        // Em um driver Laravel típico, nós podemos rodar as migrations do artisan
        // Mas se houver base.sql, executamos via NativeDriver
        if (is_file($sqlFile)) {
            $native = new NativeDriver($this->rootPath);
            $native->installDatabase($conn, $sqlFile);
        }

        // Aqui o desenvolvedor pode invocar opcionalmente:
        // shell_exec("php " . escapeshellarg($this->rootPath . '/artisan') . " migrate --force");
    }

    public function bootstrapApplication(array $config): void
    {
        // No Laravel, atualiza o .env e limpa o config cache
        $envPath = $this->rootPath . '/.env';
        if (is_file($envPath)) {
            $rawLines = file($envPath, FILE_IGNORE_NEW_LINES);
            if ($rawLines !== false) {
                $keyToLine = [];
                foreach ($rawLines as $i => $line) {
                    if (str_contains($line, '=') && !str_starts_with(ltrim($line), '#')) {
                        [$k] = explode('=', $line, 2);
                        $keyToLine[trim($k)] = $i;
                    }
                }

                // Traduz as chaves padrão para chaves Laravel
                $laravelMap = [
                    'DB_HOST'     => $config['DB_HOST'] ?? '',
                    'DB_USERNAME' => $config['DB_USER'] ?? '',
                    'DB_PASSWORD' => $config['DB_PASS'] ?? '',
                    'DB_DATABASE' => $config['DB_NAME'] ?? '',
                    'APP_ENV'     => $config['APP_ENV'] ?? 'production',
                ];

                foreach ($laravelMap as $key => $val) {
                    if (isset($keyToLine[$key])) {
                        $rawLines[$keyToLine[$key]] = $key . '=' . $val;
                    } else {
                        $rawLines[] = $key . '=' . $val;
                    }
                }
                file_put_contents($envPath, implode("\n", $rawLines) . "\n");
            }
        }
    }
}
