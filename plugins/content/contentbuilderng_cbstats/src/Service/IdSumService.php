<?php

namespace CB\Plugin\Content\ContentbuilderngStats\Service;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Site\Service\StatsService;

final class IdSumService
{
    /**
     * @return list<int>
     */
    public static function resolveSourceIds(string $id, string $idSum, int $fallbackId = 0): array
    {
        if ($id !== '' && $idSum !== '') {
            throw new IdSumException('id and idsum cannot be used together.', IdSumException::CONFLICT);
        }

        return $idSum !== '' ? self::parseIds($idSum) : [(int) ($id !== '' ? $id : $fallbackId)];
    }

    /**
     * @return list<int>
     */
    public static function parseIds(string $value): array
    {
        $parts = explode('+', trim($value));

        if (count($parts) < 2) {
            throw new IdSumException('idsum requires at least two view identifiers.', IdSumException::TOO_FEW);
        }

        if (count($parts) > 5) {
            throw new IdSumException('idsum accepts at most five view identifiers.', IdSumException::TOO_MANY);
        }

        $ids = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '' || preg_match('/^[1-9][0-9]*$/D', $part) !== 1) {
                throw new IdSumException('idsum contains an invalid view identifier.', IdSumException::INVALID_ID);
            }

            $id = (int) $part;

            if (in_array($id, $ids, true)) {
                throw new IdSumException('idsum contains a duplicate view identifier.', IdSumException::DUPLICATE_ID);
            }

            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * @param list<int> $ids
     * @param callable(int, array<string, mixed>): array<string, mixed> $loader
     * @param array<string, mixed> $options
     *
     * @return list<array<string, mixed>>
     */
    public static function collectPayloads(array $ids, callable $loader, array $options): array
    {
        $payloads = [];

        foreach ($ids as $id) {
            $payloads[] = $loader($id, $options);
        }

        return $payloads;
    }

    /**
     * Merge already filtered and grouped view payloads before additions, title mappings and sorting.
     *
     * @param list<array<string, mixed>> $payloads
     *
     * @return array<string, mixed>
     */
    public static function mergePayloads(array $payloads): array
    {
        if ($payloads === []) {
            return [];
        }

        $merged = $payloads[0];
        $values = [];

        foreach ($payloads as $payload) {
            foreach ((array) ($payload['field']['values'] ?? []) as $label => $value) {
                $values[$label] = ($values[$label] ?? 0) + (int) $value;
            }
        }

        $merged['records']['total'] = array_sum($values);
        $merged['field'] = (array) ($merged['field'] ?? []);
        $merged['field']['total'] = array_sum($values);
        $merged['field']['values'] = $values;
        $merged['field'] = array_replace(
            $merged['field'],
            StatsService::computeFieldAggregates($values)
        );

        return $merged;
    }
}
