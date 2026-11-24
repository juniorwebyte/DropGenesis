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

/**
 * Verificação de chave de licença — implementação única.
 *
 * Havia três versões deste código no projeto (embutida no index.php, no
 * ajax.php e no NativeDriver), com regras diferentes entre si:
 *
 *  - As três montavam a URL do verificador com expressões regulares distintas.
 *    Duas delas produziam `…/drop/drop/api/v1/verify.php`, com o segmento
 *    repetido, porque removiam `/DropGenesis/index.php` do SCRIPT_NAME e em
 *    seguida concatenavam `/drop/…` de novo.
 *  - O NativeDriver aceitava como válida, sem consultar nada, qualquer chave
 *    começada em "WEBYTE" ou igual a "TESTE" — um bypass completo do
 *    licenciamento, gravado no código distribuído.
 *  - Todas apontavam para `api/v1/verify.php` da própria instalação. Esse
 *    endpoint exige `api/config.php` e consulta a tabela `api_licenses` — dois
 *    artefatos que só passam a existir DEPOIS da instalação. Durante o
 *    instalador ele responde 500, então nenhuma chave real conseguia passar e
 *    a instalação só concluía digitando "dev".
 *
 * O endpoint agora é configurável e a origem dele é explícita para o operador.
 */
final class License
{
    /** Chaves de desenvolvimento aceitas sem rede (comportamento preservado). */
    private const DEV_KEYS = ['dev', 'dev_mode'];

    /** Resultado: chave aceita por um verificador remoto. */
    public const MODE_REMOTE = 'remote';

    /** Resultado: nenhum verificador configurado; chave registrada sem checagem. */
    public const MODE_OFFLINE = 'offline';

    /** Resultado: chave de desenvolvimento. */
    public const MODE_DEV = 'dev';

    private ManifestParser $manifest;

    public function __construct(ManifestParser $manifest)
    {
        $this->manifest = $manifest;
    }

    /**
     * Endpoint de verificação, ou null quando não há nenhum utilizável.
     *
     * Ordem de precedência:
     *  1. variável de ambiente `DROP_LICENSE_URL` (Docker / painel do VPS);
     *  2. `license.verify_url` no drop.json (distribuição);
     *  3. o `api/v1/verify.php` local — e SOMENTE se `api/config.php` já
     *     existir, porque antes disso o endpoint é comprovadamente incapaz de
     *     responder.
     */
    public function endpoint(): ?string
    {
        $fromEnv = trim(Env::get('DROP_LICENSE_URL'));
        if ($fromEnv !== '') {
            return $fromEnv;
        }

        $license = (array) $this->manifest->get('license', []);
        $fromManifest = trim((string) ($license['verify_url'] ?? ''));
        if ($fromManifest !== '') {
            return $fromManifest;
        }

        if (is_file(Paths::appFile('api/config.php'))) {
            return Paths::scheme() . '://' . Paths::host() . Paths::basePath() . '/api/v1/verify.php';
        }

        return null;
    }

    /**
     * Verifica uma chave.
     *
     * @return array{valid:bool,verified:bool,mode:string,message:string}
     *         `verified` distingue "conferida e aprovada" de "aceita sem
     *         conferência" — o instalador mostra os dois casos de forma
     *         diferente em vez de fingir que são a mesma coisa.
     */
    public function verify(string $key, ?string $domain = null): array
    {
        $key    = trim($key);
        $domain = $domain ?? Paths::host();

        if ($key === '') {
            return $this->fail('Chave de licença vazia.');
        }

        if (in_array(strtolower($key), self::DEV_KEYS, true)) {
            return [
                'valid'    => true,
                'verified' => true,
                'mode'     => self::MODE_DEV,
                'message'  => 'Licença válida (Modo Desenvolvimento).',
            ];
        }

        if (!$this->looksLikeKey($key)) {
            return $this->fail('Formato de chave inválido.');
        }

        $endpoint = $this->endpoint();

        if ($endpoint === null) {
            // Sem verificador não existe resposta honesta de "válida". A chave
            // é registrada e a aplicação a revalida depois de instalada, com a
            // API no ar. Travar aqui apenas tornaria o produto impossível de
            // instalar, que é exatamente o estado anterior.
            return [
                'valid'    => true,
                'verified' => false,
                'mode'     => self::MODE_OFFLINE,
                'message'  => 'Nenhum verificador de licença configurado — a chave será registrada e validada após a instalação.',
            ];
        }

        return $this->askEndpoint($endpoint, $key, $domain);
    }

    // ---------------------------------------------------------------------
    // Internos
    // ---------------------------------------------------------------------

    /**
     * @return array{valid:bool,verified:bool,mode:string,message:string}
     */
    private function askEndpoint(string $endpoint, string $key, string $domain): array
    {
        if (!function_exists('curl_init')) {
            return $this->fail('Extensão cURL indisponível: não é possível validar a licença.');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['key' => $key, 'domain' => $domain]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_FOLLOWLOCATION => false,
            // A verificação de certificado fica ligada. Ela só é dispensada
            // quando o operador assume o risco explicitamente, para cenários
            // de homologação com certificado autoassinado.
            CURLOPT_SSL_VERIFYPEER => self::strictTls(),
            CURLOPT_SSL_VERIFYHOST => self::strictTls() ? 2 : 0,
        ]);

        $response = curl_exec($ch);
        $status   = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlErr  = curl_error($ch);

        if ($response === false || $response === '') {
            return $this->fail('Verificador de licença inacessível' . ($curlErr !== '' ? ' (' . $curlErr . ')' : '') . '.');
        }

        $data = json_decode((string) $response, true);
        if (!is_array($data)) {
            return $this->fail('Resposta inválida do verificador de licença (HTTP ' . $status . ').');
        }

        if (($data['valid'] ?? false) === true) {
            return [
                'valid'    => true,
                'verified' => true,
                'mode'     => self::MODE_REMOTE,
                'message'  => (string) ($data['message'] ?? 'Licença ativa.'),
            ];
        }

        return $this->fail((string) ($data['message'] ?? 'Chave recusada pelo verificador.'));
    }

    /**
     * Filtro de formato — evita gastar uma ida à rede com entrada obviamente
     * inválida e barra a chave vazia/absurda no modo offline.
     */
    private function looksLikeKey(string $key): bool
    {
        $length = strlen($key);
        return $length >= 6 && $length <= 128 && (bool) preg_match('/^[A-Za-z0-9\-_\.]+$/', $key);
    }

    private static function strictTls(): bool
    {
        $relaxed = strtolower(trim(Env::get('DROP_LICENSE_INSECURE_TLS')));
        return !in_array($relaxed, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return array{valid:bool,verified:bool,mode:string,message:string}
     */
    private function fail(string $message): array
    {
        return [
            'valid'    => false,
            'verified' => true,
            'mode'     => self::MODE_REMOTE,
            'message'  => $message,
        ];
    }
}
