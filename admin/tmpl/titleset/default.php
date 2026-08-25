<?php

declare(strict_types=1);

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<?php if (($this->data['source'] ?? '') === 'provided') : ?>
<div class="container-fluid">
    <div class="alert alert-info"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_VIEW_INTRO'); ?></div>
    <div class="card mb-3">
        <div class="card-body"><dl class="row mb-0">
            <?php foreach (['filename', 'name', 'comments'] as $key) : ?>
                <dt class="col-sm-3"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_' . strtoupper($key)); ?></dt>
                <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars((string) ($this->data[$key] ?? ''), ENT_QUOTES, 'UTF-8')); ?></dd>
            <?php endforeach; ?>
            <dt class="col-sm-3"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_MODIFIED'); ?></dt>
            <dd class="col-sm-9"><?php echo !empty($this->data['modified'])
                ? HTMLHelper::_('date', '@' . (int) $this->data['modified'], Text::_('DATE_FORMAT_LC5'))
                : Text::_('COM_CONTENTBUILDERNG_TITLESETS_NOT_SAVED'); ?></dd>
        </dl></div>
    </div>
    <div class="card"><div class="card-header"><h2 class="h4 mb-0"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_MAPPINGS'); ?></h2></div>
        <div class="card-body table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr>
                    <th><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_ORIGINAL_VALUE'); ?></th>
                    <th><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_DISPLAY_LABEL'); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ((array) ($this->data['titles'] ?? []) as $mapping) : ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars((string) ($mapping['value'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></code></td>
                        <td><?php echo htmlspecialchars((string) ($mapping['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else : ?>
    <?php HTMLHelper::_('behavior.formvalidator'); ?>
<form action="<?php echo Route::_('index.php?option=com_contentbuilderng&view=titleset'); ?>"
      method="post" name="adminForm" id="titleset-form" class="form-validate">
    <div class="card mb-3">
        <div class="card-body">
            <?php echo $this->form->renderField('filename'); ?>
            <?php echo $this->form->renderField('name'); ?>
            <?php echo $this->form->renderField('comments'); ?>
            <div class="control-group">
                <div class="control-label"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_MODIFIED'); ?></div>
                <div class="controls pt-2"><?php echo !empty($this->data['modified'])
                    ? HTMLHelper::_('date', '@' . (int) $this->data['modified'], Text::_('DATE_FORMAT_LC5'))
                    : Text::_('COM_CONTENTBUILDERNG_TITLESETS_NOT_SAVED'); ?></div>
            </div>
            <?php foreach (['description', 'locale', 'version', 'author', 'modified', 'source'] as $hiddenField) : ?>
                <?php echo $this->form->getInput($hiddenField); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_MAPPINGS'); ?></h2>
        </div>
        <div class="card-body"><?php echo $this->form->renderFieldset('titles'); ?></div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('titleset-form');
    if (!form || typeof Joomla === 'undefined' || typeof Joomla.submitform !== 'function') return;
    let dirty = false;
    let allowNavigation = false;
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('change', function () { dirty = true; });
    window.addEventListener('beforeunload', function (event) {
        if (!dirty || allowNavigation) return;
        event.preventDefault();
        event.returnValue = '';
    });
    Joomla.submitbutton = function (task) {
        if (task === 'titleset.cancel' && dirty && !window.confirm(<?php echo json_encode(Text::_('COM_CONTENTBUILDERNG_TITLESETS_CANCEL_CONFIRM'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>)) return false;
        allowNavigation = true;
        Joomla.submitform(task, form);
        return true;
    };
});
</script>
<?php endif; ?>
