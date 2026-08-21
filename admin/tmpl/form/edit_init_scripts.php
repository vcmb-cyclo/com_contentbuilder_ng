<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$cbFormEditConfig = [
    'formId' => (int) ($this->item->id ?? 0),
    'debugModeEnabled' => !empty($this->item->debug_mode),
    'isBreezingFormsType' => $isBreezingFormsType,
    'breezingFormsEditableToken' => $breezingFormsEditableToken,
    'limitstart' => \CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper::getApplication()->getInput()->getInt('limitstart', 0),
    'helpUrl' => Uri::base() . 'index.php?option=com_contentbuilderng&view=form&layout=help&tmpl=component',
    'text' => [
        'columns' => Text::_('COM_CONTENTBUILDERNG_COLUMNS'),
        'typeEditEnableBfConfirm' => Text::_('COM_CONTENTBUILDERNG_TYPE_EDIT_ENABLE_BF_CONFIRM'),
        'formNotFound' => Text::_('COM_CONTENTBUILDERNG_FORM_NOT_FOUND'),
        'saveFailed' => Text::_('COM_CONTENTBUILDERNG_SAVE_FAILED'),
        'confirmCloseUnsaved' => Text::_('COM_CONTENTBUILDERNG_CONFIRM_CLOSE_UNSAVED'),
        'unnamed' => Text::_('COM_CONTENTBUILDERNG_UNNAMED'),
        'inheritedFrom' => Text::_('COM_CONTENTBUILDERNG_INHERITED_FROM'),
        'errorEnterFormname' => Text::_('COM_CONTENTBUILDERNG_ERROR_ENTER_FORMNAME'),
        'errorEnterFormnameAll' => Text::_('COM_CONTENTBUILDERNG_ERROR_ENTER_FORMNAME_ALL'),
        'initialiseOverwriteConfirm' => Text::_('COM_CONTENTBUILDERNG_INITIALISE_OVERWRITE_CONFIRM'),
        'listStatesResetInactiveConfirm' => Text::_('COM_CONTENTBUILDERNG_LIST_STATES_RESET_INACTIVE_CONFIRM'),
        'listStatesResetPaletteConfirm' => Text::_('COM_CONTENTBUILDERNG_LIST_STATES_RESET_PALETTE_CONFIRM'),
        'listStatesResetDisableConfirm' => Text::_('COM_CONTENTBUILDERNG_LIST_STATES_RESET_DISABLE_CONFIRM'),
        'listStatesResetFullConfirm' => Text::_('COM_CONTENTBUILDERNG_LIST_STATES_RESET_FULL_CONFIRM'),
        'detailsResetDisplayConfirm' => Text::_('COM_CONTENTBUILDERNG_DETAILS_ACTION_RESET_DISPLAY_CONFIRM'),
        'detailsRegenerateConfirm' => Text::_('COM_CONTENTBUILDERNG_DETAILS_ACTION_REGENERATE_CONFIRM'),
        'detailsDisableConfirm' => Text::_('COM_CONTENTBUILDERNG_DETAILS_ACTION_DISABLE_CONFIRM'),
        'detailsResetFullConfirm' => Text::_('COM_CONTENTBUILDERNG_DETAILS_ACTION_RESET_FULL_CONFIRM'),
        'editResetDisplayConfirm' => Text::_('COM_CONTENTBUILDERNG_EDIT_ACTION_RESET_DISPLAY_CONFIRM'),
        'editRegenerateConfirm' => Text::_('COM_CONTENTBUILDERNG_EDIT_ACTION_REGENERATE_CONFIRM'),
        'editDisableConfirm' => Text::_('COM_CONTENTBUILDERNG_EDIT_ACTION_DISABLE_CONFIRM'),
        'editResetFullConfirm' => Text::_('COM_CONTENTBUILDERNG_EDIT_ACTION_RESET_FULL_CONFIRM'),
        'resetIntroConfirm' => Text::_('COM_CONTENTBUILDERNG_RESET_LIST_INTRO_CONFIRM'),
    ],
    'listStateDefaultTitles' => array_map(
        static fn(int $index): string => Text::sprintf('COM_CONTENTBUILDERNG_LIST_STATE_DEFAULT_TITLE', $index),
        range(1, 10)
    ),
];

$cbFormEditInitScriptPath = JPATH_ROOT . '/media/com_contentbuilderng/js/form-edit-init.js';
$cbFormEditInitScriptVersion = is_file($cbFormEditInitScriptPath) ? (string) filemtime($cbFormEditInitScriptPath) : '1';
?>

<script>
    window.cbFormEditConfig = <?php echo json_encode($cbFormEditConfig, JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="<?php echo htmlspecialchars(Uri::root(true) . '/media/com_contentbuilderng/js/form-edit-init.js?' . $cbFormEditInitScriptVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>

<?php if ($isBreezingFormsType && (int) ($this->item->id ?? 0) > 0) : ?>
<?php require __DIR__ . '/bf_system_fields_modal_scripts.php'; ?>
<?php endif; ?>
