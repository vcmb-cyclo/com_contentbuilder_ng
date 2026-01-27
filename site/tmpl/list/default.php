<?php

/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderHelper;

$frontend = Factory::getApplication()->isClient('site');
$language_allowed = ContentbuilderLegacyHelper::authorizeFe('language');
$edit_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('edit') : ContentbuilderLegacyHelper::authorize('edit');
$delete_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('delete') : ContentbuilderLegacyHelper::authorize('delete');
$view_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('view') : ContentbuilderLegacyHelper::authorize('view');
$new_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('new') : ContentbuilderLegacyHelper::authorize('new');
$state_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('state') : ContentbuilderLegacyHelper::authorize('state');
$publish_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('publish') : ContentbuilderLegacyHelper::authorize('publish');
$rating_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('rating') : ContentbuilderLegacyHelper::authorize('rating');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

// Charge le manifeste joomla.asset.json du composant
$wa->getRegistry()->addExtensionRegistryFile('com_contentbuilder');

$wa->useScript('jquery');
$wa->useScript('com_contentbuilder.contentbuilder');

$___getpost = 'post';
$___tableOrdering = "Joomla.tableOrdering = function";
?>
<?php Factory::getApplication()->getDocument()->addStyleDeclaration($this->theme_css); ?>
<?php Factory::getApplication()->getDocument()->addScriptDeclaration($this->theme_js); ?>
<script>
	Joomla.tableOrdering = function(order, dir, task) {
		var form = document.getElementById('adminForm');
		if (!form) return;

		form.limitstart.value = 0; // reset au tri
		form.filter_order.value = order;
		form.filter_order_Dir.value = dir;

		Joomla.submitform(task || '', form);
	};

	function contentbuilder_delete() {
		if (confirm('<?php echo Text::_('COM_CONTENTBUILDER_CONFIRM_DELETE_MESSAGE'); ?>')) {
			var form = document.getElementById('adminForm');
			document.getElementById('task').value = 'list.delete';
			Joomla.submitform('list.delete', form);
		}
	}

	function contentbuilder_state() {
		var form = document.getElementById('adminForm');
		document.getElementById('task').value = 'list.state';
		Joomla.submitform('list.state', form);
	}

	function contentbuilder_publish() {
		var form = document.getElementById('adminForm');
		document.getElementById('task').value = 'list.publish';
		Joomla.submitform('list.publish', form);
	}

	function contentbuilder_language() {
		var form = document.getElementById('adminForm');
		document.getElementById('task').value = 'list.language';
		Joomla.submitform('list.language', form);
	}

	document.addEventListener('DOMContentLoaded', function() {
		const form = document.getElementById('adminForm');
		if (!form) return;

		// Limit box select (name="limit")
		const limitSelect = form.querySelector('select[name="limit"]');
		if (limitSelect) {
			limitSelect.classList.add('form-select', 'form-select-sm');
			limitSelect.style.maxWidth = '120px';
			limitSelect.style.width = 'auto';
		}
	});
