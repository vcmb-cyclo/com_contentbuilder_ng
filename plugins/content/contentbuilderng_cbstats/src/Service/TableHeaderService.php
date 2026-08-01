<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngStats\Service;

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

final class TableHeaderService
{
    /**
     * @param array<string, mixed> $field
     * @param array<int|string, string> $mappings
     * @return array{label: string, total: string}
     */
    public static function resolve(
        array $field,
        array $mappings,
        string $defaultValue,
        string $defaultTotal
    ): array {
        $requested = (string) ($field['requested'] ?? '');
        $label = (string) ($field['label'] ?? $requested ?: $defaultValue);
        $labelHeader = $mappings[$label] ?? ($requested !== '' ? ($mappings[$requested] ?? $label) : $label);
        $totalHeader = $mappings['Total'] ?? ($mappings[$defaultTotal] ?? $defaultTotal);

        return ['label' => $labelHeader, 'total' => $totalHeader];
    }

    /**
     * A frozen manual source has no field identifier. Its first non-total
     * mapping key becomes the synthetic field header so the exported mapping
     * remains both unchanged and effective.
     *
     * @param array<int|string, string> $mappings
     */
    public static function resolveManualFieldKey(
        array $mappings,
        string $defaultValue,
        string $defaultTotal
    ): string {
        foreach ($mappings as $key => $_display) {
            $key = (string) $key;

            if ($key !== 'Total' && $key !== $defaultTotal) {
                return $key;
            }
        }

        return $defaultValue;
    }
}
