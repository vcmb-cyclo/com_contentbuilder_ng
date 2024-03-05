<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 5.0
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

require_once($ff_compath . '/facileforms.process.php');

class HTML_facileFormsElement
{
	static function newitem($option, $pkg, $form, $page)
	{
		$mainframe = Factory::getApplication();
		$ff_mossite = Uri::base();
		?>

		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script type="text/javascript"
			src="<?php echo $ff_mossite; ?>/components/com_breezingforms/libraries/js/overlib_mini.js"></script>
		<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
			<table cellpadding="4" cellspacing="1" border="0" class="adminform" style="width:300px;">
				<tr>
					<th colspan="5" class="title">BreezingForms -
						<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NEWTYPE'); ?>
					</th>
				</tr>
				<tr>
					<td></td>
					<td valign="top">
						<fieldset>
							<legend><strong>
									<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_STATICS'); ?>
								</strong></legend>
							<table cellpadding="4" cellspacing="1" border="0" class="adminform">
								<tr>
									<td nowrap><input type="radio" id="newtype1" name="newtype" value='Static Text/HTML'
											checked="checked" /><label for="newtype1">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_STATICTEXT'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype2" name="newtype" value='Rectangle' /><label
											for="newtype2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RECTANGLE'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype3" name="newtype" value='Image' /><label
											for="newtype3">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_IMAGE'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype4" name="newtype" value='Tooltip' /><label
											for="newtype4">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TOOLTIP'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype5" name="newtype" value='Captcha' /><label
											for="newtype4">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CAPTCHA'); ?>
										</label></td>
								</tr>
							</table>
						</fieldset>
						<fieldset>
							<legend><strong>
									<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BUTTONS'); ?>
								</strong></legend>
							<table cellpadding="4" cellspacing="1" border="0" class="adminform">
								<tr>
									<td nowrap><input type="radio" id="newtype10" name="newtype" value='Regular Button' /><label
											for="newtype10">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_REGBUTTON'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype11" name="newtype" value='Graphic Button' /><label
											for="newtype11">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_GRAPHBUTTON'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype12" name="newtype" value='Icon' /><label
											for="newtype12">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ICON'); ?>
										</label></td>
								</tr>
							</table>
						</fieldset>
					</td>
					<td valign="top">
						<fieldset>
							<legend><strong>
									<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_INPUTS'); ?>
								</strong></legend>
							<table cellpadding="4" cellspacing="1" border="0" class="adminform">
								<tr>
									<td nowrap><input type="radio" id="newtype20" name="newtype" value='Checkbox' /><label
											for="newtype20">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CHECKBOX'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype21" name="newtype" value='Radio Button' /><label
											for="newtype21">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RADIO'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype22" name="newtype" value='Text' /><label
											for="newtype22">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TEXT'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype23" name="newtype" value='Textarea' /><label
											for="newtype23">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TEXTAREA'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype24" name="newtype" value='Select List' /><label
											for="newtype24">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELECT'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype25" name="newtype" value='Query List' /><label
											for="newtype25">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_QUERYLIST'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype26" name="newtype" value='File Upload' /><label
											for="newtype26">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_UPLOAD'); ?>
										</label></td>
								</tr>
								<tr>
									<td nowrap><input type="radio" id="newtype27" name="newtype" value='Hidden Input' /><label
											for="newtype27">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_HIDDEN'); ?>
										</label></td>
								</tr>
							</table>
						</fieldset>
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap colspan="3" style="text-align:right">
						<input class="btn btn-primary" onclick="submitbutton('newedit');" type="submit"
							value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_TOOLBAR_CONTINUE'), ENT_QUOTES, 'UTF-8'); ?>" />
						&nbsp;&nbsp;
						<input class="btn btn-primary" onclick="submitbutton('cancel');" type="submit"
							value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_TOOLBAR_CANCEL'), ENT_QUOTES, 'UTF-8'); ?>" />
					</td>
					<td></td>
				</tr>
			</table>
			<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="act" value="editpage" />
			<input type="hidden" name="form" value="<?php echo $form; ?>" />
			<input type="hidden" name="page" value="<?php echo $page; ?>" />
		</form>
		<?php
	} // newitem

	static function displayType($type)
	{
		switch ($type) {
			case 'Static Text/HTML':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_STATICTEXT');
				break;
			case 'Rectangle':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_RECTANGLE');
				break;
			case 'Image':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_IMAGE');
				break;
			case 'Tooltip':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_TOOLTIP');
				break;
			case 'Query List':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_QUERYLIST');
				break;
			case 'Regular Button':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_REGBUTTON');
				break;
			case 'Graphic Button':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_GRAPHBUTTON');
				break;
			case 'Icon':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_ICON');
				break;
			case 'Checkbox':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_CHECKBOX');
				break;
			case 'Radio Button':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_RADIO');
				break;
			case 'Select List':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELECT');
				break;
			case 'Text':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_TEXT');
				break;
			case 'Textarea':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_TEXTAREA');
				break;
			case 'File Upload':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_UPLOAD');
				break;
			case 'Hidden Input':
				$disptype = BFText::_('COM_BREEZINGFORMS_ELEMENTS_HIDDEN');
				break;
			default:
				$disptype = $type;
				break;
		} // switch
		return $disptype;
	} // displayType

