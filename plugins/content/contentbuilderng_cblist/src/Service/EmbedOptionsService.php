<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngList\Service;

use CB\Component\Contentbuilderng\Site\Service\EmbeddedListActionFilterService;
use CB\Component\Contentbuilderng\Site\Service\EmbeddedListFieldFilterService;

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
     *     height: int,
     *     layout: string,
     *     loading: 'eager'|'lazy',
     *     fields: list<string>,
     *     actions: list<string>,
     *     title: string
     * }
     */
    public static function resolve(array $attributes): array
    {
        $id = self::positiveInteger($attributes['id'] ?? '');

        if ($id === null) {
            throw new \InvalidArgumentException('id');
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
        $actions = self::actionSelectors((string) ($attributes['actions'] ?? ''));

        return [
            'id' => $id,
            'height' => $height,
            'layout' => $layout,
            'loading' => $loading,
            'fields' => $fields,
            'actions' => $actions,
            'title' => trim((string) ($attributes['title'] ?? '')),
        ];
    }

    /**
     * @return list<string>
     */
    private static function actionSelectors(string $value): array
    {
        $actions = EmbeddedListActionFilterService::parseActions($value);

        foreach ($actions as $action) {
            if (!EmbeddedListActionFilterService::isKnownAction($action)) {
                throw new \InvalidArgumentException('actions');
            }
        }

        return $actions;
    }

    /**
     * @return list<string>
     */
    private static function fieldSelectors(string $value): array
    {
        $selectors = EmbeddedListFieldFilterService::parseSelectors($value);

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
