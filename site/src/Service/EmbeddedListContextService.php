<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

final class EmbeddedListContextService
{
    /**
     * @return array<string, string>
     */
    public static function parameters(string $context, string $fields, string $actions): array
    {
        if (!EmbeddedListFieldFilterService::isEmbeddedRequest($context)) {
            return [];
        }

        $fields = trim($fields);
        $actions = trim($actions);

        if ($fields === '' && $actions === '') {
            return [];
        }

        return array_filter([
            'cblist_embed' => EmbeddedListFieldFilterService::REQUEST_CONTEXT,
            'cblist_fields' => $fields,
            'cblist_actions' => $actions,
        ], static fn(string $value): bool => $value !== '');
    }

    public static function buildQuery(string $context, string $fields, string $actions): string
    {
        $parameters = self::parameters($context, $fields, $actions);

        return $parameters === [] ? '' : '&' . http_build_query($parameters);
    }
}
