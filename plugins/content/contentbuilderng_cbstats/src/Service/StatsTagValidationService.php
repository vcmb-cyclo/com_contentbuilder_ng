<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngStats\Service;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Site\Service\StatsHideOptionsService;
use CB\Component\Contentbuilderng\Site\Service\StatsService;
use CB\Component\Contentbuilderng\Site\Service\ContentCardService;

final class StatsTagValidationService
{
    private const OUTPUTS = [
        'total', 'table', 'form_name', 'distinct', 'sum', 'min', 'max', 'avg',
        'json', 'pie', 'bar', 'histogram', 'line', 'radar',
    ];
    private const MANUAL_OUTPUTS = ['total', 'table', 'pie', 'bar', 'histogram', 'line', 'radar'];
    private const LIST_OUTPUTS = ['table', 'json', 'pie', 'bar', 'histogram', 'line', 'radar'];
    private const FIELD_OUTPUTS = ['table', 'json', 'pie', 'bar', 'histogram', 'line', 'radar', 'distinct', 'sum', 'min', 'max', 'avg'];
    private const ALLOWED_KEYS = [
        'source', 'id', 'idsum', 'debug', 'output', 'field', 'filter[field]',
        'filter[value]', 'value', 'add', 'titles', 'ranges', 'headers', 'title',
        'background', 'sort', 'dir', 'values', 'export', 'limit', 'hide', 'total',
        'card', 'w', 'width', 'height',
    ];

    /**
     * @param array<string, string> $attributes
     *
     * @return list<array{code: string, parameter: string, value: string, detail: string}>
     */
    public static function validationErrors(array $attributes, int $fallbackId = 0, array $quoted = []): array
    {
        $errors = [];

        foreach (array_values(array_diff(array_keys($attributes), self::ALLOWED_KEYS)) as $key) {
            $errors[] = self::error('unknown_option', $key, (string) $attributes[$key]);
        }
        foreach (['id', 'idsum', 'limit', 'w'] as $numericKey) {
            if (($quoted[$numericKey] ?? false) === true) {
                $errors[] = self::error(
                    'invalid_value',
                    $numericKey,
                    (string) ($attributes[$numericKey] ?? ''),
                    $numericKey . '_syntax'
                );
            }
        }

        $source = TagSyntaxService::normalizeKeyword((string) ($attributes['source'] ?? 'view'));
        $output = TagSyntaxService::normalizeKeyword((string) ($attributes['output'] ?? 'total'));
        $manual = $source === 'manual';

        if (!in_array($source, ['view', 'manual'], true)) {
            $errors[] = self::error('invalid_value', 'source', (string) ($attributes['source'] ?? ''), 'source');
        }

        $allowedOutputs = $manual ? self::MANUAL_OUTPUTS : self::OUTPUTS;
        if (!in_array($output, $allowedOutputs, true)) {
            $errors[] = self::error('invalid_value', 'output', (string) ($attributes['output'] ?? ''), $manual ? 'manual_output' : 'output');
        }

        $card = trim((string) ($attributes['card'] ?? ''));
        if ($card !== '' && !ContentCardService::isValid($card)) {
            $errors[] = self::error('invalid_value', 'card', $card, 'card');
        }

        $width = trim((string) ($attributes['w'] ?? ''));
        if ($width !== '' && !ContentCardService::isValidWidth($width)) {
            $errors[] = self::error('invalid_value', 'w', $width, 'w');
        } elseif ($width !== '' && !ContentCardService::isValid($card)) {
            $errors[] = self::error('invalid_value', 'w', $width, 'w_requires_card');
        }

        foreach (['width', 'height'] as $dimension) {
            $value = trim((string) ($attributes[$dimension] ?? ''));
            if ($value !== '' && CssDimensionService::normalize($value) === null) {
                $errors[] = self::error('invalid_value', $dimension, $value, $dimension);
            }
        }

        if ($source === 'view') {
            self::validateViewSource($attributes, $fallbackId, $output, $errors);
        } elseif ($manual) {
            try {
                ManualValuesParser::parse((string) ($attributes['values'] ?? ''));
            } catch (ManualValuesException $exception) {
                $errors[] = self::error(
                    'invalid_value',
                    'values',
                    $exception->getEntry() !== '' ? $exception->getEntry() : (string) ($attributes['values'] ?? ''),
                    'values'
                );
            }
        }

        $usesListOptions = ($manual && in_array($output, self::MANUAL_OUTPUTS, true))
            || (!$manual && in_array($output, self::LIST_OUTPUTS, true));
        if ($usesListOptions) {
            $allowedSort = $manual ? ['none', 'title', 'label', 'value'] : ['none', 'title', 'value'];
            $sort = TagSyntaxService::normalizeKeyword((string) ($attributes['sort'] ?? 'none'));
            if (!in_array($sort, $allowedSort, true)) {
                $errors[] = self::error('invalid_value', 'sort', (string) ($attributes['sort'] ?? ''), $manual ? 'manual_sort' : 'sort');
            }

            $dir = TagSyntaxService::normalizeKeyword((string) ($attributes['dir'] ?? 'asc'));
            if (!in_array($dir, ['asc', 'desc'], true)) {
                $errors[] = self::error('invalid_value', 'dir', (string) ($attributes['dir'] ?? ''), 'dir');
            }
        }

        try {
            DisplayOptionsService::parseLimit($attributes);
        } catch (\InvalidArgumentException) {
            $errors[] = self::error('invalid_value', 'limit', (string) ($attributes['limit'] ?? ''), 'limit');
        }

        if (in_array($output, $allowedOutputs, true)) {
            try {
                $hide = StatsHideOptionsService::fromAttributes($attributes);
                StatsHideOptionsService::validateForOutput($hide, $output);
            } catch (\InvalidArgumentException $exception) {
                $errors[] = self::error(
                    'invalid_value',
                    array_key_exists('total', $attributes) ? 'total' : 'hide',
                    array_key_exists('total', $attributes)
                        ? (string) $attributes['total']
                        : (string) ($attributes['hide'] ?? $exception->getMessage()),
                    'hide'
                );
            }
        }

        $export = TagSyntaxService::normalizeKeyword((string) ($attributes['export'] ?? ''));
        if ($export !== '' && $export !== 'manual') {
            $errors[] = self::error('invalid_value', 'export', (string) $attributes['export'], 'export');
        }

        $background = trim((string) ($attributes['background'] ?? ''));
        if ($background !== '' && TotalPresentationService::validateBackground($background) === '') {
            $errors[] = self::error('invalid_value', 'background', $background, 'background');
        }

        self::validateMappings($attributes, $usesListOptions, $manual, $errors);

        return $errors;
    }

