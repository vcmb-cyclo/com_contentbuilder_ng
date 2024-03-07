<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL 
 * @license     GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<style type="text/css">
    label {
        display: inline;
    }
</style>

<div style="float:right" class="mb-3">
    <input type="button" class="btn btn-sm btn-primary w-25" value="<?php echo Text::_('COM_CONTENTBUILDER_SAVE'); ?>"
        onclick="document.adminForm.task.value='save';document.adminForm.submit();" />
    <input type="button" class="btn btn-sm btn-primary w-25"
        value="<?php echo Text::_('COM_CONTENTBUILDER_CANCEL'); ?>"
        onclick="document.adminForm.task.value='cancel';document.adminForm.submit();" />
</div>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div style="clear:both;"></div>

    <div class="col100">
        <table class="adminlist table table-striped">
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <?php echo Text::_('COM_CONTENTBUILDER_ID'); ?>
                </td>
                <td>
                    <?php echo htmlentities($this->subject->id, ENT_QUOTES, 'UTF-8'); ?>
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <?php echo Text::_('COM_CONTENTBUILDER_NAME'); ?>
                </td>
                <td>
                    <?php echo htmlentities($this->subject->name, ENT_QUOTES, 'UTF-8'); ?>
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <?php echo Text::_('COM_CONTENTBUILDER_USERNAME'); ?>
                </td>
                <td>
                    <?php echo htmlentities($this->subject->username, ENT_QUOTES, 'UTF-8'); ?>
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="limit_add">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIMIT_ADD'); ?>:
                    </label>
                </td>
                <td>
                    <input class="form-control form-control-sm w-25" id="limit_add" name="limit_add" type="text"
                        value="<?php echo $this->subject->limit_add; ?>" />
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="limit_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIMIT_EDIT'); ?>:
                    </label>
                </td>
                <td>
                    <input class="form-control form-control-sm w-25" id="limit_edit" name="limit_edit" type="text"
                        value="<?php echo $this->subject->limit_edit; ?>" />
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="verification_date_view">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DATE_VIEW'); ?>:
                    </label>
                </td>
                <td>
                    <?php
                    $calAttr = [
                        'class' => 'verification_date_view',
                        'showTime' => true,
                        'timeFormat' => '24',
                        'singleHeader' => false,
                        'todayBtn' => true,
                        'weekNumbers' => true,
                        'minYear' => '',
                        'maxYear' => '',
                        'firstDay' => '1',
                    ];

                    echo HTMLHelper::_('calendar', $this->subject->verification_date_view, 'verification_date_view', 'verification_date_view', '%Y-%m-%d %H:%M:00', $calAttr);

                    ?>
                    <input class="form-check-input" type="checkbox" id="verified_view" name="verified_view" value="1"
                        <?php echo $this->subject->verified_view ? ' checked="checked"' : ''; ?> /> <label
                        for="verified_view">
                        <?php echo Text::_('COM_CONTENTBUILDER_VERIFIED_VIEW'); ?>
                    </label>

                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="verification_date_new">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DATE_NEW'); ?>:
                    </label>
                </td>
                <td>

                    <?php
                    $calAttr = [
                        'class' => 'verification_date_new',
                        'showTime' => true,
                        'timeFormat' => '24',
                        'singleHeader' => false,
                        'todayBtn' => true,
                        'weekNumbers' => true,
                        'minYear' => '',
                        'maxYear' => '',
                        'firstDay' => '1',
                    ];

                    echo HTMLHelper::_('calendar', $this->subject->verification_date_new, 'verification_date_new', 'verification_date_new', '%Y-%m-%d %H:%M:00', $calAttr);
                    ?>

                    <input class="form-check-input" type="checkbox" id="verified_new" name="verified_new" value="1"
                        <?php echo $this->subject->verified_new ? ' checked="checked"' : ''; ?> /> <label
                        for="verified_new">
                        <?php echo Text::_('COM_CONTENTBUILDER_VERIFIED_NEW'); ?>
                    </label>

                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="verification_date_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DATE_EDIT'); ?>:
                    </label>
                </td>
                <td>

                    <?php
                    $calAttr = [
                        'class' => 'verification_date_edit',
                        'showTime' => true,
                        'timeFormat' => '24',
                        'singleHeader' => false,
                        'todayBtn' => true,
                        'weekNumbers' => true,
                        'minYear' => '',
                        'maxYear' => '',
                        'firstDay' => '1',
                    ];

                    echo HTMLHelper::_('calendar', $this->subject->verification_date_edit, 'verification_date_edit', 'verification_date_edit', '%Y-%m-%d %H:%M:00', $calAttr);
                    ?>

                    <input class="form-check-input" type="checkbox" id="verified_edit" name="verified_edit" value="1"
                        <?php echo $this->subject->verified_edit ? ' checked="checked"' : ''; ?> /> <label
                        for="verified_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_VERIFIED_EDIT'); ?>
                    </label>

                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="published">
                        <?php echo Text::_('PUBLISHED'); ?>
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" id="published" name="published" value="1" <?php echo $this->subject->published ? ' checked="checked"' : ''; ?> />

                </td>
            </tr>
        </table>
    </div>


    <input type="hidden" name="option" value="com_contentbuilder" />
    <input type="hidden" name="task" id="task" value="" />
    <input type="hidden" name="form_id" value="<?php echo CBRequest::getInt('form_id', 0); ?>" />
    <input type="hidden" name="joomla_userid" value="<?php echo $this->subject->id; ?>" />
    <input type="hidden" name="cb_id" value="<?php echo $this->subject->cb_id; ?>" />
    <input type="hidden" name="tmpl" value="<?php echo CBRequest::getCmd('tmpl', ''); ?>" />
    <input type="hidden" name="controller" value="users" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>