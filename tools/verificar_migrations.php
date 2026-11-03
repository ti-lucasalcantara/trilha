<?php

// Executa somente na instância descartável do MariaDB local, na porta 33319.
use CodeIgniter\Boot;
use CodeIgniter\Database\MigrationRunner;
use Config\Database;
use Config\Paths;

define('FCPATH', dirname(__DIR__) . '/public/');
define('ENVIRONMENT', 'development');
require FCPATH . '../app/Config/Paths.php';
$caminhos = new Paths();
require $caminhos->systemDirectory . '/Boot.php';
Boot::bootConsole($caminhos);

$conexao = [
    'hostname' => '127.0.0.1', 'port' => 33319,
    'username' => 'root', 'password' => 'trilha_migration_test',
    'database' => '', 'DBDriver' => 'MySQLi', 'DBDebug' => true,
    'charset' => 'utf8mb4', 'DBCollat' => 'utf8mb4_general_ci',
];
$administrador = Database::connect($conexao, false);
$nomeBanco = 'trilha_migrations_check';
$administrador->query("CREATE DATABASE IF NOT EXISTS `$nomeBanco` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$conexao['database'] = $nomeBanco;
$banco = Database::connect($conexao, false);
$executor = new MigrationRunner(new \Config\Migrations(), $banco);
$executor->setNamespace('App');

try {
    if (! $executor->latest()) {
        throw new RuntimeException('Falha ao aplicar migrations: ' . json_encode($executor->getErrors()));
    }
    $quantidade = count($banco->listTables());
    echo "APLICAÇÃO: $quantidade tabelas (incluindo o histórico do CI4).\n";
    // Verifica o isolamento entre organizações com uma chave estrangeira composta.
    foreach (['A', 'B'] as $nome) {
        $banco->table('organizacoes')->insert([
            'nome' => $nome, 'pais' => 'BR', 'idioma' => 'pt-BR',
            'fuso_horario' => 'America/Sao_Paulo', 'status' => 'teste',
            'criado_em' => '2026-11-02 00:00:00',
        ]);
        $organizacoes[] = $banco->insertID();
    }
    $banco->table('unidades')->insert([
        'organizacao_id' => $organizacoes[0], 'nome' => 'Unidade A',
        'status' => 'teste', 'criado_em' => '2026-11-02 00:00:00',
    ]);
    $unidade = $banco->insertID();
    $rejeitado = false;
    try {
        $banco->table('setores')->insert([
            'organizacao_id' => $organizacoes[1], 'unidade_id' => $unidade,
            'nome' => 'Organização inválida', 'status' => 'teste',
            'criado_em' => '2026-11-02 00:00:00',
        ]);
    } catch (\CodeIgniter\Database\Exceptions\DatabaseException $excecao) {
        $rejeitado = str_contains($excecao->getMessage(), 'foreign key constraint fails');
    }
    if (! $rejeitado) {
        throw new RuntimeException('A relação entre organizações distintas não foi rejeitada.');
    }
    echo "ISOLAMENTO: relação entre organizações rejeitada.\n";
    $banco->table('unidades')->where('id', $unidade)->delete();
    $banco->table('organizacoes')->whereIn('id', $organizacoes)->delete();
    if (! $executor->regress(0)) {
        throw new RuntimeException('Falha na reversão.');
    }
    $banco->resetDataCache();
    if (count($banco->listTables()) !== 1) {
        throw new RuntimeException('A reversão deixou tabelas da aplicação.');
    }
    echo "REVERSÃO: somente o histórico permanece.\n";
    if (! $executor->latest()) {
        throw new RuntimeException('Falha na reaplicação das migrations.');
    }
    echo "REAPLICAÇÃO: concluída.\n";
    $executor->regress(0);
} finally {
    $banco->close();
    $administrador->query("DROP DATABASE `$nomeBanco`");
    $administrador->close();
}
