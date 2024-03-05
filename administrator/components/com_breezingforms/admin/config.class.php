<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

require_once($ff_admpath.'/admin/config.html.php');
require_once($ff_admpath.'/admin/import.class.php');

class facileFormsConfig extends facileFormsConf {

	function edit($option, $caller, $pkg = '')
	{
		HTML_facileFormsConf::edit($option, $caller, $pkg);
	} // edit

	function cancel($option, $caller, $pkg)
	{
		if ($caller != 'index.php' && $pkg != '') Factory::getApplication()->redirect($caller."&pkg=$pkg");
		Factory::getApplication()->redirect($caller);
	} // cancel

	function save($option, $caller, $pkg)
	{
        if(isset($_REQUEST['updatekey'])){

            try {

                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery("Update #__update_sites Set last_check_timestamp = 0, extra_query = " . $db->quote('key=' . $_REQUEST['updatekey']) . " Where `name` = 'BreezingForms Pro' And `type` = 'extension'");
                $db->execute();

                $db->setQuery("Select update_site_id From #__update_sites Where `name` = 'BreezingForms Pro' And `type` = 'extension'");
                $update_site_id = $db->loadResult();

                $db->setQuery("Delete From #__updates Where `update_site_id` = " . $db->quote($update_site_id));
                $db->execute();


            }catch(Exception $e){

            }catch (Error $e){

            }

            unset($_REQUEST['updatekey']);
        }

		$this->bindRequest($_REQUEST);
		$this->store();
		if ($pkg != '') {
                    Factory::getApplication()->enqueueMessage(BFText::_('COM_BREEZINGFORMS_CONFIG_SAVED'));
                    Factory::getApplication()->redirect($caller."&pkg=$pkg", BFText::_('COM_BREEZINGFORMS_CONFIG_SAVED'));
                }
		Factory::getApplication()->enqueueMessage(BFText::_('COM_BREEZINGFORMS_CONFIG_SAVED'));
                Factory::getApplication()->redirect('index.php?option=com_breezingforms&act=configuration');
	} // save

	function addToAll(&$all, $row)
	{
		$cnt = count($all);
		for ($a = 0; $a < $cnt; $a++) {
			if ($row->package == $all[$a]->package) return;
			if ($row->package < $all[$a]->package) {
				for ($b = $cnt; $b > $a; $b--) $all[$b] = $all[$b-1];
				$all[$a] = $row;
				return;
			} // if
		} //for
		$all[] = $row;
	} // addToAll

	function getAllPackages(&$all)
	{
		$all = array();
		$rows = _ff_select(
			"select distinct package as package ".
			"from #__facileforms_compmenus where parent=0 ".
			"order by package"
		);
		for ($r = 0; $r < count($rows); $r++) facileFormsConfig::addToAll($all,$rows[$r]);

		$rows = _ff_select(
			"select distinct  package as package ".
			"from #__facileforms_forms ".
			"order by package"
		);
		for ($r = 0; $r < count($rows); $r++) facileFormsConfig::addToAll($all,$rows[$r]);

		$rows = _ff_select(
			"select distinct  package as package ".
			"from #__facileforms_scripts ".
			"order by package"
		);
		for ($r = 0; $r < count($rows); $r++) facileFormsConfig::addToAll($all,$rows[$r]);

		$rows = _ff_select(
			"select distinct  package as package ".
			"from #__facileforms_pieces ".
			"order by package"
		);
		for ($r = 0; $r < count($rows); $r++) facileFormsConfig::addToAll($all,$rows[$r]);
		return $all;
	} // getAllPackages

	function makePackage($option, $caller, $pkg)
	{
		$lists = array();

		facileFormsConfig::getAllPackages($lists['pkgnames']);

		$lists['packages'] = _ff_select(
			"select *  from #__facileforms_packages order by  id"
		);
		$lists['forms'] = _ff_select(
			"select id, concat(package,'::',name) as title from #__facileforms_forms ".
			"order by title, id"
		);
		$lists['scripts'] = _ff_select(
			"select id, concat(package,'::',name) as title from #__facileforms_scripts ".
			"order by title, id"
		);
		$lists['pieces'] = _ff_select(
			"select id, concat(package,'::',name) as title from #__facileforms_pieces ".
			"order by title, id"
		);
		$lists['compmenus'] = _ff_select(
			"select id, concat(package,'::',title) as title from #__facileforms_compmenus ".
			"where parent=0 ".
			"order by title, id"
		);
		HTML_facileFormsConf::makePackage($option, $caller, $pkg, $lists);
	} // makePackage

