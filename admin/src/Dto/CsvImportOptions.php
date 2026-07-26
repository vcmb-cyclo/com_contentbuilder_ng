<?php

/**
 * @package     ContentBuilderNG
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Dto;

\defined('_JEXEC') or die;

final readonly class CsvImportOptions
{
    /**
     * @param array<int,int|string>|null $selectedColumns
     */
    public function __construct(
        public string $delimiter = ',',
        public string $repairEncoding = '',
        public ?array $selectedColumns = null,
        public bool $importData = true,
        public bool $dropRecords = false,
        public int $published = 0
    ) {
    }

    /**
     * @param array<string,mixed> $postData
     */
    public static function fromPostData(array $postData): self
    {
        $nested = is_array($postData['jform'] ?? null) ? $postData['jform'] : [];

        return new self(
            delimiter: (string) self::option($nested, $postData, 'csv_delimiter', ','),
            repairEncoding: (string) self::option($nested, $postData, 'csv_repair_encoding', ''),
            selectedColumns: self::selection($nested, $postData),
            importData: self::boolean(self::option($nested, $postData, 'csv_import_data', true)),
            dropRecords: self::boolean(self::option($nested, $postData, 'csv_drop_records', false)),
            published: (int) self::option($nested, $postData, 'csv_published', 0)
        );
    }

    /**
     * @param array<string,mixed> $nested
     * @param array<string,mixed> $root
     */
    private static function option(array $nested, array $root, string $key, mixed $default): mixed
    {
        if (array_key_exists($key, $nested)) {
            return $nested[$key];
        }

        return $root[$key] ?? $default;
    }

    /**
     * @param array<string,mixed> $nested
     * @param array<string,mixed> $root
     *
     * @return array<int,int|string>|null
     */
    private static function selection(array $nested, array $root): ?array
    {
        if (array_key_exists('csv_import_columns', $nested)) {
            return (array) $nested['csv_import_columns'];
        }

        if (array_key_exists('csv_import_columns', $root)) {
            return (array) $root['csv_import_columns'];
        }

        return null;
    }

    private static function boolean(mixed $value): bool
    {
        if (is_array($value)) {
            $value = end($value);
        }

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
