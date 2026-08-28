<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngStats\Service;

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

final class TableHeaderService
{
    /**
     * @param array<string, mixed> $field
     * @param array<string, string> $labels
     * @return array{category: string, value: string}
     */
    public static function resolve(
        array $field,
        array $labels,
        string $defaultCategory,
        string $defaultValue
    ): array {
        $requested = (string) ($field['requested'] ?? '');
        $fieldLabel = (string) ($field['label'] ?? $requested ?: $defaultCategory);

        return [
            'category' => $labels['category'] ?? $fieldLabel,
            'value' => $labels['value'] ?? $defaultValue,
        ];
    }
}
