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
     * @param array<int|string, string>     $names
     *
     * @return list<int|string>
     */
    public static function filter(
        array $visibleColumns,
        array $names,
        string $rawSelectors
    ): array {
        $match = self::matchSelectors($visibleColumns, $names, $rawSelectors);

        if ($match['unknown'] !== []) {
            throw new \InvalidArgumentException('unknown_field:' . $match['unknown'][0]);
        }

        return $match['columns'];
    }

    /**
     * Matches only exact source element names or exact reference IDs.
     * Display labels, case changes and accent variants are intentionally
     * rejected so article syntax remains stable when labels are translated.
     *
     * @param array<int|string, int|string> $visibleColumns
     * @param array<int|string, string>     $names
     *
     * @return array{columns: list<int|string>, unknown: list<string>}
     */
    public static function matchSelectors(
        array $visibleColumns,
        array $names,
        string $rawSelectors
    ): array {
        $selectors = self::parseSelectors($rawSelectors);

        if ($selectors === []) {
            return ['columns' => array_values($visibleColumns), 'unknown' => []];
        }

        $filteredColumns = [];
        $selectedReferences = [];
        $unknownSelectors = [];

        foreach ($selectors as $selector) {
            $selectorMatched = false;

            foreach ($visibleColumns as $referenceId) {
                $referenceKey = (string) $referenceId;

                if (isset($selectedReferences[$referenceKey])) {
                    continue;
                }

                $candidates = [
                    $referenceKey,
                    (string) ($names[$referenceId] ?? ''),
                ];

                foreach ($candidates as $candidate) {
                    if ($selector !== '' && $candidate === $selector) {
                        $filteredColumns[] = $referenceId;
                        $selectorMatched = true;
                        $selectedReferences[$referenceKey] = true;
                        break;
                    }
                }
            }

            if (!$selectorMatched) {
                $unknownSelectors[] = $selector;
            }
        }

        return ['columns' => $filteredColumns, 'unknown' => $unknownSelectors];
    }

    /**
     * Splits selectors on `|`. Commas and semicolons are rejected rather than
     * accepted as alternate separators.
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
}
