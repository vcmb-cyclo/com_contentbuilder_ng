<?php

/**
 * @package     ContentBuilderNG
 * @author      Markus Bopp
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Service\ApiPermissionRequirementService;
use CB\Component\Contentbuilderng\Site\Service\CbstatsHelpService;
use CB\Component\Contentbuilderng\Site\Service\EmbeddedListHelpService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$apiExampleDetailUrl = (string) ($displayData['apiExampleDetailUrl'] ?? '');
$apiExampleListUrl = (string) ($displayData['apiExampleListUrl'] ?? '');
$apiExampleUpdateUrl = (string) ($displayData['apiExampleUpdateUrl'] ?? '');
$apiExampleStatsUrl = (string) ($displayData['apiExampleStatsUrl'] ?? '');
$apiExampleFilteredStatsUrl = (string) ($displayData['apiExampleFilteredStatsUrl'] ?? '');
$apiExampleVerboseUrl = (string) ($displayData['apiExampleVerboseUrl'] ?? '');
$apiExampleDetailDisplayUrl = (string) ($displayData['apiExampleDetailDisplayUrl'] ?? '');
$apiExampleListDisplayUrl = (string) ($displayData['apiExampleListDisplayUrl'] ?? '');
$apiExampleStatsDisplayUrl = (string) ($displayData['apiExampleStatsDisplayUrl'] ?? '');
$apiExampleFilteredStatsDisplayUrl = (string) ($displayData['apiExampleFilteredStatsDisplayUrl'] ?? '');
$apiExampleVerboseDisplayUrl = (string) ($displayData['apiExampleVerboseDisplayUrl'] ?? '');
$apiExampleSparseListUrl = (string) ($displayData['apiExampleSparseListUrl'] ?? '');
$apiExampleSparseDetailUrl = (string) ($displayData['apiExampleSparseDetailUrl'] ?? '');
$apiExampleSparseStatsUrl = (string) ($displayData['apiExampleSparseStatsUrl'] ?? '');
$apiExampleSparseListDisplayUrl = (string) ($displayData['apiExampleSparseListDisplayUrl'] ?? '');
$apiExampleSparseDetailDisplayUrl = (string) ($displayData['apiExampleSparseDetailDisplayUrl'] ?? '');
$apiExampleSparseStatsDisplayUrl = (string) ($displayData['apiExampleSparseStatsDisplayUrl'] ?? '');
$apiExamplePayloadJson = (string) ($displayData['apiExamplePayloadJson'] ?? '');
$formId = (int) ($displayData['formId'] ?? 0);
$cbStatsTotalSyntax = '{CBStats id=' . $formId . ' output=total}';
$cbStatsPieSyntax   = '{CBStats id=' . $formId . ' field=NomDuChamp title="Répartition" output=pie card=h1 width=80%}';
$cbStatsBarSyntax   = '{CBStats id=' . $formId . ' field=NomDuChamp sort=value dir=desc output=bar}';
$cbStatsApiJsonUrl = 'index.php?option=com_contentbuilderng&task=api.display&id=' . $formId . '&action=cbstats&field=NomDuChamp&output=json';
$cbListBasicSyntax = '{CBList id=' . $formId . '}';
$cbListFieldsSyntax = '{CBList id=' . $formId . ' fields="Nom|Prenom|Email" sort="Nom" dir=asc}';
$cbListCleanSyntax = '{CBList id=' . $formId . ' actions=none pagination=0 limit=10}';
$cbListTestUrl = 'index.php?option=com_contentbuilderng&task=list.display&id=' . $formId;
$helpLanguage = match (Factory::getApplication()->getLanguage()->getTag()) {
    'fr-FR' => 'fr-FR',
    'de-DE' => 'de-DE',
    default => 'en-GB',
};
$cbStatsHelpUrl = CbstatsHelpService::syntaxUrl() . '&help_lang=' . rawurlencode($helpLanguage);
$cbListHelpUrl = EmbeddedListHelpService::syntaxUrl() . '&help_lang=' . rawurlencode($helpLanguage);
$apiPermissionRequirements = new ApiPermissionRequirementService();
$permissionLabelKeys = [
    'api' => 'COM_CONTENTBUILDERNG_PERM_API',
    'view' => 'COM_CONTENTBUILDERNG_PERM_VIEW',
    'listaccess' => 'COM_CONTENTBUILDERNG_PERM_LIST_ACCESS',
    'edit' => 'COM_CONTENTBUILDERNG_PERM_EDIT',
    'rating' => 'COM_CONTENTBUILDERNG_PERM_RATING',
    'stats' => 'COM_CONTENTBUILDERNG_PERM_STATS',
];
$renderPermissions = static function (array $permissions) use ($permissionLabelKeys): string {
    $items = [];

    foreach ($permissions as $permission) {
        $labelKey = $permissionLabelKeys[$permission] ?? '';
        if ($labelKey === '') {
            continue;
        }

        $items[] = '<span class="badge bg-secondary">' . htmlspecialchars(Text::_($labelKey), ENT_QUOTES, 'UTF-8') . '</span>';
    }

    return implode(' <span class="text-muted">+</span> ', $items);
};
$wa = \CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper::getApplication()->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_contentbuilderng');
$wa->useStyle('com_contentbuilderng.admin-form-api');
?>
<h3 id="cb-form-api" class="mb-3"><?php echo Text::_('COM_CONTENTBUILDERNG_API_TAB_TITLE'); ?></h3>
<p class="text-muted mb-3">
    <?php echo Text::_('COM_CONTENTBUILDERNG_API_TAB_INTRO'); ?>
</p>
<div class="alert alert-info mb-3">
    <?php echo Text::_('COM_CONTENTBUILDERNG_API_TAB_PERMISSION_HINT'); ?>
</div>
<div class="table-responsive mb-3">
    <table id="cb-form-api-endpoints" class="table table-striped align-middle">
        <thead>
            <tr>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_API_METHOD'); ?></th>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_API_ENDPOINT'); ?></th>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_API_DESCRIPTION'); ?></th>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_API_PERMISSIONS'); ?></th>
            </tr>
        </thead>
        <tr>
            <td><code>GET</code></td>
            <td>
                <a href="<?php echo htmlspecialchars($apiExampleDetailUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    <code><?php echo htmlspecialchars($apiExampleDetailDisplayUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                </a>
            </td>
            <td><?php echo Text::_('COM_CONTENTBUILDERNG_API_GET_DETAIL_DESC'); ?></td>
            <td><?php echo $renderPermissions($apiPermissionRequirements->getRequiredPermissions('GET', '', 1)); ?></td>
        </tr>
        <tr>
            <td><code>GET</code></td>
            <td>
                <a href="<?php echo htmlspecialchars($apiExampleListUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    <code><?php echo htmlspecialchars($apiExampleListDisplayUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                </a>
            </td>
            <td><?php echo Text::_('COM_CONTENTBUILDERNG_API_GET_LIST_DESC'); ?></td>
            <td><?php echo $renderPermissions($apiPermissionRequirements->getRequiredPermissions('GET', '', 0)); ?></td>
        </tr>
        <tr>
            <td><code>GET</code></td>
            <td>
                <div class="mb-2">
                    <strong class="d-block"><?php echo Text::_('COM_CONTENTBUILDERNG_API_STATS_GLOBAL_EXAMPLE'); ?></strong>
                    <a href="<?php echo htmlspecialchars($apiExampleStatsUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                        <code><?php echo htmlspecialchars($apiExampleStatsDisplayUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                    </a>
                </div>
                <div>
                    <strong class="d-block"><?php echo Text::_('COM_CONTENTBUILDERNG_API_STATS_FILTERED_EXAMPLE'); ?></strong>
                    <a href="<?php echo htmlspecialchars($apiExampleFilteredStatsUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                        <code><?php echo htmlspecialchars($apiExampleFilteredStatsDisplayUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                    </a>
                </div>
            </td>
            <td>
                <strong class="d-block mb-1"><?php echo Text::_('COM_CONTENTBUILDERNG_API_STATS_SECTION_TITLE'); ?></strong>
                <?php echo Text::_('COM_CONTENTBUILDERNG_API_GET_STATS_DESC'); ?>
            </td>
            <td><?php echo $renderPermissions($apiPermissionRequirements->getRequiredPermissions('GET', 'stats', 0)); ?></td>
        </tr>
        <tr class="cb-form-api-cbstats-row">
            <td>
                <strong class="d-block">CBStats</strong>
                <code><?php echo Text::_('COM_CONTENTBUILDERNG_API_CONTENT_PLUGIN_METHOD'); ?></code>
            </td>
            <td>
                <strong class="d-block mb-2"><?php echo Text::_('COM_CONTENTBUILDERNG_API_CBSTATS_ARTICLE_EXAMPLES'); ?></strong>
                <div class="cb-form-api-cbstats-examples">
                    <code><?php echo htmlspecialchars($cbStatsTotalSyntax, ENT_QUOTES, 'UTF-8'); ?></code>
                    <code><?php echo htmlspecialchars($cbStatsPieSyntax, ENT_QUOTES, 'UTF-8'); ?></code>
                    <code><?php echo htmlspecialchars($cbStatsBarSyntax, ENT_QUOTES, 'UTF-8'); ?></code>
                </div>
                <strong class="d-block mt-3 mb-2"><?php echo Text::_('COM_CONTENTBUILDERNG_API_TEST_URLS'); ?></strong>
                <div class="cb-form-api-cbstats-examples">
                    <a href="<?php echo htmlspecialchars(Route::_($cbStatsApiJsonUrl, false), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                        <code><?php echo htmlspecialchars($cbStatsApiJsonUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                    </a>
                </div>
            </td>
            <td>
                <h4 class="h5 mb-2">CBStats</h4>
                <p><?php echo Text::_('COM_CONTENTBUILDERNG_API_CBSTATS_SUMMARY'); ?></p>
                <p><strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_MAIN_OUTPUTS'); ?></strong><br><code>total, table, pie, bar, histogram, line, radar, json, sum, min, max, avg, remaining, percentage, progress, distinct, view_name</code></p>
                <p><strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_MAIN_OPTIONS'); ?></strong><br><code>id, idsum, field, value, filter[field], filter[value], output, target, groups, groupset, titles, titleset, sort, dir, limit, hide, title, card, w, width, height, export</code></p>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars($cbStatsHelpUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo Text::_('COM_CONTENTBUILDERNG_API_OPEN_CBSTATS_HELP'); ?> <span aria-hidden="true">↗</span>
                </a>
            </td>
            <td><?php echo $renderPermissions($apiPermissionRequirements->getRequiredPermissions('GET', 'cbstats', 0)); ?></td>
        </tr>
        <tr class="cb-form-api-cblist-row">
            <td><strong class="d-block">CBList</strong><code><?php echo Text::_('COM_CONTENTBUILDERNG_API_CONTENT_PLUGIN_METHOD'); ?></code></td>
            <td>
                <strong class="d-block mb-2"><?php echo Text::_('COM_CONTENTBUILDERNG_API_CBLIST_ARTICLE_EXAMPLES'); ?></strong>
                <div class="cb-form-api-cbstats-examples">
                    <?php foreach ([$cbListBasicSyntax, $cbListFieldsSyntax, $cbListCleanSyntax] as $cbListSyntax) : ?>
                        <code><?php echo htmlspecialchars($cbListSyntax, ENT_QUOTES, 'UTF-8'); ?></code>
                    <?php endforeach; ?>
                </div>
                <strong class="d-block mt-3 mb-2"><?php echo Text::_('COM_CONTENTBUILDERNG_API_TEST_URLS'); ?></strong>
                <a href="<?php echo htmlspecialchars(Route::_($cbListTestUrl, false), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><code><?php echo htmlspecialchars($cbListTestUrl, ENT_QUOTES, 'UTF-8'); ?></code></a>
            </td>
            <td>
                <h4 class="h5 mb-2">CBList</h4>
                <p><?php echo Text::_('COM_CONTENTBUILDERNG_API_CBLIST_SUMMARY'); ?></p>
                <p><strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_MAIN_OPTIONS'); ?></strong><br><code>id, fields, sort, dir, pagination, limit, actions, title, layout, height, loading, card, w, output, offset</code></p>
                <p><strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_MAIN_ACTIONS'); ?></strong><br><code>search, state, publish, language, new, edit, delete, export, rating, detail, print, none</code></p>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars($cbListHelpUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo Text::_('COM_CONTENTBUILDERNG_API_OPEN_CBLIST_HELP'); ?> <span aria-hidden="true">↗</span>
                </a>
            </td>
            <td><?php echo $renderPermissions($apiPermissionRequirements->getRequiredPermissions('GET', '', 0)); ?></td>
        </tr>
        <tr>
            <td><code>PUT</code> / <code>PATCH</code> / <code>POST</code></td>
            <td>
                <a href="<?php echo htmlspecialchars($apiExampleUpdateUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    <code><?php echo htmlspecialchars($apiExampleUpdateUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                </a>
            </td>
            <td><?php echo Text::_('COM_CONTENTBUILDERNG_API_UPDATE_DESC'); ?></td>
            <td><?php echo $renderPermissions($apiPermissionRequirements->getRequiredPermissions('PUT', '', 1)); ?></td>
        </tr>
    </table>
</div>
<div class="alert alert-secondary py-2 mb-3">
    <strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_OPENAPI_SPEC_TITLE'); ?></strong>
    <?php echo Text::_('COM_CONTENTBUILDERNG_API_OPENAPI_SPEC_TEXT'); ?>
    <a href="<?php echo htmlspecialchars(Route::_('index.php?option=com_contentbuilderng&view=about&layout=openapi', false), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
        <code class="cb-form-api-inline-code">openapi.json</code>
    </a>
</div>
<div class="alert alert-secondary py-2 mb-3">
    <strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_VERBOSE_OPTION_TITLE'); ?></strong>
    <?php echo Text::_('COM_CONTENTBUILDERNG_API_VERBOSE_OPTION_TEXT'); ?>
    <a href="<?php echo htmlspecialchars($apiExampleVerboseUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
        <code class="cb-form-api-inline-code"><?php echo htmlspecialchars($apiExampleVerboseDisplayUrl, ENT_QUOTES, 'UTF-8'); ?></code>
    </a>
</div>
<div class="card mb-3">
    <div class="card-body">
        <h4 class="card-title"><?php echo Text::_('COM_CONTENTBUILDERNG_API_SPARSE_FIELDSETS_TITLE'); ?></h4>
        <p><?php echo Text::_('COM_CONTENTBUILDERNG_API_SPARSE_FIELDSETS_INTRO'); ?></p>
        <ul class="mb-2">
            <li>
                <strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_SPARSE_FIELDSETS_LIST'); ?></strong>
                <a href="<?php echo htmlspecialchars($apiExampleSparseListUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    <code class="cb-form-api-inline-code"><?php echo htmlspecialchars($apiExampleSparseListDisplayUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                </a>
            </li>
            <li>
                <strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_SPARSE_FIELDSETS_DETAIL'); ?></strong>
                <a href="<?php echo htmlspecialchars($apiExampleSparseDetailUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    <code class="cb-form-api-inline-code"><?php echo htmlspecialchars($apiExampleSparseDetailDisplayUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                </a>
            </li>
            <li>
                <strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_SPARSE_FIELDSETS_STATS'); ?></strong>
                <a href="<?php echo htmlspecialchars($apiExampleSparseStatsUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    <code class="cb-form-api-inline-code"><?php echo htmlspecialchars($apiExampleSparseStatsDisplayUrl, ENT_QUOTES, 'UTF-8'); ?></code>
                </a>
            </li>
        </ul>
        <p class="text-muted mb-0"><?php echo Text::_('COM_CONTENTBUILDERNG_API_SPARSE_FIELDSETS_NOTE'); ?></p>
    </div>
</div>
<label id="cb-form-api-payload" for="cb_api_example_payload" class="form-label"><strong><?php echo Text::_('COM_CONTENTBUILDERNG_API_JSON_LABEL'); ?></strong></label>
<textarea id="cb_api_example_payload" class="form-control" rows="7" readonly="readonly"><?php echo htmlspecialchars($apiExamplePayloadJson, ENT_QUOTES, 'UTF-8'); ?></textarea>
