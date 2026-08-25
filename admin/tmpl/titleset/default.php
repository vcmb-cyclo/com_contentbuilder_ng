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
    <div class="card mb-3"><div class="card-header"><h2 class="h4 mb-0"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_METADATA'); ?></h2></div>
        <div class="card-body"><dl class="row mb-0">
            <?php foreach (['filename', 'name', 'description', 'locale', 'version', 'author', 'comments'] as $key) : ?>
                <dt class="col-sm-3"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_' . strtoupper($key)); ?></dt>
                <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars((string) ($this->data[$key] ?? ''), ENT_QUOTES, 'UTF-8')); ?></dd>
            <?php endforeach; ?>
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
<form action="<?php echo Route::_('index.php?option=com_contentbuilderng&view=titleset'); ?>"
      method="post" name="adminForm" id="titleset-form" class="form-validate">
    <div class="alert alert-info">
        <?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_EDIT_INTRO'); ?>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h2 class="h4 mb-0"><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_METADATA'); ?></h2>
        </div>
        <div class="card-body"><?php echo $this->form->renderFieldset('metadata'); ?></div>
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
<?php endif; ?>