	static function edit($option, $tabpane, $pkg, &$row, &$lists)
	{
		global $ff_mossite, $ff_admsite, $ff_config;
		$mainframe = Factory::getApplication();
		$ff_mossite = Uri::base();
		$action = $row->id ? BFText::_('COM_BREEZINGFORMS_ELEMENTS_EDIT') : BFText::_('COM_BREEZINGFORMS_ELEMENTS_ADD');

		$hasInit = false;
		switch ($row->type) {
			case 'Static Text/HTML':
			case 'Rectangle':
			case 'Image':
			case 'Tooltip':
			case 'Query List':
			case 'Regular Button':
			case 'Graphic Button':
			case 'Icon':
			case 'Captcha':
				break;
			default:
				$hasInit = true;
		} // switch

		$hasAction = false;
		switch ($row->type) {
			case 'Static Text/HTML':
			case 'Rectangle':
			case 'Image':
			case 'Tooltip':
			case 'Query List':
			case 'Hidden Input':
			case 'Captcha':
				break;
			default:
				$hasAction = true;
		} // switch

		$hasValidation = false;
		switch ($row->type) {
			case 'Static Text/HTML':
			case 'Rectangle':
			case 'Image':
			case 'Tooltip':
			case 'Query List':
			case 'Regular Button':
			case 'Graphic Button':
			case 'Icon':
			case 'Captcha':
				break;
			default:
				$hasValidation = true;
		} // switch
		?>
		<script type="text/javascript" src="<?php echo $ff_admsite; ?>/admin/areautils.js"></script>
		<script type="text/javascript">
				<!--
				function checkIdentifier(value, name)
				{
					var invalidChars = /\W/;
					var error = '';
					if (value == '')
						error += "<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ENTNAME'); ?>\n";
					else
			if (invalidChars.test(value))
				error += "<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ENTIDENT'); ?>\n";
			return error;
				} // checkIdentifier

			var bf_submitbutton = function (pressbutton) {
				var form = document.adminForm;
				var error = '';
				if (pressbutton != 'cancel') {
					if (form.title.value == '')
						error += "<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TITLEEMPTY'); ?>\n";
					error += checkIdentifier(form.name.value, 'name');
				} // if

				if (error != '') {
					alert(error);
				} else {
					<?php
					if ($row->type == 'Query List')
						echo "\t\t\t\tsaveQueryList();\n";
					if ($row->type == 'Static Text/HTML' && $ff_config->wysiwyg) {
						jimport('joomla.html.editor');
						$editor = Factory::getEditor();
						echo $editor->save('data1');
					}
					?>
					submitform(pressbutton);
				} // if
			}; // submitbutton

			if (typeof Joomla != "undefined") {
				Joomla.submitbutton = bf_submitbutton;
			}

			submitbutton = bf_submitbutton;

			<?php
			if ($row->type == 'Graphic Button' || $row->type == 'Icon') {
				?>
				function dispcaptiontext(value) {
					if (document.getElementById)
						if (document.getElementById('captiontext'))
							switch (value) {
								case '0':
									document.getElementById('captiontext').style.display = 'none';
									break;
								default:
									document.getElementById('captiontext').style.display = '';
							} // switch
				} // dispcaptiontext

				<?php
			}

			if ($row->type == 'Tooltip') {
				?>
				function disptooltipurl(value) {
					if (document.getElementById)
						if (document.getElementById('tooltipurl'))
							switch (value) {
								case '0':
								case '1':
									document.getElementById('tooltipurl').style.display = 'none';
									break;
								default:
									document.getElementById('tooltipurl').style.display = '';
							} // switch
				} // disptooltipurl

				<?php
			} // if tooltip
	
			if ($row->type == 'Query List') {
				?>
				function loadQueryList() {
					var form = document.adminForm;
					var text = trim(form.data1.value);
					var rows = text.split('\n');
					var rcnt = rows.length;
					var r;
					for (r = 0; r < rcnt; r++) rows[r] = trim(rows[r]);
					if (rcnt > 0) form.border.value = rows[0];
					if (rcnt > 1) form.cellspacing.value = rows[1];
					if (rcnt > 2) form.cellpadding.value = rows[2];
					if (rcnt > 3) form.trhclass.value = rows[3];
					if (rcnt > 4) form.tr1class.value = rows[4];
					if (rcnt > 5) form.tr2class.value = rows[5];
					if (rcnt > 6) form.trfclass.value = rows[6];
					if (rcnt > 7) form.tdfclass.value = rows[7];
					if (rcnt > 8 && rows[8] != '') form.pagenav.options[rows[8]].selected = true;
					qcolUnpack();
				} // loadQueryList

				function saveQueryList() {
					var form = document.adminForm;
					form.data1.value =
						trim(form.border.value) + '\n' +
						trim(form.cellspacing.value) + '\n' +
						trim(form.cellpadding.value) + '\n' +
						trim(form.trhclass.value) + '\n' +
						trim(form.tr1class.value) + '\n' +
						trim(form.tr2class.value) + '\n' +
						trim(form.trfclass.value) + '\n' +
						trim(form.tdfclass.value) + '\n' +
						trim(form.pagenav.value) + '\n';
					qcolPack();
				} // saveQueryList

				function showpagenav(height) {
					if (parseInt(height))
						document.getElementById('pagenavrow').style.display = '';
					else
						document.getElementById('pagenavrow').style.display = 'none';
				} // showpagenav

				function createQueryCode() {
					form = document.adminForm;
					name = form.name.value;
					if (name == '') {
						alert('Please enter the element name first.');
						return;
					} // if
					if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ASKCREATEQUERY'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>")) return;
					code =
						"global $database;\n" +
						"\n" +
						"$database->setQuery(\n" +
						"    \"select id, name, username, email \".\n" +
						"    \"from #__users \".\n" +
						"    \"order by id\"\n" +
						");\n" +
						"$rows = $database->loadObjectList();\n";
					oldcode = form.data2.value;
					if (oldcode != '')
						form.data2.value =
							code +
							"\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
							oldcode;
					else
						form.data2.value = code;
					codeAreaChange(form.data2);
				} // createQueryCode

				function trim(s) {
					while (s.length > 0 && (s.charAt(0) == ' ' || s.charAt(0) == '\n' || s.charAt(0) == '\r'))
						s = s.substr(1, s.length - 1);
					while (s.length > 0 && (s.charAt(s.length - 1) == ' ' || s.charAt(s.length - 1) == '\n' || s.charAt(s.length - 1) == '\r'))
						s = s.substr(0, s.length - 1);
					return s;
				} // trim

				function expstring(text) {
					text = trim(text);
					var i;
					var o = '';
					for (i = 0; i < text.length; i++) {
						c = text.charAt(i);
						switch (c) {
							case '&': o += '\\x26'; break;
							case '<': o += '\\x3C'; break;
							case '>': o += '\\x3E'; break;
							case '\'': o += '\\x27'; break;
							case '\\': o += '\\x5C'; break;
							case '"': o += '\\x22'; break;
							case '\n': o += '\\n'; break;
							case '\r': o += '\\r'; break;
							default: o += c;
						} // switch
					} // for
					return o;
				} // expstring

				function htmlchars(text) {
					var i;
					var o = '';
					for (i = 0; i < text.length; i++) {
						c = text.charAt(i);
						switch (c) {
							case '&': o += '&amp;'; break;
							case '<': o += '&lt;'; break;
							case '>': o += '&gt;'; break;
							case '"': o += '&quot;'; break;
							default: o += c;
						} // switch
					} // for
					return o;
				} // htmlchars

				function impstring(text) {
					var str = '';
					var ss = 0;
					var s;
					var tl = text.length;
					var hexdigs = "0123456789abcdefABCDEF";
					while (ss < tl) {
						s = text.charAt(ss++);
						if (s == '\\') {
							if (ss < tl) s = text.charAt(ss++); else s = 0;
							switch (s) {
								case 0: break;
								case 'e': str += '\33'; break;
								case 't': str += '\t'; break;
								case 'r': str += '\r'; break;
								case 'n': str += '\n'; break;
								case 'f': str += '\f'; break;
								case 'x': {
									if (ss < tl) s = text.charAt(ss++); else s = 0;
									var ch = '';
									while (hexdigs.indexOf(s) >= 0 && ch.length < 2) {
										ch += s;
										if (ss < tl) s = text.charAt(ss++); else s = 0;
									} // while
									while (ch.length < 2) ch = '0' + ch;
									str += unescape('%' + ch);
									if (s) ss--;
									break;
								}
								default:
									str += s;
							} // switch
						} else
							str += s;
					} // while
					return str;
				} // impstring

				var qcolRows = new Array();
				var qcolRowCnt = 0;
				var qcolIndex = 0;

				var qcolTitle = 0;
				var qcolName = 1;
				var qcolClass1 = 2;
				var qcolClass2 = 3;
				var qcolClass3 = 4;
				var qcolWidth = 5;
				var qcolWidthmd = 6;
				var qcolThspan = 7;
				var qcolThalign = 8;
				var qcolThvalign = 9;
				var qcolThwrap = 10;
				var qcolAlign = 11;
				var qcolValign = 12;
				var qcolWrap = 13;
				var qcolValue = 14;
				var qcolSize = 15;

				var qcolCellCheckbox = 0;
				var qcolCellTitle = 1;
				var qcolCellName = 2;
				var qcolCellThattribs = 3;
				var qcolCellAttributes = 4;
				var qcolCellWidth = 5;
				var qcolCellOrderup = 6;
				var qcolCellOrderdown = 7;
				var qcolCellCount = 8;

				function qcolPack() {
					var r;
					var text = '';
					for (r = 0; r < qcolRowCnt; r++) {
						var c;
						var row = qcolRows[r];
						for (c = 0; c < row.length; c++) {
							if (c > 0) text += '&';
							text += expstring(row[c]);
						} // for
						text += '\n';
					} // for
					document.adminForm.data3.value = text;
				} // qcolPack

				function qcolUnpack() {
					var text = trim(document.adminForm.data3.value);
					var rows = text.split('\n');
					var r;
					qcolRowCnt = 0;
					for (r = 0; r < rows.length; r++) {
						if (rows[r] != '') {
							var vals = rows[r].split('&');
							var v;
							for (v = 0; v < vals.length; v++)
								vals[v] = impstring(vals[v]);
							qcolRows[qcolRowCnt++] = vals;
						} // if
					} // for
					qcolDisplay();
				} // qcolUnpack

				function qcolCheckAll(checked) {
					var r;
					for (r = 0; r < qcolRowCnt; r++)
						document.getElementById('cb' + r).checked = checked;
					document.getElementById('qcolCbAll').checked = checked;
				} // qcolCheckAll

				function qcolSelects() {
					var r;
					var s = 0;
					for (r = 0; r < qcolRowCnt; r++)
						if (document.getElementById('cb' + r).checked) s++;
					return s;
				} // qcolSelects

				function qcolDisplay() {
					var form = document.QueryColForm;
					var table = document.getElementById("qcolTable");
					var oldRows = table.rows.length - 1;
					var r;
					var skip = 0;
					for (r = 0; r < qcolRowCnt; r++) {
						// get or create table row
						var row;
						if (r >= oldRows) {
							row = table.insertRow(r + 1);
							row.className = 'row' + (r % 2 + 1);
							var c;
							for (c = 0; c < qcolCellCount; c++) {
								row.insertCell(c);
								row.cells[c].noWrap = 'true';
							} // for
							row.cells[qcolCellOrderup].style.textAlign = 'right';
						} else
							row = table.rows[r + 1];

						var data = qcolRows[r];
						var title = data[qcolTitle];
						if (title.length > 50) title = title.substr(0, 47) + '...';
						row.cells[qcolCellCheckbox].innerHTML = '<input type="checkbox" id="cb' + r + '" name="cb' + r + '" value="' + r + '"/>';
						row.cells[qcolCellTitle].innerHTML = '<a href="javascript:qcolEdit(' + r + ')">' + htmlchars(title) + '<\/a>';
						row.cells[qcolCellName].innerHTML = data[qcolName];

						// header attribs
						var attr = '';
						var span = parseInt(data[qcolThspan]);
						if (skip > 0 || span < 1) {
							attr = '-';
							skip--;
						} else {
							if (span > 1) {
								attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SPAN'); ?>(' + span + ') ';
								skip = span - 1;
							} // if
							switch (data[qcolThalign]) {
								case '1': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LEFT'); ?> '; break;
								case '2': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CENTER'); ?> '; break;
								case '3': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RIGHT'); ?> '; break;
								case '4': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_JUSTIFY'); ?> '; break;
								default: ;
							} // switch
							switch (data[qcolThvalign]) {
								case '1': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TOP'); ?> '; break;
								case '2': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MIDDLE'); ?> '; break;
								case '3': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BOTTOM'); ?> '; break;
								case '4': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BASELINE'); ?> '; break;
								default: ;
							} // switch
							switch (data[qcolThwrap]) {
								case '1': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NOWRAP'); ?>'; break;
								default: ;
							} // switch
						} // if
						row.cells[qcolCellThattribs].innerHTML = attr;

						// data attribs
						attr = '';
						if (span < 1) {
							attr = '-';
							skip--;
						} else {
							switch (data[qcolAlign]) {
								case '1': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LEFT'); ?> '; break;
								case '2': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CENTER'); ?> '; break;
								case '3': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RIGHT'); ?> '; break;
								case '4': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_JUSTIFY'); ?> '; break;
								default: ;
							} // switch
							switch (data[qcolValign]) {
								case '1': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TOP'); ?> '; break;
								case '2': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MIDDLE'); ?> '; break;
								case '3': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BOTTOM'); ?> '; break;
								case '4': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BASELINE'); ?> '; break;
								default: ;
							} // switch
							switch (data[qcolWrap]) {
								case '1': attr += '<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NOWRAP'); ?>'; break;
								default: ;
							} // switch
						} // if
						row.cells[qcolCellAttributes].innerHTML = attr;

						// width
						attr = '';
						if (parseInt(data[qcolWidth]) > 0) {
							attr += data[qcolWidth];
							if (data[qcolWidthmd] == '1') attr += '%'; else attr += 'px';
						} // if
						row.cells[qcolCellWidth].innerHTML = attr;

						// ordering
						if (r > 0)
							row.cells[qcolCellOrderup].innerHTML = '<a href="javascript:qcolOrderup(' + r + ')"><img src="components/com_breezingforms/images/icons/uparrow.png" alt="^" border="0"/><\/a>';
						else
							row.cells[qcolCellOrderup].innerHTML = '';
						if (r < qcolRowCnt - 1)
							row.cells[qcolCellOrderdown].innerHTML = '<a href="javascript:qcolOrderdown(' + r + ')"><img src="components/com_breezingforms/images/icons/downarrow.png" alt="v" border="0"/><\/a>';
						else
							row.cells[qcolCellOrderdown].innerHTML = '';
					} // for
					for (r = oldRows; r > qcolRowCnt; r--) table.deleteRow(r);
					qcolCheckAll(false);
				} // qcolDisplay

				function qcolEdit(index) {
					var form = document.QueryColForm;
					qcolIndex = index;
					var row = qcolRows[qcolIndex];
					var c = 0;
					form.colTitle.value = row[qcolTitle];
					form.colName.value = row[qcolName];
					form.colClass1.value = row[qcolClass1];
					form.colClass2.value = row[qcolClass2];
					form.colClass3.value = row[qcolClass3];
					form.colWidth.value = row[qcolWidth];
					form.colWidthmd.value = row[qcolWidthmd];
					form.colThspan.value = row[qcolThspan];
					form.colThalign.value = row[qcolThalign];
					form.colThvalign.value = row[qcolThvalign];
					form.colThwrap.value = row[qcolThwrap];
					form.colAlign.value = row[qcolAlign];
					form.colValign.value = row[qcolValign];
					form.colWrap.value = row[qcolWrap];
					form.colValue.value = row[qcolValue];
					document.getElementById('QueryColDialog').style.display = '';
					form.colTitle.focus();
					MM_swapImage('colSave', '', 'images/save_f2.png', 1);
					MM_swapImgRestore();
					MM_swapImage('colCancel', '', 'images/cancel_f2.png', 1);
					MM_swapImgRestore();
				} // qcolEdit

				function qcolAdd() {
					var form = document.QueryColForm;
					qcolIndex = qcolRowCnt;
					form.colTitle.value = '';
					form.colName.value = '';
					form.colClass1.value = '';
					form.colClass2.value = '';
					form.colClass3.value = '';
					form.colWidth.value = '';
					form.colWidthmd.value = 0;
					form.colThspan.value = 1;
					form.colThalign.value = 0;
					form.colThvalign.value = 0;
					form.colThwrap.value = 0;
					form.colAlign.value = 0;
					form.colValign.value = 0;
					form.colWrap.value = 0;
					form.colValue.value = '\x3C?php return $value; ?\x3E';
					document.getElementById('QueryColDialog').style.display = '';
					form.colTitle.focus();
					MM_swapImage('colSave', '', 'images/save_f2.png', 1);
					MM_swapImgRestore();
					MM_swapImage('colCancel', '', 'images/cancel_f2.png', 1);
					MM_swapImgRestore();
				} // qcolAdd

				function qcolCopy() {
					if (!qcolSelects()) {
						alert('<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELECTCOLUMNS'); ?>');
						return;
					} // if
					var r;
					var newcnt = qcolRowCnt;
					for (r = 0; r < qcolRowCnt; r++) {
						if (document.getElementById('cb' + r).checked) {
							qcolRows[newcnt] = new Array();
							var x;
							for (x = 0; x < qcolRows[r].length; x++)
								qcolRows[newcnt][x] = qcolRows[r][x];
							newcnt++;
						} // if
					} // for
					qcolRowCnt = newcnt;
					qcolDisplay();
				} // qcolCopy

				function qcolDelete() {
					if (!qcolSelects()) {
						alert('<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELECTCOLUMNS'); ?>');
						return;
					} // if
					if (!confirm('<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ASKDELCOLUMNS'); ?>')) return;
					var r;
					for (r = qcolRowCnt - 1; r >= 0; r--) {
						if (document.getElementById('cb' + r).checked) {
							var r2;
							qcolRowCnt--;
							for (r2 = r; r2 < qcolRowCnt; r2++) qcolRows[r2] = qcolRows[r2 + 1];
						} // if
					} // for
					qcolDisplay();
				} // qcolDelete

				function qcolOrderup(index) {
					var row = qcolRows[index];
					qcolRows[index] = qcolRows[index - 1];
					qcolRows[index - 1] = row;
					qcolDisplay();
				} // qcolOrderup

				function qcolOrderdown(index) {
					var row = qcolRows[index];
					qcolRows[index] = qcolRows[index + 1];
					qcolRows[index + 1] = row;
					qcolDisplay();
				} // qcolOrderdown

				function qcolOk() {
					var form = document.QueryColForm;
					var error = '';
					if (form.colTitle.value == '')
						error += "<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TITLEEMPTY'); ?>\n";
					error += checkIdentifier(form.colName.value, 'name');
					if (error != '') {
						alert(error);
						return;
					} // if
					document.getElementById("QueryColDialog").style.display = 'none';

					if (qcolIndex >= qcolRowCnt) {
						// add a new row to the table
						qcolRows[qcolRowCnt++] = new Array(
							form.colTitle.value,
							form.colName.value,
							form.colClass1.value,
							form.colClass2.value,
							form.colClass3.value,
							form.colWidth.value,
							form.colWidthmd.value,
							form.colThspan.value,
							form.colThalign.value,
							form.colThvalign.value,
							form.colThwrap.value,
							form.colAlign.value,
							form.colValign.value,
							form.colWrap.value,
							form.colValue.value
						);
					} else {
						// udate existing row
						var row = qcolRows[qcolIndex];
						row[qcolTitle] = form.colTitle.value,
							row[qcolName] = form.colName.value,
							row[qcolClass1] = form.colClass1.value,
							row[qcolClass2] = form.colClass2.value,
							row[qcolClass3] = form.colClass3.value,
							row[qcolWidth] = form.colWidth.value,
							row[qcolWidthmd] = form.colWidthmd.value,
							row[qcolThspan] = form.colThspan.value,
							row[qcolThalign] = form.colThalign.value,
							row[qcolThvalign] = form.colThvalign.value,
							row[qcolThwrap] = form.colThwrap.value,
							row[qcolAlign] = form.colAlign.value,
							row[qcolValign] = form.colValign.value,
							row[qcolWrap] = form.colWrap.value,
							row[qcolValue] = form.colValue.value
					} // if
					qcolDisplay();
				} // qcolOk

				function qcolCancel() {
					document.getElementById("QueryColDialog").style.display = 'none';
				} // qcolCancel
				<?php
			} // if query list
	
			if ($hasInit) {
				?>
				function dispinit(value) {
					if (document.getElementById('initexec'))
						switch (value) {
							case '1':
								document.getElementById('initexec').style.display = '';
								document.getElementById('initlib').style.display = '';
								document.getElementById('initcode').style.display = 'none';
								break;
							case '2':
								document.getElementById('initexec').style.display = '';
								document.getElementById('initlib').style.display = 'none';
								document.getElementById('initcode').style.display = '';
								break;
							default:
								document.getElementById('initexec').style.display = 'none';
								document.getElementById('initlib').style.display = 'none';
								document.getElementById('initcode').style.display = 'none';
						} // switch
				} // dispinit

				function createInitCode() {
					form = document.adminForm;
					name = form.name.value;
					if (name == '') {
						alert('Please enter the element name first.');
						return;
					} // if
					if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAINIT'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>")) return;
					code =
						"function ff_" + name + "_init(element, condition)\n" +
						"{\n" +
						"    switch (condition) {\n";
					if (form.script1flag1.checked)
						code +=
							"        case 'formentry':\n" +
							"            break;\n";
					if (form.script1flag2.checked)
						code +=
							"        case 'pageentry':\n" +
							"            break;\n";
					code +=
						"        default:;\n" +
						"    } // switch\n" +
						"} // ff_" + name + "_init\n";
					oldcode = form.script1code.value;
					if (oldcode != '')
						form.script1code.value =
							code +
							"\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
							oldcode;
					else
						form.script1code.value = code;
					codeAreaChange(form.script1code);
				} // createInitCode

				<?php
			} // if hasInit
	
			if ($hasAction) {
				?>
				function dispaction(value) {
					if (document.getElementById)
						if (document.getElementById('actionact'))
							switch (value) {
								case '1':
									document.getElementById('actionact').style.display = '';
									document.getElementById('actionlib').style.display = '';
									document.getElementById('actioncode').style.display = 'none';
									break;
								case '2':
									document.getElementById('actionact').style.display = '';
									document.getElementById('actionlib').style.display = 'none';
									document.getElementById('actioncode').style.display = '';
									break;
								default:
									document.getElementById('actionact').style.display = 'none';
									document.getElementById('actionlib').style.display = 'none';
									document.getElementById('actioncode').style.display = 'none';
							} // switch
				} // dispaction

				function createActionCode() {
					form = document.adminForm;
					name = form.name.value;
					if (name == '') {
						alert('Please enter the element name first.');
						return;
					} // if
					if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAACTION'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>")) return;
					code =
						"function ff_" + name + "_action(element, action)\n" +
						"{\n" +
						"    switch (action) {\n";
					if (form.script2flag1)
						if (form.script2flag1.checked)
							code +=
								"        case 'click':\n" +
								"            break;\n";
					if (form.script2flag2)
						if (form.script2flag2.checked)
							code +=
								"        case 'blur':\n" +
								"            break;\n";
					if (form.script2flag3)
						if (form.script2flag3.checked)
							code +=
								"        case 'change':\n" +
								"            break;\n";
					if (form.script2flag4)
						if (form.script2flag4.checked)
							code +=
								"        case 'focus':\n" +
								"            break;\n";
					if (form.script2flag5)
						if (form.script2flag5.checked)
							code +=
								"        case 'select':\n" +
								"            break;\n";
					code +=
						"        default:;\n" +
						"    } // switch\n" +
						"} // ff_" + name + "_action\n";
					oldcode = form.script2code.value;
					if (oldcode != '')
						form.script2code.value =
							code +
							"\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
							oldcode;
					else
						form.script2code.value = code;
					codeAreaChange(form.script2code);
				} // createActionCode

				<?php
			} // if hasAction
	
			if ($hasValidation) {
				?>
				function dispvalidation(value) {
					if (document.getElementById)
						if (document.getElementById('validationlib'))
							switch (value) {
								case '1':
									document.getElementById('validationmsg').style.display = '';
									document.getElementById('validationlib').style.display = '';
									document.getElementById('validationcode').style.display = 'none';
									break;
								case '2':
									document.getElementById('validationmsg').style.display = '';
									document.getElementById('validationlib').style.display = 'none';
									document.getElementById('validationcode').style.display = '';
									break;
								default:
									document.getElementById('validationmsg').style.display = 'none';
									document.getElementById('validationlib').style.display = 'none';
									document.getElementById('validationcode').style.display = 'none';
							} // switch
				} // dispvalidation

				function createValidationCode() {
					form = document.adminForm;
					name = form.name.value;
					if (name == '') {
						alert('Please enter the element name first.');
						return;
					} // if
					if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAVALID'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>")) return;
					code =
						"function ff_" + name + "_validation(element, message)\n" +
						"{\n" +
						"    if (element_fails_my_test) {\n" +
						"        if (message=='') message = element.name+\" faild in my test.\\n\"\n" +
						"        ff_validationFocus(element.name);\n" +
						"        return message;\n" +
						"    } // if\n" +
						"    return '';\n" +
						"} // ff_" + name + "_validation\n";
					oldcode = form.script3code.value;
					if (oldcode != '')
						form.script3code.value =
							code +
							"\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
							oldcode;
					else
						form.script3code.value = code;
					codeAreaChange(form.script3code);
				} // createValidationCode

				<?php
			} // if hasValidation
	
			if ($row->type == 'Select List') {
				?>
				function addOption() {
					form = document.SelectOptionForm;
					form.optionText.value = '';
					form.optionValue.value = '';
					form.optionSelected[0].checked = true;
					document.getElementById("SelectOptionDialog").style.display = '';
					form.optionText.focus();
				} // addOption

				function okOption() {
					form = document.SelectOptionForm;
					if (form.optionText.value == '') {
						alert("Please enter a text.");
						form.optionText.focus();
						return;
					} // if
					data = document.adminForm.data2;
					value = data.value;
					if (value != '')
						if (value.substr(value.length - 1, 1) != "\n")
							value += "\n";
					sel = '0;';
					if (form.optionSelected[1].checked) sel = '1;';
					value +=
						sel +
						form.optionText.value + ';' +
						form.optionValue.value;
					data.value = value;
					form.optionText.value = '';
					form.optionValue.value = '';
					form.optionSelected[0].checked = true;
					form.optionText.focus();
				} // addOption

				function quitOption() {
					document.getElementById("SelectOptionDialog").style.display = 'none';
				} // addOption
				<?php
			}
			?>
			onload = function () {
				<?php
				if ($row->type == 'Query List')
					echo "\t\t\tloadQueryList();\n";
				// because of mozilla browser problems, the widest elements must be shown first
				$s1size = $s2size = $s3size = $ff_config->areasmall;
				$nonbig = true;
				if ($hasInit && $row->script1cond == 2) {
					echo "\t\t\tdispinit('2');\n";
					$s1size = $ff_config->areamedium;
					$nonbig = false;
				} // if
				if ($hasAction && $row->script2cond == 2) {
					echo "\t\t\tdispaction('2');\n";
					if ($nonbig) {
						$s2size = $ff_config->areamedium;
						$nonbig = false;
					} // if
				} // if
				if ($hasValidation && $row->script3cond == 2) {
					echo "\t\t\tdispvalidation('2');\n";
					if ($nonbig)
						$s3size = $ff_config->areamedium;
				} // if
				if ($hasInit && $row->script1cond == 1)
					echo "\t\t\tdispinit('1');\n";
				if ($hasAction && $row->script2cond == 1)
					echo "\t\t\tdispaction('1');\n";
				if ($hasValidation && $row->script3cond == 1)
					echo "\t\t\tdispvalidation('1');\n";
				if (($row->type == 'Graphic Button' || $row->type == 'Icon') && $row->flag1 != 0)
					echo "\t\t\tdispcaptiontext('" . $row->flag1 . "');\n";
				if ($hasInit)
					echo "\t\t\tcodeAreaAdd('script1code', 'script1lines');\n";
				if ($hasAction)
					echo "\t\t\tcodeAreaAdd('script2code', 'script2lines');\n";
				if ($hasValidation)
					echo "\t\t\tcodeAreaAdd('script3code', 'script3lines');\n";
				if ($row->type == 'Query List')
					echo "\t\t\tcodeAreaAdd('data2', 'data2lines');\n";
				switch ($tabpane) {
					case 1:
					case 2:
						echo "\t\t\ttabPane1.setSelectedIndex($tabpane);\n";
						break;
					default:
						echo "\t\t\tdocument.adminForm.title.focus();\n";
				} // switch
				?>
			} // onload
			//-->
		</script>

		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script type="text/javascript"
			src="<?php echo $ff_mossite ?>/components/com_breezingforms/libraries/js/overlib_mini.js"></script>

		<?php
		if ($row->type == 'Select List') {
			?>
			<div id="SelectOptionDialog" style="position:absolute;top:380px;left:45%;z-index:100;display:none;">
				<table cellpadding="4" cellspacing="1" border="0" class="adminform" style="width:350px">
					<form name='SelectOptionForm' onsubmit='okOption();return false;'>
						<tr>
							<th colspan="4" class="title">BreezingForms -
								<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NEWSELOPT'); ?>
							</th>
						</tr>
						<tr>
							<td></td>
							<td>
								<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TEXT'); ?>:
							</td>
							<td><input type="text" size="50" value="" name="optionText" class="inputbox" /></td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_VALUE'); ?>:
							</td>
							<td><input type="text" size="50" value="" name="optionValue" class="inputbox" /></td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELECTED'); ?>:
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "optionSelected", "", 0); ?>
							</td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2" style="text-align:right">
								<input type="button" value="<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OKBUTTON'); ?>"
									onclick="okOption()" class="button btn btn-primary">
								&nbsp;&nbsp;
								<input type="button" value="<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ABORTBUTT'); ?>"
									onclick="quitOption()" class="button btn btn-primary">
							</td>
							<td></td>
						</tr>
				</table>
				</form>
			</div>
			<?php
		} // if Select List

