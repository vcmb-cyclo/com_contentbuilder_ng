<?php

/**
 * @package     ContentBuilderNG
 * @author      Markus Bopp
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
*/



// No direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');


use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

$wa = \CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper::getApplication()->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_contentbuilderng');
$wa->useStyle('com_contentbuilderng.admin-about');
$wa->useScript('com_contentbuilderng.admin-about.js');

$versionValue = (string) ($this->componentVersion ?: Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));
$creationDateValue = (string) ($this->componentCreationDate ?: Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));
$buildTimestampValue = trim((string) ($this->componentBuildTimestamp ?? ''));
$copyrightValue = (string) ($this->componentCopyright ?: Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));
// La tuile n'affiche jamais le texte brut du manifeste (potentiellement long) :
// un libellé court suffit puisque le lien juste en dessous pointe vers le texte complet.
$licenseValue = Text::_('COM_CONTENTBUILDERNG_LICENSE_FALLBACK');
$buildTypeValue = strtolower(trim((string) ($this->componentBuildType ?? '')));
$isProductionBuild = $buildTypeValue === 'production';
$buildTypeLabel = $isProductionBuild
    ? Text::_('COM_CONTENTBUILDERNG_PRODUCTION_BUILD_LABEL')
    : Text::_('COM_CONTENTBUILDERNG_DEV_BUILD_LABEL');
$formatBuildTimestamp = static function (string $timestamp): string {
    if (trim($timestamp) === '') {
        return '';
    }

    try {
        $timezoneName = (string) \CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper::getApplication()->get('offset', 'UTC');
        $timezone = new \DateTimeZone($timezoneName !== '' ? $timezoneName : 'UTC');
        $date = new \DateTimeImmutable($timestamp, new \DateTimeZone('UTC'));

        return $date->setTimezone($timezone)->format('Y-m-d H:i:s T');
    } catch (\Throwable) {
        return '';
    }
};
$buildTimestampDisplay = $isProductionBuild ? $formatBuildTimestamp($buildTimestampValue) : '';
$buildTypeDisplay = $buildTimestampDisplay !== ''
    ? $buildTypeLabel . ' · ' . $buildTimestampDisplay
    : $buildTypeLabel;
