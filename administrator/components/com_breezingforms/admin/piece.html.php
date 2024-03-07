<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\HTML\HTMLHelper;

class HTML_facileFormsPiece
{
	static function edit($option, $pkg, &$row, &$typelist)
	{
		global $ff_mossite, $ff_admsite, $ff_config;
		$action = $row->id ? BFText::_('COM_BREEZINGFORMS_PIECES_EDITPIECE') : BFText::_('COM_BREEZINGFORMS_PIECES_ADDPIECE');
		JToolBarHelper::custom('save', 'save.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_SAVE'), false);
		JToolBarHelper::custom('cancel', 'cancel.png', 'cancel_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_QUICKMODE_CLOSE'), false);
		?>
		<script type="text/javascript" src="<?php echo $ff_admsite; ?>/admin/areautils.js"></script>
		<script type="text/javascript">
						<!--
						function checkIdentifier(value)
						{
							var invalidChars = /\W/;
							var error = '';
							if (value == '')
								error += "<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_ENTERNAME'); ?>\n";
							else
			if (invalidChars.test(value))
				error += "<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_ENTERIDENT'); ?>\n";
			return error;
						} // checkIdentifier

			function submitbutton(pressbutton) {
				var form = document.adminForm;
				var error = '';
				if (pressbutton != 'cancel') {
					error += checkIdentifier(form.name.value, 'name');
					if (form.title.value == '') error += "<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_ENTTITLE'); ?>\n";
				} // if
				if (error != '')
					alert(error);
				else
					submitform(pressbutton);
			} // submitbutton

			onload = function () {
				document.adminForm.title.focus();
			} // onload
			//-->
		</script>
		<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
			<table cellpadding="4" cellspacing="1" border="0" class="adminform" style="width:100%;">
				<tr>
					<td></td>
					<td nowrap>
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_TITLE'); ?>:
					</td>
					<td nowrap>
						<input type="text" size="50" maxlength="50" name="title" value="<?php echo $row->title; ?>"
							class="inputbox" />
						<?php
						echo '<span><span title="' . bf_ToolTipText(BFText::_('COM_BREEZINGFORMS_PIECES_TIPTITLE')) . '" class="icon-question-circle hasTooltip" aria-hidden="true"></span></span>';
						?>
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap>
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_PUBLISHED'); ?>:
					</td>
					<td nowrap>
						<?php echo HTMLHelper::_('select.booleanlist', "published", "", $row->published); ?>
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap>
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_PACKAGE'); ?>:
					</td>
					<td nowrap>
						<input type="text" size="30" maxlength="30" id="package" name="package"
							value="<?php echo $row->package; ?>" class="inputbox" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap>
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_NAME'); ?>:
					</td>
					<td nowrap>
						<input type="text" size="30" maxlength="30" id="name" name="name" value="<?php echo $row->name; ?>"
							class="inputbox" />
						<?php
						echo '<span><span title="' . bf_ToolTipText(BFText::_('COM_BREEZINGFORMS_PIECES_TIPNAME')) . '" class="icon-question-circle hasTooltip" aria-hidden="true"></span></span>';
						?>
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap>
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_TYPE'); ?>:
					</td>
					<td nowrap>
						<select id="type" name="type" class="inputbox" size="1">
							<?php
							for ($t = 0; $t < count($typelist); $t++) {
								$tl = $typelist[$t];
								$selected = '';
								if ($tl[0] == $row->type)
									$selected = ' selected';
								echo '<option value="' . $tl[0] . '"' . $selected . '>' . $tl[1] . '</option>';
							} // for
							?>
						</select>
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap colspan="2">
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_DESCRIPTION'); ?>:
						<a href="javascript:void(0);"
							onClick="textAreaResize('description',<?php echo $ff_config->areasmall; ?>);">[
							<?php echo $ff_config->areasmall; ?>]
						</a>
						<a href="javascript:void(0);"
							onClick="textAreaResize('description',<?php echo $ff_config->areamedium; ?>);">[
							<?php echo $ff_config->areamedium; ?>]
						</a>
						<a href="javascript:void(0);"
							onClick="textAreaResize('description',<?php echo $ff_config->arealarge; ?>);">[
							<?php echo $ff_config->arealarge; ?>]
						</a>
						<br />
						<textarea wrap="off" name="description" style="width:100%;" rows="<?php echo $ff_config->areasmall; ?>"
							class="inputbox"><?php echo $row->description; ?></textarea>
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap colspan="2">
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_CODE'); ?>:
						<br />

						<?php
						$params = array('syntax' => 'javascript');
						$editor = Editor::getInstance('codemirror');
						echo $editor->display('code', $row->code, '100%', 300, 40, 20, false, 'code', null, null, $params);
						?>

					</td>
					<td></td>
				</tr>
			</table>
			<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
			<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="act" value="managepieces" />
		</form>
		<?php
	} // edit

	static function typeName($type)
	{
		switch ($type) {
			case 'Untyped':
				return BFText::_('COM_BREEZINGFORMS_PIECES_UNTYPED');
			case 'Before Form':
				return BFText::_('COM_BREEZINGFORMS_PIECES_BEFOREFORM');
			case 'After Form':
				return BFText::_('COM_BREEZINGFORMS_PIECES_AFTERFORM');
			case 'Begin Submit':
				return BFText::_('COM_BREEZINGFORMS_PIECES_BEGINSUBMIT');
			case 'End Submit':
				return BFText::_('COM_BREEZINGFORMS_PIECES_ENDSUBMIT');
			default:
				;
		} // switch
		return '???';
	} // typeName

	static function listitems($option, &$rows, &$pkglist)
	{
		global $ff_config, $ff_version;
		?>
		<script type="text/javascript">
							<!--
							function submitbutton(pressbutton)
							{
								var form = document.adminForm;
								switch (pressbutton) {
									case 'copy':
									case 'publish':
									case 'unpublish':
									case 'remove':
										if (form.boxchecked.value==0) {
											alert("<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_SELPIECESFIRST'); ?>");
			return;
										} // if
			break;
									default:
			break;
								} // switch
			if (pressbutton == 'remove')
				if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_ASKDELETE'); ?>")) return;
			if (pressbutton == '' && form.pkgsel.value == '')
				form.pkg.value = '- blank -';
			else
				form.pkg.value = form.pkgsel.value;
			Joomla.submitform(pressbutton);
							} // submitbutton

			<?php

			JToolBarHelper::custom('new', 'new.png', 'new_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_NEW'), false);
			JToolBarHelper::custom('copy', 'copy.png', 'copy_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_COPY'), false);
			JToolBarHelper::custom('publish', 'publish.png', 'publish_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_PUBLISH'), false);
			JToolBarHelper::custom('unpublish', 'unpublish.png', 'unpublish_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_UNPUBLISH'), false);
			JToolBarHelper::custom('remove', 'delete.png', 'delete_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_DELETE'), false);
			?>

			function listItemTask(id, task) {
				var f = document.adminForm;
				cb = eval('f.' + id);
				if (cb) {
					for (i = 0; true; i++) {
						cbx = eval('f.cb' + i);
						if (!cbx) break;
						cbx.checked = false;
					} // for
					cb.checked = true;
					f.boxchecked.value = 1;
					Joomla.submitbutton(task);
				}
				return false;
			} // listItemTask
			//-->
		</script>
		<form action="index.php" method="post" name="adminForm" id="adminForm">

			<label class="bfPackageSelector">
				<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_PACKAGE'); ?>
				<select id="pkgsel" name="pkgsel" class="inputbox" size="1" onchange="submitbutton('');">
					<?php
					if (count($pkglist))
						foreach ($pkglist as $pkg) {
							$selected = '';
							if ($pkg[0])
								$selected = ' selected';
							echo '<option value="' . $pkg[1] . '"' . $selected . '>' . $pkg[1] . '&nbsp;</option>';
						} // foreach
					?>
				</select>
			</label>

			<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist table table-striped">
				<tr>
					<th style="width: 25px;" nowrap align="right">ID</th>
					<th style="width: 25px;" nowrap align="center"><input type="checkbox" name="toggle" value=""
							onclick="Joomla.checkAll(this);" /></th>
					<th align="left">
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_TITLE'); ?>
					</th>
					<th align="left">
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_NAME'); ?>
					</th>
					<th align="left">
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_TYPE'); ?>
					</th>
					<th align="left">
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_DESCRIPTION'); ?>
					</th>
					<th align="center">
						<?php echo BFText::_('COM_BREEZINGFORMS_PIECES_PUBLISHED'); ?>
					</th>
				</tr>
				<?php
				$k = 0;
				for ($i = 0; $i < count($rows); $i++) {
					$row = $rows[$i];
					$desc = $row->description;
					if (strlen($desc) > $ff_config->limitdesc)
						$desc = substr($desc, 0, $ff_config->limitdesc) . '...';
					?>
					<tr class="row<?php echo $k; ?>">
						<td nowrap valign="top" align="right">
							<?php echo $row->id; ?>
						</td>
						<td nowrap valign="top" align="center"><input type="checkbox" id="cb<?php echo $i; ?>" name="ids[]"
								value="<?php echo $row->id; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
						<td valign="top" align="left"><a href="#edit" onclick="return listItemTask('cb<?php echo $i; ?>','edit')">
								<?php echo $row->title; ?>
							</a></td>
						<td valign="top" align="left">
							<?php echo $row->name; ?>
						</td>
						<td valign="top" align="left">
							<?php echo HTML_facileFormsPiece::typeName($row->type); ?>
						</td>
						<td valign="top" align="left">
							<?php echo htmlspecialchars($desc, ENT_QUOTES); ?>
						</td>
						<td valign="top" align="center">
							<?php
							if ($row->published == "1") {
								?><a class="tbody-icon active" href="javascript:void(0);"
									onClick="return listItemTask('cb<?php echo $i; ?>','unpublish')"><span class="icon-publish"
										aria-hidden="true"></span></a>
								<?php
							} else {
								?><a class="tbody-icon" href="javascript:void(0);"
									onClick="return listItemTask('cb<?php echo $i; ?>','publish')"><span class="icon-unpublish"
										aria-hidden="true"></span></a>
								<?php
							} // if
							?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				} // for
				?>
			</table>
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="act" value="managepieces" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="pkg" value="" />
		</form>
		<?php
	} // listitems

} // class HTML_facileFormsPiece
?>