<?php

/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024 by XDA+GIL 
 * @license Released under the terms of the GNU General Public License
 *
 * This is the main component entry point that will be called by joomla or mambo
 * after after calling
 *
 *     http://siteurl/index.php?option=com_breezingforms......
 * The first form is the normal call from frontend where the whole page is
 * displayed by uting the template. The second form is a display of the plain
 * form, wich is used to run in iframe or in popup windows.
 * */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFFactory.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFRequest.php');

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!function_exists('bf_b64enc')) {

    function bf_b64enc($str)
    {
        $base = 'base';
        $sixty_four = '64_encode';
        return call_user_func($base . $sixty_four, $str);
    }

}

if (!function_exists('bf_b64dec')) {

    function bf_b64dec($str)
    {
        $base = 'base';
        $sixty_four = '64_decode';
        return call_user_func($base . $sixty_four, $str);
    }

}

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_breezingforms' . DS . 'libraries' . DS . 'crosstec' . DS . 'classes' . DS . 'BFJoomlaConfig.php');

$mainframe = Factory::getApplication();

$cache = Factory::getCache();
$cache->setCaching(false);

jimport('joomla.filesystem.file');

require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFText.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFTableElements.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/functions/helpers.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/constants.php');

// declare global variables
global
$database, // joomla/mambo database object
$ff_version, // FacileForms version number
$ff_config, // FacileForms configuration object
$ff_mospath, // path to root of joomla/mambo
$ff_compath, // path to component frontend root
$ff_mossite, // url of the site root
$ff_request, // array of request parameters ff_param_*
$ff_processor, // current form procesor object
$ff_target;    // index of form on current page

$database = $db = Factory::getContainer()->get(DatabaseInterface::class);

// declare local vars
// (1) only used in component space and not plain form)
$plainform = 0;  // running as plain form by index.php?tmpl=component
$formid = null;  // form id number
$formname = null;  // form name
$task = 'view'; // either 'view' or 'submit'
$page = 1;  // page to display
$inframe = 0;  // run in iframe
$border = 0;  // show a border around the form (1)
$align = 1;  // 0-left 1-center 2-right (1)
$left = 0;  // left margin in px (1)
$top = 0;  // top margin in px (1)
$suffix = '';  // CSS class suffix
$parprv = '';  // private parameters
$runmode = 0;  // run mode
$pagetitle = true;  // set page title
$editable = 0;
$editable_override = 0;
$menu_item_title = '';
$menu_item_show_page_heading = 0;
$menu_item_page_heading = '';
$menu_item_meta_description = '';
$menu_item_meta_keywords = '';
$menu_item_robots = '';

if (!isset($xModuleId)) {
    $xModuleId = 0;
}

if (!isset($ff_applic)) {
    $ff_applic = '';
}

$runmode = htmlentities(@BFRequest::getVar('ff_runmode', $runmode), ENT_QUOTES, 'UTF-8');

// get paths
$ff_mospath = JPATH_SITE;
$ff_compath = $ff_mospath . '/components/com_breezingforms';

// load config and initialize globals
require_once($ff_compath . '/facileforms.class.php');
$ff_config = new facileFormsConf();
initFacileForms();

// check for plain form

$plainform = BFRequest::getWord('tmpl', '') == 'component';

// create target id for this form and check if ff params are ment for this target
if (!$ff_target)
    $ff_target = 1;
else
    $ff_target++;
$parent_target = @BFRequest::getInt('ff_target', 1);
$my_ff_params = $plainform || $parent_target == $ff_target;

// clear list of request parameters
$ff_request = array();

