<?php

namespace App\Libraries\Organizacao;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class DescricaoProcesso
{
    private const ELEMENTOS = ['p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li'];

    /** Reconstrói somente a formatação oferecida no editor, sem copiar atributos. */
    public static function limpar(string $html): string
    {
        if ($html === '' || strlen($html) > 65535) return '';

        $documento = new DOMDocument();
        $documento->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        $corpo = $documento->getElementsByTagName('body')->item(0);
        return $corpo ? self::conteudo($corpo) : '';
    }

    private static function conteudo(DOMNode $no): string
    {
        $html = '';
        foreach ($no->childNodes as $filho) {
            if ($filho instanceof DOMText) {
                $texto = str_replace("\u{00A0}", ' ', $filho->data);
                $html .= htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            } elseif ($filho instanceof DOMElement) {
                $tag = $filho->tagName;
                if (in_array($tag, ['script', 'style', 'template'], true)) continue;
                if ($tag === 'br') {
                    $html .= '<br>';
                    continue;
                }
                $texto = self::conteudo($filho);
                $html .= in_array($tag, self::ELEMENTOS, true) ? "<{$tag}>{$texto}</{$tag}>" : $texto;
            }
        }
        return $html;
    }
}
