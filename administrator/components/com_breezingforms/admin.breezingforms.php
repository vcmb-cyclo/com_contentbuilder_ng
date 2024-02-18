<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/

use Joomla\CMS\Version;

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFFactory.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFRequest.php');

if(BFRequest::getVar('mosmsg', '') != ''){

    JFactory::getApplication()->enqueueMessage(BFRequest::getVar('mosmsg', ''));
}

$db = BFFactory::getDbo();

if(!function_exists('bf_b64enc')){
    
    function bf_b64enc($str){
        $base = 'base';
        $sixty_four = '64_encode';
        return call_user_func($base.$sixty_four, $str);
    }

}

if(!function_exists('bf_b64dec')){
    function bf_b64dec($str){
        $base = 'base';
        $sixty_four = '64_decode';
        return call_user_func($base.$sixty_four, $str);
    }
}

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_breezingforms'.DS.'libraries'.DS.'crosstec'.DS.'classes'.DS.'BFJoomlaConfig.php');

jimport('joomla.version');
$version = new JVersion();

function bf_getTableFields($tables, $typeOnly = true)
{
        jimport('joomla.version');
        $version = new JVersion();

        $results = array();

        settype($tables, 'array');

        foreach ($tables as $table)
        {
            try{
                $results[$table] = BFFactory::getDbo()->getTableColumns($table, $typeOnly);
            }catch(Exception $e){  }
        }

        return $results;
}

$option = BFRequest::getCmd('option');
$task = BFRequest::getCmd('task');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

if ( !JFactory::getUser()->authorise('core.manage', 'com_breezingforms'))
{
    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
    JFactory::getApplication()->redirect('index.php', 403);
    return;
}

// purge ajax save
$sourcePath = JPATH_SITE . DS . 'components' . DS . 'com_breezingforms' . DS . 'exports'.DS;
if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
    while (false !== ($file = @readdir($handle))) {
        if($file!="." && $file!=".."&& $file!="index.html") {
            @JFile::delete($sourcePath.$file);
        }
    }
    @closedir($handle);
}

$sourcePath = JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_breezingforms' . DS . 'packages'.DS;
if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
    while (false !== ($file = @readdir($handle))) {
        if($file!="." && $file!=".." && $file!="index.html" && $file!="stdlib.english.xml") {
            @JFile::delete($sourcePath.$file);
        }
    }
    @closedir($handle);
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms')){
    JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms');
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'index.html')){
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'index.html', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'index.html'
    );
}

#### MAIL TEMPLATES

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'mailtpl')){
    JFolder::copy(
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_breezingforms'.DS.'mailtpl'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'mailtpl'.DS
    );
}

#### PDF TEMPLATES

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'pdftpl')){
    JFolder::copy(
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_breezingforms'.DS.'pdftpl'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'pdftpl'.DS
    );
}

JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'pdftpl'.DS.'fonts');

#### DOWNLOAD TEMPLATES

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'downloadtpl')){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'downloadtpl'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'downloadtpl'.DS
    );
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'downloadtpl'.DS.'stripe_download.php')){
    JFile::copy(
        JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'downloadtpl'.DS.'stripe_download.php',
        JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'downloadtpl'.DS.'stripe_download.php'
    );
}

#### UPLOADS

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads')){
    JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads');
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'uploads'.DS.'index.html', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads'.DS.'index.html'
    );
}

// Default upload folder is now htaccess protected 2016-02-16

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads'.DS.'.htaccess')){
    $def = 'deny from all';
    JFile::write(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads'.DS.'.htaccess', $def);
}

#### PAYMENT CACHE

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'payment_cache')){
    JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'payment_cache');
    
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'payment_cache'.DS.'.htaccess')){
    $def = 'deny from all';
    JFile::write(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'payment_cache'.DS.'.htaccess', $def);
}

