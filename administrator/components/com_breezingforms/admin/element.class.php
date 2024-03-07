<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.4.6
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\HTML\HTMLHelper;

require_once($ff_admpath . '/admin/element.html.php');

class facileFormsElement
{
	static function edit($option, $tabpane, $pkg, $form, $page, $ids, $newtype)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsElements($database);
		if ($newtype == '')
			$row->load($ids[0]);
		else {
			$row->form = $form;
			$row->page = $page;
			$row->published = 1;
			$row->logging = 1;
			$row->posx = 0;
			$row->posy = 0;
			$row->width = 0;
			$row->height = 0;
			$row->flag1 = 0;
			$row->flag2 = 0;
			$row->script1flag1 = 1;
			$row->script1flag2 = 0;
			$row->script2flag1 = 1;
			$row->script2flag2 = 0;
			$row->script2flag3 = 0;
			$row->script2flag4 = 0;
			$row->script2flag5 = 0;
			$database->setQuery(
				"select max(ordering)+1 from #__facileforms_elements " .
				"where form=$form and page=$page"
			);
			$row->ordering = $database->loadResult();

			switch ($newtype) {
				case 'Rectangle':
					$row->type = $newtype;
					$row->logging = 0;
					$row->posx = 10;
					$row->posy = 10;
					$row->width = 100;
					$row->height = 50;
					$row->data1 = '1px solid black';    // border
					$row->data2 = '#eeeeee';            // background color
					break;
				case 'Hidden Input':
					$row->type = $newtype;
					$row->data1 = 'Hiddeninput';
					break;
				case 'Image':
					$row->type = $newtype;
					$row->logging = 0;
					$row->posx = 10;
					$row->posy = 50;
					$row->data1 = '{ff_images}/pizzashop/margherita.jpg';
					$row->data2 = 'Margherita';
					break;
				case 'Tooltip':
					$row->type = $newtype;
					$row->logging = 0;
					$row->posx = 100;
					$row->posy = 50;
					$row->flag1 = 0; // 0-tooltip 1-warning 2-custom
					$row->data2 = 'Some <em>hint</em> or <strong>warning</strong> for the user';
					break;
				case 'Checkbox':
					$row->type = $newtype;
					$row->posx = 10;
					$row->posy = 50;
					$row->data1 = 'cb';
					$row->data2 = 'Checkbox';
					break;
				case 'Radio Button':
					$row->type = $newtype;
					$row->posx = 10;
					$row->posy = 50;
					$row->data1 = 'rb';
					$row->data2 = 'Radiobutton';
					break;
				case 'Regular Button':
					$row->type = $newtype;
					$row->class2 = 'button';
					$row->logging = 0;
					$row->posx = 10;
					$row->posy = 100;
					$row->data2 = 'Regularbutton';
					break;
				case 'Graphic Button':
					$row->type = $newtype;
					$row->class2 = 'button';
					$row->logging = 0;
					$row->posx = 10;
					$row->posy = 40;
					$row->flag1 = 1; // caption below
					$row->data1 = '{ff_images}/icons/movert_f2.png';
					$row->data2 = 'Next';
					break;
				case 'Icon':
					$row->type = $newtype;
					$row->logging = 0;
					$row->posx = 10;
					$row->posy = 80;
					$row->flag1 = 1; // caption below
					$row->data1 = '{mossite}/components/com_breezingforms/images/next.png';
					$row->data3 = '{mossite}/components/com_breezingforms/images/next_f2.png';
					$row->data2 = '<font size="2"><strong>Next</strong></font>';
					break;
				case 'Text':
					$row->type = $newtype;
					$row->class2 = 'inputbox';
					$row->posx = 10;
					$row->posy = 10;
					$row->width = 6;
					$row->height = 6;
					break;
				case 'File Upload':
					$row->type = $newtype;
					$row->class2 = 'inputbox';
					$row->posx = 10;
					$row->posy = 30;
					$row->width = 50;
					$row->height = 2000000;
					$row->data1 = '{ff_uploads}';
					$row->data2 = 'text/*,application/zip';
					break;
				case 'Textarea':
					$row->type = $newtype;
					$row->class2 = 'inputbox';
					$row->posx = 10;
					$row->posy = 20;
					$row->width = 20;
					$row->height = 15;
					break;
				case 'Select List':
					$row->type = $newtype;
					$row->class2 = 'inputbox';
					$row->posx = 50;
					$row->posy = 30;
					$row->data1 = 1;
					$row->data2 = "1;Select Color;''\r\n" .
						"0;Red;red\r\n" .
						"0;Green;green\r\n" .
						"0;Blue;blue\r\n";
					break;
				case 'Query List':
					$row->type = $newtype;
					$row->class2 = 'moduletable';
					$row->posx = 5;
					$row->posxmode = 1;
					$row->posy = 100;
					$row->width = 90;
					$row->widthmode = 1;
					$row->height = 15;
					$row->flag1 = 1; // show header
					$row->flag2 = 1; // selection checkboxes
					$row->data1 = "0\n" .    // border
						"0\n" .    // cellspacing
						"0\n" .    // cellpadding
						"\n" .     // <tr(header)> class
						"\n" .     // <tr(row1)> class
						"\n" .     // <tr(row2)> class
						"\n";     // <tr(footer)> class
					break;
				case 'Captcha':
					$row->type = 'Captcha';
					$row->posx = 10;
					$row->posy = 20;
					break;
				default: // assume Static Text/HTML
					$row->type = 'Static Text/HTML';
					$row->logging = 0;
					$row->posx = 10;
					$row->posy = 20;
					$row->data1 = 'The text to display';
					break;
			} // switch
		} // if
		$lists = array();