$licenseUrl = 'https://www.gnu.org/licenses/gpl-2.0.html';
$tooltipAudit = Text::_('COM_CONTENTBUILDERNG_ABOUT_TOOLTIP_AUDIT');
$tooltipDbRepair = Text::_('COM_CONTENTBUILDERNG_ABOUT_TOOLTIP_DB_REPAIR');
$tooltipShowLog = Text::_('COM_CONTENTBUILDERNG_ABOUT_TOOLTIP_SHOW_LOG');
$tooltipLinkVcmb = Text::_('COM_CONTENTBUILDERNG_ABOUT_TOOLTIP_LINK_VCMB');
$tooltipLinkLicense = Text::_('COM_CONTENTBUILDERNG_ABOUT_TOOLTIP_LINK_LICENSE');
$tooltipLinkOpenApi = Text::_('COM_CONTENTBUILDERNG_ABOUT_TOOLTIP_LINK_OPENAPI');
$openApiSpecUrl = Route::_('index.php?option=com_contentbuilderng&view=about&layout=openapi', false);
$labelAuditButton = Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT');
$labelDbRepairButton = Text::_('COM_CONTENTBUILDERNG_ABOUT_MIGRATE_PACKED_DATA');
$labelShowLogButton = Text::_('COM_CONTENTBUILDERNG_ABOUT_LAST_LOG');
$auditRowNumberLabel = Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_ROW');
Text::script('COM_CONTENTBUILDERNG_ABOUT_AUDIT_AJAX_REQUEST_FAILED');
Text::script('COM_CONTENTBUILDERNG_ABOUT_AUDIT_REFRESH_FAILED');
$auditReport = is_array($this->auditReport ?? null) ? $this->auditReport : [];
$auditSummary = (array) ($auditReport['summary'] ?? []);
$duplicateIndexes = (array) ($auditReport['duplicate_indexes'] ?? []);
$historicalTables = (array) ($auditReport['historical_tables'] ?? []);
$historicalMenuEntries = (array) ($auditReport['historical_menu_entries'] ?? []);
$tableEncodingIssues = (array) ($auditReport['table_encoding_issues'] ?? []);
$packedDataAudit = is_array($auditReport['packed_data_audit'] ?? null) ? $auditReport['packed_data_audit'] : [];
$packedDataAuditTables = array_values((array) ($packedDataAudit['tables'] ?? []));
$hasPackedDataAuditRows = false;
foreach ($packedDataAuditTables as $packedDataAuditTable) {
    if (is_array($packedDataAuditTable) && !empty((array) ($packedDataAuditTable['rows'] ?? []))) {
        $hasPackedDataAuditRows = true;
        break;
    }
}
$columnEncodingIssues = (array) ($auditReport['column_encoding_issues'] ?? []);
$mixedTableCollations = (array) ($auditReport['mixed_table_collations'] ?? []);
$missingAuditColumns = (array) ($auditReport['missing_audit_columns'] ?? []);
$missingFormAuditColumns = (array) ($auditReport['missing_form_audit_columns'] ?? []);
$pluginExtensionDuplicates = (array) ($auditReport['plugin_extension_duplicates'] ?? []);
$bfFieldSyncIssues = (array) ($auditReport['bf_view_field_sync_issues'] ?? []);
$menuViewIssues = (array) ($auditReport['menu_view_issues'] ?? []);
$frontendPermissionIssues = (array) ($auditReport['frontend_permission_issues'] ?? []);
$elementReferenceIssues = (array) ($auditReport['element_reference_issues'] ?? []);
$contentRecordDuplicateIssues = (array) ($auditReport['content_record_duplicate_issues'] ?? []);
$invalidDatetimeSortIssues = (array) ($auditReport['invalid_datetime_sort_issues'] ?? []);
$storageColumnTypeIssues = (array) ($auditReport['storage_column_type_issues'] ?? []);
$generatedArticleCategoryIssues = (array) ($auditReport['generated_article_category_issues'] ?? []);
$debugModeIssues = (array) ($auditReport['debug_mode_issues'] ?? []);
$formAudits = (array) ($auditReport['form_audits'] ?? []);
$staleLanguageFiles = (array) ($auditReport['stale_language_files'] ?? []);
$staleLanguageFilesCount = (int) ($auditSummary['stale_language_files'] ?? count($staleLanguageFiles));
$hasStaleLanguageFiles = $staleLanguageFilesCount > 0;
$staleInstallerTempDirs = (array) ($auditReport['stale_installer_temp_dirs'] ?? []);
$staleInstallerTempDirsCount = (int) ($auditSummary['stale_installer_temp_dirs'] ?? count($staleInstallerTempDirs));
$hasStaleInstallerTempDirs = $staleInstallerTempDirsCount > 0;
$encodingTargetCharset = (string) ($auditSummary['encoding_target_charset'] ?? 'utf8mb4');
$encodingTargetCollation = (string) ($auditSummary['encoding_target_collation'] ?? 'utf8mb4_0900_ai_ci');
$missingAuditColumnsTotal = (int) ($auditSummary['missing_audit_columns_total'] ?? 0);
$missingAuditColumnsTableCount = (int) ($auditSummary['missing_audit_column_tables'] ?? count($missingAuditColumns));
$missingFormAuditColumnsTotal = (int) ($auditSummary['missing_form_audit_columns_total'] ?? 0);
$missingFormAuditColumnsTableCount = (int) ($auditSummary['missing_form_audit_column_tables'] ?? count($missingFormAuditColumns));
$pluginDuplicateGroups = (int) ($auditSummary['plugin_duplicate_groups'] ?? count($pluginExtensionDuplicates));
$pluginDuplicateRowsToRemove = (int) ($auditSummary['plugin_duplicate_rows_to_remove'] ?? 0);
if ($pluginDuplicateRowsToRemove === 0 && $pluginExtensionDuplicates !== []) {
    foreach ($pluginExtensionDuplicates as $pluginExtensionDuplicate) {
        if (!is_array($pluginExtensionDuplicate)) {
            continue;
        }

        $pluginDuplicateRowsToRemove += count((array) ($pluginExtensionDuplicate['duplicate_ids'] ?? []));
    }
}
if ($missingAuditColumnsTotal === 0 && $missingAuditColumns !== []) {
    foreach ($missingAuditColumns as $missingAuditColumn) {
        if (!is_array($missingAuditColumn)) {
            continue;
        }

        $missingAuditColumnsTotal += count((array) ($missingAuditColumn['missing'] ?? []));
    }
}
$bfFieldSyncViews = (int) ($auditSummary['bf_view_field_sync_views'] ?? count($bfFieldSyncIssues));
$bfFieldSyncMissingTotal = (int) ($auditSummary['bf_view_field_sync_missing_in_cb'] ?? 0);
$bfFieldSyncOrphanTotal = (int) ($auditSummary['bf_view_field_sync_orphan_in_cb'] ?? 0);
$historicalMenuEntriesCount = (int) ($auditSummary['historical_menu_entries'] ?? count($historicalMenuEntries));
$contentRecordDuplicateRowsToRemove = (int) ($auditSummary['content_record_duplicate_rows_to_remove'] ?? 0);
if ($contentRecordDuplicateRowsToRemove === 0 && $contentRecordDuplicateIssues !== []) {
    foreach ($contentRecordDuplicateIssues as $contentRecordDuplicateIssue) {
        if (!is_array($contentRecordDuplicateIssue)) {
            continue;
        }

        $contentRecordDuplicateRowsToRemove += count((array) ($contentRecordDuplicateIssue['duplicate_ids'] ?? []));
    }
}

