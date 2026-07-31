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

namespace CB\Component\Contentbuilderng\Administrator\Helper\Audit;

\defined('_JEXEC') or die('Restricted access');

use CB\Component\Contentbuilderng\Administrator\Helper\ContentbuilderngHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\PackedDataHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

/**
 * Reports upload destinations that sit inside the web root without an
 * execution guard, and can drop a hardening .htaccess into them on request.
 *
 * Uploads are never rewritten implicitly: a destination directory can be
 * shared with other software, so the guard is only ever written when an
 * administrator explicitly runs this repair step.
 */
final class UploadDirectoryProtectionAuditHelper
{
    private const GUARD_FILENAME = '.htaccess';

    private const GUARD_CONTENTS = "# Added by ContentBuilder NG (Repair > upload directory protection).\n"
        . "# Prevents execution of uploaded files. Safe to remove if this directory\n"
        . "# is not served by Apache, or if execution is blocked elsewhere.\n"
        . "php_flag engine off\n"
        . "RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phar .cgi .pl .py\n"
        . "RemoveType .php .phtml .php3 .php4 .php5 .php7 .php8 .phar .cgi .pl .py\n"
        . "<IfModule mod_headers.c>\n"
        . "    Header set X-Content-Type-Options nosniff\n"
        . "</IfModule>\n";

    /**
     * Collect configured upload destinations that are unprotected.
     *
     * @return array{0: array<int, array{path: string, source: string, form_id: int}>, 1: array<int, string>}
     */
    public static function inspect(DatabaseInterface $db): array
    {
        $found  = [];
        $errors = [];
        $seen   = [];

        foreach (self::collectConfiguredDirectories($db, $errors) as $entry) {
            $path = self::resolvePath((string) $entry['path']);

            if ($path === '' || isset($seen[$path])) {
                continue;
            }

            $seen[$path] = true;

            // Only directories that exist, live inside the site and are
            // actually reachable by the web server are worth guarding.
            if (!is_dir($path) || !ContentbuilderngHelper::is_internal_path($path)) {
                continue;
            }

            if (!self::isInsideWebRoot($path) || self::hasGuard($path)) {
                continue;
            }

            $found[] = [
                'path'    => $path,
                'source'  => (string) $entry['source'],
                'form_id' => (int) $entry['form_id'],
            ];
        }

        return [$found, $errors];
    }

    /**
     * Write the execution guard into every unprotected upload destination.
     *
     * @return array{scanned: int, issues: int, repaired: int, unchanged: int, errors: int, protected: array<string>, warnings: array<string>}
     */
    public static function repair(DatabaseInterface $db): array
    {
        [$found, $inspectErrors] = self::inspect($db);

        $scanned    = count($found);
        $repaired   = 0;
        $unchanged  = 0;
        $errorCount = count($inspectErrors);
        $guarded    = [];
        $warnings   = $inspectErrors;

        foreach ($found as $entry) {
            $path = (string) $entry['path'];
            $target = $path . '/' . self::GUARD_FILENAME;

            if (!is_dir($path) || !is_writable($path)) {
                $warnings[] = 'Not writable, skipped: ' . $path;
                $unchanged++;
                $errorCount++;
                continue;
            }

            try {
                if (File::write($target, $contents = self::GUARD_CONTENTS)) {
                    $guarded[] = $target;
                    $repaired++;
                } else {
                    $warnings[] = 'Could not write: ' . $target;
                    $unchanged++;
                    $errorCount++;
                }
            } catch (\Throwable $e) {
                $warnings[] = 'Error writing ' . $target . ': ' . $e->getMessage();
                $unchanged++;
                $errorCount++;
            }
        }

        return [
            'scanned'   => $scanned,
            'issues'    => $scanned,
            'repaired'  => $repaired,
            'unchanged' => $unchanged,
            'errors'    => $errorCount,
            'protected' => $guarded,
            'warnings'  => $warnings,
        ];
    }

    /**
     * Form-level and element-level upload destinations, as configured.
     *
     * @param array<int, string> $errors
     *
     * @return array<int, array{path: string, source: string, form_id: int}>
     */
    private static function collectConfiguredDirectories(DatabaseInterface $db, array &$errors): array
    {
        $entries = [];

        try {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('upload_directory'),
                ])
                ->from($db->quoteName('#__contentbuilderng_forms'));
            $db->setQuery($query);

            foreach ($db->loadAssocList() ?: [] as $row) {
                $directory = trim((string) ($row['upload_directory'] ?? ''));

                if ($directory !== '') {
                    $entries[] = [
                        'path'    => $directory,
                        'source'  => 'form',
                        'form_id' => (int) ($row['id'] ?? 0),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $errors[] = 'Failed to read form upload directories: ' . $e->getMessage();
        }

        try {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('form_id'),
                    $db->quoteName('options'),
                ])
                ->from($db->quoteName('#__contentbuilderng_elements'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('upload'));
            $db->setQuery($query);

            foreach ($db->loadAssocList() ?: [] as $row) {
                $options = PackedDataHelper::decodePackedData($row['options'] ?? '', [], true);
                $directory = '';

                if (is_array($options)) {
                    $directory = trim((string) ($options['upload_directory'] ?? ''));
                } elseif (is_object($options)) {
                    $directory = trim((string) ($options->upload_directory ?? ''));
                }

                if ($directory !== '') {
                    $entries[] = [
                        'path'    => $directory,
                        'source'  => 'element',
                        'form_id' => (int) ($row['form_id'] ?? 0),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $errors[] = 'Failed to read element upload directories: ' . $e->getMessage();
        }

        return $entries;
    }

    /**
     * Expand the {cbsite} token and strip the per-record path tokens, keeping
     * the deepest static ancestor: token segments only ever create
     * subdirectories of it.
     */
    private static function resolvePath(string $configured): string
    {
        $path = str_replace('|', '/', $configured);
        $path = str_replace(['{CBSite}', '{cbsite}'], JPATH_SITE, $path);
        $path = str_replace('\\', '/', $path);

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if (str_contains($segment, '{')) {
                break;
            }

            $segments[] = $segment;
        }

        $path = rtrim(implode('/', $segments), '/');

        if ($path === '') {
            return '';
        }

        if (!str_starts_with($path, '/') && !preg_match('#^[A-Za-z]:/#', $path)) {
            $path = rtrim(str_replace('\\', '/', JPATH_SITE), '/') . '/' . ltrim($path, '/');
        }

        $real = @realpath($path);

        return $real === false ? '' : rtrim(str_replace('\\', '/', $real), '/');
    }

    private static function isInsideWebRoot(string $path): bool
    {
        $root = realpath(JPATH_SITE) ?: JPATH_SITE;
        $root = rtrim(str_replace('\\', '/', $root), '/');

        return $path === $root || str_starts_with($path, $root . '/');
    }

    private static function hasGuard(string $path): bool
    {
        return is_file($path . '/' . self::GUARD_FILENAME);
    }
}
