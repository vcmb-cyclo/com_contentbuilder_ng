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

namespace CB\Component\Contentbuilderng\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Detects whether a database exception was caused by a unique-key violation,
 * so callers can treat a losing request in a race as a benign no-op instead
 * of a hard failure.
 */
final class DuplicateKeyViolationHelper
{
    public static function isDuplicateKeyViolation(\Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'duplicate entry') || str_contains($message, '1062');
    }
}
