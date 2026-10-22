<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Cleans news HTML from the admin editor with an allow-list (docs/plan/03-backend.md §2.5).
 *
 * Allowed: p h2 h3 strong em ul ol li a img figure figcaption blockquote br. Other formatting tags are unwrapped
 * (their text stays; b → strong, i → em), dangerous ones are dropped with their content. Every attribute is
 * removed except a[href] (http(s), mailto, tel or a page of this site) and img[src alt width height]
 * (pictures from this site's uploads/ or assets/ only). Loose text at the top level is put into paragraphs.
 */
final class HtmlSanitizer
{
    private const ALLOWED = ['p', 'h2', 'h3', 'strong', 'em', 'ul', 'ol', 'li', 'a', 'img', 'figure', 'figcaption', 'blockquote', 'br'];
    private const RENAME = ['b' => 'strong', 'i' => 'em', 'h1' => 'h2', 'h4' => 'h3', 'h5' => 'h3', 'h6' => 'h3'];
    /** removed together with everything inside */
    private const DROP = ['script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'svg', 'math', 'template', 'noscript',
        'form', 'input', 'button', 'select', 'textarea', 'option', 'link', 'meta', 'base', 'head', 'title', 'video', 'audio', 'source', 'canvas'];
    private const INLINE = ['strong', 'em', 'a', 'img', 'br'];
    private const BLOCK = ['p', 'h2', 'h3', 'ul', 'ol', 'blockquote', 'figure'];

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        // the XML declaration makes libxml read UTF-8; LIBXML_NONET: never fetch anything
        $doc->loadHTML('<?xml encoding="UTF-8"?><!DOCTYPE html><html><body><div id="root">' . $html . '</div></body></html>', LIBXML_NONET | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        $root = $doc->getElementById('root');
        if ($root === null) {
            return '';
        }
        self::walk($root, $doc);
        self::wrapLoose($root, $doc);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        // drop empty paragraphs the editor leaves behind
        return trim((string) preg_replace('#<p>(\s|&nbsp;|<br>)*</p>#u', '', $out));
    }

    private static function walk(\DOMNode $node, \DOMDocument $doc): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction || $child instanceof \DOMCdataSection) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof \DOMElement) {
                continue; // text
            }
            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }
            if (isset(self::RENAME[$tag])) {
                $child = self::rename($child, self::RENAME[$tag], $doc);
                $tag = self::RENAME[$tag];
            }
            self::walk($child, $doc);
            // unknown wrapper (div, span, font, …), or a paragraph/heading around blocks (editors make <p><ul>…): keep what is inside
            if (!in_array($tag, self::ALLOWED, true) || (in_array($tag, ['p', 'h2', 'h3'], true) && self::hasBlockChild($child))) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            self::cleanAttributes($child, $tag);
            if (($tag === 'img' && !$child->hasAttribute('src')) || ($tag === 'a' && !$child->hasAttribute('href'))) {
                if ($tag === 'a') {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                }
                $node->removeChild($child);
            }
        }
    }

    private static function hasBlockChild(\DOMElement $el): bool
    {
        foreach ($el->childNodes as $c) {
            if ($c instanceof \DOMElement && in_array(strtolower($c->tagName), self::BLOCK, true)) {
                return true;
            }
        }
        return false;
    }

    private static function cleanAttributes(\DOMElement $el, string $tag): void
    {
        $keep = [];
        if ($tag === 'a') {
            $href = trim($el->getAttribute('href'));
            if (self::safeHref($href)) {
                $keep['href'] = $href;
                if (preg_match('#^https?://#i', $href)) {
                    $keep['target'] = '_blank';
                    $keep['rel'] = 'noopener';
                }
            }
        } elseif ($tag === 'img') {
            $src = trim($el->getAttribute('src'));
            if (preg_match('#^(\.\./)?(uploads|assets)/[A-Za-z0-9/_.-]+\.(jpe?g|png|webp|gif)$#', $src) && !str_contains($src, '/../')) {
                $keep['src'] = $src;
                $keep['alt'] = mb_substr($el->getAttribute('alt'), 0, 255);
                foreach (['width', 'height'] as $a) {
                    if (preg_match('/^\d{1,4}$/', $el->getAttribute($a))) {
                        $keep[$a] = $el->getAttribute($a);
                    }
                }
                $keep['loading'] = 'lazy';
            }
        }
        foreach (iterator_to_array($el->attributes) as $attr) {
            $el->removeAttribute($attr->name);
        }
        foreach ($keep as $name => $value) {
            $el->setAttribute($name, $value);
        }
    }

    /** http(s), mailto, tel, an anchor, or a page of this site (contact.html, product-ct-scan.html). */
    private static function safeHref(string $href): bool
    {
        // browsers ignore control characters and whitespace inside "java\tscript:"
        $plain = strtolower((string) preg_replace('/[\x00-\x20]+/', '', html_entity_decode($href, ENT_QUOTES | ENT_HTML5)));
        return preg_match('#^(https?://|mailto:|tel:)#', $plain) === 1
            || preg_match('#^(\.\./)?[a-z0-9][a-z0-9_/-]*\.html(\#[A-Za-z0-9_-]+)?$#', $plain) === 1
            || preg_match('#^\#[A-Za-z0-9_-]+$#', $plain) === 1;
    }

    /** Text and inline elements directly in the root go into a <p>, so the article is made of blocks. */
    private static function wrapLoose(\DOMElement $root, \DOMDocument $doc): void
    {
        $p = null;
        foreach (iterator_to_array($root->childNodes) as $child) {
            $inline = $child instanceof \DOMText || ($child instanceof \DOMElement && in_array(strtolower($child->tagName), self::INLINE, true));
            if (!$inline) {
                $p = null;
                continue;
            }
            if ($child instanceof \DOMText && trim($child->textContent) === '' && $p === null) {
                $root->removeChild($child);
                continue;
            }
            if ($p === null) {
                $p = $doc->createElement('p');
                $root->insertBefore($p, $child);
            }
            $p->appendChild($child);
        }
    }

    private static function rename(\DOMElement $el, string $tag, \DOMDocument $doc): \DOMElement
    {
        $new = $doc->createElement($tag);
        while ($el->firstChild) {
            $new->appendChild($el->firstChild);
        }
        $el->parentNode->replaceChild($new, $el);
        return $new;
    }
}