		if ($row->type == 'Query List') {
			?>
			<div id="QueryColDialog"
				style="background-color: #cccccc; position:absolute;top:120px;left:30%;z-index:100;display:none;">
				<form action="#" name='QueryColForm' onsubmit="return false;">
					<table cellpadding="4" cellspacing="1" border="0" class="adminform" style="width:550px">
						<tr>
							<th colspan="4" class="title">BreezingForms -
								<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EDITQUERYCOL'); ?>
							</th>
						</tr>
						<tr>
							<td></td>
							<td>
								<fieldset>
									<legend>
										<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_HEADER'); ?>
									</legend>
									<table class="adminform">
										<tr>
											<td nowrap>
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TITLE'); ?>:
											</td>
											<td nowrap>
												<input type="text" size="50" maxlength="500" name="colTitle" class="inputbox" />
												<?php
												echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_QCOLTIPTITLE'));
												?>
											</td>
										</tr>
										<tr>
											<td nowrap>
												<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;th&gt;:
											</td>
											<td nowrap>
												<input type="text" size="30" maxlength="30" name="colClass1" class="inputbox" />
											</td>
										</tr>
										<tr>
											<td nowrap>&lt;th&gt;
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SPAN'); ?>:
											</td>
											<td nowrap>
												<input type="text" size="6" maxlength="6" name="colThspan" class="inputbox" />
											</td>
										</tr>
										<tr>
											<td nowrap>
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ATTRIBUTES'); ?>:
											</td>
											<td nowrap>
												<select name="colThalign" size="1" class="inputbox">
													<option value="0" selected="selected">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
													</option>
													<option value="1">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LEFT'); ?>
													</option>
													<option value="2">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CENTER'); ?>
													</option>
													<option value="3">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RIGHT'); ?>
													</option>
													<option value="4">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_JUSTIFY'); ?>
													</option>
												</select>
												<select name="colThvalign" size="1" class="inputbox">
													<option value="0" selected="selected">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
													</option>
													<option value="1">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TOP'); ?>
													</option>
													<option value="2">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MIDDLE'); ?>
													</option>
													<option value="3">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BOTTOM'); ?>
													</option>
													<option value="4">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BASELINE'); ?>
													</option>
												</select>
												<select name="colThwrap" size="1" class="inputbox">
													<option value="0" selected="selected">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
													</option>
													<option value="1">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NOWRAP'); ?>
													</option>
												</select>
											</td>
										</tr>
									</table>
								</fieldset>
							</td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<fieldset>
									<legend>
										<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_DATA'); ?>
									</legend>
									<table class="adminform">
										<tr>
											<td nowrap>
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NAME'); ?>:
											</td>
											<td nowrap>
												<input type="text" size="30" maxlength="30" name="colName" class="inputbox" />
												<?php
												echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_QCOLTIPNAME'));
												?>
											</td>
										</tr>
										<tr>
											<td nowrap>
												<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;td(1)&gt;:
											</td>
											<td nowrap>
												<input type="text" size="30" maxlength="30" name="colClass2" class="inputbox" />
											</td>
										</tr>
										<tr>
											<td nowrap>
												<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;td(2)&gt;:
											</td>
											<td nowrap>
												<input type="text" size="30" maxlength="30" name="colClass3" class="inputbox" />
											</td>
										</tr>
										<tr>
											<td valign="top" nowrap>
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_WIDTH'); ?>:
											</td>
											<td nowrap>
												<input type="text" size="6" maxlength="6" name="colWidth" class="inputbox" />
												<br />
												<select name="colWidthmd" size="1" class="inputbox">
													<option value="0" selected="selected">px</option>
													<option value="1">%</option>
												</select>
											</td>
										</tr>
										<tr>
											<td nowrap>
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ATTRIBUTES'); ?>:
											</td>
											<td nowrap>
												<select name="colAlign" size="1" class="inputbox">
													<option value="0" selected="selected">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
													</option>
													<option value="1">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LEFT'); ?>
													</option>
													<option value="2">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CENTER'); ?>
													</option>
													<option value="3">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RIGHT'); ?>
													</option>
													<option value="4">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_JUSTIFY'); ?>
													</option>
												</select>
												<select name="colValign" size="1" class="inputbox">
													<option value="0" selected="selected">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
													</option>
													<option value="1">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TOP'); ?>
													</option>
													<option value="2">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MIDDLE'); ?>
													</option>
													<option value="3">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BOTTOM'); ?>
													</option>
													<option value="4">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BASELINE'); ?>
													</option>
												</select>
												<select name="colWrap" size="1" class="inputbox">
													<option value="0" selected="selected">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
													</option>
													<option value="1">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NOWRAP'); ?>
													</option>
												</select>
											</td>
										</tr>
										<tr>
											<td nowrap colspan="2">
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_VALUE'); ?>:
												<a href="#"
													onClick="document.QueryColForm.colValue.rows=<?php echo $ff_config->areasmall; ?>;">[
													<?php echo $ff_config->areasmall; ?>]
												</a>
												<a href="#"
													onClick="document.QueryColForm.colValue.rows=<?php echo $ff_config->areamedium; ?>;">[
													<?php echo $ff_config->areamedium; ?>]
												</a>
												<a href="#"
													onClick="document.QueryColForm.colValue.rows=<?php echo $ff_config->arealarge; ?>;">[
													<?php echo $ff_config->arealarge; ?>]
												</a>
												<br />
												<textarea wrap="off" name="colValue" style="width:500px;"
													rows="<?php echo $ff_config->areasmall; ?>" class="inputbox"></textarea>
											</td>
										</tr>
									</table>
								</fieldset>
							</td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td nowrap colspan="2" style="text-align:right">

								<input class="btn btn-primary" onclick="qcolOk();" type="submit"
									value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_TOOLBAR_SAVE'), ENT_QUOTES, 'UTF-8'); ?>" />
								&nbsp;&nbsp;
								<input class="btn btn-primary" onclick="qcolCancel();" type="submit"
									value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_TOOLBAR_CANCEL'), ENT_QUOTES, 'UTF-8'); ?>" />

							</td>
							<td>&nbsp;</td>
						</tr>
					</table>
				</form>
				<br />
				<br />
			</div>
			<?php
		} // if Query List

