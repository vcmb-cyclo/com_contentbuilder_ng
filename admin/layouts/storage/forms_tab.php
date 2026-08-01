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
use Joomla\CMS\Router\Route;

/** @var array<int,object{id:int,name:string,title:string}> $usingForms */
$usingFormsData = $displayData['usingForms'] ?? null;
$usingForms = is_array($usingFormsData) ? $usingFormsData : [];
?>
<div class="m-3">
    <ul class="list-unstyled mb-0">
        <?php foreach ($usingForms as $usingForm) :
            $usingFormId = (int) ($usingForm->id ?? 0);
            $usingFormLabel = trim((string) ($usingForm->title ?? ''));
            if ($usingFormLabel === '') {
                $usingFormLabel = trim((string) ($usingForm->name ?? ''));
            }
            if ($usingFormLabel === '') {
                $usingFormLabel = '#' . $usingFormId;
            }
            ?>
            <li class="mb-1">
                <a href="<?php echo htmlspecialchars(Route::_('index.php?option=com_contentbuilderng&view=form&layout=edit&id=' . $usingFormId, false), ENT_QUOTES, 'UTF-8'); ?>"
                    target="_blank"
                    title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_USING_FORMS_LINK_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="fa-solid fa-file-lines me-1" aria-hidden="true"></span>
                    <?php echo htmlspecialchars($usingFormLabel, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
