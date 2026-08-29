<?php

/**
 * @package     ContentBuilderNG
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die('Restricted access');

final class StatsHideOptionsService
{
    public const INVALID_ITEM = 1;
    public const INVALID_SEPARATOR = 2;
    public const NOT_APPLICABLE = 3;
    public const ALL_HIDDEN = 4;
    public const LEGACY_TOTAL = 5;

    private const ALLOWED = ['title', 'total', 'values', 'graph'];
    private const GRAPH_OUTPUTS = ['pie', 'bar', 'histogram', 'line', 'radar'];

    /**
     * @param array<string,string> $attributes
     * @return array{title: bool, total: bool, values: bool, graph: bool}
     */
    public static function fromAttributes(array $attributes): array
    {
        if (array_key_exists('total', $attributes)) {
            throw new \InvalidArgumentException('total', self::LEGACY_TOTAL);
        }

        return self::parse(array_key_exists('hide', $attributes) ? (string) $attributes['hide'] : null);
    }

    /**
     * @return array{title: bool, total: bool, values: bool, graph: bool}
     */
    public static function parse(?string $value): array
    {
        $options = ['title' => false, 'total' => false, 'values' => false, 'graph' => false];

        if ($value === null) {
            return $options;
        }

        // Joomla normally decodes URL and editor entities before this service,
        // but normalize them here as well so article and URL callers always
        // deliver the complete pipe-separated value to the same parser.
        $value = html_entity_decode(rawurldecode($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = trim($value);

        if (strtolower($value) === 'none') {
            return $options;
        }

        if ($value === '') {
            throw new \InvalidArgumentException('', self::INVALID_ITEM);
        }

        if (str_contains($value, ',') || str_contains($value, ';')) {
            throw new \InvalidArgumentException($value, self::INVALID_SEPARATOR);
        }

        foreach (explode('|', $value) as $item) {
            $item = strtolower(trim($item));

            if ($item === '' || !in_array($item, self::ALLOWED, true)) {
                throw new \InvalidArgumentException($item, self::INVALID_ITEM);
            }

            $options[$item] = true;
        }

        return $options;
    }

    /**
     * @param array{title: bool, total: bool, values: bool, graph: bool} $options
     */
    public static function validateForOutput(array $options, string $output): void
    {
        if ($output === 'json' && $options['title']) {
            throw new \InvalidArgumentException('title|json', self::NOT_APPLICABLE);
        }

        if (in_array($output, self::GRAPH_OUTPUTS, true)) {
            if ($options['total'] && $options['values'] && $options['graph']) {
                throw new \InvalidArgumentException('', self::ALL_HIDDEN);
            }

            return;
        }

        if ($output === 'table') {
            foreach (['values', 'graph'] as $item) {
                if ($options[$item]) {
                    throw new \InvalidArgumentException($item . '|' . $output, self::NOT_APPLICABLE);
                }
            }

            return;
        }

        if ($output === 'total' && $options['total']) {
            throw new \InvalidArgumentException('', self::ALL_HIDDEN);
        }

        foreach (['total', 'values', 'graph'] as $item) {
            if ($options[$item]) {
                throw new \InvalidArgumentException($item . '|' . $output, self::NOT_APPLICABLE);
            }
        }
    }

    /**
     * @param array{title: bool, total: bool, values: bool, graph: bool} $options
     */
    public static function serialize(array $options): string
    {
        return implode('|', array_values(array_filter(
            self::ALLOWED,
            static fn(string $item): bool => $options[$item]
        )));
    }
}