#### DROPBOX CUSTOM KEYS

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'dropbox')){
    JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'dropbox');
    $def = 'deny from all';
    JFile::write(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'dropbox'.DS.'.htaccess', $def);
    JFile::copy(
            JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_breezingforms'.DS.'libraries'.DS.'dropbox'.DS.'config.json', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'dropbox'.DS.'config.json'
    );
}

#### THEMES

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes')){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode'.DS
    );
    JFolder::move(
           JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode'.DS,
           JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS
    );
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap4')){
    JFolder::copy(
        JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode-bootstrap4'.DS,
        JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode-bootstrap4'.DS
    );
    JFolder::move(
        JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode-bootstrap4'.DS,
        JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap4'.DS
    );
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap5')){
    JFolder::copy(
        JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode-bootstrap5'.DS,
        JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode-bootstrap5'.DS
    );
    JFolder::move(
        JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode-bootstrap5'.DS,
        JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap5'.DS
    );
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'images')){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'images'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'images'.DS
    );
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'images'.DS.'icons-png'.DS)){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'images'.DS.'icons-png'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'images'.DS.'icons-png'.DS
    );
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'jq.mobile.1.4.5.min.css')){
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'jq.mobile.1.4.5.min.css',
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'jq.mobile.1.4.5.min.css'
    );
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'jq.mobile.1.4.5.icons.min.css')){
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'jq.mobile.1.4.5.icons.min.css',
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'jq.mobile.1.4.5.icons.min.css'
    );
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'ajax-loader.gif')){
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'ajax-loader.gif', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'ajax-loader.gif'
    );
}

#### DELETE SYSTEM THEMES FILES FROM MEDIA FOLDER (the ones in the original themes path are being used)

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.css');
}

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie7.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie7.css');
}

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie6.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie6.css');
}

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie.css');
}

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap'.DS.'system.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap'.DS.'system.css');
}

/**
 * 
 * SAME CHECKS FOR CAPTCHA AS IN FRONTEND, SINCE THEY DONT SHARE THE SAME SESSION
 * 
 */
if(BFRequest::getBool('checkCaptcha')){
    
    ob_end_clean();
        
    require_once(JPATH_SITE . '/components/com_breezingforms/images/captcha/securimage.php');
    $securimage = new Securimage();
    if(!$securimage->check(str_replace('?','',BFRequest::getVar('value', '')))){
        echo 'capResult=>false';
    } else {
        echo 'capResult=>true';
    }
    exit;
    
}

$mainframe = JFactory::getApplication();

$cache = JFactory::getCache('com_content');
$cache->clean();

// force jquery to be loaded after mootools but before any other js (since J! 3.4)
JHtml::_('jquery.framework');

// purge ajax save
$sourcePath = JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . 'ajax_cache'.DS;
if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
    while (false !== ($file = @readdir($handle))) {
        if($file!="." && $file!="..") {
            $parts = explode('_', $file);
            if(count($parts)==3 && $parts[0] == 'ajaxsave') {
                if (@JFile::exists($sourcePath.$file) && @is_readable($sourcePath.$file)) {
                    $fileCreationTime = @filectime($sourcePath.$file);
                    $fileAge = time() - $fileCreationTime;
                    if($fileAge >= 86400) {
                        @JFile::delete($sourcePath.$file);
                    }
                }
            }
        }
    }
    @closedir($handle);
}