	function exportScript($name, $table, $cond, $id, $code, $ind, &$xml)
	{
		if ($cond > 0) {
			$nl = "\n";
			$xml .= indent($ind).'<'.$name.'cond>'.$cond.'</'.$name.'cond>'.nl();
			if ($cond == 1) {
				if($id){
					$xml .= indent($ind).'<'.$name.'id>'.$id.'</'.$name.'id>'.nl();
					$funcname = _ff_selectValue('select name from `'.$table.'` where id='.Factory::getContainer()->get(DatabaseInterface::class)->Quote($id));
					if ($funcname && $funcname != '')
						$xml .= indent($ind).'<'.$name.'name>'.$funcname.'</'.$name.'name>'.nl();
				}
			} else {
				$code = expstring($code);
				if ($code != '')
					$xml .= indent($ind).'<'.$name.'code>'.$code.'</'.$name.'code>'.nl();
			} // if
		} // if
	} // exportScript

	function mkPackage($option, $caller, $pkg)
	{
		global $ff_admpath, $ff_version, $mosConfig_fileperms;

		$id          = $pkg;
		$name        = BFRequest::getVar( 'pkg_name', '');
		$title       = BFRequest::getVar( 'pkg_title', '');
		$version     = BFRequest::getVar( 'pkg_version', '');
		$created     = date('Y-m-d H:i:s');
		$author      = BFRequest::getVar( 'pkg_author', '');
		$email       = BFRequest::getVar( 'pkg_email', '');
		$url         = BFRequest::getVar( 'pkg_url', '');
		$description = BFRequest::getVar( 'pkg_description', '');
		$copyright   = BFRequest::getVar( 'pkg_copyright', '');

		savePackage($id, $name, $title, $version, $created, $author,
					$email, $url, $description, $copyright);

		$xmlname = $ff_admpath.'/packages/'.$name.'.xml';
		$existed = file_exists($xmlname);
		if ($existed)
			if (!is_writable($xmlname))
				die('XML file is not writable!');

		$file= fopen($xmlname, "w");

		$xml  = '<?xml version="1.0" encoding="utf-8" ?>'.nl().
				'<FacileFormsPackage';
		if ($id != '') $xml .= ' id="'.$id.'"';
                if ($id == '') $xml .= ' id="'.$name.'"';
		$xml .=
				' type="autoincrement" version="'.$ff_version.'">'.nl().
				indent(1).'<name>'.expstring($name).'</name>'.nl().
				indent(1).'<title>'.expstring($title).'</title>'.nl().
				indent(1).'<version>'.expstring($version).'</version>'.nl().
				indent(1).'<creationDate>'.$created.'</creationDate>'.nl().
				indent(1).'<author>'.expstring($author).'</author>'.nl().
				indent(1).'<authorEmail>'.expstring($email).'</authorEmail>'.nl().
				indent(1).'<authorUrl>'.expstring($url).'</authorUrl>'.nl().
				indent(1).'<description>'.expstring($description).'</description>'.nl().
				indent(1).'<copyright>'.expstring($copyright).'</copyright>'.nl();

		if ($id == '')
			$ids = BFRequest::getVar( 'scriptsel', array());
		else {
			$ids = array();
			$rows = _ff_select(
				"select id from #__facileforms_scripts ".
				"where package =  ".Factory::getContainer()->get(DatabaseInterface::class)->Quote($id)." ".
				"order by id"
			);
			if (count($rows)) foreach ($rows as $row) $ids[] = $row->id;
		} // if
		if (count($ids) > 0) {
                        
                        $quoted_ids = array();
                        
                        foreach($ids As $the_id){
                           $quoted_ids[] = Factory::getContainer()->get(DatabaseInterface::class)->Quote($the_id);
                        }
                    
                        $ids = implode(',', $quoted_ids);
                        
			$scripts = _ff_select(
				"select * from #__facileforms_scripts where id in ($ids) order by  package, name, id"
			);
			for ($s = 0; $s < count($scripts); $s++) {
				$script = $scripts[$s];
				$xml .= indent(1).'<script id="'.$script->id.'">'.nl();
				if ($script->published != 1)
					$xml .= indent(2).'<published>'.$script->published.'</published>'.nl();
				if ($script->package != '')
					$xml .= indent(2).'<package>'.expstring($script->package).'</package>'.nl();
				$xml .= indent(2).'<name>'.expstring($script->name).'</name>'.nl().
						indent(2).'<title>'.expstring($script->title).'</title>'.nl();
				if ($script->type != 'Untyped')
				   $xml .= indent(2).'<type>'.expstring($script->type).'</type>'.nl();
				$script->description = trim($script->description);
				if ($script->description != '')
					$xml .= indent(2).'<description>'.expstring($script->description).'</description>'.nl();
				$script->code = trim($script->code);
				if ($script->code != '')
					$xml .= indent(2).'<code>'.expstring($script->code).'</code>'.nl();
				$xml .= indent(1).'</script>'.nl();
			} // for
		} // if

		if ($id == '')
			$ids = BFRequest::getVar( 'piecesel', array());
		else {
			$ids = array();
			$rows = _ff_select(
				"select id from #__facileforms_pieces ".
				"where package =  ".Factory::getContainer()->get(DatabaseInterface::class)->Quote($id)." ".
				"order by id"
			);
			if (count($rows)) foreach ($rows as $row) $ids[] = $row->id;
		} // if
		if (count($ids) > 0) {
                    
                        $quoted_ids = array();
                        
                        foreach($ids As $the_id){
                           $quoted_ids[] = Factory::getContainer()->get(DatabaseInterface::class)->Quote($the_id);
                        }
                    
			$ids = implode(',', $quoted_ids);
			$pieces = _ff_select(
				"select * from #__facileforms_pieces where id in ($ids) order by  package, name, id"
			);
			for ($p = 0; $p < count($pieces); $p++) {
				$piece = $pieces[$p];
				$xml .= indent(1).'<piece id="'.$piece->id.'">'.nl();
				if ($piece->published != 1)
				   $xml .= indent(2).'<published>'.$piece->published.'</published>'.nl();
				if ($piece->package != '')
					$xml .= indent(2).'<package>'.expstring($piece->package).'</package>'.nl();
				$xml .= indent(2).'<name>'.expstring($piece->name).'</name>'.nl().
						indent(2).'<title>'.expstring($piece->title).'</title>'.nl();
				if ($piece->type != 'Untyped')
				   $xml .= indent(2).'<type>'.expstring($piece->type).'</type>'.nl();
				$piece->description = trim($piece->description);
				if ($piece->description != '')
					$xml .= indent(2).'<description>'.expstring($piece->description).'</description>'.nl();
				$piece->code = trim($piece->code);
				if ($piece->code != '')
					$xml .= indent(2).'<code>'.expstring($piece->code).'</code>'.nl();
				$xml .= indent(1).'</piece>'.nl();
			} // for
		} // if

		if ($id == '')
			$ids = BFRequest::getVar( 'formsel', array());
		else {
			$ids = array();
			$rows = _ff_select(
				"select id from #__facileforms_forms ".
				"where package =  ".Factory::getContainer()->get(DatabaseInterface::class)->Quote($id)." ".
				"order by id"
			);
			if (count($rows)) foreach ($rows as $row) $ids[] = $row->id;
		} // if
		if (count($ids) > 0) {
			$quoted_ids = array();
                        
                        foreach($ids As $the_id){
                           $quoted_ids[] = Factory::getContainer()->get(DatabaseInterface::class)->Quote($the_id);
                        }
                    
			$ids = implode(',', $quoted_ids);
			$forms = _ff_select(
				"select * from #__facileforms_forms where id in ($ids) order by  package, ordering, id"
			);
			for ($f = 0; $f < count($forms); $f++) {
				$form = $forms[$f];
				$xml .= indent(1).'<form id="'.$form->id.'">'.nl();
				if ($form->published != 1) $xml .= indent(2).'<published>'.$form->published.'</published>'.nl();
				if ($form->runmode != 0) $xml .= indent(2).'<runmode>'.$form->runmode.'</runmode>'.nl();
				if ($form->package != '')
					$xml .= indent(2).'<package>'.expstring($form->package).'</package>'.nl();
				$xml .=
					indent(2).'<name>'.expstring($form->name).'</name>'.nl().
					indent(2).'<title>'.expstring($form->title).'</title>'.nl();
				if ($form->description != '') $xml .= indent(2).'<description>'.expstring($form->description).'</description>'.nl();
				if ($form->class1 != '') $xml .= indent(2).'<class1>'.expstring($form->class1).'</class1>'.nl();
				if ($form->class2 != '') $xml .= indent(2).'<class2>'.expstring($form->class2).'</class2>'.nl();
				$xml .= indent(2).'<width>'.$form->width.'</width>'.nl();
				if ($form->widthmode != 0) $xml .= indent(2).'<widthmode>'.$form->widthmode.'</widthmode>'.nl();
				$xml .= indent(2).'<height>'.$form->height.'</height>'.nl();
				if ($form->heightmode != 0) $xml .= indent(2).'<heightmode>'.$form->heightmode.'</heightmode>'.nl();
				if ($form->pages    != 1) $xml .= indent(2).'<pages>'.$form->pages.'</pages>'.nl();
				if ($form->emailntf != 1) $xml .= indent(2).'<emailntf>'.$form->emailntf.'</emailntf>'.nl();
				if ($form->emaillog != 1) $xml .= indent(2).'<emaillog>'.$form->emaillog.'</emaillog>'.nl();
				if ($form->emailxml != 0) $xml .= indent(2).'<emailxml>'.$form->emailxml.'</emailxml>'.nl();
                if ($form->double_opt != 0) $xml .= indent(2).'<double_opt>'.$form->double_opt.'</double_opt>'.nl();
                if ($form->opt_mail != '') $xml .= indent(2).'<opt_mail>'.$form->opt_mail.'</opt_mail>'.nl();
				if ($form->emailntf == 2) {
					$form->emailadr = expstring($form->emailadr);
					if ($form->emailadr != '') $xml .= indent(2).'<emailadr>'.$form->emailadr.'</emailadr>'.nl();
				} // if
				if($form->template_code != '')$xml.=indent(2).'<template_code>'.bf_b64enc($form->template_code).'</template_code>';
				if($form->template_code_processed != '')$xml.=indent(2).'<template_code_processed>'.bf_b64enc($form->template_code_processed).'</template_code_processed>';
				if($form->template_areas != '')$xml.=indent(2).'<template_areas>'.bf_b64enc($form->template_areas).'</template_areas>';
				if ($form->dblog != 1) $xml .=  indent(2).'<dblog>'.$form->dblog.'</dblog>'.nl();
				$form->description = trim($form->description);
				if ($form->prevmode != 2) $xml .= indent(2).'<prevmode>'.$form->prevmode.'</prevmode>'.nl();
				if ($form->prevmode != 0 && $form->widthmode != 0 && $form->prevwidth != '')
					$xml .= indent(2).'<prevwidth>'.$form->prevwidth.'</prevwidth>'.nl();
				$this->exportScript(
					'script1',
					'#__facileforms_scripts',
					$form->script1cond,
					$form->script1id,
					$form->script1code,
					2, $xml
				);
				$this->exportScript(
					'script2',
					'#__facileforms_scripts',
					$form->script2cond,
					$form->script2id,
					$form->script2code,
					2, $xml
				);
				$this->exportScript(
					'piece1',
					'#__facileforms_pieces',
					$form->piece1cond,
					$form->piece1id,
					$form->piece1code,
					2, $xml
				);
				$this->exportScript(
					'piece2',
					'#__facileforms_pieces',
					$form->piece2cond,
					$form->piece2id,
					$form->piece2code,
					2, $xml
				);
				$this->exportScript(
					'piece3',
					'#__facileforms_pieces',
					$form->piece3cond,
					$form->piece3id,
					$form->piece3code,
					2, $xml
				);
				$this->exportScript(
					'piece4',
					'#__facileforms_pieces',
					$form->piece4cond,
					$form->piece4id,
					$form->piece4code,
					2, $xml
				);

				$elems = _ff_select(
					"select * from #__facileforms_elements where form=$form->id order by page, ordering, id"
				);
				for ($e = 0; $e < count($elems); $e++) {
					$elem = $elems[$e];
					$xml .= indent(2).'<element id="'.$elem->id.'">'.nl();
					if ($elem->page != 1) $xml .= indent(3).'<page>'.$elem->page.'</page>'.nl();
					if ($elem->published != 1) $xml .= indent(3).'<published>'.$elem->published.'</published>'.nl();
					$xml .= indent(3).'<name>'.expstring($elem->name).'</name>'.nl().
							indent(3).'<title>'.expstring($elem->title).'</title>'.nl();
					if ($elem->type != 'Static Text/HTML') $xml .= indent(3).'<type>'.$elem->type.'</type>'.nl();
					if ($elem->class1 != '') $xml .= indent(3).'<class1>'.expstring($elem->class1).'</class1>'.nl();
					if ($elem->class2 != '') $xml .= indent(3).'<class2>'.expstring($elem->class2).'</class2>'.nl();
					if (isInputElement($elem->type)) {
						if ($elem->logging != 1)
						   $xml .= indent(3).'<logging>'.$elem->logging.'</logging>'.nl();
					} // if
					if (isVisibleElement($elem->type)) {
						if ($elem->posx != NULL) $xml .= indent(3).'<posx>'.$elem->posx.'</posx>'.nl();
						if ($elem->posx != NULL && $elem->posxmode!=0) $xml .= indent(3).'<posxmode>'.$elem->posxmode.'</posxmode>'.nl();
						if ($elem->posy != NULL) $xml .= indent(3).'<posy>'.$elem->posy.'</posy>'.nl();
						if ($elem->posy != NULL && $elem->posymode!=0) $xml .= indent(3).'<posymode>'.$elem->posymode.'</posymode>'.nl();
						if ($elem->width != NULL) $xml .= indent(3).'<width>'.$elem->width.'</width>'.nl();
						if ($elem->width != NULL && $elem->widthmode!=0) $xml .= indent(3).'<widthmode>'.$elem->widthmode.'</widthmode>'.nl();
						if ($elem->height != NULL) $xml .= indent(3).'<height>'.$elem->height.'</height>'.nl();
						if ($elem->height != NULL && $elem->heightmode!=0) $xml .= indent(3).'<heightmode>'.$elem->heightmode.'</heightmode>'.nl();
					} // if
					$xml .= indent(3).'<mailback>'.$elem->mailback.'</mailback>'.nl();
					$xml .= indent(3).'<mailbackfile>'.$elem->mailbackfile.'</mailbackfile>'.nl();
					if ($elem->flag1) $xml .= indent(3).'<flag1>'.$elem->flag1.'</flag1>'.nl();
					if ($elem->flag2) $xml .= indent(3).'<flag2>'.$elem->flag2.'</flag2>'.nl();
					$elem->data1 = expstring($elem->data1);
					if ($elem->data1 != '') $xml .= indent(3).'<data1>'.$elem->data1.'</data1>'.nl();
					$elem->data2 = expstring($elem->data2);
					if ($elem->data2 != '') $xml .= indent(3).'<data2>'.$elem->data2.'</data2>'.nl();
					$elem->data3 = expstring($elem->data3);
					if ($elem->data3 != '') $xml .= indent(3).'<data3>'.$elem->data3.'</data3>'.nl();
					$this->exportScript(
						'script1',
						'#__facileforms_scripts',
						$elem->script1cond,
						$elem->script1id,
						$elem->script1code,
						3, $xml
					);
					if ($elem->script1cond > 0) {
						if ($elem->script1flag1) $xml .= indent(3).'<script1flag1>'.$elem->script1flag1.'</script1flag1>'.nl();
						if ($elem->script1flag2) $xml .= indent(3).'<script1flag2>'.$elem->script1flag2.'</script1flag2>'.nl();
					} // if
					$this->exportScript(
						'script2',
						'#__facileforms_scripts',
						$elem->script2cond,
						$elem->script2id,
						$elem->script2code,
						3, $xml
					);
					if ($elem->script2cond > 0) {
						if ($elem->script2flag1) $xml .= indent(3).'<script2flag1>'.$elem->script2flag1.'</script2flag1>'.nl();
						if ($elem->script2flag2) $xml .= indent(3).'<script2flag2>'.$elem->script2flag2.'</script2flag2>'.nl();
						if ($elem->script2flag3) $xml .= indent(3).'<script2flag3>'.$elem->script2flag3.'</script2flag3>'.nl();
						if ($elem->script2flag4) $xml .= indent(3).'<script2flag4>'.$elem->script2flag4.'</script2flag4>'.nl();
						if ($elem->script2flag5) $xml .= indent(3).'<script2flag5>'.$elem->script2flag5.'</script2flag5>'.nl();
					} // if
					$this->exportScript(
						'script3',
						'#__facileforms_scripts',
						$elem->script3cond,
						$elem->script3id,
						$elem->script3code,
						3, $xml
					);
					if ($elem->script3cond > 0) {
						if ($elem->script3msg != '') $xml .= indent(3).'<script3msg>'.expstring($elem->script3msg).'</script3msg>'.nl();
					} // if
					$xml .= indent(2).'</element>'.nl();
				} // for
				$xml .= indent(1).'</form>'.nl();
			} // for
		} // if

		if ($id == '')
			$ids = BFRequest::getVar( 'menusel', array());
		else {
			$ids = array();
			$rows = _ff_select(
				"select id from #__facileforms_compmenus ".
				"where package =  ".Factory::getContainer()->get(DatabaseInterface::class)->Quote($id)." and parent = 0 ".
				"order by id"
			);
			if (count($rows)) foreach ($rows as $row) $ids[] = $row->id;
		} // if
		if (count($ids) > 0) {
			$quoted_ids = array();
                        
                        foreach($ids As $the_id){
                           $quoted_ids[] = Factory::getContainer()->get(DatabaseInterface::class)->Quote($the_id);
                        }
                    
			$ids = implode(',', $quoted_ids);
			$menus = _ff_select(
				"select * from #__facileforms_compmenus where id in ($ids) order by  package, ordering, id"
			);
			for ($m = 0; $m < count($menus); $m++) {
				$menu = $menus[$m];
				$xml .= indent(1).'<compmenu id="'.$menu->id.'">'.nl();
				if ($menu->published != 1)
				   $xml .= indent(2).'<published>'.$menu->published.'</published>'.nl();
				$menu->img = trim($menu->img);
				if ($menu->img != '')
					$xml .= indent(2).'<img>'.expstring($menu->img).'</img>'.nl();
				if ($menu->package != '')
					$xml .= indent(2).'<package>'.expstring($menu->package).'</package>'.nl();
				$xml .= indent(2).'<title>'.expstring($menu->title).'</title>'.nl();
				$menu->name = trim($menu->name);
				if ($menu->name != '')
					$xml .= indent(2).'<name>'.expstring($menu->name).'</name>'.nl();
				if ($menu->page != 1 && $menu->page!='')
				   $xml .= indent(2).'<page>'.$menu->page.'</page>'.nl();
				if ($menu->frame != 0)
				   $xml .= indent(2).'<frame>'.$menu->frame.'</frame>'.nl();
				if ($menu->border != 0)
				   $xml .= indent(2).'<border>'.$menu->border.'</border>'.nl();
				$menu->params = trim($menu->params);
				if ($menu->params != '')
					$xml .= indent(2).'<params>'.expstring($menu->params).'</params>'.nl();

				$submenus = _ff_select(
					"select * from #__facileforms_compmenus where parent=$menu->id order by ordering, id"
				);

				for ($s = 0; $s < count($submenus); $s++) {
					$submenu = $submenus[$s];
					$xml .= indent(2).'<compmenu id="'.$submenu->id.'">'.nl();
					if ($submenu->published != 1)
					   $xml .= indent(3).'<published>'.$submenu->published.'</published>'.nl();
					$submenu->img = trim($submenu->img);
					if ($submenu->img != '')
						$xml .= indent(3).'<img>'.expstring($submenu->img).'</img>'.nl();
					if ($menu->package != '')
						$xml .= indent(3).'<package>'.expstring($submenu->package).'</package>'.nl();
					$xml .= indent(3).'<title>'.expstring($submenu->title).'</title>'.nl();
					$submenu->name = trim($submenu->name);
					if ($submenu->name != '')
						$xml .= indent(3).'<name>'.expstring($submenu->name).'</name>'.nl();
					if ($submenu->page != 1 && $submenu->page!='')
					   $xml .= indent(3).'<page>'.$submenu->page.'</page>'.nl();
					if ($submenu->frame != 0)
					   $xml .= indent(3).'<frame>'.$submenu->frame.'</frame>'.nl();
					if ($submenu->border != 0)
					   $xml .= indent(3).'<border>'.$submenu->border.'</border>'.nl();
					$submenu->params = trim($submenu->params);
					if ($submenu->params != '')
						$xml .= indent(3).'<params>'.expstring($submenu->params).'</params>'.nl();
					$xml .= indent(2).'</compmenu>'.nl();
				} // for

				$xml .= indent(1).'</compmenu>'.nl();
			} // for
		} // if

		$xml .= '</FacileFormsPackage>'.nl();
		fwrite($file, $xml);
		fclose($file);

		if (!$existed) {
			$filemode = NULL;
			if (isset($mosConfig_fileperms)) {
				if ($mosConfig_fileperms!='')
					$filemode = octdec($mosConfig_fileperms);
			} else
				$filemode = 0644;
			if (isset($filemode)) @chmod($xmlname, $filemode);
		} // if
		HTML_facileFormsConf::edit($option, $caller, $pkg, $xmlname);
	} // mkPackage

