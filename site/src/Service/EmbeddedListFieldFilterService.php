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
        $match = self::matchFieldSelectors($visibleColumns, $names, $rawSelectors);

        if ($match['unknown'] !== []) {
            throw new \InvalidArgumentException('unknown_field:' . $match['unknown'][0]);
        }

        return $match['columns'];
    }

    /**
     * Validates fields against every source element while returning only the
     * columns currently published and enabled for list display. An existing
     * element that is unpublished or has list_include disabled is therefore a
     * valid selector, but it cannot re-enable the column in the rendered list.
     *
     * @param array<int|string, int|string> $visibleColumns
     * @param array<int|string, string>     $names
     *
     * @return array{columns: list<int|string>, unknown: list<string>}
     */
    public static function matchFieldSelectors(
        array $visibleColumns,
        array $names,
        string $rawSelectors
    ): array {
        return self::matchSelectorsAgainstKnownElements(
            $visibleColumns,
            $names,
            $rawSelectors,
            true
        );
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
        return self::matchSelectorsAgainstKnownElements(
            $visibleColumns,
            $names,
            $rawSelectors,
            false
        );
    }

    /**
     * @param array<int|string, int|string> $visibleColumns
     * @param array<int|string, string>     $names
     *
     * @return array{columns: list<int|string>, unknown: list<string>}
     */
    private static function matchSelectorsAgainstKnownElements(
        array $visibleColumns,
        array $names,
        string $rawSelectors,
        bool $acceptUnavailableElements
    ): array {
        $selectors = self::parseSelectors($rawSelectors);

        if ($selectors === []) {
            return ['columns' => array_values($visibleColumns), 'unknown' => []];
        }

        $filteredColumns = [];
        $selectedReferences = [];
        $unknownSelectors = [];
        $visibleReferences = array_fill_keys(
            array_map(static fn(int|string $referenceId): string => (string) $referenceId, $visibleColumns),
            true
        );
        $knownReferences = $visibleColumns;

        if ($acceptUnavailableElements) {
            foreach (array_keys($names) as $referenceId) {
                if (!isset($visibleReferences[(string) $referenceId])) {
                    $knownReferences[] = $referenceId;
                }
            }
        }

        foreach ($selectors as $selector) {
            $selectorMatched = false;

            foreach ($knownReferences as $referenceId) {
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
                        $selectorMatched = true;

                        if (isset($visibleReferences[$referenceKey])) {
                            $filteredColumns[] = $referenceId;
                            $selectedReferences[$referenceKey] = true;
                        }

                        break;
                    }
                }

                if ($selectorMatched) {
                    break;
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
