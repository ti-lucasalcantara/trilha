<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use Throwable;

class EditorDocumento
{
    private readonly EnvioDocumento $documentos;

    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
        $this->documentos = new EnvioDocumento($banco, $empresa);
    }

    public static function limpar(string $conteudo): string
    {
        return DescricaoProcesso::limpar(
            $conteudo,
            ['div', 'h2', 'h3', 'blockquote', 'b', 'i', 's', 'strike', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
            preservarAlinhamento: true,
        );
    }

    private function rascunhos(string $processo, string $setor, ?string $autor)
    {
        $consulta = $this->banco->table('rascunhos_documento')->where([
            'organizacao_id' => $this->empresa, 'processo_id' => $processo, 'setor_id' => $setor,
        ]);
        return $autor === null ? $consulta : $consulta->where('autor_id', $autor);
    }

    public function listar(string $processo, string $setor, ?string $autor): array
    {
        return $this->rascunhos($processo, $setor, $autor)->select('chave, titulo')
            ->where('documento_id', null)->orderBy('atualizado_em', 'DESC')->get()->getResultArray();
    }

    public function obter(string $processo, string $setor, ?string $autor, string $chave): ?array
    {
        return $this->rascunhos($processo, $setor, $autor)->where('chave', $chave)->get()->getRowArray();
    }

    public function salvar(string $processo, array $setor, ?string $vinculo, string $autor, array $dados, string $ano, bool $finalizar): ?string
    {
        $agora = date('Y-m-d H:i:s');
        $escopo = ['organizacao_id' => $this->empresa];
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar a gravação.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $processos = new ConsultaProcessos($this->banco, $this->empresa, (string) $setor['id']);
            if (!$processos->listar($vinculo, $dados['hash_processo'])) {
                throw new DomainException('O processo está indisponível no setor selecionado.');
            }
            $rascunho = $this->obter($processo, $setor['id'], $vinculo, $dados['chave']);
            if ($rascunho && $rascunho['documento_id'] !== null && $finalizar) {
                $this->banco->transCommit();
                return (string) $rascunho['documento_id'];
            }
            if (($rascunho['revisao'] ?? '') !== $dados['revisao'] || ($rascunho && $rascunho['documento_id'] !== null)) {
                throw new DomainException('O rascunho mudou em outra aba. Volte ao processo e reabra a versão salva.');
            }
            $versao = $this->documentos->versaoPublicada($dados['tipo'], $agora);
            if (!$versao) throw new DomainException('Selecione um tipo de documento ativo.');
            $registro = [
                'tipo_id' => $dados['tipo'], 'autor_id' => $autor, 'titulo' => $dados['titulo'],
                'conteudo' => $dados['conteudo'], 'atualizado_em' => $agora, 'revisao' => bin2hex(random_bytes(24)),
            ];
            $documento = null;
            if ($finalizar) {
                $status = $this->documentos->statusInicial($agora);
                $numero = $this->documentos->reservarNumero($dados['tipo'], $ano, $agora);
                $this->banco->table('documentos')->insert($escopo + [
                    'processo_principal_id' => $processo, 'tipo_documento_id' => $dados['tipo'],
                    'tipo_documento_versao_id' => $versao['id'], 'titulo' => $dados['titulo'],
                    'origem' => 'editor_trilha', 'status_id' => $status, 'numero_sequencial' => $numero,
                    'periodo_numeracao' => $ano, 'numero_completo' => sprintf('DOC-T%s-%04d/%s', $dados['tipo'], $numero, $ano),
                    'unidade_origem_id' => $setor['unidade'], 'setor_origem_id' => $setor['id'],
                    'criado_por' => $autor, 'criado_em' => $agora,
                ]);
                $documento = (string) $this->banco->insertID();
                $this->banco->table('documento_versoes')->insert($escopo + [
                    'documento_id' => $documento, 'numero_versao' => 1, 'conteudo_editor' => $dados['conteudo'],
                    'hash_conteudo' => hash('sha256', $dados['conteudo']), 'algoritmo_hash' => 'sha256',
                    'status_id' => $status, 'criada_por' => $autor, 'criado_em' => $agora, 'finalizada_em' => $agora,
                ]);
                $idVersao = $this->banco->insertID();
                $this->banco->table('documentos')->where($escopo + ['id' => $documento])->update(['versao_atual_id' => $idVersao]);
                $registro['documento_id'] = $documento;
                $registro['conteudo'] = null;
            }
            if ($rascunho) {
                $this->rascunhos($processo, $setor['id'], $vinculo)->where('chave', $dados['chave'])->update($registro);
            } else {
                $this->banco->table('rascunhos_documento')->insert($escopo + $registro + [
                    'chave' => $dados['chave'], 'processo_id' => $processo, 'setor_id' => $setor['id'],
                ]);
            }
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) throw new DomainException('Não foi possível salvar o documento.');
            return $documento;
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }
}
