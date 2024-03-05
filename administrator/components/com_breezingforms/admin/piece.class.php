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

require_once($ff_admpath.'/admin/piece.html.php');

class facileFormsPiece
{
	static function edit($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
    	ArrayHelper::toInteger($ids);
		$typelist = array();
		$typelist[] = array('Untyped',BFText::_('COM_BREEZINGFORMS_PIECES_UNTYPED'));
		$typelist[] = array('Before Form',BFText::_('COM_BREEZINGFORMS_PIECES_BEFOREFORM'));
		$typelist[] = array('After Form',BFText::_('COM_BREEZINGFORMS_PIECES_AFTERFORM'));
		$typelist[] = array('Begin Submit',BFText::_('COM_BREEZINGFORMS_PIECES_BEGINSUBMIT'));
		$typelist[] = array('End Submit',BFText::_('COM_BREEZINGFORMS_PIECES_ENDSUBMIT'));
		$row = new facileFormsPieces($database);
		if (count($ids)){
			$row->load($ids[0]);
		} else {
			$row->type = $typelist[0];
			$row->package = $pkg;
			$row->published = 1;
		} // if
		HTML_facileFormsPiece::edit($option, $pkg, $row, $typelist);
	} // edit

	static function save($option, $pkg)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row = new facileFormsPieces($database);
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
		Factory::getApplication()->redirect(
			"index.php?option=$option&act=managepieces&pkg=$pkg");
	} // save

	static function cancel($option, $pkg)
	{
		Factory::getApplication()->redirect("index.php?option=$option&act=managepieces&pkg=$pkg");
	} // cancel

	static function copy($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
                ArrayHelper::toInteger($ids);
		$total = count($ids);
		$row = new facileFormsPieces($database);
		if (count($ids)) foreach ($ids as $id) {
			$row->load(intval($id));
			$row->id       = NULL;
			$row->store();
		} // foreach
		$msg = $total.' '.BFText::_('COM_BREEZINGFORMS_PIECES_SUCCOPIED');
		Factory::getApplication()->redirect("index.php?option=$option&act=managepieces&pkg=$pkg&mosmsg=$msg");
	} // copy

	static function del($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
                ArrayHelper::toInteger($ids);
		if (count($ids)) {
			$ids = implode(',', $ids);
			$database->setQuery("delete from #__facileforms_pieces where id in ($ids)");
			if (!$database->execute()) {
				echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			} // if
		} // if
		Factory::getApplication()->redirect("index.php?option=$option&act=managepieces&pkg=$pkg");
	} // del

	static function publish($option, $pkg, $ids, $publish)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
                ArrayHelper::toInteger($ids);
		$ids = implode( ',', $ids );
		$database->setQuery(
			"update #__facileforms_pieces set published='$publish' where id in ($ids)"
		);
		if (!$database->execute()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		} // if
		Factory::getApplication()->redirect( "index.php?option=$option&act=managepieces&pkg=$pkg" );
	} // publish

	static function listitems($option, $pkg)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);

		$database->setQuery(
			"select distinct  package as name ".
			"from #__facileforms_pieces ".
			"where package is not null and package!='' ".
			"order by name"
		);
		
		
		try{
			$pkgs = $database->loadObjectList();
		} catch(Exception $e){
			echo $e->getCode() . ' : ' .$e->getMessage();
			return false;
		}


		$pkgok = $pkg=='';
		if (!$pkgok && count($pkgs)) foreach ($pkgs as $p) if ($p->name==$pkg) { $pkgok = true; break; }
		if (!$pkgok) $pkg = '';
		$pkglist = array();
		$pkglist[] = array($pkg=='', '');
		if (count($pkgs)) foreach ($pkgs as $p) $pkglist[] = array($p->name==$pkg, $p->name);

		$database->setQuery(
			"select * from #__facileforms_pieces ".
			"where package =  ".$database->Quote($pkg)." ".
			"order by type, name, id desc"
		);
		$rows = $database->loadObjectList();

		try{
			$rows = $database->loadObjectList();
		} catch(Exception $e){
			echo $e->getCode() . ' : ' .$e->getMessage();
			return false;
		}

		HTML_facileFormsPiece::listitems($option, $rows, $pkglist);
	} // listitems

} // class facileFormsPiece
?>