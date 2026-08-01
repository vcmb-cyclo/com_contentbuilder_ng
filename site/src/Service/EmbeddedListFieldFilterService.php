<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

final class EmbeddedListFieldFilterService
{
    /**
     * Public rendering context marker, not an authorization token.
     */
    public const REQUEST_CONTEXT = 'content-plugin';

    public static function isEmbeddedRequest(string $context): bool
    {
        return $context === self::REQUEST_CONTEXT;
    }

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
     * Splits on `|`, same convention as {CBStats hide="..."}. `,` and `;`
     * are rejected rather than accepted as an alternate separator: a field
     * label can itself legitimately contain a comma, which silently
     * splitting on it would corrupt.
     *
     * @return list<string>
     */
    public static function parseSelectors(string $rawSelectors): array
    {
        $rawSelectors = trim($rawSelectors);

        if ($rawSelectors === '') {
            return [];
        }

        if (str_contains($rawSelectors, ',') || str_contains($rawSelectors, ';')) {
            throw new \InvalidArgumentException('fields');
        }

        $selectors = array_map('trim', explode('|', $rawSelectors));
        $selectors = array_values(array_filter(
            $selectors,
            static fn(string $selector): bool => $selector !== ''
        ));

        return array_values(array_unique($selectors));
    }

    private static function normalize(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }
}
