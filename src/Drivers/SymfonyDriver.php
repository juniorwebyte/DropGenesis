<?php

namespace DropGenesis\Drivers;

use DropGenesis\Contracts\DriverInterface;
use DropGenesis\Core\Paths;

class SymfonyDriver implements DriverInterface
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
        $native = new NativeDriver($this->rootPath);
        return $native->verifyLicense($key, $domain);
    }

    public function installDatabase(\mysqli $conn, string $sqlFile): void
    {
        if (is_file($sqlFile)) {
            $native = new NativeDriver($this->rootPath);
            $native->installDatabase($conn, $sqlFile);
        }
    }

    public function bootstrapApplication(array $config): void
    {
        // No Symfony moderno (.env local / DATABASE_URL)
        $envPath = $this->rootPath . '/.env.local';
        if (!is_file($envPath)) {
            $envPath = $this->rootPath . '/.env';
        }

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

                // URL format: mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0
                $passEscaped = urlencode($config['DB_PASS'] ?? '');
                $dbUrl = sprintf(
                    'mysql://%s:%s@%s/%s?serverVersion=8.0',
                    $config['DB_USER'] ?? '',
                    $passEscaped,
                    $config['DB_HOST'] ?? '127.0.0.1',
                    $config['DB_NAME'] ?? ''
                );

                $symfonyMap = [
                    'DATABASE_URL' => $dbUrl,
                    'APP_ENV'      => $config['APP_ENV'] ?? 'prod',
                ];

                foreach ($symfonyMap as $key => $val) {
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
