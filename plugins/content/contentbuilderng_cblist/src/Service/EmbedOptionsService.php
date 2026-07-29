<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngList\Service;

\defined('_JEXEC') or die;

final class EmbedOptionsService
{
    private const DEFAULT_HEIGHT = 640;
    private const MIN_HEIGHT = 240;
    private const MAX_HEIGHT = 5000;

    /**
     * @param array<string, string> $attributes
     *
     * @return array{
     *     id: int,
     *     itemid: int,
     *     height: int,
     *     layout: string,
     *     loading: 'eager'|'lazy',
     *     fields: list<string>,
     *     title: string
     * }
     */
    public static function resolve(array $attributes): array
    {
        $id = self::positiveInteger($attributes['id'] ?? '');

        if ($id === null) {
            throw new \InvalidArgumentException('id');
        }

        $itemId = 0;
        if (isset($attributes['itemid']) && trim($attributes['itemid']) !== '') {
            $itemId = self::positiveInteger($attributes['itemid']) ?? 0;
            if ($itemId < 1) {
                throw new \InvalidArgumentException('itemid');
            }
        }

        $height = self::DEFAULT_HEIGHT;
        if (isset($attributes['height']) && trim($attributes['height']) !== '') {
            $height = self::positiveInteger($attributes['height']) ?? 0;
            if ($height < self::MIN_HEIGHT || $height > self::MAX_HEIGHT) {
                throw new \InvalidArgumentException('height');
            }
        }

        $layout = trim((string) ($attributes['layout'] ?? ''));
        if ($layout !== '' && preg_match('/^[A-Za-z0-9_-]+$/D', $layout) !== 1) {
            throw new \InvalidArgumentException('layout');
        }

        $loading = strtolower(trim((string) ($attributes['loading'] ?? 'lazy')));
        if (!in_array($loading, ['eager', 'lazy'], true)) {
            throw new \InvalidArgumentException('loading');
        }

        $fields = self::fieldSelectors((string) ($attributes['fields'] ?? ''));

        return [
            'id' => $id,
            'itemid' => $itemId,
            'height' => $height,
            'layout' => $layout,
            'loading' => $loading,
            'fields' => $fields,
            'title' => trim((string) ($attributes['title'] ?? '')),
        ];
    }

    /**
     * @return list<string>
     */
    private static function fieldSelectors(string $value): array
    {
        $selectors = preg_split('/\s*,\s*/u', trim($value)) ?: [];
        $selectors = array_values(
            array_filter(
                array_map('trim', $selectors),
                static fn(string $selector): bool => $selector !== ''
            )
        );
        $selectors = array_values(array_unique($selectors));

        if (count($selectors) > 100) {
            throw new \InvalidArgumentException('fields');
        }

        foreach ($selectors as $selector) {
            if (mb_strlen($selector, 'UTF-8') > 255 || preg_match('/[\x00-\x1F\x7F]/u', $selector) === 1) {
                throw new \InvalidArgumentException('fields');
            }
        }

        return $selectors;
    }

    private static function positiveInteger(string $value): ?int
    {
        $value = trim($value);

        if (preg_match('/^[1-9][0-9]*$/D', $value) !== 1) {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($integer) && $integer > 0 ? $integer : null;
    }
}
