<?php

/**
 * @package     ContentBuilderNG
 * @author      Markus Bopp
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$requestedSection = Factory::getApplication()->getInput()->getCmd('section', 'overview');
$sections = [
    'overview' => ['COM_CONTENTBUILDERNG_HELP_VIEW_TITLE', 'OVERVIEW'],
    'tab0' => ['COM_CONTENTBUILDERNG_VIEW', 'VIEW'],
    'tab9' => ['COM_CONTENTBUILDERNG_ADVANCED_OPTIONS', 'OPTIONS'],
    'tab2' => ['COM_CONTENTBUILDERNG_LIST_INTRO_TEXT', 'INTRO'],
    'tab1' => ['COM_CONTENTBUILDERNG_LIST_STATES', 'STATES'],
    'tab3' => ['COM_CONTENTBUILDERNG_TAB_DETAILS_DISPLAY', 'DETAIL'],
    'tab5' => ['COM_CONTENTBUILDERNG_TAB_EDIT_DISPLAY', 'EDIT'],
    'tab10' => ['COM_CONTENTBUILDERNG_ARTICLE', 'ARTICLE'],
    'tab6' => ['COM_CONTENTBUILDERNG_API_TAB_TITLE', 'API'],
    'tab7' => ['COM_CONTENTBUILDERNG_EMAIL_TEMPLATES', 'EMAILS'],
    'tab8' => ['COM_CONTENTBUILDERNG_PERMISSIONS', 'PERMISSIONS'],
    'tab12' => ['COM_CONTENTBUILDERNG_AUDIT_TRAIL', 'AUDIT'],
    'tab13' => ['COM_CONTENTBUILDERNG_TAB_PERFORMANCE', 'PERFORMANCE'],
    'tab14' => ['COM_CONTENTBUILDERNG_TAB_DATA', 'DATA'],
    'tab11' => ['COM_CONTENTBUILDERNG_TAB_DEBUG', 'DEBUG'],
];

if (!isset($sections[$requestedSection])) {
    $requestedSection = 'overview';
}

[$titleKey, $contentSuffix] = $sections[$requestedSection];
$helpBaseUrl = Uri::base() . 'index.php?option=com_contentbuilderng&view=form&layout=help&tmpl=component';
$contextKeyPrefix = 'COM_CONTENTBUILDERNG_HELP_CONTEXT_' . $contentSuffix;
?>
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0"><?php echo Text::sprintf('COM_CONTENTBUILDERNG_HELP_CONTEXT_TITLE', Text::_($titleKey)); ?></h1>
        <span class="badge bg-primary"><?php echo Text::_('COM_CONTENTBUILDERNG_HELP_CONTEXT_CURRENT_TAB'); ?></span>
    </div>

    <nav class="d-flex flex-wrap gap-1 mb-3" aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_HELP_CONTEXT_NAV'), ENT_QUOTES, 'UTF-8'); ?>">
        <?php foreach ($sections as $sectionId => [$sectionTitleKey]) : ?>
            <a class="btn btn-sm <?php echo htmlspecialchars($sectionId === $requestedSection ? 'btn-primary' : 'btn-outline-secondary', ENT_QUOTES, 'UTF-8'); ?>"
               href="<?php echo htmlspecialchars($helpBaseUrl . '&section=' . rawurlencode($sectionId), ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo Text::_($sectionTitleKey); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h5"><?php echo Text::_($titleKey); ?></h2>
            <p class="lead fs-6 mb-3"><?php echo Text::_($contextKeyPrefix . '_PURPOSE'); ?></p>
            <h3 class="h6"><?php echo Text::_('COM_CONTENTBUILDERNG_HELP_CONTEXT_WORKFLOW'); ?></h3>
            <p><?php echo Text::_($contextKeyPrefix . '_WORKFLOW'); ?></p>
            <h3 class="h6"><?php echo Text::_('COM_CONTENTBUILDERNG_HELP_CONTEXT_CHECKS'); ?></h3>
            <p class="mb-0"><?php echo Text::_($contextKeyPrefix . '_CHECKS'); ?></p>
        </div>
    </div>

    <?php if (in_array($requestedSection, ['tab1', 'tab3', 'tab5'], true)) : ?>
        <div class="card border-info shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_INDICATOR_HELP_TITLE'); ?></h2>
                <?php if ($requestedSection === 'tab1') : ?>
                    <ul class="mb-0">
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_LIST_STATES_BADGE_HELP_NONE'); ?></li>
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_LIST_STATES_BADGE_HELP_ORANGE'); ?></li>
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_LIST_STATES_BADGE_HELP_GREEN'); ?></li>
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_LIST_STATES_BADGE_HELP_RED'); ?></li>
                    </ul>
                <?php else : ?>
                    <ul class="mb-0">
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_INDICATOR_HELP_NONE'); ?></li>
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_INDICATOR_HELP_ORANGE'); ?></li>
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_INDICATOR_HELP_GREEN'); ?></li>
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_INDICATOR_HELP_RED'); ?></li>
                        <li><?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_INDICATOR_HELP_LOCK'); ?></li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="alert alert-warning mb-0">
        <span class="fa-solid fa-triangle-exclamation me-1" aria-hidden="true"></span>
        <?php echo Text::_($contextKeyPrefix . '_WARNING'); ?>
    </div>
</div>
