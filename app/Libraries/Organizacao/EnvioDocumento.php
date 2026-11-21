<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use DomainException;
use Throwable;

class EnvioDocumento
{
    public const FORMATOS = '.pdf,.doc,.docx,.xls,.xlsx,.csv,.xml,image/*';
    public const LIMITE_BYTES = 10 * 1024 * 1024;
    private const MIMES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel', 'application/x-ole-storage', 'application/CDFV2'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'csv' => ['text/csv', 'text/plain', 'application/csv', 'text/tab-separated-values'],
        'xml' => ['application/xml', 'text/xml', 'text/plain'],
    ];

    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
    }

    public function opcoes(): array
    {
        return $this->banco->table('tipos_documento t')->select('t.id, t.nome')->distinct()
            ->join('tipo_documento_versoes v', 'v.organizacao_id = t.organizacao_id AND v.tipo_documento_id = t.id')
            ->where(['t.organizacao_id' => $this->empresa, 't.ativo' => 1])
            ->where('v.publicado_em <=', date('Y-m-d H:i:s'))->orderBy('t.nome')->get()->getResultArray();
    }

    /** Usa a primeira situação ativa ou prepara Recebido no primeiro envio. */
    public function statusInicial(string $agora): string
    {
        $escopo = ['organizacao_id' => $this->empresa];
        $situacoes = $this->banco->table('status_documento');
        $ativa = $situacoes->select('id')->where($escopo + ['ativo' => 1])->orderBy('ordem')->orderBy('id')->get(1)->getRowArray();
        if ($ativa) return (string) $ativa['id'];

        $recebido = $situacoes->select('id')->where($escopo + ['nome' => 'Recebido'])->get()->getRowArray();
        if ($recebido) {
            $situacoes->where($escopo + ['id' => $recebido['id']])->update(['ativo' => 1, 'atualizado_em' => $agora]);
            return (string) $recebido['id'];
        }
        $situacoes->insert($escopo + ['nome' => 'Recebido', 'ordem' => 0, 'ativo' => 1, 'criado_em' => $agora]);
        return (string) $this->banco->insertID();
    }

    public function salvar(string $hashOrganizacao, string $processo, string $tipo, string $titulo, string $ano, array $setor, string $autor, ?string $leitor, ?UploadedFile $arquivo): void
    {
        if ($arquivo && ($arquivo->getError() === UPLOAD_ERR_INI_SIZE || $arquivo->getSize() > self::LIMITE_BYTES)) {
            throw new DomainException('O arquivo deve ter no máximo 10 MB.');
        }
        if (!$arquivo || !$arquivo->isValid() || $arquivo->getSize() < 1) {
            throw new DomainException('Escolha um arquivo válido para enviar.');
        }
        $nome = basename(str_replace('\\', '/', $arquivo->getClientName()));
        $nome = trim(preg_replace('/[\x00-\x1f\x7f]/', '', $nome));
        $extensao = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
        $mime = $arquivo->getMimeType();
        if ($nome === '' || !mb_check_encoding($nome, 'UTF-8') || mb_strlen($nome) > 255
            || (!str_starts_with($mime, 'image/') && !in_array($mime, self::MIMES[$extensao] ?? [], true))) {
            throw new DomainException('O formato do arquivo não é compatível com o envio.');
        }
        $tamanho = $arquivo->getSize();
        $hash = hash_file('sha256', $arquivo->getTempName());
        if (!$hash) throw new DomainException('Não foi possível ler o arquivo.');

        $diretorio = WRITEPATH . 'uploads/' . $hashOrganizacao;
        $chave = bin2hex(random_bytes(24));
        $caminho = $diretorio . DIRECTORY_SEPARATOR . $chave;
        $escopo = ['organizacao_id' => $this->empresa];
        $agora = date('Y-m-d H:i:s');
        $movido = false;
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar o envio.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $consulta = $this->banco->table('processos p')
                ->where(['p.organizacao_id' => $this->empresa, 'p.id' => $processo, 'p.excluido_em' => null])
                ->where(ContextoSetor::condicaoProcessos($this->banco, $setor['id'], 'p'), null, false);
            if ($leitor !== null) $consulta->where(PoliticaProcesso::condicao($this->banco, 'p', $leitor), null, false);
            $valido = $consulta->countAllResults();
            $versao = $this->versaoPublicada($tipo, $agora);
            if (!$valido || !$versao) throw new DomainException('Confira se o processo e o tipo de documento continuam disponíveis.');
            $status = $this->statusInicial($agora);
            $numero = $this->reservarNumero($tipo, $ano, $agora);

            if (!is_dir($diretorio) && !@mkdir($diretorio, 0700, true) && !is_dir($diretorio)) {
                throw new DomainException('O armazenamento de documentos está indisponível.');
            }
            $movido = $arquivo->move($diretorio, $chave);
            $this->banco->table('arquivos')->insert($escopo + [
                'chave_armazenamento' => $hashOrganizacao . '/' . $chave, 'nome_original' => $nome, 'mime_type' => $mime,
                'tamanho_bytes' => $tamanho, 'hash' => $hash, 'algoritmo_hash' => 'sha256',
                'status_verificacao' => 'pendente', 'criado_por' => $autor, 'criado_em' => $agora,
            ]);
            $idArquivo = $this->banco->insertID();
            $this->banco->table('documentos')->insert($escopo + [
                'processo_principal_id' => $processo, 'tipo_documento_id' => $tipo, 'tipo_documento_versao_id' => $versao['id'],
                'titulo' => $titulo !== '' ? $titulo : (pathinfo($nome, PATHINFO_FILENAME) ?: $nome), 'origem' => 'upload', 'status_id' => $status,
                'numero_sequencial' => $numero, 'periodo_numeracao' => $ano,
                'numero_completo' => sprintf('DOC-T%s-%04d/%s', $tipo, $numero, $ano),
                'setor_origem_id' => $setor['id'], 'unidade_origem_id' => $setor['unidade'],
                'criado_por' => $autor, 'criado_em' => $agora,
            ]);
            $documento = $this->banco->insertID();
            $this->banco->table('documento_versoes')->insert($escopo + [
                'documento_id' => $documento, 'numero_versao' => 1, 'arquivo_original_id' => $idArquivo,
                'hash_conteudo' => $hash, 'algoritmo_hash' => 'sha256', 'status_id' => $status,
                'criada_por' => $autor, 'criado_em' => $agora,
            ]);
            $idVersao = $this->banco->insertID();
            $this->banco->table('documentos')->where($escopo + ['id' => $documento])->update(['versao_atual_id' => $idVersao]);
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) throw new DomainException('Não foi possível concluir o envio.');
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            if ($movido && is_file($caminho)) unlink($caminho);
            throw $erro;
        }
    }

    /** Reserva o próximo número sob o bloqueio da organização, na transação do envio. */
    public function reservarNumero(string $tipo, string $ano, string $agora): int
    {
        $escopo = ['organizacao_id' => $this->empresa, 'tipo_documento_id' => $tipo];
        $chave = $escopo + ['periodo' => $ano];
        $sequencias = $this->banco->table('sequencias_documento');
        $contador = $sequencias->where($chave)->get()->getRowArray();
        $ultima = $this->banco->table('documentos')->where($escopo + ['periodo_numeracao' => $ano])
            ->selectMax('numero_sequencial')->get()->getRowArray();
        $base = max((int) ($contador['ultimo_numero'] ?? 0), (int) ($ultima['numero_sequencial'] ?? 0));
        if ($base === PHP_INT_MAX) throw new DomainException('A sequência anual deste tipo de documento atingiu o limite.');
        $numero = $base + 1;
        if ($contador) {
            $sequencias->where($chave)->update(['ultimo_numero' => $numero, 'atualizado_em' => $agora]);
        } else {
            $sequencias->insert($chave + ['ultimo_numero' => $numero, 'criado_em' => $agora]);
        }
        return $numero;
    }

    public function versaoPublicada(string $tipo, string $agora): ?array
    {
        return $this->banco->table('tipo_documento_versoes v')->select('v.id')
            ->join('tipos_documento t', 't.organizacao_id = v.organizacao_id AND t.id = v.tipo_documento_id')
            ->where(['v.organizacao_id' => $this->empresa, 'v.tipo_documento_id' => $tipo, 't.ativo' => 1])
            ->where('v.publicado_em <=', $agora)->orderBy('v.versao', 'DESC')->get(1)->getRowArray();
    }
}
