<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

$database = Factory::getContainer()->get(DatabaseInterface::class);

/*
ob_start();
print_r($_REQUEST);
$ffCheck = ob_get_contents();
ob_end_clean();
echo 'run out: <pre>'.$ffCheck.'</pre>';
*/

// process form parameters
$formid = BFRequest::getInt('ff_form',0);
if ($formid!=null) {
	$database->setQuery(
		"select * from #__facileforms_forms ".
		"where id=".$database->quote($formid)." ".
		"and published=1 ".
		"and (runmode=0 or runmode=2)"
	);
	$forms = $database->loadObjectList();
	if (count($forms) < 1) { echo 'Form '.$formid.' not found!'; exit; }
	$form = $forms[0];
} else {
	$formname = BFRequest::getVar('ff_name','');
	if ($formname == '') {
		echo 'Parameter <em>&ff_name=FormName</em> missing in URL.';
		exit;
	} // if
	$database->setQuery(
		"select * from #__facileforms_forms ".
		"where name=".$database->quote($formname)." ".
		"and published=1 ".
		"and (runmode=0 or runmode=2)".
		"order by ordering, id"
	);
	$forms = $database->loadObjectList();
	if (count($forms) < 1) { echo 'Form <em>'.htmlentities($formname, ENT_QUOTES, 'UTF-8').'</em> not found!'; exit; }
	$form = $forms[0];
} // if

$page    = BFRequest::getVar('ff_page',1);
$inframe = BFRequest::getVar('ff_frame',0);
$border  = BFRequest::getVar('ff_border',0);
$task    = BFRequest::getVar('ff_task','view');

if ($inframe) {
	// create url for the frame
	$url =
		$ff_mossite = Uri::root().'index.php'
			.'?option=com_breezingforms'
                        .'&amp;tmpl=component'
			.'&amp;Itemid=0'
			.'&amp;ff_form='.htmlentities($form->id, ENT_QUOTES, 'UTF-8')
			
			.'&amp;ff_runmode='.htmlentities(_FF_RUNMODE_BACKEND, ENT_QUOTES, 'UTF-8');
	if ($page != 1) $url .= '&amp;ff_page='.htmlentities($page, ENT_QUOTES, 'UTF-8');
	if ($border) $url .= '&amp;ff_border=1';
	reset($ff_request);
	while (list($prop, $val) = each($ff_request))
		$url .= '&amp;'.htmlentities($prop, ENT_QUOTES, 'UTF-8').'='.htmlentities(urlencode($val), ENT_QUOTES, 'UTF-8');

	// prepare iframe width
	$framewidth = 'width="'.htmlentities($form->width, ENT_QUOTES, 'UTF-8');
	if ($form->widthmode) $framewidth .= '%" '; else $framewidth .= '" ';

	// prepare iframe height
	$frameheight = '';
	if (!$form->heightmode) $frameheight = 'height="'.htmlentities($form->height, ENT_QUOTES, 'UTF-8').'" ';

	// assemble iframe parameters
	$params =   'id="ff_frame'.htmlentities($form->id, ENT_QUOTES, 'UTF-8').'" '.
				'src="'.$url.'" '.
				$framewidth.
				$frameheight.
				'frameborder="'.htmlentities($border, ENT_QUOTES, 'UTF-8').'" '.
				'allowtransparency="true" '.
				'scrolling="no"';

	// emit frame code
	echo "<iframe ".$params.">\n".
		 "<p>Sorry, your browser cannot display frames!</p>\n".
		 "</iframe>\n".
		 "</div>\n";
} else {
	// process inline
	
	/**
	 * @var JUser
	 */
	$myUser = Factory::getApplication()->getIdentity();
	
	$database->setQuery("select id from #__users where lower(username)=lower('".$myUser->get('username','')."')");
	$id = $database->loadResult();
	if ($id) $myUser->get('id',-1);
	require_once($ff_compath.'/facileforms.process.php');
	if ($task == 'view') {
		echo '<div id="overDiv" style="position:absolute;visibility:hidden;z-index:1000;"></div>'."\n";
		$divstyle = 'width:'.$form->width;
		$divstyle .= ($form->widthmode) ? '%;' : 'px;';;
		if (!$form->heightmode) $divstyle .= 'height:'.$form->height.'px;';
		$tablestyle = ($divstyle=='') ? '' : ' style="'.$divstyle.'"';
		echo '<table cellpadding="0" cellspacing="0" border="'.$border.'"'.$tablestyle.'>'."\n".
			 "<tr><td>\n".
			 '<div style="left:0px;top:0px;'.$divstyle.'position:relative;">'."\n";
	} // if
	$curdir = getcwd();
	chdir($ff_mospath);
	$ff_processor = new HTML_facileFormsProcessor(
		_FF_RUNMODE_BACKEND, false, $form->id, $page, $border
	);
	chdir($curdir);
	if ($task == 'submit')
		$ff_processor->submit();
	else {
		$ff_processor->view();
		echo "</div>\n</td></tr>\n</table>\n";
	} // if
} // if

?>