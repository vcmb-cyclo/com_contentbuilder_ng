<?php

namespace CB\Plugin\Content\ContentbuilderngStats\Service;

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

final class DisplayOptionsService
{
    public const INVALID_LIMIT = 1;

    /** @param array<string, string> $attributes */
    public static function parseLimit(array $attributes): ?int
    {
        if (!array_key_exists('limit', $attributes)) {
            return null;
        }

        $value = trim((string) $attributes['limit']);

        if (!preg_match('/^[1-9][0-9]*$/D', $value)) {
            throw new \InvalidArgumentException('Invalid CBStats limit.', self::INVALID_LIMIT);
        }

        $limit = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($limit === false) {
            throw new \InvalidArgumentException('Invalid CBStats limit.', self::INVALID_LIMIT);
        }

        return $limit;
    }

    /**
     * @param list<array{label: string, value: int|float}> $items
     * @return list<array{label: string, value: int|float}>
     */
    public static function applyLimit(array $items, ?int $limit): array
    {
        return $limit === null ? $items : array_slice($items, 0, $limit);
    }
}
