<?php

namespace DropGenesis\Core;

/**
 * O pacote chegou inteiro?
 *
 * Numa hospedagem compartilhada o pacote chega por FTP ou pelo gerenciador de
 * arquivos do painel, e é aí que arquivo se perde: o upload de milhares de
 * arquivos cai no meio, a extração do zip é interrompida, e a InfinityFree
 * descarta em silêncio todo arquivo de 10 MB ou mais. O sintoma aparecia só
 * depois de instalar, como erro fatal em alguma tela ("Failed opening required
 * .../libs/..."), longe da causa.
 *
 * scripts/build_release.php grava no pacote o manifesto (install/pacote.json):
 * cada arquivo, com tamanho e SHA-1. Aqui se confere existência e tamanho —
 * rápido o bastante para cada abertura do assistente, e pega tanto o arquivo
 * que não chegou quanto o que chegou pela metade.
 */
final class Integrity
{
    /**
     * @return array{manifesto: bool, versao: string, total: int, ausentes: list<string>, incompletos: list<string>}
     */
    public static function check(string $root, string $manifestRel): array
    {
        $resultado = ['manifesto' => false, 'versao' => '', 'total' => 0, 'ausentes' => [], 'incompletos' => []];

        $caminho = rtrim($root, '/\\') . '/' . ltrim($manifestRel, '/');
        if (!is_file($caminho)) {
            // Instalação a partir do repositório (sem pacote): não há o que conferir.
            return $resultado;
        }

        $resultado['manifesto'] = true;
        $dados = json_decode((string) file_get_contents($caminho), true);
        if (!is_array($dados) || !is_array($dados['arquivos'] ?? null)) {
            $resultado['incompletos'][] = ltrim($manifestRel, '/');
            return $resultado;
        }

        $resultado['versao'] = (string) ($dados['versao'] ?? '');
        foreach ($dados['arquivos'] as $rel => $info) {
            $rel = (string) $rel;
            $resultado['total']++;
            $abs = rtrim($root, '/\\') . '/' . $rel;
            if (!is_file($abs)) {
                $resultado['ausentes'][] = $rel;
                continue;
            }
            $esperado = is_array($info) ? (int) ($info[0] ?? -1) : -1;
            if ($esperado >= 0 && filesize($abs) !== $esperado) {
                $resultado['incompletos'][] = $rel;
            }
        }

        return $resultado;
    }
}
