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

namespace DropGenesis\Contracts;

interface DriverInterface
{
    /**
     * Retorna uma matriz que descreve quais recursos este driver suporta.
     * Exemplos de chaves: 'database', 'license_verify', 'bootstrap', 'multi_db'
     *
     * @return array<string,bool>
     */
    public function getCapabilities(): array;

    public function detectDatabaseUrl(bool $isHttps): string;
    public function verifyLicense(string $key, string $domain): array;
    public function installDatabase(\mysqli $conn, string $sqlFile): void;
    public function bootstrapApplication(array $config): void;
}
