<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/



// No direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');


use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$versionValue = (string) ($this->componentVersion ?: Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE'));
$creationDateValue = (string) ($this->componentCreationDate ?: Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE'));
$authorValue = (string) ($this->componentAuthor ?: Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE'));
$auditReport = is_array($this->auditReport ?? null) ? $this->auditReport : [];
$auditSummary = (array) ($auditReport['summary'] ?? []);
$duplicateIndexes = (array) ($auditReport['duplicate_indexes'] ?? []);
$legacyTables = (array) ($auditReport['legacy_tables'] ?? []);
$tableEncodingIssues = (array) ($auditReport['table_encoding_issues'] ?? []);
$columnEncodingIssues = (array) ($auditReport['column_encoding_issues'] ?? []);
$mixedTableCollations = (array) ($auditReport['mixed_table_collations'] ?? []);
$auditErrors = (array) ($auditReport['errors'] ?? []);
$hasAuditReport = $auditReport !== [];
$columnEncodingIssueLimit = 200;
$columnEncodingIssuesDisplayed = array_slice($columnEncodingIssues, 0, $columnEncodingIssueLimit);
$columnEncodingIssueHiddenCount = max(0, count($columnEncodingIssues) - count($columnEncodingIssuesDisplayed));

?>
<style>
    .cb-about-intro {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .cb-about-intro-media {
        flex: 0 0 auto;
    }
    .cb-about-intro-content {
        flex: 1 1 auto;
        min-width: 0;
    }
    .cb-about-intro-content p {
        margin: 0;
        padding: 0;
        text-align: left;
    }
    @media (max-width: 767.98px) {
        .cb-about-intro {
            flex-wrap: wrap;
        }
    }
    .cb-about-version-card {
        background:
            radial-gradient(circle at 100% 0%, rgba(13, 110, 253, .10), transparent 48%),
            radial-gradient(circle at 0% 100%, rgba(25, 135, 84, .09), transparent 44%),
            linear-gradient(140deg, #f8fafc 0%, #ffffff 72%);
        border: 1px solid #dbe4ee;
        border-radius: 1rem;
        overflow: hidden;
    }
    .cb-about-version-header {
        border-bottom: 1px dashed #d2dbe6;
        padding-bottom: .75rem;
    }
    .cb-about-version-title {
        color: #172b4d;
        font-weight: 700;
        letter-spacing: .01em;
    }
    .cb-about-version-badge {
        background-color: #172b4d;
        color: #ffffff;
        border-radius: 999px;
        font-size: .72rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: .35rem .65rem;
    }
    .cb-about-version-tile {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: .35rem;
        height: 100%;
        border: 1px solid #dce3eb;
        border-radius: .9rem;
        background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
        padding: 1.05rem 1.05rem .95rem;
        box-shadow: 0 .5rem 1rem rgba(15, 23, 42, .06);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .cb-about-version-tile::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: .23rem;
        border-radius: .9rem .9rem 0 0;
        background: var(--cb-accent-color, #0d6efd);
    }
    .cb-about-version-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 .65rem 1.25rem rgba(15, 23, 42, .1);
    }
    .cb-about-version-tile--version {
        --cb-accent-color: #0d6efd;
    }
    .cb-about-version-tile--date {
        --cb-accent-color: #198754;
    }
    .cb-about-version-tile--author {
        --cb-accent-color: #fd7e14;
    }
    .cb-about-version-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        background-color: #e8f1ff;
        color: #0d6efd;
    }
    .cb-about-version-tile--date .cb-about-version-icon {
        background-color: #e7f6ed;
        color: #198754;
    }
    .cb-about-version-tile--author .cb-about-version-icon {
        background-color: #fff1e8;
        color: #fd7e14;
    }
    .cb-about-version-label {
        margin: .15rem 0 0;
        color: #6c757d;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .cb-about-version-value {
        margin: 0;
        color: #1b2a41;
        font-size: 1.22rem;
        font-weight: 700;
        line-height: 1.25;
        word-break: break-word;
    }
</style>
<form
    action="<?php echo Route::_('index.php?option=com_contentbuilder_ng&view=about'); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
>
<div class="cb-about-intro mt-3 mb-3">
    <div class="cb-about-intro-media">
        <img
            src="<?php echo htmlspecialchars(Uri::root(true) . '/media/com_contentbuilder_ng/images/piranha_50x500_blanc.png', ENT_QUOTES, 'UTF-8'); ?>"
            alt="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDER_NG_PIRANHA_IMAGE_ALT'), ENT_QUOTES, 'UTF-8'); ?>"
            class="img-fluid"
            style="max-width: 140px; height: auto;"
            loading="lazy"
        />
    </div>
    <div class="cb-about-intro-content">
        <p class="mb-0">
            <?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_DESC'); ?>
            <a href="https://breezingforms.vcmb.fr" target="_blank" rel="noopener noreferrer">VCMB migration</a>
        </p>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h3 class="h6 card-title mb-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_TITLE'); ?></h3>

        <?php if (!$hasAuditReport) : ?>
            <div class="alert alert-info mb-0">
                <?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_EMPTY'); ?>
            </div>
        <?php else : ?>
            <p class="text-muted small mb-2">
                <?php echo Text::sprintf(
                    'COM_CONTENTBUILDER_NG_ABOUT_AUDIT_LAST_RUN',
                    (string) ($auditReport['generated_at'] ?? Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE')),
                    (int) ($auditReport['scanned_tables'] ?? 0)
                ); ?>
            </p>

            <div class="table-responsive mb-3">
                <table class="table table-sm table-striped align-middle mb-0">
                    <tbody>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_ISSUES_TOTAL'); ?></th>
                        <td><?php echo (int) ($auditSummary['issues_total'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_DUPLICATE_GROUPS'); ?></th>
                        <td><?php echo (int) ($auditSummary['duplicate_index_groups'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_DUPLICATE_TO_DROP'); ?></th>
                        <td><?php echo (int) ($auditSummary['duplicate_indexes_to_drop'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_LEGACY_TABLES'); ?></th>
                        <td><?php echo (int) ($auditSummary['legacy_tables'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_TABLE_ENCODING_ISSUES'); ?></th>
                        <td><?php echo (int) ($auditSummary['table_encoding_issues'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_COLUMN_ENCODING_ISSUES'); ?></th>
                        <td><?php echo (int) ($auditSummary['column_encoding_issues'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_MIXED_COLLATIONS'); ?></th>
                        <td><?php echo max(0, count($mixedTableCollations) - 1); ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <?php if ((int) ($auditSummary['issues_total'] ?? 0) === 0 && empty($auditErrors)) : ?>
                <div class="alert alert-success mb-3">
                    <?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_NO_ISSUES'); ?>
                </div>
            <?php endif; ?>

            <h4 class="h6 mt-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_DUPLICATE_GROUPS'); ?></h4>
            <?php if (empty($duplicateIndexes)) : ?>
                <div class="alert alert-info">
                    <?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_NO_DUPLICATE_INDEXES'); ?>
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_TABLE'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_INDEX_KEEP'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_INDEX_DROP'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_INDEXES'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($duplicateIndexes as $duplicateIndex) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($duplicateIndex['table'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) ($duplicateIndex['keep'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(implode(', ', (array) ($duplicateIndex['drop'] ?? [])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(implode(', ', (array) ($duplicateIndex['indexes'] ?? [])), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h4 class="h6 mt-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_LEGACY_TABLES'); ?></h4>
            <?php if (empty($legacyTables)) : ?>
                <div class="alert alert-info">
                    <?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_NO_LEGACY_TABLES'); ?>
                </div>
            <?php else : ?>
                <ul class="mb-0">
                    <?php foreach ($legacyTables as $legacyTable) : ?>
                        <li><?php echo htmlspecialchars((string) $legacyTable, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h4 class="h6 mt-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_TABLE_ENCODING_ISSUES'); ?></h4>
            <?php if (empty($tableEncodingIssues)) : ?>
                <div class="alert alert-info">
                    <?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_NO_TABLE_ENCODING_ISSUES'); ?>
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_TABLE'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_COLLATION'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_EXPECTED'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tableEncodingIssues as $tableIssue) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($tableIssue['table'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) (($tableIssue['collation'] ?? '') !== '' ? $tableIssue['collation'] : Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE')), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) ($tableIssue['expected'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h4 class="h6 mt-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_COLUMN_ENCODING_ISSUES'); ?></h4>
            <?php if (empty($columnEncodingIssuesDisplayed)) : ?>
                <div class="alert alert-info">
                    <?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_NO_COLUMN_ENCODING_ISSUES'); ?>
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_TABLE'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_COLUMN'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_CHARSET'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_COLLATION'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($columnEncodingIssuesDisplayed as $columnIssue) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($columnIssue['table'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) ($columnIssue['column'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) (($columnIssue['charset'] ?? '') !== '' ? $columnIssue['charset'] : Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE')), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) (($columnIssue['collation'] ?? '') !== '' ? $columnIssue['collation'] : Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE')), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($columnEncodingIssueHiddenCount > 0) : ?>
                    <p class="text-muted small mb-0">
                        <?php echo Text::sprintf('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_TRUNCATED', $columnEncodingIssueHiddenCount); ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>

            <h4 class="h6 mt-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_MIXED_COLLATIONS'); ?></h4>
            <?php if (count($mixedTableCollations) <= 1) : ?>
                <div class="alert alert-info">
                    <?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_NO_MIXED_COLLATIONS'); ?>
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_COLLATION'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_COUNT'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_TABLE'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($mixedTableCollations as $collationStat) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($collationStat['collation'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) ($collationStat['count'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars(implode(', ', (array) ($collationStat['tables'] ?? [])), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if (!empty($auditErrors)) : ?>
                <h4 class="h6 mt-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_ABOUT_AUDIT_ERRORS'); ?></h4>
                <ul class="mb-0">
                    <?php foreach ($auditErrors as $auditError) : ?>
                        <li><?php echo htmlspecialchars((string) $auditError, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3 cb-about-version-card">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 cb-about-version-header">
            <h3 class="h5 mb-0 cb-about-version-title"><?php echo Text::_('COM_CONTENTBUILDER_NG_VERSION_INFORMATION'); ?></h3>
            <span class="cb-about-version-badge">ContentBuilder NG</span>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="cb-about-version-tile cb-about-version-tile--version">
                    <span class="cb-about-version-icon" aria-hidden="true">VER</span>
                    <p class="cb-about-version-label"><?php echo Text::_('COM_CONTENTBUILDER_NG_VERSION_LABEL'); ?></p>
                    <p class="cb-about-version-value"><?php echo htmlspecialchars($versionValue, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="cb-about-version-tile cb-about-version-tile--date">
                    <span class="cb-about-version-icon" aria-hidden="true">DATE</span>
                    <p class="cb-about-version-label"><?php echo Text::_('COM_CONTENTBUILDER_NG_CREATION_DATE_LABEL'); ?></p>
                    <p class="cb-about-version-value"><?php echo htmlspecialchars($creationDateValue, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="cb-about-version-tile cb-about-version-tile--author">
                    <span class="cb-about-version-icon" aria-hidden="true">DEV</span>
                    <p class="cb-about-version-label"><?php echo Text::_('COM_CONTENTBUILDER_NG_AUTHOR_LABEL'); ?></p>
                    <p class="cb-about-version-value"><?php echo htmlspecialchars($authorValue, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h3 class="h6 card-title mb-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_PHP_LIBRARIES'); ?></h3>

        <?php if (empty($this->phpLibraries)) : ?>
            <div class="alert alert-info mb-0">
                <?php echo Text::_('COM_CONTENTBUILDER_NG_PHP_LIBRARIES_NOT_AVAILABLE'); ?>
            </div>
        <?php else : ?>
            <p class="text-muted small">
                <?php echo Text::sprintf('COM_CONTENTBUILDER_NG_PHP_LIBRARIES_COUNT', count($this->phpLibraries)); ?>
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_PHP_LIBRARY'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_PHP_LIBRARY_VERSION'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_PHP_LIBRARY_SCOPE'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->phpLibraries as $library) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string) $library['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) ($library['version'] ?: Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE')), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php echo Text::_(!empty($library['is_dev']) ? 'COM_CONTENTBUILDER_NG_PHP_LIBRARY_SCOPE_DEV' : 'COM_CONTENTBUILDER_NG_PHP_LIBRARY_SCOPE_RUNTIME'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="card mt-3">
    <div class="card-body">
        <h3 class="h6 card-title mb-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_JS_LIBRARIES'); ?></h3>

        <?php if (empty($this->javascriptLibraries)) : ?>
            <div class="alert alert-info mb-0">
                <?php echo Text::_('COM_CONTENTBUILDER_NG_JS_LIBRARIES_NOT_AVAILABLE'); ?>
            </div>
        <?php else : ?>
            <p class="text-muted small">
                <?php echo Text::sprintf('COM_CONTENTBUILDER_NG_JS_LIBRARIES_COUNT', count($this->javascriptLibraries)); ?>
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_JS_LIBRARY'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_JS_LIBRARY_VERSION'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_JS_LIBRARY_ASSETS'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_CONTENTBUILDER_NG_JS_LIBRARY_SOURCE'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->javascriptLibraries as $library) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string) ($library['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) ($library['version'] ?? Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE')), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) ($library['assets'] ?? Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE')), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) ($library['source'] ?? Text::_('COM_CONTENTBUILDER_NG_NOT_AVAILABLE')), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
    <input type="hidden" name="option" value="com_contentbuilder_ng">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
