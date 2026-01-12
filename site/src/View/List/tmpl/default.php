<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

// no direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderHelper;


$language_allowed = ContentbuilderLegacyHelper::authorizeFe('language');
$edit_allowed = class_exists('cbFeMarker') ? ContentbuilderLegacyHelper::authorizeFe('edit') : ContentbuilderLegacyHelper::authorize('edit');
$delete_allowed = class_exists('cbFeMarker') ? ContentbuilderLegacyHelper::authorizeFe('delete') : ContentbuilderLegacyHelper::authorize('delete');
$view_allowed = class_exists('cbFeMarker') ? ContentbuilderLegacyHelper::authorizeFe('view') : ContentbuilderLegacyHelper::authorize('view');
$new_allowed = class_exists('cbFeMarker') ? ContentbuilderLegacyHelper::authorizeFe('new') : ContentbuilderLegacyHelper::authorize('new');
$state_allowed = class_exists('cbFeMarker') ? ContentbuilderLegacyHelper::authorizeFe('state') : ContentbuilderLegacyHelper::authorize('state');
$publish_allowed = class_exists('cbFeMarker') ? ContentbuilderLegacyHelper::authorizeFe('publish') : ContentbuilderLegacyHelper::authorize('publish');
$rating_allowed = class_exists('cbFeMarker') ? ContentbuilderLegacyHelper::authorizeFe('rating') : ContentbuilderLegacyHelper::authorize('rating');

Factory::getApplication()->getDocument()->addScript(Uri::root(true) . '/components/com_contentbuilder/assets/js/contentbuilder.js');

$___getpost = 'post';
$___tableOrdering = "Joomla.tableOrdering = function";
?>
<?php Factory::getApplication()->getDocument()->addStyleDeclaration($this->theme_css); ?>
<?php Factory::getApplication()->getDocument()->addScriptDeclaration($this->theme_js); ?>
<style type="text/css">
	.cbPagesCounter {
		float: left;
		padding-right: 10px;
		padding-top: 4px;
	}
</style>
<script language="javascript" type="text/javascript">
	<!--
	<?php echo $___tableOrdering; ?>(order, dir, task) {
		var form = document.adminForm;
		form.limitstart.value = <?php echo CBRequest::getInt('limitstart', 0) ?>;
		form.filter_order.value = order;
		form.filter_order_Dir.value = dir;
		document.adminForm.submit(task);
	};
	function contentbuilder_selectAll(checker) {
		for (var i = 0; i < document.adminForm.elements.length; i++) {
			if (document.adminForm.elements[i].name == 'cid[]') {
				if (checker.checked) {
					document.adminForm.elements[i].checked = true;
				} else {
					document.adminForm.elements[i].checked = false;
				}
			}
		}
	}
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

<?php
/* 2024-01-08 GIL - Increase size of XLS logo, change fawafonts "fa fa-file-excel" to "fa fa-solid fa-2x fa-file-excel" */
if ($this->export_xls):
	?>
	<div class="mb-3" style="float: right; text-align: right;">
		<a
			href="<?php echo Route::_('index.php?option=com_contentbuilder&view=export&id=' . CBRequest::getInt('id', 0) . '&type=xls&format=raw&tmpl=component'); ?>"><i
				class="fa fa-solid fa-2x fa-file-excel"></i></a>
	</div>
	<div style="clear: both;"></div>
	<?php
endif;
?>
<?php if ($this->page_title): ?>
	<h1 class="contentheading">
		<?php echo $this->page_title; ?>
	</h1>
<?php endif; ?>
<?php echo $this->intro_text; ?>
<div style="float: right; text-align: right;">
	<?php
	/** XDA+GN / BEGIN remove, Hide NEW button
	if ($new_allowed) {
		?>
		<button class="button btn btn-sm btn-primary cbButton cbNewButton"
			onclick="location.href='<?php echo Route::_('index.php?option=com_contentbuilder&view=edit&backtolist=1&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&record_id=0&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order')); ?>'"><?php echo Text::_('COM_CONTENTBUILDER_NEW'); ?></button>
		<?php
	}
	-- END of BEGIN - NEW BUTTON */
	?>
	<?php
	if ($delete_allowed) {
		?>
		<button class="button btn btn-sm btn-primary cbButton cbDeleteButton" onclick="contentbuilder_delete();">
			<?php echo Text::_('COM_CONTENTBUILDER_DELETE'); ?>
		</button>
		<?php
	}
	if ($delete_allowed || $new_allowed) {
		?>
		<div style="padding-bottom: 10px;"></div>
		<?php
	}
	?>
