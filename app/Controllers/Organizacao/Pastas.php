<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ConsultaProcessos;
use App\Libraries\Organizacao\ContextoSetor;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

class Pastas extends BaseController
{
    public function exibir(string $hash): ResponseInterface
    {
        [$consulta, $layout, $vinculo, $autores] = $this->contexto($hash);
        $pastas = array_column($consulta->explorador($vinculo, true)['pastasReais'], null, 'id');
        $caminhos = $consulta->caminhosPastas($pastas);
        $pasta = $this->request->getGet('pasta');
        $pasta = is_string($pasta) && isset($caminhos[$pasta]) ? $pasta : '';
        $busca = $this->request->getGet('q');
        $busca = is_string($busca) ? mb_substr(trim($busca), 0, 200) : '';

        $ancestrais = static function (string $id) use ($pastas): array {
            $trilha = [];
            while ($id !== '') {
                $trilha[] = $id;
                $id = (string) $pastas[$id]['pasta_pai_id'];
            }
            return array_reverse($trilha);
        };
        $filhas = array_filter($pastas, static fn (array $item): bool =>
            (string) $item['pasta_pai_id'] === $pasta
            && ($busca === '' || mb_stripos($item['nome'], $busca) !== false));
        $processos = $pasta === '' ? [] : $consulta->listarNaPasta($pasta, $layout['vinculoPreferencias']);
        if ($busca !== '') {
            $processos = array_filter($processos, static fn (array $processo): bool =>
                mb_stripos($processo['assunto'] ?? '', $busca) !== false
                || mb_stripos($processo['numero'] ?? '', $busca) !== false);
        }
        $selecionada = $this->request->getGet('selecionada');
        $selecionada = is_string($selecionada) ? ($filhas[$selecionada] ?? null) : null;
        $destinos = $trilhas = [];
        foreach ($caminhos as $id => $caminho) {
            $trilhas[$id] = $ancestrais((string) $id);
            if ($selecionada && !in_array((string) $selecionada['id'], $trilhas[$id], true) && (string) $id !== $pasta) {
                $destinos[$id] = $caminho;
            }
        }

        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/pastas', $layout + [
            'pastas' => $pastas, 'trilhas' => $trilhas, 'pastaAtual' => $pasta, 'filhas' => $filhas, 'processos' => $processos,
            'selecionada' => $selecionada, 'destinos' => $destinos, 'busca' => $busca, 'autores' => $autores,
            'podeAlterar' => $selecionada && ($vinculo === null || (string) $selecionada['criada_por'] === $vinculo),
            'podeCriar' => $pasta === '' || $vinculo === null || (string) $pastas[$pasta]['criada_por'] === $vinculo,
            'acaoAberta' => $this->request->getGet('acao'),
        ]));
    }

    public function salvar(string $hash): ResponseInterface
    {
        [$consulta, $layout, $vinculo, $autores] = $this->contexto($hash);
        $dados = [];
        foreach (['acao', 'pasta', 'destino', 'origem', 'nome', 'visibilidade', 'setor_contexto', 'responsavel'] as $campo) {
            $valor = $this->request->getPost($campo);
            $dados[$campo] = is_string($valor) ? trim($valor) : '';
        }
        $base = site_url('organizacao/' . $hash . '/pastas');
        $voltar = $base;

        try {
            $acao = $dados['acao'];
            if ($dados['setor_contexto'] === '' || $dados['setor_contexto'] !== (string) ($layout['contextoTrabalho']['setor']['id'] ?? '')) {
                throw new DomainException('O setor ativo mudou. Reabra a tela de pastas.');
            }
            if (!in_array($acao, ['criar', 'renomear', 'visibilidade', 'mover', 'remover'], true)) {
                throw new DomainException('Ação inválida.');
            }
            $pastas = array_column($consulta->explorador($vinculo)['pastasReais'], null, 'id');
            $caminhos = $consulta->caminhosPastas($pastas);
            $id = $dados['pasta'];
            if (($id !== '' || $acao !== 'criar') && !isset($caminhos[$id])) {
                throw new DomainException('A pasta está indisponível neste setor.');
            }
            $pai = $acao === 'criar' ? $id : (string) $pastas[$id]['pasta_pai_id'];
            $parametros = $pai === '' ? [] : ['pasta' => $pai];
            $voltar = $base . ($parametros ? '?' . http_build_query($parametros) : '');
            $voltarErro = $acao === 'criar' ? $voltar : $base . '?' . http_build_query($parametros + ['selecionada' => $id]);

            if (in_array($acao, ['criar', 'renomear'], true) && ($dados['nome'] === '' || mb_strlen($dados['nome']) > 200)) {
                throw new DomainException('Informe um nome com até 200 caracteres.');
            }
            if (in_array($acao, ['criar', 'visibilidade'], true) && !in_array($dados['visibilidade'], ['setor', 'privada'], true)) {
                throw new DomainException('Selecione uma visibilidade válida.');
            }
            if ($acao === 'mover' && $dados['destino'] !== '' && !isset($caminhos[$dados['destino']])) {
                throw new DomainException('A pasta de destino está indisponível.');
            }

            $autor = $layout['vinculoPreferencias'];
            if ($acao === 'criar' && $autor === null) {
                $autor = $dados['responsavel'];
                if (!isset($autores[$autor])) throw new DomainException('Selecione um usuário interno ativo.');
            }
            if ($acao === 'criar' && $id === '') {
                if (!$consulta->cadastrarPasta($dados['nome'], $autor, $dados['visibilidade'])) {
                    throw new DomainException('Não foi possível criar a pasta.');
                }
            } else {
                $dados['acao'] = $acao === 'criar' ? 'subpasta' : $acao;
                $consulta->alterarPasta($dados, $vinculo, $autor, !$layout['administradorSimulado']);
            }

            $mensagem = match ($acao) {
                'criar' => 'Pasta criada.',
                'renomear' => 'Pasta renomeada.',
                'visibilidade' => 'Visibilidade atualizada.',
                'mover' => 'Pasta movida.',
                'remover' => 'Pasta excluída.',
            };
            return redirect()->to($voltar)->withCookies()->with('sucessoPastas', $mensagem);
        } catch (DomainException $erro) {
            $mensagem = $erro->getMessage();
        } catch (DatabaseException $erro) {
            log_message('error', 'Não foi possível salvar a pasta: {exception}', ['exception' => $erro]);
            $mensagem = 'Não foi possível salvar a alteração. Tente novamente.';
        }
        return redirect()->to($voltarErro ?? $voltar)->withCookies()->with('erroPastas', $mensagem);
    }

    private function contexto(string $hash): array
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $acesso = new AcessoOrganizacao($banco);
        $contexto = $acesso->contexto($hash);
        $empresa = (string) $contexto['empresa'];
        $vinculo = $contexto['vinculo'] === null ? null : (string) $contexto['vinculo'];
        $administrador = (bool) $contexto['fundador'];
        $escopo = $administrador ? null : $vinculo;
        $contextoTrabalho = (new ContextoSetor($banco, $empresa, $escopo))->obter();
        $autores = $vinculo === null ? array_column((new ConsultaEmpresa($banco, $empresa))->usuarios(), null, 'vinculo_id') : [];
        $layout = [
            'tituloPagina' => 'Pastas' . ($contexto['simulacao'] ? ' · Simulação' : ''),
            'paginaAtiva' => 'pastas', 'identificadorOrganizacao' => $hash,
            'nomeOrganizacao' => $contexto['organizacao'], 'nomeUsuario' => $contexto['usuario'],
            'usuarioTemAvatar' => !empty($contexto['tem_avatar']), 'modoSimulacao' => $contexto['simulacao'],
            'simulacaoSemVinculo' => $vinculo === null, 'vinculoPreferencias' => $vinculo,
            'administradorSimulado' => $administrador, 'empresaSimulada' => $empresa,
            'consultasPermitidas' => $administrador ? [] : $acesso->consultas($contexto),
            'contextoTrabalho' => $contextoTrabalho,
        ];
        return [new ConsultaProcessos($banco, $empresa, (string) ($contextoTrabalho['setor']['id'] ?? '')), $layout, $escopo, $autores];
    }
}