		$database->setQuery(
			"select id, concat(package,'::',name) as text " .
			"from #__facileforms_scripts " .
			"where published=1 and type='Element Init' " .
			"order by text, id desc"
		);

		try {
			$lists['scripts1'] = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		$database->setQuery(
			"select id, concat(package,'::',name) as text " .
			"from #__facileforms_scripts " .
			"where published=1 and type='Element Action' " .
			"order by text, id desc"
		);

		try {
			$lists['scripts2'] = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		$database->setQuery(
			"select id, concat(package,'::',name) as text " .
			"from #__facileforms_scripts " .
			"where published=1 and type='Element Validation' " .
			"order by text, id desc"
		);

		try {
			$lists['scripts3'] = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		$order = HTMLHelper::_(
			'list.genericordering',
			"select ordering as value, title as text " .
			"from #__facileforms_elements " .
			"where form=$form and page=$page " .
			"order by ordering"
		);
		$lists['ordering'] =
			HTMLHelper::_(
				'select.genericlist',
				$order,
				'ordering',
				'class="inputbox" size="1" style="z-index:1" ',
				'value',
				'text',
				intval($row->ordering)
			);

		HTML_facileFormsElement::edit($option, $tabpane, $pkg, $row, $lists);
	} // edit

	static function newElement($option, $pkg, $form, $page)
	{
		HTML_facileFormsElement::newitem($option, $pkg, $form, $page);
	} // newElement

	static function save($option, $pkg, $form, $page)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsElements($database);

		// bind it to the table
		if (!$row->bind($_POST)) {
			echo "<script> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if

		if ($row->script1flag1 == null)
			$row->script1flag1 = 0;
		if ($row->script1flag2 == null)
			$row->script1flag2 = 0;
		if ($row->script2flag1 == null)
			$row->script2flag1 = 0;
		if ($row->script2flag2 == null)
			$row->script2flag2 = 0;
		if ($row->script2flag3 == null)
			$row->script2flag3 = 0;
		if ($row->script2flag4 == null)
			$row->script2flag4 = 0;
		if ($row->script2flag5 == null)
			$row->script2flag5 = 0;

		// store it in the db
		if (!$row->store()) {
			echo "<script> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if
		$row->reorder("form=$form and page=$page");

		// CONTENTBUILDER
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		if (file_exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php')) {
			$formId = $form;
			require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
			$cbForm = contentbuilder::getForm('com_breezingforms', $formId);
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$db->setQuery("Select id From #__contentbuilder_forms Where `type` = 'com_breezingforms' And `reference_id` = " . intval($formId));
			$cbForms = $db->loadColumn();
			if (is_object($cbForm) && count($cbForms)) {
				require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'tables' . DS . 'elements.php');
				foreach ($cbForms as $dataId) {
					contentbuilder::synchElements($dataId, $cbForm);
					$elements_table = new TableElements($db);
					$elements_table->reorder('form_id=' . $dataId);
				}
			}
		}
		// CONTENTBUILDER END

		Factory::getApplication()->redirect(
			"index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg",
			BFText::_('COM_BREEZINGFORMS_ELEMENTS_SAVED')
		);
	} // save

	static function cancel($option, $pkg, $form, $page)
	{
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // cancel

	static function del($option, $pkg, $form, $page, $ids)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);

		$quoted_ids = array();

		foreach ($ids as $the_id) {
			$quoted_ids[] = Factory::getContainer()->get(DatabaseInterface::class)->Quote($the_id);
		}

		$ids = implode(',', $quoted_ids);

		$database->setQuery("delete from #__facileforms_elements where form=$form and page=$page and id in ($ids)");
		if (!$database->execute()) {
			echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
		} // if
		//$database->setQuery("delete from #__facileforms_forms where id in ($forms)");
		//if (!$database->execute()) {
		//	echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		//} // if
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // del

	static function getDestination($option, $pkg, $form, $page, $ids, $action)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$fff = array();

		$database->setQuery(
			"select * from #__facileforms_forms " .
			"where id = $form order by ordering"
		);
		$rows = $database->loadObjectList();
		$package = '';
		if (count($rows))
			foreach ($rows as $row) {
				$package = $row->package;
				for ($p = 1; $p <= $row->pages; $p++)
					$fff[] = HTMLHelper::_('select.option', $row->id . ',' . $p, $row->package . '::' . $row->name . ' / ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGE') . ' ' . $p);
			} // for

		$database->setQuery(
			"select * from #__facileforms_forms " .
			"where package = " . $database->Quote($package) . " and id!=$form " .
			"order by ordering"
		);
		$rows = $database->loadObjectList();
		if (count($rows))
			foreach ($rows as $row)
				for ($p = 1; $p <= $row->pages; $p++)
					$fff[] = HTMLHelper::_('select.option', $row->id . ',' . $p, $row->package . '::' . $row->name . ' / ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGE') . ' ' . $p);

