<?php

declare(strict_types=1);

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
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