</div>
<div style="clear: both;"></div>

<!-- 2023-12-19 XDA / GIL - BEGIN - Fix
<form action="index.php" method=<php echo $___getpost;?>" name="adminForm" id
# Bug CB Joomla 4 (march 2023) - fix error search, delete, pagination, 404 error 
Replace line 144 of components/com_contentbuilder/views/list/tmpl/default.php
# by this block -->
<form action="
<?php
$szRoute = 'index.php?option=com_contentbuilder'
	. '&Itemid=' . CBRequest::getInt('Itemid', 0)
	. '&limitstart=' . CBRequest::getInt('limitstart', 0)
	. '&filter_order=' . CBRequest::getCmd('filter_order')
	. '&id=' . CBRequest::getInt('id');
echo Route::_($szRoute); ?>" method="<?php echo $___getpost; ?>" name="adminForm" id="adminForm">
	<!-- 2023-12-19 END -->
	<div style="overflow-x: auto;">
		<table class="cbFilterTable" width="100%">
			<tr>
				<td>
					<?php

					if (
						$state_allowed && count($this->states) ||
						$publish_allowed ||
						$language_allowed
					) {
						echo Text::_('COM_CONTENTBUILDER_BULK_OPTIONS') . '&nbsp;';
					}
					?>
					<?php
					if ($state_allowed && count($this->states)) {
						?>
						<select class="form-select-sm mb-0" style="max-width: 100px;" name="list_state">
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
						<button style="margin-bottom: 0.25rem;"
							class="button btn btn-sm btn-primary cbButton cbSearchButton" onclick="contentbuilder_state();">
							<?php echo Text::_('COM_CONTENTBUILDER_SET'); ?>
						</button>
						<?php
					}
					?>
					<?php
					if ($publish_allowed) {
						?>
						<select class="form-select-sm" style="max-width: 100px;" name="list_publish">
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
						<button style="margin-bottom: 0.25rem;"
							class="button btn btn-sm btn-primary cbButton cbSearchButton"
							onclick="contentbuilder_publish();">
							<?php echo Text::_('COM_CONTENTBUILDER_SET'); ?>
						</button>
						<?php
					}
					?>
					<?php
					if ($language_allowed) {
						?>
						<select class="form-select-sm" style="max-width: 100px;" name="list_language">
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
						<button style="margin-bottom: 0.25rem;"
							class="button btn btn-sm btn-primary cbButton cbSearchButton"
							onclick="contentbuilder_language();">
							<?php echo Text::_('COM_CONTENTBUILDER_SET'); ?>
						</button>
						<?php
					}
					?>
				</td>
			</tr>

			<tr>
				<?php
				if ($this->display_filter) {
					?>
					<td>
						<?php echo Text::_('COM_CONTENTBUILDER_FILTER') . '&nbsp;'; ?>
						<input class="form-control form-control-sm" type="text" id="contentbuilder_filter" name="filter"
							value="<?php echo $this->escape($this->lists['filter']); ?>" class="inputbox"
							onchange="document.adminForm.submit();" />
						<?php
						if ($this->list_state && count($this->states)) {
							?>
							<select class="form-select-sm" style="max-width: 100px;" name="list_state_filter"
								id="list_state_filter" onchange="document.adminForm.submit();">
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
							<?php
						}

						if ($this->list_publish && $publish_allowed) {
							?>

							<select class="form-select-sm" style="max-width: 100px;" name="list_publish_filter"
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
							<?php
						}

						if ($this->list_language) {
							?>
							<select class="form-select-sm" style="max-width: 100px;" name="list_language_filter"
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
							<?php
						}
						?>

						<button style="margin-bottom: 0.25rem;" type="submit"
							class="button btn btn-sm btn-primary cbButton cbSearchButton" id="cbSearchButton"
							onclick="document.adminForm.submit();">
							<?php echo Text::_('COM_CONTENTBUILDER_SEARCH') ?>
						</button>
						<button style="margin-bottom: 0.25rem;" class="button btn btn-sm btn-primary cbButton cbResetButton"
							onclick="document.getElementById('contentbuilder_filter').value='';<?php echo $this->list_language && count($this->languages) ? "if(document.getElementById('list_language_filter')) document.getElementById('list_language_filter').selectedIndex=0;" : ""; ?><?php echo $this->list_state && count($this->states) ? "if(document.getElementById('list_state_filter')) document.getElementById('list_state_filter').selectedIndex=0;" : ""; ?><?php echo $this->list_publish ? "if(document.getElementById('list_publish_filter')) document.getElementById('list_publish_filter').selectedIndex=0;" : ""; ?>document.adminForm.submit();">
							<?php echo Text::_('COM_CONTENTBUILDER_RESET') ?>
						</button>
					</td>
					<?php
				}
				?>
			</tr>
		</table>
		<table class="mt-3 table table-striped">
			<thead>
				<tr>
					<?php
					if ($this->show_id_column) {
						?>
						<th class="sectiontableheader hidden-phone" width="5">
							<?php echo HTMLHelper::_('grid.sort', htmlentities('COM_CONTENTBUILDER_ID', ENT_QUOTES, 'UTF-8'), 'colRecord', $this->lists['order_Dir'], $this->lists['order']); ?>
						</th>
						<?php
					}

					if ($this->select_column && ($delete_allowed || $state_allowed || $publish_allowed)) {
						?>
						<th class="sectiontableheader hidden-phone" width="20">
							<input class="contentbuilder_select_all form-check-input" type="checkbox"
								onclick="contentbuilder_selectAll(this);" />
						</th>
						<?php
					}

					if ($this->edit_button && $edit_allowed) {
						?>
						<th class="sectiontableheader" width="20">
							<?php echo Text::_('COM_CONTENTBUILDER_EDIT'); ?>
						</th>
						<?php
					}

					if ($this->list_state) {
						?>
						<th class="sectiontableheader hidden-phone">
							<?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?>
						</th>
						<?php
					}

					if ($this->list_publish && $publish_allowed) {
						?>
						<th class="sectiontableheader" width="20">
							<?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED'); ?>
						</th>
						<?php
					}

					if ($this->list_language) {
						?>
						<th class="sectiontableheader hidden-phone" width="20">
							<?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?>
						</th>
						<?php
					}

					if ($this->list_article) {
						?>
						<th class="sectiontableheader hidden-phone">
							<?php echo HTMLHelper::_('grid.sort', htmlentities('COM_CONTENTBUILDER_ARTICLE', ENT_QUOTES, 'UTF-8'), 'colArticleId', $this->lists['order_Dir'], $this->lists['order']); ?>
						</th>
						<?php
					}

					if ($this->list_author) {
						?>
						<th class="sectiontableheader hidden-phone">
							<?php echo HTMLHelper::_('grid.sort', htmlentities('COM_CONTENTBUILDER_AUTHOR', ENT_QUOTES, 'UTF-8'), 'colAuthor', $this->lists['order_Dir'], $this->lists['order']); ?>
						</th>
						<?php
					}

					if ($this->list_rating) {
						?>
						<th class="sectiontableheader hidden-phone">
							<?php echo HTMLHelper::_('grid.sort', htmlentities('COM_CONTENTBUILDER_RATING', ENT_QUOTES, 'UTF-8'), 'colRating', $this->lists['order_Dir'], $this->lists['order']); ?>
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
							<th class="sectiontableheader<?php echo $hidden; ?>">
								<?php echo HTMLHelper::_('grid.sort', nl2br(htmlentities(contentbuilder_wordwrap($label, 20, "\n", true), ENT_QUOTES, 'UTF-8')), "col$reference_id", $this->lists['order_Dir'], $this->lists['order']); ?>
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
				$link = Route::_('index.php?option=com_contentbuilder&view=details&id=' . $this->form_id . '&record_id=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
				$edit_link = Route::_('index.php?option=com_contentbuilder&view=edit&backtolist=1&id=' . $this->form_id . '&record_id=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
				$publish_link = Route::_('index.php?option=com_contentbuilder&view=edit&task=edit.publish&backtolist=1&id=' . $this->form_id . '&list_publish=1&cid[]=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
				$unpublish_link = Route::_('index.php?option=com_contentbuilder&view=edit&task=edit.publish&backtolist=1&id=' . $this->form_id . '&list_publish=0&cid[]=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
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
							<a href="<?php echo $edit_link; ?>"><img
									src="<?php Uri::root(true) ?>components/com_contentbuilder/images/edit.png" border="0"
									width="18" height="18" /></a>
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
							<div class="pagination pagination-toolbar">
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
								<?php echo $pages_links; ?>
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