if (($bfFieldSyncMissingTotal === 0 || $bfFieldSyncOrphanTotal === 0) && $bfFieldSyncIssues !== []) {
    $fallbackMissingTotal = 0;
    $fallbackOrphanTotal = 0;

    foreach ($bfFieldSyncIssues as $bfFieldSyncIssue) {
        if (!is_array($bfFieldSyncIssue)) {
            continue;
        }

        $fallbackMissingTotal += (int) ($bfFieldSyncIssue['missing_count'] ?? 0);
        $fallbackOrphanTotal += (int) ($bfFieldSyncIssue['orphan_count'] ?? 0);
    }

    if ($bfFieldSyncMissingTotal === 0) {
        $bfFieldSyncMissingTotal = $fallbackMissingTotal;
    }

    if ($bfFieldSyncOrphanTotal === 0) {
        $bfFieldSyncOrphanTotal = $fallbackOrphanTotal;
    }
}
$cbTableStats = is_array($auditReport['cb_tables'] ?? null) ? $auditReport['cb_tables'] : [];
$cbTableSummary = (array) ($cbTableStats['summary'] ?? []);
$cbTableDetails = (array) ($cbTableStats['tables'] ?? []);
$cbMissingNgTables = (array) ($cbTableStats['missing_ng_tables'] ?? []);
$auditErrors = (array) ($auditReport['errors'] ?? []);
$auditGeneratedAtDisplay = (string) ($auditReport['generated_at'] ?? Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));