$tables = bf_getTableFields( BFFactory::getDBO()->getTableList() );
if(isset($tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms'])){


}

require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFTabs.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFText.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFTableElements.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/functions/helpers.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/constants.php');

jimport('joomla.version');
$version = new JVersion();

$_POST    = bf_stripslashes_deep($_POST);
$_GET     = bf_stripslashes_deep($_GET);
$_REQUEST = bf_stripslashes_deep($_REQUEST);

$db = BFFactory::getDbo();

/*
 * Temporary section end
 */

global $errors, $errmode;
global $ff_mospath, $ff_admpath, $ff_compath, $ff_request;
global $ff_mossite, $ff_admsite, $ff_admicon, $ff_comsite;
global $ff_config, $ff_compatible, $ff_install;

$my = JFactory::getUser();

if (!isset($ff_compath)) { // joomla!
    
    jimport('joomla.version');
    $version = new JVersion();

    // get paths
    $comppath = '/components/com_breezingforms';
    $ff_admpath = dirname(__FILE__);
    $ff_mospath = str_replace('\\','/',dirname(dirname(dirname($ff_admpath))));
    $ff_admpath = str_replace('\\','/',$ff_admpath);
    $ff_compath = $ff_mospath.$comppath;

    require_once($ff_admpath.'/toolbar.facileforms.php');
} // if

$errors = array();
$errmode = 'die';   // die or log

// compatibility check
if (!$ff_compatible) {
    echo '<h1>'.BFText::_('COM_BREEZINGFORMS_INCOMPATIBLE').'</h1>';
    exit;
} // if

// load ff parameters
$ff_request = array();
// reset($_REQUEST);
foreach($_REQUEST as $prop => $val){
    if (is_scalar($val) && substr($prop,0,9)=='ff_param_')
        $ff_request[$prop] = $val;
}

if ($ff_install) {
    $act = 'installation';
    $task = 'step2';
} // if

$ids = BFRequest::getVar( 'ids', array());

echo '<div class="row" id="bf-content"><div class="col-md-12">';

switch($act) {
    case 'installation':
        require_once($ff_admpath.'/admin/install.php');
        break;
    case 'configuration':
        require_once($ff_admpath.'/admin/config.php');
        break;
    case 'managemenus':
        require_once($ff_admpath.'/admin/menu.php');
        break;
    case 'manageforms':
        require_once($ff_admpath.'/admin/form.php');
        break;
    case 'editpage':
        require_once($ff_admpath.'/admin/element.php');
        break;
    case 'managescripts':
        require_once($ff_admpath.'/admin/script.php');
        break;
    case 'managepieces':
        require_once($ff_admpath.'/admin/piece.php');
        break;
    case 'run':
        require_once($ff_admpath.'/admin/run.php');
        break;
    case 'easymode':
        require_once($ff_admpath.'/admin/easymode.php');
        break;
    case 'quickmode':
        require_once($ff_admpath.'/admin/quickmode.php');
        break;
    case 'quickmode_editor':
        require_once($ff_admpath.'/admin/quickmode-editor.php');
        break;
    case 'integrate':
        require_once($ff_admpath.'/admin/integrator.php');
        break;
    case 'recordmanagement':
        require_once($ff_admpath.'/admin/recordmanagement.php');
        break;
    default:
        require_once($ff_admpath.'/admin/recordmanagement.php');
        break;
} // switch

echo '</div></div>';

// some general purpose functions for admin

function isInputElement($type)
{
    switch ($type) {
        case 'Static Text/HTML':
        case 'Rectangle':
        case 'Image':
        case 'Tooltip':
        case 'Query List':
        case 'Regular Button':
        case 'Graphic Button':
        case 'Icon':
            return false;
        default:
            break;
    } // switch
    return true;
} // isInputElement

function isVisibleElement($type)
{
    switch ($type) {
        case 'Hidden Input':
            return false;
        default:
            break;
    } // switch
    return true;
} // isVisibleElement

function _ff_query($sql, $insert = 0)
{
    global $database, $errors;
    $database = BFFactory::getDbo();
    $id = null;
    $database->setQuery($sql);
    $database->query();
    if ($database->getErrorNum()) {
        if (isset($errmode) && $errmode=='log')
            $errors[] = $database->getErrorMsg();
        else
            die($database->stderr());
    } // if
    if ($insert) $id = $database->insertid();
    return $id;
} // _ff_query

function _ff_select($sql)
{
    global $database, $errors;
    $database = BFFactory::getDbo();
    $database->setQuery($sql);
    $rows = $database->loadObjectList();
    if ($database->getErrorNum()) {
        if (isset($errmode) && $errmode=='log')
            $errors[] = $database->getErrorMsg();
        else
            die($database->stderr());
    } // if
    
    return $rows;
} // _ff_select

function _ff_selectValue($sql)
{
    global $database, $errors;
    $database = BFFactory::getDbo();
    $database->setQuery($sql);
    $value = $database->loadResult();
    if ($database->getErrorNum()) {
        
            die($database->stderr());
    } // if
    return $value;
} // _ff_selectValue

function protectedComponentIds()
{

    $rows = _ff_select(
        "select id, parent_id As parent from #__menu ".
        "where ".
        " link in (".
            "'index.php?option=com_breezingforms&act=managerecs',".
            "'index.php?option=com_breezingforms&act=managemenus',".
            "'index.php?option=com_breezingforms&act=manageforms',".
            "'index.php?option=com_breezingforms&act=managescripts',".
            "'index.php?option=com_breezingforms&act=managepieces',".
            "'index.php?option=com_breezingforms&act=share',".
            "'index.php?option=com_breezingforms&act=integrate',".
            "'index.php?option=com_breezingforms&act=configuration'".
        ") ".
        "order by id"
    );

    $parent = 0;
    $ids = array();
    if (count($rows))
        foreach ($rows as $row) {
            if ($parent == 0) {
                $parent = 1;
                if(isset($row->parent)){
                    $ids[] = intval($row->parent);
                }
            } // if
            $ids[] = intval($row->id);
        } // foreach
 return implode(',', $ids);
} // protectedComponentIds

function addComponentMenu($row, $parent, $copy = false)
{
    $db = BFFactory::getDbo();
    $admin_menu_link = '';
    if ($row->name!='') {
        $admin_menu_link =
            'option=com_breezingforms'.
            '&act=run'.
            '&ff_name='.htmlentities($row->name, ENT_QUOTES, 'UTF-8');
        if ($row->page!=1) $admin_menu_link .= '&ff_page='.htmlentities($row->page, ENT_QUOTES, 'UTF-8');
        if ($row->frame==1) $admin_menu_link .= '&ff_frame=1';
        if ($row->border==1) $admin_menu_link .= '&ff_border=1';
        if ($row->params!='') $admin_menu_link .= $row->params;
    } // if
    if ($parent==0) $ordering = 0; else $ordering = $row->ordering;

        jimport('joomla.version');
        $version = new JVersion();

        if(version_compare($version->getShortVersion(), '3.0', '<') && version_compare($version->getShortVersion(), '1.6', '>=')){

            $parent = $parent == 0 ? 1 : $parent;

            $db->setQuery("Select component_id From #__menu Where link = 'index.php?option=com_breezingforms' And parent_id = 1");
            $result = $db->loadResult();
            if($result){
                
                return _ff_query(
                    "insert into #__menu (".
                            "`title`, alias, menutype, parent_id, ".
                            "link,".
                            "ordering, level, component_id, client_id, img, lft, rgt".
                    ") ".
                    "values (".$db->Quote( ($copy ? 'Copy of ' : '') . $row->title . ($copy ? ' ('.md5(session_id().microtime().mt_rand(0,  mt_getrandmax())).')' : '')).", ".$db->Quote( ($copy ? 'Copy of ' : '') . $row->title . ($copy ? ' ('.md5(session_id().microtime().mt_rand(0,  mt_getrandmax())).')' : '')).", 'menu', $parent, ".
                            "'index.php?$admin_menu_link',".
                            "'$ordering', 1, ".intval($result).", 1, 'components/com_breezingforms/images/$row->img',( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet )".
                    ")",
                    true
                );
            }else{
                die("BreezingForms main menu item not found!");
            }
        } else if(version_compare($version->getShortVersion(), '3.0', '>=')){
            $parent = $parent == 0 ? 1 : $parent;

            $db->setQuery("Select component_id From #__menu Where link = 'index.php?option=com_breezingforms' And parent_id = 1");
            $result = $db->loadResult();
            if($result){
                
                return _ff_query(
                    "insert into #__menu (".
                            "`title`, alias, menutype, parent_id, ".
                            "link,".
                            "level, component_id, client_id, img, lft, rgt".
                    ") ".
                    "values (".$db->Quote( ($copy ? 'Copy of ' : '') . $row->title . ($copy ? ' ('.md5(session_id().microtime().mt_rand(0,  mt_getrandmax())).')' : '')).", ".$db->Quote( ($copy ? 'Copy of ' : '') . $row->title . ($copy ? ' ('.md5(session_id().microtime().mt_rand(0,  mt_getrandmax())).')' : '')).", 'menu', $parent, ".
                            "'index.php?$admin_menu_link',".
                            "1, ".intval($result).", 1, 'components/com_breezingforms/images/$row->img',( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet )".
                    ")",
                    true
                );
            }else{
                die("BreezingForms main menu item not found!");
            }
        }
        // if older JVersion
    return _ff_query(
        "insert into #__components (".
            "id, name, link, menuid, parent, ".
            "admin_menu_link, admin_menu_alt, `option`, ".
            "ordering, admin_menu_img, iscore, params".
        ") ".
        "values (".
            "'', ".$db->Quote($row->title).", '', 0, $parent, ".
            "'$admin_menu_link', ".$db->Quote($row->title).", 'com_breezingforms', ".
            "'$ordering', '$row->img', 1, ''".
        ")",
        true
    );
} // addComponentMenu

function updateComponentMenus($copy = false)
{
    // remove unprotected menu items
    $protids = protectedComponentIds();
    if(trim($protids)!=''){

            jimport('joomla.version');
            $version = new JVersion();

            if(version_compare($version->getShortVersion(), '1.6', '>=')){
                _ff_query(
            "delete from #__menu ".
            "where `link` Like 'index.php?option=com_breezingforms&act=run%' ".
            "and id not in ($protids)"
        );
            }else{
        _ff_query(
            "delete from #__components ".
            "where `option`='com_breezingforms' ".
            "and id not in ($protids)"
        );
            }
    } 
    
    // add published menu items
    $rows = _ff_select(
        "select ".
            "m.id as id, ".
            "m.parent as parent, ".
            "m.ordering as ordering, ".
            "m.title as title, ".
            "m.img as img, ".
            "m.name as name, ".
            "m.page as page, ".
            "m.frame as frame, ".
            "m.border as border, ".
            "m.params as params, ".
            "m.published as published ".
        "from #__facileforms_compmenus as m ".
            "left join #__facileforms_compmenus as p on m.parent=p.id ".
        "where m.published=1 ".
            "and (m.parent=0 or p.published=1) ".
        "order by ".
            "if(m.parent,p.ordering,m.ordering), ".
            "if(m.parent,m.ordering,-1)"
    );
    $parent = 0;
    if (count($rows)) foreach ($rows as $row) {

                jimport('joomla.version');
                $version = new JVersion();

                if(version_compare($version->getShortVersion(), '1.6', '>=')){

                    BFFactory::getDbo()->setQuery("Select id From #__menu Where `alias` = " . BFFactory::getDbo()->Quote($row->title));

                    if(BFFactory::getDbo()->loadResult()){
                        return BFText::_('COM_BREEZINGFORMS_MENU_ITEM_EXISTS');
                    }

                    if ($row->parent==0 || $row->parent==1){
                            $parent = addComponentMenu($row, 1, $copy);
                    }else{
                            addComponentMenu($row, $parent, $copy);
                    }
                }else{
                    if ($row->parent==0){
                            $parent = addComponentMenu($row, 0);
                    }else{
                            addComponentMenu($row, $parent);
                    }
                }
    } // foreach

        return '';
} // updateComponentMenus

function dropPackage($id)
{
    // drop package settings
    _ff_query("delete from #__facileforms_packages where id = ".BFFactory::getDbo()->Quote($id)."");

    // drop backend menus
    $rows = _ff_select("select id from #__facileforms_compmenus where package = ".BFFactory::getDbo()->Quote($id)."");
    if (count($rows)) foreach ($rows as $row)
        _ff_query("delete from #__facileforms_compmenus where id=$row->id or parent=$row->id");
    updateComponentMenus();

    // drop forms
    $rows = _ff_select("select id from #__facileforms_forms where package = ".BFFactory::getDbo()->Quote($id)."");
    if (count($rows)) foreach ($rows as $row) {
        _ff_query("delete from #__facileforms_elements where form = $row->id");
        _ff_query("delete from #__facileforms_forms where id = $row->id");
    } // if

    // drop scripts
    _ff_query("delete from #__facileforms_scripts where package =  ".BFFactory::getDbo()->Quote($id)."");

    // drop pieces
    _ff_query("delete from #__facileforms_pieces where package =  ".BFFactory::getDbo()->Quote($id)."");
} // dropPackage

function savePackage($id, $name, $title, $version, $created, $author, $email, $url, $description, $copyright)
{
    $db = BFFactory::getDbo();
    $cnt = _ff_selectValue("select count(*) from #__facileforms_packages where id=".BFFactory::getDbo()->Quote($id)."");
    if (!$cnt) {
        
        _ff_query(
            "insert into #__facileforms_packages ".
                    "(id, name, title, version, created, author, ".
                     "email, url, description, copyright) ".
            "values (".$db->Quote($id).", ".$db->Quote($name).", ".$db->Quote($title).", ".$db->Quote($version).", ".$db->Quote($created).", ".$db->Quote($author).",
                    ".$db->Quote($email).", ".$db->Quote($url).", ".$db->Quote($description).", ".$db->Quote($copyright).")"
        );
    } else {
        _ff_query(
            "update #__facileforms_packages ".
                "set name=".$db->Quote($name).", title=".$db->Quote($title).", version=".$db->Quote($version).", created=".$db->Quote($created).", author=".$db->Quote($author).", ".
                "email=".$db->Quote($email).", url=".$db->Quote($url).", description=".$db->Quote($description).", copyright=".$db->Quote($copyright). " 
            where id =  ".$db->Quote($id)
        );
    } // if
} // savePackage