		?>
		<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
			<table cellpadding="0" cellspacing="0" border="0" class="adminform" style="width:775px;">
				<tr>
					<th colspan="3" class="title">BreezingForms -
						<?php echo $action; ?>
						<?php echo HTML_facileFormsElement::displayType($row->type); ?>
					</th>
				</tr>
				<tr>
					<td></td>
					<td width="100%">
						<?php
						$tabs = new BFTabs(0);
						$tabs->startPane("editPane");
						$tabs->startTab(BFText::_('COM_BREEZINGFORMS_ELEMENTS_SETTINGS'), "tab_settings");
						?>
						<table cellpadding="0" cellspacing="0" border="0" class="adminform">

							<tr>
								<td></td>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TITLE'); ?>:
								</td>
								<td nowrap>
									<input type="text" size="50" maxlength="50" name="title" value="<?php echo $row->title; ?>"
										class="inputbox" />
									<?php
									echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_TIPTITLE'));
									?>
								</td>
								<td></td>
							</tr>
							<tr>
								<td></td>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NAME'); ?>:
								</td>
								<td nowrap>
									<input type="text" size="30" maxlength="30" name="name" value="<?php echo $row->name ?>"
										class="inputbox" />
									<?php
									echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_TIPNAME'));
									?>
								</td>
								<td></td>
							</tr>

							<?php
							switch ($row->type) {
								case 'Hidden Input':
									break;
								default:
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;div&gt;:
										</td>
										<td nowrap>
											<input type="text" size="30" maxlength="30" name="class1"
												value="<?php echo $row->class1; ?>" class="inputbox" />
										</td>
										<td></td>
									</tr>
									<?php
									break;
							} // switch
					
							$elemname = '';
							switch ($row->type) {
								case 'Image':
								case 'Tooltip':
								case 'Icon':
									$elemname = 'img';
									break;
								case 'Checkbox':
								case 'Radio Button':
								case 'Regular Button':
								case 'Text':
								case 'File Upload':
									$elemname = 'input';
									break;
								case 'Graphic Button':
									$elemname = 'button';
									break;
								case 'Select List':
									$elemname = 'select';
									break;
								case 'Textarea':
									$elemname = 'textarea';
									break;
								case 'Query List':
									$elemname = 'table';
									break;
								default:
									;
							} // switch
							if ($elemname != '') {
								?>
								<tr>
									<td></td>
									<td nowrap>
										<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;
										<?php echo $elemname; ?>&gt;:
									</td>
									<td nowrap>
										<input type="text" size="30" maxlength="30" name="class2"
											value="<?php echo $row->class2; ?>" class="inputbox" />
									</td>
									<td></td>
								</tr>
								<?php
							} // if
					
							switch ($row->type) {
								case 'Query List':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;tr(header)&gt;:
										</td>
										<td nowrap>
											<input type="text" size="30" maxlength="30" name="trhclass" class="inputbox" />
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;tr(data1)&gt;:
										</td>
										<td nowrap>
											<input type="text" size="30" maxlength="30" name="tr1class" class="inputbox" />
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;tr(data2)&gt;:
										</td>
										<td nowrap>
											<input type="text" size="30" maxlength="30" name="tr2class" class="inputbox" />
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;tr(footer)&gt;:
										</td>
										<td nowrap>
											<input type="text" size="30" maxlength="30" name="trfclass" class="inputbox" />
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;td(footer)&gt;:
										</td>
										<td nowrap>
											<input type="text" size="30" maxlength="30" name="tdfclass" class="inputbox" />
										</td>
										<td></td>
									</tr>
									<?php
								default:
									;
							} // switch
							?>
							<tr>
								<td></td>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ORDERING'); ?>:
								</td>
								<td nowrap>
									<?php echo $lists['ordering']; ?>
								</td>
								<td></td>
							</tr>

							<tr>
								<td></td>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PUBLISHED'); ?>:
								</td>
								<td nowrap>
									<?php echo JHTML::_('select.booleanlist', "published", "", $row->published); ?>
								</td>
								<td></td>
							</tr>
							<?php
							switch ($row->type) {
								case 'Checkbox':
								case 'Radio Button':
								case 'Select List':
								case 'Query List':
								case 'Text':
								case 'File Upload':
								case 'Textarea':
								case 'Hidden Input':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_INCINLOG'); ?>:
										</td>
										<td nowrap>
											<?php echo JHTML::_('select.booleanlist', "logging", "", $row->logging); ?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								default:
									break;
							} // switch
					
							switch ($row->type) {
								case 'Hidden Input':
									break;
								default:
									?>
									<tr>
										<td></td>
										<td valign="top" nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_POSITION'); ?> X:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="posx" value="<?php echo $row->posx; ?>"
												class="inputbox" />
											<br />
											<select name="posxmode" size="1" class="inputbox">
												<option value="0" <?php if ($row->posxmode == 0)
													echo ' selected="selected"'; ?>>px
												</option>
												<option value="1" <?php if ($row->posxmode == 1)
													echo ' selected="selected"'; ?>>%
												</option>
											</select>
										</td>
										<td></td>
									</tr>

									<tr>
										<td></td>
										<td valign="top" nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_POSITION'); ?> Y:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="posy" value="<?php echo $row->posy; ?>"
												class="inputbox" />
											<br />
											<select name="posymode" size="1" class="inputbox">
												<option value="0" <?php if ($row->posymode == 0)
													echo ' selected="selected"'; ?>>px
												</option>
												<option value="1" <?php if ($row->posymode == 1)
													echo ' selected="selected"'; ?>>%
												</option>
											</select>
										</td>
										<td></td>
									</tr>
									<?php
									break;
							} // switch
					
