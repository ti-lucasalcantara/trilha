<?php

namespace App\Libraries\Admin;

use CodeIgniter\Database\BaseConnection;
use App\Libraries\Organizacao\ContextoSetor;

/** Consulta dados de uma empresa sem alterar seu conteúdo ou suas permissões. */
class ConsultaEmpresa
{
    private const INVENTARIO = [
        'processos' => ['Processos', ['numero_completo' => 'Número', 'assunto' => 'Assunto', 'aberto_em' => 'Abertura']],
        'documentos' => ['Documentos', ['numero_completo' => 'Número', 'titulo' => 'Título', 'origem' => 'Origem', 'criado_em' => 'Inclusão']],
        'tramitacoes' => ['Tramitações', ['processo_id' => 'Processo (ID)', 'documento_id' => 'Documento (ID)', 'finalidade' => 'Finalidade', 'enviada_em' => 'Envio']],
        'arquivos' => ['Arquivos', ['nome_original' => 'Nome', 'mime_type' => 'Tipo', 'tamanho_bytes' => 'Tamanho (bytes)', 'criado_em' => 'Inclusão']],
        'pastas' => ['Pastas', ['nome' => 'Nome', 'visibilidade' => 'Visibilidade', 'criado_em' => 'Inclusão']],
        'unidades' => ['Unidades', ['nome' => 'Nome', 'sigla' => 'Sigla', 'status' => 'Situação']],
        'setores' => ['Setores', ['nome' => 'Nome', 'sigla' => 'Sigla', 'unidade_id' => 'Unidade (ID)', 'status' => 'Situação']],
        'perfis' => ['Perfis', ['nome' => 'Nome', 'descricao' => 'Descrição', 'status' => 'Situação']],
        'tipos_processo' => ['Tipos de processo', ['nome' => 'Nome', 'sigla' => 'Sigla', 'descricao' => 'Descrição']],
        'tipos_documento' => ['Tipos de documento', ['nome' => 'Nome', 'sigla' => 'Sigla', 'descricao' => 'Descrição']],
        'status_processo' => ['Situações de processo', ['nome' => 'Nome', 'descricao' => 'Descrição', 'ordem' => 'Ordem']],
        'status_documento' => ['Situações de documento', ['nome' => 'Nome', 'descricao' => 'Descrição', 'ordem' => 'Ordem']],
        'etiquetas' => ['Etiquetas', ['nome' => 'Nome', 'escopo' => 'Âmbito', 'cor' => 'Cor']],
        'blocos_assinatura' => ['Blocos de assinatura', ['nome' => 'Nome', 'descricao' => 'Descrição', 'status' => 'Situação']],
        'assinaturas' => ['Assinaturas', ['nome_registrado' => 'Signatário', 'documento_versao_id' => 'Versão do documento (ID)', 'metodo' => 'Método', 'assinada_em' => 'Assinatura']],
        'contratacoes' => ['Contratações', ['plano_versao_id' => 'Versão do plano (ID)', 'status' => 'Situação', 'inicio_em' => 'Início', 'encerramento_em' => 'Encerramento']],
        'cobrancas' => ['Cobranças registradas', ['referencia' => 'Referência', 'valor' => 'Valor', 'moeda' => 'Moeda', 'vencimento' => 'Vencimento', 'status' => 'Situação']],
    ];

    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
    }

    /** Lista os usuários internos aptos a compor uma prévia da empresa. */
    public function usuarios(): array
    {
        return $this->banco->table('vinculos_organizacao vinculo')
            ->select('usuario.id, usuario.nome, usuario.email, vinculo.id AS vinculo_id')
            ->join('usuarios usuario', 'usuario.id = vinculo.usuario_id')
            ->where([
                'vinculo.organizacao_id' => $this->empresa, 'vinculo.tipo_vinculo' => 'interno',
                'vinculo.status' => 'ativo', 'vinculo.fim_em' => null, 'usuario.status' => 'ativo',
            ])
            ->where('vinculo.inicio_em <=', date('Y-m-d H:i:s'))
            ->orderBy('usuario.nome')->get()->getResultArray();
    }

    /** Apura os bytes dos arquivos registrados e a cota da versão contratada. */
    public function armazenamento(string $versao): array
    {
        $total = $this->banco->table('arquivos')->selectSum('tamanho_bytes', 'total')
            ->where('organizacao_id', $this->empresa)->get()->getRowArray();
        $versaoPlano = $versao === '' ? [] : $this->banco->table('plano_versoes v')
            ->select('v.limites, p.nome')->join('planos p', 'p.id = v.plano_id')->where('v.id', $versao)->get()->getRowArray();
        $limites = json_decode($versaoPlano['limites'] ?? '{}', true);
        $cota = isset($limites['armazenamento_gb']) ? (float) $limites['armazenamento_gb'] : null;
        $utilizado = (float) ($total['total'] ?? 0) / 1_000_000_000;

        return [
            'plano' => $versaoPlano['nome'] ?? null,
            'cota' => $cota,
            'utilizado' => $utilizado,
            'disponivel' => $cota === null ? null : max(0, $cota - $utilizado),
            'excedente' => $cota === null ? 0 : max(0, $utilizado - $cota),
        ];
    }

    /** Pagina o inventário por categoria, usando somente colunas autorizadas para a consulta administrativa. */
    public function inventario(string $categoria, int $pagina, ?string $setor = null, ?string $vinculo = null): array
    {
        $categorias = ['equipe' => 'Equipe'] + array_map(static fn (array $definicao) => $definicao[0], self::INVENTARIO);
        if (!isset($categorias[$categoria])) $categoria = 'processos';

        if ($categoria === 'equipe') {
            $colunas = ['nome' => 'Nome', 'email' => 'E-mail', 'tipo_vinculo' => 'Tipo de vínculo', 'status' => 'Situação', 'fim_em' => 'Fim do vínculo'];
            $consulta = $this->banco->table('vinculos_organizacao vinculo')
                ->select('usuario.nome, usuario.email, vinculo.tipo_vinculo, vinculo.status, vinculo.fim_em')
                ->join('usuarios usuario', 'usuario.id = vinculo.usuario_id')
                ->where('vinculo.organizacao_id', $this->empresa)->orderBy('vinculo.id', 'DESC');
        } else {
            $colunas = self::INVENTARIO[$categoria][1];
            $consulta = $this->banco->table($categoria)->select(array_keys($colunas))
                ->where('organizacao_id', $this->empresa)->orderBy('id', 'DESC');
        }

        // O inventário do Admin continua global. A área de trabalho usa o setor ativo.
        if ($setor !== null) {
            if ($categoria === 'processos') {
                $consulta->where(['processos.arquivado_em' => null, 'processos.excluido_em' => null]);
                $consulta->where(ContextoSetor::condicaoProcessos($this->banco, $setor, 'processos'), null, false);
                if ($vinculo !== null) $consulta->where(\App\Libraries\Organizacao\PoliticaProcesso::condicao($this->banco, 'processos', $vinculo), null, false);
            } elseif ($categoria === 'documentos' || $categoria === 'pastas') {
                $consulta->where($categoria === 'pastas' ? 'setor_id' : 'setor_origem_id', $setor ?: '0');
                if ($categoria === 'documentos') $consulta->where($this->condicaoDocumento('documentos', $vinculo), null, false);
            } elseif ($categoria === 'assinaturas') {
                $versoes = $this->banco->table('documento_versoes v')->select('v.id')
                    ->join('documentos d', 'd.organizacao_id = v.organizacao_id AND d.id = v.documento_id')
                    ->where(['v.organizacao_id' => $this->empresa, 'd.setor_origem_id' => $setor ?: '0']);
                $versoes->where($this->condicaoDocumento('d', $vinculo), null, false);
                $consulta->whereIn('documento_versao_id', $versoes);
            }
        }

        $total = $consulta->countAllResults(false);
        $paginas = max(1, (int) ceil($total / 25));
        $pagina = max(1, min($pagina, $paginas));
        return [
            'categorias' => $categorias, 'categoria' => $categoria, 'colunas' => $colunas,
            'registros' => $consulta->get(25, ($pagina - 1) * 25)->getResultArray(),
            'pagina' => $pagina, 'paginas' => $paginas, 'total' => $total,
        ];
    }

    /** Protege documentos com acesso limitado e oculta os vinculados à lixeira na área de trabalho. */
    private function condicaoDocumento(string $alias, ?string $vinculo): string
    {
        $documento = $vinculo === null ? '' : \App\Libraries\Organizacao\PoliticaProcesso::condicao($this->banco, $alias, $vinculo, true) . ' AND ';
        $processo = $vinculo === null ? '' : ' AND ' . \App\Libraries\Organizacao\PoliticaProcesso::condicao($this->banco, 'principal', $vinculo);
        return "({$documento}({$alias}.processo_principal_id IS NULL OR EXISTS (
            SELECT 1 FROM processos principal WHERE principal.organizacao_id = {$alias}.organizacao_id
                AND principal.id = {$alias}.processo_principal_id AND principal.excluido_em IS NULL{$processo})))";
    }

    /** Obtém apenas os setores e as atribuições do vínculo selecionado na empresa. */
    public function contextoUsuario(string $vinculo): array
    {
        $agora = date('Y-m-d H:i:s');
        $setores = $this->banco->table('vinculos_setor v')
            ->select('s.nome AS setor, u.nome AS unidade, v.funcao')
            ->join('setores s', 's.organizacao_id = v.organizacao_id AND s.id = v.setor_id')
            ->join('unidades u', 'u.organizacao_id = s.organizacao_id AND u.id = s.unidade_id')
            ->where(['v.organizacao_id' => $this->empresa, 'v.vinculo_id' => $vinculo])
            ->where('v.inicio_em <=', $agora)
            ->groupStart()->where('v.fim_em', null)->orWhere('v.fim_em >', $agora)->groupEnd()
            ->orderBy('u.nome')->orderBy('s.nome')->get()->getResultArray();

        $perfis = $this->banco->table('atribuicoes_perfil a')
            ->select('a.id, p.nome AS perfil, p.status, u.nome AS unidade, s.nome AS setor')
            ->join('perfis p', 'p.organizacao_id = a.organizacao_id AND p.id = a.perfil_id')
            ->join('unidades u', 'u.organizacao_id = a.organizacao_id AND u.id = a.unidade_id', 'left')
            ->join('setores s', 's.organizacao_id = a.organizacao_id AND s.id = a.setor_id', 'left')
            ->where(['a.organizacao_id' => $this->empresa, 'a.vinculo_id' => $vinculo])
            ->orderBy('p.nome')->get()->getResultArray();

        $permissoes = $this->banco->table('atribuicoes_perfil a')
            ->select('a.id AS atribuicao_id, permissao.modulo, permissao.descricao')
            ->join('perfil_permissoes pp', 'pp.organizacao_id = a.organizacao_id AND pp.perfil_id = a.perfil_id')
            ->join('permissoes permissao', 'permissao.id = pp.permissao_id')
            ->where(['a.organizacao_id' => $this->empresa, 'a.vinculo_id' => $vinculo])
            ->orderBy('permissao.modulo')->orderBy('permissao.descricao')->get()->getResultArray();
        $porPerfil = [];
        foreach ($permissoes as $permissao) $porPerfil[$permissao['atribuicao_id']][] = $permissao;
        return ['setores' => $setores, 'perfis' => $perfis, 'permissoes' => $porPerfil];
    }
}
