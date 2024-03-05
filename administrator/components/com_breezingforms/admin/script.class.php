<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

require_once($ff_admpath.'/admin/script.html.php');

class facileFormsScript
{
	static function edit($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
                ArrayHelper::toInteger($ids);
		$typelist = array();
		$typelist[] = array('Untyped',BFText::_('COM_BREEZINGFORMS_SCRIPTS_UNTYPED'));
		$typelist[] = array('Element Init',BFText::_('COM_BREEZINGFORMS_SCRIPTS_ELEMENTINIT'));
		$typelist[] = array('Element Action',BFText::_('COM_BREEZINGFORMS_SCRIPTS_ELEMENTACTION'));
		$typelist[] = array('Element Validation',BFText::_('COM_BREEZINGFORMS_SCRIPTS_ELEMENTVALID'));
		$typelist[] = array('Form Init',BFText::_('COM_BREEZINGFORMS_SCRIPTS_FORMINIT'));
		$typelist[] = array('Form Submitted',BFText::_('COM_BREEZINGFORMS_SCRIPTS_FORMSUBMIT'));
		$row = new facileFormsScripts($database);
		if (count($ids)){
			$row->load($ids[0]);
		} else {
			$row->type = $typelist[0];
			$row->package = $pkg;
			$row->published = 1;
		} // if
		HTML_facileFormsScript::edit($option, $pkg, $row, $typelist);
	} // edit

	static function save($option, $pkg)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsScripts($database);
		// bind it to the table
		if (!$row->bind($_POST)) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		} // if
		// store it in the db
		if (!$row->store()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		} // if
        Factory::getApplication()->enqueueMessage(BFText::_('COM_BREEZINGFORMS_SCRIPTS_SAVED'));
		Factory::getApplication()->redirect(
			"index.php?option=$option&act=managescripts&pkg=$pkg");
	} // save

	static function cancel($option, $pkg)
	{
		Factory::getApplication()->redirect("index.php?option=$option&act=managescripts&pkg=$pkg");
	} // cancel

	static function copy($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$total = count($ids);
		$row = new facileFormsScripts($database);
		if (count($ids)) foreach ($ids as $id) {
			$row->load(intval($id));
			$row->id       = NULL;
			$row->store();
		} // foreach
		$msg = $total.' '.BFText::_('COM_BREEZINGFORMS_SCRIPTS_SUCCOPIED');
		Factory::getApplication()->redirect("index.php?option=$option&act=managescripts&pkg=$pkg&mosmsg=$msg");
	} // copy

	static function del($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		if (count($ids)) {
			$ids = implode(',', $ids);
			$database->setQuery("delete from #__facileforms_scripts where id in ($ids)");
			if (!$database->execute()) {
				echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			} // if
		} // if
		Factory::getApplication()->redirect("index.php?option=$option&act=managescripts&pkg=$pkg");
	} // del

	static function publish($option, $pkg, $ids, $publish)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
                ArrayHelper::toInteger($ids);
		$ids = implode( ',', $ids );
		$database->setQuery(
			"update #__facileforms_scripts set published=".$database->Quote($publish)." where id in ($ids)"
		);
		if (!$database->execute()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		} // if
		Factory::getApplication()->redirect( "index.php?option=$option&act=managescripts&pkg=$pkg" );
	} // publish

	static function listitems($option, $pkg)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);

		$database->setQuery(
			"select distinct  package as name ".
			"from #__facileforms_scripts ".
			"where package is not null and package!='' ".
			"order by name"
		);

        try {
			$pkgs = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		$pkgok = $pkg=='';
		if (!$pkgok && count($pkgs)) foreach ($pkgs as $p) if ($p->name==$pkg) { $pkgok = true; break; }
		if (!$pkgok) $pkg = '';
		$pkglist = array();
		$pkglist[] = array($pkg=='', '');
		if (count($pkgs)) foreach ($pkgs as $p) $pkglist[] = array($p->name==$pkg, $p->name);

		$database->setQuery(
			"select * from #__facileforms_scripts ".
			"where package =  ".$database->Quote($pkg)." ".
			"order by type, name, id desc"
		);
		
        try {
			$rows = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try


		HTML_facileFormsScript::listitems($option, $rows, $pkglist);
	} // listitems

} // class facileFormsScript
?>