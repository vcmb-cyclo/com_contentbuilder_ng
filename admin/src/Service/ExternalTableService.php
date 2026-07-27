<?php

/**
 * @package     ContentBuilderNG
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class ExternalTableService
{
    private const PROTECTED_CORE_TABLES = [
        'assets',
        'associations',
        'categories',
        'content',
        'content_frontpage',
        'content_types',
        'extensions',
        'fields',
        'fields_categories',
        'fields_groups',
        'fields_values',
        'finder_links',
        'languages',
        'menu',
        'menu_types',
        'messages',
        'modules',
        'modules_menu',
        'schemas',
        'session',
        'tags',
        'template_styles',
        'updates',
        'update_sites',
        'update_sites_extensions',
        'users',
        'user_keys',
        'user_notes',
        'user_profiles',
        'user_usergroup_map',
        'usergroups',
        'viewlevels',
    ];

    private const KNOWN_EXTENSION_MARKERS = [
        'acym' => 'AcyMailing',
        'akeeba' => 'Akeeba',
        'baforms' => 'Balbooa Forms',
        'chronoforms' => 'ChronoForms',
        'community' => 'Community Builder',
        'convertforms' => 'Convert Forms',
        'djcatalog' => 'DJ-Catalog2',
        'docman' => 'DOCman',
        'dpcalendar' => 'DPCalendar',
        'easyblog' => 'EasyBlog',
        'eventbooking' => 'Event Booking',
        'falang' => 'Falang',
        'flexicontent' => 'FLEXIcontent',
        'hikashop' => 'HikaShop',
        'j2store' => 'J2Store',
        'jcomments' => 'JComments',
        'jevents' => 'JEvents',
        'jomsocial' => 'JomSocial',
        'k2_' => 'K2',
        'kunena' => 'Kunena',
        'osmap' => 'OSMap',
        'quix' => 'Quix',
        'regularlabs' => 'Regular Labs',
        'remository' => 'Remository',
        'rsform' => 'RSForm! Pro',
        'sppagebuilder' => 'SP Page Builder',
        'virtuemart' => 'VirtueMart',
    ];

    public const SYSTEM_COLUMNS = [
        'id',
        'storage_id',
        'user_id',
        'created',
        'created_by',
        'modified_user_id',
        'modified',
        'modified_by',
    ];

    /** @var array<string,true>|null */
    private ?array $tablesWithIdColumn = null;

    private bool $bulkLookupUnavailable = false;

    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * @return list<string>
     */
    public function getSelectableTables(): array
    {
        $tables = array_values(array_filter(
            (array) $this->db->getTableList(),
            fn(string $table): bool => !$this->isContentBuilderTable($table) && $this->hasIdColumn($table)
        ));
        natcasesort($tables);

        return array_values($tables);
    }

    public function isSelectable(string $table): bool
    {
        return $table !== ''
            && in_array($table, (array) $this->db->getTableList(), true)
            && !$this->isContentBuilderTable($table)
            && $this->hasIdColumn($table);
    }

    /**
     * The whole "Storage" abstraction (list/details/edit rendering, unique
     * values, record sync...) treats one row as one record identified by a
     * stable "id" column. A table without one (e.g. Joomla's own
     * #__user_profiles, keyed on user_id + profile_key) can't support that,
     * and picking it as an existing-table source breaks throughout the
     * codebase rather than in one isolated spot — so it must never be
     * offered/accepted as selectable in the first place.
     */
    private function hasIdColumn(string $table): bool
    {
        $bulk = $this->loadTablesWithIdColumn();

        if ($bulk !== null) {
            return isset($bulk[$table]);
        }

        // Degraded mode only (see loadTablesWithIdColumn()): one introspection
        // query per table.
        try {
            return array_key_exists('id', $this->db->getTableColumns($table, false));
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Set of schema tables owning an "id" column, resolved in a single query
     * and memoised for the request: getSelectableTables() has to test every
     * table of the instance, and Joomla's driver issues an uncached
     * "SHOW FULL COLUMNS" per getTableColumns() call, which turned that filter
     * into one query per table.
     *
     * Returns null when INFORMATION_SCHEMA cannot be read (restricted grants
     * on shared hosting, for instance). Callers then fall back to per-table
     * introspection: degraded performance is preferable to reporting that no
     * table qualifies, which would silently empty the selector.
     *
     * @return array<string,true>|null
     */
    private function loadTablesWithIdColumn(): ?array
    {
        if ($this->tablesWithIdColumn !== null || $this->bulkLookupUnavailable) {
            return $this->tablesWithIdColumn;
        }

        try {
            $query = $this->db->getQuery(true)
                ->select('DISTINCT ' . $this->db->quoteName('TABLE_NAME'))
                ->from($this->db->quoteName('INFORMATION_SCHEMA.COLUMNS'))
                ->where($this->db->quoteName('TABLE_SCHEMA') . ' = DATABASE()')
                ->where($this->db->quoteName('COLUMN_NAME') . ' = ' . $this->db->quote('id'));
            $this->db->setQuery($query);

            $this->tablesWithIdColumn = array_fill_keys(
                array_map('strval', $this->db->loadColumn() ?: []),
                true
            );
        } catch (\Throwable $e) {
            $this->bulkLookupUnavailable = true;
            $this->tablesWithIdColumn = null;
        }

        return $this->tablesWithIdColumn;
    }

    public function getBytableMode(string $table): int
    {
        return $this->isKnownReadOnly($table) ? 2 : 1;
    }

    public function getSourceType(string $table): string
    {
        $prefix = strtolower($this->db->getPrefix());
        $name = strtolower($table);

        foreach (self::PROTECTED_CORE_TABLES as $coreTable) {
            if ($name === $prefix . $coreTable) {
                return 'joomla';
            }
        }

        if (str_contains($name, 'facileform') || str_contains($name, 'breezing')) {
            return 'breezingforms';
        }

        foreach (array_keys(self::KNOWN_EXTENSION_MARKERS) as $marker) {
            if (str_contains($name, $marker)) {
                return 'joomla-extension';
            }
        }

        return 'external';
    }

    public function getSourceLabel(string $table): string
    {
        $sourceType = $this->getSourceType($table);
        if ($sourceType === 'joomla') {
            return 'Joomla';
        }
        if ($sourceType === 'breezingforms') {
            return 'BreezingForms';
        }

        $name = strtolower($table);
        foreach (self::KNOWN_EXTENSION_MARKERS as $marker => $label) {
            if (str_contains($name, $marker)) {
                return $label;
            }
        }

        return '';
    }

    public function isKnownReadOnly(string $table): bool
    {
        return $this->getSourceType($table) !== 'external';
    }

    private function isContentBuilderTable(string $table): bool
    {
        return str_starts_with(
            strtolower($table),
            strtolower($this->db->getPrefix()) . 'contentbuilderng_'
        );
    }

    /**
     * @return array{table:string,columns:list<string>,missing_system_columns:list<string>}
     */
    public function previewImpact(string $table): array
    {
        if (!$this->isSelectable($table)) {
            throw new \InvalidArgumentException('External table is not selectable.');
        }

        $columns = array_map('strval', array_keys($this->db->getTableColumns($table, true)));
        $missing = $this->getMissingSystemColumns($columns);

        return [
            'table' => $table,
            'columns' => $columns,
            'missing_system_columns' => $missing,
        ];
    }

    /**
     * @param list<string> $columns
     *
     * @return list<string>
     */
    public function getMissingSystemColumns(array $columns): array
    {
        return array_values(array_diff(self::SYSTEM_COLUMNS, array_map('strtolower', $columns)));
    }
}