function relinkScripts(&$oldscripts)
{
    if ($oldscripts != null && @count($oldscripts))
        foreach ($oldscripts as $row) {
            $newid = _ff_selectValue("select max(id) from #__facileforms_scripts where name = ".BFFactory::getDbo()->Quote($row->name)."");
            if ($newid) {
                _ff_query("update #__facileforms_forms set script1id=$newid where script1id=$row->id");
                _ff_query("update #__facileforms_forms set script2id=$newid where script2id=$row->id");
                _ff_query("update #__facileforms_elements set script1id=$newid where script1id=$row->id");
                _ff_query("update #__facileforms_elements set script2id=$newid where script2id=$row->id");
                _ff_query("update #__facileforms_elements set script3id=$newid where script3id=$row->id");
            } // if
        } // foreach
} // relinkScripts

function relinkPieces(&$oldpieces)
{
    if ($oldpieces != null && @count($oldpieces))
        foreach ($oldpieces as $row) {
            $newid = _ff_selectValue("select max(id) from #__facileforms_pieces where name = ".BFFactory::getDbo()->Quote($row->name)."");
            if ($newid) {
                _ff_query("update #__facileforms_forms set piece1id=$newid where piece1id=$row->id");
                _ff_query("update #__facileforms_forms set piece2id=$newid where piece2id=$row->id");
                _ff_query("update #__facileforms_forms set piece3id=$newid where piece3id=$row->id");
                _ff_query("update #__facileforms_forms set piece4id=$newid where piece4id=$row->id");
            } // if
        } // foreach
} // relinkPieces