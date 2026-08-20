<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngList\Service;

use CB\Component\Contentbuilderng\Site\Service\EmbeddedListActionFilterService;
use CB\Component\Contentbuilderng\Site\Service\ContentCardService;
use CB\Component\Contentbuilderng\Site\Service\EmbeddedListFieldFilterService;

\defined('_JEXEC') or die;

final class EmbedOptionsService
{
    private const DEFAULT_HEIGHT = 240;
    private const MIN_HEIGHT = 240;
    private const MAX_HEIGHT = 5000;
    private const LAYOUTS = ['default', 'cards', 'listone', 'listtwo', 'listthree', 'listcard', 'listcompact', 'listtiles'];
    private const MAX_LIMIT = 5000;
    private const ALLOWED_KEYS = [
        'id',
        'height',
        'pagination',
        'limit',
        'layout',
        'loading',
        'fields',
        'actions',
        'title',
        'sort',
        'dir',
        'output',
        'offset',
        'card',
        'w',
    ];

    /**
     * @param array<string, string> $attributes
     *
     * @return list<array{code: string, parameter: string, value: string, detail: string}>
     */
    public static function validationErrors(array $attributes, array $quoted = []): array
    {
        $errors = [];

        foreach (array_values(array_diff(array_keys($attributes), self::ALLOWED_KEYS)) as $key) {
            $errors[] = self::error('unknown_option', $key, (string) $attributes[$key]);
        }

        foreach (['id', 'height', 'pagination', 'limit', 'offset', 'w'] as $numericKey) {
            if (($quoted[$numericKey] ?? false) === true) {
                $errors[] = self::error(
                    'invalid_value',
                    $numericKey,
                    (string) ($attributes[$numericKey] ?? ''),
                    $numericKey . '_syntax'
                );
            }
        }

        if (self::positiveInteger($attributes['id'] ?? '') === null) {
            $errors[] = self::error('invalid_value', 'id', (string) ($attributes['id'] ?? ''), 'id');
        }

        if (isset($attributes['height']) && trim($attributes['height']) !== '') {
            $height = self::positiveInteger($attributes['height']) ?? 0;
            if ($height < self::MIN_HEIGHT || $height > self::MAX_HEIGHT) {
                $errors[] = self::error('invalid_value', 'height', $attributes['height'], 'height');
            }
        }

        if (isset($attributes['pagination']) && trim($attributes['pagination']) !== '') {
            $pagination = self::nonNegativeInteger($attributes['pagination']);
            if ($pagination === null || $pagination > 5000) {
                $errors[] = self::error('invalid_value', 'pagination', $attributes['pagination'], 'pagination');
            }
        }

        if (isset($attributes['limit']) && trim($attributes['limit']) !== '') {
            $limit = self::positiveInteger($attributes['limit']);
            if ($limit === null || $limit > self::MAX_LIMIT) {
                $errors[] = self::error('invalid_value', 'limit', $attributes['limit'], 'limit');
            }
        }

        if (isset($attributes['offset']) && trim($attributes['offset']) !== '') {
            $offset = self::nonNegativeInteger($attributes['offset']);
            if ($offset === null || $offset > self::MAX_LIMIT - 1) {
                $errors[] = self::error('invalid_value', 'offset', $attributes['offset'], 'offset');
            }
        }

        $output = strtolower(trim((string) ($attributes['output'] ?? 'list')));
        if (!in_array($output, ['list', 'value'], true)) {
            $errors[] = self::error('invalid_value', 'output', (string) ($attributes['output'] ?? ''), 'output');
        }

        $card = strtolower(trim((string) ($attributes['card'] ?? '')));
        if ($card !== '' && !ContentCardService::isValid($card)) {
            $errors[] = self::error('invalid_value', 'card', (string) $attributes['card'], 'card');
        }

        $width = trim((string) ($attributes['w'] ?? ''));
        if ($width !== '' && !ContentCardService::isValidWidth($width)) {
            $errors[] = self::error('invalid_value', 'w', $width, 'w');
        } elseif ($width !== '' && !ContentCardService::isValid($card)) {
            $errors[] = self::error('invalid_value', 'w', $width, 'w_requires_card');
        }

        $layout = trim((string) ($attributes['layout'] ?? ''));
        if ($layout !== '' && !in_array($layout, self::LAYOUTS, true)) {
            $errors[] = self::error('invalid_value', 'layout', $layout, 'layout');
        }

        $loading = strtolower(trim((string) ($attributes['loading'] ?? 'lazy')));
        if (!in_array($loading, ['eager', 'lazy'], true)) {
            $errors[] = self::error('invalid_value', 'loading', (string) ($attributes['loading'] ?? ''), 'loading');
        }

        try {
            $fields = self::fieldSelectors((string) ($attributes['fields'] ?? ''));
            if ($output === 'value' && count($fields) !== 1) {
                $errors[] = self::error(
                    'invalid_value',
                    'fields',
                    (string) ($attributes['fields'] ?? ''),
                    'fields_single'
                );
            }
        } catch (\InvalidArgumentException) {
            $errors[] = self::error('invalid_value', 'fields', (string) ($attributes['fields'] ?? ''), 'fields');
        }

        if ($output === 'value') {
            foreach (['pagination', 'actions', 'title', 'layout', 'height', 'loading', 'card', 'w'] as $incompatibleKey) {
                if (array_key_exists($incompatibleKey, $attributes)) {
                    $errors[] = self::error(
                        'invalid_value',
                        $incompatibleKey,
                        (string) $attributes[$incompatibleKey],
                        'output_incompatible'
                    );
                }
            }
        } elseif (array_key_exists('offset', $attributes)) {
            $errors[] = self::error(
                'invalid_value',
                'offset',
                (string) $attributes['offset'],
                'offset_requires_output'
            );
        }

        try {
            $actions = EmbeddedListActionFilterService::parseActions((string) ($attributes['actions'] ?? ''));
            foreach ($actions as $action) {
                if (!EmbeddedListActionFilterService::isKnownAction($action)) {
                    $errors[] = self::error('unknown_action', 'actions', $action, 'actions');
                }
            }
        } catch (\InvalidArgumentException) {
            $errors[] = self::error('invalid_value', 'actions', (string) ($attributes['actions'] ?? ''), 'actions');
        }

        $sort = trim((string) ($attributes['sort'] ?? ''));
        $sortSelectors = [];
        try {
            $sortSelectors = self::fieldSelectors($sort);
            if ($sort !== '' && $sortSelectors === []) {
                $errors[] = self::error('invalid_value', 'sort', $sort, 'sort');
            }
        } catch (\InvalidArgumentException) {
            $errors[] = self::error('invalid_value', 'sort', $sort, 'sort');
        }

        $rawDirection = (string) ($attributes['dir'] ?? ($output === 'value' ? 'desc' : 'asc'));
        $sortDirections = [];
        try {
            $sortDirections = self::fieldSelectors(strtolower(trim($rawDirection)));
            if ($sortDirections === [] || array_diff($sortDirections, ['asc', 'desc']) !== []) {
                $errors[] = self::error('invalid_value', 'dir', $rawDirection, 'dir');
            } elseif ($sort !== '' && count($sortDirections) > 1 && count($sortDirections) !== count($sortSelectors)) {
                $errors[] = self::error('invalid_value', 'dir', $rawDirection, 'dir_count');
            }
        } catch (\InvalidArgumentException) {
            $errors[] = self::error('invalid_value', 'dir', $rawDirection, 'dir');
        }

        return $errors;
    }