if (!empty($auditReport['generated_at'])) {
    try {
        $userTz = \CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper::getApplication()->getIdentity()->getParam('timezone', '');
        $configTz = \CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper::getApplication()->get('offset', 'UTC');
        $displayTz = new \DateTimeZone($userTz !== '' ? $userTz : $configTz);
        $auditGeneratedAt = new \DateTimeImmutable((string) $auditReport['generated_at'], new \DateTimeZone('UTC'));
        $auditGeneratedAtDisplay = $auditGeneratedAt->setTimezone($displayTz)->format('Y-m-d H:i:s');
    } catch (\Throwable) {
    }
}
$auditWarnings = [];
foreach ($auditErrors as $auditError) {
    $warningText = trim((string) $auditError);

    if ($warningText === '') {
        continue;
    }

    $warningDetail = '';
    $warningLinkUrl = '';
    $warningLinkLabel = '';
    $warningStorageId = 0;
    $warningRepairAvailable = false;
    $storageTableNotFoundMatch = [];
    if (preg_match('/^Storage\s+#(\d+)\s+\((.*?)\)\s*:?\s*(table not found|table name too long)\.?$/i', $warningText, $storageTableNotFoundMatch) === 1) {
        $storageIdLabel = (int) ($storageTableNotFoundMatch[1] ?? 0);
        $warningStorageId = $storageIdLabel;
        $storageNameLabel = trim((string) ($storageTableNotFoundMatch[2] ?? ''));
        $storageIssueType = strtolower(trim((string) ($storageTableNotFoundMatch[3] ?? '')));
        $isMissingTable = $storageIssueType === 'table not found';
        $warningText = Text::sprintf(
            $isMissingTable
                ? 'COM_CONTENTBUILDERNG_ABOUT_AUDIT_WARNING_STORAGE_TABLE_NOT_FOUND'
                : 'COM_CONTENTBUILDERNG_ABOUT_AUDIT_WARNING_STORAGE_TABLE_NAME_TOO_LONG',
            $storageIdLabel,
            $storageNameLabel
        );
        $warningDetail = Text::_(
            $isMissingTable
                ? 'COM_CONTENTBUILDERNG_ABOUT_AUDIT_WARNING_STORAGE_TABLE_NOT_FOUND_DETAIL'
                : 'COM_CONTENTBUILDERNG_ABOUT_AUDIT_WARNING_STORAGE_TABLE_NAME_TOO_LONG_DETAIL'
        );
        $warningRepairAvailable = $isMissingTable;
        if ($storageIdLabel > 0) {
            $warningLinkUrl = Route::_(
                'index.php?option=com_contentbuilderng&view=storage&layout=edit&id=' . $storageIdLabel,
                false
            );
            $warningLinkLabel = Text::sprintf(
                'COM_CONTENTBUILDERNG_ABOUT_AUDIT_WARNING_STORAGE_TABLE_NOT_FOUND_LINK',
                $storageIdLabel
            );
        }
    }

    $auditWarnings[] = [
        'summary' => $warningText,
        'detail' => $warningDetail,
        'link_url' => $warningLinkUrl,
        'link_label' => $warningLinkLabel,
        'storage_id' => $warningStorageId,
        'repair_available' => $warningRepairAvailable,
    ];
}
$hasAuditReport = $auditReport !== [];
$logReport = is_array($this->logReport ?? null) ? $this->logReport : [];
$hasLogReport = $logReport !== [];
$logFileName = (string) ($logReport['file'] ?? Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));
$logSize = (int) ($logReport['size'] ?? 0);
$logLoadedAt = (string) ($logReport['loaded_at'] ?? Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));
$logContent = (string) ($logReport['content'] ?? '');
$logDisplayContent = $logContent;
if ($logDisplayContent !== '') {
    $logLines = preg_split('/\r\n|\r|\n/', $logDisplayContent);
    if (is_array($logLines) && $logLines !== []) {
        $logDisplayContent = implode(PHP_EOL, array_reverse($logLines));
    }
}
$logTruncated = (int) ($logReport['truncated'] ?? 0) === 1;
$logTailBytes = (int) ($logReport['tail_bytes'] ?? 0);
$repairWorkflow = is_array($this->repairWorkflow ?? null) ? $this->repairWorkflow : [];
$repairWorkflowSteps = array_values((array) ($repairWorkflow['steps'] ?? []));
$repairWorkflowCurrentIndex = (int) ($repairWorkflow['current_step'] ?? 0);
$repairWorkflowCurrentStep = $repairWorkflowSteps[$repairWorkflowCurrentIndex] ?? null;
$repairWorkflowCurrentStepId = is_array($repairWorkflowCurrentStep) ? (string) ($repairWorkflowCurrentStep['id'] ?? '') : '';
$repairWorkflowCurrentStatus = is_array($repairWorkflowCurrentStep) ? (string) ($repairWorkflowCurrentStep['status'] ?? 'pending') : 'pending';
$repairWorkflowCurrentResult = is_array($repairWorkflowCurrentStep) ? (array) ($repairWorkflowCurrentStep['result'] ?? []) : [];
$repairWorkflowRequested = \CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper::getApplication()->getInput()->getInt('repair_workflow', 0) === 1;
$repairWorkflowIsActive = $repairWorkflowRequested && !empty($repairWorkflow) && (bool) ($repairWorkflow['active'] ?? false);
$repairWorkflowIsCompleted = (bool) ($repairWorkflow['completed'] ?? false);
$repairWorkflowHasNext = $repairWorkflowIsActive
    && !$repairWorkflowIsCompleted
    && $repairWorkflowCurrentIndex < count($repairWorkflowSteps) - 1;
