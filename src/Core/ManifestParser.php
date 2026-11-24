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

namespace DropGenesis\Core;

class ManifestParser
{
    private array $data = [];

    /**
     * O manifesto é lido a cada requisição, de propósito.
     *
     * Ele era copiado para $_SESSION com a chave derivada do mtime. O ganho é
     * nulo — são ~700 bytes de JSON — e o custo é gravar a sessão em disco a
     * cada página. Numa hospedagem compartilhada, com sessão em arquivo, essa
     * gravação custa mais do que o json_decode que ela evitava.
     */
    public function __construct(string $filePath)
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException("Manifest file not found: " . basename($filePath));
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Cannot read manifest file: " . basename($filePath));
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON manifest: " . json_last_error_msg());
        }

        $this->data = is_array($decoded) ? $decoded : [];
        $this->validate();
    }

    private function validate(): void
    {
        $requiredKeys = ['name', 'version', 'driver', 'requirements'];
        foreach ($requiredKeys as $key) {
            if (!isset($this->data[$key])) {
                throw new \RuntimeException("Missing required manifest key: " . $key);
            }
        }
    }

    public function getName(): string
    {
        return (string) ($this->data['name'] ?? '');
    }

    public function getVersion(): string
    {
        return (string) ($this->data['version'] ?? '0.0.0');
    }

    public function getDriver(): string
    {
        return (string) ($this->data['driver'] ?? 'native');
    }

    public function getRequirements(): array
    {
        return (array) ($this->data['requirements'] ?? []);
    }

    public function getDatabaseConfig(): array
    {
        return (array) ($this->data['database'] ?? []);
    }

    /**
     * Migrações opcionais declaradas no manifesto, já filtradas pelas que
     * existem em disco.
     *
     * O instalador oferecia três caixas de seleção — hardening, estrutura e
     * fase 2 — marcadas por padrão, para arquivos .sql que não estão no
     * projeto. Quem instalava via as três opções, marcava, e a mensagem final
     * dizia apenas "Instalação concluída", sem qualquer indício de que nada
     * havia sido aplicado. Agora só aparece o que realmente pode rodar.
     *
     * @param  string $appRoot Raiz da aplicação em disco.
     * @return array<int,array{file:string,path:string,label:string,hint:string}>
     */
    public function getOptionalMigrations(string $appRoot): array
    {
        $declared = (array) ($this->getDatabaseConfig()['optional_migrations'] ?? []);
        $appRoot  = rtrim(str_replace('\\', '/', $appRoot), '/');

        $available = [];
        foreach ($declared as $migration) {
            $relative = trim((string) ($migration['file'] ?? ''));
            if ($relative === '') {
                continue;
            }

            $path = $appRoot . '/' . ltrim(str_replace('\\', '/', $relative), '/');
            if (!is_file($path)) {
                continue;
            }

            $available[] = [
                'file'  => $relative,
                'path'  => $path,
                'label' => (string) ($migration['label'] ?? $relative),
                'hint'  => (string) ($migration['hint'] ?? ''),
            ];
        }

        return $available;
    }

    /**
     * Valores iniciais sugeridos no formulário de instalação.
     *
     * @return array<string,string>
     */
    public function getDefaults(): array
    {
        return array_map('strval', (array) ($this->data['defaults'] ?? []));
    }

    /**
     * Optional post-install hooks defined in the manifest.
     * Example in drop.json:
     *   "post_install": { "event": "after_install", "url": "/setup/done" }
     */
    public function getPostInstall(): array
    {
        return (array) ($this->data['post_install'] ?? []);
    }

    /**
     * Raw access to any manifest key not covered by an explicit accessor.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
