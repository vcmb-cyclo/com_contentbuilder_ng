<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

final class EmbeddedListContextService
{
    /**
     * @return array<string, string>
     */
    public static function parameters(string $context, string $fields, string $actions, string $limit = '', string $hidePagination = ''): array
    {
        if (!EmbeddedListFieldFilterService::isEmbeddedRequest($context)) {
            return [];
        }

        $fields = trim($fields);
        $actions = trim($actions);
        $limit = trim($limit);
        $hidePagination = $hidePagination === '1' ? '1' : '';
        if (preg_match('/^[1-9][0-9]*$/D', $limit) !== 1 || (int) $limit > 5000) {
            $limit = '';
        }

        if ($fields === '' && $actions === '' && $limit === '' && $hidePagination === '') {
            return [];
        }

        return array_filter([
            'cblist_embed' => EmbeddedListFieldFilterService::REQUEST_CONTEXT,
            'cblist_fields' => $fields,
            'cblist_actions' => $actions,
            'cblist_limit' => $limit,
            'cblist_hide_pagination' => $hidePagination,
        ], static fn(string $value): bool => $value !== '');
    }

    public static function buildQuery(string $context, string $fields, string $actions, string $limit = '', string $hidePagination = ''): string
    {
        $parameters = self::parameters($context, $fields, $actions, $limit, $hidePagination);
        return $parameters === [] ? '' : '&' . http_build_query($parameters);
    }
}