$repairWorkflowDisplayCurrentIndex = $repairWorkflowCurrentIndex;
if ($repairWorkflowCurrentStatus !== 'pending' && $repairWorkflowHasNext) {
    $repairWorkflowDisplayCurrentIndex++;
}
$repairWorkflowShowCurrentPanel = true;
if (
    $repairWorkflowIsCompleted
    && is_array($repairWorkflowCurrentStep)
    && in_array($repairWorkflowCurrentStatus, ['done', 'skipped', 'not_required'], true)
) {
    $currentResultLines = (array) ($repairWorkflowCurrentResult['lines'] ?? []);
    $currentResultSummary = trim((string) ($repairWorkflowCurrentResult['summary'] ?? ''));

    if ($currentResultLines === [] || $currentResultSummary !== '') {
        $repairWorkflowShowCurrentPanel = false;
    }
}
$repairWorkflowStepLabels = [
    'duplicate_indexes' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_DUPLICATE_GROUPS'),
    'historical_tables' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_HISTORICAL_TABLES'),
    'historical_menu_entries' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_HISTORICAL_MENU_ENTRIES'),
    'table_encoding' => Text::sprintf(
        'COM_CONTENTBUILDERNG_DB_REPAIR_STEP_TABLE_ENCODING_TITLE_WITH_TARGET',
        $encodingTargetCollation
    )
        . ' / '
        . Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_COLUMN_ENCODING_ISSUES')
        . ' / '
        . Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_MIXED_COLLATIONS'),
    'packed_data' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_PACKED_DATA_TITLE'),
    'audit_columns' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_MISSING_AUDIT_COLUMNS'),
    'form_audit_columns' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_MISSING_FORM_AUDIT_COLUMNS'),
    'plugin_duplicates' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_PLUGIN_DUPLICATES'),
    'bf_field_sync' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_BF_FIELD_SYNC'),
    'menu_view_consistency' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_MENU_VIEW_CONSISTENCY'),
    'frontend_permission_consistency' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_FRONTEND_PERMISSION_CONSISTENCY'),
    'element_reference_consistency' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_ELEMENT_REFERENCE_CONSISTENCY'),
    'content_record_duplicates' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_CONTENT_RECORD_DUPLICATES'),
    'generated_article_categories' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_GENERATED_ARTICLE_CATEGORIES'),
    'debug_mode' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_DEBUG_MODE'),
    'stale_language_files' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_STALE_LANGUAGE_FILES'),
    'stale_installer_temp' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_STALE_INSTALLER_TEMP'),
];
$repairWorkflowStepDescriptions = [
    'duplicate_indexes' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_DUPLICATE_GROUPS'),
    'historical_tables' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_HISTORICAL_TABLES'),
    'table_encoding' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_TABLE_ENCODING_DESC'),
    'packed_data' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_PACKED_DATA_DESC'),
    'audit_columns' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_AUDIT_COLUMNS_DESC'),
    'form_audit_columns' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_FORM_AUDIT_COLUMNS_DESC'),
    'plugin_duplicates' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_PLUGIN_DUPLICATES_DESC'),
    'historical_menu_entries' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_HISTORICAL_MENU_DESC'),
    'bf_field_sync' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_BF_FIELD_SYNC'),
    'menu_view_consistency' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_MENU_VIEW_CONSISTENCY'),
    'frontend_permission_consistency' => Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_FRONTEND_PERMISSION_CONSISTENCY'),
    'element_reference_consistency' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_ELEMENT_REFERENCE_DESC'),
    'content_record_duplicates' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_CONTENT_RECORD_DUPLICATES_DESC'),
    'generated_article_categories' => Text::_('COM_CONTENTBUILDERNG_DB_REPAIR_STEP_GENERATED_ARTICLE_CATEGORIES_DESC'),
];
$phpLibrariesCount = count((array) $this->phpLibraries);
$javascriptLibrariesCount = count((array) $this->javascriptLibraries);
$pluginsCount = count((array) $this->plugins);
$columnEncodingIssueLimit = 200;
$tableEncodingIssueCount = count($tableEncodingIssues);
$columnEncodingIssueCount = count($columnEncodingIssues);
$mixedCollationIssueCount = max(0, count($mixedTableCollations) - 1);
$columnEncodingIssuesDisplayed = array_slice($columnEncodingIssues, 0, $columnEncodingIssueLimit);
$columnEncodingIssueHiddenCount = max(0, count($columnEncodingIssues) - count($columnEncodingIssuesDisplayed));
$hasAuditIssues = (int) ($auditSummary['issues_total'] ?? 0) > 0;
$hasDuplicateIndexIssues = !empty($duplicateIndexes);
$hasDuplicateIndexDropIssues = (int) ($auditSummary['duplicate_indexes_to_drop'] ?? 0) > 0;
$hasLegacyTableIssues = !empty($historicalTables);
$hasLegacyMenuIssues = $historicalMenuEntriesCount > 0;
$hasTableEncodingIssues = $tableEncodingIssueCount > 0;
$hasPackedDataIssues = (int) ($auditSummary['packed_data_candidates'] ?? 0) > 0;
$hasColumnEncodingIssues = $columnEncodingIssueCount > 0;
$hasMixedCollationIssues = $mixedCollationIssueCount > 0;
$hasMissingAuditColumnIssues = $missingAuditColumnsTableCount > 0 || $missingAuditColumnsTotal > 0;
$hasMissingFormAuditColumnIssues = $missingFormAuditColumnsTableCount > 0 || $missingFormAuditColumnsTotal > 0;
$hasPluginDuplicateIssues = $pluginDuplicateGroups > 0 || $pluginDuplicateRowsToRemove > 0;
$hasBfFieldSyncIssues = $bfFieldSyncViews > 0 || $bfFieldSyncMissingTotal > 0 || $bfFieldSyncOrphanTotal > 0;
$hasMenuViewIssues = (int) ($auditSummary['menu_view_issues'] ?? count($menuViewIssues)) > 0;
$hasFrontendPermissionIssues = (int) ($auditSummary['frontend_permission_issues'] ?? count($frontendPermissionIssues)) > 0;
$hasElementReferenceIssues = (int) ($auditSummary['element_reference_issues'] ?? count($elementReferenceIssues)) > 0;
$hasContentRecordDuplicateIssues = (int) ($auditSummary['content_record_duplicate_issues'] ?? count($contentRecordDuplicateIssues)) > 0;
$generatedArticleCategoryIssueCount = (int) ($auditSummary['generated_article_category_issues'] ?? count($generatedArticleCategoryIssues));
$generatedArticleCategoryRowCount = (int) ($auditSummary['generated_article_category_rows'] ?? 0);
if ($generatedArticleCategoryRowCount === 0 && $generatedArticleCategoryIssues !== []) {
    foreach ($generatedArticleCategoryIssues as $generatedArticleCategoryIssue) {
        if (!is_array($generatedArticleCategoryIssue)) {
            continue;
        }

        $generatedArticleCategoryRowCount += (int) ($generatedArticleCategoryIssue['invalid_article_count'] ?? 0);
    }
}
$hasGeneratedArticleCategoryIssues = $generatedArticleCategoryIssueCount > 0 || $generatedArticleCategoryRowCount > 0;
$debugModeIssueCount = (int) ($auditSummary['debug_mode_issues'] ?? count($debugModeIssues));
$hasDebugModeIssues = $debugModeIssueCount > 0;
$formAuditIssueForms = (int) ($auditSummary['form_audit_issue_forms'] ?? 0);
$formAuditIssueChecks = (int) ($auditSummary['form_audit_issue_checks'] ?? 0);
$hasFormAuditIssues = $formAuditIssueForms > 0 || $formAuditIssueChecks > 0;
$invalidDatetimeSortIssueCount = (int) ($auditSummary['invalid_datetime_sort_issues'] ?? count($invalidDatetimeSortIssues));
$invalidDatetimeSortRowCount = (int) ($auditSummary['invalid_datetime_sort_rows'] ?? 0);
if ($invalidDatetimeSortRowCount === 0 && $invalidDatetimeSortIssues !== []) {
    foreach ($invalidDatetimeSortIssues as $invalidDatetimeSortIssue) {
        if (!is_array($invalidDatetimeSortIssue)) {
            continue;
        }

        $invalidDatetimeSortRowCount += (int) ($invalidDatetimeSortIssue['invalid_count'] ?? 0);
    }
}
$hasInvalidDatetimeSortIssues = $invalidDatetimeSortIssueCount > 0 || $invalidDatetimeSortRowCount > 0;
$storageColumnTypeIssueCount = (int) ($auditSummary['storage_column_type_issues'] ?? count($storageColumnTypeIssues));
$hasStorageColumnTypeIssues = $storageColumnTypeIssueCount > 0;
$formatBytes = static function (int $bytes): string {
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = (float) $bytes;
    $unitIndex = 0;

    while ($value >= 1024 && $unitIndex < count($units) - 1) {
        $value /= 1024;
        $unitIndex++;
    }

    if ($unitIndex === 0) {
        return number_format((int) round($value), 0, '.', ' ') . ' ' . $units[$unitIndex];
    }

    return number_format($value, 2, '.', ' ') . ' ' . $units[$unitIndex];
};
$formatAuditIssueList = static function (array $values, int $limit = 8): string {
    $clean = [];

    foreach ($values as $value) {
        $label = trim((string) $value);
        if ($label !== '') {
            $clean[] = $label;
        }
    }

    $clean = array_values(array_unique($clean));
    if ($clean === []) {
        return '0';
    }

    $visible = array_slice($clean, 0, $limit);
    $remaining = max(0, count($clean) - count($visible));
    $rendered = implode(', ', $visible);

    if ($remaining > 0) {
        $rendered .= ' +' . $remaining;
    }

    return $rendered;
};
$renderAuditTitle = static function (string $label, bool $hasIssues): string {
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

    if ($hasIssues) {
        return '<span class="cb-audit-section-title"><span class="cb-audit-warning-icon icon-warning" aria-hidden="true"></span><span>' . $safeLabel . '</span></span>';
    }

    return $safeLabel;
};
// The number shown in front of a section title is the row number of that
// section in the summary table above it. Both are assigned here from the order
// the summary rows are actually rendered, so the column stays sequential and
// the titles stay in sync when rows are added, moved or removed.
$auditSectionNumberStore = [];
$auditSectionNumberAliases = [
    // Sections that have no summary row of their own reuse the number of the
    // first summary row that belongs to them.
    'cb_table_stats' => 'cb_tables_total',
];
$auditSummaryRowNumber = static function (string $sectionId) use (&$auditSectionNumberStore): int {
    if (!isset($auditSectionNumberStore[$sectionId])) {
        $auditSectionNumberStore[$sectionId] = count($auditSectionNumberStore) + 1;
    }

    return $auditSectionNumberStore[$sectionId];
};
$getAuditSectionNumber = static function (string $sectionId) use (&$auditSectionNumberStore, $auditSectionNumberAliases): int {
    $sectionId = $auditSectionNumberAliases[$sectionId] ?? $sectionId;

    return (int) ($auditSectionNumberStore[$sectionId] ?? 0);
};
$getAuditSectionHeadingId = static function (string $sectionId): string {
    return 'cb-audit-section-' . $sectionId;
};
$renderAuditSummaryLink = static function (string $sectionId, string $label) use ($getAuditSectionHeadingId): string {
    $anchorId = htmlspecialchars($getAuditSectionHeadingId($sectionId), ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

    return '<a class="text-reset text-decoration-none" href="#' . $anchorId . '">' . $safeLabel . '</a>';
};
$renderNumberedAuditTitle = static function (string $sectionId, string $label, bool $hasIssues) use ($renderAuditTitle, $getAuditSectionNumber): string {
    $number = $getAuditSectionNumber($sectionId);

    return $renderAuditTitle(($number > 0 ? $number . '. ' : '') . $label, $hasIssues);
};

?>
<form
    action="<?php echo Route::_('index.php?option=com_contentbuilderng&view=about'); ?>"
    method="post"
    enctype="multipart/form-data"
    name="adminForm"
    id="adminForm"
>
<div class="cb-about-intro mt-3 mb-3">
    <div class="cb-about-intro-media">
        <img
            src="<?php echo htmlspecialchars(Uri::root(true) . '/media/com_contentbuilderng/images/piranha_50x500_blanc.png', ENT_QUOTES, 'UTF-8'); ?>"
            alt="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_PIRANHA_IMAGE_ALT'), ENT_QUOTES, 'UTF-8'); ?>"
            class="img-fluid"
            style="max-width: 140px; height: auto;"
            loading="lazy"
        />
    </div>
    <div class="cb-about-intro-content">
        <p class="mb-0">
            <?php echo Text::_('COM_CONTENTBUILDERNG_ABOUT_DESC'); ?>
        </p>
        <div class="cb-about-intro-links">
            <a
                class="cb-about-intro-link cb-about-intro-link--vcmb"
                href="https://breezingforms-ng.vcmb.fr"
                target="_blank"
                rel="noopener noreferrer"
                title="<?php echo htmlspecialchars($tooltipLinkVcmb, ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="<?php echo htmlspecialchars($tooltipLinkVcmb, ENT_QUOTES, 'UTF-8'); ?>"
            ><?php echo Text::_('COM_CONTENTBUILDERNG_BREEZINGFORMS_NG_LINK'); ?></a>
            <iframe
                src="https://ghbtns.com/github-btn.html?user=vcmb-cyclo&amp;repo=com_contentbuilderng&amp;type=star&amp;count=true&amp;size=large"
                frameborder="0"
                scrolling="0"
                width="125"
                height="30"
                title="GitHub Stars"
                style="display:block;align-self:center;"
            ></iframe>
            <a
                class="cb-about-intro-link cb-about-intro-link--license"
                href="<?php echo htmlspecialchars($licenseUrl, ENT_QUOTES, 'UTF-8'); ?>"
                target="_blank"
                rel="noopener noreferrer"
                title="<?php echo htmlspecialchars($tooltipLinkLicense, ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="<?php echo htmlspecialchars($tooltipLinkLicense, ENT_QUOTES, 'UTF-8'); ?>"
            ><?php echo Text::_('COM_CONTENTBUILDERNG_LICENSE_LINK'); ?></a>
            <a
                class="cb-about-intro-link cb-about-intro-link--openapi"
                href="<?php echo htmlspecialchars($openApiSpecUrl, ENT_QUOTES, 'UTF-8'); ?>"
                target="_blank"
                rel="noopener noreferrer"
                title="<?php echo htmlspecialchars($tooltipLinkOpenApi, ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="<?php echo htmlspecialchars($tooltipLinkOpenApi, ENT_QUOTES, 'UTF-8'); ?>"
            ><?php echo Text::_('COM_CONTENTBUILDERNG_ABOUT_OPENAPI_LINK'); ?></a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/version_summary.php'; ?>

<?php require __DIR__ . '/audit_report.php'; ?>

<?php require __DIR__ . '/log.php'; ?>

<?php require __DIR__ . '/repair_workflow.php'; ?>

<?php require __DIR__ . '/php_libraries.php'; ?>

<?php require __DIR__ . '/js_libraries.php'; ?>

<?php require __DIR__ . '/installed_plugins.php'; ?>
    <input type="hidden" name="option" value="com_contentbuilderng">
    <input type="hidden" name="repair_step" id="repair_step" value="">
    <input type="hidden" name="repair_action" id="repair_action" value="">
    <input type="hidden" name="repair_storage_id" id="repair_storage_id" value="">
    <input type="hidden" name="stale_installer_temp_path" id="stale_installer_temp_path" value="">
    <input type="hidden" name="task" id="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
