<?php

/** Gera os formatos raster do T geométrico definido em public/favicon.svg. */
declare(strict_types=1);

/** Desenha o T roxo com transparência e suaviza suas bordas na redução. */
function desenharIcone(int $tamanho): GdImage
{
    $escala = $tamanho >= 128 ? 2 : 8;
    $tamanhoAmpliado = $tamanho * $escala;
    $imagemAmpliada = imagecreatetruecolor($tamanhoAmpliado, $tamanhoAmpliado);
    imagealphablending($imagemAmpliada, false);
    imagesavealpha($imagemAmpliada, true);
    $transparente = imagecolorallocatealpha($imagemAmpliada, 0, 0, 0, 127);
    imagefill($imagemAmpliada, 0, 0, $transparente);
    $roxo = imagecolorallocatealpha($imagemAmpliada, 124, 58, 237, 0);
    $proporcao = $tamanhoAmpliado / 64;

    // As duas barras arredondadas formam apenas a letra T, sem outros elementos.
    desenharRetanguloArredondado($imagemAmpliada, 7, 8, 50, 13, 5, $proporcao, $roxo);
    desenharRetanguloArredondado($imagemAmpliada, 25, 15, 14, 43, 5, $proporcao, $roxo);

    $imagemFinal = imagecreatetruecolor($tamanho, $tamanho);
    imagealphablending($imagemFinal, false);
    imagesavealpha($imagemFinal, true);
    imagecopyresampled($imagemFinal, $imagemAmpliada, 0, 0, 0, 0, $tamanho, $tamanho, $tamanhoAmpliado, $tamanhoAmpliado);
    imagedestroy($imagemAmpliada);

    return $imagemFinal;
}

/** Preenche um retângulo com quatro cantos arredondados na escala informada. */
function desenharRetanguloArredondado(GdImage $imagem, int $x, int $y, int $largura, int $altura, int $raio, float $proporcao, int $cor): void
{
    $esquerda = (int) round($x * $proporcao);
    $topo = (int) round($y * $proporcao);
    $direita = (int) round(($x + $largura) * $proporcao) - 1;
    $base = (int) round(($y + $altura) * $proporcao) - 1;
    $raioPixels = (int) round($raio * $proporcao);
    imagefilledrectangle($imagem, $esquerda + $raioPixels, $topo, $direita - $raioPixels, $base, $cor);
    imagefilledrectangle($imagem, $esquerda, $topo + $raioPixels, $direita, $base - $raioPixels, $cor);
    foreach ([$esquerda + $raioPixels, $direita - $raioPixels] as $centroX) {
        foreach ([$topo + $raioPixels, $base - $raioPixels] as $centroY) {
            imagefilledellipse($imagem, $centroX, $centroY, $raioPixels * 2, $raioPixels * 2, $cor);
        }
    }
}

// O ICO contém imagens PNG de vários tamanhos para diferentes navegadores.
$diretorioPublico = dirname(__DIR__) . '/public';
$tamanhos = [16, 32, 48, 64, 128, 256];
$entradas = '';
$conteudoImagens = '';
$deslocamento = 6 + count($tamanhos) * 16;
foreach ($tamanhos as $tamanho) {
    $imagem = desenharIcone($tamanho);
    ob_start();
    imagepng($imagem);
    $conteudoPng = ob_get_clean();
    imagedestroy($imagem);
    $dimensao = $tamanho === 256 ? 0 : $tamanho;
    $entradas .= pack('CCCCvvVV', $dimensao, $dimensao, 0, 0, 1, 32, strlen($conteudoPng), $deslocamento);
    $conteudoImagens .= $conteudoPng;
    $deslocamento += strlen($conteudoPng);
}
file_put_contents($diretorioPublico . '/favicon.ico', pack('vvv', 0, 1, count($tamanhos)) . $entradas . $conteudoImagens);
$imagemGrande = desenharIcone(512);
imagepng($imagemGrande, $diretorioPublico . '/img/favicon-t.png');
imagedestroy($imagemGrande);
echo "Favicon gerado em SVG, ICO e PNG de 512 pixels.\n";
