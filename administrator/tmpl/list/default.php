<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 * @copyright Copyright (C) 2026 by XDA+GIL
 */

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderHelper;


$language_allowed = ContentbuilderLegacyHelper::authorize('language');
$edit_allowed = ContentbuilderLegacyHelper::authorize('edit');
$delete_allowed = ContentbuilderLegacyHelper::authorize('delete');
$view_allowed = ContentbuilderLegacyHelper::authorize('view');
$new_allowed = ContentbuilderLegacyHelper::authorize('new');
$state_allowed = ContentbuilderLegacyHelper::authorize('state');
$publish_allowed = ContentbuilderLegacyHelper::authorize('publish');
$rating_allowed = ContentbuilderLegacyHelper::authorize('rating');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

// Charge le manifeste joomla.asset.json du composant
$wa->getRegistry()->addExtensionRegistryFile('com_contentbuilder');

$wa->useScript('jquery');
$wa->useScript('com_contentbuilder.contentbuilder');


?>
<?php Factory::getApplication()->getDocument()->addStyleDeclaration($this->theme_css); ?>
<?php Factory::getApplication()->getDocument()->addScriptDeclaration($this->theme_js); ?>

<script language="javascript" type="text/javascript">
<!--
    function contentbuilder_state() {
        document.getElementById('controller').value = 'edit';
        document.getElementById('view').value = 'edit';
        document.getElementById('task').value = 'list.state';
        document.adminForm.submit();
    }
    function contentbuilder_publish() {
        document.getElementById('controller').value = 'edit';
        document.getElementById('view').value = 'edit';
        document.getElementById('task').value = 'list.publish';
        document.adminForm.submit();
    }
    function contentbuilder_language() {
        document.getElementById('controller').value = 'edit';
        document.getElementById('view').value = 'edit';
        document.getElementById('task').value = 'list.language';
        document.adminForm.submit();
    }
    function contentbuilder_delete() {
        var confirmed = confirm('<?php echo Text::_('COM_CONTENTBUILDER_CONFIRM_DELETE_MESSAGE'); ?>');
        if (confirmed) {
            document.getElementById('controller').value = 'edit';
            document.getElementById('view').value = 'edit';
            document.getElementById('task').value = 'list.delete';
            document.adminForm.submit();
        }
    }
    jQuery(document).ready(function () {
        jQuery(function () {
            jQuery("#contentbuilder_filter").keypress(function (e) {
                if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                    jQuery('#cbSearchButton').click();
                    return false;
                } else {
                    return true;
                }
            });
        });
    });
    //-->
</script>

<?php if ($this->page_title): ?>
    <h1 class="contentheading">
        <?php echo $this->page_title; ?>
    </h1>
<?php endif; ?>
<?php echo $this->intro_text; ?>
<div class="row g-2 justify-content-md-end align-items-center mb-3">
    <?php
    if ($this->export_xls):
        ?>
        <div class="col-12 col-sm-auto d-grid d-sm-block">
            <a class="btn btn-sm btn-outline-success"
                href="<?php echo Route::_('index.php?option=com_contentbuilder&view=export&id=' . CBRequest::getInt('id', 0) . '&type=xls&format=raw&tmpl=component'); ?>">
                <i class="fa fa-file-excel" aria-hidden="true"></i>
            </a>
        </div>
        <?php
    endif;
    ?>
    <?php
    if ($new_allowed) {
        ?>
        <div class="col-12 col-sm-auto d-grid d-sm-block">
            <button class="button btn btn-sm btn-primary cbButton cbNewButton"
                onclick="location.href='<?php echo Route::_('index.php?option=com_contentbuilder&task=edit.display&backtolist=1&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&record_id=0&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order')); ?>'">
                <?php echo Text::_('COM_CONTENTBUILDER_NEW'); ?>
            </button>
        </div>
        <?php
    }
    ?>

    <?php
    if ($delete_allowed) {
        ?>
        <div class="col-12 col-sm-auto d-grid d-sm-block">
            <button class="button btn btn-sm btn-outline-danger cbButton cbDeleteButton" onclick="contentbuilder_delete();">
                <i class="fa fa-trash" aria-hidden="true"></i>
                <?php echo Text::_('COM_CONTENTBUILDER_DELETE'); ?>
            </button>
        </div>
        <?php
    }
    ?>
</div>



