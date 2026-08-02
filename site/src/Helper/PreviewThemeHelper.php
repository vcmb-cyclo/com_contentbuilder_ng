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

use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use Joomla\Input\Input;

/**
 * Resolves the admin-preview theme override: which contentbuilderng_themes
 * plugin the list/details/edit screens render with, when an administrator
 * previews a view and picks a theme other than the one stored on the form.
 *
 * The override is per-request only — nothing is persisted, so previewing a
 * theme never changes what a normal visitor sees. It is also gated on the
 * signed admin-preview flag by the caller: unlike the colour mode, which is
 * a client-side CSS switch, choosing a theme imports a plugin server-side,
 * so it stays behind cb_preview_ok rather than being offered on every
 * direct-storage entry point.
 */
final class PreviewThemeHelper
{
    public const QUERY_PARAM = 'cb_preview_theme';

    /**
     * No override requested (or the requested one was rejected).
     */
    public const NONE = '';

    /**
     * @var list<string>|null
     */
    private static ?array $availableThemes = null;

    /**
     * Returns the requested theme when it names an installed and enabled
     * theme plugin, NONE otherwise. An unknown value is dropped rather than
     * passed through, so a mistyped URL falls back to the form's own theme
     * instead of silently landing on the 'thoth' import fallback.
     */
    public static function resolve(Input $input, bool $previewActive): string
    {
        if (!$previewActive) {
            return self::NONE;
        }

        $theme = trim((string) $input->getCmd(self::QUERY_PARAM, ''));

        if ($theme === '') {
            return self::NONE;
        }

        return \in_array($theme, self::availableThemes(), true) ? $theme : self::NONE;
    }

    public static function apply(string $storedTheme, string $previewTheme): string
    {
        return $previewTheme !== self::NONE ? $previewTheme : $storedTheme;
    }

    /**
     * Installed and enabled contentbuilderng_themes plugins, ordered so the
     * stock themes lead in their conventional order and any third-party one
     * follows alphabetically.
     *
     * @return list<string>
     */
    public static function availableThemes(): array
    {
        if (self::$availableThemes !== null) {
            return self::$availableThemes;
        }

        $db = RuntimeContextHelper::getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('element'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('contentbuilderng_themes'))
            ->where($db->quoteName('enabled') . ' = 1');
        $db->setQuery($query);

        $themes = [];

        foreach (($db->loadColumn() ?: []) as $element) {
            $element = trim((string) $element);

            if ($element !== '') {
                $themes[] = $element;
            }
        }

        $themes = array_values(array_unique($themes));

        usort($themes, static function (string $a, string $b): int {
            $order = ['thoth' => 0, 'dark' => 1, 'blank' => 2, 'khepri' => 3];
            $rankA = $order[$a] ?? 99;
            $rankB = $order[$b] ?? 99;

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            return strcasecmp($a, $b);
        });

        self::$availableThemes = $themes;

        return self::$availableThemes;
    }

    public static function appendQuery(string $query, string $theme): string
    {
        return $theme === self::NONE
            ? $query
            : $query . '&' . self::QUERY_PARAM . '=' . rawurlencode($theme);
    }

    public static function appendHiddenField(string $fields, string $theme): string
    {
        if ($theme === self::NONE) {
            return $fields;
        }

        return $fields . "\n"
            . '<input type="hidden" name="' . self::QUERY_PARAM . '" value="'
            . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '" />';
    }
}
