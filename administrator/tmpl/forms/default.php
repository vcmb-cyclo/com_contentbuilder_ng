<?php

/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
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
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderHelper;

// Charge les scripts Joomla nécessaires (checkAll, submit, etc.)
HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.multiselect');

// Sécurité: valeurs par défaut
$order     = $this->lists['order'] ?? 'a.ordering';
$orderDir  = $this->lists['order_Dir'] ?? 'asc';
$orderDir  = strtolower($orderDir) === 'desc' ? 'desc' : 'asc';

// Les flèches d'ordering ne doivent être actives QUE si on est trié sur ordering
$saveOrder = ($order === 'a.ordering');

$n = is_countable($this->items) ? count($this->items) : 0;

// Joomla 6 native list start
$app = Factory::getApplication();
$list = (array) $app->input->get('list', [], 'array');
$listStart = isset($list['start']) ? (int) $list['start'] : 0;
$limitValue = (int) ($this->pagination->limit ?? 0);

$limitOptions = [];
for ($i = 5; $i <= 30; $i += 5) {
    $limitOptions[] = HTMLHelper::_('select.option', (string) $i);
}
$limitOptions[] = HTMLHelper::_('select.option', '50', Text::_('J50'));
$limitOptions[] = HTMLHelper::_('select.option', '100', Text::_('J100'));
$limitOptions[] = HTMLHelper::_('select.option', '0', Text::_('JALL'));

$limitSelect = HTMLHelper::_(
    'select.genericlist',
    $limitOptions,
    'list[limit]',
    'class="form-select js-select-submit-on-change active" id="list_limit" onchange="document.adminForm.submit();"',
    'value',
    'text',
    $limitValue
);

$sortLink = function (string $label, string $field) use ($order, $orderDir, $limitValue): string {
    $isActive = ($order === $field);
    $nextDir = ($isActive && $orderDir === 'asc') ? 'desc' : 'asc';
    $indicator = $isActive
        ? ($orderDir === 'asc'
            ? ' <span class="ms-1 icon-sort icon-sort-asc" aria-hidden="true"></span>'
            : ' <span class="ms-1 icon-sort icon-sort-desc" aria-hidden="true"></span>')
        : '';
    $url = Route::_(
        'index.php?option=com_contentbuilder_ng&view=forms&list[start]=0&list[ordering]='
        . $field . '&list[direction]=' . $nextDir . '&list[limit]=' . $limitValue
    );

    return '<a href="' . $url . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . $indicator . '</a>';
};

?>
<form action="index.php"
    method="post"
    name="adminForm"
    id="adminForm">

    <div id="editcell">
        <label for="filter_tag">
            <?php echo Text::_('COM_CONTENTBUILDER_NG_FILTER_TAG'); ?>:
        </label>
        <select class="form-select-sm" id="filter_tag" name="filter_tag" onchange="document.adminForm.submit();">
            <option value=""> -
                <?php echo htmlentities(Text::_('COM_CONTENTBUILDER_NG_FILTER_TAG_ALL'), ENT_QUOTES, 'UTF-8') ?> -
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
                        <?php echo $sortLink(Text::_('COM_CONTENTBUILDER_NG_ID'), 'a.id'); ?>
                    </th>
                    <th width="20">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th>
                        <?php echo $sortLink(Text::_('COM_CONTENTBUILDER_NG_VIEW_NAME'), 'a.name'); ?>
                    </th>
                    <th>
                        <?php echo $sortLink(Text::_('COM_CONTENTBUILDER_NG_TAG'), 'a.tag'); ?>
                    </th>
                    <th>
                        <?php echo $sortLink(Text::_('COM_CONTENTBUILDER_NG_FORM_SOURCE'), 'a.title'); ?>
                    </th>
                    <th width="90" class="text-center">
                        <?php echo $sortLink(Text::_('COM_CONTENTBUILDER_NG_TYPE'), 'a.type'); ?>
                    </th>
                    <th>
                        <?php echo $sortLink(Text::_('COM_CONTENTBUILDER_NG_DISPLAY'), 'a.display_in'); ?>
                    </th>


                    <th class="w-10 text-nowrap">
                        <?php echo $sortLink(Text::_('COM_CONTENTBUILDER_NG_ORDERBY'), 'a.ordering'); ?>
                    </th>
                    <th class="text-nowrap">
                        <?php echo $sortLink(Text::_('JGLOBAL_MODIFIED'), 'a.modified'); ?>
                    </th>
                    <th class="w-1 text-center">
                        <?php echo $sortLink(Text::_('COM_CONTENTBUILDER_NG_PUBLISHED'), 'a.published'); ?>
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
                    $link = Route::_('index.php?option=com_contentbuilder_ng&task=form.edit&id=' . $row->id);
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
                                <?php
                                $sourceTitle = (string) ($row->source_title ?? $row->title ?? '');
                                echo htmlspecialchars($sourceTitle, ENT_QUOTES, 'UTF-8');
                                ?>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="<?php echo $link; ?>">
                                <?php
                                $typeCode = (string) ($row->type ?? '');
                                $typeShortMap = [
                                    'com_breezingforms'  => 'BF',
                                    'com_contentbuilder_ng' => 'CB',
                                    'com_contentbuilderng'  => 'CB',
                                ];
                                $typeShort = $typeShortMap[$typeCode] ?? $typeCode;
                                echo htmlspecialchars($typeShort, ENT_QUOTES, 'UTF-8');
                                ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo $link; ?>">
                                <?php echo $row->display_in == 0 ? Text::_('COM_CONTENTBUILDER_NG_DISPLAY_FRONTEND') : ($row->display_in == 1 ? Text::_('COM_CONTENTBUILDER_NG_DISPLAY_BACKEND') : Text::_('COM_CONTENTBUILDER_NG_DISPLAY_BOTH')); ?>
                            </a>
                        </td>
                        <td class="order,text-nowrap">
                            <span>
                                <?php echo $this->pagination->orderUpIcon($i, $saveOrder, 'forms.orderup', Text::_('JLIB_HTML_MOVE_UP'), $this->ordering);
                                ?>
                            </span>
                            <span>
                                <?php echo 
                                $this->pagination->orderDownIcon($i, $n, $saveOrder, 'forms.orderdown', Text::_('JLIB_HTML_MOVE_DOWN'), $this->ordering);
                                ?>
                            </span>
                            <?php $disabled = $this->ordering ? '' : 'disabled="disabled"'; ?>
                        </td>
                        <td class="text-nowrap">
                            <?php
                            $m = $row->modified ?? '';
                            if ($m && $m !== '0000-00-00 00:00:00') {
                                echo HTMLHelper::_('date', $m, Text::_('DATE_FORMAT_LC5'));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo HTMLHelper::_('jgrid.published', (int) $row->published, $i, 'forms.', true, 'cb');
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
                    <span><?php echo Text::_('COM_CONTENTBUILDER_NG_DISPLAY_NUM'); ?></span>
                    <span class="d-inline-block"><?php echo $limitSelect; ?></span>
                    <span><?php echo Text::_('COM_CONTENTBUILDER_NG_OF'); ?></span>
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

    <input type="hidden" name="option" value="com_contentbuilder_ng" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="view" value="forms" />
    <input type="hidden" name="list[start]" value="<?php echo (int) $listStart; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="list[ordering]" value="<?php echo htmlspecialchars($order, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="list[direction]" value="<?php echo htmlspecialchars($orderDir, ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