<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
        <?php
        if (
            $state_allowed && count($this->states) ||
            $publish_allowed ||
            $language_allowed
        ) {
            ?>
            <div class="row g-2 align-items-center flex-md-nowrap mb-2">
                <div class="col-12 col-lg-auto fw-semibold text-center text-md-start">
                    <?php echo Text::_('COM_CONTENTBUILDER_BULK_OPTIONS'); ?>
                </div>
                <?php
                if ($state_allowed && count($this->states)) {
                    ?>
                    <div class="col-12 col-md-auto">
                        <div class="row g-2 align-items-center flex-sm-nowrap">
                            <div class="col-12 col-sm-auto">
                                <select class="form-select form-select-sm" name="list_state">
                                    <option value="0"> -
                                        <?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?> -
                                    </option>
                                    <?php
                                    foreach ($this->states as $state) {
                                        ?>
                                        <option value="<?php echo $state['id'] ?>">
                                            <?php echo $state['title'] ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 col-sm-auto">
                                <button class="button btn btn-sm btn-outline-primary cbButton cbSearchButton"
                                    onclick="contentbuilder_state();">
                                    <?php echo Text::_('COM_CONTENTBUILDER_APPLY'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
                <?php
                if ($publish_allowed) {
                    ?>
                    <div class="col-12 col-md-auto">
                        <div class="row g-2 align-items-center flex-sm-nowrap">
                            <div class="col-12 col-sm-auto">
                                <select class="form-select form-select-sm" name="list_publish">
                                    <option value="-1"> -
                                        <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED_UNPUBLISHED'); ?> -
                                    </option>
                                    <option value="1">
                                        <?php echo Text::_('COM_CONTENTBUILDER_PUBLISH') ?>
                                    </option>
                                    <option value="0">
                                        <?php echo Text::_('COM_CONTENTBUILDER_UNPUBLISH') ?>
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-auto">
                                <button class="button btn btn-sm btn-outline-primary cbButton cbSearchButton"
                                    onclick="contentbuilder_publish();">
                                    <?php echo Text::_('COM_CONTENTBUILDER_APPLY'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
                <?php
                if ($language_allowed) {
                    ?>
                    <div class="col-12 col-md-auto">
                        <div class="row g-2 align-items-center flex-sm-nowrap">
                            <div class="col-12 col-sm-auto">
                                <select class="form-select form-select-sm" name="list_language">
                                    <option value="*"> -
                                        <?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?> -
                                    </option>
                                    <option value="*">
                                        <?php echo Text::_('COM_CONTENTBUILDER_ANY'); ?>
                                    </option>
                                    <?php
                                    foreach ($this->languages as $filter_language) {
                                        ?>
                                        <option value="<?php echo $filter_language; ?>">
                                            <?php echo $filter_language; ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 col-sm-auto">
                                <button class="button btn btn-sm btn-outline-primary cbButton cbSearchButton"
                                    onclick="contentbuilder_language();">
                                    <?php echo Text::_('COM_CONTENTBUILDER_APPLY'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>

        <?php
        if ($this->display_filter) {
            ?>
            <div class="row g-2 align-items-center flex-md-nowrap mb-3">
                <div class="col-12 col-lg-auto fw-semibold text-center text-md-start">
                    <?php echo Text::_('COM_CONTENTBUILDER_FILTER'); ?>
                </div>
                <div class="col-12 col-md-auto">
                    <input class="form-control form-control-sm" type="text" id="contentbuilder_filter" name="filter"
                        value="<?php echo $this->escape($this->lists['filter']); ?>"
                        onchange="document.adminForm.submit();" />
                </div>
                <?php
                if ($this->list_state && count($this->states)) {
                    ?>
                    <div class="col-12 col-md-auto">
                        <select class="form-select form-select-sm" name="list_state_filter" id="list_state_filter"
                            onchange="document.adminForm.submit();">
                            <option value="0"> -
                                <?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?> -
                            </option>
                            <?php
                            foreach ($this->states as $state) {
                                ?>
                                <option value="<?php echo $state['id'] ?>" <?php echo $this->lists['filter_state'] == $state['id'] ? ' selected="selected"' : ''; ?>>
                                    <?php echo $state['title'] ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }

                if ($this->list_publish && $publish_allowed) {
                    ?>
                    <div class="col-12 col-md-auto">
                        <select class="form-select form-select-sm" name="list_publish_filter"
                            id="list_publish_filter" onchange="document.adminForm.submit();">
                            <option value="-1"> -
                                <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED_UNPUBLISHED'); ?> -
                            </option>
                            <option value="1" <?php echo $this->lists['filter_publish'] == 1 ? ' selected="selected"' : ''; ?>>
                                <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED') ?>
                            </option>
                            <option value="0" <?php echo $this->lists['filter_publish'] == 0 ? ' selected="selected"' : ''; ?>>
                                <?php echo Text::_('COM_CONTENTBUILDER_UNPUBLISHED') ?>
                            </option>
                        </select>
                    </div>
                    <?php
                }

                if ($this->list_language) {
                    ?>
                    <div class="col-12 col-md-auto">
                        <select class="form-select form-select-sm" name="list_language_filter"
                            id="list_language_filter" onchange="document.adminForm.submit();">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?> -
                            </option>
                            <?php
                            foreach ($this->languages as $filter_language) {
                                ?>
                                <option value="<?php echo $filter_language; ?>" <?php echo $this->lists['filter_language'] == $filter_language ? ' selected="selected"' : ''; ?>>
                                    <?php echo $filter_language; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }
                ?>

                <div class="col-12 col-md-auto">
                    <div class="row g-2 align-items-center flex-sm-nowrap">
                        <div class="col-12 col-sm-auto d-grid d-sm-block">
                            <button type="submit" class="button btn btn-sm btn-primary cbButton cbSearchButton"
                                id="cbSearchButton" onclick="document.adminForm.submit();">
                                <?php echo Text::_('COM_CONTENTBUILDER_SEARCH') ?>
                            </button>
                        </div>
                        <div class="col-12 col-sm-auto d-grid d-sm-block">
                            <button class="button btn btn-sm btn-outline-secondary cbButton cbResetButton"
                                onclick="document.getElementById('contentbuilder_filter').value='';<?php echo $this->list_language && count($this->languages) ? "if(document.getElementById('list_language_filter')) document.getElementById('list_language_filter').selectedIndex=0;" : ""; ?><?php echo $this->list_state && count($this->states) ? "if(document.getElementById('list_state_filter')) document.getElementById('list_state_filter').selectedIndex=0;" : ""; ?><?php echo $this->list_publish ? "if(document.getElementById('list_publish_filter')) document.getElementById('list_publish_filter').selectedIndex=0;" : ""; ?>document.adminForm.submit();">
                                <?php echo Text::_('COM_CONTENTBUILDER_RESET') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>

        <?php
        $current_order = isset($this->lists['order']) ? $this->lists['order'] : '';
        $current_dir = isset($this->lists['order_Dir']) ? strtolower($this->lists['order_Dir']) : '';
        $current_dir = $current_dir === 'desc' ? 'desc' : 'asc';
        $sort_indicator = function ($order_key) use ($current_order, $current_dir) {
            if ($order_key !== $current_order) {
                return '';
            }
            return $current_dir === 'asc'
                ? ' <span class="ms-1 icon-sort icon-sort-asc" aria-hidden="true"></span>'
                : ' <span class="ms-1 icon-sort icon-sort-desc" aria-hidden="true"></span>';
        };
        $formId = (int) ($this->form_id ?? CBRequest::getInt('id', 0));
        $itemId = (int) CBRequest::getInt('Itemid', 0);
        $tmpl = (string) CBRequest::getVar('tmpl', '');
        $layout = (string) CBRequest::getVar('layout', '');
        $tmplParam = $tmpl !== '' ? '&tmpl=' . $tmpl : '';
        $layoutParam = $layout !== '' ? '&layout=' . $layout : '';
        $itemIdParam = $itemId ? '&Itemid=' . $itemId : '';
        $sortLink = function (string $labelHtml, string $field) use ($current_order, $current_dir, $formId, $tmplParam, $layoutParam, $itemIdParam) {
            $nextDir = ($current_order === $field && $current_dir === 'asc') ? 'desc' : 'asc';
            $url = Route::_(
                'index.php?option=com_contentbuilder&task=list.display&id=' . $formId
                . $tmplParam . $layoutParam . $itemIdParam
                . '&limitstart=0&filter_order=' . $field . '&filter_order_Dir=' . $nextDir
            );

            return '<a href="' . $url . '">' . $labelHtml . '</a>';
        };
        ?>
        <table class="mt-3 table table-striped">
            <thead>
                <tr>
                    <?php
                    if ($this->show_id_column) {
                        ?>
                        <th class="sectiontableheader hidden-phone align-middle text-nowrap small text-uppercase" width="5">
                            <?php echo $sortLink(
                                htmlentities('COM_CONTENTBUILDER_ID', ENT_QUOTES, 'UTF-8') . $sort_indicator('colRecord'),
                                'colRecord'
                            ); ?>
                        </th>
                        <?php
                    }

                    if ($this->select_column && ($delete_allowed || $state_allowed || $publish_allowed)) {
                        ?>
                        <th class="sectiontableheader hidden-phone align-middle text-nowrap small text-uppercase" width="20">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>
                        <?php
                    }

                    if ($this->edit_button && $edit_allowed) {
                        ?>
                        <th class="sectiontableheader align-middle text-nowrap small text-uppercase" width="20">
                            <?php echo Text::_('COM_CONTENTBUILDER_EDIT'); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_state) {
                        ?>
                        <th class="sectiontableheader hidden-phone align-middle text-nowrap small text-uppercase">
                            <?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_publish && $publish_allowed) {
                        ?>
                        <th class="sectiontableheader align-middle text-nowrap small text-uppercase" width="20">
                            <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED'); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_language) {
                        ?>
                        <th class="sectiontableheader hidden-phone align-middle text-nowrap small text-uppercase" width="20">
                            <?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_article) {
                        ?>
                        <th class="sectiontableheader hidden-phone align-middle text-nowrap small text-uppercase">
                            <?php echo $sortLink(
                                htmlentities('COM_CONTENTBUILDER_ARTICLE', ENT_QUOTES, 'UTF-8') . $sort_indicator('colArticleId'),
                                'colArticleId'
                            ); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_author) {
                        ?>
                        <th class="sectiontableheader hidden-phone align-middle text-nowrap small text-uppercase">
                            <?php echo $sortLink(
                                htmlentities('COM_CONTENTBUILDER_AUTHOR', ENT_QUOTES, 'UTF-8') . $sort_indicator('colAuthor'),
                                'colAuthor'
                            ); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_rating) {
                        ?>
                        <th class="sectiontableheader hidden-phone align-middle text-nowrap small text-uppercase">
                            <?php echo $sortLink(
                                htmlentities('COM_CONTENTBUILDER_RATING', ENT_QUOTES, 'UTF-8') . $sort_indicator('colRating'),
                                'colRating'
                            ); ?>
                        </th>
                        <?php
                    }

                    if ($this->labels) {
                        $label_count = 0;
                        $hidden = ' hidden-phone';
                        foreach ($this->labels as $reference_id => $label) {
                            if ($label_count == 0) {
                                $hidden = '';
                            } else {
                                $hidden = ' hidden-phone';
                            }
                            ?>
                            <th class="sectiontableheader<?php echo $hidden; ?> align-middle text-nowrap small text-uppercase">
                                <?php echo $sortLink(
                                    nl2br(htmlentities(ContentbuilderHelper::contentbuilder_wordwrap($label, 20, "\n", true), ENT_QUOTES, 'UTF-8')) . $sort_indicator("col$reference_id"),
                                    "col$reference_id"
                                ); ?>
                            </th>
                            <?php
                            $label_count++;
                        }
                    }
                    ?>
                </tr>
            </thead>
            <?php
            $k = 0;
            $n = count($this->items);
            for ($i = 0; $i < $n; $i++) {
                $row = $this->items[$i];
                $link = Route::_('index.php?option=com_contentbuilder&task=details.display&id=' . $this->form_id . '&record_id=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
                $edit_link = Route::_('index.php?option=com_contentbuilder&task=edit.display&backtolist=1&id=' . $this->form_id . '&record_id=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
                $publish_link = Route::_('index.php?option=com_contentbuilder&task=edit.display&task=edit.publish&backtolist=1&id=' . $this->form_id . '&list_publish=1&cid[]=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
                $unpublish_link = Route::_('index.php?option=com_contentbuilder&task=edit.display&task=edit.publish&backtolist=1&id=' . $this->form_id . '&list_publish=0&cid[]=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
                $select = '<input class="form-check-input" type="checkbox" name="cid[]" value="' . $row->colRecord . '"/>';
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <?php
                    if ($this->show_id_column) {
                        ?>
                        <td class="hidden-phone">
                            <?php
                            if (($view_allowed || $this->own_only)) {
                                ?>
                                <a href="<?php echo $link; ?>">
                                    <?php echo $row->colRecord; ?>
                                </a>
                                <?php
                            } else {
                                ?>
                                <?php echo $row->colRecord; ?>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->select_column && ($delete_allowed || $state_allowed || $publish_allowed)) {
                        ?>
                        <td class="hidden-phone">
                            <?php echo $select; ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->edit_button && $edit_allowed) {
                        ?>
                        <td>
                            <a href="<?php echo $edit_link; ?>">
                                <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>media/com_contentbuilder/images/edit.png" alt="Edit"
                                    border="0" width="18" height="18" /></a>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_state) {
                        ?>
                        <td class="hidden-phone"
                            style="background-color: #<?php echo isset($this->state_colors[$row->colRecord]) ? $this->state_colors[$row->colRecord] : 'FFFFFF'; ?>;">
                            <?php echo isset($this->state_titles[$row->colRecord]) ? htmlentities($this->state_titles[$row->colRecord], ENT_QUOTES, 'UTF-8') : ''; ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_publish && $publish_allowed) {
                        ?>
                        <td align="center" valign="middle">
                            <?php echo ContentbuilderHelper::publishButton(isset($this->published_items[$row->colRecord]) && $this->published_items[$row->colRecord] ? true : false, $publish_link, $unpublish_link, 'tick.png', 'publish_x.png', $publish_allowed); ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_language) {
                        ?>
                        <td class="hidden-phone">
                            <?php echo isset($this->lang_codes[$row->colRecord]) && $this->lang_codes[$row->colRecord] ? $this->lang_codes[$row->colRecord] : '*'; ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_article) {
                        ?>
                        <td class="hidden-phone">
                            <?php
                            if (($view_allowed || $this->own_only)) {
                                ?>
                                <a href="<?php echo $link; ?>">
                                    <?php echo $row->colArticleId; ?>
                                </a>
                                <?php
                            } else {
                                ?>
                                <?php echo $row->colArticleId; ?>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_author) {
                        ?>
                        <td class="hidden-phone">
                            <?php
                            if (($view_allowed || $this->own_only)) {
                                ?>
                                <a href="<?php echo $link; ?>">
                                    <?php echo htmlentities($row->colAuthor, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <?php
                            } else {
                                ?>
                                <?php echo htmlentities($row->colAuthor, ENT_QUOTES, 'UTF-8'); ?>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_rating) {
                        ?>
                        <td class="hidden-phone">
                            <?php
                            echo ContentbuilderLegacyHelper::getRating(CBRequest::getInt('id', 0), $row->colRecord, $row->colRating, $this->rating_slots, CBRequest::getCmd('lang', ''), $rating_allowed, $row->colRatingCount, $row->colRatingSum);
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    $label_count = 0;
                    $hidden = ' class="hidden-phone"';
                    foreach ($row as $key => $value) {
                        // filtering out disallowed columns
                        if (in_array(str_replace('col', '', $key), $this->visible_cols)) {
                            if ($label_count == 0) {
                                $hidden = '';
                            } else {
                                $hidden = ' class="hidden-phone"';
                            }
                            ?>
                            <td<?php echo $hidden; ?>>
                                <?php
                                if (in_array(str_replace('col', '', $key), $this->linkable_elements) && ($view_allowed || $this->own_only)) {
                                    ?>
                                    <a href="<?php echo $link; ?>">
                                        <?php echo $value; ?>
                                    </a>
                                    <?php
                                } else {
                                    ?>
                                    <?php echo $value; ?>
                                    <?php
                                }
                                ?>
                                </td>
                                <?php
                                $label_count++;
                        }
                    }
                    ?>
                </tr>
                <?php
                $k = 1 - $k;
            }
            $pages_links = $this->pagination->getPagesLinks();
            if ($pages_links || $this->show_records_per_page) {
                ?>
                <tfoot>
                    <tr>
                        <td colspan="1000" valign="middle" align="center">
                            <div class="pagination pagination-toolbar d-flex flex-column flex-md-row flex-md-nowrap justify-content-between align-items-center gap-2">
                                <?php
                                if ($this->show_records_per_page) {
                                    ?>
                                    <div class="cbPagesCounter">
                                        <?php echo $this->pagination->getPagesCounter(); ?>
                                        <?php
                                        echo '&nbsp;&nbsp;&nbsp;' . Text::_('COM_CONTENTBUILDER_DISPLAY_NUM') . '&nbsp;';
                                        echo $this->pagination->getLimitBox();
                                        ?>
                                        <?php echo Text::_('COM_CONTENTBUILDER_OF'); ?>
                                        <?php echo $this->total; ?>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <?php echo $pages_links; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <?php
            }
            ?>
        </table>
    </div>
    <?php
    if (CBRequest::getVar('tmpl', '') != '') {
        ?>
        <input type="hidden" name="tmpl" value="<?php echo CBRequest::getVar('tmpl', ''); ?>" />
        <?php
    }
    ?>
    <input type="hidden" name="option" value="com_contentbuilder" />
    <input type="hidden" name="task" id="task" value="" />
    <input type="hidden" name="view" id="view" value="list" />
    <input type="hidden" name="Itemid" value="<?php echo CBRequest::getInt('Itemid', 0); ?>" />
    <input type="hidden" name="limitstart" value="" />
    <input type="hidden" name="id" value="<?php echo CBRequest::getInt('id', 0) ?>" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
