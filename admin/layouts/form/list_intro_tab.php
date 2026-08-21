<?php

/**
 * @package     ContentBuilderNG
 * @author      Xavier DANO
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$form = $displayData['form'] ?? null;

if (!$form) {
    return;
}
?>
<h3 id="cb-form-list-intro-text" class="mb-3">
    <?php echo Text::_('COM_CONTENTBUILDERNG_LIST_INTRO_MODE_TITLE'); ?>
</h3>
<p class="text-muted mb-3">
    <?php echo Text::_('COM_CONTENTBUILDERNG_LIST_INTRO_MODE_INTRO'); ?>
</p>
<?php echo $form->renderField('intro_text'); ?>
