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

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Site\Helper\PreviewThemeHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$currentTheme = (string) ($displayData['theme'] ?? PreviewThemeHelper::NONE);
$storedTheme = trim((string) ($displayData['storedTheme'] ?? ''));
$themes = (array) ($displayData['themes'] ?? []);

if (\count($themes) < 2) {
    return;
}

$effectiveTheme = $currentTheme !== PreviewThemeHelper::NONE ? $currentTheme : $storedTheme;
$options = [];

foreach ($themes as $theme) {
    $theme = trim((string) $theme);

    if ($theme === '') {
        continue;
    }

    $uri = clone Uri::getInstance();

    // Picking the view's own theme means "no override at all", so the
    // parameter is dropped rather than set to the stored value.
    if ($theme === $storedTheme) {
        $uri->delVar(PreviewThemeHelper::QUERY_PARAM);
    } else {
        $uri->setVar(PreviewThemeHelper::QUERY_PARAM, $theme);
    }

    $options[] = [
        'url' => $uri->toString(),
        'label' => $theme === $storedTheme
            ? Text::sprintf('COM_CONTENTBUILDERNG_PREVIEW_THEME_CONFIGURED', $theme)
            : $theme,
        'selected' => $theme === $effectiveTheme ? 'selected' : '',
    ];
}

$label = Text::_('COM_CONTENTBUILDERNG_PREVIEW_THEME');
$tooltip = Text::_('COM_CONTENTBUILDERNG_PREVIEW_THEME_TOOLTIP');
?>
<span class="d-inline-flex align-items-center gap-2 ms-2">
    <label for="cb-preview-theme-select" class="mb-0">
        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
    </label>
    <select
        id="cb-preview-theme-select"
        class="form-select form-select-sm w-auto cb-preview-theme-select"
        title="<?php echo htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8'); ?>"
        onchange="if (this.value) { window.location.href = this.value; }">
        <?php foreach ($options as $option) : ?>
            <option
                value="<?php echo htmlspecialchars((string) $option['url'], ENT_QUOTES, 'UTF-8'); ?>"
                <?php echo htmlspecialchars((string) $option['selected'], ENT_QUOTES, 'UTF-8'); ?>
            ><?php echo htmlspecialchars((string) $option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
    </select>
</span>
