<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<?php Factory::getApplication()->getDocument()->addStyleDeclaration($this->theme_css); ?>
<?php Factory::getApplication()->getDocument()->addScriptDeclaration($this->theme_js); ?>
<div align="center">
    <button class="button" onclick="window.print()">
        <?php echo Text::_('COM_CONTENTBUILDER_PRINT') ?>
    </button>
    <button class="button" onclick="self.close()">
        <?php echo Text::_('COM_CONTENTBUILDER_CLOSE') ?>
    </button>
</div>
<h1 class="contentheading">
    <?php echo $this->page_title; ?>
</h1>
<?php echo $this->event->afterDisplayTitle; ?>
<?php
if (CBRequest::getInt('cb_show_author', 1)) {
    ?>

    <?php if ($this->created): ?>
        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_CREATED_ON'); ?>
            <?php echo HTMLHelper::_('date', $this->created, Text::_('DATE_FORMAT_LC2')); ?>
        </span>
    <?php endif; ?>

    <?php if ($this->created_by): ?>
        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_BY'); ?>
            <?php echo $this->created_by; ?>
        </span><br />
    <?php endif; ?>

    <?php
}
?>

<br />
<br />

<?php echo $this->event->beforeDisplayContent; ?>
<?php echo $this->toc ?>
<?php echo $this->tpl ?>
<?php echo $this->event->afterDisplayContent; ?>


<?php
if (CBRequest::getInt('cb_show_author', 1)) {
    ?>

    <?php if ($this->modified_by): ?>
        <br />

        <?php if ($this->modified): ?>
            <span class="small created-by">
                <?php echo Text::_('COM_CONTENTBUILDER_LAST_UPDATED_ON'); ?>
                <?php echo HTMLHelper::_('date', $this->modified, Text::_('DATE_FORMAT_LC2')); ?>
            </span>
        <?php endif; ?>

        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_BY'); ?>
            <?php echo $this->modified_by; ?>
        </span>

    <?php endif; ?>

    <?php
}
?>