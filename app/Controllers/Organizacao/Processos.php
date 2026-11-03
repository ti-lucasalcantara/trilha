<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/** Exibe a consulta visual de processos com exemplos inteiramente locais. */
class Processos extends BaseController
{
    /** Prepara a organização demonstrativa e as opções de navegação e filtragem. */
    public function exibir(string $identificadorOrganizacao): ResponseInterface
    {
        if ($identificadorOrganizacao !== '1') {
            throw PageNotFoundException::forPageNotFound();
        }
        helper('url');
        $processos = $this->obterProcessosDemonstrativos();
        $opcoes = [];
        foreach (['tipo', 'situacao', 'unidade', 'setor', 'responsavel', 'acesso', 'etiqueta'] as $campo) {
            $opcoes[$campo] = array_values(array_unique(array_column($processos, $campo)));
            sort($opcoes[$campo]);
        }
        $unidades = [];
        $pastas = [];
        foreach ($processos as $processo) {
            $unidades[$processo['unidade']][$processo['setor']] = true;
            $pastas[$processo['grupoPasta']][$processo['pasta']] = true;
        }

        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processos', [
            'tituloPagina' => 'Processos', 'paginaAtiva' => 'processos',
            'identificadorOrganizacao' => $identificadorOrganizacao,
            'nomeOrganizacao' => 'Empresa Horizonte', 'nomeUsuario' => 'Lucas',
            'processos' => $processos, 'opcoes' => $opcoes, 'unidades' => $unidades, 'pastas' => $pastas,
        ]));
    }

    /** Abre um exemplo pelo identificador provisório, que futuramente será um hash. */
    public function visualizar(string $identificadorOrganizacao, string $identificadorProcesso): ResponseInterface
    {
        if ($identificadorOrganizacao !== '1') {
            throw PageNotFoundException::forPageNotFound();
        }
        $processoSelecionado = null;
        foreach ($this->obterProcessosDemonstrativos() as $processo) {
            if ((string) $processo['id'] === $identificadorProcesso) {
                $processoSelecionado = $processo;
                break;
            }
        }
        if ($processoSelecionado === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        helper('url');
        $pdfDisponivel = is_file(FCPATH . 'pdf/teste.pdf');

        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processo', [
            'tituloPagina' => $processoSelecionado['numero'], 'paginaAtiva' => 'processos',
            'identificadorOrganizacao' => $identificadorOrganizacao,
            'nomeOrganizacao' => 'Empresa Horizonte', 'nomeUsuario' => 'Lucas',
            'processo' => $processoSelecionado,
            'documentos' => $this->prepararFormatosDocumentos($this->obterDocumentosDemonstrativos($processoSelecionado)),
            'pdfDisponivel' => $pdfDisponivel,
            'enderecoPdf' => base_url('pdf/teste.pdf'),
        ]));
    }

    /** Identifica os formatos originais ilustrativos, mantendo o mesmo PDF na prévia. */
    private function prepararFormatosDocumentos(array $documentos): array
    {
        $formatos = ['pdf', 'xlsx', 'jpg', 'pdf', 'docx'];
        foreach ($documentos as $indice => &$documento) {
            $documento['formato'] = $formatos[$indice];
            $documento['nomeArquivo'] = $documento['titulo'] . '.' . $documento['formato'];
        }
        unset($documento);
        return $documentos;
    }

    /** Fornece documentos fictícios que compartilham o PDF disponibilizado pelo usuário. */
    private function obterDocumentosDemonstrativos(array $processo): array
    {
        return [
            ['id' => '1', 'numero' => 'DOC-0001/2026', 'titulo' => 'Documento de abertura', 'tipo' => 'Solicitação', 'autor' => $processo['responsavel'], 'setor' => $processo['setor'], 'data' => '02/11/2026 · 09:15', 'situacao' => 'Assinado', 'cor' => 'verde', 'acesso' => 'Interno', 'versao' => '1', 'descricao' => 'Apresentação do assunto e da necessidade que originou o processo.'],
            ['id' => '2', 'numero' => 'DOC-0002/2026', 'titulo' => 'Documentação complementar', 'tipo' => 'Documento externo', 'autor' => $processo['responsavel'], 'setor' => $processo['setor'], 'data' => '02/11/2026 · 10:20', 'situacao' => 'Anexado', 'cor' => 'azul', 'acesso' => 'Interno', 'versao' => '1', 'descricao' => 'Informações complementares enviadas para instruir a análise.'],
            ['id' => '3', 'numero' => 'DOC-0003/2026', 'titulo' => 'Anexo de referência', 'tipo' => 'Anexo', 'autor' => 'Mariana Costa', 'setor' => 'Financeiro', 'data' => '02/11/2026 · 11:05', 'situacao' => 'Anexado', 'cor' => 'azul', 'acesso' => 'Restrito por setor', 'versao' => '1', 'descricao' => 'Material de apoio e referência para os setores envolvidos.'],
            ['id' => '4', 'numero' => 'DOC-0004/2026', 'titulo' => 'Despacho de análise', 'tipo' => 'Despacho', 'autor' => 'Lucas', 'setor' => 'Administrativo', 'data' => '02/11/2026 · 14:30', 'situacao' => 'Aguardando assinatura', 'cor' => 'laranja', 'acesso' => 'Interno', 'versao' => '2', 'descricao' => 'Registro da análise e das providências propostas pelo setor.'],
            ['id' => '5', 'numero' => 'DOC-0005/2026', 'titulo' => 'Parecer do setor', 'tipo' => 'Parecer', 'autor' => 'Beatriz Alves', 'setor' => 'Jurídico', 'data' => '02/11/2026 · 16:10', 'situacao' => 'Em elaboração', 'cor' => 'roxo', 'acesso' => 'Confidencial', 'versao' => '1', 'descricao' => 'Manifestação técnica demonstrativa sobre os documentos do processo.'],
        ];
    }

    /** Monta cenários fictícios suficientes para avaliar filtros e várias páginas. */
    private function obterProcessosDemonstrativos(): array
    {
        $exemplos = [
            ['COMPRAS-0008/2026', 'Aquisição de equipamentos para a equipe', 'Compras', 'Matriz', 'Financeiro', 'Mariana Costa', 'Em análise', 'Interno', 'Horizonte Tecnologia', 'Equipamentos', 'Administrativo', 'Compras'],
            ['ADM-0012/2026', 'Admissão de novo colaborador', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Em andamento', 'Confidencial', 'João Martins', 'Pessoas', 'Pessoas', 'Admissões'],
            ['COMPRAS-0007/2026', 'Renovação de licenças de software', 'Compras', 'Matriz', 'Tecnologia', 'Pedro Lima', 'Em análise', 'Restrito por setor', 'Equipe de Tecnologia', 'Tecnologia', 'Administrativo', 'Compras'],
            ['CONTRATOS-0003/2026', 'Revisão do contrato de manutenção', 'Contratos', 'Matriz', 'Jurídico', 'Beatriz Alves', 'Em andamento', 'Restrito por setor', 'Oficina Central', 'Manutenção', 'Administrativo', 'Contratos'],
            ['COMPRAS-0009/2026', 'Materiais para a nova unidade', 'Compras', 'Filial Centro', 'Operações', 'Rafael Santos', 'Aberto', 'Interno', 'Equipe de Operações', 'Nova unidade', 'Administrativo', 'Compras'],
            ['ADM-0011/2026', 'Atualização de documentos admissionais', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Em análise', 'Confidencial', 'Carla Oliveira', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0004/2026', 'Prestação de serviços de limpeza', 'Contratos', 'Filial Centro', 'Operações', 'Rafael Santos', 'Concluído', 'Interno', 'Limpeza Exemplo', 'Serviços', 'Administrativo', 'Contratos'],
            ['INTERNO-0001/2026', 'Organização do arquivo administrativo', 'Administrativo', 'Matriz', 'Administrativo', 'Lucas', 'Aberto', 'Interno', 'Equipe Administrativa', 'Organização', 'Administrativo', 'Rotinas internas'],
            ['COMPRAS-0010/2026', 'Compra de cadeiras ergonômicas', 'Compras', 'Matriz', 'Administrativo', 'Lucas', 'Em andamento', 'Interno', 'Equipe Administrativa', 'Equipamentos', 'Administrativo', 'Compras'],
            ['ADM-0013/2026', 'Admissão de assistente financeiro', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Aberto', 'Confidencial', 'Paula Ribeiro', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0005/2026', 'Aditivo do serviço de internet', 'Contratos', 'Matriz', 'Tecnologia', 'Pedro Lima', 'Em análise', 'Restrito por setor', 'Provedor Exemplo', 'Tecnologia', 'Administrativo', 'Contratos'],
            ['INTERNO-0002/2026', 'Atualização do cadastro de fornecedores', 'Administrativo', 'Matriz', 'Financeiro', 'Mariana Costa', 'Concluído', 'Interno', 'Equipe Financeira', 'Cadastro', 'Administrativo', 'Rotinas internas'],
            ['COMPRAS-0011/2026', 'Reposição de materiais de escritório', 'Compras', 'Filial Centro', 'Operações', 'Rafael Santos', 'Concluído', 'Interno', 'Papelaria Exemplo', 'Materiais', 'Administrativo', 'Compras'],
            ['ADM-0014/2026', 'Admissão de analista de suporte', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Em andamento', 'Confidencial', 'André Ferreira', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0006/2026', 'Revisão do contrato de locação', 'Contratos', 'Matriz', 'Jurídico', 'Beatriz Alves', 'Suspenso', 'Restrito por setor', 'Imobiliária Exemplo', 'Locação', 'Administrativo', 'Contratos'],
            ['INTERNO-0003/2026', 'Planejamento da mudança de setor', 'Administrativo', 'Filial Centro', 'Operações', 'Rafael Santos', 'Em análise', 'Interno', 'Equipe de Operações', 'Nova unidade', 'Administrativo', 'Rotinas internas'],
            ['COMPRAS-0012/2026', 'Aquisição de ferramentas de manutenção', 'Compras', 'Filial Centro', 'Operações', 'Rafael Santos', 'Aberto', 'Interno', 'Manutenção Exemplo', 'Manutenção', 'Administrativo', 'Compras'],
            ['ADM-0015/2026', 'Integração documental de colaborador', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Concluído', 'Confidencial', 'Fernanda Alves', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0007/2026', 'Serviço de armazenamento documental', 'Contratos', 'Matriz', 'Jurídico', 'Beatriz Alves', 'Aberto', 'Restrito por setor', 'Arquivo Exemplo', 'Serviços', 'Administrativo', 'Contratos'],
            ['INTERNO-0004/2026', 'Revisão das orientações administrativas', 'Administrativo', 'Matriz', 'Administrativo', 'Lucas', 'Em andamento', 'Interno', 'Equipe Administrativa', 'Organização', 'Administrativo', 'Rotinas internas'],
            ['COMPRAS-0013/2026', 'Contratação de equipamento de impressão', 'Compras', 'Matriz', 'Financeiro', 'Mariana Costa', 'Suspenso', 'Interno', 'Impressão Exemplo', 'Equipamentos', 'Administrativo', 'Compras'],
            ['ADM-0016/2026', 'Admissão de auxiliar de operações', 'Admissão', 'Filial Centro', 'Operações', 'Rafael Santos', 'Aberto', 'Confidencial', 'Diego Santos', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0008/2026', 'Encerramento do contrato de consultoria', 'Contratos', 'Matriz', 'Jurídico', 'Beatriz Alves', 'Concluído', 'Restrito por setor', 'Consultoria Exemplo', 'Serviços', 'Administrativo', 'Contratos'],
            ['INTERNO-0005/2026', 'Conferência dos inventários das unidades', 'Administrativo', 'Matriz', 'Administrativo', 'Lucas', 'Em análise', 'Interno', 'Equipes das Unidades', 'Organização', 'Administrativo', 'Rotinas internas'],
        ];
        $processos = [];
        foreach ($exemplos as $indice => $exemplo) {
            [$numero, $assunto, $tipo, $unidade, $setor, $responsavel, $situacao, $acesso, $interessado, $etiqueta, $grupoPasta, $pasta] = $exemplo;
            $processos[] = [
                'id' => $indice + 1, 'numero' => $numero, 'assunto' => $assunto, 'tipo' => $tipo,
                'unidade' => $unidade, 'setor' => $setor, 'responsavel' => $responsavel,
                'situacao' => $situacao, 'acesso' => $acesso, 'interessado' => $interessado,
                'etiqueta' => $etiqueta, 'grupoPasta' => $grupoPasta, 'pasta' => $pasta,
                'abertura' => '2026-10-' . str_pad((string) (28 - $indice), 2, '0', STR_PAD_LEFT),
                'atualizacao' => '2026-11-02 ' . str_pad((string) (17 - intdiv($indice, 2)), 2, '0', STR_PAD_LEFT) . ':' . ($indice % 2 === 0 ? '40' : '15'),
                'prazo' => $situacao === 'Concluído' ? '' : '2026-11-' . str_pad((string) (3 + $indice), 2, '0', STR_PAD_LEFT),
                'destinos' => $indice % 4 === 0 ? ['Administrativo', 'Financeiro', 'Jurídico'] : [$setor],
                'acompanhado' => in_array($indice, [0, 3, 8, 14, 20], true),
            ];
        }
        return $processos;
    }
}