	function instPackage($option, $caller, $pkg)
	{
		$rows = _ff_select(
			"select * from #__facileforms_packages where id != '' order by id"
		);
		HTML_facileFormsConf::instPackage($option, $caller, $pkg, $rows);
	} // instPackage

	function instLocalPackage($option, $caller, $pkg)
	{
		global $ff_mospath, $errors, $errmode;

		$installfile = str_replace('{mospath}', $ff_mospath, BFRequest::getVar( 'installfile', ''));
		$errors = array();
		$errmode = 'log';
		$inst = new ff_importPackage();
		$ok = $inst->import($installfile);
		if (!$ok) {
			$msg = '';
			if (count($errors)) foreach($errors as $err) $msg .= $err.'<br/>';
			HTML_facileFormsConf::message($option, $caller, $pkg, $msg, 'instpackage');
		} else
			HTML_facileFormsConf::showPackage($option, $caller, $pkg, $inst);
	} // instLocalPackage

	function instUploadPackage($option, $caller, $pkg)
	{
		global $errors, $errmode;
		$uploadfile = $_FILES['uploadfile'];
		
		if ($uploadfile['name']=='') {
			HTML_facileFormsConf::message(
				$option, $caller, $pkg, BFText::_('COM_BREEZINGFORMS_INSTALLER_NOUPLOADFILE'), 'instpackage'
			);
			return;
		} // if
		$path = NULL;
		$msg = $this->uploadFile($uploadfile['tmp_name'], $uploadfile['name'], $path);
		if ($msg == '') {
			$errors = array();
			$errmode = 'log';
			$inst = new ff_importPackage();
			$ok = $inst->import($path);
			if (!$ok && count($errors)) foreach($errors as $err) $msg .= $err.'<br/>';
		} // if
                
		if ($msg != '')
			HTML_facileFormsConf::message($option, $caller, $pkg, $msg, 'instpackage');
		else
			HTML_facileFormsConf::showPackage($option, $caller, $pkg, $inst);
	} // instUploadPackage

