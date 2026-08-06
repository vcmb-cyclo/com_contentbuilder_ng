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

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die('Restricted access');

final class CompactPaginationService
{
    /**
     * @return list<int|null> Page numbers with null entries representing gaps.
     */
    public static function pages(int $totalPages, int $currentPage): array
    {
        if ($totalPages < 1) {
            return [];
        }

        $currentPage = max(1, min($currentPage, $totalPages));
        $windowStart = max(1, min($currentPage - 2, $totalPages - 4));
        $windowEnd = min($totalPages, $windowStart + 4);
        $pages = [1, 2, $totalPages - 1, $totalPages];

        for ($page = $windowStart; $page <= $windowEnd; $page++) {
            $pages[] = $page;
        }

        $pages = array_values(array_unique(array_filter(
            $pages,
            static fn(int $page): bool => $page >= 1 && $page <= $totalPages
        )));
        sort($pages, SORT_NUMERIC);

        $result = [];
        $previous = null;

        foreach ($pages as $page) {
            if ($previous !== null && $page > $previous + 1) {
                $result[] = null;
            }

            $result[] = $page;
            $previous = $page;
        }

        return $result;
    }

    public static function isInLocalWindow(int $page, int $totalPages, int $currentPage): bool
    {
        if ($totalPages < 1 || $page < 1 || $page > $totalPages) {
            return false;
        }

        $currentPage = max(1, min($currentPage, $totalPages));
        $windowStart = max(1, min($currentPage - 2, $totalPages - 4));

        return $page >= $windowStart && $page <= min($totalPages, $windowStart + 4);
    }
}
