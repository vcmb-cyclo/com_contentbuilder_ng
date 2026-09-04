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

use Joomla\CMS\Language\Text;

$issue = trim((string) ($displayData['issue'] ?? ''));
$description = trim((string) ($displayData['description'] ?? ''));

if ($issue === '') {
    return;
}
?>
<div class="d-flex justify-content-end mb-2">
    <button
        type="button"
        class="btn btn-sm btn-warning"
        data-cb-audit-ajax-task="about.repairAuditIssue"
        data-cb-audit-ajax-field="repair_issue"
        data-cb-audit-ajax-value="<?php echo htmlspecialchars($issue, ENT_QUOTES, 'UTF-8'); ?>"
        title="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>"
        aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_REPAIR'), ENT_QUOTES, 'UTF-8'); ?>"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
    ><span class="fa-solid fa-wrench me-1" aria-hidden="true"></span><?php echo Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_REPAIR'); ?></button>
</div>
