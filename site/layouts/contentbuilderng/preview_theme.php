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

$storedUri = clone Uri::getInstance();
$storedUri->delVar(PreviewThemeHelper::QUERY_PARAM);

$options = [[
    'url' => $storedUri->toString(),
    'label' => $storedTheme !== ''
        ? Text::sprintf('COM_CONTENTBUILDERNG_PREVIEW_THEME_STORED_NAMED', $storedTheme)
        : Text::_('COM_CONTENTBUILDERNG_PREVIEW_THEME_STORED'),
    'selected' => $currentTheme === PreviewThemeHelper::NONE ? 'selected' : '',
]];

foreach ($themes as $theme) {
    $theme = trim((string) $theme);

    if ($theme === '') {
        continue;
    }

    $uri = clone Uri::getInstance();
    $uri->setVar(PreviewThemeHelper::QUERY_PARAM, $theme);

    $options[] = [
        'url' => $uri->toString(),
        'label' => $theme,
        'selected' => $currentTheme === $theme ? 'selected' : '',
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
