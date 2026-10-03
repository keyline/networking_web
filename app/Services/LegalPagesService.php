<?php

namespace App\Services;

use App\Models\Page;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Privacy Policy and Terms & Conditions content. Stored as CMS pages so the
 * website (page/{slug}) and the mobile app (user/policy-terms) share one copy.
 */
class LegalPagesService
{
    /** Form field => [page slug, page title] */
    public const PAGES = [
        'privacy' => ['privacy-policy', 'Privacy Policy'],
        'terms'   => ['terms-conditions', 'Terms & Conditions'],
    ];

    private const ALLOWED_TAGS = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'h2', 'h3', 'h4',
        'ul', 'ol', 'li', 'a', 'blockquote', 'hr', 'div', 'span', 'sup', 'sub',
    ];

    /** @return array<string, Page> keyed like PAGES, created when missing */
    public static function pages(): array
    {
        $pages = [];
        foreach (self::PAGES as $key => [$slug, $title]) {
            $pages[$key] = Page::firstOrCreate(
                ['page_slug' => $slug],
                ['page_name' => $title, 'page_content' => '', 'status' => 1]
            );
        }
        return $pages;
    }

    /** Keep basic formatting only: no scripts, styles, event handlers or javascript: links. */
    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="legal-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        foreach (iterator_to_array($xpath->query('//*')) as $node) {
            /** @var DOMElement $node */
            if ($node->getAttribute('id') === 'legal-root' && $node->parentNode === $doc) {
                continue;
            }
            $tag = strtolower($node->nodeName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form'], true)) {
                $node->parentNode?->removeChild($node);
                continue;
            }
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unwrap unknown tags but keep their text
                while ($node->firstChild) {
                    $node->parentNode->insertBefore($node->firstChild, $node);
                }
                $node->parentNode?->removeChild($node);
                continue;
            }
            foreach (iterator_to_array($node->attributes) as $attr) {
                $name = strtolower($attr->nodeName);
                $keep = $tag === 'a' && in_array($name, ['href', 'target', 'rel'], true);
                if (!$keep) {
                    $node->removeAttribute($attr->nodeName);
                }
            }
            if ($tag === 'a') {
                $href = trim($node->getAttribute('href'));
                if ($href === '' || !preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $href)) {
                    $node->removeAttribute('href');
                }
                if ($node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener');
                } else {
                    $node->removeAttribute('target');
                }
            }
        }

        $root = $doc->getElementById('legal-root') ?? $xpath->query('//div')->item(0);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return trim($out);
    }
}
