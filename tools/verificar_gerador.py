"""Verifica o gerador traduzido em uma pasta temporária, sem modificar migrations aplicadas."""
import ast
from pathlib import Path
import re
from tempfile import TemporaryDirectory

raiz = Path(__file__).resolve().parents[1]
caminho_gerador = raiz / 'tools/gerar_migrations.py'
with TemporaryDirectory(prefix='conferencia-migrations-', dir=raiz / 'writable') as diretorio_temporario:
    arvore = ast.parse(caminho_gerador.read_text(encoding='utf-8'))
    for instrucao in arvore.body:
        if isinstance(instrucao, ast.Assign) and any(isinstance(alvo, ast.Name) and alvo.id == 'DIRETORIO_SAIDA' for alvo in instrucao.targets):
            instrucao.value = ast.parse('Path(' + repr(diretorio_temporario) + ')', mode='eval').body
    ast.fix_missing_locations(arvore)
    exec(compile(arvore, str(caminho_gerador), 'exec'), {'__file__': str(caminho_gerador)})
    arquivos_gerados = sorted(Path(diretorio_temporario).glob('*.php'))
    arquivos_originais = sorted((raiz / 'app/Database/Migrations').glob('*.php'))
    assert [arquivo.name for arquivo in arquivos_gerados] == [arquivo.name for arquivo in arquivos_originais]
    for gerado, original in zip(arquivos_gerados, arquivos_originais):
        conteudo_gerado = re.sub(r'/\*\*.*?\*/', '', gerado.read_text(encoding='utf-8'), flags=re.S)
        conteudo_original = re.sub(r'/\*\*.*?\*/', '', original.read_text(encoding='utf-8'), flags=re.S)
        assert re.sub(r'\s+', ' ', conteudo_gerado).strip() == re.sub(r'\s+', ' ', conteudo_original).strip(), original.name
    print(f'{len(arquivos_gerados)} migrations equivalentes, sem alteração de esquema ou identificadores.')