    /**
     * @param array<string, string> $attributes
     * @param list<array{code: string, parameter: string, value: string, detail: string}> $errors
     */
    private static function validateViewSource(array $attributes, int $fallbackId, string $output, array &$errors): void
    {
        $id = trim((string) ($attributes['id'] ?? ''));
        $idSum = trim((string) ($attributes['idsum'] ?? ''));

        if ($id !== '' && $idSum !== '') {
            $errors[] = self::error('invalid_value', 'id/idsum', $id . ' / ' . $idSum, 'id_conflict');
        } elseif ($idSum !== '') {
            try {
                IdSumService::parseIds($idSum);
            } catch (IdSumException) {
                $errors[] = self::error('invalid_value', 'idsum', $idSum, 'idsum');
            }
        } elseif (!self::isPositiveInteger($id) && $fallbackId < 1) {
            $errors[] = self::error('invalid_value', 'id', $id, 'id');
        }

        $field = trim((string) ($attributes['field'] ?? ''));
        if ((in_array($output, self::FIELD_OUTPUTS, true) || $idSum !== '') && $field === '') {
            $errors[] = self::error('invalid_value', 'field', '', 'field');
        }

        if ($idSum !== '' && $output === 'form_name') {
            $errors[] = self::error('invalid_value', 'output', $output, 'idsum_output');
        }

        $filter = TagSyntaxService::resolveFilter($attributes);
        if (($filter['field'] === '') !== ($filter['value'] === '')) {
            $errors[] = self::error(
                'invalid_value',
                $filter['field'] === '' ? 'filter[field]' : 'filter[value]',
                '',
                'filter'
            );
        }
    }

    /**
     * @param array<string, string> $attributes
     * @param list<array{code: string, parameter: string, value: string, detail: string}> $errors
     */
    private static function validateMappings(array $attributes, bool $usesListOptions, bool $manual, array &$errors): void
    {
        $checks = [
            'headers' => static fn(string $value): array => StatsService::parseFieldStatsHeaders($value),
        ];

        if ($usesListOptions) {
            $checks['add'] = static fn(string $value): array => StatsService::parseFieldStatsAdditions($value);
            $checks['titles'] = static fn(string $value): array => StatsService::parseFieldStatsTitles($value);
        }
        if (!$manual) {
            $checks['ranges'] = static fn(string $value): array => StatsService::parseFieldStatsRanges($value);
        }

        foreach ($checks as $parameter => $parser) {
            $value = trim((string) ($attributes[$parameter] ?? ''));
            if ($value === '') {
                continue;
            }

            try {
                $parser($value);
            } catch (\InvalidArgumentException $exception) {
                $errors[] = self::error(
                    'invalid_value',
                    $parameter,
                    $exception->getMessage() !== '' ? $exception->getMessage() : $value,
                    $parameter
                );
            }
        }
    }

    private static function isPositiveInteger(string $value): bool
    {
        return preg_match('/^[1-9][0-9]*$/D', $value) === 1;
    }

    /** @return array{code: string, parameter: string, value: string, detail: string} */
    private static function error(string $code, string $parameter, string $value, string $detail = ''): array
    {
        return compact('code', 'parameter', 'value', 'detail');
    }
}