</script>

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
		<button class="btn btn-sm btn-primary"
			onclick="location.href='<?php echo Route::_('index.php?option=com_contentbuilder&task=edit.display&backtolist=1&id=' . Factory::getApplication()->input->getInt('id', 0) . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&record_id=0&filter_order=' . Factory::getApplication()->input->getCmd('filter_order')); ?>'"><?php echo Text::_('COM_CONTENTBUILDER_NEW'); ?></button>
		<?php
	}
	-- END of BEGIN - NEW BUTTON */
	?>
	<?php
	if ($delete_allowed) {
	?>
		<button class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1" onclick="contentbuilder_delete();" title="<?php echo Text::_('COM_CONTENTBUILDER_DELETE'); ?>">
			<i class="fa fa-trash" aria-hidden="true"></i>
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
Replace line 144 of media/com_contentbuilder/images/list/tmpl/default.php
# by this block -->
<form action="<?php echo Route::_('index.php?option=com_contentbuilder&task=list.display&id=' . (int) Factory::getApplication()->input->getInt('id') . '&Itemid=' . (int) Factory::getApplication()->input->getInt('Itemid', 0)); ?>"
	method="<?php echo $___getpost; ?>" name="adminForm" id="adminForm">

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
						<div class="d-inline-flex align-items-center gap-1 me-2">
							<select class="form-select form-select-sm" style="max-width: 100px;" name="list_state">
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
							<button class="btn btn-sm btn-primary" onclick="contentbuilder_state();">
								<?php echo Text::_('COM_CONTENTBUILDER_APPLY'); ?>
							</button>
						</div>
					<?php
					}
					?>
					<?php
					if ($publish_allowed) {
					?>
						<div class="d-inline-flex align-items-center gap-1 me-2">
							<select class="form-select form-select-sm" style="max-width: 100px;" name="list_publish">
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
							<button class="btn btn-sm btn-primary" onclick="contentbuilder_publish();">
								<?php echo Text::_('COM_CONTENTBUILDER_APPLY'); ?>
							</button>
						</div>
					<?php
					}
					?>
					<?php
					if ($language_allowed) {
					?>
						<div class="d-inline-flex align-items-center gap-1 me-2">
							<select class="form-select form-select-sm" style="max-width: 100px;" name="list_language">
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
							<button class="btn btn-sm btn-primary" onclick="contentbuilder_language();">
								<?php echo Text::_('COM_CONTENTBUILDER_APPLY'); ?>
							</button>
						</div>
					<?php
					}
					?>
				</td>
			</tr>

			<tr>
				<td>
					<div class="d-flex flex-wrap align-items-center gap-2">

						<!-- GAUCHE : filtre + selects + boutons (optionnel) -->
						<div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">

							<?php if ($this->display_filter) : ?>
								<div class="input-group input-group-sm" style="max-width: 520px;">
									<span class="input-group-text">
										<?php echo Text::_('COM_CONTENTBUILDER_FILTER'); ?>
									</span>

									<input
										type="text"
										class="form-control"
										id="contentbuilder_filter"
										name="filter"
										value="<?php echo $this->escape($this->lists['filter']); ?>"
										onchange="document.adminForm.submit();" />

									<button type="submit" class="btn btn-primary" id="cbSearchButton">
										<?php echo Text::_('COM_CONTENTBUILDER_SEARCH'); ?>
									</button>

									<button type="button" class="btn btn-outline-secondary"
										onclick="document.getElementById('contentbuilder_filter').value='';
                <?php echo $this->list_language && count($this->languages) ? "if(document.getElementById('list_language_filter')) document.getElementById('list_language_filter').selectedIndex=0;" : ""; ?>
                <?php echo $this->list_state && count($this->states) ? "if(document.getElementById('list_state_filter')) document.getElementById('list_state_filter').selectedIndex=0;" : ""; ?>
                <?php echo $this->list_publish ? "if(document.getElementById('list_publish_filter')) document.getElementById('list_publish_filter').selectedIndex=0;" : ""; ?>
                document.adminForm.submit();">
										<?php echo Text::_('COM_CONTENTBUILDER_RESET'); ?>
									</button>
								</div>
							<?php endif; ?>

							<?php if ($this->list_state && count($this->states)) : ?>
								<select class="form-select form-select-sm" style="max-width: 160px;"
									name="list_state_filter" id="list_state_filter"
									onchange="document.adminForm.submit();">
									<option value="0"> - <?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?> -</option>
									<?php foreach ($this->states as $state) : ?>
										<option value="<?php echo $state['id'] ?>" <?php echo $this->lists['filter_state'] == $state['id'] ? 'selected' : ''; ?>>
											<?php echo $state['title'] ?>
										</option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>

							<?php if ($this->list_publish && $publish_allowed) : ?>
								<select class="form-select form-select-sm" style="max-width: 190px;"
									name="list_publish_filter" id="list_publish_filter"
									onchange="document.adminForm.submit();">
									<option value="-1"> - <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED_UNPUBLISHED'); ?> -</option>
									<option value="1" <?php echo $this->lists['filter_publish'] == 1 ? 'selected' : ''; ?>>
										<?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED') ?>
									</option>
									<option value="0" <?php echo $this->lists['filter_publish'] == 0 ? 'selected' : ''; ?>>
										<?php echo Text::_('COM_CONTENTBUILDER_UNPUBLISHED') ?>
									</option>
								</select>
							<?php endif; ?>

							<?php if ($this->list_language) : ?>
								<select class="form-select form-select-sm" style="max-width: 160px;"
									name="list_language_filter" id="list_language_filter"
									onchange="document.adminForm.submit();">
									<option value=""> - <?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?> -</option>
									<?php foreach ($this->languages as $filter_language) : ?>
										<option value="<?php echo $filter_language; ?>" <?php echo $this->lists['filter_language'] == $filter_language ? 'selected' : ''; ?>>
											<?php echo $filter_language; ?>
										</option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>

						</div>

						<!-- DROITE : limitbox + excel (indépendants du filtre) -->
						<?php if ($this->show_records_per_page || $this->export_xls) : ?>
							<div class="d-flex align-items-center gap-2 ms-auto">

								<?php if ($this->show_records_per_page) : ?>
									<div style="max-width: 120px;">
										<?php echo $this->pagination->getLimitBox(); ?>
									</div>
								<?php endif; ?>

								<?php if ($this->export_xls) : ?>
									<a class="btn btn-sm btn-outline-success align-self-center"
										href="<?php echo Route::_('index.php?option=com_contentbuilder&view=export&id=' . (int) Factory::getApplication()->input->getInt('id', 0) . '&type=xls&format=raw&tmpl=component'); ?>"
										title="Export Excel">
										<i class="fa fa-solid fa-file-excel"></i>
									</a>
								<?php endif; ?>

							</div>
						<?php endif; ?>

					</div>
				</td>
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
								<?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_PUBLISHED'), 'colPublished', $this->lists['order_Dir'], $this->lists['order']); ?>
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
								<?php echo HTMLHelper::_('grid.sort', nl2br(htmlentities(ContentbuilderHelper::contentbuilder_wordwrap($label, 20, "\n", true), ENT_QUOTES, 'UTF-8')), "col$reference_id", $this->lists['order_Dir'], $this->lists['order']); ?>
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
				$link = Route::_('index.php?option=com_contentbuilder&task=details.display&id=' . $this->form_id . '&record_id=' . $row->colRecord . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : ''));
				$edit_link = Route::_('index.php?option=com_contentbuilder&task=edit.display&backtolist=1&id=' . $this->form_id . '&record_id=' . $row->colRecord . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : ''));
					$isPublished = isset($this->published_items[$row->colRecord]) && $this->published_items[$row->colRecord];
					$togglePublish = $isPublished ? 0 : 1;
					$toggle_link = Route::_('index.php?option=com_contentbuilder&task=edit.publish&backtolist=1&id=' . $this->form_id . '&list_publish=' . $togglePublish . '&cid[]=' . $row->colRecord . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : ''));
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
								<img
									src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>media/com_contentbuilder/images/edit.png"
									border="0"
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
								<?php
								$iconClass = $isPublished ? 'icon-publish text-success' : 'icon-unpublish text-danger';
								$iconTitle = $isPublished ? Text::_('JPUBLISHED') : Text::_('JUNPUBLISHED');
								?>
								<a class="btn btn-sm btn-link p-0" href="<?php echo $toggle_link; ?>" title="<?php echo $iconTitle; ?>">
									<span class="<?php echo $iconClass; ?>" aria-hidden="true"></span>
									<span class="visually-hidden"><?php echo $iconTitle; ?></span>
								</a>
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
							echo ContentbuilderLegacyHelper::getRating(Factory::getApplication()->input->getInt('id', 0), $row->colRecord, $row->colRating, $this->rating_slots, Factory::getApplication()->input->getCmd('lang', ''), $rating_allowed, $row->colRatingCount, $row->colRatingSum);
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
			} ?>
			<?php if ($this->pagination->pagesTotal > 1 || $this->show_records_per_page) : ?>
				<tfoot>
					<tr>
						<td colspan="1000">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
			<?php endif; ?>
		</table>
	</div>
	<?php
	if (Factory::getApplication()->input->get('tmpl', '', 'string') != '') {
	?>
		<input type="hidden" name="tmpl" value="<?php echo Factory::getApplication()->input->get('tmpl', '', 'string'); ?>" />
	<?php
	}
	?>
	<input type="hidden" name="option" value="com_contentbuilder" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="view" id="view" value="list" />
	<input type="hidden" name="Itemid" value="<?php echo Factory::getApplication()->input->getInt('Itemid', 0); ?>" />
	<input type="hidden" name="limitstart" value="<?php echo (int) $this->pagination->limitstart; ?>" />
	<input type="hidden" name="list[start]" value="<?php echo (int) $this->pagination->limitstart; ?>" />
	<input type="hidden" name="id" value="<?php echo Factory::getApplication()->input->getInt('id', 0) ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
