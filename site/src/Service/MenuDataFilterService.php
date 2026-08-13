<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Service\RuntimeUtilityService;

final class MenuDataFilterService
{
    public const INPUT_NAME = 'cb_menu_data_filters';

    /**
     * @param array<int|string, mixed> $filters
     * @param list<string>|null $allowedReferences Null means that every numeric reference is allowed.
     */
    public static function encode(array $filters, ?array $allowedReferences = null): string
    {
        $allowed = $allowedReferences === null ? null : array_fill_keys($allowedReferences, true);
        $normalized = [];

        foreach ($filters as $reference => $rawValue) {
            $reference = trim((string) $reference);

            if (!ctype_digit($reference) || ($allowed !== null && !isset($allowed[$reference]))) {
                continue;
            }

            $terms = array_values(array_filter(array_map(
                static fn(string $term): string => trim(str_replace(["\r", "\n", "\t"], '', $term)),
                explode('|', (string) $rawValue)
            ), static fn(string $term): bool => $term !== ''));

            if ($terms !== []) {
                $normalized[$reference] = $terms;
            }
        }

        return $normalized === []
            ? ''
            : (json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }

    /** @return array<string, list<string>> */
    public static function decode(mixed $raw, RuntimeUtilityService $runtimeUtility): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return [];
        }

        $filters = [];

        foreach ($decoded as $reference => $rawTerms) {
            $reference = trim((string) $reference);

            if (!ctype_digit($reference) || !is_array($rawTerms)) {
                continue;
            }

            $terms = [];
            foreach ($rawTerms as $rawTerm) {
                if (!is_string($rawTerm)) {
                    continue;
                }

                $term = $runtimeUtility->sanitizeDataFilterTerm($rawTerm);

                if ($term !== '') {
                    $terms[] = $term;
                }
            }

            if ($terms !== []) {
                $filters[$reference] = array_values(array_unique($terms));
            }
        }

        return $filters;
    }
}
