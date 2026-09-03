<?php

/**
 * @package     ContentBuilderNG
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace CB\Component\Contentbuilderng\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class StorageColumnTypeHelper
{
    public const DEFAULT_TYPE = 'text';
    public const INT_MIN = -2147483648;
    public const INT_MAX = 2147483647;
    public const DATE_MIN = '1000-01-01';
    public const DATE_MAX = '9999-12-31';
    public const VARCHAR_MAX_SIZE = 255;
    public const TEXT_MAX_SIZE = 65535;
    private const TYPES = ['text', 'varchar', 'int', 'decimal', 'date', 'datetime', 'boolean'];

    /**
     * @return array<string,string>
     */
    public static function options(): array
    {
        return [
            'text' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_TEXT'),
            'varchar' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_VARCHAR'),
            'int' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_INT'),
            'decimal' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_DECIMAL'),
            'date' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_DATE'),
            'datetime' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_DATETIME'),
            'boolean' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_BOOLEAN'),
        ];
    }

    public static function normalize(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        return in_array($type, self::TYPES, true) ? $type : self::DEFAULT_TYPE;
    }

    public static function editableElementType(?string $type): string
    {
        return match (self::normalize($type)) {
            'date' => 'calendar',
            'datetime' => 'datetime',
            'int' => 'number',
            'decimal' => 'decimal',
            'boolean' => 'boolean',
            default => 'text',
        };
    }

    public static function isStorageManagedEditableType(string $type): bool
    {
        return in_array($type, ['', 'text', 'calendar', 'datetime', 'number', 'decimal', 'boolean'], true);
    }

    public static function label(?string $type): string
    {
        $type = self::normalize($type);
        $options = self::options();

        return (string) ($options[$type] ?? $type);
    }

    /**
     * "Taille" (longueur de colonne) : uniquement pertinente pour les
     * champs texte (varchar/text) ; sans objet pour les autres types
     * (int/decimal/date/datetime/boolean) et pour les champs système.
     */
    public static function supportsSize(?string $type): bool
    {
        return in_array(self::normalize($type), ['varchar', 'text'], true);
    }

    /**
     * Taille pré-remplie à la sélection du type ("texte court"/"texte
     * long"), modifiable ensuite par l'utilisateur.
     */
    public static function defaultSize(?string $type): ?int
    {
        return self::maxSize($type);
    }

    public static function maxSize(?string $type): ?int
    {
        return match (self::normalize($type)) {
            'varchar' => self::VARCHAR_MAX_SIZE,
            'text' => self::TEXT_MAX_SIZE,
            default => null,
        };
    }

    /**
     * Borne la taille saisie à des valeurs physiquement valides pour le
     * type SQL choisi.
     */
    public static function normalizeSize(?string $type, mixed $size): ?int
    {
        if (!self::supportsSize($type)) {
            return null;
        }

        $size = (int) $size;

        if ($size < 1) {
            return self::defaultSize($type);
        }

        $maximum = self::maxSize($type);

        return $maximum === null ? null : min($size, $maximum);
    }

    public static function sqlDefinition(?string $type, mixed $size = null, bool $required = false): string
    {
        $normalizedType = self::normalize($type);
        $size = self::normalizeSize($normalizedType, $size ?? self::defaultSize($normalizedType));

        $charset = ' CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci';
        $nullability = $required ? ' NOT NULL' : ' NULL';

        return match ($normalizedType) {
            'varchar' => 'VARCHAR(' . ($size ?? 255) . ')' . $charset . $nullability,
            'int' => 'INT' . $nullability,
            'decimal' => 'DECIMAL(15,4)' . $nullability,
            'date' => 'DATE' . $nullability,
            'datetime' => 'DATETIME' . $nullability,
            'boolean' => 'TINYINT(1)' . $nullability,
            default => match (true) {
                $size !== null && $size > 16777215 => 'LONGTEXT' . $charset . $nullability,
                $size !== null && $size > 65535 => 'MEDIUMTEXT' . $charset . $nullability,
                default => 'TEXT' . $charset . $nullability,
            },
        };
    }

    public static function isValidIntegerValue(mixed $value): bool
    {
        if (!is_scalar($value)) {
            return false;
        }

        $value = trim((string) $value);
        if (!preg_match('/^[+-]?\d+$/D', $value)) {
            return false;
        }

        $negative = str_starts_with($value, '-');
        $digits = ltrim($value, '+-0');
        $digits = $digits === '' ? '0' : $digits;
        $limit = $negative ? '2147483648' : '2147483647';

        return strlen($digits) < strlen($limit)
            || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) <= 0);
    }

    public static function isValidTemporalValue(mixed $value, ?string $type): bool
    {
        if (!is_scalar($value)) {
            return false;
        }

        $type = self::normalize($type);
        if (!in_array($type, ['date', 'datetime'], true)) {
            return false;
        }

        $value = trim((string) $value);
        $format = $type === 'datetime' ? 'Y-m-d H:i:s' : 'Y-m-d';
        $parsedValue = \DateTimeImmutable::createFromFormat('!' . $format, $value);
        $errors = \DateTimeImmutable::getLastErrors();

        if (
            !$parsedValue instanceof \DateTimeImmutable
            || ($errors !== false && ($errors['warning_count'] !== 0 || $errors['error_count'] !== 0))
            || $parsedValue->format($format) !== $value
        ) {
            return false;
        }

        $minimum = self::DATE_MIN . ($type === 'datetime' ? ' 00:00:00' : '');
        $maximum = self::DATE_MAX . ($type === 'datetime' ? ' 23:59:59' : '');

        return strcmp($value, $minimum) >= 0 && strcmp($value, $maximum) <= 0;
    }

    /**
     * Type-appropriate "empty" literal used to backfill existing NULL rows
     * before a column is promoted to NOT NULL — TEXT/BLOB columns cannot
     * carry a literal DEFAULT in MySQL, so backfilling first (rather than
     * relying on a DEFAULT clause) is the only approach that works
     * uniformly across every supported SQL type.
     */
    public static function emptyValueLiteral(?string $type): string
    {
        return match (self::normalize($type)) {
            'int', 'decimal', 'boolean' => '0',
            'date' => "'1970-01-01'",
            'datetime' => "'1970-01-01 00:00:00'",
            default => "''",
        };
    }

    /**
     * Backfills any existing NULL value in $quotedColumn to the type's empty
     * literal, then promotes the column to NOT NULL. Safe to call on a
     * populated table: unlike a straight `MODIFY ... NOT NULL`, this never
     * fails because of pre-existing NULLs.
     */
    public static function enforceRequired(
        DatabaseInterface $db,
        string $quotedTable,
        string $quotedColumn,
        ?string $type,
        mixed $size
    ): void {
        $db->setQuery(
            'UPDATE ' . $quotedTable
            . ' SET ' . $quotedColumn . ' = ' . self::emptyValueLiteral($type)
            . ' WHERE ' . $quotedColumn . ' IS NULL'
        );
        $db->execute();

        $db->setQuery(
            'ALTER TABLE ' . $quotedTable
            . ' MODIFY ' . $quotedColumn . ' ' . self::sqlDefinition($type, $size, true)
        );
        $db->execute();
    }

    public static function physicalTypeMatches(?string $expectedType, mixed $columnDefinition): bool
    {
        $physicalType = self::extractPhysicalType($columnDefinition);

        if ($physicalType === '') {
            return false;
        }

        return match (self::normalize($expectedType)) {
            'varchar' => str_starts_with($physicalType, 'varchar'),
            'int' => $physicalType === 'int' || str_starts_with($physicalType, 'int('),
            'decimal' => str_starts_with($physicalType, 'decimal'),
            'date' => $physicalType === 'date',
            'datetime' => $physicalType === 'datetime',
            'boolean' => str_starts_with($physicalType, 'tinyint(1)') || $physicalType === 'boolean' || $physicalType === 'bool',
            default => str_starts_with($physicalType, 'text') || str_starts_with($physicalType, 'mediumtext') || str_starts_with($physicalType, 'longtext'),
        };
    }

    public static function physicalNullabilityMatches(bool $required, mixed $columnDefinition): bool
    {
        $physicalNullable = self::extractPhysicalNullable($columnDefinition);

        // Some callers only provide a type string. In that case there is no
        // reliable nullability information to compare and the type check
        // remains authoritative.
        return $physicalNullable === null || $physicalNullable === !$required;
    }

    public static function extractPhysicalNullable(mixed $columnDefinition): ?bool
    {
        if (is_object($columnDefinition)) {
            $columnDefinition = get_object_vars($columnDefinition);
        }

        if (is_array($columnDefinition)) {
            $rawNullable = null;
            $hasNullable = false;

            foreach (['Null', 'null', 'Nullable', 'nullable'] as $key) {
                if (array_key_exists($key, $columnDefinition)) {
                    $rawNullable = $columnDefinition[$key];
                    $hasNullable = true;
                    break;
                }
            }

            if (!$hasNullable) {
                return null;
            }

            if (is_bool($rawNullable)) {
                return $rawNullable;
            }

            $normalizedNullable = strtoupper(trim((string) $rawNullable));

            return match ($normalizedNullable) {
                'YES', 'Y', 'TRUE', '1' => true,
                'NO', 'N', 'FALSE', '0' => false,
                default => null,
            };
        }

        $definition = trim((string) $columnDefinition);
        if (preg_match('/\bNOT\s+NULL\b/i', $definition)) {
            return false;
        }
        if (preg_match('/\bNULL\b/i', $definition)) {
            return true;
        }

        return null;
    }

    public static function describePhysicalDefinition(mixed $columnDefinition): string
    {
        $type = self::extractPhysicalType($columnDefinition);
        $nullable = self::extractPhysicalNullable($columnDefinition);

        if ($nullable === null) {
            return $type;
        }

        return trim($type . ($nullable ? ' NULL' : ' NOT NULL'));
    }

    public static function extractPhysicalType(mixed $columnDefinition): string
    {
        if (is_object($columnDefinition)) {
            $columnDefinition = get_object_vars($columnDefinition);
        }

        if (is_array($columnDefinition)) {
            $rawType = (string) ($columnDefinition['Type'] ?? $columnDefinition['type'] ?? '');
        } else {
            $rawType = (string) $columnDefinition;
        }

        $rawType = strtolower(trim($rawType));

        if ($rawType === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $rawType);

        return (string) ($parts[0] ?? $rawType);
    }
}
