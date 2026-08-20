<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

final class ContentCardService
{
    public const VARIANTS = [
        'h1', 'h2', 'h3', 'h4', 'h5',
        'h6', 'v1', 'v2', 'v3', 'v4', 'v5', 'v6',
    ];

    public const WIDTHS = ['33', '66', '100'];

    public static function normalize(string $value): string
    {
        $value = strtolower(trim($value));

        return in_array($value, self::VARIANTS, true) ? $value : '';
    }

    public static function isValid(string $value): bool
    {
        return self::normalize($value) !== '';
    }

    public static function normalizeWidth(string $value): string
    {
        $value = trim($value);

        return in_array($value, self::WIDTHS, true) ? $value : '';
    }

    public static function isValidWidth(string $value): bool
    {
        return self::normalizeWidth($value) !== '';
    }

    public static function render(string $content, string $variant, string $title = '', string $width = ''): string
    {
        $variant = self::normalize($variant);
        if ($variant === '') {
            return $content;
        }

        $header = trim($title) !== ''
            ? '<div class="cb-card-header">'
                . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8')
                . '</div>'
            : '';

        $width = self::normalizeWidth($width);
        $widthClass = $width !== '' ? ' cb-card-w' . $width : '';

        return '<div class="cb-card cb-card-' . $variant . $widthClass . '">'
            . $header
            . '<div class="cb-card-body">' . $content . '</div>'
            . '</div>';
    }
}
