<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

class FotoUsuario
{
    public function __construct(private readonly BaseConnection $banco)
    {
    }

    public function salvar(string $usuario, ?UploadedFile $arquivo): void
    {
        if (!$arquivo || !$arquivo->isValid() || $arquivo->getSize() > 2 * 1024 * 1024) {
            throw new DomainException('Envie uma foto de até 2 MB.');
        }
        $dados = file_get_contents($arquivo->getTempName());
        $dimensoes = is_string($dados) ? @getimagesizefromstring($dados) : false;
        if (!$dimensoes || !in_array($dimensoes[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
            || max($dimensoes[0], $dimensoes[1]) > 4096) {
            throw new DomainException('Escolha uma imagem JPG, PNG ou WebP com até 4096 pixels por lado.');
        }
        $imagem = @imagecreatefromstring($dados);
        if (!$imagem) throw new DomainException('Não foi possível ler essa imagem. Escolha outra foto.');

        $lado = min($dimensoes[0], $dimensoes[1]);
        $avatar = imagecreatetruecolor(256, 256);
        imagefill($avatar, 0, 0, imagecolorallocate($avatar, 255, 255, 255));
        imagecopyresampled($avatar, $imagem, 0, 0, intdiv($dimensoes[0] - $lado, 2),
            intdiv($dimensoes[1] - $lado, 2), 256, 256, $lado, $lado);
        ob_start();
        imagejpeg($avatar, null, 88);
        $jpeg = ob_get_clean();
        if (!$jpeg) throw new DomainException('Não foi possível preparar a foto.');

        $this->banco->query("UPDATE usuarios SET avatar_jpeg = UNHEX(?), atualizado_em = ? WHERE id = ? AND status = 'ativo'",
            [bin2hex($jpeg), date('Y-m-d H:i:s'), $usuario]);
    }

    public function responder(string $usuario, ResponseInterface $resposta): ResponseInterface
    {
        $conta = $this->banco->table('usuarios')->select('avatar_jpeg')
            ->where(['id' => $usuario, 'status' => 'ativo'])->get()->getRowArray();
        if (empty($conta['avatar_jpeg'])) throw PageNotFoundException::forPageNotFound();

        return $resposta->setContentType('image/jpeg')->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')->setBody($conta['avatar_jpeg']);
    }
}
