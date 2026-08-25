<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

final class EditorialCardService
{
    private const MARKER_CLASS = 'cb-card-editorial';
    private const ROOT_ID = 'cb-editorial-card-root';

    public static function containsMarker(string $html): bool
    {
        return stripos($html, self::MARKER_CLASS) !== false;
    }

    public static function transform(string $html): string
    {
        if (!self::containsMarker($html)) {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div id="'
                . self::ROOT_ID . '">' . $html . '</div></body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return $html;
        }

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query(
            '//*[contains(concat(" ", normalize-space(@class), " "), " ' . self::MARKER_CLASS . ' ")]'
        );

        if ($nodes === false) {
            return $html;
        }

        $cards = [];
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement && strtolower($node->tagName) === 'div') {
                $cards[] = $node;
            }
        }

        if ($cards === []) {
            return $html;
        }

        foreach ($cards as $card) {
            self::transformCard($document, $card);
        }

        self::removeEmptyGridTextNodes($xpath);

        $root = $document->getElementById(self::ROOT_ID);
        if (!$root instanceof DOMElement) {
            return $html;
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private static function removeEmptyGridTextNodes(DOMXPath $xpath): void
    {
        $grids = $xpath->query(
            '//*[contains(concat(" ", normalize-space(@class), " "), " cb-cards ")]'
        );

        if ($grids === false) {
            return;
        }

        foreach ($grids as $grid) {
            $children = [];
            foreach ($grid->childNodes as $child) {
                $children[] = $child;
            }

            foreach ($children as $child) {
                if ($child->nodeType !== XML_TEXT_NODE) {
                    continue;
                }

                $value = str_replace("\u{00A0}", '', (string) $child->nodeValue);
                if (trim($value) === '') {
                    $grid->removeChild($child);
                }
            }
        }
    }

    private static function transformCard(DOMDocument $document, DOMElement $card): void
    {
        $variant = ContentCardService::normalize($card->getAttribute('data-card')) ?: 'v1';
        $width = ContentCardService::normalizeWidth($card->getAttribute('data-w')) ?: '33';
        $title = ContentCardService::parseTitle($card->getAttribute('data-title'));
        $classes = preg_split('/\s+/', trim($card->getAttribute('class'))) ?: [];
        $classes = array_values(array_filter(
            $classes,
            static fn(string $class): bool => $class !== '' && $class !== self::MARKER_CLASS
        ));
        $classes[] = 'cb-card';
        $classes[] = 'cb-card-' . $variant;
        $classes[] = 'cb-card-w' . $width;
        $card->setAttribute('class', implode(' ', array_unique($classes)));
        $card->removeAttribute('data-title');
        $card->removeAttribute('data-card');
        $card->removeAttribute('data-w');

        $children = [];
        foreach ($card->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            $card->removeChild($child);
        }

        if (trim($title['text']) !== '') {
            $header = $document->createElement($title['tag']);
            $header->setAttribute('class', 'cb-card-header');
            if ($title['fontSize'] !== '') {
                $header->setAttribute('style', 'font-size:' . $title['fontSize']);
            }
            $header->appendChild($document->createTextNode($title['text']));
            $card->appendChild($header);
        }

        $body = $document->createElement('div');
        $body->setAttribute('class', 'cb-card-body');
        foreach ($children as $child) {
            $body->appendChild($child);
        }
        $card->appendChild($body);
    }
}