		$database->setQuery(
			"select * from #__facileforms_forms " .
			"where package!= " . $database->Quote($package) .
			"order by package, ordering"
		);
		$rows = $database->loadObjectList();
		if (count($rows))
			foreach ($rows as $row)
				for ($p = 1; $p <= $row->pages; $p++)
					$fff[] = HTMLHelper::_('select.option', $row->id . ',' . $p, $row->package . '::' . $row->name . ' / ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGE') . ' ' . $p);

		$sellist =
			HTMLHelper::_(
				'select.genericlist',
				$fff,
				'destination',
				'class="inputbox" size="15"',
				'value',
				'text',
				$form . ',' . $page
			);
		HTML_facileFormsElement::getDestination($option, $pkg, $form, $page, $ids, $sellist, $action);
	} // getDestination

	static function copy($option, $pkg, $form, $page, $ids)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$destination = explode(',', BFRequest::getVar('destination', ''));
		list($newform, $newpage) = $destination;
		if (!$newform && !$newpage)
			Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg&mosmsg=" . BFText::_('COM_BREEZINGFORMS_ELEMENTS_ANERROR'));
		$total = count($ids);
		$row = new facileFormsElements($database);
		if (count($ids))
			foreach ($ids as $id) {
				$row->load(intval($id));
				$row->id = NULL;
				$row->form = $newform;
				$row->page = $newpage;
				$row->ordering = 999999;
				$row->store();
				$row->reorder('form=' . $newform . ' and page = ' . $newpage);
			} // foreach
		$msg = $total . ' ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_COPIED') . $newform . ', ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGE2') . $newpage;
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg&mosmsg=$msg");
	} // copy

	static function move($option, $pkg, $form, $page, $ids)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$destination = explode(',', BFRequest::getVar('destination', ''));
		list($newform, $newpage) = $destination;
		if (!$newform && !$newpage)
			Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg&mosmsg=" . BFText::_('COM_BREEZINGFORMS_ELEMENTS_ANERROR'));
		if ($newform != $form || $newpage != $page) {
			$total = count($ids);
			$row = new facileFormsElements($database);
			if (count($ids))
				foreach ($ids as $id) {
					$row->load(intval($id));
					$row->form = $newform;
					$row->page = $newpage;
					$row->ordering = 999999;
					$row->store();
					$row->reorder('form=' . $newform . ' and page = ' . $newpage);
				} // foreach
			$msg = $total . ' ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_MOVED') . $newform . ', ' . BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGE2') . $newpage;
		} else
			$msg = BFText::_('COM_BREEZINGFORMS_ELEMENTS_NOTMOVED');
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg&mosmsg=$msg");
	} // move

	static function sort($option, $pkg, $form, $page)
	{

		$rows = _ff_select("select * from `#__facileforms_forms` where id = " . Factory::getContainer()->get(DatabaseInterface::class)->Quote($form));
		$width = $height = 600;
		if (count($rows)) {
			$f = $rows[0];
			if ($f->widthmode == 0)
				$width = $f->width;
			else
				$width = $f->prevwidth;
			if ($f->heightmode == 0)
				$height = $f->height;
			if ($width < 1)
				$width = 600;
			if ($height < 1)
				$height = 600;
		} // if
		$rows = _ff_select(
			"select id " .
			"from `#__facileforms_elements` " .
			"where form=" . Factory::getContainer()->get(DatabaseInterface::class)->Quote($form) . " and page=" . Factory::getContainer()->get(DatabaseInterface::class)->Quote($page) . " " .
			"order by " .
			"if(`type`='Hidden Input',1,0), " .
			"if(`type`='Hidden Input',0,if(posy<0," . intval($height) . "+if(posymode=0,posy,(posy*" . intval($height) . ")/100),if(posymode=0,posy,(posy*" . intval($height) . ")/100))), " .
			"if(`type`='Hidden Input',0,if(posx<0," . intval($width) . "+if(posxmode=0,posx,(posx*" . intval($width) . ")/100),if(posxmode=0,posx,(posx*" . intval($width) . ")/100))), " .
			"name, title, id"
		);
		$o = 1;
		if (count($rows))
			foreach ($rows as $row) {
				_ff_query("update `#__facileforms_elements` set ordering=$o where id=$row->id");
				$o++;
			} // foreach
		Factory::getApplication()->redirect(
			"index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg",
			BFText::_('COM_BREEZINGFORMS_ELEMENTS_SORTED')
		);
	} // save

	static function movePos($option, $pkg, $form, $page, $ids, $task)
	{
		global $ff_config, $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$ff_config->movepixels = BFRequest::getVar('movepixels', '');
		$ff_config->store();
		$pos = explode(',', BFRequest::getVar('movepositions', ''));
		$cnt = floor(count($pos) / 3);
		$elem = new facileFormsElements($database);
		$p = 0;
		for ($i = 0; $i < $cnt; $i++) {
			$elem->load($pos[$p++]);
			$elem->posx = $pos[$p++];
			$elem->posy = $pos[$p++];
			$elem->store();
		} // for
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // movePos

	static function gridshow($option, $pkg, $form, $page, $ids, $task)
	{
		global $ff_config, $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$ff_config->gridshow = BFRequest::getVar('gridshow', 0);
		$ff_config->store();
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg&checkedIds=" . implode(',', $ids));
	} // gridshow

	static function publish($option, $pkg, $form, $page, $ids, $publish)
	{
		global $database, $my;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$ids = implode(',', $ids);
		$database->setQuery(
			"update #__facileforms_elements set published=$publish where form=$form and page=$page and id in ($ids)"
		);
		if (!$database->execute()) {
			echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // publish

	static function order($option, $pkg, $form, $page, $ids, $inc)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsElements($database);
		$row->load($ids[0]);
		$row->move($inc, "form=$form and page=$page");
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // order

	static function orderWithoutRedirect($option, $pkg, $form, $page, $positions)
	{
		if (trim($positions) != '') {

			$database = Factory::getContainer()->get(DatabaseInterface::class);

			$expl = explode(',', $positions);
			$explSize = count($expl);

			for ($i = 0; $i < $explSize; $i++) {

				$idAndPos = explode(':', $expl[$i]);

				if (count($idAndPos) == 2 && is_numeric($idAndPos[0]) && is_numeric($idAndPos[1])) {

					$database->setQuery("Update #__facileforms_elements Set ordering = '" . intval($idAndPos[1]) . "' Where id = '" . intval($idAndPos[0]) . "' And form=$form and page=$page");
					$database->execute();

					//$jtable = new BFTableElements($database);
					//$jtable->reorder("form=$form and page=$page");
				}
			}
		}

	} // orderWithoutRedirect

	static function listitems($option, $pkg, $form, $page, $prevmode)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$database->setQuery(
			"select * from #__facileforms_elements where form=$form and page=$page order by ordering"
		);

		try {
			$rows = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		$formrow = new facileFormsForms($database);
		$formrow->load($form);
		$checkedIds = explode(',', BFRequest::getVar('checkedIds', ''));
		HTML_facileFormsElement::listitems($option, $pkg, $formrow, $page, $rows, $prevmode, $checkedIds);
	} // listitems

	static function addPageBehind($option, $pkg, $form, $page)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsForms($database);
		$row->load($form);
		if ($page < $row->pages) {
			$database->setQuery(
				"update #__facileforms_elements set page=page+1 where form=$form and page>$page"
			);
			if (!$database->execute()) {
				echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
				exit();
			} // if
		} // if
		$row->pages++;
		$row->store();
		$page++;
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // addPageBehind

	static function addPageBefore($option, $pkg, $form, $page)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsForms($database);
		$row->load($form);
		$database->setQuery(
			"update #__facileforms_elements set page=page+1 where form=$form and page>=$page"
		);
		if (!$database->execute()) {
			echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if
		$row->pages++;
		$row->store();
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // addPageBefore

	static function delPage($option, $pkg, $form, $page)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsForms($database);
		$row->load($form);
		$database->setQuery(
			"delete from #__facileforms_elements where form=$form and page=$page"
		);
		if (!$database->execute()) {
			echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if
		$database->setQuery(
			"update #__facileforms_elements set page=page-1 where form=$form and page>$page"
		);
		if (!$database->execute()) {
			echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if
		$row->pages--;
		$row->store();
		if ($page > $row->pages)
			$page--;
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // delPage

	static function getPageDestination($option, $pkg, $form, $page)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsForms($database);
		$row->load($form);
		$lst = array();
		for ($p = 1; $p <= $row->pages; $p++)
			$lst[] = HTMLHelper::_('select.option', $p, BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGE') . ' ' . $p);
		$sellist =
			HTMLHelper::_(
				'select.genericlist',
				$lst,
				'destination',
				'class="inputbox" size="15"',
				'value',
				'text',
				$page
			);
		HTML_facileFormsElement::getPagedest($option, $pkg, $form, $page, $sellist);
	} // getPageDestination

	static function movePage($option, $pkg, $form, $page)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$newpage = BFRequest::getInt('destination', '');
		if ($newpage != $page) {
			$database->setQuery(
				"update #__facileforms_elements set page=0 where form=$form and page=$page"
			);
			if (!$database->execute()) {
				echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
				exit();
			} // if
			if ($newpage > $page) {
				$database->setQuery(
					"update #__facileforms_elements set page=page-1 where form=$form and page>$page and page<=$newpage"
				);
			} else {
				$database->setQuery(
					"update #__facileforms_elements set page=page+1 where form=$form and page>=$newpage and page<$page"
				);
			} // if
			if (!$database->execute()) {
				echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
				exit();
			} // if
			$database->setQuery(
				"update #__facileforms_elements set page=$newpage where form=$form and page=0"
			);
			if (!$database->execute()) {
				echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
				exit();
			} // if
			$page = $newpage;
		} // if
		Factory::getApplication()->redirect("index.php?option=$option&act=editpage&form=$form&page=$page&pkg=$pkg");
	} // movePage

} // class facileFormsElement
?>