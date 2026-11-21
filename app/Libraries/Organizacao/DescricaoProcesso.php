<?php

namespace App\Libraries\Organizacao;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class DescricaoProcesso
{
    private const ELEMENTOS = ['p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li'];

    /** Reconstrói somente os elementos e a formatação permitidos pelo editor. */
    public static function limpar(string $html, array $elementosExtras = [], bool $preservarAlinhamento = false): string
    {
        if ($html === '' || strlen($html) > 65535) return '';

        $documento = new DOMDocument();
        $documento->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        $corpo = $documento->getElementsByTagName('body')->item(0);
        return $corpo ? self::conteudo($corpo, [...self::ELEMENTOS, ...$elementosExtras], $preservarAlinhamento) : '';
    }

    private static function conteudo(DOMNode $no, array $elementos, bool $preservarAlinhamento): string
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
                $texto = self::conteudo($filho, $elementos, $preservarAlinhamento);
                $atributos = $preservarAlinhamento ? self::alinhamento($filho) : '';
                $html .= in_array($tag, $elementos, true) ? "<{$tag}{$atributos}>{$texto}</{$tag}>" : $texto;
            }
        }
        return $html;
    }

    private static function alinhamento(DOMElement $elemento): string
    {
        if (!in_array($elemento->tagName, ['p', 'div', 'h2', 'h3', 'blockquote', 'li', 'td', 'th'], true)) return '';

        $valores = '(left|center|right|justify)';
        if (preg_match('/(?:^|;)\s*text-align\s*:\s*' . $valores . '\s*(?:;|$)/i', $elemento->getAttribute('style'), $correspondencia)
            || preg_match('/^editor-align-' . $valores . '$/D', $elemento->getAttribute('class'), $correspondencia)) {
            return ' class="editor-align-' . strtolower($correspondencia[1]) . '"';
        }

        return '';
    }
}
