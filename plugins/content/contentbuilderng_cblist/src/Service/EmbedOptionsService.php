<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngList\Service;

use CB\Component\Contentbuilderng\Site\Service\EmbeddedListActionFilterService;
use CB\Component\Contentbuilderng\Site\Service\EmbeddedListFieldFilterService;

\defined('_JEXEC') or die;

final class EmbedOptionsService
{
    private const DEFAULT_HEIGHT = 240;
    private const MIN_HEIGHT = 240;
    private const MAX_HEIGHT = 5000;
    private const LAYOUTS = ['default', 'cards', 'listone', 'listtwo', 'listthree', 'listcard', 'listcompact', 'listtiles'];

    /**
     * @param array<string, string> $attributes
     *
     * @return array{
     *     id: int,
     *     height: int,
     *     pagination: int|null,
     *     layout: string,
     *     loading: 'eager'|'lazy',
     *     fields: list<string>,
     *     actions: list<string>,
     *     sort: string,
     *     sort_direction: 'asc'|'desc',
     *     title: string,
     *     title_set: bool
     * }
     */
    public static function resolve(array $attributes): array
    {
        $allowedKeys = ['id', 'height', 'pagination', 'layout', 'loading', 'fields', 'actions', 'title', 'sort', 'dir'];
        $unknownKeys = array_values(array_diff(array_keys($attributes), $allowedKeys));
        if ($unknownKeys !== []) {
            throw new \InvalidArgumentException('option_unknown:' . $unknownKeys[0]);
        }
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

        $pagination = null;
        if (isset($attributes['pagination']) && trim($attributes['pagination']) !== '') {
            $pagination = self::positiveInteger($attributes['pagination']);
            if ($pagination === null || $pagination > 5000) {
                throw new \InvalidArgumentException('pagination');
            }
        }

        $layout = trim((string) ($attributes['layout'] ?? ''));
        if ($layout !== '' && preg_match('/^[A-Za-z0-9_-]+$/D', $layout) !== 1) {
            throw new \InvalidArgumentException('layout');
        }
        if ($layout !== '' && !in_array($layout, self::LAYOUTS, true)) {
            throw new \InvalidArgumentException('layout_unknown:' . $layout);
        }

        $loading = strtolower(trim((string) ($attributes['loading'] ?? 'lazy')));
        if (!in_array($loading, ['eager', 'lazy'], true)) {
            throw new \InvalidArgumentException('loading');
        }

        $fields = self::fieldSelectors((string) ($attributes['fields'] ?? ''));
        $actions = self::actionSelectors((string) ($attributes['actions'] ?? ''));
        $sort = trim((string) ($attributes['sort'] ?? ''));
        $sortSelectors = self::fieldSelectors($sort);
        if ($sort !== "" && $sortSelectors === []) {
            throw new \InvalidArgumentException('sort');
        }
        $sortDirection = strtolower(trim((string) ($attributes['dir'] ?? 'asc')));
        $sortDirections = self::fieldSelectors($sortDirection);
        if ($sortDirections === [] || array_diff($sortDirections, ['asc', 'desc']) !== [] || ($sort !== "" && count($sortDirections) !== count($sortSelectors))) {
            throw new \InvalidArgumentException('dir');
        }

        return [
            'id' => $id,
            'height' => $height,
            'pagination' => $pagination,
            'layout' => $layout,
            'loading' => $loading,
            'fields' => $fields,
            'actions' => $actions,
            'sort' => $sort,
            'sort_direction' => $sortDirection,
            'title' => trim((string) ($attributes['title'] ?? '')),
            'title_set' => array_key_exists('title', $attributes),
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
                throw new \InvalidArgumentException('action_unknown:' . $action);
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