	function uploadFile( $filename, $userfile_name, &$path)
	{
		global $ff_admpath, $mosConfig_fileperms;

		$baseDir = JPath::clean($ff_admpath.'/packages');
		if (!file_exists($baseDir))
			return BFText::_('COM_BREEZINGFORMS_INSTALLER_UPLOADNODIR');
		if (!is_writable($baseDir))
			return BFText::_('COM_BREEZINGFORMS_INSTALLER_UPLOADDIRNOTWRT');
		$path = $baseDir.'/'.$userfile_name;
		if (!move_uploaded_file($filename, $path))
			return BFText::_('COM_BREEZINGFORMS_INSTALLER_MOVEFAILED');
		$filemode = NULL;
		if (isset($mosConfig_fileperms)) {
			if ($mosConfig_fileperms!='')
				$filemode = octdec($mosConfig_fileperms);
		} else
			$filemode = 0644;
		if (isset($filemode))
			if (!@chmod($path, $filemode))
				return BFText::_('COM_BREEZINGFORMS_INSTALLER_CHMODFAILED');
		return '';
	} // uploadfile

	function uninstPackages($option, $caller, $pkg, $ids)
	{
		if (count($ids)) foreach ($ids as $id) dropPackage($id);
		HTML_facileFormsConf::message(
			$option, $caller, $pkg,
			count($ids).' '.BFText::_('COM_BREEZINGFORMS_INSTALLER_PKGSUNINST'),
			'instpackage'
		);
	} // uninstPackages

} // class facileFormsConfig

?>