<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

final class EmbeddedListFieldFilterService
{
    /**
     * @param array<int|string, int|string> $visibleColumns
     * @param array<int|string, string>     $labels
     * @param array<int|string, string>     $names
     *
     * @return list<int|string>
     */
    public static function filter(
        array $visibleColumns,
        array $labels,
        array $names,
        string $rawSelectors
    ): array {
        $selectors = self::parseSelectors($rawSelectors);

        if ($selectors === []) {
            return array_values($visibleColumns);
        }

        $normalizedSelectors = array_fill_keys(
            array_map(self::normalize(...), $selectors),
            true
        );

        return array_values(
            array_filter(
                $visibleColumns,
                static function (int|string $referenceId) use ($labels, $names, $normalizedSelectors): bool {
                    $candidates = [
                        (string) $referenceId,
                        (string) ($names[$referenceId] ?? ''),
                        (string) ($labels[$referenceId] ?? ''),
                    ];

                    foreach ($candidates as $candidate) {
                        $normalized = self::normalize($candidate);

                        if ($normalized !== '' && isset($normalizedSelectors[$normalized])) {
                            return true;
                        }
                    }

                    return false;
                }
            )
        );
    }

    /**
     * @return list<string>
     */
    public static function parseSelectors(string $rawSelectors): array
    {
        $selectors = preg_split('/\s*,\s*/u', trim($rawSelectors)) ?: [];
        $selectors = array_values(
            array_filter(
                array_map('trim', $selectors),
                static fn(string $selector): bool => $selector !== ''
            )
        );

        return array_values(array_unique($selectors));
    }

    private static function normalize(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }
}