    /**
     * @param array<string, string> $attributes
     *
     * @return array{
     *     id: int,
     *     height: int,
     *     pagination: int|null,
     *     limit: int|null,
     *     layout: string,
     *     loading: 'eager'|'lazy',
     *     fields: list<string>,
     *     actions: list<string>,
     *     sort: string,
     *     sort_direction: non-empty-string,
     *     title: string,
     *     title_set: bool,
     *     output: 'list'|'value',
     *     offset: int,
     *     card: string,
     *     w: string
     * }
     */
    public static function resolve(array $attributes, array $quoted = []): array
    {
        $errors = self::validationErrors($attributes, $quoted);
        if ($errors !== []) {
            throw new \InvalidArgumentException('validation:' . json_encode($errors, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
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
            $pagination = self::nonNegativeInteger($attributes['pagination']);
            if ($pagination === null || $pagination > 5000) {
                throw new \InvalidArgumentException('pagination');
            }
        }

        $layout = trim((string) ($attributes['layout'] ?? ''));
        if ($layout === 'cards') {
            $layout = 'listcard';
        }

        $limit = null;
        if (isset($attributes['limit']) && trim($attributes['limit']) !== '') {
            $limit = self::positiveInteger($attributes['limit']);
            if ($limit === null || $limit > self::MAX_LIMIT) {
                throw new \InvalidArgumentException('limit');
            }
        }

        $loading = strtolower(trim((string) ($attributes['loading'] ?? 'lazy')));
        if (!in_array($loading, ['eager', 'lazy'], true)) {
            throw new \InvalidArgumentException('loading');
        }

        $fields = self::fieldSelectors((string) ($attributes['fields'] ?? ''));
        $output = strtolower(trim((string) ($attributes['output'] ?? 'list')));
        $offset = isset($attributes['offset'])
            ? (self::nonNegativeInteger($attributes['offset']) ?? 0)
            : 0;
        $card = ContentCardService::normalize((string) ($attributes['card'] ?? ''));
        $width = ContentCardService::normalizeWidth((string) ($attributes['w'] ?? ''));
        $actions = self::actionSelectors((string) ($attributes['actions'] ?? ''));
        $sort = trim((string) ($attributes['sort'] ?? ($output === 'value' ? 'ID' : '')));
        $sortSelectors = self::fieldSelectors($sort);
        if ($sort !== "" && $sortSelectors === []) {
            throw new \InvalidArgumentException('sort');
        }
        $sortDirection = strtolower(trim((string) ($attributes['dir'] ?? ($output === 'value' ? 'desc' : 'asc'))));
        $sortDirections = self::fieldSelectors($sortDirection);
        if ($sortDirections === [] || array_diff($sortDirections, ['asc', 'desc']) !== []) {
            throw new \InvalidArgumentException('dir');
        }
        if ($sort !== '' && count($sortDirections) === 1 && count($sortSelectors) > 1) {
            $sortDirections = array_fill(0, count($sortSelectors), $sortDirections[0]);
            $sortDirection = implode('|', $sortDirections);
        }
        if ($sort !== '' && count($sortDirections) !== count($sortSelectors)) {
            throw new \InvalidArgumentException('dir');
        }

        $title = trim((string) ($attributes['title'] ?? ''));
        if (strcasecmp($title, 'hide') === 0) {
            $title = '';
        }

        return [
            'id' => $id,
            'height' => $height,
            'pagination' => $pagination,
            'limit' => $limit,
            'layout' => $layout,
            'loading' => $loading,
            'fields' => $fields,
            'actions' => $actions,
            'sort' => $sort,
            'sort_direction' => $sortDirection,
            'title' => $title,
            'title_set' => array_key_exists('title', $attributes),
            'output' => $output,
            'offset' => $offset,
            'card' => $card,
            'w' => $width,
        ];
    }

    /**
     * @return array{code: string, parameter: string, value: string, detail: string}
     */
    private static function error(string $code, string $parameter, string $value, string $detail = ''): array
    {
        return [
            'code' => $code,
            'parameter' => $parameter,
            'value' => $value,
            'detail' => $detail,
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

    private static function nonNegativeInteger(string $value): ?int
    {
        $value = trim($value);

        if (preg_match('/^(?:0|[1-9][0-9]*)$/D', $value) !== 1) {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($integer) && $integer >= 0 ? $integer : null;
    }
}