							switch ($row->type) {
								case 'Hidden Input':
									break;
								case 'Textarea':
									?>
									<tr>
										<td></td>
										<td valign="top" nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_WIDTH'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="width" value="<?php echo $row->width; ?>"
												class="inputbox" />
											<br />
											<select name="widthmode" size="1" class="inputbox">
												<option value="0" <?php if ($row->widthmode == 0)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_COLUMNS'); ?>
												</option>
												<option value="1" <?php if ($row->widthmode == 1)
													echo ' selected="selected"'; ?>>px
												</option>
											</select>
										</td>
										<td></td>
									</tr>

									<tr>
										<td></td>
										<td valign="top" nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_HEIGHT'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="height" value="<?php echo $row->height; ?>"
												class="inputbox" />
											<br />
											<select name="heightmode" size="1" class="inputbox">
												<option value="0" <?php if ($row->heightmode == 0)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ROWS'); ?>
												</option>
												<option value="1" <?php if ($row->heightmode == 1)
													echo ' selected="selected"'; ?>>px
												</option>
											</select>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Captcha':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_WIDTH'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="width" value="<?php echo $row->width; ?>"
												class="inputbox" /> px
											<input type="hidden" name="widthmode" value="1" />
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_HEIGHT'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="height" value="<?php echo $row->height; ?>"
												class="inputbox" /> px
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Text':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_WIDTH'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="width" value="<?php echo $row->width; ?>"
												class="inputbox" />
											<br />
											<select name="widthmode" size="1" class="inputbox">
												<option value="0" <?php if ($row->widthmode == 0)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_COLUMNS'); ?>
												</option>
												<option value="1" <?php if ($row->widthmode == 1)
													echo ' selected="selected"'; ?>>px
												</option>
											</select>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MAXLENGTH'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="height" value="<?php echo $row->height; ?>"
												class="inputbox" />
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_COLUMNS'); ?>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MAILBACK'); ?>:
										</td>
										<td nowrap>
											<?php echo JHTML::_('select.booleanlist', "mailback", "", $row->mailback); ?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Select List':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_WIDTH'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="width" value="<?php echo $row->width; ?>"
												class="inputbox" /> px
										</td>
										<td></td>
									</tr>

									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_HEIGHT'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="height" value="<?php echo $row->height; ?>"
												class="inputbox" /> px
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'File Upload':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_FIELDSZ'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="width" value="<?php echo $row->width; ?>"
												class="inputbox" />
										</td>
										<td></td>
									</tr>

									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MAXFILESIZE'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="8" maxlength="8" name="height" value="<?php echo $row->height; ?>"
												class="inputbox" />
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Static Text/HTML':
								case 'Rectangle':
								case 'Image':
									?>
									<tr>
										<td></td>
										<td valign="top" nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_WIDTH'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="width" value="<?php echo $row->width; ?>"
												class="inputbox" />
											<br />
											<select name="widthmode" size="1" class="inputbox">
												<option value="0" <?php if ($row->widthmode == 0)
													echo ' selected="selected"'; ?>>px
												</option>
												<option value="1" <?php if ($row->widthmode == 1)
													echo ' selected="selected"'; ?>>%
												</option>
											</select>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td valign="top" nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_HEIGHT'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="height" value="<?php echo $row->height; ?>"
												class="inputbox" />
											<br />
											<select name="heightmode" size="1" class="inputbox">
												<option value="0" <?php if ($row->heightmode == 0)
													echo ' selected="selected"'; ?>>px
												</option>
												<option value="1" <?php if ($row->heightmode == 1)
													echo ' selected="selected"'; ?>>%
												</option>
											</select>
											<?php
											if ($row->type == 'Image')
												echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_IMAGE0'));
											else
												echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_OTHER0'));
											?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Query List':
									?>
									<tr>
										<td></td>
										<td valign="top" nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_WIDTH'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="width" value="<?php echo $row->width; ?>"
												class="inputbox" />
											<br />
											<select name="widthmode" size="1" class="inputbox">
												<option value="0" <?php if ($row->widthmode == 0)
													echo ' selected="selected"'; ?>>px
												</option>
												<option value="1" <?php if ($row->widthmode == 1)
													echo ' selected="selected"'; ?>>%
												</option>
											</select>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ROWSPERPAGE'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="6" maxlength="6" name="height" value="<?php echo $row->height; ?>"
												class="inputbox" onchange="showpagenav(this.value)" />
										</td>
										<td></td>
									</tr>
									<tr id="pagenavrow" <?php if ($row->height == 0)
										echo ' style="display:none"'; ?>>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGENAV'); ?>:
										</td>
										<td nowrap>
											<select name="pagenav" size="1" class="inputbox">
												<option value="0">none</option>
												<option value="1" selected="selected">&lt;&lt;
													<?php echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGESTART'); ?> &lt;
													<?php echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGEPREV'); ?> 1 2 3
													<?php BFText::_('COM_BREEZINGFORMS_PROCESS_PAGENEXT'); ?> &gt;
													<?php BFText::_('COM_BREEZINGFORMS_PROCESS_PAGEEND'); ?> &gt;&gt;
												</option>
												<option value="2">&lt;&lt;
													<?php echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGESTART'); ?> &lt;
													<?php echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGEPREV'); ?>
													<?php echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGENEXT'); ?> &gt;
													<?php echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGEEND'); ?> &gt;&gt;
												</option>
												<option value="3">&lt;&lt; &lt; 1 2 3 &gt; &gt;&gt;</option>
												<option value="4">&lt;&lt; &lt; &gt; &gt;&gt;</option>
												<option value="5">1 2 3</option>
											</select>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								default:
									break;
							} // switch
					
							switch ($row->type) {
								case 'Checkbox':
								case 'Radio Button':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CHECKED'); ?>:
										</td>
										<td nowrap>
											<?php echo JHTML::_('select.booleanlist', "flag1", "", $row->flag1); ?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Select List':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MULTIPLE'); ?>:
										</td>
										<td nowrap>
											<?php echo JHTML::_('select.booleanlist', "flag1", "", $row->flag1); ?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Query List':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SHOWHEADER'); ?>:
										</td>
										<td nowrap>
											<?php echo JHTML::_('select.booleanlist', "flag1", "", $row->flag1); ?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Text':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PASSWORD'); ?>:
										</td>
										<td nowrap>
											<?php echo JHTML::_('select.booleanlist', "flag1", "", $row->flag1); ?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'File Upload':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_UPLDTIMESTAMP'); ?>:
										</td>
										<td nowrap>
											<?php echo JHTML::_('select.booleanlist', "flag1", "", $row->flag1); ?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Tooltip':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TYPE'); ?>:
										</td>
										<td nowrap>
											<input type="radio" id="flag10" name="flag1" value="0" <?php if ($row->flag1 == 0)
												echo ' checked="checked"'; ?> onclick="disptooltipurl(this.value)" /><label for="flag10"> <img
													src="<?php echo $ff_mossite; ?>components/com_breezingforms/images/js/ThemeOffice/tooltip.png"
													alt="" border="0" /></label>&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" id="flag11" name="flag1" value="1" <?php if ($row->flag1 == 1)
												echo ' checked="checked"'; ?> onclick="disptooltipurl(this.value)" /><label for="flag11"> <img
													src="<?php echo $ff_mossite; ?>components/com_breezingforms/images/js/ThemeOffice/warning.png"
													alt="" border="0" /></label>&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" id="flag12" name="flag1" value="2" <?php if ($row->flag1 == 2)
												echo ' checked="checked"'; ?> onclick="disptooltipurl(this.value)" /><label for="flag12">
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CUSTOM'); ?>
											</label>
										<td></td>
									</tr>
									<?php
								default:
									break;
							} // switch
					
							switch ($row->type) {
								case 'Checkbox':
								case 'Radio Button':
								case 'Regular Button':
								case 'Graphic Button':
								case 'Select List':
								case 'File Upload':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TYPE'); ?>:
										</td>
										<td nowrap>
											<select name="flag2" size="1" class="inputbox">
												<option value="0" <?php if ($row->flag2 == 0)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ENABLED'); ?>
												</option>
												<option value="1" <?php if ($row->flag2 == 1)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_DISABLED'); ?>
												</option>
											</select>
										<td></td>
									</tr>
									<?php
									break;

								case 'Icon':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BORDER'); ?>:
										</td>
										<td nowrap>
											<?php echo JHTML::_('select.booleanlist', "flag2", "", $row->flag2); ?>
										</td>
										<td></td>
									</tr>
									<?php
									break;

								case 'Text':
								case 'Textarea':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TYPE'); ?>:
										</td>
										<td nowrap>
											<select name="flag2" size="1" class="inputbox">
												<option value="0" <?php if ($row->flag2 == 0)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ENABLED'); ?>
												</option>
												<option value="1" <?php if ($row->flag2 == 1)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_DISABLED'); ?>
												</option>
												<option value="2" <?php if ($row->flag2 == 2)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_READONLY'); ?>
												</option>
											</select>
										<td></td>
									</tr>
									<?php
									break;
								case 'Query List':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_FIRSTCOLUMN'); ?>:
										</td>
										<td nowrap>
											<select name="flag2" size="1" class="inputbox">
												<option value="0" <?php if ($row->flag2 == 0)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NORMAL'); ?>
												</option>
												<option value="1" <?php if ($row->flag2 == 1)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CHECKBOXES'); ?>
												</option>
												<option value="2" <?php if ($row->flag2 == 2)
													echo ' selected="selected"'; ?>>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RADIOBUTTONS'); ?>
												</option>
											</select>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								default:
									break;
							} // switch
					
							switch ($row->type) {
								case 'File Upload':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_UPLDIR'); ?>:
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data1" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data1, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Static Text/HTML':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TEXTHTML'); ?>:
											<?php
											if ($ff_config->wysiwyg) {
												echo '<br/>';
												jimport('joomla.html.editor');
												$editor = Factory::getEditor();
												echo $editor->display('data1', $row->data1, 'data1', '500', '100%', '45', '10');
											} else {
												echo '<a href="#" onClick="textAreaResize(\'data1\',' . $ff_config->areasmall . ');">[' . $ff_config->areasmall . ']</a> ' .
													'<a href="#" onClick="textAreaResize(\'data1\',' . $ff_config->areamedium . ');">[' . $ff_config->areamedium . ']</a> ' .
													'<a href="#" onClick="textAreaResize(\'data1\',' . $ff_config->arealarge . ');">[' . $ff_config->arealarge . ']</a>' .
													'<br/>' .
													'<textarea wrap="off" name="data1" style="width:700px;" rows="' . $ff_config->areasmall . '" class="inputbox">' . htmlspecialchars($row->data1, ENT_QUOTES) . '</textarea>';
											} // if
											?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Textarea':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_VALUE'); ?>:
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data1" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data1, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Hidden Input':
								case 'Checkbox':
								case 'Radio Button':
								case 'Text':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_VALUE'); ?>:
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data1" style="width:700px"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data1, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Rectangle':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BORDER'); ?>:
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data1" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data1, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Image':
								case 'Graphic Button':
								case 'Icon':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_IMGURL'); ?>:
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data1" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data1, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Tooltip':
									?>
									<tr id="tooltipurl" style="display:none;">
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_IMGURL'); ?>:
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data1" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data1, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Select List':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SIZE'); ?>:
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data1',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data1" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data1, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Query List':
									?>
									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BORDERWIDTH'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="3" maxlength="2" name="border" class="inputbox" />
										</td>
										<td></td>
									</tr>

									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CELLSPACING'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="3" maxlength="2" name="cellspacing" class="inputbox" />
										</td>
										<td></td>
									</tr>

									<tr>
										<td></td>
										<td nowrap>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CELLPADDING'); ?>:
										</td>
										<td nowrap>
											<input type="text" size="3" maxlength="2" name="cellpadding" class="inputbox" />
										</td>
										<td></td>
									</tr>
									<?php
									break;
								default:
									break;
							} // switch
					
							switch ($row->type) {
								case 'Icon':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_IMGURLF2'); ?>:
											<a href="#" onClick="textAreaResize('data3',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data3',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data3',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data3" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data3, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								default:
									break;
							} // switch
					
							switch ($row->type) {
								case 'File Upload':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MIMETYPES'); ?>:
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data2" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Graphic Button':
								case 'Icon':
									?>
									<tr>
										<td></td>
										<td colspan="2">
											<fieldset>
												<legend>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CAPTION'); ?>
												</legend>
												<table cellpadding="4" cellspacing="1" border="0">
													<tr>
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TYPE'); ?>:
														</td>
														<td nowrap>
															<input type="radio" id="flag10" name="flag1" value="0" <?php if ($row->flag1 == 0)
																echo ' checked="checked"'; ?>
																onclick="dispcaptiontext(this.value)" /><label for="flag10">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
															</label>
															<input type="radio" id="flag11" name="flag1" value="1" <?php if ($row->flag1 == 1)
																echo ' checked="checked"'; ?>
																onclick="dispcaptiontext(this.value)" /><label for="flag11">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BELOW'); ?>
															</label>
															<input type="radio" id="flag12" name="flag1" value="2" <?php if ($row->flag1 == 2)
																echo ' checked="checked"'; ?>
																onclick="dispcaptiontext(this.value)" /><label for="flag12">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ABOVE'); ?>
															</label>
															<input type="radio" id="flag13" name="flag1" value="3" <?php if ($row->flag1 == 3)
																echo ' checked="checked"'; ?>
																onclick="dispcaptiontext(this.value)" /><label for="flag13">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LEFT'); ?>
															</label>
															<input type="radio" id="flag14" name="flag1" value="4" <?php if ($row->flag1 == 4)
																echo ' checked="checked"'; ?>
																onclick="dispcaptiontext(this.value)" /><label for="flag14">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RIGHT'); ?>
															</label>
														</td>
													</tr>
													<tr id="captiontext" style="display:none;">
														<td nowrap colspan="2">
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TEXT'); ?>:
															<a href="#"
																onClick="textAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
																<?php echo $ff_config->areasmall; ?>]
															</a>
															<a href="#"
																onClick="textAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
																<?php echo $ff_config->areamedium; ?>]
															</a>
															<a href="#"
																onClick="textAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
																<?php echo $ff_config->arealarge; ?>]
															</a>
															<br />
															<textarea wrap="off" name="data2" style="width:680px;"
																rows="<?php echo $ff_config->areasmall; ?>"
																class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
														</td>
													</tr>
												</table>
											</fieldset>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Tooltip':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TEXT'); ?>:
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data2" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Radio Button':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CAPTION'); ?>:
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data2" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Checkbox':
								case 'Regular Button':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LABEL'); ?>:
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data2" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Rectangle':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BKGCOLOR'); ?>:
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data2" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Image':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ALTTEXT'); ?>:
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<br />
											<textarea wrap="off" name="data2" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								case 'Select List':
									?>
									<tr>
										<td></td>
										<td nowrap colspan="2">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OPTIONS'); ?>:
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
												<?php echo $ff_config->areasmall; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
												<?php echo $ff_config->areamedium; ?>]
											</a>
											<a href="#" onClick="textAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
												<?php echo $ff_config->arealarge; ?>]
											</a>
											<a href="#" onClick="addOption();">
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ADDOPTIONS'); ?>
											</a>
											<br />
											<textarea wrap="off" name="data2" style="width:700px;"
												rows="<?php echo $ff_config->areasmall; ?>"
												class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
											<?php
											echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_OPTINFO'));
											?>
										</td>
										<td></td>
									</tr>
									<?php
									break;
								default:
									break;
							} // switch
							?>
						</table>
						<?php
						if ($row->type == 'Query List') {
							$tabs->endTab();
							$tabs->startTab(BFText::_('COM_BREEZINGFORMS_ELEMENTS_QUERY'), "tab_query");
							?>
							<table class="adminform">
								<tr>
									<td></td>
									<td nowrap colspan="2">
										<a href="#" onClick="codeAreaResize('data2',<?php echo $ff_config->areasmall; ?>);">[
											<?php echo $ff_config->areasmall; ?>]
										</a>
										<a href="#" onClick="codeAreaResize('data2',<?php echo $ff_config->areamedium; ?>);">[
											<?php echo $ff_config->areamedium; ?>]
										</a>
										<a href="#" onClick="codeAreaResize('data2',<?php echo $ff_config->arealarge; ?>);">[
											<?php echo $ff_config->arealarge; ?>]
										</a>
										<a href="#" onClick="createQueryCode();">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREATEQUERY'); ?>
										</a>
										<?php echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_TIPQUERY')); ?>
										<br />
										<textarea onFocus="codeAreaFocus(this);" readonly="readonly" wrap="off" name="data2lines"
											style="width:60px;" rows="<?php echo $ff_config->areamedium; ?>"
											class="inputbox"></textarea>
										<textarea onFocus="codeAreaFocus(this);" onKeyUp="codeAreaChange(this,event);" wrap="off"
											name="data2" style="width:630px;" rows="<?php echo $ff_config->areamedium; ?>"
											class="inputbox"><?php echo htmlspecialchars($row->data2, ENT_QUOTES); ?></textarea>
									</td>
									<td></td>
								</tr>
							</table>
							<?php
							$tabs->endTab();
							$tabs->startTab(BFText::_('COM_BREEZINGFORMS_ELEMENTS_QUERYCOLS'), "tab_querycols");
							?>
							<table width="100%" class="adminform">
								<tr>
									<td></td>
									<td nowrap colspan="2">
										<div style="text-align:right;">
											<input type="button" value="<?php echo BFText::_('COM_BREEZINGFORMS_TOOLBAR_NEW'); ?>"
												onclick="qcolAdd()" class="button btn btn-primary" />
											<input type="button" value="<?php echo BFText::_('COM_BREEZINGFORMS_TOOLBAR_COPY'); ?>"
												onclick="qcolCopy()" class="button btn btn-primary" />
											<input type="button"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_TOOLBAR_DELETE'); ?>"
												onclick="qcolDelete()" class="button btn btn-primary" />
										</div><br />
										<table id="qcolTable" width="100%" class="adminlist table table-striped">
											<tr>
												<th nowrap valign="top" align="center"><input id="qcolCbAll" name="qcolCbAll"
														type="checkbox" value="1" onclick="qcolCheckAll(this.checked);" /></th>
												<th width="100%" valign="top" align="left">
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TITLE'); ?>
												</th>
												<th nowrap valign="top" align="left">
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NAME'); ?>
												</th>
												<th nowrap valign="top" align="left">
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_HEADER') . ' ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_ATTRIBUTES'); ?>
												</th>
												<th nowrap valign="top" align="left">
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_DATA') . ' ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_ATTRIBUTES'); ?>
												</th>
												<th nowrap valign="top" align="left">
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_WIDTH'); ?>
												</th>
												<th nowrap valign="top" align="center" colspan="2">
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_REORDER'); ?>
												</th>
											</tr>
										</table>
									</td>
									<td></td>
								</tr>
							</table>
							<?php
						} // if
				
						if ($hasInit || $hasAction || $hasValidation) {
							$tabs->endTab();
							$tabs->startTab(BFText::_('COM_BREEZINGFORMS_ELEMENTS_SCRIPTS'), "tab_scripts");
							?>
							<table class="adminform">
								<?php
								if ($hasInit) {
									?>
									<tr>
										<td></td>
										<td colspan="2">
											<fieldset>
												<legend>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_INITSCRIPT'); ?>
												</legend>
												<table cellpadding="4" cellspacing="1" border="0">
													<tr>
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TYPE'); ?>:
														</td>
														<td nowrap>
															<input type="radio" id="script1cond1" name="script1cond" value="0"
																onclick="dispinit(this.value)" <?php if ($row->script1cond == 0)
																	echo ' checked="checked"'; ?> /><label for="script1cond1">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
															</label>
															<input type="radio" id="script1cond2" name="script1cond" value="1"
																onclick="dispinit(this.value)" <?php if ($row->script1cond == 1)
																	echo ' checked="checked"'; ?> /><label for="script1cond2">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LIBRARY'); ?>
															</label>
															<input type="radio" id="script1cond3" name="script1cond" value="2"
																onclick="dispinit(this.value)" <?php if ($row->script1cond == 2)
																	echo ' checked="checked"'; ?> /><label for="script1cond3">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CUSTOM'); ?>
															</label>
														</td>
														<td></td>
													</tr>
													<tr id="initexec" style="display:none;">
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CONDITIONS'); ?>:
														</td>
														<td nowrap>
															<input type="checkbox" id="script1flag1" name="script1flag1" value="1" <?php if ($row->script1flag1 == 1)
																echo ' checked="checked"'; ?> /><label
																for="script1flag1">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_FORMENTRY'); ?>
															</label>
															<input type="checkbox" id="script1flag2" name="script1flag2" value="1" <?php if ($row->script1flag2 == 1)
																echo ' checked="checked"'; ?> /><label
																for="script1flag2">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGEENTRY'); ?>
															</label>
														</td>
														<td></td>
													</tr>
													<tr id="initlib" style="display:none;">
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SCRIPT'); ?>:
														</td>
														<td nowrap>
															<select name="script1id" size="1" class="inputbox">
																<?php
																$scripts = $lists['scripts1'];
																for ($i = 0; $i < count($scripts); $i++) {
																	$script = $scripts[$i];
																	$selected = '';
																	if ($script->id == $row->script1id)
																		$selected = ' selected';
																	echo '<option value="' . $script->id . '"' . $selected . '>' . $script->text . '</option>';
																} // for
																?>
															</select>
														</td>
														<td></td>
													</tr>
													<tr id="initcode" style="display:none;">
														<td nowrap valign="top" colspan="2">
															<a href="#"
																onClick="codeAreaResize('script1code',<?php echo $ff_config->areasmall; ?>);">[
																<?php echo $ff_config->areasmall; ?>]
															</a>
															<a href="#"
																onClick="codeAreaResize('script1code',<?php echo $ff_config->areamedium; ?>);">[
																<?php echo $ff_config->areamedium; ?>]
															</a>
															<a href="#"
																onClick="codeAreaResize('script1code',<?php echo $ff_config->arealarge; ?>);">[
																<?php echo $ff_config->arealarge; ?>]
															</a>
															<a href="#" onClick="createInitCode();">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREATECODE'); ?>
															</a>
															<?php
															echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_TIPINIT')) . "\n";
															?>
															<br />
															<textarea onFocus="codeAreaFocus(this);" readonly="readonly" wrap="off"
																name="script1lines" style="width:60px;" rows="<?php echo $s1size; ?>"
																class="inputbox"></textarea>
															<textarea onFocus="codeAreaFocus(this);"
																onKeyUp="codeAreaChange(this,event);" wrap="off" name="script1code"
																style="width:610px;" rows="<?php echo $s1size; ?>"
																class="inputbox"><?php echo htmlspecialchars($row->script1code, ENT_QUOTES); ?></textarea>
														</td>
														<td></td>
													</tr>
												</table>
											</fieldset>
										</td>
										<td></td>
									</tr>
									<?php
								} // if hasInit
					
								if ($hasAction) {
									?>
									<tr>
										<td></td>
										<td colspan="2">
											<fieldset>
												<legend>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ACTIONSCRIPT'); ?>
												</legend>
												<table cellpadding="4" cellspacing="1" border="0">
													<tr>
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TYPE'); ?>:
														</td>
														<td nowrap>
															<input type="radio" id="script2cond1" name="script2cond" value="0"
																onclick="dispaction(this.value)" <?php if ($row->script2cond == 0)
																	echo ' checked="checked"'; ?> /><label for="script2cond1">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
															</label>
															<input type="radio" id="script2cond2" name="script2cond" value="1"
																onclick="dispaction(this.value)" <?php if ($row->script2cond == 1)
																	echo ' checked="checked"'; ?> /><label for="script2cond2">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LIBRARY'); ?>
															</label>
															<input type="radio" id="script2cond3" name="script2cond" value="2"
																onclick="dispaction(this.value)" <?php if ($row->script2cond == 2)
																	echo ' checked="checked"'; ?> /><label for="script2cond3">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CUSTOM'); ?>
															</label>
														</td>
														<td></td>
													</tr>
													<tr id="actionact" style="display:none;">
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ACTIONS'); ?>:
														</td>
														<td nowrap>
															<?php
															switch ($row->type) {
																case 'Regular Button':
																case 'Graphic Button':
																case 'Icon':
																	?>
																	<input type="checkbox" id="script2flag1" name="script2flag1" value="1" <?php if ($row->script2flag1 == 1)
																		echo ' checked="checked"'; ?> /><label
																		for="script2flag1">
																		<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CLICK'); ?>
																	</label>
																	<?php
																	break;
																default:
																	?>
																	<input type="checkbox" id="script2flag1" name="script2flag1" value="1" <?php if ($row->script2flag1 == 1)
																		echo ' checked="checked"'; ?> /><label
																		for="script2flag1">
																		<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CLICK'); ?>
																	</label>
																	<input type="checkbox" id="script2flag2" name="script2flag2" value="1" <?php if ($row->script2flag2 == 1)
																		echo ' checked="checked"'; ?> /><label
																		for="script2flag2">
																		<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BLUR'); ?>
																	</label>
																	<input type="checkbox" id="script2flag3" name="script2flag3" value="1" <?php if ($row->script2flag3 == 1)
																		echo ' checked="checked"'; ?> /><label
																		for="script2flag3">
																		<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CHANGE'); ?>
																	</label>
																	<input type="checkbox" id="script2flag4" name="script2flag4" value="1" <?php if ($row->script2flag4 == 1)
																		echo ' checked="checked"'; ?> /><label
																		for="script2flag4">
																		<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_FOCUS'); ?>
																	</label>
																	<input type="checkbox" id="script2flag5" name="script2flag5" value="1" <?php if ($row->script2flag5 == 1)
																		echo ' checked="checked"'; ?> /><label
																		for="script2flag5">
																		<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELECTION'); ?>
																	</label>
																	<?php
																	break;
															} // switch
															?>
														</td>
														<td></td>
													</tr>
													<tr id="actionlib" style="display:none;">
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SCRIPT'); ?>:
														</td>
														<td nowrap>
															<select name="script2id" size="1" class="inputbox">
																<?php
																$scripts = $lists['scripts2'];
																for ($i = 0; $i < count($scripts); $i++) {
																	$script = $scripts[$i];
																	$selected = '';
																	if ($script->id == $row->script2id)
																		$selected = ' selected';
																	echo '<option value="' . $script->id . '"' . $selected . '>' . $script->text . '</option>';
																} // for
																?>
															</select>
														</td>
														<td></td>
													</tr>
													<tr id="actioncode" style="display:none;">
														<td nowrap valign="top" colspan="2">
															<a href="#"
																onClick="codeAreaResize('script2code',<?php echo $ff_config->areasmall; ?>);">[
																<?php echo $ff_config->areasmall; ?>]
															</a>
															<a href="#"
																onClick="codeAreaResize('script2code',<?php echo $ff_config->areamedium; ?>);">[
																<?php echo $ff_config->areamedium; ?>]
															</a>
															<a href="#"
																onClick="codeAreaResize('script2code',<?php echo $ff_config->arealarge; ?>);">[
																<?php echo $ff_config->arealarge; ?>]
															</a>
															<a href="#" onClick="createActionCode();">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREATECODE'); ?>
															</a>
															<?php
															echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_TIPACTION')) . "\n";
															?>
															<br />
															<textarea onFocus="codeAreaFocus(this);" readonly="readonly" wrap="off"
																name="script2lines" style="width:60px;" rows="<?php echo $s2size; ?>"
																class="inputbox"></textarea>
															<textarea onFocus="codeAreaFocus(this);"
																onKeyUp="codeAreaChange(this,event);" wrap="off" name="script2code"
																style="width:610px;" rows="<?php echo $s2size; ?>"
																class="inputbox"><?php echo htmlspecialchars($row->script2code, ENT_QUOTES); ?></textarea>
														</td>
														<td></td>
													</tr>
												</table>
											</fieldset>
										</td>
										<td></td>
									</tr>
									<?php
								} // if hasAction
					
								if ($hasValidation) {
									?>
									<tr>
										<td></td>
										<td colspan="2">
											<fieldset>
												<legend>
													<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_VALIDSCRIPT'); ?>
												</legend>
												<table cellpadding="4" cellspacing="1" border="0">
													<tr>
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TYPE'); ?>:
														</td>
														<td nowrap>
															<input type="radio" id="script3cond1" name="script3cond" value="0"
																onclick="dispvalidation(this.value)" <?php if ($row->script3cond == 0)
																	echo ' checked="checked"'; ?> /><label for="script3cond1">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NONE'); ?>
															</label>
															<input type="radio" id="script3cond2" name="script3cond" value="1"
																onclick="dispvalidation(this.value)" <?php if ($row->script3cond == 1)
																	echo ' checked="checked"'; ?> /><label for="script3cond2">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_LIBRARY'); ?>
															</label>
															<input type="radio" id="script3cond3" name="script3cond" value="2"
																onclick="dispvalidation(this.value)" <?php if ($row->script3cond == 2)
																	echo ' checked="checked"'; ?> /><label for="script3cond3">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CUSTOM'); ?>
															</label>
														</td>
														<td></td>
													</tr>
													<tr id="validationmsg" style="display:none;">
														<td nowrap valign="top">
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MESSAGE'); ?>:
														</td>
														<td nowrap valign="top">
															<input type="text" size="50" maxlength="255" name="script3msg"
																value="<?php echo $row->script3msg; ?>" class="inputbox" />
															<?php
															echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_TIPMESSAGE')) . "\n";
															?>
														</td>
														<td></td>
													</tr>
													<tr id="validationlib" style="display:none;">
														<td nowrap>
															<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SCRIPT'); ?>:
														</td>
														<td nowrap>
															<select name="script3id" size="1" class="inputbox">
																<?php
																$scripts = $lists['scripts3'];
																for ($i = 0; $i < count($scripts); $i++) {
																	$script = $scripts[$i];
																	$selected = '';
																	if ($script->id == $row->script3id)
																		$selected = ' selected';
																	echo '<option value="' . $script->id . '"' . $selected . '>' . $script->text . '</option>';
																} // for
																?>
															</select>
														</td>
														<td></td>
													</tr>
													<tr id="validationcode" style="display:none;">
														<td nowrap valign="top" colspan="2">
															<a href="#"
																onClick="codeAreaResize('script3code',<?php echo $ff_config->areasmall; ?>);">[
																<?php echo $ff_config->areasmall; ?>]
															</a>
															<a href="#"
																onClick="codeAreaResize('script3code',<?php echo $ff_config->areamedium; ?>);">[
																<?php echo $ff_config->areamedium; ?>]
															</a>
															<a href="#"
																onClick="codeAreaResize('script3code',<?php echo $ff_config->arealarge; ?>);">[
																<?php echo $ff_config->arealarge; ?>]
															</a>
															<a href="#" onClick="createValidationCode();">
																<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREATECODE'); ?>
															</a>
															<?php
															echo bf_ToolTip(BFText::_('COM_BREEZINGFORMS_ELEMENTS_TIPVALID')) . "\n";
															?>
															<br />
															<textarea onFocus="codeAreaFocus(this);" readonly="readonly" wrap="off"
																name="script3lines" style="width:60px;" rows="<?php echo $s3size; ?>"
																class="inputbox"></textarea>
															<textarea onFocus="codeAreaFocus(this);"
																onKeyUp="codeAreaChange(this,event);" wrap="off" name="script3code"
																style="width:610px;" rows="<?php echo $s3size; ?>"
																class="inputbox"><?php echo htmlspecialchars($row->script3code, ENT_QUOTES); ?></textarea>
														</td>
														<td></td>
													</tr>
												</table>
											</fieldset>
										</td>
										<td></td>
									</tr>
									<?php
								} // if hasValidation
								?>
							</table>
							<?php
						} // if hasInit, hasAction or hasValidation
						$tabs->endTab();
						$tabs->endPane();
						?>
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td><br />
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap style="text-align:right">
						<input class="btn btn-primary" type="submit"
							onclick="<?php if ($row->type == 'Query List')
								echo "\t\t\t\tsaveQueryList();" ?>document.getElementById('task').value='save';"
								value="<?php echo BFText::_('COM_BREEZINGFORMS_TOOLBAR_SAVE'); ?>" />&nbsp;&nbsp;
						<input class="btn btn-primary" type="submit" onclick="document.getElementById('task').value='cancel';"
							value="<?php echo BFText::_('COM_BREEZINGFORMS_TOOLBAR_CANCEL'); ?>" />
					</td>
					<td></td>
				</tr>
			</table>
			<?php
			if ($row->type == 'Query List') {
				?>
				<input type="hidden" name="data1" value="<?php echo htmlspecialchars($row->data1, ENT_QUOTES); ?>" />
				<input type="hidden" name="data3" value="<?php echo htmlspecialchars($row->data3, ENT_QUOTES); ?>" />
				<?php
			} // if
			?>
			<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
			<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" id="task" name="task" value="" />
			<input type="hidden" name="act" value="editpage" />
			<input type="hidden" name="form" value="<?php echo $row->form; ?>" />
			<input type="hidden" name="page" value="<?php echo $row->page; ?>" />
			<input type="hidden" name="type" value="<?php echo $row->type; ?>" />
		</form>
		<?php
	} // edit

	static function listitems($option, $pkg, &$form, $page, &$rows, $prevmode, &$checkedIds)
	{
		global $ff_processor, $ff_mospath, $ff_mossite, $ff_admicon, $ff_comsite, $ff_config,
		$ff_request, $ff_version, $database, $my;
		$mainframe = Factory::getApplication();
		$database = Factory::getContainer()->get(DatabaseInterface::class);

		$ff_mossite = Uri::root();
		?>
		<script type="text/javascript">
					<!--
					var bf_submitbutton = function(pressbutton)
					{
						var form = document.adminForm;
						switch (pressbutton) {
												case 'close':
													location.href="index.php?option=com_breezingforms&act=manageforms";
													return;
													break;
							case 'copy':
							case 'move':
							case 'publish':
							case 'unpublish':
							case 'remove':
								if (form.boxchecked.value==0) {
									alert("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELELEMENTS'); ?>");
			return;
								} // if
			break;
							default:
			break;
						} // switch
			switch (pressbutton) {
				case 'share':
					form.act.value = 'share';
					break;
				case 'sort':
					if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ASKSORT'); ?>")) return;
					break;
				case 'remove':
					if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ASKDELELEMENTS'); ?>")) return;
					break;
				case 'delpage':
					if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ASKDELPAGE'); ?>")) return;
					break;
				case 'movepos':
					if (form.movepixels.value == '') {
						alert("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_ENTPIXMOVE'); ?>");
						return;
					} // if
					var nonDigits = /\D/;
					if (nonDigits.test(form.movepixels.value)) {
						alert("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PIXMOVEINT'); ?>");
						return;
					} // if
					break;
				default:
					break;
			} // switch
			submitform(pressbutton);
					}; // submitbutton

			if (typeof Joomla != "undefined") {
				Joomla.submitbutton = bf_submitbutton;
			}

			submitbutton = bf_submitbutton;

			function changepage(newpage) {
				document.adminForm.page.value = newpage;
				submitform('');
			} // changepage

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
					submitbutton(task);
				}
				return false;
			} // listItemTask

			<?php if ($form->prevmode > 0) { ?>
				ff_coords = [
					<?php
					for ($i = 0; $i < count($rows); $i++) {
						$row = $rows[$i];
						if ($i)
							echo ",\n";
						echo
							"\t\t\t\t["
							. intval(isVisibleElement($row->type)) . ','
							. intval($row->posx) . ',' . intval($row->posx) . ',' . intval($row->posxmode) . ','
							. intval($row->posy) . ',' . intval($row->posy) . ',' . intval($row->posymode) . ']';
					} // for
					if ($i)
						echo "\n";
					else
						echo "\t\t\t\t0";
					?>
				];

				var highlightTmpColor = "";
				var highlightTmpPadding = "";

				function highlightElement(elementIndex) {
					var f = document.adminForm;

					<?php if ($ff_config->stylesheet) { ?>
						var fd = ff_prevframe.document;
					<?php } else { ?>
						var fd = document;
					<?php } // endif  ?>

					var cb = eval('f.cb' + elementIndex);
					highlightTmpColor = fd.getElementById('ff_div' + cb.value).style.backgroundColor;
					highlightTmpPadding = fd.getElementById('ff_div' + cb.value).style.padding;

					fd.getElementById('ff_div' + cb.value).style.backgroundColor = "red";
					fd.getElementById('ff_div' + cb.value).style.padding = "5px";
				}

				function unhighlightElement(elementIndex) {
					var f = document.adminForm;

					<?php if ($ff_config->stylesheet) { ?>
						var fd = ff_prevframe.document;
					<?php } else { ?>
						var fd = document;
					<?php } // endif  ?>

					var cb = eval('f.cb' + elementIndex);

					fd.getElementById('ff_div' + cb.value).style.backgroundColor = highlightTmpColor;
					fd.getElementById('ff_div' + cb.value).style.padding = highlightTmpPadding;
				}

				function moveElements(direction) {
					var f = document.adminForm;
					var step = parseInt(f.id_movepixels.value);
					if (step == 0) return;
					<?php if ($ff_config->stylesheet) { ?>
						var fd = ff_prevframe.document;
					<?php } else { ?>
						var fd = document;
					<?php } // endif  ?>
					var i;
					for (i = 0; i < <?php echo count($rows); ?>; i++) {
						var cb = eval('f.cb' + i);
						if (cb.checked && ff_coords[i][0]) {
							var el = fd.getElementById('ff_div' + cb.value).style;
							var x, y, u;
							switch (direction) {
								case 'left':
									x = ff_coords[i][2];
									if (ff_coords[i][3]) u = '%'; else u = 'px';
									if (x >= 0) {
										if (x > step)
											x -= step;
										else
											x = 0;
										el.left = x + u;
									} else {
										x -= step;
										el.right = (-x) + u;
									} // if
									ff_coords[i][2] = x;
									break;
								case 'right':
									x = ff_coords[i][2];
									if (ff_coords[i][3]) u = '%'; else u = 'px';
									if (x >= 0) {
										x += step;
										el.left = x + u;
									} else {
										if ((-x) > step)
											x += step;
										else
											x = -1;
										el.right = (-y) + u;
									} // if
									ff_coords[i][2] = x;
									break;
								case 'up':
									y = ff_coords[i][5];
									if (ff_coords[i][6]) u = '%'; else u = 'px';
									if (y >= 0) {
										if (y > step)
											y -= step;
										else
											y = 0;
										el.top = y + u;
									} else {
										y -= step;
										el.bottom = (-y) + u;
									} // if
									ff_coords[i][5] = y;
									break;
								case 'down':
									y = ff_coords[i][5];
									if (ff_coords[i][6]) u = '%'; else u = 'px';
									if (y >= 0) {
										y += step;
										el.top = y + u;
									} else {
										if ((-y) > step)
											y += step;
										else
											y = -1;
										el.bottom = (-y) + u;
									} // if
									ff_coords[i][5] = y;
									break;
								default: ;
							} // switch
						} // if
					} // for
					var disabled = true;
					for (i = 0; i < <?php echo count($rows); ?>; i++)
						if (ff_coords[i][1] != ff_coords[i][2] || ff_coords[i][4] != ff_coords[i][5]) {
							disabled = false;
							break;
						} // if
					f.savepos.disabled = disabled;
					f.restpos.disabled = disabled;
				} // moveElements

				function savePositions() {
					var f = document.adminForm;
					<?php if ($ff_config->stylesheet) { ?>
						var fd = ff_prevframe.document;
					<?php } else { ?>
						var fd = document;
					<?php } // endif  ?>
					var i;
					var pos = '';
					for (i = 0; i < <?php echo count($rows); ?>; i++)
						if (ff_coords[i][1] != ff_coords[i][2] || ff_coords[i][4] != ff_coords[i][5]) {
							var cb = eval('f.cb' + i);
							var el = fd.getElementById('ff_div' + cb.value).style;
							if (pos != '') pos += ',';
							pos += cb.value + ',' + ff_coords[i][2] + ',' + ff_coords[i][5];
						} // if
					f.movepositions.value = pos;

					submitbutton('movepos');
				} // savePositions

				function restorePositions() {
					var f = document.adminForm;
					<?php if ($ff_config->stylesheet) { ?>
						var fd = ff_prevframe.document;
					<?php } else { ?>
						var fd = document;
					<?php } // endif  ?>
					var i;
					for (i = 0; i < <?php echo count($rows); ?>; i++)
						if (ff_coords[i][1] != ff_coords[i][2] || ff_coords[i][4] != ff_coords[i][5]) {
							var cb = eval('f.cb' + i);
							var el = fd.getElementById('ff_div' + cb.value).style;
							var x = ff_coords[i][2] = ff_coords[i][1];
							var y = ff_coords[i][5] = ff_coords[i][4];
							var u;
							if (ff_coords[i][3]) u = '%'; else u = 'px';
							if (x >= 0) el.left = x + u; else el.right = (-x) + u;
							if (ff_coords[i][6]) u = '%'; else u = 'px';
							if (y >= 0) el.top = y + u; else el.bottom = (-y) + u;
						} // if
					f.savepos.disabled = true;
					f.restpos.disabled = true;
				} // restorePositions
			<?php } // endif  ?>

			<?php if ($prevmode == 'submit') { ?>
				onload = function () {
					submitform('');
				} // onload
			<?php } // endif  ?>
			//-->
		</script>
		<div id="overDiv" style="position:absolute;visibility:hidden;z-index:1000;"></div>

		<table cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td nowrap>
					<table class="adminheading">
						<tr>
							<th class="edit" nowrap>BreezingForms
								<?php echo $ff_version; ?><br /><span class="componentheading">
									<?php echo $form->title; ?>
									<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGE'); ?>
									<?php echo $page; ?>
								</span>
							</th>
						</tr>
					</table>
				</td>
				<td width="100%" align="right" nowrap>
					<?php
					JToolBarHelper::custom('new', 'new.png', 'new_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_NEW'), false);
					JToolBarHelper::custom('editform', 'edit.png', 'edit_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_EDITFORM'), false);
					JToolBarHelper::custom('copy', 'copy.png', 'copy_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_COPY'), false);
					JToolBarHelper::custom('move', 'move.png', 'move_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_MOVE'), false);
					JToolBarHelper::custom('publish', 'publish.png', 'publish_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_PUBLISH'), false);
					JToolBarHelper::custom('unpublish', 'unpublish.png', 'unpublish_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_UNPUBLISH'), false);
					JToolBarHelper::custom('remove', 'delete.png', 'delete_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_DELETE'), false);
					JToolBarHelper::custom('close', 'cancel.png', 'cancel_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_QUICKMODE_CLOSE'), false);
					?>
				</td>
			</tr>
		</table>
		<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
			<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist table table-striped">
				<tr>
					<th nowrap align="center"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th nowrap align="left">
						<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TITLE'); ?>
					</th>
					<th nowrap align="left">
						<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_NAME'); ?>
					</th>
					<th nowrap align="left">
						<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TYPE'); ?>
					</th>
					<th nowrap align="center">
						<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PUBLISHED'); ?>
					</th>
					<th nowrap align="center" colspan="2"><a href="#sort" onclick="submitbutton('sort')">
							<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_REORDER'); ?>
						</a></th>
					<th nowrap align="center">X</th>
					<th nowrap align="center">Y</th>
					<th nowrap align="center">
						<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SCRIPTID'); ?>
					</th>
					<th width="100%"></th>
				</tr>
				<?php
				$k = 0;
				$boxchecked = 0;
				for ($i = 0; $i < count($rows); $i++) {
					$row = $rows[$i];
					$checked = '';
					if (in_array($row->id, $checkedIds)) {
						$checked = 'checked="checked"';
						$boxchecked++;
					} // if
					?>
					<tr class="row<?php echo $k ?>">
						<td nowrap align="center"><input type="checkbox" id="cb<?php echo $i; ?>" name="ids[]"
								value="<?php echo $row->id; ?>" onclick="Joomla.isChecked(this.checked);" <?php echo $checked; ?> />
						</td>
						<td nowrap align="left">
							<div id="hoverItem_ff_div<?php echo $row->id; ?>"><a href="#edit"
									onmouseout="unhighlightElement(<?php echo $i ?>)"
									onmouseover="highlightElement(<?php echo $i ?>)"
									onclick="return listItemTask('cb<?php echo $i; ?>','edit')">
									<?php echo $row->title; ?>
								</a></div>
						</td>
						<td nowrap align="left">
							<?php echo $row->name; ?>
						</td>
						<td nowrap align="left">
							<?php echo HTML_facileFormsElement::displayType($row->type); ?>
						</td>
						<td nowrap align="center">
							<?php
							if ($row->published == "1") {
								echo "<a href=\"#unpublish\" onClick=\"return listItemTask('cb" . $i . "','unpublish')\"><img src=\"components/com_breezingforms/images/icons/publish_g.png\" alt=\"+\" border=\"0\" /></a>";
							} else {
								echo "<a href=\"#publish\" onClick=\"return listItemTask('cb" . $i . "','publish')\"><img src=\"components/com_breezingforms/images/icons/publish_x.png\" alt=\"-\" border=\"0\" /></a>";
							} // if
							?>
						</td>
						<td nowrap align="right">
							<?php
							if ($i > 0)
								echo "<a href=\"#orderup\" onClick=\"return listItemTask('cb" . $i . "','orderup')\"><img src=\"components/com_breezingforms/images/icons/uparrow.png\" alt=\"^\" border=\"0\" /></a>";
							?>
						</td>
						<td nowrap align="left">
							<?php
							if ($i < count($rows) - 1)
								echo "<a href=\"#orderdown\" onClick=\"return listItemTask('cb" . $i . "','orderdown')\"><img src=\"components/com_breezingforms/images/icons/downarrow.png\" alt=\"v\" border=\"0\" /></a>";
							?>
						</td>
						<td nowrap align="right">
							<?php echo $row->posx;
							if ($row->posxmode)
								echo '%';
							else
								echo 'px'; ?>
						</td>
						<td nowrap align="right">
							<?php echo $row->posy;
							if ($row->posymode)
								echo '%';
							else
								echo 'px'; ?>
						</td>
						<td nowrap align="right">
							<?php echo $row->id; ?>
						</td>
						<td></td>
					</tr>
					<?php
					$k = 1 - $k;
				} // for
				?>
			</table>

			<?php
			if ($form->prevmode > 0) {
				if ($form->prevmode == 1) {
					?>
					<br /> <br />
					<?php
				} else {
					?>
					<script type="text/javascript"
						src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/wz_dragdrop/wz_dragdrop.js' ?>"></script>
					<!-- BEGIN OF SURFACE -->
					<div id="SelectOptionDialog" style="background-color: #cccccc;position:absolute;top:233px;right:15px;z-index:100;">
						<?php
				}
				?>
					<table cellpadding="4" cellspacing="1" border="0" class="adminform" style="width:100%;">
						<tr>
							<th colspan="2" class="title">BreezingForms -
								<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGELAY'); ?>
							</th>
						</tr>
						<tr>
							<td colspan="2">
								<table class="menubar" cellpadding="3" cellspacing="0" border="0">
									<tr>
										<td nowrap class="menudottedline" align="right">


											<input class="btn btn-warning" onclick="submitbutton('addbefore');" type="submit"
												value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_ELEMENTS_ADDPAGEBEFORE'), ENT_QUOTES, 'UTF-8'); ?>" />
											&nbsp;&nbsp;
											<input class="btn btn-warning" onclick="submitbutton('addbehind');" type="submit"
												value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_ELEMENTS_ADDPAGEBEHIND'), ENT_QUOTES, 'UTF-8'); ?>" />
											<?php
											if ($form->pages > 1) {
												?>
												&nbsp;&nbsp;<input class="btn btn-primary" onclick="submitbutton('movepage');"
													type="submit"
													value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_ELEMENTS_MOVEPG'), ENT_QUOTES, 'UTF-8'); ?>" />
												&nbsp;&nbsp;<input class="btn btn-primary" onclick="submitbutton('delpage');"
													type="submit"
													value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_ELEMENTS_DELPAGE'), ENT_QUOTES, 'UTF-8'); ?>" />

												<?php
											} // if
											?>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td valign="top">
								<table class="menubar" cellpadding="0" cellspacing="0" border="0">
									<tr>
										<td nowrap colspan="5" style="text-align:center">
											<?php
											if ($form->prevmode == 2) {
												?>
												<?php echo BFText::_('COM_BREEZINGFORMS_DRAGGING') ?> <br />
												<?php echo BFText::_('COM_BREEZINGFORMS_DRAGGING_ON') ?> <input type="radio"
													id="draggingEnabled" name="dragToggle"
													onclick="dd.elements.SelectOptionDialog.setDraggable(true);" />
												<?php echo BFText::_('COM_BREEZINGFORMS_DRAGGING_OFF') ?> <input id="draggingDisabled"
													checked type="radio" name="dragToggle"
													onclick="dd.elements.SelectOptionDialog.setDraggable(false);" />
												<br />
												<?php
											}
											?>
											<input type="checkbox" id="gridshow" name="gridshow" onclick="submitbutton('gridshow');"
												value="1" <?php if ($ff_config->gridshow == 1)
													echo ' checked="checked"'; ?> /><label
												for="gridshow">
												<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_GRID'); ?>
											</label>
											<hr />
										</td>
									</tr>
									<tr>
										<td nowrap colspan="5" style="text-align:center">
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MOVEPIX'); ?>:<br />
											<input type="text" size="6" maxlength="6" id="id_movepixels" name="movepixels"
												value="<?php echo is_int($ff_config->movepixels) ? is_int($ff_config->movepixels) : 5; ?>"
												class="inputbox" />
											<br />
										</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
										<td align="center">
											<a href="javascript:moveElements('up');">
												<img src="<?php echo $ff_admicon; ?>/moveup_f2.png" width="16" height="16" alt="up"
													name="moveup" border="0" align="middle" />
											</a>
										</td>
										<td></td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td>
											<a href="javascript:moveElements('left');">
												<img src="<?php echo $ff_admicon; ?>/movelt_f2.png" width="16" height="16"
													alt="left" name="moveleft" border="0" align="middle" />
											</a>
										</td>
										<td></td>
										<td align="right">
											<a href="javascript:moveElements('right');">
												<img src="<?php echo $ff_admicon; ?>/movert_f2.png" width="16" height="16"
													alt="right" name="moveright" border="0" align="middle" />
											</a>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td></td>
										<td align="center">
											<a href="javascript:moveElements('down');">
												<img src="<?php echo $ff_admicon; ?>/movedn_f2.png" width="16" height="16"
													alt="down" name="movedown" border="0" align="middle" />
											</a>
										</td>
										<td></td>
										<td>
											<hr />
										</td>
									</tr>
									<tr>
										<td colspan="5" style="text-align:center">
											<input class="btn btn-primary" id="savepos" type="button"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SAVE'); ?>"
												onclick="savePositions();" disabled="disabled" /><br />
											<hr />
											<input class="btn btn-primary" id="restpos" type="button"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_RESTORE'); ?>"
												onclick="restorePositions();" disabled="disabled" />
											<hr />
										</td>
									</tr>
									<tr>
										<td colspan="5" style="text-align:center">
											<?php
											for ($p = 1; $p <= $form->pages; $p++) {
												$attribute = '';
												if ($p == $page)
													$attribute = 'disabled="disabled"';
												echo '<input class="btn btn-primary" type="button" value="' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGE') . ' ' . $p . '" onclick="changepage(\'' . $p . '\');" ' . $attribute . '/><br/>';
											} // for
											?>
										</td>
									</tr>
								</table>
							</td>
							<td align="center">
								<?php
								if ($ff_config->stylesheet) {
									// compose iframe url
									$url =
										$ff_mossite . 'index.php'
										. '?option=com_breezingforms'
										. '&amp;tmpl=component'
										. '&amp;Itemid=0'
										. '&amp;ff_form=' . $form->id
										. '&amp;ff_frame=1'
										. '&amp;ff_runmode=' . _FF_RUNMODE_PREVIEW
										. '&amp;ff_page=' . $page;
									reset($ff_request);
									//while (list($prop, $val) = each($ff_request))
									foreach ($ff_request as $prop => $val)
										$url .= '&amp;' . $prop . '=' . urlencode($val);

									// prepare iframe width
									$framewidth = 'width="';
									if ($form->widthmode)
										$framewidth .= $form->prevwidth . '" ';
									else
										$framewidth .= $form->width . '" ';

									// prepare iframe height
									$frameheight = '';
									if (!$form->heightmode)
										$frameheight = 'height="' . $form->height . '" ';

									// assemble iframe parameters
									$params =
										'name="ff_prevframe" ' .
										'id="ff_prevframe" ' .
										'src="' . $url . '" ' .
										$framewidth .
										$frameheight .
										'frameborder="0" ' .
										'scrolling="no"';
									?>
									<iframe <?php echo $params; ?>>
										<p>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BROWSER1'); ?>
										</p>
										<p>
											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BROWSER2'); ?>
										</p>
									</iframe>
									<?php
								} else {
									$tstyle = ' style="';
									$dstyle = '';
									if ($form->widthmode) {
										$tstyle .= 'width:' . $form->prevwidth . 'px;';
									} else {
										$tstyle .= 'width:' . $form->width . 'px;';
										$dstyle .= 'width:' . $form->width . 'px;';
									} // else
									if (!$form->heightmode) {
										$tstyle .= 'height:' . $form->height . 'px;';
										$dstyle .= 'height:' . $form->height . 'px;';
									} // if
									$tstyle .= '"';
									?>
									<table cellpadding="0" cellspacing="0" border="0" <?php echo $tstyle; ?>>
										<tr>
											<td>
												<div style="position:relative;left:0px;top:0px;<?php echo $dstyle; ?>">
													<?php
													$myUser = Factory::getUser();

													$database->setQuery("select id from #__users where lower(username)=lower('" . $myUser->get('username', '') . "')");
													$id = $database->loadResult();
													if ($id)
														$myUser->get('id', -1);
													$curdir = getcwd();
													chdir($ff_mospath);
													$ff_processor = new HTML_facileFormsProcessor(_FF_RUNMODE_PREVIEW, false, $form->id, $page, $option);
													if ($prevmode == 'submit')
														$ff_processor->submit();
													else
														$ff_processor->view();
													chdir($curdir);
													?>
												</div>
											</td>
										</tr>
									</table>
									<?php
								} // if
								?>
							</td>
						</tr>
					</table>

					<?php
					if ($form->prevmode == 2) {
						?>
					</div>

					<script type="text/javascript">
						<!-
						-
							SET_DHTML('SelectOptionDialog');
							dd.elements.SelectOptionDialog.setDraggable(false);
							//-->
							</script>
			
							<input type="hidden" id="ff_itemPositions" name="ff_itemPositions" value=""/>
			
				<?php
					} // if
			} // if $form->prevmode > 0
			?>

					<input type="hidden" name="boxchecked" value="<?php echo $boxchecked; ?>" />
					<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
					<input type="hidden" name="option" value="<?php echo $option; ?>" />
					<input type="hidden" name="act" value="editpage" />
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="form" value="<?php echo $form->id; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="hidden" name="movepositions" value="" />
				</form>
		<?php
	} // listitems

	static function getDestination($option, $pkg, $form, $page, &$ids, &$sellist, $action)
	{
		if ($action == 'copysave')
			$title = BFText::_('COM_BREEZINGFORMS_ELEMENTS_COPY');
		else
			$title = BFText::_('COM_BREEZINGFORMS_ELEMENTS_MOVE');
		?>
				<script type="text/javascript">
				<!--
				var bf_submitbutton = function(pressbutton) {
					var form = document.adminForm;
					if (pressbutton == 'cancel') {
						submitform( pressbutton );
						return;
					} // if
					// do field validation
					if (!getSelectedValue('adminForm', 'destination')) {
						alert( "<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELFORMPAGE'); ?>" );
					} else {
						submitform( pressbutton );
					} // if
				} // submitbutton

						if(typeof Joomla != "undefined"){
							Joomla.submitbutton = bf_submitbutton;
						}

						submitbutton = bf_submitbutton;

				//-->
				</script>
				<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
				<table cellpadding="4" cellspacing="1" border="0" class="adminform" style="width:300px;">

					<tr><th colspan="3" class="title"><?php echo $title; ?></th></tr>
					<tr>
						<td></td>
						<td style="text-align:center"  valign="top">
							<strong><?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_TOFORMPAGE'); ?>:</strong>
							<br/><br/><?php echo $sellist; ?><br /><br />
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td nowrap style="text-align:right">
											<input class="btn btn-primary" onclick="submitbutton('<?php echo $action; ?>');" type="submit" value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_TOOLBAR_CONTINUE'), ENT_QUOTES, 'UTF-8'); ?>"/>
											&nbsp;&nbsp;
											<input class="btn btn-primary" onclick="submitbutton('cancel');" type="submit" value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_TOOLBAR_CANCEL'), ENT_QUOTES, 'UTF-8'); ?>"/>
						</td>
						<td></td>
					</tr>
				</table>
				<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
				<input type="hidden" name="option" value="<?php echo $option; ?>" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="act" value="editpage" />
				<input type="hidden" name="form" value="<?php echo $form; ?>" />
				<input type="hidden" name="page" value="<?php echo $page; ?>" />
		<?php
		if (count($ids))
			foreach ($ids as $id) {
				echo '<input type="hidden" name="ids[]" value="' . $id . '" />';
			} // foreach
		?>
				</form>
		<?php
	} // getDestination

	static function getPagedest($option, $pkg, $form, $page, &$sellist)
	{
		?>
				<script type="text/javascript">
				<!--
				function submitbutton(pressbutton) {
					var form = document.adminForm;
					if (pressbutton == 'cancel') {
						submitform( pressbutton );
						return;
					} // if
					// do field validation
					if (!getSelectedValue('adminForm', 'destination')) {
						alert( "<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELPAGEMOVE'); ?>" );
					} else {
						submitform( pressbutton );
					} // if
				} // submitbutton
				//-->
				</script>
				<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
				<table cellpadding="4" cellspacing="1" border="0" class="adminform" style="width:300px;">
					<tr><th colspan="3" class="title">BreezingForms - <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_MOVEPAGE'); ?></th></tr>
					<tr>
						<td></td>
						<td style="text-align:center" valign="top">
							<strong><?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELNRPAGE'); ?>:</strong>
							<br/><br/><?php echo $sellist; ?><br /><br />
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td nowrap style="text-align:right">
												<input class="btn btn-primary" onclick="submitbutton('movepagesave');" type="submit" value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_TOOLBAR_CONTINUE'), ENT_QUOTES, 'UTF-8'); ?>"/>
												&nbsp;&nbsp;
												<input class="btn btn-primary" onclick="submitbutton('cancel');" type="submit" value="<?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_TOOLBAR_CANCEL'), ENT_QUOTES, 'UTF-8'); ?>"/>
						</td>
						<td></td>
					</tr>
				</table>
				<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
				<input type="hidden" name="option" value="<?php echo $option; ?>" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="act" value="editpage" />
				<input type="hidden" name="form" value="<?php echo $form; ?>" />
				<input type="hidden" name="page" value="<?php echo $page; ?>" />
				</form>
		<?php
	} // getPagedest

} // class HTML_facileFormsElement
?>