if (
    !BFRequest::getBool('bfCaptcha') &&
    !BFRequest::getBool('checkCaptcha') &&
    !BFRequest::getBool('confirmStripe') &&
    !BFRequest::getBool('confirmPayPal') &&
    !BFRequest::getBool('confirmPayPalIpn') &&
    !BFRequest::getBool('paypalDownload') &&
    !BFRequest::getBool('stripeDownload') &&
    !BFRequest::getBool('showPayPalConnectMsg') &&
    !BFRequest::getBool('successSofortueberweisung') &&
    !BFRequest::getBool('confirmSofortueberweisung') &&
    !BFRequest::getBool('sofortueberweisungDownload') &&
    !BFRequest::getBool('flashUpload') &&
    BFRequest::getVar('opt_in') != 'true' &&
    BFRequest::getVar('opt_out') != 'true'
) {

    BFRequest::setVar('format', 'html');

    if ($runmode == _FF_RUNMODE_FRONTEND) {

        // is this called by a module?
        if (isset($ff_applic) && $ff_applic == 'mod_facileforms') {

            // get the module parameters
            $formname = $params->get('ff_mod_name');
            $page = intval($params->get('ff_mod_page', $page));
            $inframe = intval($params->get('ff_mod_frame', $inframe));
            $border = intval($params->get('ff_mod_border', $border));
            $align = intval($params->get('ff_mod_align', $align));
            $left = intval($params->get('ff_mod_left', $left));
            $top = intval($params->get('ff_mod_top', $top));
            $suffix = $params->get('ff_mod_suffix', '');
            $parprv = $params->get('ff_mod_parprv', '');
            addRequestParams($params->get('ff_mod_parpub', ''));
            $pagetitle = false;

            Factory::getSession()->set('ff_editableMod' . $xModuleId . $formname, intval($params->get('ff_mod_editable', $editable)));
            Factory::getSession()->set('ff_editable_overrideMod' . $xModuleId . $formname, intval($params->get('ff_mod_editable_override', $editable_override)));
        } else if (isset($ff_applic) && $ff_applic == 'plg_facileforms') {

            $formname = htmlentities(BFRequest::getVar('ff_name', ''), ENT_QUOTES, 'UTF-8');
            $page = htmlentities(BFRequest::getVar('ff_page', 1), ENT_QUOTES, 'UTF-8');
            $inframe = htmlentities(BFRequest::getVar('ff_frame', ''), ENT_QUOTES, 'UTF-8');
            $border = htmlentities(BFRequest::getVar('ff_border', ''), ENT_QUOTES, 'UTF-8');
            $align = htmlentities(BFRequest::getVar('ff_align', ''), ENT_QUOTES, 'UTF-8');
            $editable = intval($plg_editable);
            $editable_override = intval($plg_editable_override);
            $left = '';
            $top = '';
            $suffix = htmlentities(BFRequest::getVar('ff_suffix', ''), ENT_QUOTES, 'UTF-8');
            $parprv = '';
            addRequestParams('');
        } else {

            // is this called with an Itemid?
            if (BFRequest::getInt('Itemid', 0) > 0 && BFRequest::getVar('ff_applic', '') != 'mod_facileforms' && BFRequest::getVar('ff_applic', '') != 'plg_facileforms') {

                $menu = Factory::getApplication()->getMenu()->getActive();
                $params = $menu->getParams();

                if ($params !== null) {

                    $menu_item_title = $params->get('page_title', '');
                    $menu_item_show_page_heading = $params->get('show_page_heading', 0);
                    $menu_item_page_heading = $params->get('page_heading', '');

                    $menu_item_meta_description = $params->get('menu-meta_description', '');
                    $menu_item_meta_keywords = $params->get('menu-meta_keywords', '');
                    $menu_item_robots = $params->get('robots', '');

                    if ($menu_item_meta_description) {
                        Factory::getDocument()->setMetaData('description', $menu_item_meta_description);
                    }

                    if ($menu_item_meta_keywords) {
                        Factory::getDocument()->setMetaData('keywords', $menu_item_meta_keywords);
                    }

                    if ($menu_item_robots) {
                        Factory::getDocument()->setMetaData('robots', $menu_item_robots);
                    }

                    $formname = $params->get('ff_com_name');
                    $page = intval($params->get('ff_com_page', $page));
                    $inframe = intval($params->get('ff_com_frame', $inframe));
                    $border = intval($params->get('ff_com_border', $border));
                    $align = intval($params->get('ff_com_align', $align));
                    $left = intval($params->get('ff_com_left', $left));
                    $top = intval($params->get('ff_com_top', $top));
                    $editable = intval($params->get('ff_com_editable', $editable));
                    $editable_override = intval($params->get('ff_com_editable_override', $editable_override));
                    $suffix = $params->get('ff_com_suffix', '');
                    $parprv = $params->get('ff_com_parprv', '');
                    addRequestParams($params->get('ff_com_parpub', ''));
                }
            } // if
        }
    } // if

    if ($my_ff_params) {
        // allow overriding by url params
        $formid = @BFRequest::getVar('ff_form', $formid);

        if ($formid == null)
            $formname = @BFRequest::getVar('ff_name', $formname);
        else
            $formname = null;

        $task = @BFRequest::getVar('ff_task', $task);
        $page = @BFRequest::getVar('ff_page', $page);
        $inframe = @BFRequest::getVar('ff_frame', $inframe);
        $border = @BFRequest::getVar('ff_border', $border);
        $align1 = @BFRequest::getVar('ff_align', -1);
        if ($align1 >= 0) {
            $align = @BFRequest::getVar('ff_align', $align);
            $left = 0;
            if ($align > 2) {
                $left = $align;
                $align = 3;
            }
        } // if
        $top = @BFRequest::getVar('ff_top', $top);
        $suffix = @BFRequest::getVar('ff_suffix', $suffix);
    }

    // load form
    $ok = true;
    if (is_numeric($formid)) {
        $database->setQuery(
            "select * from #__facileforms_forms " .
            "where id=" . intval($formid) . " and published=1"
        );
        $forms = $database->loadObjectList();
        if (count($forms) < 1) {
            echo '[Form ' . intval($formid) . ' not found!]';
            $ok = false;
        } else
            $form = $forms[0];
    } else
        if ($formname != null) {
            $database->setQuery(
                "select * from #__facileforms_forms " .
                "where name=" . $database->Quote($formname) . " and published=1 " .
                "order by ordering, id"
            );
            $forms = $database->loadObjectList();
            if (count($forms) < 1) {
                echo '[Form ' . htmlentities($formname, ENT_QUOTES, 'UTF-8') . ' not found!]';
                $ok = false;
            } else
                $form = $forms[0];
        } else {

            if (BFRequest::getVar('option', '') != 'com_breezingforms') {
                throw new Exception(Text::_('No form id or name provided!'), 404);
            } else {
                echo '[No form id or name provided!]';
            }

            $ok = false;
        } // if

    if ($ok) {

        // set by plugin
        if (isset($_SESSION['ff_editablePlg' . $form->name]) && $_SESSION['ff_editablePlg' . BFRequest::getInt('ff_contentid', 0) . $form->name] != 0 && (BFRequest::getVar('ff_applic') == 'plg_facileforms' || (isset($ff_applic) && $ff_applic == 'plg_facileforms'))) {
            $editable = $_SESSION['ff_editablePlg' . BFRequest::getInt('ff_contentid', 0) . $form->name];
        }

        // set by plugin
        if (isset($_SESSION['ff_editable_overridePlg' . $form->name]) && $_SESSION['ff_editable_overridePlg' . BFRequest::getInt('ff_contentid', 0) . $form->name] != 0 && (BFRequest::getVar('ff_applic') == 'plg_facileforms' || (isset($ff_applic) && $ff_applic == 'plg_facileforms'))) {
            $editable_override = $_SESSION['ff_editable_overridePlg' . BFRequest::getInt('ff_contentid', 0) . $form->name];
        }

        // set by module
        if ((BFRequest::getVar('ff_applic') == 'mod_facileforms' || (isset($ff_applic) && $ff_applic == 'mod_facileforms'))) {
            if (Factory::getSession()->get('ff_editableMod' . $xModuleId . $form->name, 0) != 0) {
                $editable = Factory::getSession()->get('ff_editableMod' . $xModuleId . $form->name, 0);
            } else if (Factory::getSession()->get('ff_editableMod' . BFRequest::getInt('ff_module_id', 0) . $form->name, 0) != 0) {
                $editable = Factory::getSession()->get('ff_editableMod' . BFRequest::getInt('ff_module_id', 0) . $form->name, 0);
            }
        }

        // set by module
        if ((BFRequest::getVar('ff_applic') == 'mod_facileforms' || (isset($ff_applic) && $ff_applic == 'mod_facileforms'))) {
            if (Factory::getSession()->get('ff_editable_overrideMod' . $xModuleId . $form->name, 0) != 0) {
                $editable_override = Factory::getSession()->get('ff_editable_overrideMod' . $xModuleId . $form->name, 0);
            } else if (Factory::getSession()->get('ff_editable_overrideMod' . BFRequest::getInt('ff_module_id', 0) . $form->name, 0) != 0) {
                $editable_override = Factory::getSession()->get('ff_editable_overrideMod' . BFRequest::getInt('ff_module_id', 0) . $form->name, 0);
            }
        }

        if ((!isset($ff_applic) || $ff_applic != 'plg_facileforms') && $pagetitle && $form->title != '' && !(BFRequest::getInt('cb_form_id', 0) || BFRequest::getCmd('cb_record_id', ''))) {
            if ($menu_item_title != '') {
                Factory::getDocument()->setTitle($menu_item_title);
            } else if ($pagetitle) { // being set by module, false implies no change at all
                Factory::getDocument()->setTitle($form->title);
            }
        }

        if ($form->name == $formname)
            addRequestParams($parprv);
        if ($my_ff_params) {
            // reset($_REQUEST);
            foreach ($_REQUEST as $prop => $val) {
                if (!is_array($val) && substr($prop, 0, 9) == 'ff_param_')
                    $ff_request[$prop] = $val;
            }
            // Deprecated in PHP 7.2 version so code above is used
            // while (list($prop, $val) = each($_REQUEST))
            // 	if (!is_array($val) && substr($prop,0,9)=='ff_param_')
            // 		$ff_request[$prop] = $val;
        } // if

        if ($inframe && !$plainform) {

            // open frame and detach processing
            $divstyle = 'width:100%;';
            switch ($align) {
                case 0:
                    $divstyle .= 'text-align:left;';
                    break;
                case 1:
                    $divstyle .= 'text-align:center;';
                    break;
                case 2:
                    $divstyle .= 'text-align:right;';
                    break;
                case 3:
                    if ($left > 0)
                        $divstyle .= 'padding-left:' . htmlentities($left, ENT_QUOTES, 'UTF-8') . 'px;';
                    break;
                default:
                    break;
            } // switch
            if ($top > 0)
                $divstyle .= 'padding-top:' . htmlentities($top, ENT_QUOTES, 'UTF-8') . 'px;';
            $framewidth = 'width="' . htmlentities($form->width . ($form->widthmode ? '%' : ''), ENT_QUOTES, 'UTF-8') . '" ';
            $frameheight = '';
            if (!$form->heightmode)
                $frameheight = 'height="' . htmlentities($form->height, ENT_QUOTES, 'UTF-8') . '" ';
            $url = $ff_mossite . '/index.php'
                . '?option=com_breezingforms'
                . '&amp;Itemid=' . ((BFRequest::getInt('Itemid', 0) > 0 && BFRequest::getInt('Itemid', 0) < 99999999) ? BFRequest::getInt('Itemid', 0) : 0)
                . '&amp;ff_form=' . htmlentities($form->id, ENT_QUOTES, 'UTF-8')
                . '&amp;ff_applic=' . htmlentities($ff_applic, ENT_QUOTES, 'UTF-8')
                . '&amp;ff_module_id=' . htmlentities($xModuleId, ENT_QUOTES, 'UTF-8')
                . '&amp;format=html'
                . '&amp;tmpl=component'
                . '&amp;ff_frame=1';
            if ($page != 1)
                $url .= '&amp;ff_page=' . htmlentities($page, ENT_QUOTES, 'UTF-8');
            if ($border)
                $url .= '&amp;ff_border=1';
            if ($parent_target > 1)
                $url .= '&amp;ff_target=' . htmlentities($parent_target, ENT_QUOTES, 'UTF-8');
            reset($ff_request);

            foreach ($ff_request as $prop => $val)
                $url .= '&amp;' . htmlentities($prop, ENT_QUOTES, 'UTF-8') . '=' . htmlentities(urlencode($val), ENT_QUOTES, 'UTF-8');

            $params = 'id="ff_frame' . $form->id . '" ' .
                'src="' . $url . '" ' .
                $framewidth .
                $frameheight .
                'frameborder="' . htmlentities($border, ENT_QUOTES, 'UTF-8') . '" ' .
                'allowtransparency="true" ' .
                'scrolling="no" ';
            if ($form->autoheight == 1) {
                Factory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jq.min.js');
                Factory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jq.iframeautoheight.js');
                Factory::getDocument()->addScriptDeclaration("<!--
                            JQuery(document).ready(function() {
                                //JQuery(\".breezingforms_iframe\").css(\"width\",\"100%\");
                                JQuery(\".breezingforms_iframe\").iframeAutoHeight({heightOffset: 15, debug: false, diagnostics: false});
                            });
                            //-->");
            }

            // DO NOT REMOVE OR CHANGE OR OTHERWISE MAKE INVISIBLE THE FOLLOWING COPYRIGHT MESSAGE
            // FAILURE TO COMPLY IS A DIRECT VIOLATION OF THE GNU GENERAL PUBLIC LICENSE
            // http://www.gnu.org/copyleft/gpl.html
            echo "\n<!-- BreezingForms V" . $ff_version . " Copyright(c) 2008-2013 by Markus Bopp | FacileForms Copyright 2004-2006 by Peter Koch, Chur, Switzerland.  All rights reserved. -->\n";
            // END OF COPYRIGHT
            echo '<div class="bfClearfix" style="' . $divstyle . '">' . "\n" .
                "<iframe class=\"breezingforms_iframe\" " . $params . " sandbox=\"allow-modals allow-same-origin allow-scripts allow-forms allow-pointer-lock allow-popups allow-top-navigation\">\n" .
                "<p>Sorry, your browser cannot display frames!</p>\n" .
                "</iframe>\n" .
                "</div>\n";
        } else {


            if ($menu_item_show_page_heading) {
                echo '<h1>' . ($menu_item_title != '' ? ($menu_item_page_heading != '' ? $menu_item_page_heading : $menu_item_title) : $form->title) . '</h1>' . "\n";
            }

            // process inline
            $myUser = Factory::getUser();

            $database->setQuery("select id from #__users where lower(username)=lower('" . $myUser->get('username', '') . "')");
            $id = $database->loadResult();
            if ($id)
                $myUser->get('id', -1);
            require_once($ff_compath . '/facileforms.process.php');
            if ($task == 'view') {
                $div1style = '';
                $div2style = '';
                if ($form->template_code == '') {
                    $fullwidth = $form->widthmode && $form->width >= 100;
                    if ($form->widthmode) {
                        $div1style .= 'min-width:10px;';
                        $div2style .= 'min-width:10px;';
                    } // if
                    $div2style .= 'width:' . htmlentities(($fullwidth ? '100' : $form->width) . ($form->widthmode ? '%' : 'px'), ENT_QUOTES, 'UTF-8') . ';';
                    if (!$form->heightmode)
                        $div2style .= 'height:' . htmlentities($form->height, ENT_QUOTES, 'UTF-8') . 'px;';
                    if ($plainform) {
                        $div2style .= 'position:absolute;top:0px;left:0px;margin:0px;';
                    } else {
                        $div1style .= 'width:100%;';
                        $div2style .= 'position:relative;overflow:hidden;';
                        if ($border)
                            $div2style .= 'border:1px solid black;';
                        if (!$fullwidth) {
                            switch ($align) {
                                case 1:
                                    $div1style .= 'text-align:center;';
                                    $div2style .= 'text-align:left;margin-left:auto;margin-right:auto;';
                                    break;
                                case 2:
                                    $div1style .= 'text-align:right;';
                                    $div2style .= 'text-align:left;margin-left:auto;margin-right:0px;';
                                    break;
                                case 3:
                                    if ($left > 0)
                                        $div2style .= 'margin-left:' . htmlentities($left, ENT_QUOTES, 'UTF-8') . 'px;';
                                default:
                                    break;
                            } // switch
                        } // if
                        if ($top > 0)
                            $div2style .= 'margin-top:' . htmlentities($top, ENT_QUOTES, 'UTF-8') . 'px;';
                    } // if
                }
                ob_start();
                // DO NOT REMOVE OR CHANGE OR OTHERWISE MAKE INVISIBLE THE FOLLOWING COPYRIGHT MESSAGE
                // FAILURE TO COMPLY IS A DIRECT VIOLATION OF THE GNU GENERAL PUBLIC LICENSE
                // http://www.gnu.org/copyleft/gpl.html
                echo "\n<!-- BreezingForms V" . $ff_version . " Copyright(c) 2008-2013 by Markus Bopp | FacileForms Copyright 2004-2006 by Peter Koch, Chur, Switzerland.  All rights reserved. -->\n";
                // END OF COPYRIGHT
                $bfStyle = '';
                if ($form->template_code == '') {
                    $bfStyle = ' style="' . $div1style . '"';
                }
                if (!$plainform)
                    echo '<div class="bfClearfix"' . $bfStyle . '>' . "\n";
                if (trim($form->template_code_processed) == '') {
                    echo '<div class="bfClearfix" style="' . $div2style . '">' . "\n";
                }
            } // if task = view
            if ($left > 3)
                $align = $left;
            // remove temporary flash upload files if any
            $sourcePath = JPATH_SITE . '/components/com_breezingforms/uploads/';
            if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
                while (false !== ($file = @readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $parts = explode('_', $file);
                        if (count($parts) >= 5) {
                            if ($parts[count($parts) - 1] == 'flashtmp') {
                                if (@file_exists($sourcePath . $file) && @is_readable($sourcePath . $file)) {
                                    $fileCreationTime = @filectime($sourcePath . $file);
                                    $fileAge = time() - $fileCreationTime;
                                    if ($fileAge >= 86400) {
                                        @File::delete($sourcePath . $file);
                                    }
                                }
                            }
                        }
                    }
                }
                @closedir($handle);
            }
            // remove temporary chunked upload files if any
            $sourcePath = JPATH_SITE . '/components/com_breezingforms/uploads/chunks';
            if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
                while (false !== ($file = @readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $parts = explode('_', $file);
                        if (count($parts) >= 5) {
                            if ($parts[count($parts) - 1] == 'chunktmp') {
                                if (@file_exists($sourcePath . $file) && @is_readable($sourcePath . $file)) {
                                    $fileCreationTime = @filectime($sourcePath . $file);
                                    $fileAge = time() - $fileCreationTime;
                                    if ($fileAge >= 86400) {
                                        @File::delete($sourcePath . $file);
                                    }
                                }
                            }
                        }
                    }
                }
                @closedir($handle);
            }
            // purge payment cache
            $sourcePath = JPATH_SITE . '/media/breezingforms/payment_cache/';
            if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
                while (false !== ($file = @readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $parts = explode('_', $file);
                        if (count($parts) == 4) {
                            if (@file_exists($sourcePath . $file) && @is_readable($sourcePath . $file)) {
                                $fileCreationTime = @filectime($sourcePath . $file);
                                $fileAge = time() - $fileCreationTime;
                                if ($fileAge >= 86400) {
                                    @File::delete($sourcePath . $file);
                                }
                            }
                        }
                    }
                }
                @closedir($handle);
            }

            $ff_processor = new HTML_facileFormsProcessor(
                $runmode,
                $inframe,
                $form->id,
                $page,
                $border,
                $align,
                $top,
                $ff_target,
                $suffix,
                $editable,
                $editable_override
            );

            if ($task == 'submit') {
                $ff_processor->submit();
            } else {

                $ff_processor->view();

                if (trim($form->template_code_processed) == '') {
                    echo "</div>\n";
                }
                if (!$plainform)
                    echo "</div>\n";

                if ($runmode == _FF_RUNMODE_PREVIEW) {

                    $mouseOvers = '';
                    $draggableIds = '';
                    $draggableSize = count($ff_processor->draggableDivIds);
                    for ($x = 0; $x < $draggableSize; $x++) {
                        if ($x + 1 < $draggableSize) {
                            $draggableIds .= '"' . $ff_processor->draggableDivIds[$x] . '",';
                        } else {
                            $draggableIds .= '"' . $ff_processor->draggableDivIds[$x] . '"';
                        }

                        $mouseOvers .= '
						
							var ' . $ff_processor->draggableDivIds[$x] . '_paddingRTmp;
							var ' . $ff_processor->draggableDivIds[$x] . '_paddingLTmp;
							var ' . $ff_processor->draggableDivIds[$x] . '_colorTmp;
							
							' . $ff_processor->draggableDivIds[$x] . '_colorTmp    = document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.backgroundColor;
							' . $ff_processor->draggableDivIds[$x] . '_paddingRTmp = document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingRight;
							' . $ff_processor->draggableDivIds[$x] . '_paddingLTmp = document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingLeft;
							' . $ff_processor->draggableDivIds[$x] . '_paddingTTmp = document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingTop;
							' . $ff_processor->draggableDivIds[$x] . '_paddingBTmp = document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingBottom;
							
							function bfItemOver_' . $ff_processor->draggableDivIds[$x] . '(e){
								if(document.getElementById("' . $ff_processor->draggableDivIds[$x] . '")){
									
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.cursor="pointer";
								
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingRight = "10px";
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingLeft = "10px";
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingTop = "0px";
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingBottom = "0px";
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.backgroundColor = "red";
									
									parent.document.getElementById("hoverItem_' . $ff_processor->draggableDivIds[$x] . '").style.backgroundColor = "#cccccc";
								}
							}
						
							function bfItemOut_' . $ff_processor->draggableDivIds[$x] . '(e){
								if(document.getElementById("' . $ff_processor->draggableDivIds[$x] . '")){
								
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.cursor="";
								
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingRight= ' . $ff_processor->draggableDivIds[$x] . '_paddingRTmp;
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingLeft= ' . $ff_processor->draggableDivIds[$x] . '_paddingLTmp;
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingTop= ' . $ff_processor->draggableDivIds[$x] . '_paddingTTmp;
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.paddingBottom= ' . $ff_processor->draggableDivIds[$x] . '_paddingBTmp;
									document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").style.backgroundColor = ' . $ff_processor->draggableDivIds[$x] . '_colorTmp;
									
									parent.document.getElementById("hoverItem_' . $ff_processor->draggableDivIds[$x] . '").style.backgroundColor = "";
								}
							}
							
							if(document.getElementById("' . $ff_processor->draggableDivIds[$x] . '")){
								document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").onmouseover = bfItemOver_' . $ff_processor->draggableDivIds[$x] . ';
								document.getElementById("' . $ff_processor->draggableDivIds[$x] . '").onmouseout = bfItemOut_' . $ff_processor->draggableDivIds[$x] . ';
							}
						';
                    }

                    echo '
					<script>
					
					SET_DHTML(' . $draggableIds . ');
					
					' . $mouseOvers . '
					
					function my_DragFunc(){
						parent.document.adminForm.savepos.disabled = false;
						// TODO: when undo is enabled, drag and drop is not possible. needs to be solved
						//parent.document.adminForm.restpos.disabled = false;
					}
					
					function my_DropFunc(){
					
						parent.document.getElementById("ff_itemPositions").value = "";
					
						for(var i = 0; i < parent.ff_coords.length;i++){
						
							eval("var cb = parent.document.adminForm.cb"+i+";");
							
							var itemComma = "";
							if(i+1 < parent.ff_coords.length){
								itemComma = ",";
							} else {
								itemComma = "";
							}
							
							parent.document.getElementById("ff_itemPositions").value += 
								cb.value+":"+document.getElementById("ff_div"+cb.value).style.zIndex+itemComma;
						
						}
						
						for(var i = 0; i < parent.ff_coords.length;i++){
							
							eval("var cb = parent.document.adminForm.cb"+i+";");
							
							if(document.getElementById("ff_div"+cb.value) == document.getElementById(dd.obj.id)){
								
								parent.ff_coords[i][2] = dd.obj.x;
								parent.ff_coords[i][5] = dd.obj.y;
								break;
							}
						}
					}
					
					</script>';
                }

                ob_end_flush();
            } // if
        } // if
    } // if
} else if (BFRequest::getBool('checkCaptcha')) {

    @ob_end_clean();

    require_once(JPATH_SITE . '/components/com_breezingforms/images/captcha/securimage.php');
    $securimage = new Securimage();
    if (!$securimage->check(str_replace('?', '', BFRequest::getVar('value', '')))) {
        echo 'capResult=>false';
    } else {
        echo 'capResult=>true';
    }
    exit;
} else if (BFRequest::getBool('confirmPayPalIpn') && (!isset($ff_applic) || $ff_applic == '')) {

    BFRequest::setVar('format', 'html');

    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote(BFRequest::getInt('form_id', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        header("Status: 200 OK");
        exit;
    }

    $form = $list[0];

    $areas = Zend_Json::decode($form->template_areas);
    if (!is_array($areas)) {
        header("Status: 200 OK");
        exit;
    }

    foreach ($areas as $area) {

        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfPayPal') {

                $options = $element['options'];

                $auth_token = $options['token'];
                $paypal = 'https://www.paypal.com';
                if ($options['testaccount']) {
                    $paypal = 'https://www.sandbox.paypal.com';
                    $auth_token = $options['testToken'];
                }

                $req = 'cmd=_notify-validate';

                $tx_token = BFRequest::getVar('txn_id', 0);
                foreach ($_POST as $key => $value) {
                    $value = urlencode(stripslashes($value));
                    $req .= "&$key=$value";
                }

                $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

                $pointer = null;
                $res = '';

                if (function_exists('curl_init')) {
                    $ch = curl_init();
                    $pointer = $ch;
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_URL, $paypal . '/cgi-bin/webscr');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
                    curl_setopt($ch, CURLOPT_SSLVERSION, 6); //6 is for TLSV1.2

                    ob_start();
                    curl_exec($ch);
                    $res = ob_get_contents();
                } else {
                    // try fsockopen
                    $fp = fsockopen($paypal, 80, $errno, $errstr, 30);
                    $pointer = $fp;
                    fputs($fp, $header . $req);
                    $headerdone = false;
                    while (!feof($fp)) {
                        $line = fgets($fp, 1024);
                        if (strcmp($line, "\r\n") == 0) {
                            $headerdone = true;
                        } else if ($headerdone) {
                            $res .= $line;
                        }
                    }
                }

                $lines = explode("\n", $res);

                if (strcmp($lines[0], "VERIFIED") == 0) {

                    $query = "SELECT * FROM #__facileforms_records WHERE id = '" . BFRequest::getInt('record_id', -1) . "' LIMIT 1";
                    $db->setQuery($query);
                    $txid = $db->loadObjectList();

                    if (count($txid) != 0) {

                        if ($txid[0]->paypal_tx_id == '') {

                            $db->setQuery("
										Update
											#__facileforms_records
										Set
											paypal_tx_id = " . $db->Quote('PayPal: ' . $tx_token . ' (VALID)') . ",
											paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s')) . ",
											paypal_testaccount = " . $db->Quote($options['testaccount'] ? 1 : 0) . ",
											paypal_download_tries = 0
										Where
											id = '" . BFRequest::getInt('record_id', -1) . "'
											");

                            $db->execute();

                            // trigger a script after succeeded payment?
                            if (file_exists(JPATH_SITE . '/bf_paypalipn_success.php')) {
                                require_once(JPATH_SITE . '/bf_paypalipn_success.php');
                            }

                            // send mail after succeeded payment?
                            if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                                bf_sendNotificationByPaymentCache(BFRequest::getInt('form_id', -1), BFRequest::getInt('record_id', -1), 'admin');
                                bf_sendNotificationByPaymentCache(BFRequest::getInt('form_id', -1), BFRequest::getInt('record_id', -1), 'mailback');
                            }
                        }

                        header("Status: 200 OK");
                    }

                    header("Status: 200 OK");
                } else if (strcmp($lines[0], "INVALID") == 0) {

                    $query = "SELECT * FROM #__facileforms_records WHERE id = '" . BFRequest::getInt('record_id', -1) . "' LIMIT 1";
                    $db->setQuery($query);
                    $txid = $db->loadObjectList();

                    if (count($txid) != 0) {

                        $db->setQuery("
										Update
											#__facileforms_records
										Set
											paypal_tx_id = " . $db->Quote('PayPal: ' . $tx_token . ' (INVALID)') . ",
											paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s')) . ",
											paypal_testaccount = " . $db->Quote($options['testaccount'] ? 1 : 0) . ",
											paypal_download_tries = 0
										Where
											id = '" . BFRequest::getInt('record_id', -1) . "'
											");

                        $db->execute();
                    }

                    header("Status: 200 OK");
                }

                header("Status: 200 OK");

                // should be kept open until sending the status headers
                if (function_exists('curl_init')) {
                    curl_close($pointer);
                    ob_end_clean();
                } else {
                    fclose($pointer);
                }

                break;
            }
        }
    }
} else if (BFRequest::getBool('confirmStripe') && (!isset($ff_applic) || $ff_applic == '')) {

    BFRequest::setVar('format', 'html');

    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote(BFRequest::getInt('form_id', -1)));
    $list = $db->loadObjectList();

    if (count($list) == 0) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = Zend_Json::decode($form->template_areas);

    if (!is_array($areas)) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_STRIPE_DATA'));
        exit;
    }

    $tx_token = BFRequest::getVar('token');
    $record_id = BFRequest::getInt('record_id');

    foreach ($areas as $area) {

        foreach ($area['elements'] as $element) {

            if ($element['internalType'] == 'bfStripe') {

                $options = $element['options'];

                require_once JPATH_SITE . '/administrator/components/com_breezingforms/libraries/stripe/vendor/autoload.php';

                \Stripe\Stripe::setApiKey($options['secretKey']);
                $stripe = new \Stripe\StripeClient($options['secretKey']);

                // Create the charge on Stripe's servers - this will charge the user's card
                try {


                    $db->setQuery("
									Select paypal_tx_id From 
										#__facileforms_records 
									Where 
										id = '" . $record_id . "'
									And
										paypal_tx_id like 'Stripe:%'
									");

                    $exists = $db->loadResult();

                    if (!$exists) {

                        /* XDA if( Factory::getSession()->get('bf_stripe_last_payment_amount'.$record_id, null) == null ){

            BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_STRIPE_AMOUNT'));
            exit;
                                } XDA */

                        $stripearray = array();
                        $stripearray = [
                            "amount" => Factory::getSession()->get('bf_stripe_last_payment_amount' . $record_id, null),
                            // amount in cents, again
                            "currency" => strtolower($options['currencyCode']),
                            "source" => $tx_token,
                            "description" => $options['itemname'],
                            "metadata" => array()
                            //,"metadata" => array("Order ID" => $_session_cart['order_id'])
                        ];
                        if (Factory::getSession()->get('emailfield', '') !== '') {
                            $stripearray += ['receipt_email' => Factory::getSession()->get('emailfield', '')];
                            Factory::getSession()->clear('emailfield');
                        }
                        //$charge = \Stripe\Charge::create( $stripearray );
                        /*
                        $charge = \Stripe\Checkout\Session::create([
                                'customer_email' => 'bff@gmail.com',
                                'billing_address_collection' => 'required',
                                'line_items' => [[
                                  'price' => 'price_1JYA3UDkYxK6vMJ2QF2S6fNh',
                                  'quantity' => 1,
                                ]],
                                'payment_method_types' => [
                                  'card',
                                ],
                                'mode' => 'payment',
                                'success_url' => 'https://firadeldibuixilapintura.cat/test/success',
                                'cancel_url' => 'https://firadeldibuixilapintura.cat/test/cancel',
                          ]);*/


                        Factory::getSession()->clear('bf_stripe_last_payment_amount' . $record_id);
                    } else {

                        $exploded = explode(':', $exists);
                        //$charge = \Stripe\Charge::retrieve(trim($exploded[1]));
                    }

                    //                                      $tx_token = $charge->id;
                    $session_id = $_GET['session_id'];
                    $session = $stripe->checkout->sessions->retrieve(
                        "$session_id",
                        []
                    );

                    if ($session->payment_status != 'paid') {

                        //echo $_GET['session_id'].'<br>bf dont understand new stripe and says it was diclined lol: <br>'.var_dump($session);
                        $msg = Text::_("COM_BREEZINGFORMS_STRIPE_DECLINED");

                        require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                    } else {
                        /** get payment intend id and creation time */
                        $stripe_pi_id = $session->payment_intent; //payment intent id from last session also replacing tx_token/charge->id with paymentIntents->id
                        $stripe_pi = $stripe->paymentIntents->retrieve(
                            "$stripe_pi_id",
                            []
                        );
                        $stripe_pi_create = $stripe_pi->created; // replacing charge->created with paymentIntents->created
                        // XDA BEGIN
                        // Stripe Payment Intent updates to complete its description from pi_xxx to BF item Name - pi_xxx
                        $stripe->paymentIntents->update($stripe_pi_id, ['description' => $options['itemname']]);
                        // XDA END
                        $db->setQuery("
                                                                                Update 
                                                                                        #__facileforms_records 
                                                                                Set 
                                                                                        paypal_tx_id = " . $db->Quote('Stripe: ' . strip_tags($stripe_pi_id)) . ", 
                                                                                        paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s', $stripe_pi_create)) . ",
                                                                                        paypal_testaccount = " . $db->Quote(!$stripe_pi->livemode ? 1 : 0) . ",
                                                                                        paypal_download_tries = 0
                                                                                Where 
                                                                                        id = '" . BFRequest::getInt('record_id', -1) . "'
											");

                        $db->execute();

                        // trigger a script after succeeded payment?
                        if (file_exists(JPATH_SITE . '/bf_stripe_success.php')) {
                            require_once(JPATH_SITE . '/bf_stripe_success.php');
                        }

                        // send mail after succeeded payment?
                        if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                            bf_sendNotificationByPaymentCache(BFRequest::getInt('form_id', -1), BFRequest::getInt('record_id', -1), 'admin');
                            bf_sendNotificationByPaymentCache(BFRequest::getInt('form_id', -1), BFRequest::getInt('record_id', -1), 'mailback');
                        }

                        if ($options['downloadableFile']) {

                            $record_id = BFRequest::getInt('record_id', -1);
                            $tries = $options['downloadTries'];
                            $form_id = BFRequest::getInt('form_id', -1);
                            require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/stripe_download.php');
                        } else {

                            if ($options['thankYouPage'] != '') {
                                BFRedirect($options['thankYouPage']);
                            } else {
                                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_THANK_YOU_FOR_PAYING_WITH_STRIPE'));
                            }
                        }
                    }
                } catch (\Stripe\Error\Card $e) {

                    $msg = Text::_("COM_BREEZINGFORMS_STRIPE_DECLINED");
                    require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                }

                break;
            }
        }
    }
} else if (BFRequest::getBool('stripeDownload') && (!isset($ff_applic) || $ff_applic == '')) {

    BFRequest::setVar('format', 'raw');

    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote(BFRequest::getInt('form', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = Zend_Json::decode($form->template_areas);
    if (!is_array($areas)) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_PAYMENT_DATA'));
    }

    foreach ($areas as $area) {
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfStripe') {

                $options = $element['options'];

                if ($options['downloadableFile']) {

                    $file = $options['filepath'];

                    $db->setQuery("
									Select paypal_download_tries From 
										#__facileforms_records 
									Where 
										id = '" . BFRequest::getInt('record_id', -1) . "'
									And
										paypal_tx_id = " . $db->Quote('Stripe: ' . BFRequest::getVar('token', '')) . "
									");

                    $downloads = $db->loadObjectList();

                    if (count($downloads) == 1) {

                        if ($downloads[0]->paypal_download_tries < $options['downloadTries']) {

                            $db->setQuery("
											Update 
												#__facileforms_records 
											Set
												paypal_download_tries = paypal_download_tries + 1 
											Where 
												id = '" . BFRequest::getInt('record_id', -1) . "'
											And
												paypal_tx_id = " . $db->Quote('Stripe: ' . BFRequest::getVar('token', '')) . "
											");

                            $db->execute();

                            if (!file_exists($file)) {
                                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_DOWNLOAD_FILE'));
                            }

                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename=' . basename($file));
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($file));
                            ob_clean();
                            flush();
                            readfile($file) or die("Error reading the file " . $file);
                            exit;
                        } else {

                            BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_MAX_DOWNLOAD_TRIES_REACHED'));
                        }
                    } else {

                        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_DOWNLOAD_NOT_POSSIBLE'));
                    }
                } else {

                    BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_NO_DOWNLOADABLE_PRODUCT'));
                }

                break;
            }
        }
    }
} else if (BFRequest::getBool('confirmPayPal') && (!isset($ff_applic) || $ff_applic == '')) {

    BFRequest::setVar('format', 'html');

    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote(BFRequest::getInt('form_id', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = Zend_Json::decode($form->template_areas);
    if (!is_array($areas)) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_PAYPAL_DATA'));
        exit;
    }

    foreach ($areas as $area) {
        $checkPP = true;
        foreach ($area['elements'] as $element) {
            if ($element['name'] == 'PayPalSelect' || $element['name'] == 'BfPaymentSelect') {
                $checkPP = false;
                break;
            }
        }
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfPayPal') {

                $options = $element['options'];

                $auth_token = $options['token'];
                $paypal = 'https://www.paypal.com';
                if ($options['testaccount']) {
                    $paypal = 'https://www.sandbox.paypal.com';
                    $auth_token = $options['testToken'];
                }

                $req = 'cmd=_notify-synch';

                $tx_token = BFRequest::getVar('tx', 0);
                $req .= "&tx=" . urlencode($tx_token) . "&at=" . urlencode($auth_token);

                $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

                if (function_exists('curl_init')) {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_URL, $paypal . '/cgi-bin/webscr');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
                    curl_setopt($ch, CURLOPT_SSLVERSION, 6); //6 is for TLSV1.2

                    ob_start();
                    curl_exec($ch);
                    $res = ob_get_contents();
                    curl_close($ch);
                    ob_end_clean();
                } else {
                    // try fsockopen
                    $fp = fsockopen($paypal, 80, $errno, $errstr, 30);
                    fputs($fp, $header . $req);
                    $res = '';
                    $headerdone = false;
                    while (!feof($fp)) {
                        $line = fgets($fp, 1024);
                        if (strcmp($line, "\r\n") == 0) {
                            $headerdone = true;
                        } else if ($headerdone) {
                            $res .= $line;
                        }
                    }
                    fclose($fp);
                }

                $lines = explode("\n", $res);
                $keyarray = array();

                if (strcmp($lines[0], "SUCCESS") == 0) {
                    for ($i = 1; $i < count($lines); $i++) {
                        if ($lines[$i] != "") {
                            list($key, $val) = explode("=", $lines[$i]);
                            $keyarray[urldecode($key)] = urldecode($val);
                        }
                    }

                    if ($checkPP && (($options['amount'] > 0 && $keyarray['mc_gross'] != (doubleval($options['amount']) + doubleval($options['tax']))) || $keyarray['mc_currency'] != strtoupper($options['currencyCode']))) {

                        $success = false;
                        $msg = Text::_("Payment was not correct (amount/currency)");
                        require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                    } else {

                        $query = "SELECT * FROM #__facileforms_records WHERE id = '" . BFRequest::getInt('record_id', -1) . "' LIMIT 1";
                        $db->setQuery($query);
                        $txid = $db->loadObjectList();

                        if (count($txid) != 0) {

                            if ($txid[0]->paypal_tx_id == '') {

                                $db->setQuery("
										Update 
											#__facileforms_records 
										Set 
											paypal_tx_id = " . $db->Quote('PayPal: ' . $tx_token) . ", 
											paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s', strtotime($keyarray["payment_date"]))) . ",
											paypal_testaccount = " . $db->Quote($options['testaccount'] ? 1 : 0) . ",
											paypal_download_tries = 0
										Where 
											id = '" . BFRequest::getInt('record_id', -1) . "'
											");

                                $db->execute();

                                // trigger a script after succeeded payment?
                                if (file_exists(JPATH_SITE . '/bf_paypal_success.php')) {
                                    require_once(JPATH_SITE . '/bf_paypal_success.php');
                                }

                                // send mail after succeeded payment?
                                if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                                    bf_sendNotificationByPaymentCache(BFRequest::getInt('form_id', -1), BFRequest::getInt('record_id', -1), 'admin');
                                    bf_sendNotificationByPaymentCache(BFRequest::getInt('form_id', -1), BFRequest::getInt('record_id', -1), 'mailback');
                                }

                                if ($options['downloadableFile']) {

                                    $record_id = BFRequest::getInt('record_id', -1);
                                    $tries = $options['downloadTries'];
                                    $form_id = BFRequest::getInt('form_id', -1);
                                    require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/download.php');
                                } else {

                                    if ($options['thankYouPage'] != '') {
                                        BFRedirect($options['thankYouPage']);
                                    } else {
                                        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_THANK_YOU_FOR_PAYING_WITH_PAYPAL'));
                                    }
                                }

                                $success = true;
                            } else {
                                if ($options['downloadableFile']) {

                                    $record_id = BFRequest::getInt('record_id', -1);
                                    $tries = $options['downloadTries'];
                                    $form_id = BFRequest::getInt('form_id', -1);
                                    require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/download.php');
                                } else {
                                    if ($options['useIpn']) {
                                        if ($options['thankYouPage'] != '') {
                                            BFRedirect($options['thankYouPage']);
                                        } else {
                                            BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_THANK_YOU_FOR_PAYING_WITH_PAYPAL'));
                                        }
                                    } else {
                                        $success = false;
                                        $msg = Text::_("This transaction was already processed");
                                        require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                                    }
                                }
                            }
                        } else {
                            $success = false;
                            $msg = Text::_("Could not find record!");
                            require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                        }
                    }
                } else if (strcmp($lines[0], "FAIL") == 0) {
                    $success = false;
                    $msg = Text::_("Verification failed");
                    require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                } else {
                    $success = false;
                    $msg = Text::_("Verification did not return any values");
                    require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                }

                break;
            }
        }
    }
} else if (BFRequest::getBool('paypalDownload') && (!isset($ff_applic) || $ff_applic == '')) {

    BFRequest::setVar('format', 'raw');

    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote(BFRequest::getInt('form', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = Zend_Json::decode($form->template_areas);
    if (!is_array($areas)) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_PAYPAL_DATA'));
    }

    foreach ($areas as $area) {
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfPayPal') {

                $options = $element['options'];

                if ($options['downloadableFile']) {

                    $file = $options['filepath'];

                    $db->setQuery("
									Select paypal_download_tries From 
										#__facileforms_records 
									Where 
										id = '" . BFRequest::getInt('record_id', -1) . "'
									And
										( 
                                                                                    paypal_tx_id = " . $db->Quote('PayPal: ' . BFRequest::getVar('tx', '')) . "
                                                                                  Or
                                                                                    paypal_tx_id = " . $db->Quote('PayPal: ' . BFRequest::getVar('tx', '') . ' (VALID)') . "
                                                                                )
									");

                    $downloads = $db->loadObjectList();

                    if (count($downloads) == 1) {

                        if ($downloads[0]->paypal_download_tries < $options['downloadTries']) {

                            $db->setQuery("
											Update 
												#__facileforms_records 
											Set
												paypal_download_tries = paypal_download_tries + 1 
											Where 
												id = '" . BFRequest::getInt('record_id', -1) . "'
											And
												(
                                                                                                    paypal_tx_id = " . $db->Quote('PayPal: ' . BFRequest::getVar('tx', '')) . "
                                                                                                  Or
                                                                                                    paypal_tx_id = " . $db->Quote('PayPal: ' . BFRequest::getVar('tx', '') . ' (VALID)') . "
                                                                                                )
											");

                            $db->execute();

                            if (!file_exists($file)) {
                                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_DOWNLOAD_FILE'));
                            }

                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename=' . basename($file));
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($file));
                            ob_clean();
                            flush();
                            readfile($file) or die("Error reading the file " . $file);
                            exit;
                        } else {

                            BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_MAX_DOWNLOAD_TRIES_REACHED'));
                        }
                    } else {

                        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_DOWNLOAD_NOT_POSSIBLE'));
                    }
                } else {

                    BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_NO_DOWNLOADABLE_PRODUCT'));
                }

                break;
            }
        }
    }
} else if (BFRequest::getBool('showPayPalConnectMsg')) {

    BFRequest::setVar('format', 'html');

    $style = '<link rel="stylesheet" href="' . Uri::root() . 'templates/' . $mainframe->getTemplate() . '/css/template.css" type="text/css" />';

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . strtolower(Factory::getApplication()->getLanguage()->getTag()) . '" lang="' . strtolower(FFFFactorypplication()->getLanguage()->getTag()) . '" >
<head>' . $style . '</head>
<div class="payPalConnectMsg">
<div class="paymentConnectMsg">
' . BFText::_('COM_BREEZINGFORMS_PLEASE_WAIT_REQUEST') . '
</div>
</div>
</body>';
} else if (BFRequest::getBool('successSofortueberweisung')) {

    BFRequest::setVar('format', 'html');

    $tx_token = BFRequest::getVar('tx', '');
    if ($tx_token == '') {
        $msg = Text::_("This transaction id is empty!");
        require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
    } else {

        $formId = BFRequest::getInt('user_variable_0', '');
        $recordId = BFRequest::getInt('user_variable_1', '');

        if ($formId != '' && $recordId != '') {

            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

            $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote($formId));
            $list = $db->loadObjectList();
            if (count($list) == 0) {
                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_FORM_DOES_NOT_EXIST'));
                exit;
            }

            $form = $list[0];

            $areas = Zend_Json::decode($form->template_areas);
            if (!is_array($areas)) {
                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_SU_DATA'));
            }

            foreach ($areas as $area) {
                foreach ($area['elements'] as $element) {
                    if ($element['internalType'] == 'bfSofortueberweisung') {
                        $options = $element['options'];
                        if ($options['downloadableFile']) {
                            $tx_token = BFRequest::getVar('tx', '');
                            $tries = $options['downloadTries'];

                            $db->setQuery("
									Select paypal_download_tries From 
										#__facileforms_records 
									Where 
										id = '" . $recordId . "'
									And
										paypal_tx_id = " . $db->Quote('Sofortüberweisung: ' . BFRequest::getVar('tx', '')) . "
									");

                            $downloads = $db->loadObjectList();

                            $confirmed = false;
                            if (count($downloads) == 1) {
                                $confirmed = true;
                            }

                            require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/sofort_download.php');
                        } else {
                            if ($options['thankYouPage'] != '') {
                                BFRedirect($options['thankYouPage']);
                            } else {
                                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_THANK_YOU_FOR_PAYING_WITH_SU'));
                            }
                        }

                        break;
                    }
                }
            }
        } else {
            $msg = Text::_("COM_BREEZINGFORMS_MISSING_PAYMENT_INFORMATION");
            $tx_token = Text::_("COM_BREEZINGFORMS_NOT_AVAILABLE");
            if (BFRequest::getVar('tx', '') != '') {
                $tx_token = BFRequest::getVar('tx', '');
            }
            require_once(JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
        }
    }
} else if (BFRequest::getBool('confirmSofortueberweisung')) {

    BFRequest::setVar('format', 'raw');

    $formId = BFRequest::getInt('user_variable_0', -1);
    $recordId = BFRequest::getInt('user_variable_1', -1);

    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote($formId));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        exit;
    }

    $form = $list[0];

    $areas = Zend_Json::decode($form->template_areas);
    if (!is_array($areas)) {
        exit;
    }

    foreach ($areas as $area) {
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfSofortueberweisung') {

                $options = $element['options'];

                $data = array(
                    'transaction' => BFRequest::getVar('transaction', ''),
                    'user_id' => BFRequest::getVar('user_id', ''),
                    'project_id' => BFRequest::getVar('project_id', ''),
                    'sender_holder' => BFRequest::getVar('sender_holder', ''),
                    'sender_account_number' => BFRequest::getVar('sender_account_number', ''),
                    'sender_bank_code' => BFRequest::getVar('sender_bank_code', ''),
                    'sender_bank_name' => BFRequest::getVar('sender_bank_name', ''),
                    'sender_bank_bic' => BFRequest::getVar('sender_bank_bic', ''),
                    'sender_iban' => BFRequest::getVar('sender_iban', ''),
                    'sender_country_id' => BFRequest::getVar('sender_country_id', ''),
                    'recipient_holder' => BFRequest::getVar('recipient_holder', ''),
                    'recipient_account_number' => BFRequest::getVar('recipient_account_number', ''),
                    'recipient_bank_code' => BFRequest::getVar('recipient_bank_code', ''),
                    'recipient_bank_name' => BFRequest::getVar('recipient_bank_name', ''),
                    'recipient_bank_bic' => BFRequest::getVar('recipient_bank_bic', ''),
                    'recipient_iban' => BFRequest::getVar('recipient_iban', ''),
                    'recipient_country_id' => BFRequest::getVar('recipient_country_id', ''),
                    'international_transaction' => BFRequest::getVar('international_transaction', ''),
                    'amount' => BFRequest::getVar('amount', ''),
                    'currency_id' => BFRequest::getVar('currency_id', ''),
                    'reason_1' => BFRequest::getVar('reason_1', ''),
                    'reason_2' => BFRequest::getVar('reason_2', ''),
                    'security_criteria' => BFRequest::getVar('security_criteria', ''),
                    'user_variable_0' => BFRequest::getVar('user_variable_0', ''),
                    'user_variable_1' => BFRequest::getVar('user_variable_1', ''),
                    'user_variable_2' => BFRequest::getVar('user_variable_2', ''),
                    'user_variable_3' => BFRequest::getVar('user_variable_3', ''),
                    'user_variable_4' => BFRequest::getVar('user_variable_4', ''),
                    'user_variable_5' => BFRequest::getVar('user_variable_5', ''),
                    'created' => BFRequest::getVar('created', ''),
                    'project_password' => $options['project_password']
                );

                $data_implode = implode('|', $data);
                $hash = sha1($data_implode);

                $query = "SELECT * FROM #__facileforms_records WHERE id = '" . $recordId . "' And paypal_tx_id = '' LIMIT 1";
                $db->setQuery($query);
                $txid = $db->loadObjectList();

                if ($hash == BFRequest::getVar('hash', '')) {

                    if (count($txid) != 0) {

                        if ($txid[0]->paypal_tx_id == '') {

                            $db->setQuery("
										Update 
											#__facileforms_records 
										Set 
											paypal_tx_id = " . $db->Quote('Sofortüberweisung: ' . BFRequest::getVar('transaction', '')) . ", 
											paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s', strtotime(BFRequest::getVar('created', '')))) . ",
											paypal_testaccount = 0,
											paypal_download_tries = 0
										Where 
											id = '" . $recordId . "'
											");

                            $db->execute();

                            $recipients = explode('###', BFRequest::getVar('user_variable_2', ''));
                            $recipientsSize = count($recipients);
                            $mailer = Factory::getMailer();
                            $mailer->Subject = BFText::_('COM_BREEZINGFORMS_YOUR_PAYMENT_AT_SU');
                            $mailer->Body = BFText::_('COM_BREEZINGFORMS_HALLO') . "\n\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_YOUR_PAYMENT_SUCCEEDED') . "\n\n";
                            $mailer->Body .= '--------------------------------------' . "\n\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_REASON1') . ': ' . BFRequest::getVar('reason_1', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_REASON2') . ': ' . BFRequest::getVar('reason_2', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_AMOUNT') . ': ' . str_replace('.', ',', BFRequest::getVar('amount', '')) . ' ' . BFRequest::getVar('currency_id', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_TRANSACTION') . ': ' . BFRequest::getVar('transaction', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_ACCOUNT_HOLDER') . ': ' . BFRequest::getVar('sender_holder', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_ACCOUNT_NUMBER') . ': ' . BFRequest::getVar('sender_account_number', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_BANK_CODE') . ': ' . BFRequest::getVar('recipient_bank_code', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_BANK_NAME') . ': ' . BFRequest::getVar('sender_bank_name', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_BIC') . ': ' . BFRequest::getVar('sender_bank_bic', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_IBAN') . ': ' . BFRequest::getVar('sender_iban', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_PAYMENT_DATE') . ': ' . BFRequest::getVar('created', '') . "\n\n";

                            $mailer->Body .= '--------------------------------------' . "\n\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_RECEIPT_FOR_YOUR_PAYMENT') . "\n\n";
                            $mailer->Body .= '--------------------------------------' . "\n\n";

                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_ACCOUNT_HOLDER') . ': ' . BFRequest::getVar('recipient_holder', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_ACCOUNT_NUMBER') . ': ' . BFRequest::getVar('recipient_account_number', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_BANK_CODE') . ': ' . BFRequest::getVar('recipient_bank_code', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_BANK_NAME') . ': ' . BFRequest::getVar('recipient_bank_name', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_BIC') . ': ' . BFRequest::getVar('recipient_bank_bic', '') . "\n";
                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_IBAN') . ': ' . BFRequest::getVar('recipient_iban', '') . "\n\n";

                            $mailer->Body .= '--------------------------------------' . "\n\n";

                            $mailer->Body .= BFText::_('COM_BREEZINGFORMS_PAYMENT_GATEWAY_SU');

                            for ($i = 0; $i < $recipientsSize; $i++) {
                                if (bf_is_email($recipients[$i])) {
                                    $mailer->AddAddress($recipients[$i]);
                                    $mailer->Send();
                                }
                            }

                            // trigger a script after succeeded payment?
                            if (file_exists(JPATH_SITE . '/bf_sofortueberweisung_success.php')) {
                                require_once(JPATH_SITE . '/bf_sofortueberweisung_success.php');
                            }

                            // send mail after succeeded payment?
                            if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                                bf_sendNotificationByPaymentCache($formId, $recordId, 'admin');
                                bf_sendNotificationByPaymentCache($formId, $recordId, 'mailback');
                            }
                        }
                    }
                }

                break;
            }
        }
    }
} else if (BFRequest::getBool('sofortueberweisungDownload') && (!isset($ff_applic) || $ff_applic == '')) {

    BFRequest::setVar('format', 'raw');

    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote(BFRequest::getInt('form', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = Zend_Json::decode($form->template_areas);
    if (!is_array($areas)) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_PAYMENT_DATA'));
    }

    foreach ($areas as $area) {
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfSofortueberweisung') {

                $options = $element['options'];

                if ($options['downloadableFile']) {

                    $file = $options['filepath'];

                    $db->setQuery("
									Select paypal_download_tries From 
										#__facileforms_records 
									Where 
										id = '" . BFRequest::getInt('record_id', -1) . "'
									And
										paypal_tx_id = " . $db->Quote('Sofortüberweisung: ' . BFRequest::getVar('tx', '')) . "
									");

                    $downloads = $db->loadObjectList();

                    if (count($downloads) == 1) {

                        if ($downloads[0]->paypal_download_tries < $options['downloadTries']) {

                            $db->setQuery("
											Update 
												#__facileforms_records 
											Set
												paypal_download_tries = paypal_download_tries + 1 
											Where 
												id = '" . BFRequest::getInt('record_id', -1) . "'
											And
												paypal_tx_id = " . $db->Quote('Sofortüberweisung: ' . BFRequest::getVar('tx', '')) . "
											");

                            $db->execute();

                            if (!file_exists($file)) {
                                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_COULD_NOT_FIND_DOWNLOAD_FILE'));
                            }

                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename=' . basename($file));
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($file));
                            ob_clean();
                            flush();
                            readfile($file) or die("Error reading the file " . $file);
                            exit;
                        } else {

                            BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_MAX_DOWNLOAD_TRIES_REACHED'));
                        }
                    } else {

                        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_DOWNLOAD_NOT_POSSIBLE'));
                    }
                } else {

                    BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMS_NO_DOWNLOADABLE_PRODUCT'));
                }

                break;
            }
        }
    }
} else if (BFRequest::getBool('flashUpload')) {

    function bfProcess(&$dataObject, $finaltargetFile, $parent = null, $index = 0, $childrenLength = 0)
    {
        $mdata = $dataObject['properties'];
        if ($mdata['type'] == 'element') {
            switch ($mdata['bfType']) {
                case 'bfFile':
                    if (isset($mdata['flashUploaderBytes']) && intval($mdata['flashUploaderBytes']) > 0 && isset($mdata['bfName']) && trim($mdata['bfName']) == trim(BFRequest::getVar('itemName', ''))) {
                        if (file_exists($finaltargetFile) && @filesize($finaltargetFile) > intval($mdata['flashUploaderBytes'])) {
                            @File::delete($finaltargetFile);
                            echo trim($mdata['label']) . ': ' . BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_TOO_LARGE');
                            exit;
                        }
                        break;
                    }
                    break;
            }
        }
        if (isset($dataObject['children']) && count($dataObject['children']) != 0) {
            $childrenAmount = count($dataObject['children']);
            for ($i = 0; $i < $childrenAmount; $i++) {
                bfProcess($dataObject['children'][$i], $finaltargetFile, $mdata, $i, $childrenAmount);
            }
        }
    }

    @ob_end_clean();
    if (is_numeric(BFRequest::getVar('form', '')) && !empty($_FILES) && BFRequest::getVar('bfFlashUploadTicket', '') != '') {

        $db->setQuery("Select form.id, form.template_code_processed, form.template_code From #__facileforms_forms as form, #__facileforms_elements as element Where form.id = " . $db->Quote(BFRequest::getInt('form', -1)) . " And element.name = " . $db->Quote(BFRequest::getVar('itemName', '')) . " And element.form = " . $db->Quote(BFRequest::getInt('form', -1)));
        $objectList = $db->loadObjectList();
        $formIdCount = count($objectList);
        if ($formIdCount > 0) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = JPATH_SITE . '/components/com_breezingforms/uploads/';
            if (@file_exists($targetPath) && @is_dir($targetPath)) {
                $secureTicket = Factory::getSession()->get('secure_ticket', '', 'com_breezingforms');
                if ($secureTicket == '') {
                    mt_srand();
                    $secureTicket = md5(strtotime('now') . mt_rand(0, mt_getrandmax()));
                    Factory::getSession()->set('secure_ticket', $secureTicket, 'com_breezingforms');
                }

                $targetFile = str_replace('//', '/', $targetPath) . 'chunks' . DS . BFRequest::getInt('offset', 0) . '_' . bf_sanitizeFilename(BFRequest::getVar('name', 'unknown')) . '_' . BFRequest::getVar('itemName', '') . '_' . BFRequest::getVar('bfFlashUploadTicket') . '_' . $secureTicket . '_chunktmp';
                $finaltargetFile = str_replace('//', '/', $targetPath) . bf_sanitizeFilename(BFRequest::getVar('name', 'unknown')) . '_' . BFRequest::getVar('itemName', '') . '_' . BFRequest::getVar('bfFlashUploadTicket') . '_' . $secureTicket . '_flashtmp';

                if (@File::upload($tempFile, $targetFile)) {

                    $chunky = @BFFile::read($targetFile);

                    // ok, here we try native PHP file operation
                    // to prevent opening and readin the file
                    if (@is_writable(str_replace('//', '/', $targetPath))) {
                        $fp = @fopen($finaltargetFile, 'ab');
                        @fwrite($fp, $chunky);
                        @fclose($fp);
                    } else {
                        // as last resort, we use the
                        // joomla api that uses FTP if possible
                        // and if the folder is not writable
                        // and hope the file is not exceeding the
                        // php memory limit
                        $final = '';
                        if (@file_exists($finaltargetFile)) {
                            $final = @BFFile::read($finaltargetFile);
                        }
                        $newbuf = $final . $chunky;
                        @File::write($finaltargetFile, $newbuf);
                    }

                    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
                    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
                    $dataObject = Zend_Json::decode(bf_b64dec($objectList[0]->template_code));

                    bfProcess($dataObject, $finaltargetFile);
                    @File::delete($targetFile);
                } else {
                    echo 'Could not upload file ' . addslashes($_FILES['Filedata']['name']) . '!';
                }
            } else {
                echo 'Invalid file storage path for file ' . addslashes($_FILES['Filedata']['name']) . '! Please check the upload folder path and its permissions!';
            }
        } else {
            echo 'Form id and element do not match!';
        }
    }
    exit;
} else if (BFRequest::getVar('opt_in') == 'true') {

    // DOUBLE OPT IN

    jimport('joomla.html.html');

    $jinput = Factory::getApplication()->input;
    $ip = $jinput->server->get('REMOTE_ADDR');

    $userSubmitedID = BFRequest::getVar('id');
    $token = BFRequest::getVar('token');
    $database->setQuery("UPDATE #__facileforms_records SET opted=1, opt_ip = " . $database->quote($ip) . ", opt_date = " . $database->quote(JHtml::date('now', 'Y-m-d H:i:s')) . " WHERE opt_token = " . $database->quote($token) . " And id=" . $database->quote($userSubmitedID) . " And opted = 0");
    $database->execute();

    echo Text::_("COM_BREEZINGFORMS_FORMS_DOUBLE_OPT_EMAIL_THANK_YOU");

    // DOUBLE OPT IN END
} else if (BFRequest::getVar('opt_out') == 'true') {

    jimport('joomla.html.html');

    $jinput = Factory::getApplication()->input;
    $ip = $jinput->server->get('REMOTE_ADDR');

    $userSubmitedID = BFRequest::getVar('id');
    $token = BFRequest::getVar('token');
    $database->setQuery("UPDATE #__facileforms_records SET opted=0, opt_ip = " . $database->quote($ip) . ", opt_date = " . $database->quote(JHtml::date('now', 'Y-m-d H:i:s')) . " WHERE opt_token = " . $database->quote($token) . " And id=" . $database->quote($userSubmitedID) . " And opted = 1");
    $database->execute();

    echo Text::_("COM_BREEZINGFORMS_FORMS_DOUBLE_OPT_OUT_EMAIL_THANK_YOU");
}

if (BFRequest::getBool('raw', false)) {
    session_write_close();
    exit;
}

$cache->setCaching(true);
