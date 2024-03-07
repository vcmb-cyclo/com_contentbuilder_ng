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

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\HTML\HTMLHelper;

require_once($ff_admpath . '/admin/menu.html.php');

class facileFormsMenu
{
	static function create($option, $pkg)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$lists = array();

		$database->setQuery(
			"select id, name, title " .
			"from #__facileforms_forms " .
			"order by ordering"
		);
		$lists['forms'] = $database->loadObjectList();

		$parents = array();
		$parents[] = HTMLHelper::_('select.option', 0, BFText::_('COM_BREEZINGFORMS_MENUS_TOP'));
		$database->setQuery(
			"select id as value, title as text " .
			"from #__facileforms_compmenus " .
			"where parent=0 " .
			"order by title, id"
		);
		$plist = $database->loadObjectList();
		if (count($plist))
			foreach ($plist as $obj)
				$parents[] = $obj;
		$lists['parents'] =
			HTMLHelper::_(
				'select.genericlist',
				$parents,
				'parent',
				'class="inputbox" size="1"',
				'value',
				'text',
				0
			);

		HTML_facileFormsMenu::create($option, $pkg, $lists);
	} // create

	static function edit($option, $pkg, $ids, $formid, $parent)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		ArrayHelper::toInteger($ids);
		$row = new facileFormsMenus($database);
		if ($formid == '')
			$row->load($ids[0]);
		else {
			if ($formid > 0) {
				$form = new facileFormsForms($database);
				$form->load($formid);
				$row->title = $form->title;
				$row->name = $form->name;
			} // if
			$row->parent = $parent;
			$database->setQuery(
				"select max(ordering)+1 " .
				"from #__facileforms_compmenus " .
				"where parent=$parent"
			);
			$row->ordering = $database->loadResult();
		} // if

		$lists = array();

		$parents = array();
		$parents[] = HTMLHelper::_('select.option', 0, BFText::_('COM_BREEZINGFORMS_MENUS_TOP'));
		$database->setQuery(
			"select id as value, title as text " .
			"from #__facileforms_compmenus " .
			"where parent=0 " .
			"order by title, id"
		);
		$plist = $database->loadObjectList();
		if (count($plist))
			foreach ($plist as $obj)
				$parents[] = $obj;
		$lists['parents'] =
			HTMLHelper::_(
				'select.genericlist',
				$parents,
				'parent',
				'class="inputbox" size="1"',
				'value',
				'text',
				intval($row->parent)
			);

		$order =
			HTMLHelper::_(
				'list.genericordering',
				"select ordering as value, title as text " .
				"from #__facileforms_compmenus " .
				"where parent=" . $row->parent . " and package= " . $database->Quote($pkg) . " " .
				"order by ordering"
			);
		$lists['ordering'] =
			HTMLHelper::_(
				'select.genericlist',
				$order,
				'ordering',
				'class="inputbox" size="1"',
				'value',
				'text',
				intval($row->ordering)
			);

		$lists['imgs'] = array(
			'js/ThemeOffice/add_section.png',
			'js/ThemeOffice/backup.png',
			'js/ThemeOffice/categories.png',
			'js/ThemeOffice/checkin.png',
			'js/ThemeOffice/component.png',
			'js/ThemeOffice/config.png',
			'js/ThemeOffice/content.png',
			'js/ThemeOffice/controlpanel.png',
			'js/ThemeOffice/credits.png',
			'js/ThemeOffice/db.png',
			'js/ThemeOffice/document.png',
			'js/ThemeOffice/edit.png',
			'js/ThemeOffice/globe1.png',
			'js/ThemeOffice/globe2.png',
			'js/ThemeOffice/globe3.png',
			'js/ThemeOffice/globe4.png',
			'js/ThemeOffice/help.png',
			'js/ThemeOffice/home.png',
			'js/ThemeOffice/install.png',
			'js/ThemeOffice/language.png',
			'js/ThemeOffice/license.png',
			'js/ThemeOffice/mail.png',
			'js/ThemeOffice/mainmenu.png',
			'js/ThemeOffice/mass_email.png',
			'js/ThemeOffice/media.png',
			'js/ThemeOffice/menus.png',
			'js/ThemeOffice/messaging.png',
			'js/ThemeOffice/messaging_config.png',
			'js/ThemeOffice/messaging_inbox.png',
			'js/ThemeOffice/module.png',
			'js/ThemeOffice/preview.png',
			'js/ThemeOffice/query.png',
			'js/ThemeOffice/restore.png',
			'js/ThemeOffice/search_text.png',
			'js/ThemeOffice/sections.png',
			'js/ThemeOffice/statistics.png',
			'js/ThemeOffice/sysinfo.png',
			'js/ThemeOffice/template.png',
			'js/ThemeOffice/tooltip.png',
			'js/ThemeOffice/trash.png',
			'js/ThemeOffice/tux.png',
			'js/ThemeOffice/user.png',
			'js/ThemeOffice/users.png',
			'js/ThemeOffice/users_add.png',
			'js/ThemeOffice/warning.png'
		);
		HTML_facileFormsMenu::edit($option, $pkg, $row, $lists);
	} // edit

	static function save($option, $pkg)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsMenus($database);

		unset($_POST['ordering']);
		unset($row->ordering);

		// bind it to the table
		if (!$row->bind($_POST)) {
			echo "<script> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if

		// store it in the db
		if (!$row->store()) {
			echo "<script> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if
		$row->reorder('parent=' . $row->parent);
		$result = updateComponentMenus();
		$type = 'message';
		$msg = BFText::_('COM_BREEZINGFORMS_MENUS_SAVED');
		if ($result != '') {
			$msg = $result;
			$type = 'error';
		}
		Factory::getApplication()->redirect("index.php?option=$option&act=managemenus&pkg=$pkg", $msg, $type);
	} // save

	static function cancel($option, $pkg)
	{
		Factory::getApplication()->redirect("index.php?option=$option&act=managemenus&pkg=$pkg");
	} // cancel

	static function copy($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$total = count($ids);
		$row = new facileFormsMenus($database);
		$child = new facileFormsMenus($database);
		if (count($ids))
			foreach ($ids as $id) {
				$row->load(intval($id));
				$row->id = NULL;
				$row->ordering = 999999;
				$row->store();
				$row->reorder('parent=0');
				$database->setQuery("select id from #__facileforms_compmenus where parent=$id");
				$cids = $database->loadObjectList();
				for ($i = 0; $i < count($cids); $i++) {
					$cid = $cids[$i];
					$child->load(intval($cid->id));
					$child->id = NULL;
					$child->parent = $row->id;
					$child->store();
				} // for
			} // foreach
		$msg = $total . ' ' . BFText::_('COM_BREEZINGFORMS_MENUS_SUCOPIED');
		$result = updateComponentMenus(true);
		if ($result != '') {
			$msg = $result;
		}
		Factory::getApplication()->redirect("index.php?option=$option&act=managemenus&pkg=$pkg&mosmsg=$msg");
	} // copy

	static function del($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		ArrayHelper::toInteger($ids);
		if (count($ids)) {
			$ids = implode(',', $ids);
			$database->setQuery("delete from #__facileforms_compmenus where parent in ($ids)");
			if (!$database->execute()) {
				echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
			} // if
			$database->setQuery("delete from #__facileforms_compmenus where id in ($ids)");
			if (!$database->execute()) {
				echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
			} // if
		} // if
		updateComponentMenus();
		Factory::getApplication()->redirect("index.php?option=$option&act=managemenus&pkg=$pkg");
	} // del

	static function order($option, $pkg, $ids, $inc)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		ArrayHelper::toInteger($ids);
		$row = new facileFormsMenus($database);
		$row->load($ids[0]);
		$row->move($inc, "package=" . $database->Quote($pkg) . " and parent=" . $row->parent);
		updateComponentMenus();
		Factory::getApplication()->redirect("index.php?option=$option&act=managemenus&pkg=$pkg");
	} // order

	static function publish($option, $pkg, $ids, $publish)
	{
		ArrayHelper::toInteger($ids);
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$ids = implode(',', $ids);
		$database->setQuery(
			"update #__facileforms_compmenus set published='$publish' where id in ($ids)"
		);
		if (!$database->execute()) {
			echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
			exit();
		} // if
		updateComponentMenus();
		Factory::getApplication()->redirect("index.php?option=$option&act=managemenus&pkg=$pkg");
	} // publish

	static function listitems($option, $pkg)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);

		$database->setQuery(
			"select distinct package as name " .
			"from #__facileforms_compmenus " .
			"where package is not null and package!='' " .
			"order by name"
		);

		try {
			$pkgs = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		$pkgok = $pkg == '';
		if (!$pkgok && count($pkgs))
			foreach ($pkgs as $p)
				if ($p->name == $pkg) {
					$pkgok = true;
					break;
				}
		if (!$pkgok)
			$pkg = '';
		$pkglist = array();
		$pkglist[] = array($pkg == '', '');
		if (count($pkgs))
			foreach ($pkgs as $p)
				$pkglist[] = array($p->name == $pkg, $p->name);

		$database->setQuery(
			"select " .
			"m.id as id, " .
			"if(m.parent,concat(p.title,'/',m.title),m.title) as title, " .
			"m.img as img, " .
			"m.name as name, " .
			"m.frame as frame, " .
			"m.border as border, " .
			"m.params as params, " .
			"m.published as published " .
			"from #__facileforms_compmenus as m " .
			"left join #__facileforms_compmenus as p on m.parent=p.id " .
			"where m.package =  " . $database->Quote($pkg) . " " .
			"order by " .
			"if(m.parent,p.ordering,m.ordering), " .
			"if(m.parent,m.ordering,-1)"
		);
		$rows = $database->loadObjectList();

		try {
			$rows = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		HTML_facileFormsMenu::listitems($option, $rows, $pkglist);
	} // listitems

} // class facileFormsMenu
?>