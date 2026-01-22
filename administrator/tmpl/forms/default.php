<?php

/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA + GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderHelper;

// Charge les scripts Joomla nécessaires (checkAll, submit, etc.)
HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.multiselect');

// Sécurité: valeurs par défaut
$order     = $this->lists['order'] ?? 'a.ordering';
$orderDir  = $this->lists['order_Dir'] ?? 'asc';

// Les flèches d'ordering ne doivent être actives QUE si on est trié sur ordering
$saveOrder = ($order === 'a.ordering');

$n = is_countable($this->items) ? count($this->items) : 0;

// limitstart courant (évite CBRequest/eval)
$app = Factory::getApplication();
$limitstart = $app->input->getInt('limitstart', 0);

?>
<form action="index.php"
    method="post"
    name="adminForm"
    id="adminForm">

    <div id="editcell">
        <label for="filter_tag">
            <?php echo Text::_('COM_CONTENTBUILDER_FILTER_TAG'); ?>:
        </label>
        <select class="form-select-sm" id="filter_tag" name="filter_tag" onchange="document.adminForm.submit();">
            <option value=""> -
                <?php echo htmlentities(Text::_('COM_CONTENTBUILDER_FILTER_TAG_ALL'), ENT_QUOTES, 'UTF-8') ?> -
            </option>
            <?php
            foreach ($this->tags as $tag) {
            ?>
                <option value="<?php echo htmlentities($tag->tag, ENT_QUOTES, 'UTF-8') ?>" <?php echo strtolower($this->lists['filter_tag']) == strtolower($tag->tag) ? ' selected="selected"' : ''; ?>>
                    <?php echo htmlentities($tag->tag, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php
            }
            ?>
        </select>
        <br />
        <br />
        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th width="5">
                        <?php echo Text::_('COM_CONTENTBUILDER_ID'); ?>
                    </th>
                    <th width="20">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_(
                            'grid.sort',
                            Text::_('COM_CONTENTBUILDER_VIEW_NAME'),
                            'a.name',
                            $this->lists['order_Dir'],
                            $this->lists['order']
                        ); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_(
                            'grid.sort',
                            Text::_('COM_CONTENTBUILDER_TAG'),
                            'a.tag',
                            $this->lists['order_Dir'],
                            $this->lists['order']
                        ); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_(
                            'grid.sort',
                            Text::_('COM_CONTENTBUILDER_FORM_SOURCE'),
                            'a.title',
                            $this->lists['order_Dir'],
                            $this->lists['order']
                        ); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_(
                            'grid.sort',
                            Text::_('COM_CONTENTBUILDER_TYPE'),
                            'a.type',
                            $this->lists['order_Dir'],
                            $this->lists['order']
                        ); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_(
                            'grid.sort',
                            Text::_('COM_CONTENTBUILDER_DISPLAY'),
                            'a.display_in',
                            $this->lists['order_Dir'],
                            $this->lists['order']
                        ); ?>
                    </th>


                    <th width="120">
                        <?php echo HTMLHelper::_(
                            'grid.sort',
                            Text::_('COM_CONTENTBUILDER_ORDERBY'),
                            'a.ordering',
                            $this->lists['order_Dir'],
                            $this->lists['order']
                        ); ?>
                    </th>
                    <th width="5">
                        <?php echo HTMLHelper::_(
                            'grid.sort',
                            Text::_('COM_CONTENTBUILDER_PUBLISHED'),
                            'a.published',
                            $this->lists['order_Dir'],
                            $this->lists['order']
                        ); ?>
                    </th>
                    <th width="140">
                        <?php echo HTMLHelper::_(
                            'grid.sort',
                            Text::_('JGLOBAL_MODIFIED'),
                            'a.modified',
                            $this->lists['order_Dir'],
                            $this->lists['order']
                        ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $k = 0;
                $n = count($this->items);
                for ($i = 0; $i < $n; $i++) {
                    $row = $this->items[$i];
                    $checked = HTMLHelper::_('grid.id', $i, $row->id);
                    $link = Route::_('index.php?option=com_contentbuilder&task=form.edit&id=' . $row->id);
                    $published = ContentbuilderHelper::listPublish('forms', $row, $i);
                ?>
                    <tr class="<?php echo "row$k"; ?>">
                        <td>
                            <?php echo $row->id; ?>
                        </td>
                        <td>
                            <?php echo $checked; ?>
                        </td>
                        <td>
                            <a href="<?php echo $link; ?>">
                                <?php echo $row->name; ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo $link; ?>">
                                <?php echo $row->tag; ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo $link; ?>">
                                <?php echo $row->title; ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo $link; ?>">
                                <?php echo $row->type; ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo $link; ?>">
                                <?php echo $row->display_in == 0 ? Text::_('COM_CONTENTBUILDER_DISPLAY_FRONTEND') : ($row->display_in == 1 ? Text::_('COM_CONTENTBUILDER_DISPLAY_BACKEND') : Text::_('COM_CONTENTBUILDER_DISPLAY_BOTH')); ?>
                            </a>
                        </td>
                        <td class="order,text-nowrap">
                            <span>
                                <?php echo $this->pagination->orderUpIcon($i, $saveOrder, 'forms.orderup', 'Move Up', $this->ordering);
                                ?>
                            </span>
                            <span>
                                <?php echo 
                                $this->pagination->orderDownIcon($i, $n, $saveOrder, 'forms.orderdown', 'Move Down', $this->ordering);
                                ?>
                            </span>
                            <?php $disabled = $this->ordering ? '' : 'disabled="disabled"'; ?>
                        </td>
                        <td>
                            <?php echo HTMLHelper::_('jgrid.published', (int) $row->published, $i, 'forms.', true, 'cb');
                             ?>
                        </td>
                        <td>
                            <?php
                            $m = $row->modified ?? '';
                            if ($m && $m !== '0000-00-00 00:00:00') {
                                echo HTMLHelper::_('date', $m, Text::_('DATE_FORMAT_LC4'));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>


                    </tr>
                <?php
                    $k = 1 - $k;
                }
                ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="10">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                    <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php echo $this->pagination->getPagesCounter(); ?>
                    <span><?php echo Text::_('COM_CONTENTBUILDER_DISPLAY_NUM'); ?></span>
                    <span class="d-inline-block"><?php echo $this->pagination->getLimitBox(); ?></span>
                    <span><?php echo Text::_('COM_CONTENTBUILDER_OF'); ?></span>
                    <span><?php echo (int) ($this->pagination->total ?? 0); ?></span>
                    </div>

                    <div>
                    <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>

                </div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>

    <input type="hidden" name="option" value="com_contentbuilder" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="view" value="forms" />
    <input type="hidden" name="limitstart" value="<?php echo (int) $limitstart; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars($order, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars($orderDir, ENT_QUOTES, 'UTF-8'); ?>">
    
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
