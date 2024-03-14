<?php

/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 * */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\Filesystem\Path;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

class bfMobile
{
    public $isMobile = false;
}

$mainframe = Factory::getApplication();

$ff_processor = null;

define('_FF_PACKBREAKAFTER', 250);

define('_FF_STATUS_OK', 0);
define('_FF_STATUS_UNPUBLISHED', 1);
define('_FF_STATUS_SAVERECORD_FAILED', 2);
define('_FF_STATUS_SAVESUBRECORD_FAILED', 3);
define('_FF_STATUS_UPLOAD_FAILED', 4);
define('_FF_STATUS_SENDMAIL_FAILED', 5);
define('_FF_STATUS_ATTACHMENT_FAILED', 6);
define('_FF_STATUS_CAPTCHA_FAILED', 7);
define('_FF_STATUS_FILE_EXTENSION_NOT_ALLOWED', 8);
define('_FF_STATUS_SALESFORCE_SOAP_ERROR', 9);

define('_FF_DATA_ID', 0);
define('_FF_DATA_NAME', 1);
define('_FF_DATA_TITLE', 2);
define('_FF_DATA_TYPE', 3);
define('_FF_DATA_VALUE', 4);
define('_FF_DATA_FILE_SERVERPATH', 5);

define('_FF_IGNORE_STRICT', 1);
define('_FF_TRACE_NAMELIMIT', 100);

// tracemode bits
define('_FF_TRACEMODE_EVAL', 8);
define('_FF_TRACEMODE_PIECE', 16);
define('_FF_TRACEMODE_FUNCTION', 32);
define('_FF_TRACEMODE_MESSAGE', 64);
define('_FF_TRACEMODE_LOCAL', 128);
define('_FF_TRACEMODE_DIRECT', 256);
define('_FF_TRACEMODE_APPEND', 512);
define('_FF_TRACEMODE_DISABLE', 1024);
define('_FF_TRACEMODE_FIRST', 2048);

// tracemode masks
define('_FF_TRACEMODE_PRIORITY', 7);
define('_FF_TRACEMODE_TOPIC', 120);
define('_FF_TRACEMODE_VARIABLE', 248);

// debugging flags
define('_FF_DEBUG_PATCHEDCODE', 1);
define('_FF_DEBUG_ENTER', 2);
define('_FF_DEBUG_EXIT', 4);
define('_FF_DEBUG_DIRECTIVE', 8);
define('_FF_DEBUG', 0);

function ff_trace($msg = null)
{
    global $ff_processor;

    if (
        $ff_processor->dying ||
        ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE) ||
        !($ff_processor->traceMode & _FF_TRACEMODE_MESSAGE)
    )
        return;
    $level = count($ff_processor->traceStack);
    $trc = '';
    for ($l = 0; $l < $level; $l++)
        $trc .= '  ';
    $trc .= BFText::_('COM_BREEZINGFORMS_PROCESS_MSGUNKNOWN') . ": $msg\n";
    $ff_processor->traceBuffer .= htmlspecialchars($trc, ENT_QUOTES);
    if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
        $ff_processor->dumpTrace();
}

// ff_trace

function _ff_trace($line, $msg = null)
{
    global $ff_processor;

    // version for patched code
    if ($ff_processor->dying || ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE))
        return;
    $level = count($ff_processor->traceStack);
    if ($msg && ($ff_processor->traceMode & _FF_TRACEMODE_MESSAGE)) {
        $trc = '';
        for ($l = 0; $l < $level; $l++)
            $trc .= '  ';
        $trc .= BFText::_('COM_BREEZINGFORMS_PROCESS_LINE') . " $line: $msg\n";
        $ff_processor->traceBuffer .= htmlspecialchars($trc, ENT_QUOTES);
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
    } // if
    if ($level)
        $ff_processor->traceStack[$level - 1][3] = $line;
}

// _ff_trace

function _ff_getMode(&$newmode, &$name)
{
    global $ff_processor;

    $oldmode = $ff_processor->traceMode;
    if (_FF_DEBUG & _FF_DEBUG_ENTER)
        $ff_processor->traceBuffer .= htmlspecialchars(
            "\n_FF_DEBUG_ENTER:" .
            "\n  Name              = $name" .
            "\n  Old mode before   = " . $ff_processor->dispTraceMode($oldmode) .
            "\n  New mode before   = " . $ff_processor->dispTraceMode($newmode),
            ENT_QUOTES
        );
    if (is_null($newmode) || ($newmode & _FF_TRACEMODE_PRIORITY) < ($oldmode & _FF_TRACEMODE_PRIORITY)) {
        $newmode = $oldmode;
        $ret = $oldmode;
    } else {
        $newmode = ($oldmode & ~_FF_TRACEMODE_VARIABLE) | ($newmode & _FF_TRACEMODE_VARIABLE);
        if ($oldmode != $newmode)
            $ff_processor->traceMode = $newmode;
        $ret = ($newmode & _FF_TRACEMODE_LOCAL) ? $oldmode : $newmode;
    } // if
    if (_FF_DEBUG & _FF_DEBUG_ENTER) {
        $ff_processor->traceBuffer .= htmlspecialchars(
            "\n  Old mode compiled = " . $ff_processor->dispTraceMode($ret) .
            "\n  New mode compiled = " . $ff_processor->dispTraceMode($newmode) .
            "\n",
            ENT_QUOTES
        );
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
    } // if
    return $ret;
}

// _ff_getmode

function _ff_tracePiece($newmode, $name, $line, $type, $id, $pane)
{
    global $ff_processor;

    if ($ff_processor->dying || ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE))
        return;
    $oldmode = _ff_getMode($newmode, $name);
    if ($newmode & _FF_TRACEMODE_PIECE) {
        $level = count($ff_processor->traceStack);
        for ($l = 0; $l < $level; $l++)
            $ff_processor->traceBuffer .= '  ';
        $ff_processor->traceBuffer .= htmlspecialchars(
            "+" . BFText::_('COM_BREEZINGFORMS_PROCESS_ENTER') . " $name " . BFText::_('COM_BREEZINGFORMS_PROCESS_ATLINE') . " $line\n",
            ENT_QUOTES
        );
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
    } // if
    array_push($ff_processor->traceStack, array($oldmode, 'p', $name, $line, $type, $id, $pane));
}

// _ff_tracePiece

function _ff_traceFunction($newmode, $name, $line, $type, $id, $pane, &$args)
{
    global $ff_processor;

    if ($ff_processor->dying || ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE))
        return;
    $oldmode = _ff_getMode($newmode, $name);
    if ($newmode & _FF_TRACEMODE_FUNCTION) {
        $level = count($ff_processor->traceStack);
        $trc = '';
        for ($l = 0; $l < $level; $l++)
            $trc .= '  ';
        $trc .= "+" . BFText::_('COM_BREEZINGFORMS_PROCESS_ENTER') . " $name(";
        if ($args) {
            $next = false;
            foreach ($args as $arg) {
                if ($next)
                    $trc .= ', ';
                else
                    $next = true;
                if (is_null($arg))
                    $trc .= 'null';
                else
                    if (is_bool($arg)) {
                        $trc .= $arg ? 'true' : 'false';
                    } else
                        if (is_numeric($arg))
                            $trc .= $arg;
                        else
                            if (is_string($arg)) {
                                $arg = preg_replace('/([\\s]+)/si', ' ', $arg);
                                if (strlen($arg) > _FF_TRACE_NAMELIMIT)
                                    $arg = substr($arg, 0, _FF_TRACE_NAMELIMIT - 3) . '...';
                                $trc .= "'$arg'";
                            } else
                                if (is_array($arg))
                                    $trc .= BFText::_('COM_BREEZINGFORMS_PROCESS_ARRAY');
                                else
                                    if (is_object($arg))
                                        $trc .= BFText::_('COM_BREEZINGFORMS_PROCESS_OBJECT');
                                    else
                                        if (is_resource($arg))
                                            $trc .= BFText::_('COM_BREEZINGFORMS_PROCESS_RESOURCE');
                                        else
                                            $trc .= _FACILEFORMS_PROCESS_UNKTYPE;
            } // foreach
        } // if
        $trc .= ") " . BFText::_('COM_BREEZINGFORMS_PROCESS_ATLINE') . " $line\n";
        $ff_processor->traceBuffer .= htmlspecialchars($trc, ENT_QUOTES);
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
    } // if
    array_push($ff_processor->traceStack, array($oldmode, 'f', $name, $line, $type, $id, $pane));
}

// _ff_traceFunction

function _ff_traceExit($line, $retval = null)
{
    global $ff_processor;

    if ($ff_processor->dying || ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE))
        return;
    $info = array_pop($ff_processor->traceStack);
    if ($info) {
        $oldmode = $ff_processor->traceMode;
        $newmode = $info[0];
        $kind = $info[1];
        $name = $info[2];
        $type = $info[4];
        $id = $info[5];
        $pane = $info[6];
        if (_FF_DEBUG & _FF_DEBUG_EXIT) {
            $ff_processor->traceBuffer .= htmlspecialchars(
                "\n_FF_DEBUG_EXIT:" .
                "\n  Info     = $kind $name at line $line" .
                "\n  Old mode = " . $ff_processor->dispTraceMode($oldmode) .
                "\n  New mode = " . $ff_processor->dispTraceMode($newmode) .
                "\n",
                ENT_QUOTES
            );
            if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
                $ff_processor->dumpTrace();
        } // if
        if ($kind == 'p')
            $visible = $oldmode & _FF_TRACEMODE_PIECE;
        else
            $visible = $oldmode & _FF_TRACEMODE_FUNCTION;
        if ($visible) {
            $level = count($ff_processor->traceStack);
            for ($l = 0; $l < $level; $l++)
                $ff_processor->traceBuffer .= '  ';
            $ff_processor->traceBuffer .= htmlspecialchars(
                "-" . BFText::_('COM_BREEZINGFORMS_PROCESS_LEAVE') . " $name " . BFText::_('COM_BREEZINGFORMS_PROCESS_ATLINE') . " $line\n",
                ENT_QUOTES
            );
            if ($oldmode & _FF_TRACEMODE_DIRECT)
                $ff_processor->dumpTrace();
        } // if
        if ($oldmode != $newmode)
            $ff_processor->traceMode = ($oldmode & ~_FF_TRACEMODE_VARIABLE) | ($newmode & _FF_TRACEMODE_VARIABLE);
    } else {
        $ff_processor->traceBuffer .= htmlspecialchars(BFText::_('COM_BREEZINGFORMS_PROCESS_WARNSTK') . "\n", ENT_QUOTES);
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
        $type = $id = $pane = null;
        $name = BFText::_('COM_BREEZINGFORMS_PROCESS_UNKNOWN');
    } // if
    return $retval;
}

// _ff_traceExit

function _ff_errorHandler($errno, $errstr, $errfile, $errline)
{
    global $ff_processor, $ff_mossite, $database;
    $database = Factory::getContainer()->get(DatabaseInterface::class);

    if (isset($ff_processor->dying) && $ff_processor->dying)
        return;

    $msg = "\n<strong>*** " . htmlspecialchars(BFText::_('COM_BREEZINGFORMS_PROCESS_EXCAUGHT'), ENT_QUOTES) . " ***</strong>\n" .
        htmlspecialchars(BFText::_('COM_BREEZINGFORMS_PROCESS_PHPLEVEL') . ' ', ENT_QUOTES);
    $fail = false;
    if (!defined('E_DEPRECATED')) {
        define('E_DEPRECATED', 8192);
    }
    switch ($errno) {
        case E_WARNING:
            $msg .= "E_WARNING";
            break;
        case E_NOTICE:
            $msg .= "E_NOTICE";
            break;
        case E_USER_ERROR:
            $msg .= "E_USER_ERROR";
            $fail = true;
            break;
        case E_USER_WARNING:
            $msg .= "E_USER_WARNING";
            break;
        case E_USER_NOTICE:
            $msg .= "E_USER_NOTICE";
            break;
        case E_DEPRECATED:
            $msg .= "E_DEPRECATED";
            break;
        case 2048:
            if (_FF_IGNORE_STRICT)
                return;
            $msg .= "E_STRICT";
            break;
        case 16384: // JLanguage deprecation error
            return;
            break;
        default:
            $msg .= $errno;
            $fail = true;
    } // switch
    $msg .= htmlspecialchars(
        "\n" . BFText::_('COM_BREEZINGFORMS_PROCESS_PHPFILE') . " $errfile\n" .
        BFText::_('COM_BREEZINGFORMS_PROCESS_PHPLINE') . " $errline\n",
        ENT_QUOTES
    );

    $n = 0;
    if (isset($ff_processor)) {
        $n = count($ff_processor->traceStack);
    }

    if ($n) {
        $info = $ff_processor->traceStack[$n - 1];
        $name = htmlspecialchars($info[2] . ' ' . BFText::_('COM_BREEZINGFORMS_PROCESS_ATLINE') . ' ' . $info[3], ENT_QUOTES);
        $type = $info[4];
        $id = $info[5];
        $pane = $info[6];
        if ($type && $id && $ff_processor->runmode != _FF_RUNMODE_FRONTEND) {
            $url = $ff_mossite . '/administrator/index.php?option=com_breezingforms&format=html&tmpl=component';
            $what = $id;
            switch ($type) {
                case 'f':
                    $url .= '&act=editpage' .
                        '&task=editform' .
                        '&form=' . $ff_processor->form;
                    if ($ff_processor->formrow->package != '')
                        $url .= '&pkg=' . urlencode($ff_processor->formrow->package);
                    if ($pane > 0)
                        $url .= '&tabpane=' . $pane;
                    $what = 'form ' . $ff_processor->formrow->name;
                    break;
                case 'e':
                    $page = 1;
                    foreach ($ff_processor->rows as $row)
                        if ($row->id == $id) {
                            $page = $row->page;
                            $what = $row->name;
                            break;
                        } // if
                    $what = 'element ' . $what;
                    $url .= '&act=editpage' .
                        '&task=edit' .
                        '&form=' . $ff_processor->form .
                        '&page=' . $page .
                        '&ids[]=' . $id;
                    if ($ff_processor->formrow->package != '')
                        $url .= '&pkg=' . urlencode($ff_processor->formrow->package);
                    if ($pane > 0)
                        $url .= '&tabpane=' . $pane;
                    break;
                case 'p':
                    $package = '';
                    $database->setQuery("select name, package from #__facileforms_pieces where id=$id");
                    $rows = $database->loadObjectList();
                    if (count($rows)) {
                        $package = $rows[0]->package;
                        $what = $rows[0]->name;
                    }
                    $what = 'piece ' . $what;
                    $url .= '&act=managepieces' .
                        '&task=edit' .
                        '&ids[]=' . $id;
                    if ($package != '')
                        $url .= '&pkg=' . urlencode($package);
                    break;
                case 's':
                    $package = '';
                    $database->setQuery("select name, package from #__facileforms_scripts where id=$id");
                    $rows = $database->loadObjectList();
                    if (count($rows)) {
                        $package = $rows[0]->package;
                        $what = $rows[0]->name;
                    }
                    $what = 'script ' . $what;
                    $url .= '&act=managescripts' .
                        '&task=edit' .
                        '&ids[]=' . $id;
                    if ($package != '')
                        $url .= '&pkg=' . urlencode($package);
                    break;
                default:
                    $url = null;
            } // switch
            if ($url)
                $name = '<a href="#" ' .
                    'onMouseOver="window.status=\'Open ' . $what . '\';return true;" ' .
                    'onMouseOut="window.status=\'\';return true;" ' .
                    'onClick="ff_redirectParent(\'' . htmlspecialchars($url, ENT_QUOTES) . '\');return true;"' .
                    '>' . $name . '</a>';
        } // if
        $msg .= htmlspecialchars(BFText::_('COM_BREEZINGFORMS_PROCESS_LASTPOS'), ENT_QUOTES) . ' ' . $name . "\n";
    } // if
    $msg .= htmlspecialchars(BFText::_('COM_BREEZINGFORMS_PROCESS_ERRMSG') . " $errstr\n\n", ENT_QUOTES);
    if ($fail) {
        if (isset($ff_processor)) {
            $ff_processor->traceBuffer .= $msg;
            $ff_processor->suicide();
        }
    } else
        if (isset($ff_processor)) {
            if (($ff_processor->traceMode & _FF_TRACEMODE_DISABLE) == 0) {
                $ff_processor->traceBuffer .= $msg;
                if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
                    $ff_processor->dumpTrace();
            }
        } // if
}

// _ff_errorHandler

class HTML_facileFormsProcessor
{

    var $okrun = null;     // running is allowed
    var $ip = null;     // visitor ip
    var $agent = null;     // visitor agent
    var $browser = null;     // visitors browser
    var $opsys = null;     // visitors operating system
    var $provider = null;     // visitors provider
    var $submitted = null;     // submit date/time
    var $formrow = null;     // form row
    var $form = null;     // form #
    var $form_id = null;     // html form id
    var $page = null;     // page id
    var $target = null;     // target form name
    var $rows = null;     // element rows
    var $rowcount = null;     // # of element rows
    var $runmode = null;     // current run mode _FF_RUNMODE_...
    var $inline = null;     // inline preview
    var $inframe = null;     // running in a frame
    var $template = null;     // 0-frontend 1-backend
    var $homepage = null;     // home page
    var $mospath = null;     // mos absolute path
    var $mossite = null;     // mos site
    var $images = null;     // ff_images path
    var $uploads = null;     // ff_uploads path
    var $border = null;     // show border
    var $align = null;     // form alignment
    var $top = null;     // top margin
    var $suffix = null;     // class name suffix
    var $status = null;     // submit return status
    var $message = null;     // submit return message
    var $record_id = null;     // id of saved record
    var $submitdata = null;     // submitted data
    var $savedata = null;     // data for db save
    var $maildata = null;     // data for mail notification
    var $sfdata = null;
    var $xmldata = null;     // data for xml attachment
    var $mb_xmldata = null;     // data for mailback attachments
    var $queryCols = null;     // query column definitions
    var $queryRows = null;     // query rows
    var $showgrid = null;     // show grid in preview
    var $findtags = null;     // tags to be replaced
    var $replacetags = null;     // tag replacements
    var $dying = null;     // form is dying
    var $errrep = null;     // remember old error reporting
    var $traceMode = null;     // trace mode
    var $traceStack = null;     // trace stack
    var $traceBuffer = null;     // trace buffer
    var $user_id = null;
    var $username = null;
    var $user_full_name = null;
    var $mailbackRecipients = array();
    var $editable = null;
    var $editable_override = null;
    var $sendNotificationAfterPayment = false;
    var $opt_token = "9562384751";
    public $draggableDivIds = array();
    public $isMobile = false;
    public $quickmode = null;
    public $legacy_wrap = true;

    function __construct(
        $runmode, // _FF_RUNMODE_FRONTEND, ..._BACKEND, ..._PREVIEW
        $inframe, // run in iframe
        $form, // form id
        $page = 1, // page #
        $border = 0, // show border
        $align = 1, // align code
        $top = 0, // top margin
        $target = '', // target form name
        $suffix = '', // class name suffix
        $editable = 0,
        $editable_override = 0
    ) {
        global $database, $ff_config, $ff_mossite, $ff_mospath, $ff_processor;
        $ff_processor = $this;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $this->dying = false;
        $this->runmode = $runmode;
        $this->inframe = $inframe;
        $this->form = $form;
        $this->page = $page;
        $this->border = $border;
        $this->align = $align;
        $this->top = $top;
        $this->target = $target;
        $this->suffix = trim($suffix);
        $this->editable = $editable;
        $this->editable_override = $editable_override;

        if (!class_exists('Joomla\CMS\Environment\Browser')) {
            require_once(JPATH_SITE . '/libraries/joomla/environment/browser.php');
        }
        $this->ip = $_SERVER['REMOTE_ADDR'];
        if ($ff_config->disable_ip == "1") {
            $this->ip = 0;
        }
        $this->agent = Browser::getInstance()->getAgentString();

        $this->browser = Browser::getInstance()->getAgentString();

        $jbrowserInstance = Browser::getInstance();
        $this->opsys = $jbrowserInstance->getPlatform();

        if ($ff_config->getprovider == 0)
            $this->provider = BFText::_('COM_BREEZINGFORMS_PROCESS_UNKNOWN');
        else {
            $host = @GetHostByAddr($this->ip);
            $this->provider = preg_replace('/^./', '', strchr($host, '.'));
        } // if


        $tz = 'UTC';
        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

        $submitted = Factory::getDate();
        $submitted = Factory::getDate('now', $tz);

        $this->submitted = $submitted->format('Y-m-d H:i:s');

        /*
          $format = Text::_('DATE_FORMAT_LC2');
          if ( !$format ) {
          $this->submitted = date('Y-m-d H:i:s');
          }else{
          $config = Factory::getConfig();
          $offset = $config->getValue('config.offset');
          $instance = Factory::getDate(date('Y-m-d H:i:s'));
          $instance->setOffset($offset);
          $this->submitted = $instance->toFormat($format);
          } */

        $this->formrow = new facileFormsForms($database);
        $this->formrow->load($form);

        if ($this->formrow->published) {
            $database->setQuery(
                "select * from #__facileforms_elements " .
                "where form=" . $this->form . " and published=1 " .
                "order by page, ordering"
            );
            $this->rows = $database->loadObjectList();
            $this->rowcount = count($this->rows);
        } // if
        $this->inline = 0;
        $this->template = 0;
        $this->form_id = "ff_form" . $form;
        if ($runmode == _FF_RUNMODE_FRONTEND) {
            $this->homepage = $ff_mossite;
        } else {
            if ($this->inframe) {
                $this->homepage = $ff_mossite . '/administrator/index.php?tmpl=component';
                if ($this->formrow->runmode == 2)
                    $this->template++;
            } else {
                $this->template++;
                if ($runmode == _FF_RUNMODE_PREVIEW) {
                    $this->inline = 1;
                    $this->form_id = "adminForm";
                } // if
                $this->homepage = 'index.php?tmpl=component';
            } // if
        } // if
        $this->mospath = $ff_mospath;
        $this->mossite = $ff_mossite;
        $this->findtags = array(
            '{ff_currentpage}',
            '{ff_lastpage}',
            '{ff_name}',
            '{ff_title}',
            '{ff_homepage}',
            '{mospath}',
            '{mossite}'
        );
        $this->replacetags = array(
            $this->page,
            $this->formrow->pages,
            $this->formrow->name,
            $this->formrow->title,
            $this->homepage,
            $this->mospath,
            $this->mossite
        );
        $this->images = str_replace($this->findtags, $this->replacetags, $ff_config->images);
        $this->findtags[] = '{ff_images}';
        $this->replacetags[] = $this->images;
        $this->uploads = str_replace($this->findtags, $this->replacetags, $ff_config->uploads);
        $this->findtags[] = '{ff_uploads}';
        $this->replacetags[] = $this->uploads;
        // CONTENTBUILDER
        $this->findtags[] = '{CBSite}';
        $this->replacetags[] = JPATH_SITE;
        $this->findtags[] = '{cbsite}';
        $this->replacetags[] = JPATH_SITE;
        $this->showgrid = $runmode == _FF_RUNMODE_PREVIEW && $this->formrow->prevmode > 0 && $ff_config->gridshow == 1 && $ff_config->gridsize > 1;
        $this->okrun = $this->formrow->published;

        if ($this->okrun)
            switch ($this->runmode) {
                case _FF_RUNMODE_FRONTEND:
                    $this->okrun = ($this->formrow->runmode == 0 || $this->formrow->runmode == 1);
                    break;
                case _FF_RUNMODE_BACKEND:
                    $this->okrun = ($this->formrow->runmode == 0 || $this->formrow->runmode == 2);
                    break;
                default:
                    ;
            } // switch
        $this->traceMode = _FF_TRACEMODE_FIRST;
        $this->traceStack = array();
        $this->traceBuffer = null;
    }

    //  HTML_facileFormsProcessor

    function dispTraceMode($mode)
    {
        if (!is_int($mode))
            return $mode;
        $m = '(';
        if ($mode & _FF_TRACEMODE_FIRST)
            $m .= 'first ';
        $m .= ($mode & _FF_TRACEMODE_DIRECT ? 'direct' : ($mode & _FF_TRACEMODE_APPEND ? 'append' : 'popup'));
        if ($mode & _FF_TRACEMODE_DISABLE)
            $m .= ' disable';
        else {
            switch ($mode & _FF_TRACEMODE_PRIORITY) {
                case 0:
                    $m .= ' minimum';
                    break;
                case 1:
                    $m .= ' low';
                    break;
                case 2:
                    $m .= ' normal';
                    break;
                case 3:
                    $m .= ' high';
                    break;
                default:
                    $m .= ' maximum';
                    break;
            } // switch
            $m .= $mode & _FF_TRACEMODE_LOCAL ? ' local' : ' global';
            switch ($mode & _FF_TRACEMODE_TOPIC) {
                case 0:
                    $m .= ' none';
                    break;
                case _FF_TRACEMODE_TOPIC:
                    $m .= ' all';
                    break;
                default:
                    if ($mode & _FF_TRACEMODE_EVAL)
                        $m .= ' eval';
                    if ($mode & _FF_TRACEMODE_PIECE)
                        $m .= ' piece';
                    if ($mode & _FF_TRACEMODE_FUNCTION)
                        $m .= ' function';
                    if ($mode & _FF_TRACEMODE_MESSAGE)
                        $m .= ' message';
            } // switch
        } // if
        return $m . ')';
    }

    // dispTraceMode

    function trim(&$code)
    {
        $len = strlen($code);
        if (!$len)
            return false;
        if (strpos(" \t\r\n", $code[0]) === false && strpos(" \t\r\n", $code[$len - 1]) === false)
            return true;
        $code = trim($code);
        return $code != '';
    }

    // trim

    function nonblank(&$code)
    {
        return preg_match("/[^\\s]+/si", $code);
    }

    // nonblank

    function getClassName($classdef)
    {
        $name = '';
        if (strpos($classdef, ';') === false)
            $name = $classdef;
        else {
            $defs = explode(';', $classdef);
            $name = $defs[$this->template];
        } // if
        if ($this->trim($name))
            $name .= $this->suffix;
        return $name;
    }

    // getClassName

    function expJsValue($mixed, $indent = '')
    {
        if (is_null($mixed))
            return $indent . 'null';

        if (is_bool($mixed))
            return $mixed ? $indent . 'true' : $indent . 'false';

        if (is_numeric($mixed))
            return $indent . $mixed;

        if (is_string($mixed))
            return
                $indent . "'" .
                str_replace(
                    array("\\", "'", "\r", "<", "\n"),
                    array("\\\\", "\\'", "\\r", "\\074", "\\n'+" . nl() . $indent . "'"),
                    $mixed
                ) .
                "'";

        if (is_array($mixed)) {
            $dst = $indent . '[' . nl();
            $next = false;
            foreach ($mixed as $value) {
                if ($next)
                    $dst .= "," . nl();
                else
                    $next = true;
                $dst .= $this->expJsValue($value, $indent . "\t");
            } // foreach
            return $dst . nl() . $indent . ']';
        } // if

        if (is_object($mixed)) {
            $dst = $indent . '{' . nl();
            $arr = get_object_vars($mixed);
            $next = false;
            foreach ($arr as $key => $value) {
                if ($next)
                    $dst .= "," . nl();
                else
                    $next = true;
                $dst .= $indent . $key . ":" . nl() . $this->expJsValue($value, $indent . "\t");
            } // foreach
            return $dst . nl() . $indent . '}';
        } // if
        // not supported types
        if (is_resource($mixed))
            return $indent . "'" . BFText::_('COM_BREEZINGFORMS_PROCESS_RESOURCE') . "'";

        return $indent . "'" . BFText::_('COM_BREEZINGFORMS_PROCESS_UNKNOWN') . "'";
    }

    // expJsValue

    function expJsVar($name, $mixed)
    {
        return $name . ' = ' . $this->expJsValue($mixed) . ';' . nl();
    }

    // expJsVar

    function dumpTrace()
    {
        if ($this->traceMode & _FF_TRACEMODE_DIRECT) {
            $html = ob_get_contents();
            ob_end_clean();
            echo htmlspecialchars($html, ENT_QUOTES) . $this->traceBuffer;
            ob_start();
            $this->traceBuffer = null;
            return;
        } // if
        if (!$this->traceBuffer)
            return;
        if ($this->traceMode & _FF_TRACEMODE_APPEND) {
            echo '<pre>' . $this->traceBuffer . '</pre>';
            $this->traceBuffer = null;
            return;
        } // if
        echo
            '<script type="text/javascript">' . nl() .
            '<!--' . nl();
        if ($this->dying)
            echo 'console.log(' . json_encode($this->traceBuffer) . ')' . nl();
        echo
            '-->' . nl() .
            '</script>' . nl();
        $this->traceBuffer = null;
    }

    // dumpTrace

    function traceEval($name)
    {
        if (
            ($this->traceMode & _FF_TRACEMODE_DISABLE) ||
            !($this->traceMode & _FF_TRACEMODE_EVAL) ||
            $this->dying
        )
            return;
        $level = count($this->traceStack);
        for ($l = 0; $l < $level; $l++)
            $this->traceBuffer .= '  ';
        $this->traceBuffer .= htmlspecialchars("eval($name)\n", ENT_QUOTES);
        if ($this->traceMode & _FF_TRACEMODE_DIRECT)
            $this->dumpTrace();
    }

    // traceEval

    function suicide()
    {
        if ($this->dying)
            return false;
        $this->dying = true;
        $rep = 0;
        $this->errrep = error_reporting($rep);
        return true;
    }

    // suicide

    function bury()
    {
        if (!$this->dying)
            return false;
        if ($this->traceMode & _FF_TRACEMODE_DIRECT)
            $this->dumpTrace();
        ob_end_clean();
        if ($this->traceMode & _FF_TRACEMODE_DIRECT)
            echo '</pre>';
        else
            $this->dumpTrace();
        error_reporting($this->errrep);
        restore_error_handler();
        return true;
    }

    // bury

    function findToken(&$code, &$spos, &$offs)
    {
        $srch = '#(function|return[^a-zA-Z_-]|_ff_trace|ff_trace[ \\t]*\\(|//|/\*|\*/|\\\\"|\\\\\'|{|}|\(|\)|;|"|\'|\n)#si';
        $match = array();
        if (!preg_match($srch, $code, $match, PREG_OFFSET_CAPTURE, $spos))
            return '';
        $token = strtolower($match[0][0]);
        $offs = $match[0][1];
        $spos = $offs + strlen($token);
        return $token;
    }

    // findToken

    function findRealToken(&$code, &$spos, &$offs, &$line)
    {
        $linecmt = $blockcmt = false;
        $quote = null;
        for (; ; ) {
            $token = preg_replace('/[ \\t]*/', '', $this->findToken($code, $spos, $offs));
            switch ($token) {
                case '':
                    return '';
                case 'function':
                case 'return';
                case 'ff_trace(';
                case '{':
                case '}':
                case '(':
                case ')':
                case ';':
                    if (!$linecmt && !$blockcmt && !$quote)
                        return $token;
                    break;
                case "\n":
                    $line++;
                    $linecmt = false;
                    break;
                case '//':
                    if (!$blockcmt && !$quote)
                        $linecmt = true;
                    break;
                case '/*':
                    if (!$linecmt && !$quote)
                        $longcmt = true;
                    break;
                case '"':
                case "'":
                    if ($quote == $token)
                        $quote = null;
                    else
                        if (!$linecmt && !$blockcmt && !$quote)
                            $quote = $token;
                    break;
                default:
                    break;
            } // switch
        } // for
    }

    // findRealToken

    function patchCode($mode, $code, $name, $type, $id, $pane)
    {
        $flevel = $cpos = $spos = $offs = 0;
        $bye = false;
        $fstack = array();
        $line = 1;
        if ($type && $id) {
            $type = "'$type'";
            if (!$pane)
                $pane = 'null';
        } else
            $type = $id = $pane = 'null';
        $name = str_replace("'", "\\'", $name);
        $dst = "_ff_tracePiece($mode,'$name',$line,$type,$id,$pane);";
        while (!$bye) {
            switch ($this->findRealToken($code, $spos, $offs, $line)) {
                case '':
                    $bye = true;
                    break;
                case 'function':
                    $brk = false;
                    while (!$brk) {
                        // consume tokens until finding the opening bracket
                        switch ($this->findRealToken($code, $spos, $offs, $line)) {
                            case '':
                                $bye = $brk = true;
                                break;
                            case '{':
                                $dst .= substr($code, $cpos, $spos - $cpos) .
                                    '$_ff_traceArgs = func_get_args();' .
                                    '_ff_traceFunction(' . $mode . ',__FUNCTION__,' . $line . ',' . $type . ',' . $id . ',' . $pane . ',$_ff_traceArgs);' .
                                    '$_ff_traceArgs=null;';
                                $cpos = $spos;
                                if ($flevel)
                                    array_push($fstack, $flevel);
                                $flevel = 1;
                                $brk = true;
                                break;
                            default:
                                ;
                        } // switch
                    } // while
                    break;
                case 'return':
                    $dst .= substr($code, $cpos, $spos - $cpos);
                    $cpos = $spos;
                    $brk = false;
                    while (!$brk) {
                        // consume tokens until semicolon found
                        switch ($this->findRealToken($code, $spos, $offs, $line)) {
                            case '':
                                $bye = $brk = true;
                                break;
                            case ';':
                                $arg = substr($code, $cpos, $offs - $cpos);
                                if ($this->nonblank($arg))
                                    $dst .= ' _ff_traceExit(' . $line . ',' . $arg . ');';
                                else
                                    $dst .= ' _ff_traceExit(' . $line . ');';
                                $cpos = $spos;
                                $brk = true;
                                break;
                            default:
                                ;
                        } // switch
                    } // while
                    break;
                case 'ff_trace(':
                    $dst .= substr($code, $cpos, $offs - $cpos);
                    $cpos = $spos;
                    $brk = false;
                    $lvl = 0;
                    while (!$brk) {
                        // consume tokens until finding the closing bracket
                        switch ($this->findRealToken($code, $spos, $offs, $line)) {
                            case '':
                                $bye = $brk = true;
                                break;
                            case '(':
                                $lvl++;
                                break;
                            case ')':
                                if ($lvl)
                                    $lvl--;
                                else
                                    $brk = true;
                                break;
                            default:
                                ;
                        } // switch
                    } // while
                    $par = $offs == $cpos ? '' : substr($code, $cpos, $offs - $cpos);
                    $dst .= " _ff_trace($line";
                    if ($this->nonblank($par))
                        $dst .= ',';
                    break;
                case '{':
                    if ($flevel > 0)
                        $flevel++;
                    break;
                case '}';
                    if ($flevel > 0) {
                        $flevel--;
                        if (!$flevel) {
                            $dst .= substr($code, $cpos, $offs - $cpos) . ' _ff_traceExit(' . $line . ');}';
                            $cpos = $spos;
                            if (count($fstack))
                                $flevel = array_pop($fstack);
                        } // if
                    } // if
                    break;
                default:
            } // switch
        } // while
        $spos = strlen($code);
        if ($cpos < $spos)
            $dst .= substr($code, $cpos, $spos - $cpos);
        $line--;
        $dst .= "_ff_traceExit($line);";
        if (_FF_DEBUG & _FF_DEBUG_PATCHEDCODE) {
            $this->traceBuffer .= htmlspecialchars(
                "\n_FF_DEBUG_PATCHEDCODE:" .
                "\n  Mode = " . $this->dispTraceMode($mode) .
                "\n  Name = $name" .
                "\n  Link = $type $id $pane" .
                "\n------ begin patched code ------" .
                "\n$dst" .
                "\n------- end patched code -------" .
                "\n",
                ENT_QUOTES
            );
            if ($this->traceMode & _FF_TRACEMODE_DIRECT)
                $this->dumpTrace();
        } // if
        return $dst;
    }

    // patchCode

    function prepareEvalCode(&$code, $name, $type, $id, $pane)
    {
        if ($this->dying)
            return false;
        if (!$this->nonblank($code))
            return false;
        $code .= "\n/*'/*\"/**/;"; // closes all comments and strings that my be open
        $disable = ($this->traceMode & _FF_TRACEMODE_DISABLE) ? true : false;
        if (!$disable) {
            $mode = 'null';
            $srch = '#' .
                '^[\\s]*(//\+trace|/\*\+trace)' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*(\\*/|\\r\\n)?' .
                '#';
            $match = array();
            if (preg_match($srch, $code, $match)) {
                $mode = 2;
                $append = $direct = $xeval = $piece = $func = $msg = false;
                $local = $def = true;
                for ($m = 2; $m < count($match); $m++)
                    switch ($match[$m]) {
                        // disable
                        case 'dis':
                        case 'disable':
                            $disable = true;
                            break;
                        // mode
                        case 'pop':
                        case 'popup':
                            $direct = $append = false;
                            break;
                        case 'app':
                        case 'append':
                            $append = true;
                            $direct = false;
                            break;
                        case 'dir':
                        case 'direct':
                            $direct = true;
                            $append = false;
                            break;
                        // priority
                        case 'min':
                        case 'minimum':
                            $mode = 0;
                            break;
                        case 'low':
                            $mode = 1;
                            break;
                        case 'nor':
                        case 'normal':
                            $mode = 2;
                            break;
                        case 'hig':
                        case 'high':
                            $mode = 3;
                            break;
                        case 'max':
                        case 'maximum':
                            $mode = 4;
                            break;
                        // scope
                        case 'glo':
                        case 'global':
                            $local = false;
                            break;
                        case 'loc':
                        case 'local':
                            $local = true;
                            break;
                        // topics
                        case 'all':
                            $def = false;
                            $xeval = $piece = $func = $msg = true;
                            break;
                        case 'non':
                        case 'none':
                            $def = $xeval = $piece = $func = $msg = false;
                            break;
                        case 'eva':
                        case 'eval':
                            $def = false;
                            $xeval = true;
                            break;
                        case 'pie':
                        case 'piece':
                            $def = false;
                            $piece = true;
                            break;
                        case 'fun':
                        case 'function':
                            $def = false;
                            $func = true;
                            break;
                        case 'mes':
                        case 'message':
                            $def = false;
                            $msg = true;
                            break;
                        default:
                            break;
                    } // switch

                if ($def) {
                    $xeval = false;
                    $piece = $func = $msg = true;
                }
                if ($xeval)
                    $mode |= _FF_TRACEMODE_EVAL;
                if ($piece)
                    $mode |= _FF_TRACEMODE_PIECE;
                if ($func)
                    $mode |= _FF_TRACEMODE_FUNCTION;
                if ($msg)
                    $mode |= _FF_TRACEMODE_MESSAGE;
                if ($local)
                    $mode |= _FF_TRACEMODE_LOCAL;

                $first = ($this->traceMode & _FF_TRACEMODE_FIRST) ? true : false;
                if ($first) {
                    $oldMode = $this->traceMode;
                    $this->traceMode = 0;
                    if ($disable)
                        $this->traceMode |= _FF_TRACEMODE_DISABLE;
                    if ($append)
                        $this->traceMode |= _FF_TRACEMODE_APPEND;
                    if ($direct) {
                        $this->traceMode |= _FF_TRACEMODE_DIRECT;
                        $html = ob_get_contents();
                        ob_end_clean();
                        echo '<pre>' . htmlspecialchars($html, ENT_QUOTES);
                        ob_start();
                    } // if
                } else
                    $disable = false;
                if (_FF_DEBUG & _FF_DEBUG_DIRECTIVE) {
                    $_deb = "\n_FF_DEBUG_DIRECTIVE:";
                    if ($first)
                        $_deb .= "\n  Previous mode=" . $this->dispTraceMode($oldMode);
                    $_deb .= "\n  Trace mode   =" . $this->dispTraceMode($this->traceMode) .
                        "\n  New mode     =" . $this->dispTraceMode($mode) .
                        "\n";
                    $this->traceBuffer .= htmlspecialchars($_deb, ENT_QUOTES);
                    if ($this->traceMode & _FF_TRACEMODE_DIRECT)
                        $this->dumpTrace();
                } // if
            } // if trace directive
            if (!$disable) {
                if (!$name) {
                    $name = preg_replace('/([\\s]+)/si', ' ', $code);
                    if (strlen($name) > _FF_TRACE_NAMELIMIT)
                        $name = substr($code, 0, _FF_TRACE_NAMELIMIT - 3) . '...';
                } // if
                $code = $this->patchCode($mode, $code, $name, $type, $id, $pane);
            } // if
        } // if trace not disabled
        $code = str_replace($this->findtags, $this->replacetags, $code);
        return true;
    }

    // prepareEvalCode

    function getPieceById($id, $name = null)
    {
        if ($this->dying)
            return '';
        global $database;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $database->setQuery(
            'select code, name from #__facileforms_pieces ' .
            'where id=' . $id . ' and published=1 '
        );
        $rows = $database->loadObjectList();
        if ($rows && count($rows)) {
            $name = $rows[0]->name;
            return $rows[0]->code;
        } // if
        return '';
    }

    // getPieceById

    function getPieceByName($name, $id = null)
    {
        if ($this->dying)
            return '';
        global $database;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $database->setQuery(
            'select id, code from #__facileforms_pieces ' .
            'where name=\'' . $name . '\' and published=1 ' .
            'order by id desc'
        );
        $rows = $database->loadObjectList();
        if ($rows && count($rows)) {
            $id = $rows[0]->id;
            return $rows[0]->code;
        } // if
        return '';
    }

    // getPieceByName

    function execPiece($code, $name, $type, $id, $pane)
    {
        $ret = '';
        if ($this->prepareEvalCode($code, $name, $type, $id, $pane)) {
            $this->traceEval($name);

            $ret = eval($code);
        } // if
        return $ret;
    }

    // execPiece

    function execPieceById($id)
    {
        $name = null;
        $code = $this->getPieceById($id, $name);
        return $this->execPiece($code, BFText::_('COM_BREEZINGFORMS_PROCESS_PIECE') . " $name", 'p', $id, null);
    }

    // execPieceById

    function execPieceByName($name)
    {
        $id = null;
        $code = $this->getPieceByName($name, $id);
        return $this->execPiece($code, BFText::_('COM_BREEZINGFORMS_PROCESS_PIECE') . " $name", 'p', $id, null);
    }

    // execPieceByName

    function replaceCode($code, $name, $type, $id, $pane)
    {

        if ($this->dying)
            return '';
        $p1 = 0;
        $l = strlen($code);
        $c = '';
        $n = 0;
        while ($p1 < $l) {
            $p2 = strpos($code, '<?php', $p1);
            if ($p2 === false)
                $p2 = $l;
            $c .= substr($code, $p1, $p2 - $p1);
            $p1 = $p2;
            if ($p1 < $l) {
                $p1 += 5;
                $p2 = strpos($code, '?>', $p1);
                if ($p2 === false)
                    $p2 = $l;
                $n++;
                $c .= $this->execPiece(substr($code, $p1, $p2 - $p1), $name . "[$n]", $type, $id, $pane);
                if ($this->dying)
                    return '';
                $p1 = $p2 + 2;
            } // if
        } // while
        return str_replace($this->findtags, $this->replacetags, $c);
    }

    // replaceCode

    function compileQueryCol(&$elem, &$coldef)
    {
        $coldef->comp = array();
        if ($this->trim(str_replace($this->findtags, $this->replacetags, $coldef->value))) {
            $c = $p1 = 0;
            $l = strlen($coldef->value);
            while ($p1 < $l) {
                $p2 = strpos($coldef->value, '<?php', $p1);
                if ($p2 === false)
                    $p2 = $l;
                $coldef->comp[$c] = array(
                    false,
                    str_replace(
                        $this->findtags,
                        $this->replacetags,
                        trim(substr($coldef->value, $p1, $p2 - $p1))
                    )
                );
                if ($this->trim($coldef->comp[$c][1]))
                    $c++;
                $p1 = $p2;
                if ($p1 < $l) {
                    $p1 += 5;
                    $p2 = strpos($coldef->value, '?>', $p1);
                    if ($p2 === false)
                        $p2 = $l;
                    $coldef->comp[$c] = array(true, substr($coldef->value, $p1, $p2 - $p1));
                    if (
                        $this->prepareEvalCode(
                            $coldef->comp[$c][1],
                            BFText::_('COM_BREEZINGFORMS_PROCESS_QVALUEOF') . " " . $elem->name . "::" . $coldef->name,
                            'e',
                            $elem->id,
                            2
                        )
                    )
                        $c++;
                    $p1 = $p2 + 2;
                } // if
            } // while
            if ($c > count($coldef->comp))
                array_pop($coldef->comp);
        } // if non-empty
    }

    // compileQueryCol

    function execQueryValue($code, &$elem, &$row, &$coldef, $value)
    {
        $this->traceEval(BFText::_('COM_BREEZINGFORMS_PROCESS_QVALUEOF') . " " . $elem->name . "::" . $coldef->name);
        return eval($code);
    }

    // execQueryValue

    function execQuery(&$elem, &$valrows, &$coldefs)
    {
        $ret = null;
        $code = $elem->data2;
        if ($this->prepareEvalCode($code, BFText::_('COM_BREEZINGFORMS_PROCESS_QPIECEOF') . " " . $elem->name, 'e', $elem->id, 1)) {
            $rows = array();
            $this->traceEval(BFText::_('COM_BREEZINGFORMS_PROCESS_QPIECEOF') . " " . $elem->name);
            eval($code);
            $rcnt = count($rows);
            $ccnt = count($coldefs);
            $valrows = array();
            for ($r = 0; $r < $rcnt; $r++) {
                $row = &$rows[$r];
                $valrow = array();
                for ($c = 0; $c < $ccnt; $c++) {
                    $coldef = &$coldefs[$c];
                    $cname = $coldef->name;
                    $value = isset($row->$cname) ? str_replace($this->findtags, $this->replacetags, $row->$cname) : '';
                    $xcnt = count($coldef->comp);
                    if (!$xcnt)
                        $valrow[] = $value;
                    else {
                        $val = '';
                        for ($x = 0; $x < $xcnt; $x++) {
                            $val .= $coldef->comp[$x][0] ? $this->execQueryValue($coldef->comp[$x][1], $elem, $row, $coldef, $value) : $coldef->comp[$x][1];
                            if ($this->dying)
                                break;
                        } // for
                        $valrow[] = str_replace($this->findtags, $this->replacetags, $val);
                    } // if
                    unset($coldef);
                    if ($this->dying)
                        break;
                } // for
                $valrows[] = $valrow;
                unset($row);
                if ($this->dying)
                    break;
            } // for
            $rows = null;
        } // if
    }

    // execQuery

    function script2clause(&$row)
    {
        if ($this->dying)
            return '';
        global $database;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $funcname = '';
        switch ($row->script2cond) {
            case 1:
                $database->setQuery(
                    "select name from #__facileforms_scripts " .
                    "where id=" . $row->script2id . " and published=1 "
                );
                $funcname = $database->loadResult();
                break;
            case 2:
                $funcname = 'ff_' . $row->name . '_action';
                break;
            default:
                break;
        } // switch
        $attribs = '';
        if ($funcname != '') {
            if ($row->script2flag1)
                $attribs .= ' onclick="' . $funcname . '(this,\'click\');"';
            if ($row->script2flag2)
                $attribs .= ' onblur="' . $funcname . '(this,\'blur\');"';
            if ($row->script2flag3)
                $attribs .= ' onchange="' . $funcname . '(this,\'change\');"';
            if ($row->script2flag4)
                $attribs .= ' onfocus="' . $funcname . '(this,\'focus\');"';
            if ($row->script2flag5)
                $attribs .= ' onselect="' . $funcname . '(this,\'select\');"';
        } // if
        return $attribs;
    }

    // script2clause

    function loadBuiltins(&$library)
    {
        global $database, $ff_config, $ff_request;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        if ($this->dying)
            return;
        $library[] = array('FF_STATUS_OK', 'var FF_STATUS_OK = ' . _FF_STATUS_OK . ';');
        $library[] = array('FF_STATUS_UNPUBLISHED', 'var FF_STATUS_UNPUBLISHED = ' . _FF_STATUS_UNPUBLISHED . ';');
        $library[] = array('FF_STATUS_SAVERECORD_FAILED', 'var FF_STATUS_SAVERECORD_FAILED = ' . _FF_STATUS_SAVERECORD_FAILED . ';');
        $library[] = array('FF_STATUS_SAVESUBRECORD_FAILED', 'var FF_STATUS_SAVESUBRECORD_FAILED = ' . _FF_STATUS_SAVESUBRECORD_FAILED . ';');
        $library[] = array('FF_STATUS_UPLOAD_FAILED', 'var FF_STATUS_UPLOAD_FAILED = ' . _FF_STATUS_UPLOAD_FAILED . ';');
        $library[] = array('FF_STATUS_SENDMAIL_FAILED', 'var FF_STATUS_SENDMAIL_FAILED = ' . _FF_STATUS_SENDMAIL_FAILED . ';');
        $library[] = array('FF_STATUS_ATTACHMENT_FAILED', 'var FF_STATUS_ATTACHMENT_FAILED = ' . _FF_STATUS_ATTACHMENT_FAILED . ';');

        $library[] = array('ff_homepage', "var ff_homepage = '" . $this->homepage . "';");
        $library[] = array('ff_currentpage', "var ff_currentpage = " . $this->page . ";");
        $library[] = array('ff_lastpage', "var ff_lastpage = " . $this->formrow->pages . ";");
        $library[] = array('ff_images', "var ff_images = '" . $this->images . "';");
        $library[] = array('ff_validationFocusName', "var ff_validationFocusName = '';");
        $library[] = array('ff_currentheight', "var ff_currentheight = 0;");

        $code = "var ff_elements = [" . nl();
        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            $endline = "," . nl();
            if ($i == $this->rowcount - 1)
                $endline = nl();
            switch ($row->type) {
                case "Hidden Input":
                    $code .= "    ['ff_elem" . $row->id . "', 'ff_elem" . $row->id . "', '" . $row->name . "', " . $row->page . ", " . $row->id . "]" . $endline;
                    break;
                case "Static Text":
                case "Rectangle":
                case "Tooltip":
                case "Icon":
                    $code .= "    ['ff_div" . $row->id . "', 'ff_div" . $row->id . "', '" . $row->name . "', " . $row->page . ", " . $row->id . "]" . $endline;
                    break;
                default:
                    $code .= "    ['ff_elem" . $row->id . "', 'ff_div" . $row->id . "', '" . $row->name . "', " . $row->page . ", " . $row->id . "]" . $endline;
            } // switch
        } // for
        $code .= "];";
        $library[] = array('ff_elements', $code);

        $code = "var ff_param = new Object();";
        // reset($ff_request);
        foreach ($ff_request as $prop => $val) {
            if (substr($prop, 0, 9) == 'ff_param_')
                $code .= nl() . "ff_param." . substr($prop, 9) . " = '" . $val . "';";
        }
        // while (list($prop, $val) = each($ff_request))

        $library[] = array('ff_param', $code);

        $library[] = array(
            'ff_getElementByIndex',
            "function ff_getElementByIndex(index)" . nl() .
            "{" . nl() .
            "    if (index >= 0 && index < ff_elements.length)" . nl() .
            "        return eval('document." . $this->form_id . ".'+ff_elements[index][0]);" . nl() .
            "    return null;" . nl() .
            "} // ff_getElementByIndex"
        );

        $library[] = array(
            'ff_getElementByName',
            "function ff_getElementByName(name)" . nl() .
            "{" . nl() .
            "    if (name.substr(0,6) == 'ff_nm_') name = name.substring(6,name.length-2);" . nl() .
            "    for (var i = 0; i < ff_elements.length; i++)" . nl() .
            "        if (ff_elements[i][2]==name)" . nl() .
            "            return eval('document." . $this->form_id . ".'+ff_elements[i][0]);" . nl() .
            "    return null;" . nl() .
            "} // ff_getElementByName"
        );

        $library[] = array(
            'ff_getPageByName',
            "function ff_getPageByName(name)" . nl() .
            "{" . nl() .
            "    if (name.substr(0,6) == 'ff_nm_') name = name.substring(6,name.length-2);" . nl() .
            "    for (var i = 0; i < ff_elements.length; i++)" . nl() .
            "        if (ff_elements[i][2]==name)" . nl() .
            "            return ff_elements[i][3];" . nl() .
            "    return 0;" . nl() .
            "} // ff_getPageByName"
        );

        $library[] = array(
            'ff_getDivByName',
            "function ff_getDivByName(name)" . nl() .
            "{" . nl() .
            "    if (name.substr(0,6) == 'ff_nm_') name = name.substring(6,name.length-2);" . nl() .
            "    for (var i = 0; i < ff_elements.length; i++)" . nl() .
            "        if (ff_elements[i][2]==name)" . nl() .
            "            return document.getElementById(ff_elements[i][1]);" . nl() .
            "    return null;" . nl() .
            "} // ff_getDivByName"
        );

        $library[] = array(
            'ff_getIdByName',
            "function ff_getIdByName(name)" . nl() .
            "{" . nl() .
            "    if (name.substr(0,6) == 'ff_nm_') name = name.substring(6,name.length-2);" . nl() .
            "    for (var i = 0; i < ff_elements.length; i++)" . nl() .
            "        if (ff_elements[i][2]==name)" . nl() .
            "            return ff_elements[i][4];" . nl() .
            "    return null;" . nl() .
            "} // ff_getIdByName"
        );

        $library[] = array(
            'ff_getForm',
            "function ff_getForm()" . nl() .
            "{" . nl() .
            "    return document." . $this->form_id . ";" . nl() .
            "} // ff_getForm"
        );

        $code = "function ff_submitForm()" . nl() .
            "{if(document.getElementById('bfSubmitButton')){document.getElementById('bfSubmitButton').disabled = true;} if(typeof JQuery != 'undefined'){JQuery('.bfCustomSubmitButton').prop('disabled', true);} bfCheckCaptcha();}" . nl();
        $code .= "function ff_submitForm2()" . nl() .
            "{if(document.getElementById('bfSubmitButton')){document.getElementById('bfSubmitButton').disabled = true;} if(typeof JQuery != 'undefined'){JQuery('.bfCustomSubmitButton').prop('disabled', true);} " . nl();
        if ($this->inline)
            $code .= " if(typeof bf_ajax_submit != 'undefined') { bf_ajax_submit() } else { submitform('submit'); }" . nl();
        else
            $code .= " if(typeof bf_ajax_submit != 'undefined') { bf_ajax_submit() } else { document." . $this->form_id . ".submit(); }" . nl();
        $code .= "} // ff_submitForm";
        $library[] = array('ff_submitForm', $code);

        $library[] = array(
            'ff_validationFocus',
            "function ff_validationFocus(name)" . nl() .
            "{" . nl() .
            "    if (name==undefined || name=='') {" . nl() .
            "        // set focus if name of first failing element was set" . nl() .
            "        if (ff_validationFocusName!='') {" . nl() .
            "            ff_switchpage(ff_getPageByName(ff_validationFocusName));" . nl() .
            "            if(ff_getElementByName(ff_validationFocusName).focus){" . nl() .
            "	            ff_getElementByName(ff_validationFocusName).focus();" . nl() .
            "			 }" . nl() .
            "        } // if" . nl() .
            "    } else {" . nl() .
            "        // store name if this is the first failing element" . nl() .
            "        if (ff_validationFocusName=='')" . nl() .
            "            ff_validationFocusName = name;" . nl() .
            "    } // if" . nl() .
            "} // ff_validationFocus"
        );

        $code = "function ff_validation(page)" . nl() .
            "{" . nl() .
            "    if(typeof inlineErrorElements != 'undefined') inlineErrorElements = new Array();" . nl() .
            "    error = '';" . nl() .
            "    ff_validationFocusName = '';" . nl();
        $curr = -1;
        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            $funcname = '';
            switch ($row->script3cond) {
                case 1:
                    $database->setQuery(
                        "select name from #__facileforms_scripts " .
                        "where id=" . $row->script3id . " and published=1 "
                    );
                    $funcname = $database->loadResult();
                    break;
                case 2:
                    $funcname = 'ff_' . $row->name . '_validation';
                    break;
                default:
                    break;
            } // switch
            if ($funcname != '') {
                if ($row->page != $curr) {
                    if ($curr > 0)
                        $code .= "    } // if" . nl();
                    $code .= "    if (page==" . $row->page . " || page==0) {" . nl();
                    $curr = $row->page;
                } // if
                if ($this->trim($row->script3msg)) {
                    $msg = addslashes($row->script3msg) . "\\n";
                    $res_msg = '';
                    $this->getFieldTranslated('validationMessage', $row->name, $res_msg);
                    if ($res_msg != '') {
                        $msg = $res_msg . "\\n";
                    }
                } else {
                    $msg = "";
                }
                $code .= " if( typeof bfDeactivateField == 'undefined' || !bfDeactivateField['ff_nm_" . $row->name . "[]'] ){ " . nl();
                $code .= "        errorout = " . $funcname . "(document." . $this->form_id . "['ff_nm_" . $row->name . "[]'],\"" . $msg . "\");" . nl();
                $code .= "        error += errorout" . nl();
                $code .= "        if(typeof inlineErrorElements != 'undefined'){" . nl();
                $code .= "             inlineErrorElements.push([\"" . $row->name . "\",errorout]);" . nl();
                $code .= "        }" . nl();
                $code .= "}" . nl();
            } // if
        } // for
        if ($curr > 0)
            $code .= "    } // if" . nl();
        $code .= 'if(error != "" && document.getElementById(\'ff_capimgValue\')){
                 document.getElementById(\'ff_capimgValue\').src = \'' . Uri::root(true) . (Factory::getApplication()->isClient('administrator') ? '/administrator' : '') . '/components/com_breezingforms/images/captcha/securimage_show.php?bfMathRandom=\' + Math.random();
                 document.getElementById(\'bfCaptchaEntry\').value = "";
            }';
        $code .= 'if(error!="" && document.getElementById("bfSubmitButton")){document.getElementById("bfSubmitButton").disabled = false;}' . nl();
        $code .= 'if(error!="" && typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}' . nl();
        $code .= "    return error;" . nl() .
            "} // ff_validation";
        $library[] = array('ff_validation', $code);

        // ff_initialize
        $code = "function ff_initialize(condition)" . nl() .
            "{" . nl();
        $formentry = false;
        $funcname = '';
        switch ($this->formrow->script1cond) {
            case 1:
                $database->setQuery(
                    "select name from #__facileforms_scripts " .
                    "where id=" . $this->formrow->script1id . " and published=1 "
                );
                $funcname = $database->loadResult();
                break;
            case 2:
                $funcname = 'ff_' . $this->formrow->name . '_init';
                break;
            default:
                break;
        } // switch
        if ($funcname != '') {
            $code .= "    if (condition=='formentry') {" . nl() .
                "        " . $funcname . "();" . nl();
            $formentry = true;
        } // if
        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            $funcname = '';
            switch ($row->script1cond) {
                case 1:
                    $database->setQuery(
                        "select name from #__facileforms_scripts " .
                        "where id=" . $row->script1id . " and published=1 "
                    );
                    $funcname = $database->loadResult();
                    break;
                case 2:
                    $funcname = 'ff_' . $row->name . '_init';
                    break;
                default:
                    break;
            } // switch
            if ($funcname != '') {
                if ($row->script1flag1) {
                    if (!$formentry) {
                        $code .= "    if (condition=='formentry') {" . nl();
                        $formentry = true;
                    } // if
                    $code .= "        " . $funcname . "(document." . $this->form_id . "['ff_nm_" . $row->name . "[]'], condition);" . nl();
                } // if
            } // if
        } // for
        $pageentry = false;
        $curr = -1;
        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            $funcname = '';
            switch ($row->script1cond) {
                case 1:
                    $database->setQuery(
                        "select name from #__facileforms_scripts " .
                        "where id=" . $row->script1id . " and published=1 "
                    );
                    $funcname = $database->loadResult();
                    break;
                case 2:
                    $funcname = 'ff_' . $row->name . '_init';
                    break;
                default:
                    break;
            } // switch
            if ($funcname != '') {
                if ($row->script1flag2) { // page entry
                    if ($formentry) {
                        $code .= "    } else" . nl();
                        $formentry = false;
                    } // if
                    if (!$pageentry) {
                        $code .= "    if (condition=='pageentry') {" . nl();
                        $pageentry = true;
                    } // if
                    if ($curr != $row->page) {
                        if ($curr > 0)
                            $code .= "        } // if" . nl();
                        $code .= "        if (ff_currentpage==" . $row->page . ") {" . nl();
                        $curr = $row->page;
                    } // if
                    $code .= "            " . $funcname . "(document." . $this->form_id . ".ff_elem" . $row->id . ", condition);" . nl();
                } // if
            } // if
        } // for
        if ($curr > 0)
            $code .= "        } // if" . nl();
        if ($formentry || $pageentry)
            $code .= "    } // if" . nl();
        $code .= "} // ff_initialize";
        $library[] = array('ff_initialize', $code);

        if ($this->showgrid) {
            if ($this->formrow->widthmode)
                $width = $this->formrow->prevwidth;
            else
                $width = $this->formrow->width;
            $library[] = array(
                'ff_showgrid',
                "var ff_gridvcnt = 0;" . nl() .
                "var ff_gridhcnt = 0;" . nl() .
                "var ff_gridheight = " . $this->formrow->height . ";" . nl() .
                nl() .
                "function ff_showgrid()" . nl() .
                "{" . nl() .
                "   var i, e, s;" . nl() .
                "   var hcnt = parseInt(ff_gridheight / " . $ff_config->gridsize . ")+1;" . nl() .
                "   var vcnt = parseInt(" . $width . " / " . $ff_config->gridsize . ")+1;" . nl() .
                "   var formdiv = document.getElementById('ff_formdiv" . $this->form . "');" . nl() .
                "   var firstelem = formdiv.firstChild;" . nl() .
                "   for (i = ff_gridhcnt; i < hcnt; i++) {" . nl() .
                "       e = document.createElement('div');" . nl() .
                "       e.id = 'ff_gridh'+i;" . nl() .
                "       s = e.style;" . nl() .
                "       s.position = 'absolute';" . nl() .
                "       s.left = '0px';" . nl() .
                "       s.top = (i*" . $ff_config->gridsize . ")+'px';" . nl() .
                "       s.width = '" . $width . "px';" . nl() .
                "       s.fontSize = '0px';" . nl() .
                "       s.lineHeight = '1px';" . nl() .
                "       s.height = '1px';" . nl() .
                "       if (i % 2)" . nl() .
                "           s.background = '" . $ff_config->gridcolor2 . "';" . nl() .
                "       else" . nl() .
                "           s.background = '" . $ff_config->gridcolor1 . "';" . nl() .
                "       formdiv.insertBefore(e,firstelem);" . nl() .
                "   } // for" . nl() .
                "   if (hcnt > ff_gridhcnt) ff_gridhcnt = hcnt;" . nl() .
                "   for (i = 0; i < ff_gridvcnt; i++)" . nl() .
                "       document.getElementById('ff_gridv'+i).style.height = ff_gridheight+'px';" . nl() .
                "   for (i = ff_gridvcnt; i < vcnt; i++) {" . nl() .
                "       e = document.createElement('div');" . nl() .
                "       e.id = 'ff_gridv'+i;" . nl() .
                "       s = e.style;" . nl() .
                "       s.position = 'absolute';" . nl() .
                "       s.left = (i*" . $ff_config->gridsize . ")+'px';" . nl() .
                "       s.top = '0px';" . nl() .
                "       s.width = '1px';" . nl() .
                "       s.height = ff_gridheight+'px';" . nl() .
                "       if (i % 2)" . nl() .
                "           s.background = '" . $ff_config->gridcolor2 . "';" . nl() .
                "       else" . nl() .
                "           s.background = '" . $ff_config->gridcolor1 . "';" . nl() .
                "       formdiv.insertBefore(e,firstelem);" . nl() .
                "   } // for" . nl() .
                "   if (vcnt > ff_gridvcnt) ff_gridvcnt = vcnt;" . nl() .
                "} // ff_showgrid"
            );
        } // if
        // ff_resizePage
        $code = "function ff_resizepage(mode, value)" . nl() .
            "{" . nl() .
            "    var height = 0;" . nl() .
            "    if (mode > 0) {" . nl() .
            "        for (var i = 0; i < ff_elements.length; i++) {" . nl() .
            "            if (mode==2 || ff_elements[i][3]==ff_currentpage) {" . nl() .
            "                e = document.getElementById(ff_elements[i][1]);" . nl() .
            "                if(e){" . nl() .
            "                	h = e.offsetTop+e.offsetHeight;" . nl() .
            "                	if (h > height) height = h;" . nl() .
            "                }" . nl() .
            "            } // if" . nl() .
            "        } // for" . nl() .
            "    } // if" . nl() .
            "    var totheight = height+value;" . nl() .
            "    if ((mode==2 && totheight>ff_currentheight) || (mode!=2 && totheight!=ff_currentheight)) {" . nl();
        if ($this->inframe) {
            $fn = ($this->runmode == _FF_RUNMODE_PREVIEW) ? 'ff_prevframe' : ('ff_frame' . $this->form);
            $code .= "        parent.document.getElementById('" . $fn . "').style.height = totheight+'px';" . nl() .
                "        parent.window.scrollTo(0,0);" . nl() .
                "        document.getElementById('ff_formdiv" . $this->form . "').style.height = height+'px';" . nl() .
                "        window.scrollTo(0,0);" . nl();
        } // if
        else
            $code .= "        document.getElementById('ff_formdiv" . $this->form . "').style.height = totheight+'px';" . nl() .
                "        window.scrollTo(0,0);" . nl();
        $code .= "        ff_currentheight = totheight;" . nl();
        if ($this->showgrid) {
            $code .= "        ff_gridheight = totheight;" . nl() .
                "        ff_showgrid();" . nl();
        } // if
        $code .= "    } // if" . nl() .
            "} // ff_resizepage";
        $library[] = array('ff_resizepage', $code);

        if ($this->formrow->template_code_processed == '') {

            // ff_switchpage
            $code = "function ff_switchpage(page)" . nl() .
                "{;" . nl() .
                "    if (page>=1 && page<=ff_lastpage && page!=ff_currentpage) {" . nl() .
                "        vis = 'visible';" . nl();
            $curr = -1;
            for ($i = 0; $i < $this->rowcount; $i++) {
                $row = $this->rows[$i];
                if ($row->type != "Hidden Input") {
                    if ($row->page != $curr) {
                        if ($curr >= 1)
                            $code .= "        } // if" . nl();
                        $code .= "        if (page==" . $row->page . " || ff_currentpage==" . $row->page . ") {" . nl() .
                            "            if (page==" . $row->page . ") vis = 'visible';  else vis = 'hidden';" . nl();
                        $curr = $row->page;
                    } // if
                    $code .= "            document.getElementById('ff_div" . $row->id . "').style.visibility=vis;" . nl();
                } // if
            } // for
            if ($curr >= 1)
                $code .= "        } // if" . nl();
            $code .= "        ff_currentpage = page;" . nl();
            if ($this->formrow->heightmode == 1)
                $code .= "        ff_resizepage(" . $this->formrow->heightmode . ", " . $this->formrow->height . ");" . nl();
            $code .= "        ff_initialize('pageentry');" . nl() .
                "    } // if" . nl() .
                "} // ff_switchpage";
        } else {
            $visPages = '';
            $pagesSize = isset($this->formrow->pages) ? intval($this->formrow->pages) : 1;
            for ($pageCnt = 1; $pageCnt <= $pagesSize; $pageCnt++) {
                $visPages .= 'if(document.getElementById("bfPage' . $pageCnt . '"))document.getElementById("bfPage' . $pageCnt . '").style.display = "none";';
            }

            $code = 'function ff_switchpage(page){
				' . $visPages . '
				if(document.getElementById("bfPage"+page))document.getElementById("bfPage"+page).style.display = "";
				ff_currentpage = page;
				' . ($this->formrow->heightmode == 1 ? "ff_resizepage(" . $this->formrow->heightmode . ", " . $this->formrow->height . ");" : "") . '
				ff_initialize("pageentry");
			}';
        }

        $library[] = array('ff_switchpage', $code);
    }

    // loadBuiltins

    function loadScripts(&$library)
    {
        global $database;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        if ($this->dying)
            return;
        $database->setQuery(
            "select id, name, code from #__facileforms_scripts " .
            "where published=1 " .
            "order by type, title, name, id desc"
        );
        $rows = $database->loadObjectList();
        $cnt = count($rows);
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $library[] = array(trim($row->name), $row->code, 's', $row->id, null);
        } // if
    }

    // loadScripts

    function compressJavascript($str)
    {
        if ($this->dying)
            return '';
        $str = str_replace("\r", "", $str);
        $lines = explode("\n", $str);
        $code = '';
        $skip = '';
        $lcnt = 0;
        if (count($lines))
            foreach ($lines as $line) {
                $ll = strlen($line);
                $quote = '';
                $ws = false;
                $escape = false;
                for ($j = 0; $j < $ll; $j++) {
                    $c = substr($line, $j, 1);
                    $d = substr($line, $j, 2);
                    if ($quote != '') {
                        // in literal
                        if ($escape) {
                            $code .= $c;
                            $lcnt++;
                            $escape = false;
                        } else
                            if ($c == "\\") {
                                $code .= $c;
                                $lcnt++;
                                $escape = true;
                            } else
                                if ($d == $quote . $quote) {
                                    $code .= $d;
                                    $lcnt += 2;
                                    $j += 2;
                                } else {
                                    $code .= $c;
                                    $lcnt++;
                                    if ($c == $quote)
                                        $quote = '';
                                } // if
                    } else {
                        // not in literal
                        if ($d == $skip) {
                            $skip = '';
                            $j += 2;
                        } else
                            if ($skip == '') {
                                if ($d == '/*') {
                                    $skip = '*/';
                                    $j += 2;
                                } else
                                    if ($d == '//')
                                        break;
                                    else
                                        switch ($c) {
                                            case ' ':
                                            case "\t":
                                            case "\n":
                                                if ($lcnt)
                                                    $ws = true;
                                                break;
                                            case '"':
                                            case "'":
                                                if ($ws) {
                                                    $b = substr($code, strlen($code) - 1, 1);
                                                    if ($b == '_' || ($b >= '0' && $b <= '9') || ($b >= 'a' && $b <= 'z') || ($b >= 'A' && $b <= 'Z')) {
                                                        $code .= ' ';
                                                        $lcnt++;
                                                    } // if
                                                    $ws = false;
                                                } // if
                                                $quote = $c;
                                                $code .= $c;
                                                $lcnt++;
                                                break;
                                            default:
                                                if ($ws) {
                                                    if ($c == '_' || ($c >= '0' && $c <= '9') || ($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z')) {
                                                        $b = substr($code, strlen($code) - 1, 1);
                                                        if ($b == '_' || ($b >= '0' && $b <= '9') || ($b >= 'a' && $b <= 'z') || ($b >= 'A' && $b <= 'Z')) {
                                                            $code .= ' ';
                                                            $lcnt++;
                                                        } // if
                                                    } // if
                                                    $ws = false;
                                                } // if
                                                $code .= $c;
                                                $lcnt++;
                                        } // switch
                            } // if
                    } // else
                } // for
                if ($lcnt) {
                    if ($lcnt > _FF_PACKBREAKAFTER) {
                        $code .= nl();
                        $lcnt = 0;
                    } else {
                        if (strpos(',;:{}=[(+-*%', substr($code, strlen($code) - 1, 1)) === false) {
                            $code .= nl();
                            $lcnt = 0;
                        } // if
                    } // if
                } // if
            } // foreach
        if ($lcnt)
            $code .= nl();
        return $code;
    }

    // compressJavascript

    function linkcode($func, &$library, &$linked, $code, $type = null, $id = null, $pane = null)
    {
        global $ff_config;

        if ($this->dying)
            return;
        if ($func != '#scanonly') {
            // check if function allready linked
            if (in_array($func, $linked))
                return;
            // remember me
            $linked[] = $func;
        } // if
        // scan the code for library identifiers
        preg_match_all("/[A-Za-z0-9_]+/s", $code, $matches, PREG_PATTERN_ORDER);
        $idents = $matches[0];
        $cnt = count($library);
        for ($i = 0; $i < $cnt; $i++) {
            $libname = $library[$i][0];
            if ($libname != '' && in_array($libname, $idents)) {
                $library[$i][0] = ''; // invalidate
                $ltype = $lid = $lpane = null;
                if (count($library[$i]) > 4) {
                    $ltype = $library[$i][2];
                    $lid = $library[$i][3];
                    $lpane = $library[$i][4];
                } // if
                $this->linkcode($libname, $library, $linked, $library[$i][1], $ltype, $lid, $lpane);
                if ($this->dying)
                    return '';
            } // if
        } // for

        if ($func != '#scanonly') {
            // emit the code
            if ($ff_config->compress)
                echo $this->compressJavascript(
                    $this->replaceCode($code, BFText::_('COM_BREEZINGFORMS_PROCESS_SCRIPT') . " $func", $type, $id, $pane)
                );
            else
                echo $this->replaceCode($code, BFText::_('COM_BREEZINGFORMS_PROCESS_SCRIPT') . " $func", $type, $id, $pane) . nl() . nl();
        } // if
    }

    // linkcode

    function addFunction($cond, $id, $name, $code, &$library, &$linked, $type, $rowid, $pane)
    {
        global $database;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        if ($this->dying)
            return;
        switch ($cond) {
            case 1:
                $database->setQuery(
                    "select name, code from #__facileforms_scripts " .
                    "where id=" . $database->Quote($id) . " and published=1"
                );
                $rows = $database->loadObjectList();
                if (count($rows) > 0) {
                    $row = $rows[0];
                    if ($this->trim($row->name) && $this->nonblank($row->code)) {
                        $this->linkcode($row->name, $library, $linked, $row->code, 's', $id, null);
                        if ($this->dying)
                            return;
                    } // if
                } // if
                break;
            case 2:
                if ($this->trim($name) && $this->nonblank($code)) {
                    $this->linkcode($name, $library, $linked, $code, $type, $rowid, $pane);
                    if ($this->dying)
                        return;
                } // if
                break;
            default:
                break;
        } // switch
    }

    // addFunction

    function header()
    {
        global $ff_comsite, $ff_config;
        $code = 'ff_processor = new Object();' . nl() .
            $this->expJsVar('ff_processor.okrun      ', $this->okrun) .
            $this->expJsVar('ff_processor.ip         ', $this->ip) .
            $this->expJsVar('ff_processor.agent      ', $this->agent) .
            $this->expJsVar('ff_processor.browser    ', $this->browser) .
            $this->expJsVar('ff_processor.opsys      ', $this->opsys) .
            $this->expJsVar('ff_processor.provider   ', $this->provider) .
            $this->expJsVar('ff_processor.submitted  ', $this->submitted) .
            $this->expJsVar('ff_processor.form       ', $this->form) .
            $this->expJsVar('ff_processor.form_id    ', $this->form_id) .
            $this->expJsVar('ff_processor.page       ', $this->page) .
            $this->expJsVar('ff_processor.target     ', $this->target) .
            $this->expJsVar('ff_processor.runmode    ', $this->runmode) .
            $this->expJsVar('ff_processor.inframe    ', $this->inframe) .
            $this->expJsVar('ff_processor.inline     ', $this->inline) .
            $this->expJsVar('ff_processor.template   ', $this->template) .
            $this->expJsVar('ff_processor.homepage   ', $this->homepage) .
            $this->expJsVar('ff_processor.mossite    ', $this->mossite) .
            //$this->expJsVar('ff_processor.mospath    ', $this->mospath).
            $this->expJsVar('ff_processor.images     ', $this->images) .
            //$this->expJsVar('ff_processor.uploads    ', $this->uploads).
            $this->expJsVar('ff_processor.border     ', $this->border) .
            $this->expJsVar('ff_processor.align      ', $this->align) .
            $this->expJsVar('ff_processor.top        ', $this->top) .
            $this->expJsVar('ff_processor.suffix     ', $this->suffix) .
            $this->expJsVar('ff_processor.status     ', $this->status) .
            $this->expJsVar('ff_processor.message    ', $this->message) .
            $this->expJsVar('ff_processor.record_id  ', $this->record_id) .
            $this->expJsVar('ff_processor.showgrid   ', $this->showgrid) .
            $this->expJsVar('ff_processor.traceBuffer', $this->traceBuffer);
        return
            '<script type="text/javascript">' . nl() .
            '<!--' . nl() .
            ($ff_config->compress ? $this->compressJavascript($code) : $code) .
            '//-->' . nl() .
            '</script>' . nl() .
            '<script type="text/javascript" src="' . Uri::root(true) . '/components/com_breezingforms/facileforms.js"></script>' . nl();
    }

    // header

    function cbCreatePathByTokens($path, array $rows, $field_name)
    {

        if (strpos(strtolower($path), '{cbsite}') === 0) {
            $path = str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), $path);
        }

        $path = str_replace($this->findtags, $this->replacetags, $path);

        if (strpos($path, '|') === false) {
            return $path;
        }

        $after = str_replace('|', '', stristr($path, '|'));
        $path = stristr($path, '|', true) . '|';
        $path = str_replace('|', DS, $path);

        foreach ($rows as $row) {
            $value = BFRequest::getVar('ff_nm_' . $row->name, array(), 'POST', 'HTML', BFREQUEST_ALLOWHTML);

            $value = implode(DS, $value);
            if (trim($value) == '') {
                $value = '_empty_';
            }
            $path = str_replace('{' . strtolower($row->name) . ':value}', trim($value), $path);
        }

        foreach ($rows as $row) {
            $path = str_replace('{field:' . strtolower($row->name) . '}', strtolower($row->name), $path);
        }

        $path = str_replace('{userid}', Factory::getUser()->get('id', 0), $path);
        $path = str_replace('{username}', Factory::getUser()->get('username', 'anonymous') . '_' . Factory::getUser()->get('id', 0), $path);
        $path = str_replace('{name}', Factory::getUser()->get('name', 'Anonymous') . '_' . Factory::getUser()->get('id', 0), $path);
        $path = str_replace('{field}', File::makeSafe(strtolower(trim($field_name))), $path);

        $tz = 'UTC';
        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

        $date_stamp1 = date('Y_m_d');
        $date_stamp2 = date('H_i_s');
        $date_stamp3 = date('Y_m_d_H_i_s');

        $date_ = Factory::getDate($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $date_stamp1 = $date_->format('Y_m_d', true);
        $date_stamp2 = $date_->format('H_i_s', true);
        $date_stamp3 = $date_->format('Y_m_d_H_i_s', true);

        $_now = Factory::getDate();
        $path = str_replace('{date}', $date_stamp1, $path);
        $path = str_replace('{time}', $date_stamp2, $path);
        $path = str_replace('{datetime}', $date_stamp3, $path);

        $endpath = $this->makeSafeFolder($path);

        $parts = explode(DS, $endpath);
        $inner_path = '';
        foreach ($parts as $part) {
            if (!is_dir($inner_path . $part)) {
                $inner_path .= DS;
            }
            Folder::create($inner_path . $part);
            $inner_path .= $part;
        }
        return $endpath . $after;
    }

    function makeSafeFolder($path)
    {
        //$ds = (DS == '\\') ? '\\/' : DS;
        $regex = array('#[^A-Za-z0-9{}\.:_\\\/-]#');
        return preg_replace($regex, '_', $path);
    }

    function cbCheckPermissions()
    {
        // CONTENTBUILDER BEGIN
        jimport('joomla.filesystem.file');

        $cbData = null;
        $cbForm = null;
        $cbRecord = null;
        $cbFrontend = true;
        $cbFull = false;

        if (file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_contentbuilder' . DS . 'contentbuilder.xml')) {

            if (Factory::getApplication()->isClient('administrator')) {
                $cbFrontend = false;
            }

            if ($cbFrontend) {
                Factory::getApplication()->getLanguage()->load('com_contentbuilder');
            } else {
                Factory::getApplication()->getLanguage()->load('com_contentbuilder', JPATH_SITE . DS . 'administrator');
            }

            $db = Factory::getContainer()->get(DatabaseInterface::class);

            require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');

            $db->setQuery("Select `id` From #__contentbuilder_forms Where `type` = 'com_breezingforms' And `reference_id` = " . intval($this->form) . " And published = 1");

            $cbForms = $db->loadColumn();

            // if no BF form is associated with contentbuilder, we don't need no further checks
            if (!count($cbForms)) {
                return array('form' => $cbForm, 'record' => $cbRecord, 'frontend' => $cbFrontend, 'data' => $cbData, 'full' => $cbFull);
            }

            // test if there is any published contentbuilder view that allows to create new submissions
            if (!BFRequest::getInt('cb_record_id', 0) || !BFRequest::getInt('cb_form_id', 0)) {

                $cbAuth = false;
                foreach ($cbForms as $cbFormId) {
                    contentbuilder::setPermissions($cbFormId, 0, $cbFrontend ? '_fe' : '');
                    if ($cbFrontend) {
                        $cbAuth = contentbuilder::authorizeFe('new');
                    } else {
                        $cbAuth = contentbuilder::authorize('new');
                    }
                    if ($cbAuth) {
                        break;
                    }
                }

                if (count($cbForms) && !$cbAuth) {
                    throw new Exception(Text::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), 403);
                }
            }

            if (BFRequest::getInt('cb_form_id', 0)) {

                // test the permissions of given record
                if (BFRequest::getInt('cb_record_id', 0)) {
                    contentbuilder::setPermissions(BFRequest::getInt('cb_form_id', 0), BFRequest::getInt('cb_record_id', 0), $cbFrontend ? '_fe' : '');
                    contentbuilder::checkPermissions('edit', Text::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), $cbFrontend ? '_fe' : '');
                } else {
                    contentbuilder::setPermissions(BFRequest::getInt('cb_form_id', 0), 0, $cbFrontend ? '_fe' : '');
                    contentbuilder::checkPermissions('new', Text::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), $cbFrontend ? '_fe' : '');
                }

                $db->setQuery("Select * From #__contentbuilder_forms Where id = " . BFRequest::getInt('cb_form_id', 0) . " And published = 1");
                $cbData = $db->loadAssoc();
                if (is_array($cbData)) {
                    $cbFull = $cbFrontend ? contentbuilder::authorizeFe('fullarticle') : contentbuilder::authorize('fullarticle');
                    $cbForm = contentbuilder::getForm('com_breezingforms', $cbData['reference_id']);
                    $cbRecord = $cbForm->getRecord(BFRequest::getInt('cb_record_id', 0), $cbData['published_only'], $cbFrontend ? ($cbData['own_only_fe'] ? Factory::getUser()->get('id', 0) : -1) : ($cbData['own_only'] ? Factory::getUser()->get('id', 0) : -1), $cbFrontend ? $cbData['show_all_languages_fe'] : true);

                    if (!count($cbRecord) && !BFRequest::getBool('cbIsNew')) {
                        throw new Exception(Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'), 404);
                    }
                }
            }
        }
        return array('form' => $cbForm, 'record' => $cbRecord, 'frontend' => $cbFrontend, 'data' => $cbData, 'full' => $cbFull);
        // CONTENTBUILDER END
    }

    function view()
    {
        global $ff_mospath, $ff_mossite, $database, $my;
        global $ff_config, $ff_version, $ff_comsite, $ff_otherparams;

        $is_mobile_type = '';

        if (trim($this->formrow->template_code_processed) == 'QuickMode') {

            if (isset($_GET['non_mobile']) && BFRequest::getBool('non_mobile', 0)) {
                Factory::getSession()->clear('com_breezingforms.mobile');
            } else if (isset($_GET['mobile']) && BFRequest::getBool('mobile', 0)) {
                Factory::getSession()->set('com_breezingforms.mobile', true);
            }

            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/functions/helpers.php');

            $dataObject = Zend_Json::decode(bf_b64dec($this->formrow->template_code));
            $rootMdata = $dataObject['properties'];
            $is_device = false;

            $useragent = 'Unknown';

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $useragent = $_SERVER['HTTP_USER_AGENT'];
            }

            if (BFRequest::getVar('ff_applic', '') != 'mod_facileforms' && BFRequest::getInt('ff_frame', 0) != 1 && bf_is_mobile()) {
                $is_device = true;
                $this->isMobile = isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && $rootMdata['forceMobile'] ? true : (isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && Factory::getSession()->get('com_breezingforms.mobile', false) ? true : false);
            } else {
                $this->isMobile = false;

                if (isset($rootMdata['themebootstrapThemeEngine']) && $rootMdata['themebootstrapThemeEngine'] == 'bootstrap') {
                    $this->legacy_wrap = false;
                }
            }

            if ($is_device && isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && !$rootMdata['forceMobile']) {
                $is_mobile_type = 'choose';
            }

            if (!$this->isMobile || ($this->isMobile && BFRequest::getVar('ff_task', '') == 'submit')) {

                // nothing
            } else {

                if ($this->isMobile) {

                    ob_end_clean();
                    ob_start();
                    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFQuickModeMobile.php');
                    $quickMode = new BFQuickModeMobile($this);
                    if (isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && $rootMdata['forceMobile']) {
                        $quickMode->forceMobileUrl = isset($rootMdata['forceMobileUrl']) ? $rootMdata['forceMobileUrl'] : 'index.php';
                    }
                }
            }
        }

        // CONTENTBUILDER BEGIN
        $cbResult = $this->cbCheckPermissions();

        $cbForm = $cbResult['form'];
        $cbRecord = $cbResult['record'];
        $cbFrontend = $cbResult['frontend'];
        $cbFull = $cbResult['full'];
        // CONTENTBUILDER END

        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $mainframe = Factory::getApplication();
        if (!$this->okrun)
            return;
        set_error_handler('_ff_errorHandler');
        ob_start();
        echo $this->header();
        $this->queryCols = array();
        $this->queryRows = array();
        if ($this->runmode == _FF_RUNMODE_PREVIEW) {
            echo '<script type="text/javascript" src="' . Uri::root() . 'administrator/components/com_breezingforms/libraries/wz_dragdrop/wz_dragdrop.js"></script>';
        }
        if (trim($this->formrow->template_code_processed) == 'QuickMode' && $this->legacy_wrap)
            echo '<table style="display:none;width:100%;" id="bfReCaptchaWrap"><tr><td><div id="bfReCaptchaDiv"></div></td></tr></table>';
        echo '<div id="ff_formdiv' . $this->form . '"';
        echo ' class="bfFormDiv' . ($this->formrow->class1 != '' ? ' ' . $this->getClassName($this->formrow->class1) : '') . '"';
        if ($this->legacy_wrap) {
            echo '><div class="bfPage-tl"><div class="bfPage-tr"><div class="bfPage-t"></div></div></div><div class="bfPage-l"><div class="bfPage-r"><div class="bfPage-m bfClearfix">' . nl();
        } else {
            echo '>';
        }
        $this->status = BFRequest::getCmd('ff_status', '');
        $this->message = BFRequest::getVar('ff_message', '');

        // handle Before Form piece
        $code = '';
        switch ($this->formrow->piece1cond) {
            case 1: // library
                $database->setQuery(
                    'select name, code from #__facileforms_pieces ' .
                    'where id=' . $this->formrow->piece1id . ' and published=1 '
                );
                $rows = $database->loadObjectList();
                if (count($rows))
                    echo $this->execPiece($rows[0]->code, BFText::_('COM_BREEZINGFORMS_PROCESS_BFPIECE') . " " . $rows[0]->name, 'p', $this->formrow->piece1id, null);
                break;
            case 2: // custom code
                echo $this->execPiece($this->formrow->piece1code, BFText::_('COM_BREEZINGFORMS_PROCESS_BFPIECEC'), 'f', $this->form, 2);
                break;
            default:
                break;
        } // switch
        if ($this->bury())
            return;

        $cntFiles = 0;
        $fileExtensionsCheck = 'function checkFileExtensions(){';
        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            if ($row->type == 'File Upload' && trim($this->formrow->template_code) != '') {
                if (trim($row->data2) != '') {
                    $exts = explode(',', $row->data2);
                    $extsCount = count($exts);
                    $fileExtensionsCheck .= 'var ff_elem' . $row->id . 'Exts = false;';
                    for ($x = 0; $x < $extsCount; $x++) {
                        $fileExtensionsCheck .= '
							if(!ff_elem' . $row->id . 'Exts && document.getElementById("ff_elem' . $row->id . '").value.toLowerCase().lastIndexOf(".' . strtolower(trim($exts[$x])) . '") != -1){
								ff_elem' . $row->id . 'Exts = true;
							}else if(!ff_elem' . $row->id . 'Exts && document.getElementById("ff_elem' . $row->id . '").value == ""){
								ff_elem' . $row->id . 'Exts = true;
							}';
                    }
                    $fileExtensionsCheck .= '
					if(!ff_elem' . $row->id . 'Exts){
						if(typeof bfUseErrorAlerts == "undefined"){
							alert("' . addslashes(BFText::_('COM_BREEZINGFORMS_FILE_EXTENSION_NOT_ALLOWED')) . '");
						} else {
							bfShowErrors("' . addslashes(BFText::_('COM_BREEZINGFORMS_FILE_EXTENSION_NOT_ALLOWED')) . '");
						}
						if(ff_currentpage != ' . $row->page . ')ff_switchpage(' . $row->page . ');
                                                if(document.getElementById("bfSubmitButton")){
                                                    document.getElementById("bfSubmitButton").disabled = false;
                                                }
                                                if(typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}
						return false;
					}
					';
                    $cntFiles++;
                }
            }
        }
        $fileExtensionsCheck .= '
			return true;
		}
		';

        $capFunc = 'function bfCheckCaptcha(){if(checkFileExtensions())ff_submitForm2();}';

        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            if ($row->type == "Captcha") {
                $capFunc = '

				function bfAjaxObject101() {
					this.createRequestObject = function() {
						try {
							var ro = new XMLHttpRequest();
						}
						catch (e) {
							var ro = new ActiveXObject("Microsoft.XMLHTTP");
						}
						return ro;
					}
					this.sndReq = function(action, url, data) {
					
						if (action.toUpperCase() == "POST") {
							this.http.open(action,url,true);
							this.http.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							this.http.onreadystatechange = this.handleResponse;
							this.http.send(data);
						}
						else {
							this.http.open(action,url + "?" + data,true);
							this.http.onreadystatechange = this.handleResponse;
							this.http.send(null);
						}
					}
					this.handleResponse = function() {
						if ( me.http.readyState == 4) {
							if (typeof me.funcDone == "function") { me.funcDone();}
							var rawdata = me.http.responseText.split("|");
							for ( var i = 0; i < rawdata.length; i++ ) {
								var item = (rawdata[i]).split("=>");
								if (item[0] != "") {
									if (item[1].substr(0,3) == "%V%" ) {
										document.getElementById(item[0]).value = item[1].substring(3);
									}
									else {
										if(item[1] == "true"){
                                                                                    if(typeof bfDoFlashUpload != \'undefined\'){
                                                                                        bfDoFlashUpload();
                                                                                    } else {
									   		ff_submitForm2();
                                                                                    }
									   } else {
                                                                                if(typeof JQuery != "undefined" && JQuery("#bfSubmitMessage"))
									        {
                                                                                    JQuery("#bfSubmitMessage").css("visibility","hidden");
                                                                                    JQuery("#bfSubmitMessage").css("display","none");
									        }
                                                                                if(typeof bfUseErrorAlerts == "undefined"){
                                                                                    alert("' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '");
									        } else {
                                                                                   if(typeof inlineErrorElements != "undefined"){
                                                                                     inlineErrorElements.push(["bfCaptchaEntry","' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '"]);
                                                                                   }
									           bfShowErrors("' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '");
									        }
                                                                                if(typeof ladda_button != "undefined"){
                                                                                    
                                                                                    bf_restore_submitbutton();
                                                                                }
                                                                                
                                                                                        document.getElementById(\'ff_capimgValue\').src = \'' . Uri::root(true) . (Factory::getApplication()->isClient('administrator') ? '/administrator' : '') . '/components/com_breezingforms/images/captcha/securimage_show.php?bfMathRandom=\' + Math.random();
                                                                                        document.getElementById(\'bfCaptchaEntry\').value = "";
                                                                                        if(ff_currentpage != ' . $row->page . ')ff_switchpage(' . $row->page . ');
                                                                                        document.getElementById(\'bfCaptchaEntry\').focus();
                                                                                        if(document.getElementById("bfSubmitButton")){
                                                                                            document.getElementById("bfSubmitButton").disabled = false;
                                                                                        }
                                                                                        if(typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}
										}
                                                                                
									}
								}
							}
						}
						if ((me.http.readyState == 1) && (typeof me.funcWait == "function")) { me.funcWait(); }
					}
					var me = this;
					this.http = this.createRequestObject();

					var funcWait = null;
					var funcDone = null;
				}

                                function bfCheckCaptcha(){
                                        if(checkFileExtensions()){
                                               var ao = new bfAjaxObject101();
                                               ao.sndReq("get","' . Uri::root(true) . (Factory::getApplication()->isClient('administrator') ? '/administrator' : '') . '/index.php?raw=true&option=com_breezingforms&checkCaptcha=true&Itemid=0&tmpl=component&value="+document.getElementById("bfCaptchaEntry").value,"");
                                        }
                                }';
                break;
            } else if ($row->type == "ReCaptcha") {

                $capFunc = 'var bfReCaptchaLoaded = true;
                                    function bfCheckCaptcha(){
					if(checkFileExtensions()){
                                                function bfValidateCaptcha()
                                                {
                                                    if(typeof bfInvisibleRecaptcha != "undefined" && bfInvisibleRecaptcha === false){
														if(typeof bfDoFlashUpload != \'undefined\'){
															bfDoFlashUpload();
														} else {
															ff_submitForm2();
														}
														return;
                                                    }
                                                    
                                                    if(typeof onloadBFNewRecaptchaCallback == "undefined"){
                                                        challengeField = JQuery("input#recaptcha_challenge_field").val();
                                                        responseField = JQuery("input#recaptcha_response_field").val();
                                                        var html = JQuery.ajax({
                                                        type: "POST",
                                                        url: "' . Route::_("index.php?raw=true&option=com_breezingforms&bfReCaptcha=true&form=" . $this->form . "&Itemid=0&tmpl=component") . '",
                                                        data: "recaptcha_challenge_field=" + challengeField + "&recaptcha_response_field=" + responseField,
                                                        async: false
                                                        }).responseText;

                                                        if (html.replace(/^\s+|\s+$/, "") == "success")
                                                        {
                                                            if(typeof bfDoFlashUpload != \'undefined\'){
                                                                bfDoFlashUpload();
                                                            } else {
                                                                ff_submitForm2();
                                                            }
                                                        }
                                                        else
                                                        {
                                                                if(typeof bfUseErrorAlerts == "undefined"){
                                                                        alert("' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '");
                                                                } else {
                                                                    if(typeof inlineErrorElements != "undefined"){
                                                                        inlineErrorElements.push(["bfReCaptchaEntry","' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '"]);
                                                                    }
                                                                    bfShowErrors("' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '");
                                                                }

                                                                if(ff_currentpage != ' . $row->page . ')ff_switchpage(' . $row->page . ');
                                                                Recaptcha.focus_response_field();

                                                                Recaptcha.reload();

                                                                if(document.getElementById("bfSubmitButton")){
                                                                    document.getElementById("bfSubmitButton").disabled = false;
                                                                }
                                                                if(typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}
                                                                if(typeof ladda_button != "undefined"){
                                                                    bf_restore_submitbutton();
                                                                }
                                                                
                                                        }
                                                    }
                                                    else{
                                                        
                                                        if(typeof bfInvisibleRecaptcha != "undefined"){
                                                        
                                                            grecaptcha.execute();
                                                        }
                                                        
                                                        var gresponse = grecaptcha.getResponse();
                                                        
                                                        if(gresponse == ""){
                                                            
                                                            if(typeof bfInvisibleRecaptcha == "undefined"){
                                                            
	                                                            if(typeof bfUseErrorAlerts == "undefined"){
	                                                                    alert("' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '");
	                                                            } else {
	                                                                if(typeof inlineErrorElements != "undefined"){
	                                                                    inlineErrorElements.push(["bfReCaptchaEntry","' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '"]);
	                                                                }
	                                                                bfShowErrors("' . addslashes(BFText::_('COM_BREEZINGFORMS_CAPTCHA_MISSING_WRONG')) . '");
	                                                            }
                                                            
                                                            
                                                                if(ff_currentpage != ' . $row->page . ')ff_switchpage(' . $row->page . ');
                                                            }
                                                            if(document.getElementById("bfSubmitButton")){
                                                                document.getElementById("bfSubmitButton").disabled = false;
                                                            }
                                                            if(typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}
                                                            if(typeof ladda_button != "undefined"){
                                                                bf_restore_submitbutton();
                                                            }
                                                            
                                                            
                                                        }else{
               
                                                            if(typeof bfDoFlashUpload != \'undefined\'){
                                                                bfDoFlashUpload();
                                                            } else {
                                                                ff_submitForm2();
                                                            }
                                                        }
                                                    }
                                                }

                                                bfValidateCaptcha();

					}
				}';
            }
        }

        echo
            '<script type="text/javascript">' . nl() .
            '<!--' . nl() .
            '' . nl() .
            $fileExtensionsCheck .
            $capFunc;

        // create library list
        $library = array();
        $this->loadBuiltins($library);
        $this->loadScripts($library);

        // start linking
        $linked = array();

        if ($this->status == '') {
            $code = "onload = function()" . nl() .
                "{" . nl() .
                "    ff_initialize('formentry');" . nl() .
                "    ff_initialize('pageentry');" . nl();
            if ($this->formrow->heightmode)
                $code .= "    ff_resizepage(" . $this->formrow->heightmode . ", " . $this->formrow->height . ");" . nl();
            if ($this->showgrid)
                $code .= "    ff_showgrid();" . nl();
            $code .= "    if (ff_processor && ff_processor.traceBuffer) ff_traceWindow();" . nl() .
                "} // onload";
            $this->linkcode('onload', $library, $linked, $code);
        } else {
            $funcname = "";
            switch ($this->formrow->script2cond) {
                case 1:
                    $database->setQuery(
                        "select name from #__facileforms_scripts " .
                        "where id=" . $this->formrow->script2id . " and published=1 "
                    );
                    $funcname = $database->loadResult();
                    break;
                case 2:
                    $funcname = "ff_" . $this->formrow->name . "_submitted";
                    break;
                default:
                    break;
            } // switch
            if ($funcname != '' || $this->formrow->heightmode || $this->showgrid) {
                $code = "onload = function()" . nl() .
                    "{" . nl();
                if ($this->formrow->heightmode)
                    $code .= "    ff_resizepage(" . $this->formrow->heightmode . ", " . $this->formrow->height . ");" . nl();
                if ($this->showgrid)
                    $code .= "    ff_showgrid();" . nl();
                if ($funcname != '') {
                    $json_return = json_encode($this->message, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
                    if (trim($json_return) == '') {
                        $json_return = '""';
                    }
                    $code .= "    " . $funcname . "(" . $this->status . "," . $json_return . ");" . nl();
                }
                $code .= "} // onload";
                $this->linkcode('onload', $library, $linked, $code);
            } // if
        } // if
        if ($this->bury())
            return;

        // add form scripts
        $this->addFunction(
            $this->formrow->script1cond,
            $this->formrow->script1id,
            'ff_' . $this->formrow->name . '_init',
            $this->formrow->script1code,
            $library,
            $linked,
            'f',
            $this->form,
            1
        );
        if ($this->bury())
            return;
        $this->addFunction(
            $this->formrow->script2cond,
            $this->formrow->script2id,
            'ff_' . $this->formrow->name . '_submitted',
            $this->formrow->script2code,
            $library,
            $linked,
            'f',
            $this->form,
            1
        );
        if ($this->bury())
            return;

        // all element scripts & static text/HTML
        $icons = 0;
        $tooltips = 0;
        $qcheckboxes = 0;
        $qcode = '';

        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = &$this->rows[$i];

            $this->draggableDivIds[] = 'ff_div' . $row->id;

            if ($row->type == "Icon")
                $icons++;
            if ($row->type == "Tooltip")
                $tooltips++;
            if ($row->type == "Query List") {
                if ($row->flag2)
                    $qcheckboxes++;

                // load column definitions
                $this->queryCols['ff_' . $row->id] = array();
                $cols = &$this->queryCols['ff_' . $row->id];
                if ($this->trim($row->data3)) {
                    $cls = explode("\n", $row->data3);
                    for ($c = 0; $c < count($cls); $c++) {
                        if ($cls[$c] != '') {
                            $col = ''; // instead of unset
                            $col = new facileFormsQuerycols;
                            $col->unpack($cls[$c]);
                            $this->compileQueryCol($row, $col);
                            $cols[] = $col;
                        } // if
                    } // for
                } // if
                $colcnt = count($cols);
                $checkbox = 0;
                if ($row->flag2)
                    $checkbox = $row->flag2;
                $header = 0;
                if ($row->flag1)
                    $header = 1;

                // get pagenav
                $pagenav = 1;
                $settings = explode("\n", $row->data1);
                if (count($settings) > 8 && $this->trim($settings[8]))
                    $pagenav = $settings[8];

                // export the javascript parameters
                $qcode .= nl() .
                    'ff_queryCurrPage[' . $row->id . '] = 1;' . nl() .
                    'ff_queryPageSize[' . $row->id . '] = ' . $row->height . ';' . nl() .
                    'ff_queryCheckbox[' . $row->id . '] = ' . $checkbox . ';' . nl() .
                    'ff_queryHeader[' . $row->id . '] = ' . $header . ';' . nl() .
                    'ff_queryPagenav[' . $row->id . '] = ' . $pagenav . ';' . nl() .
                    'ff_queryCols[' . $row->id . '] = [';
                for ($c = 0; $c < $colcnt; $c++) {
                    if ($cols[$c]->thspan > 0)
                        $qcode .= '1';
                    else
                        $qcode .= '0';
                    if ($c < $colcnt - 1)
                        $qcode .= ',';
                } // for
                $qcode .= '];' . nl();

                // execute the query and export it to javascript
                $this->queryRows['ff_' . $row->id] = array();
                $this->execQuery($row, $this->queryRows['ff_' . $row->id], $cols);
                $qcode .= 'ff_queryRows[' . $row->id . '] = ' . $this->expJsValue($this->queryRows['ff_' . $row->id]) . ';' . nl();

                unset($cols);
                if ($this->bury())
                    return;
            } // if
            $this->addFunction(
                $row->script1cond,
                $row->script1id,
                'ff_' . $row->name . '_init',
                $row->script1code,
                $library,
                $linked,
                'e',
                $row->id,
                1
            );
            if ($this->bury()) {
                unset($row);
                return;
            }
            $this->addFunction(
                $row->script2cond,
                $row->script2id,
                'ff_' . $row->name . '_action',
                $row->script2code,
                $library,
                $linked,
                'e',
                $row->id,
                1
            );
            if ($this->bury()) {
                unset($row);
                return;
            }
            $this->addFunction(
                $row->script3cond,
                $row->script3id,
                'ff_' . $row->name . '_validate',
                $row->script3code,
                $library,
                $linked,
                'e',
                $row->id,
                1
            );
            if ($this->bury()) {
                ob_end_clean();
                return;
            }
            if ($row->type == 'Static Text/HTML')
                $this->linkcode('#scanonly', $library, $linked, $row->data1);
            unset($row);
            if ($this->bury())
                return;
        } // for

        if ($icons > 0) {
            $this->linkcode(
                'ff_hideIconBorder',
                $library,
                $linked,
                'function ff_hideIconBorder(element)' . nl() .
                '{' . nl() .
                '    element.style.border = "none";' . nl() .
                '} // ff_hideIconBorder'
            );
            if ($this->bury())
                return;
            $this->linkcode(
                'ff_dispIconBorder',
                $library,
                $linked,
                'function ff_dispIconBorder(element)' . nl() .
                '{' . nl() .
                '    element.style.border = "1px outset";' . nl() .
                '} // ff_dispIconBorder'
            );
            if ($this->bury())
                return;
        } // if

        if ($qcode != '') {
            $library[] = array('ff_queryCurrPage', 'var ff_queryCurrPage = new Array();');
            $library[] = array('ff_queryPageSize', 'var ff_queryPageSize = new Array();');
            $library[] = array('ff_queryCols', 'var ff_queryCols = new Array();');
            $library[] = array('ff_queryCheckbox', 'var ff_queryCheckbox = new Array();');
            $library[] = array('ff_queryHeader', 'var ff_queryHeader = new Array();');
            $library[] = array('ff_queryPagenav', 'var ff_queryPagenav = new Array();');
            $library[] = array('ff_queryRows', 'var ff_queryRows = new Array();' . nl() . $qcode);

            $library[] = array(
                'ff_selectAllQueryRows',
                'function ff_selectAllQueryRows(id,checked)' . nl() .
                '{' . nl() .
                '    if (!ff_queryCheckbox[id]) return;' . nl() .
                '    var cnt = ff_queryRows[id].length;' . nl() .
                '    var pagesize = ff_queryPageSize[id];' . nl() .
                '    if (pagesize > 0) {' . nl() .
                '        lastpage = parseInt((cnt+pagesize-1)/pagesize);' . nl() .
                '        if (lastpage == 1)' . nl() .
                '           pagesize = cnt;' . nl() .
                '        else {' . nl() .
                '            var currpage = ff_queryCurrPage[id];' . nl() .
                '            var p;' . nl() .
                '            for (p = 1; p < currpage; p++) cnt -= pagesize;' . nl() .
                '            if (cnt > pagesize) cnt = pagesize;' . nl() .
                '        } // if' . nl() .
                '    } // if' . nl() .
                '    var curr;' . nl() .
                '    for (curr = 0; curr < cnt; curr++)' . nl() .
                '        document.getElementById(\'ff_cb\'+id+\'_\'+curr).checked = checked;' . nl() .
                '    for (curr = cnt; curr < pagesize; curr++)' . nl() .
                '        document.getElementById(\'ff_cb\'+id+\'_\'+curr).checked = false;' . nl() .
                '    if (ff_queryCheckbox[id]==1)' . nl() .
                '        document.getElementById(\'ff_cb\'+id).checked = checked;' . nl() .
                '} // ff_selectAllQueryRows'
            );

            $code = 'function ff_dispQueryPage(id,page)' . nl() .
                '{' . nl() .
                '    var forced = false;' . nl() .
                '    if (arguments.length>2) forced = arguments[2];' . nl() .
                '    var qrows = ff_queryRows[id];' . nl() .
                '    var cnt = qrows.length;' . nl() .
                '    var currpage = ff_queryCurrPage[id];' . nl() .
                '    var pagesize = ff_queryPageSize[id];' . nl() .
                '    var pagenav = ff_queryPagenav[id];' . nl() .
                '    var lastpage = 1;' . nl() .
                '    if (pagesize > 0) {' . nl() .
                '        lastpage = parseInt((cnt+pagesize-1)/pagesize);' . nl() .
                '        if (lastpage == 1) pagesize = cnt;' . nl() .
                '    } // if' . nl() .
                '    if (page < 1) page = 1;' . nl() .
                '    if (page > lastpage) page = lastpage;' . nl() .
                '    if (!forced && page == currpage) return;' . nl() .
                '    var p, c;' . nl() .
                '    for (p = 1; p < page; p++) cnt -= pagesize;' . nl() .
                '    if (cnt > pagesize) cnt = pagesize;' . nl() .
                '    var start = (page-1) * pagesize;' . nl() .
                '    var rows = document.getElementById(\'ff_elem\'+id).rows;' . nl() .
                '    var cols = ff_queryCols[id];' . nl() .
                '    var checkbox = ff_queryCheckbox[id];' . nl() .
                '    var header = ff_queryHeader[id];' . nl() .
                '    for (p = 0; p < cnt; p++) {' . nl() .
                '        var qrow = qrows[start+p];' . nl() .
                '        var row = rows[header+p];' . nl() .
                '        var cc = 0;' . nl() .
                '        for (c = 0; c < cols.length; c++)' . nl() .
                '            if (cols[c]) {' . nl() .
                '                if (c==0 && checkbox>0) {' . nl() .
                '                    document.getElementById(\'ff_cb\'+id+\'_\'+p).value = qrow[c];' . nl() .
                '                    cc++;' . nl() .
                '                } else' . nl() .
                '                    row.cells[cc++].innerHTML = qrow[c];' . nl() .
                '            } // if' . nl() .
                '        row.style.display = \'\';' . nl() .
                '    } // for' . nl() .
                '    for (p = cnt; p < pagesize; p++) {' . nl() .
                '        var row = rows[p+header];' . nl() .
                '        row.style.display = \'none\';' . nl() .
                '    } // for' . nl() .
                '    if (pagenav > 0 && pagesize > 0) {' . nl() .
                '        var navi = \'\';' . nl() .
                '        if (pagenav<=4) {' . nl() .
                '            if (page>1) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',1);">\';' . nl() .
                '            navi += \'&lt;&lt;\';' . nl() .
                '            if (pagenav<=2) navi += \' ' . BFText::_('COM_BREEZINGFORMS_PROCESS_PAGESTART') . '\';' . nl() .
                '            if (page>1) navi += \'<\/a>\';' . nl() .
                '            navi += \' \';' . nl() .
                '            if (page>1) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',\'+(page-1)+\');">\';' . nl() .
                '            navi += \'&lt;\';' . nl() .
                '            if (pagenav<=2) navi += \' ' . BFText::_('COM_BREEZINGFORMS_PROCESS_PAGEPREV') . '\';' . nl() .
                '            if (page>1) navi += \'<\/a>\';' . nl() .
                '            navi += \' \';' . nl() .
                '        } // if' . nl() .
                '        if (pagenav % 2) {' . nl() .
                '            for (p = 1; p <= lastpage; p++)' . nl() .
                '                if (p == page) ' . nl() .
                '                    navi += p+\' \';' . nl() .
                '                else' . nl() .
                '                    navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',\'+p+\');">\'+p+\'<\/a> \';' . nl() .
                '        } // if' . nl() .
                '        if (pagenav<=4) {' . nl() .
                '            if (page<lastpage) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',\'+(page+1)+\');">\';' . nl() .
                '            if (pagenav<=2) navi += \'' . BFText::_('COM_BREEZINGFORMS_PROCESS_PAGENEXT') . ' \';' . nl() .
                '            navi += \'&gt;\';' . nl() .
                '            if (page<lastpage) navi += \'<\/a>\';' . nl() .
                '            navi += \' \';' . nl() .
                '            if (page<lastpage) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',\'+lastpage+\');">\';' . nl() .
                '            if (pagenav<=2) navi += \'' . BFText::_('COM_BREEZINGFORMS_PROCESS_PAGEEND') . ' \';' . nl() .
                '            navi += \'&gt;&gt;\';' . nl() .
                '            if (page<lastpage) navi += \'<\/a>\';' . nl() .
                '        } // if' . nl() .
                '        rows[header+pagesize].cells[0].innerHTML = navi;' . nl() .
                '    } // if' . nl() .
                '    ff_queryCurrPage[id] = page;' . nl();
            if ($qcheckboxes)
                $code .= '    if (checkbox) ff_selectAllQueryRows(id, false);' . nl();
            if ($this->formrow->heightmode > 0)
                $code .= '    ff_resizepage(' . $this->formrow->heightmode . ', ' . $this->formrow->height . ');' . nl();
            if ($this->inframe)
                $code .= '    parent.window.scrollTo(0,0);' . nl();
            $code .= '    window.scrollTo(0,0);' . nl() .
                '} // ff_dispQueryPage';
            $this->linkcode('ff_dispQueryPage', $library, $linked, $code);
            if ($this->bury())
                return;
        } // if

        echo '//-->' . nl() .
            '</script>' . nl();

        if ($icons > 0)
            echo '<script language="JavaScript" src="' . $ff_mossite . '/components/com_breezingforms/libraries/js/joomla.javascript.js" type="text/javascript"></script>' . nl();
        if ($tooltips > 0) {
            echo '<script language="Javascript" src="' . $ff_mossite . '/components/com_breezingforms/libraries/js/overlib_mini.js" type="text/javascript"></script>' . nl();
            if ($this->inframe)
                echo '<div id="overDiv" style="position:absolute;visibility:hidden;z-index:1000;"></div>' . nl();
        } // if

        if (!$this->inline) {
            $current_url = Uri::getInstance()->toString();

            $url = ($this->inframe) ? $ff_mossite . '/index.php?format=html&tmpl=component' : (($this->runmode == _FF_RUNMODE_FRONTEND) ? $current_url : 'index.php?format=html' . (BFRequest::getCmd('tmpl', '') ? '&tmpl=' . BFRequest::getCmd('tmpl', '') : $current_url));
            $params = ' action="' . $url . '"' .
                ' method="post"' .
                ' name="' . $this->form_id . '"' .
                ' id="' . $this->form_id . '"' .
                ' enctype="multipart/form-data"';
            if ($this->formrow->class2 != '')
                $params .= ' class="' . $this->getClassName($this->formrow->class2) . '"';
            echo '<form data-ajax="false" ' . $params . ' accept-charset="utf-8" onsubmit="return false;" class="bfQuickMode">' . nl();
        } // if

        $js = '';
        $cbJs = '';

        if ($this->editable && $cbRecord === null) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->setQuery("Select id, form From #__facileforms_records Where form = " . $db->Quote($this->form) . " And user_id = " . $db->Quote(Factory::getUser()->get('id', -1)) . " And user_id <> 0 And archived = 0 Order By id Desc Limit 1");
            $recordsResult = $db->loadObjectList();
            if (count($recordsResult) != 0) {
                $this->record_id = $recordsResult[0]->id;
                $db->setQuery("Select * From #__facileforms_subrecords Where record = " . $recordsResult[0]->id . "");
                $recordEntries = $db->loadObjectList();
                $js = '';
                foreach ($recordEntries as $recordEntry) {

                    //$recordEntry->value = $this->removeDangerousHtml($recordEntry->value);

                    /*
                      $input = Factory::getApplication()->input;
                      $input->set('cbCleanVar', $recordEntry->value);
                      $recordEntry->value = $input->getHtml('cbCleanVar'); */

                    BFRequest::setVar('bfCleanVar', $recordEntry->value, 'POST');
                    $recordEntry->value = BFRequest::getVar('bfCleanVar', '', 'POST', 'HTML', BFREQUEST_ALLOWHTML);

                    switch ($recordEntry->type) {
                        case 'Textarea':
                        case 'Text':
                        case 'Hidden Input':
                        case 'Number Input':
                        case 'Calendar':

                            /*
                              if($recordEntry->type == 'Textarea'){

                              $dataObject = Zend_Json::decode( bf_b64dec($this->formrow->template_code) );
                              $qmelement = $this->findQuickModeElement($dataObject, $recordEntry->name);

                              if(isset($recordEntry->value) && $qmelement !== null && isset($qmelement['properties']['is_html']) && $qmelement['properties']['is_html']) {

                              $recordEntry->value = $this->removeDangerousHtml($recordEntry->value);
                              }
                              } */

                            $js .= 'if(typeof JQuery != "undefined"){JQuery("[name=\"ff_nm_' . $recordEntry->name . '[]\"]").val(' . json_encode($recordEntry->value) . ');if(typeof JQuery != "undefined")JQuery("[name=\"ff_nm_' . $recordEntry->name . '[]\"]").trigger("change");}else{';
                            $js .= 'if(document.getElementById("ff_elem' . $recordEntry->element . '"))document.getElementById("ff_elem' . $recordEntry->element . '").value=' . json_encode($recordEntry->value) . ';if(typeof JQuery != "undefined")JQuery(document.getElementById("ff_elem' . $recordEntry->element . '")).trigger("change");' . "\n";
                            $js .= '}';
                            break;
                        case 'Checkbox':
                            if (!empty($recordEntry->value)) {
                                $js .= 'if(document.getElementById("ff_elem' . $recordEntry->element . '") && !JQuery(document.getElementById("ff_elem' . $recordEntry->element . '")).attr("checked"))JQuery(document.getElementById("ff_elem' . $recordEntry->element . '")).click();' . "\n";
                            }
                            break;
                        case 'Checkbox Group':
                            $js .= '
							for(var i = 0;i < document.ff_form' . $this->form . '.elements.length;i++){
								if(document.ff_form' . $this->form . '.elements[i].type == "checkbox" && document.ff_form' . $this->form . '.elements[i].name == "ff_nm_' . $recordEntry->name . '[]" && document.ff_form' . $this->form . '.elements[i].value == ' . json_encode($recordEntry->value) . '){
									if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $this->form . '.elements[i]).attr("checked")){
									    JQuery(document.ff_form' . $this->form . '.elements[i]).click();
									}
								}
							}' . "\n";
                            break;
                        case 'Radio Button':
                        case 'Radio Group':
                            $js .= '
							for(var i = 0;i < document.ff_form' . $this->form . '.elements.length;i++){
								if(document.ff_form' . $this->form . '.elements[i].type == "radio" && document.ff_form' . $this->form . '.elements[i].name == "ff_nm_' . $recordEntry->name . '[]" && document.ff_form' . $this->form . '.elements[i].value == ' . json_encode($recordEntry->value) . '){
									if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $this->form . '.elements[i]).attr("checked")){
									    JQuery(document.ff_form' . $this->form . '.elements[i]).click();
									}
								}
							}' . "\n";
                            break;
                        case 'Select List':
                            $js .= 'for(var i = 0; i < document.getElementById("ff_elem' . $recordEntry->element . '").options.length; i++){
								if(document.getElementById("ff_elem' . $recordEntry->element . '").options[i].value == ' . json_encode($recordEntry->value) . '){
									if(typeof JQuery != "undefined" && !JQuery(document.getElementById("ff_elem' . $recordEntry->element . '").options[i]).attr("selected")){
									    JQuery(document.getElementById("ff_elem' . $recordEntry->element . '").options[i]).attr("selected", true).trigger("change");
									}
								}
							}' . "\n";
                            break;
                    }
                }

                echo '
				<script type="text/javascript">
                                <!--' . nl() . '
                                function bfLoadEditable(){
                                    ' . $js . '
                                    // legacy seccode removal
                                    for(var i = 0;i < document.ff_form' . $this->form . '.elements.length;i++){
                                            if(document.ff_form' . $this->form . '.elements[i].name == "ff_nm_seccode[]"){
                                                    document.ff_form' . $this->form . '.elements[i].value = "";
                                            }
                                    }
                                }
                                ' . nl() . '//-->
				</script>
				' . nl();
            }
        }

        // CONTENTBUILDER BEGIN

        if ($cbRecord !== null) {

            require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
            $cbNonEditableFields = contentbuilder::getListNonEditableElements($cbResult['data']['id']);
            $cbFlashUploadValidationOverride = '';
            foreach ($cbRecord as $cbEntry) {
                if (!in_array($cbEntry->recElementId, $cbNonEditableFields)) {

                    //$cbEntry->recValue = $this->removeDangerousHtml($cbEntry->recValue);

                    /*
                      $input = Factory::getApplication()->input;
                      $input->set('cbCleanVar', $cbEntry->recValue);
                      $cbEntry->recValue = $input->getHtml('cbCleanVar'); */

                    BFRequest::setVar('bfCleanVar', $cbEntry->recValue, 'POST');
                    $cbEntry->recValue = BFRequest::getVar('bfCleanVar', '', 'POST', 'HTML', BFREQUEST_ALLOWHTML);

                    switch ($cbEntry->recType) {
                        case 'File Upload':
                            if (trim($this->formrow->template_code_processed) == 'QuickMode') {

                                if ($cbFlashUploadValidationOverride == '') {
                                    $cbJs .= '
                                            function ff_flashupload_not_empty(element, message)
                                            {
                                                if(typeof bfSummarizers == "undefined") { alert("Flash upload validation only available in QuickMode!"); return ""}
                                                if(JQuery("#bfFlashFileQueue"+element.id.split("ff_elem")[1]).html() != "" || cbFlashElemCnt[element.id] != 0 ) return "";
                                                if (message=="") message = "Please enter "+element.name+".\n";
                                                ff_validationFocus(element.name);
                                                return message;
                                            }
                                            ';
                                }

                                require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder_helpers.php');
                                $cbOut = '';
                                $cbFiles = explode("\n", str_replace("\r", "", $cbEntry->recValue));
                                $i = 0;
                                $cnt = count($cbFiles);
                                $cbJs .= '
                                    cbFlashElemCnt["ff_elem' . $cbEntry->recElementId . '"] = ' . $cnt . ';
                                ';
                                $cbDeac = '';
                                foreach ($cbFiles as $cbFile) {
                                    if (trim($cbFile)) {
                                        $cbOut .= '<div><input type=\"checkbox\" onchange=\"bfCheckUploadValidation(\'ff_elem' . $cbEntry->recElementId . '\', this, \'ff_nm_' . $cbEntry->recName . '[]\')\" value=\"1\" name=\"cb_delete_' . $cbEntry->recElementId . '[' . $i . ']\" id=\"cb_delete_' . $cbEntry->recElementId . '_' . $i . '\"/> <label style=\"margin-left: 5px; float: none !important; display: inline !important;\" for=\"cb_delete_' . $cbEntry->recElementId . '_' . $i . '\">' . addslashes(basename(contentbuilder_wordwrap($cbFile->recValue, 150, "<br>", true))) . '</label></div>';
                                        if ($cbDeac == '') {
                                            $cbDeac = 'bfDeactivateField["ff_nm_' . $cbEntry->recName . '[]"]=true;' . nl();
                                        }
                                        $i++;
                                    }
                                }
                                $js .= $cbDeac;
                                $js .= '
                                                    if (document.createTextNode){
                                                        if(!document.getElementById("bfFlashFileQueue' . $cbEntry->recElementId . '")){
                                                           var mydiv = document.createElement("div");
                                                           mydiv.innerHTML = "<br/>' . $cbOut . '";
                                                           JQuery("#ff_elem' . $cbEntry->recElementId . '_files").append(mydiv);
                                                        } else {
                                                           var mydiv = document.createElement("div");
                                                           mydiv.innerHTML = "' . $cbOut . '";
                                                           mydiv.innerHTML = "<br/>" + mydiv.innerHTML;
                                                           JQuery("#bfFlashFileQueue' . $cbEntry->recElementId . '").after(mydiv);
                                                        }
                                                    }' . nl();
                            }
                            break;
                        case 'Signature':

                            $sig_path = JPATH_SITE . '/media/breezingforms/signatures/';

                            if (strlen($cbEntry->recValue) > 0 && file_exists($sig_path . $cbEntry->recValue)) {

                                $sig_encoded = bf_b64enc(BFFile::read($sig_path . $cbEntry->recValue));

                                $base = 'ba' . 'se' . '64';

                                $js .= '
										JQuery(document).ready(function(){
											if(typeof bf_signaturePad' . $cbEntry->recElementId . ' != "undefined"){
												if(' . (strlen($sig_encoded) > 0 ? 'true' : 'false') . '){
													JQuery("[name=\"ff_nm_' . $cbEntry->recName . '[]\"]").val(' . json_encode('data:image/png;' . $base . ',' . $sig_encoded) . ')
													bf_signaturePad' . $cbEntry->recElementId . '.fromDataURL(' . json_encode('data:image/png;' . $base . ',' . $sig_encoded) . ');
												}
											}
										});';
                            }
                            break;
                        case 'Textarea':
                        case 'Text':
                        case 'Number Input':
                        case 'Hidden Input':
                        case 'Calendar':

                            /*
                              if($cbEntry->recType == 'Textarea'){

                              $dataObject = Zend_Json::decode( bf_b64dec($this->formrow->template_code) );
                              $qmelement = $this->findQuickModeElement($dataObject, $cbEntry->recName);

                              if(isset($cbEntry->recValue) && $qmelement !== null && isset($qmelement['properties']['is_html']) && $qmelement['properties']['is_html']) {

                              $cbEntry->recValue = $this->removeDangerousHtml($cbEntry->recValue);
                              }
                              } */

                            if ($cbEntry->recType == 'Calendar') {
                                $js .= "setTimeout(function(){";
                            }
                            $js .= 'if(typeof JQuery != "undefined"){';
                            $js .= 'JQuery("[name=\"ff_nm_' . $cbEntry->recName . '[]\"]").val(' . json_encode($cbEntry->recValue) . ');if(typeof JQuery != "undefined")JQuery("[name=\"ff_nm_' . $cbEntry->recName . '[]\"]").trigger("change");';
                            $js .= '}else{if(document.getElementById("ff_elem' . $cbEntry->recElementId . '"))document.getElementById("ff_elem' . $cbEntry->recElementId . '").value=' . json_encode($cbEntry->recValue) . ';if(typeof JQuery != "undefined")JQuery(document.getElementById("ff_elem' . $cbEntry->recElementId . '")).trigger("change");}' . nl();
                            if ($cbEntry->recType == 'Calendar') {
                                $js .= "}, 100);";
                            }
                            break;
                        case 'Checkbox':
                        case 'Checkbox Group':
                            $cbValues = explode(',', $cbEntry->recValue);
                            foreach ($cbValues as $cbValue) {
                                $cbValue = trim($cbValue);
                                $js .= '
                                                for(var i = 0;i < document.ff_form' . $this->form . '.elements.length;i++){
                                                        if(document.ff_form' . $this->form . '.elements[i].type == "checkbox" && document.ff_form' . $this->form . '.elements[i].name == "ff_nm_' . $cbEntry->recName . '[]" && document.ff_form' . $this->form . '.elements[i].value == ' . json_encode($cbValue) . '){
                                                                if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $this->form . '.elements[i]).attr("checked")){
                                                                    JQuery(document.ff_form' . $this->form . '.elements[i]).click();
                                                                }
                                                        }
                                                }' . nl();
                            }
                            break;
                        case 'Radio Button':
                        case 'Radio Group':
                            $cbValues = explode(',', $cbEntry->recValue);
                            foreach ($cbValues as $cbValue) {
                                $cbValue = trim($cbValue);
                                $js .= '
                                                for(var i = 0;i < document.ff_form' . $this->form . '.elements.length;i++){
                                                        if(document.ff_form' . $this->form . '.elements[i].type == "radio" && document.ff_form' . $this->form . '.elements[i].name == "ff_nm_' . $cbEntry->recName . '[]" && document.ff_form' . $this->form . '.elements[i].value == ' . json_encode($cbValue) . '){
                                                                if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $this->form . '.elements[i]).attr("checked")){
                                                                    JQuery(document.ff_form' . $this->form . '.elements[i]).click();
                                                                }
                                                        }
                                                }' . nl();
                            }
                            break;
                        case 'Select List':
                            $cbValues = explode(',', $cbEntry->recValue);
                            foreach ($cbValues as $cbValue) {
                                $cbValue = trim($cbValue);
                                $js .= 'for(var i = 0; i < document.getElementById("ff_elem' . $cbEntry->recElementId . '").options.length; i++){
                                                        if(document.getElementById("ff_elem' . $cbEntry->recElementId . '").options[i].value == ' . json_encode($cbValue) . '){
                                                                if(typeof JQuery != "undefined" && !JQuery(document.getElementById("ff_elem' . $cbEntry->recElementId . '").options[i]).attr("selected")){
                                                                    JQuery(document.getElementById("ff_elem' . $cbEntry->recElementId . '").options[i]).attr("selected", true).trigger("change");
                                                                }
                                                        }
                                                }' . nl();
                            }
                            break;
                    }
                }
            }

            echo '
                    <script type="text/javascript">
                    <!--' . nl() . '
                    var cbFlashElemCnt = new Array();
                    function bfCheckUploadValidation(id, obj, deactivatable){
                        if(obj.checked){
                            cbFlashElemCnt[id]--;
                        }else{
                            cbFlashElemCnt[id]++;
                        }
                        if(cbFlashElemCnt[id] == 0){
                            bfDeactivateField[deactivatable]=false;
                        }else{
                            bfDeactivateField[deactivatable]=true;
                        }
                    }
                    ' . $cbJs . '
                    function bfLoadContentBuilderEditable(){
                        ' . $js . '
                        // legacy seccode removal
                        for(var i = 0;i < document.ff_form' . $this->form . '.elements.length;i++){
                                if(document.ff_form' . $this->form . '.elements[i].name == "ff_nm_seccode[]"){
                                        document.ff_form' . $this->form . '.elements[i].value = "";
                                }
                        }
                    }
                    ' . nl() . '//-->
                    </script>
                    ' . nl();
        }

        $cbNonEditableFields = array();
        if ($cbForm !== null) {
            require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
            $cbNonEditableFields = contentbuilder::getListNonEditableElements($cbResult['data']['id']);
            if (count($cbNonEditableFields)) {
                Factory::getDocument()->addScriptDeclaration('<!--' . nl() . 'var bfDeactivateField = new Array();' . nl() . '//-->');
                echo '<script type="text/javascript">' . nl();
                echo '<!--' . nl();
                echo 'function bfDisableContentBuilderFields(){' . nl();
            }
            foreach ($cbNonEditableFields as $cbNonEditableField) {
                echo 'if(typeof document.getElementById("ff_elem' . $cbNonEditableField . '").disabled != "undefined"){' . nl();
                echo 'bfCbName = document.getElementById("ff_elem' . $cbNonEditableField . '").name;' . nl();
                echo 'if(typeof document.getElementsByName != "undefined"){' . nl();
                echo 'bfCbElements = document.getElementsByName(bfCbName);' . nl();
                echo 'for(var i = 0; i < bfCbElements.length; i++){' . nl();
                echo 'if(typeof bfCbElements[i].disabled != "undefined"){' . nl();
                echo 'bfCbElements[i].disabled = true;' . nl();
                echo '}' . nl();
                echo 'bfDeactivateField[bfCbName]=true;' . nl();
                echo 'if(typeof JQuery != "undefined"){ JQuery("#bfElemWrap' . $cbNonEditableField . '").css("display", "none"); }' . nl();
                echo '}' . nl();
                echo '}else{' . nl();
                echo 'document.getElementById("ff_elem' . $cbNonEditableField . '").disabled = true;' . nl();
                echo 'bfDeactivateField[bfCbName]=true;' . nl();
                echo 'if(typeof JQuery != "undefined"){ JQuery("#bfElemWrap' . $cbNonEditableField . '").css("display", "none"); }' . nl();
                echo '}' . nl();
                echo '}' . nl();
            }
            if (count($cbNonEditableFields)) {
                echo '}' . nl();
                echo '//-->' . nl();
                echo '</script>' . nl();
            }
        }

        // CONTENTBUILDER END

        if (trim($this->formrow->template_code_processed) == '') {

            // fixing J3 css
            Factory::getDocument()->addStyleDeclaration(
                '
             .bfFormDiv input[type=checkbox][id^="ff_elem"], input[type=radio][id^="ff_elem"]{
                vertical-align: text-bottom;
             }
             .bfFormDiv input[type=checkbox][id^="ff_elem"] + [id^="ff_lbl"], input[type=radio][id^="ff_elem"] + [id^="ff_lbl"]{
                display: inline;
                vertical-align: text-top;
             }
             '
            );

            for ($i = 0; $i < $this->rowcount; $i++) {
                $row = &$this->rows[$i];
                if (!is_numeric($row->width))
                    $row->width = 0;
                if (!is_numeric($row->height))
                    $row->height = 0;
                if ($row->type != 'Query List') {
                    $data1 = $this->replaceCode($row->data1, "data1 of $row->name", 'e', $row->id, 0);
                    if ($this->bury())
                        return;
                    $data2 = $this->replaceCode($row->data2, "data2 of $row->name", 'e', $row->id, 0);
                    if ($this->bury())
                        return;
                    $data3 = $this->replaceCode($row->data3, "data3 of $row->name", 'e', $row->id, 0);
                    if ($this->bury())
                        return;
                } // if
                $attribs = 'position:absolute;z-index:' . $i . ';';
                if ($row->posx >= 0)
                    $attribs .= 'left:' . $row->posx;
                else
                    $attribs .= 'right:' . (-$row->posx);
                if ($row->posxmode)
                    $attribs .= '%;';
                else
                    $attribs .= 'px;';
                if ($row->posy >= 0)
                    $attribs .= 'top:' . $row->posy;
                else
                    $attribs .= 'bottom:' . (-$row->posy);
                if ($row->posymode)
                    $attribs .= '%;';
                else
                    $attribs .= 'px;';
                $class1 = '';
                $class2 = '';
                if ($row->type == 'Select List') {
                    if ($row->class1 != '')
                        $class1 = ' class="' . $this->getClassName($row->class1) . '"';
                    if ($row->class2 != '')
                        $class2 = ' class="' . $this->getClassName($row->class2) . ' chzn-done"';
                    else
                        $class2 = ' class="chzn-done"';
                } else {
                    if ($row->class1 != '')
                        $class1 = ' class="' . $this->getClassName($row->class1) . '"';
                    if ($row->class2 != '')
                        $class2 = ' class="' . $this->getClassName($row->class2) . '"';
                }
                switch ($row->type) {
                    case 'Static Text/HTML':
                    case 'Rectangle':
                    case 'Image':
                        if ($row->height > 0) {
                            $attribs .= 'height:' . $row->height;
                            if ($row->heightmode)
                                $attribs .= '%;';
                            else
                                $attribs .= 'px;';
                        } // if
                    case 'Query List':
                        if ($row->width > 0) {
                            $attribs .= 'width:' . $row->width;
                            if ($row->widthmode)
                                $attribs .= '%;';
                            else
                                $attribs .= 'px;';
                        } // if
                    default:
                        break;
                } // switch
                if ($row->page != $this->page)
                    $attribs .= 'visibility:hidden;';
                switch ($row->type) {
                    case 'Static Text/HTML':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . $data1 . '</div>' . nl();
                        break;
                    case 'Rectangle':
                        if ($data1 != '')
                            $attribs .= 'border:' . $data1 . ';';
                        if ($data2 != '')
                            $attribs .= 'background-color:' . $data2 . ';';
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="font-size:0px;' . $attribs . '"' . $class1 . '></div>' . nl();
                        break;
                    case 'Image':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= 'width="' . $row->width . '" ';
                        if ($row->height > 0)
                            $attribs .= 'height="' . $row->height . '" ';
                        echo indentc(2) . '<img id="ff_elem' . $row->id . '" src="' . $data1 . '"  alt="' . $data2 . '" border="0" ' . $attribs . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Tooltip':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '" onMouseOver="return overlib(\'' . expstring($data2) . '\',CAPTION,\'' . $row->title . '\',BELOW,RIGHT);" onMouseOut="return nd();"' . $class1 . '>' . nlc();
                        switch ($row->flag1) {
                            case 0:
                                $url = $ff_mossite . '/components/com_breezingforms/images/tooltip.png';
                                break;
                            case 1:
                                $url = $ff_mossite . '/components/com_breezingforms/images/warning.png';
                                break;
                            default:
                                $url = $data1;
                        } // switch
                        echo indentc(2) . '<img src="' . $url . '" alt="" border="0"' . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Hidden Input':
                        echo indentc(1) . '<input id="ff_elem' . $row->id . '" type="hidden" name="ff_nm_' . $row->name . '[]" value="' . $data1 . '" />' . nl();
                        break;
                    case 'Checkbox':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->flag1)
                            $attribs .= ' checked="checked"';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '" type="checkbox" name="ff_nm_' . $row->name . '[]" value="' . $data1 . '"' . $attribs . $class2 . '/><label id="ff_lbl' . $row->id . '" for="ff_elem' . $row->id . '"> ' . $data2 . '</label>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Radio Button':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->flag1)
                            $attribs .= ' checked="checked"';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '" type="radio" name="ff_nm_' . $row->name . '[]" value="' . $data1 . '"' . $attribs . $class2 . '/><label id="ff_lbl' . $row->id . '" for="ff_elem' . $row->id . '"> ' . $data2 . '</label>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Regular Button':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '" type="button" name="ff_nm_' . $row->name . '" value="' . $data2 . '"' . $attribs . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Graphic Button':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->script2clause($row);
                        echo indentc(2) . '<button id="ff_elem' . $row->id . '" type="button" name="ff_nm_' . $row->name . '" value="' . $data2 . '"' . $attribs . $class2 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= 'width="' . $row->width . '" ';
                        if ($row->height > 0)
                            $attribs .= 'height="' . $row->height . '" ';
                        switch ($row->flag1) {
                            case 0: // none
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr><td>' . nlc();
                                echo indentc(5) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '"  alt="' . $data2 . '" border="0" ' . $attribs . '/>' . nlc();
                                echo indentc(4) . '</td></tr>' . nlc();
                                echo indentc(3) . '</table>' . nlc();
                                break;
                            case 1: // below
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr><td nowrap style="text-align:center">' . nlc();
                                echo indentc(5) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" ' . $attribs . '/><br/>' . nlc();
                                echo indentc(5) . $data2 . nlc();
                                echo indentc(4) . '</td></tr>' . nlc();
                                echo indentc(3) . '</table>' . nlc();
                                break;
                            case 2: // above
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr><td nowrap style="text-align:center">' . nlc();
                                echo indentc(5) . $data2 . '<br/>' . nlc();
                                echo indentc(5) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" ' . $attribs . '/>' . nlc();
                                echo indentc(4) . '</td></tr>' . nlc();
                                echo indentc(3) . '</table>.nlc()';
                                break;
                            case 3: // left
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr>' . nlc();
                                echo indentc(5) . '<td>' . $data2 . '</td>' . nlc();
                                echo indentc(5) . '<td><img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" ' . $attribs . '/></td>' . nlc();
                                echo indentc(4) . '</tr>' . nlc();
                                echo indentc(3) . '</table>' . nlc();
                                break;
                            default: // assume right
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr>' . nlc();
                                echo indentc(5) . '<td><img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" ' . $attribs . '/></td>' . nlc();
                                echo indentc(5) . '<td>' . $data2 . '</td>' . nlc();
                                echo indentc(4) . '</tr>' . nlc();
                                echo indentc(3) . '</table>' . nlc();
                                break;
                        } // switch
                        echo indentc(2) . '</button>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Icon':
                        if ($row->flag2)
                            echo indentc(1) . '<div id="ff_div' . $row->id . '" onmouseout="ff_hideIconBorder(this);" onmouseover="ff_dispIconBorder(this);" style="padding:3px;' . $attribs . '"' . $class1 . '>' . nlc();
                        else
                            echo indentc(1) . '<div id="ff_div' . $row->id . '"  style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $swap = '';
                        if ($data3 != '')
                            $swap = 'onmouseout="MM_swapImgRestore();" onmouseover="MM_swapImage(\'ff_img' . $row->id . '\',\'\',\'' . $data3 . '\',1);" ';

                        $swap .= $this->script2clause($row);
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= 'width="' . $row->width . '" ';
                        if ($row->height > 0)
                            $attribs .= 'height="' . $row->height . '" ';
                        switch ($row->flag1) {
                            case 0: // none
                                echo indentc(2) . '<span id="ff_elem' . $row->id . '" ' . $swap . '>' . nlc();
                                echo indentc(3) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/>' . nlc();
                                echo indentc(2) . '</span>' . nlc();
                                break;
                            case 1: // below
                                echo indentc(2) . '<table id="ff_elem' . $row->id . '" cellpadding="1" cellspacing="0" border="0" ' . $swap . '>' . nlc();
                                echo indentc(3) . '<tr><td style="text-align:center;"><img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/></td></tr>' . nlc();
                                echo indentc(3) . '<tr><td style="text-align:center;">' . $data2 . '</td></tr>' . nlc();
                                echo indentc(2) . '</table>' . nlc();
                                break;
                            case 2: // above
                                echo indentc(2) . '<table id="ff_elem' . $row->id . '" cellpadding="2" cellspacing="0" border="0" ' . $swap . '>' . nlc();
                                echo indentc(3) . '<tr><td style="text-align:center;">' . $data2 . '</td></tr>' . nlc();
                                echo indentc(3) . '<tr><td style="text-align:center;"><img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/></td></tr>' . nlc();
                                echo indentc(2) . '</table>' . nlc();
                                break;
                            case 3: // left
                                echo indentc(2) . '<span id="ff_elem' . $row->id . '" ' . $swap . ' style="vertical-align:middle;">' . nlc();
                                echo indentc(3) . $data2 . ' &nbsp;<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/>' . nlc();
                                echo indentc(2) . '</span>' . nlc();
                                break;
                            default: // assume right
                                echo indentc(2) . '<span id="ff_elem' . $row->id . '" ' . $swap . ' style="vertical-align:middle;">' . nlc();
                                echo indentc(3) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/>&nbsp; ' . $data2 . nlc();
                                echo indentc(2) . '</span>' . nlc();
                                break;
                        } // switch
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Select List':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        $styles = '';
                        if ($row->width > 0)
                            $styles .= 'width:' . $row->width . 'px;';
                        if ($row->height > 0)
                            $styles .= 'height:' . $row->height . 'px;';
                        if ($row->flag1)
                            $attribs .= ' multiple="multiple"';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->script2clause($row);
                        if ($data1 != '')
                            $attribs .= ' size="' . $data1 . '"';
                        if ($styles != '')
                            $attribs .= ' style="' . $styles . '"';
                        echo indentc(2) . '<select id="ff_elem' . $row->id . '" name="ff_nm_' . $row->name . '[]" ' . $attribs . $class2 . '>' . nlc();
                        $options = explode('\n', preg_replace('/([\\r\\n])/s', '\n', $data2));
                        $cnt = count($options);
                        for ($o = 0; $o < $cnt; $o++) {
                            $opt = explode(";", $options[$o]);
                            $selected = '';
                            switch (count($opt)) {
                                case 0:
                                    break;
                                case 1:
                                    if ($this->trim($opt[0])) {
                                        $selected = '0';
                                        $value = $text = $opt[0];
                                    } // if
                                    break;
                                case 2:
                                    $selected = $opt[0];
                                    $value = $text = $opt[1];
                                    break;
                                default:
                                    $selected = $opt[0];
                                    $text = $opt[1];
                                    $value = $opt[2];
                            } // switch
                            if ($this->trim($selected)) {
                                $attribs = '';
                                if ($this->trim($value) != '') {
                                    if ($value == '""' || $value == "''")
                                        $value = '';
                                    $attribs .= ' value="' . htmlspecialchars($value, ENT_QUOTES) . '"';
                                } // if
                                if ($selected == 1)
                                    $attribs .= ' selected="selected"';
                                echo indentc(3) . '<option' . $attribs . '>' . htmlspecialchars(trim($text), ENT_QUOTES) . '</option>' . nlc();
                            } // if
                        } // for
                        echo indentc(2) . '</select>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Text':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0) {
                            if ($row->widthmode > 0)
                                $attribs .= ' style="width:' . $row->width . 'px;"';
                            else
                                $attribs .= ' size="' . $row->width . '"';
                        } // if
                        if ($row->height > 0)
                            $attribs .= ' maxlength="' . $row->height . '"';
                        if ($row->flag1)
                            $attribs .= ' type="password"';
                        else
                            $attribs .= ' type="text"';
                        switch ($row->flag2) {
                            case 1:
                                $attribs .= ' disabled="disabled"';
                                break;
                            case 2:
                                $attribs .= ' readonly="readonly"';
                                break;
                            default:
                                break;
                        } // switch
                        $attribs .= $this->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '"' . $attribs . ' name="ff_nm_' . $row->name . '[]" value="' . $data1 . '"' . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Textarea':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        $styles = '';
                        switch ($row->flag2) {
                            case 1:
                                $attribs .= ' disabled="disabled"';
                                break;
                            case 2:
                                $attribs .= ' readonly="readonly"';
                                break;
                            default:
                                break;
                        } // switch
                        if ($row->width > 0) {
                            if ($row->widthmode > 0)
                                $styles .= 'width:' . $row->width . 'px;';
                            else
                                $attribs .= ' cols="' . $row->width . '"';
                        } // if
                        if ($row->height > 0) {
                            if ($row->heightmode > 0)
                                $styles .= 'height:' . $row->height . 'px;';
                            else {
                                $height = $row->height;
                                if ($height > 1 && stristr($this->browser, 'mozilla'))
                                    $height--;
                                $attribs .= ' rows="' . $height . '"';
                            } // if
                        } // if
                        if ($styles != '')
                            $attribs .= ' style="' . $styles . '"';
                        $attribs .= $this->script2clause($row);
                        echo indentc(2) . '<textarea id="ff_elem' . $row->id . '" name="ff_nm_' . $row->name . '[]"' . $attribs . $class2 . '>' . $data1 . '</textarea>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'File Upload':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= ' size="' . $row->width . '"';
                        if ($row->height > 0)
                            $attribs .= ' maxlength="' . $row->height . '"';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        if ($row->data2 != '')
                            $attribs .= ' accept="' . $data2 . '"';
                        $attribs .= $this->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '"' . $attribs . ' type="file" name="ff_nm_' . $row->name . '[]"' . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Captcha':
                        if (Factory::getApplication()->isClient('site')) {
                            $captcha_url = Uri::root(true) . '/components/com_breezingforms/images/captcha/securimage_show.php';
                        } else {
                            $captcha_url = Uri::root(true) . '/administrator/components/com_breezingforms/images/captcha/securimage_show.php';
                        }
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= 'width:' . $row->width . 'px;';
                        if ($row->height > 0)
                            $attribs .= 'height:' . $row->height . 'px;';
                        echo '<img id="ff_capimgValue" class="ff_capimg" src="' . $captcha_url . '"/>';
                        echo '<br/>';
                        echo '<input type="text" style="' . $attribs . '" name="bfCaptchaEntry" id="bfCaptchaEntry" />';
                        //echo '<br/>';
                        echo '<a href="#" onclick="document.getElementById(\'bfCaptchaEntry\').value=\'\';document.getElementById(\'bfCaptchaEntry\').focus();document.getElementById(\'ff_capimgValue\').src = \'' . $captcha_url . '?bfCaptcha=true&bfMathRandom=\' + Math.random(); return false"><img src="' . Uri::root() . 'components/com_breezingforms/images/captcha/refresh-captcha.png" border="0" /></a>';
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Query List':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();

                        // unpack settings
                        $settings = explode("\n", $row->data1);
                        $scnt = count($settings);
                        for ($s = 0; $s < $scnt; $s++)
                            $this->trim($settings[$s]);
                        $trhclass = '';
                        $tr1class = '';
                        $tr2class = '';
                        $trfclass = '';
                        $tdfclass = '';
                        $pagenav = 1;
                        $attribs = '';
                        if ($scnt > 0 && $settings[0] != '')
                            $attribs .= ' border="' . $settings[0] . '"';
                        if ($scnt > 1 && $settings[1] != '')
                            $attribs .= ' cellspacing="' . $settings[1] . '"';
                        if ($scnt > 2 && $settings[2] != '')
                            $attribs .= ' cellpadding="' . $settings[2] . '"';
                        if ($scnt > 3 && $settings[3] != '')
                            $trhclass = ' class="' . $this->getClassName($settings[3]) . '"';
                        if ($scnt > 4 && $settings[4] != '')
                            $tr1class = ' class="' . $this->getClassName($settings[4]) . '"';
                        if ($scnt > 5 && $settings[5] != '')
                            $tr2class = ' class="' . $this->getClassName($settings[5]) . '"';
                        if ($scnt > 6 && $settings[6] != '')
                            $trfclass = ' class="' . $this->getClassName($settings[6]) . '"';
                        if ($scnt > 7 && $settings[7] != '')
                            $tdfclass = ' class="' . $this->getClassName($settings[7]) . '"';
                        if ($scnt > 8 && $settings[8] != '')
                            $pagenav = $settings[8];

                        if ($row->width > 0)
                            $attribs .= ' width="100%"';

                        // display 1st page of table
                        echo indentc(2) . '<table id="ff_elem' . $row->id . '"' . $attribs . $class2 . '>' . nl();

                        $cols = &$this->queryCols['ff_' . $row->id];
                        $colcnt = count($cols);

                        // display header
                        if ($row->flag1) {
                            echo indentc(3) . '<tr' . $trhclass . '>' . nlc();
                            $skip = 0;
                            for ($c = 0; $c < $colcnt; $c++)
                                if ($skip > 0)
                                    $skip--;
                                else {
                                    $col = &$cols[$c];
                                    if ($col->thspan > 0) {
                                        $attribs = '';
                                        $style = '';
                                        switch ($col->thalign) {
                                            case 1:
                                                $style .= 'text-align:left;';
                                                break;
                                            case 2:
                                                $style .= 'text-align:center;';
                                                break;
                                            case 3:
                                                $style .= 'text-align:right;';
                                                break;
                                            case 4:
                                                $style .= 'text-align:justify;';
                                                break;
                                            default:
                                                ;
                                        } // switch
                                        switch ($col->thvalign) {
                                            case 1:
                                                $attribs .= ' valign="top"';
                                                break;
                                            case 2:
                                                $attribs .= ' valign="middle"';
                                                break;
                                            case 3:
                                                $attribs .= ' valign="bottom"';
                                                break;
                                            case 4:
                                                $attribs .= ' valign="baseline"';
                                                break;
                                            default:
                                                ;
                                        } // switch
                                        if ($col->thwrap == 1)
                                            $attribs .= ' nowrap="nowrap"';
                                        if ($col->thspan > 1) {
                                            $attribs .= ' colspan="' . $col->thspan . '"';
                                            $skip = $col->thspan - 1;
                                        } // if
                                        if ($col->class1 != '')
                                            $attribs .= ' class="' . $this->getClassName($col->class1) . '"';
                                        if (intval($col->width) > 0 && !$skip) {
                                            $style .= 'width:' . $col->width;
                                            if ($col->widthmd)
                                                $style .= '%;';
                                            else
                                                $style .= 'px;';
                                        } // if
                                        if ($style != '')
                                            $attribs .= ' style="' . $style . '"';
                                        if ($c == 0 && $row->flag2 > 0) {
                                            if ($row->flag2 == 1)
                                                echo indentc(4) . '<th' . $attribs . '><input type="checkbox" id="ff_cb' . $row->id . '" onclick="ff_selectAllQueryRows(' . $row->id . ',this.checked);" /></th>' . nlc();
                                            else
                                                echo indentc(4) . '<th' . $attribs . '></th>' . nlc();
                                        } else
                                            echo indentc(4) . '<th' . $attribs . '>' . $this->replaceCode($col->title, BFText::_('COM_BREEZINGFORMS_PROCESS_QTITLEOF') . " $row->name::$col->name", 'e', $row->id, 2) . '</th>' . nlc();
                                    } // if
                                    unset($col);
                                } // if
                            echo indentc(3) . '</tr>' . nl();
                        } // if
                        // display data rows
                        $qrows = &$this->queryRows['ff_' . $row->id];
                        $qcnt = count($qrows);
                        $k = 1;
                        if ($row->height > 0 && $qcnt > $row->height)
                            $qcnt = $row->height;
                        for ($q = 0; $q < $qcnt; $q++) {
                            $qrow = &$qrows[$q];
                            if ($k == 1)
                                $cl = $tr1class;
                            else
                                $cl = $tr2class;
                            echo indentc(3) . '<tr' . $cl . '>' . nlc();
                            $skip = 0;
                            for ($c = 0; $c < $colcnt; $c++) {
                                $col = &$cols[$c];
                                if ($col->thspan > 0) {
                                    $attribs = '';
                                    $style = '';
                                    switch ($col->align) {
                                        case 1:
                                            $style .= 'text-align:left;';
                                            break;
                                        case 2:
                                            $style .= 'text-align:center;';
                                            break;
                                        case 3:
                                            $style .= 'text-align:right;';
                                            break;
                                        case 4:
                                            $style .= 'text-align:justify;';
                                            break;
                                        default:
                                            ;
                                    } // switch
                                    switch ($col->valign) {
                                        case 1:
                                            $attribs .= ' valign="top"';
                                            break;
                                        case 2:
                                            $attribs .= ' valign="middle"';
                                            break;
                                        case 3:
                                            $attribs .= ' valign="bottom"';
                                            break;
                                        case 4:
                                            $attribs .= ' valign="baseline"';
                                            break;
                                        default:
                                            ;
                                    } // switch
                                    if ($col->wrap == 1)
                                        $attribs .= ' nowrap="nowrap"';
                                    if ($k == 1)
                                        $cl = $col->class2;
                                    else
                                        $cl = $col->class3;
                                    if ($cl != '')
                                        $attribs .= ' class="' . $this->getClassName($cl) . '"';
                                    if (!$skip && $col->thspan > 1)
                                        $skip = $col->thspan;
                                    if ($skip && $q == 0)
                                        if (intval($col->width) > 0) {
                                            $style .= 'width:' . $col->width;
                                            if ($col->widthmd)
                                                $style .= '%;';
                                            else
                                                $style .= 'px;';
                                        } // if
                                    if ($skip > 0)
                                        $skip--;
                                    if ($style != '')
                                        $attribs .= ' style="' . $style . '"';
                                    if ($c == 0 && $row->flag2 > 0) {
                                        if ($row->flag2 == 1)
                                            echo indentc(4) . '<td' . $attribs . '><input type="checkbox" id="ff_cb' . $row->id . '_' . $q . '" value="' . $qrow[$c] . '"  name="ff_nm_' . $row->name . '[]"/></td>' . nlc();
                                        else
                                            echo indentc(4) . '<td' . $attribs . '><input type="radio" id="ff_cb' . $row->id . '_' . $q . '" value="' . $qrow[$c] . '"  name="ff_nm_' . $row->name . '[]"/></td>' . nlc();
                                    } else
                                        echo indentc(4) . '<td' . $attribs . '>' . $qrow[$c] . '</td>' . nlc();
                                } // if
                                unset($col);
                                if ($this->dying)
                                    break;
                            } // for
                            echo indentc(3) . '</tr>' . nl();
                            $k = 3 - $k;
                            unset($qrow);
                            if ($this->dying)
                                break;
                        } // for
                        if ($this->bury())
                            return;

                        // display footer
                        if ($row->height > 0 && $pagenav > 0) {
                            $span = 0;
                            for ($c = 0; $c < $colcnt; $c++)
                                if ($cols[$c]->thspan > 0)
                                    $span++;
                            $pages = intval((count($qrows) + $row->height - 1) / $row->height);
                            echo indentc(3) . '<tr' . $trfclass . '>' . nlc();
                            echo indentc(4) . '<td colspan="' . $span . '"' . $tdfclass . '>' . nlc();
                            if ($pages > 1) {
                                echo indentc(5);
                                if ($pagenav <= 4)
                                    echo '&lt;&lt; ';
                                if ($pagenav <= 2)
                                    echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGESTART') . ' ';
                                if ($pagenav <= 4)
                                    echo '&lt; ';
                                if ($pagenav <= 2)
                                    echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGEPREV') . ' ';
                                echo nlc();
                                if ($pagenav % 2) {
                                    echo indentc(5);
                                    echo '1 ';
                                    for ($p = 2; $p <= $pages; $p++)
                                        echo indentc(5) . '<a href="javascript:ff_dispQueryPage(' . $row->id . ',' . $p . ');">' . $p . '</a> ' . nlc();
                                    echo nlc();
                                } // if
                                if ($pagenav <= 4) {
                                    echo indentc(5) . '<a href="javascript:ff_dispQueryPage(' . $row->id . ',2);">';
                                    if ($pagenav <= 2)
                                        echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGENEXT') . ' ';
                                    echo '&gt;</a> ' . nlc();
                                    echo indentc(5) . '<a href="javascript:ff_dispQueryPage(' . $row->id . ',' . $pages . ');">';
                                    if ($pagenav <= 2)
                                        echo BFText::_('COM_BREEZINGFORMS_PROCESS_PAGEEND') . ' ';
                                    echo '&gt;&gt;</a>' . nlc();
                                } // if
                            } // if
                            echo indentc(4) . '</td>' . nlc();
                            echo indentc(3) . '</tr>' . nl();
                        } // if
                        // table end
                        echo indentc(2) . '</table>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        unset($qrows);
                        unset($cols);
                        break;
                    default:
                        break;
                } // switch
                unset($row);
            } // for
        } else if (trim($this->formrow->template_code_processed) == 'QuickMode') {

            if ($this->isMobile) {

                // nothing
            } else {
                //if(true){

                if (isset($rootMdata['themebootstrapThemeEngine']) && $rootMdata['themebootstrapThemeEngine'] == 'bootstrap') {

                    if (isset($rootMdata['themebootstrapMode']) && $rootMdata['themebootstrapMode']) {

                        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFQuickModeOnePage.php');
                        $quickMode = new BFQuickModeOnePage($this);
                    } else {

                        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFQuickModeBootstrap.php');
                        $quickMode = new BFQuickModeBootstrap($this);
                    }

                    $this->quickmode = $quickMode;
                } else {
                    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFQuickMode.php');
                    $quickMode = new BFQuickMode($this);
                    $this->quickmode = $quickMode;
                }
            }

            if ($is_mobile_type == 'choose') {
                $current_url = Uri::getInstance()->toString();
                $return_url = $current_url;
                $return_url = (strstr($return_url, '?non_mobile=1') !== false ? str_replace('?non_mobile=1', '', $return_url) : str_replace('&non_mobile=1', '', $return_url));
                $return_url = $return_url . (strstr($return_url, '?') !== false ? '&' : '?') . 'mobile=1';
                echo '<script type="text/javascript">
                <!--
                var bf_mobile_url = ' . json_encode($return_url) . ';
                //-->
                </script>';
                echo '<div style="display: block; text-align: center;"><button class="ff_elem btn btn-primary" onclick="location.href=bf_mobile_url;"><span>' . Text::_('COM_BREEZINGFORMS_MOBILE_VERSION') . '</span></button></div><div></div>';
            }

            $quickMode->render();
        } else { // case if forms done with the easy mode
            // always load calendar
            HTMLHelper::_('behavior.calendar');

            echo '
			<style type="text/css">
			ul.droppableArea, ul.droppableArea li { background-image: none; list-style: none; }
			li.ff_listItem { width: auto; list-style: none; }
			li.ff_listItem .ff_div { width: auto; float: left; }
			.ff_label { outline: none; }
			.ff_elem { float: left; }
			.ff_dragBox { display: none; }
			</style>
			' . nl();
            echo $this->formrow->template_code_processed;
            $visPages = '';
            $pagesSize = isset($this->formrow->pages) ? intval($this->formrow->pages) : 1;
            for ($pageCnt = 1; $pageCnt <= $pagesSize; $pageCnt++) {
                $visPages .= 'if(document.getElementById("bfPage' . $pageCnt . '"))document.getElementById("bfPage' . $pageCnt . '").style.display = "none";';
            }
            echo '<script type="text/javascript">
                              <!--
				' . $visPages . ';
				if(document.getElementById("bfPage' . $this->page . '"))document.getElementById("bfPage' . $this->page . '").style.display = "";
                              //-->
                              </script>' . nl();
        }

        if ($this->editable) {
            echo '<script type="text/javascript"><!--' . nl() . 'if(typeof bfLoadEditable != "undefined") { 
			    if(typeof JQuery != "undefined" && typeof bfToggleFieldsLoaded != "undefined" && typeof bfToggleFieldsLoaded != "undefined"){
			        JQuery(document).ready(function(){
			            let waitForToggleFields = setInterval(function(){
			                if(bfToggleFieldsLoaded && bfToggleFieldsLoaded){
			                    clearInterval(waitForToggleFields);
			                    bfLoadEditable();
			                }
			            }, 100); 
			        });
			    }else{
			        bfLoadEditable();
			    }
			}' . nl() . '//--></script>' . nl();
        }

        if ($cbRecord !== null) {
            echo '<script type="text/javascript"><!--' . nl() . '
			    if(typeof JQuery != "undefined" && typeof bfToggleFieldsLoaded != "undefined" && typeof bfToggleFieldsLoaded != "undefined"){
			        JQuery(document).ready(function(){
			            let waitForToggleFields = setInterval(function(){
			                if(bfToggleFieldsLoaded && bfToggleFieldsLoaded){
			                    clearInterval(waitForToggleFields);
			                    bfLoadContentBuilderEditable();
			                }
			            }, 100); 
			        });
			    }else{
			        bfLoadContentBuilderEditable();
			    }
			' . nl() . '//--></script>' . nl();
        }

        if ($cbForm !== null && count($cbNonEditableFields)) {
            echo '<script type="text/javascript"><!--' . nl() . '
			    if(typeof JQuery != "undefined" && typeof bfToggleFieldsLoaded != "undefined" && typeof bfToggleFieldsLoaded != "undefined"){
			        JQuery(document).ready(function(){
			            let waitForToggleFields = setInterval(function(){
			                if(bfToggleFieldsLoaded && bfToggleFieldsLoaded){
			                    clearInterval(waitForToggleFields);
			                    bfDisableContentBuilderFields();
			                }
			            }, 100); 
			        });
			    }else{
			        bfDisableContentBuilderFields();
			    }
			' . nl() . '//--></script>' . nl();
        }

        // CONTENTBUILDER
        // writing hidden input for groups. helps on recording updates, otherwise no value would be transferred.
        // the "cbGroupMark" won't be stored.
        if ($cbForm !== null) {
            for ($i = 0; $i < $this->rowcount; $i++) {
                $row = $this->rows[$i];
                switch ($row->type) {
                    case 'Checkbox':
                    case 'Checkbox Group':
                    case 'Radio Button':
                    case 'Radio Group':
                    case 'Select List':
                        // temporary removed until further clarification if needed or not as this will interfere with javasripts on group elements (loosing their type)
                        //echo '<input type="hidden" name="ff_nm_' . $row->name . '[]" value="cbGroupMark"/>' . nl();
                        break;
                }
            }
        }

        $paymentMethod = '';
        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            if ($row->type == "PayPal" || $row->type == "Sofortueberweisung" || $row->type == "Stripe") {
                echo indentc(1) . '<input type="hidden" name="ff_payment_method" id="bfPaymentMethod" value=""/>' . nl();
                break;
            }
        }

        switch ($this->runmode) {
            case _FF_RUNMODE_FRONTEND:
                echo indentc(1) . '<input type="hidden" name="ff_contentid" value="' . BFRequest::getInt('ff_contentid', 0) . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_applic" value="' . BFRequest::getWord('ff_applic', '') . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_record_id" value="' . $this->record_id . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_module_id" value="' . BFRequest::getInt('ff_module_id', 0) . '"/>' . nl();
                echo indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_task" value="submit"/>' . nl();
                if ($this->target > 1)
                    echo indentc(1) . '<input type="hidden" name="ff_target" value="' . htmlentities($this->target, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                if ($this->inframe)
                    echo indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl();
                if ($this->border)
                    echo indentc(1) . '<input type="hidden" name="ff_border" value="1"/>' . nl();
                if ($this->page != 1)
                    echo indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                if ($this->align != 1)
                    echo indentc(1) . '<input type="hidden" name="ff_align" value="' . htmlentities($this->align, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                if ($this->top != 0)
                    echo indentc(1) . '<input type="hidden" name="ff_top" value="' . htmlentities($this->top, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                reset($ff_otherparams);
                // while (list($prop, $val) = each($ff_otherparams))
                foreach ($ff_otherparams as $prop => $val) {
                    echo indentc(1) . '<input type="hidden" name="' . htmlentities($prop, ENT_QUOTES, 'UTF-8') . '" value="' . htmlentities(urlencode($val), ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                }
                if (isset($_REQUEST['cb_form_id']) && isset($_REQUEST['cb_record_id'])) {
                    echo '<input type="hidden" name="cb_form_id" value="' . BFRequest::getInt('cb_form_id', 0) . '"/>' . nl();
                    echo '<input type="hidden" name="cb_record_id" value="' . BFRequest::getInt('cb_record_id', 0) . '"/>' . nl();
                    echo '<input type="hidden" name="return" value="' . htmlentities(BFRequest::getVar('return', ''), ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                }
                if (BFRequest::getVar('tmpl') == 'component') {
                    echo '<input type="hidden" name="tmpl" value="component"/>' . nl();
                }
                echo '</form>' . nl();
                break;

            case _FF_RUNMODE_BACKEND:
                echo indentc(1) . '<input type="hidden" name="option" value="com_breezingforms"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="act" value="run"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_task" value="submit"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_contentid" value="' . BFRequest::getInt('ff_contentid', 0) . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_applic" value="' . BFRequest::getWord('ff_applic', '') . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_record_id" value="' . $this->record_id . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_module_id" value="' . BFRequest::getInt('ff_module_id', 0) . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_runmode" value="' . htmlentities($this->runmode, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                if ($this->target > 1)
                    echo indentc(1) . '<input type="hidden" name="ff_target" value="' . htmlentities($this->target, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                if ($this->inframe)
                    echo indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl();
                if ($this->border)
                    echo indentc(1) . '<input type="hidden" name="ff_border" value="1"/>' . nl();
                if ($this->page != 1)
                    echo indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                if ($this->align != 1)
                    echo indentc(1) . '<input type="hidden" name="ff_align" value="' . htmlentities($this->align, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                if ($this->top != 0)
                    echo indentc(1) . '<input type="hidden" name="ff_top" value="' . htmlentities($this->top, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                if (isset($_REQUEST['cb_form_id']) && isset($_REQUEST['cb_record_id'])) {
                    echo '<input type="hidden" name="cb_form_id" value="' . BFRequest::getInt('cb_form_id', 0) . '"/>' . nl();
                    echo '<input type="hidden" name="cb_record_id" value="' . BFRequest::getInt('cb_record_id', 0) . '"/>' . nl();
                    echo '<input type="hidden" name="return" value="' . htmlentities(BFRequest::getVar('return', ''), ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                }
                //echo '<input type="hidden" name="tmpl" value="' . BFRequest::getCmd('tmpl', '') . '"/>' . nl();
                if (BFRequest::getVar('tmpl') == 'component') {
                    echo '<input type="hidden" name="tmpl" value="component"/>' . nl();
                }
                echo '</form>' . nl();
                break;

            default: // _FF_RUNMODE_PREVIEW:
                if ($this->inframe) {
                    echo indentc(1) . '<input type="hidden" name="option" value="com_breezingforms"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_task" value="submit"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_contentid" value="' . BFRequest::getInt('ff_contentid', 0) . '"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_applic" value="' . BFRequest::getWord('ff_applic', '') . '"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_record_id" value="' . $this->record_id . '"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_module_id" value="' . BFRequest::getInt('ff_module_id', 0) . '"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_runmode" value="' . htmlentities($this->runmode, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->page != 1)
                        echo indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if (isset($_REQUEST['cb_form_id']) && isset($_REQUEST['cb_record_id'])) {
                        echo '<input type="hidden" name="cb_form_id" value="' . BFRequest::getInt('cb_form_id', 0) . '"/>' . nl();
                        echo '<input type="hidden" name="cb_record_id" value="' . BFRequest::getInt('cb_record_id', 0) . '"/>' . nl();
                        echo '<input type="hidden" name="return" value="' . htmlentities(BFRequest::getVar('return', ''), ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    }
                    if (BFRequest::getVar('tmpl') == 'component') {
                        echo '<input type="hidden" name="tmpl" value="component"/>' . nl();
                    }
                    echo '</form>' . nl();
                } // if
        } // if
        // handle After Form piece
        $code = '';

        switch ($this->formrow->piece2cond) {
            case 1: // library
                $database->setQuery(
                    "select name, code from #__facileforms_pieces " .
                    "where id=" . intval($this->formrow->piece2id) . " and published=1 "
                );
                $rows = $database->loadObjectList();
                if (count($rows))
                    echo $this->execPiece(
                        $rows[0]->code,
                        BFText::_('COM_BREEZINGFORMS_PROCESS_AFPIECE') . " " . $rows[0]->name,
                        'p',
                        $this->formrow->piece2id,
                        null
                    );
                break;
            case 2: // custom code
                echo $this->execPiece(
                    $this->formrow->piece2code,
                    BFText::_('COM_BREEZINGFORMS_PROCESS_AFPIECEC'),
                    'f',
                    $this->form,
                    2
                );
                break;
            default:
                break;
        } // switch
        if ($this->bury())
            return;

        if ($this->legacy_wrap) {
            echo '</div></div></div><div class="bfPage-bl"><div class="bfPage-br"><div class="bfPage-b"></div></div></div></div><!-- form end -->' . nl();
        } else {
            echo '</div><!-- form end -->' . nl();
        }
        if ($this->traceMode & _FF_TRACEMODE_DIRECT) {
            $this->dumpTrace();
            ob_end_flush();
            echo '</pre>';
        } else {
            ob_end_flush();
            $this->dumpTrace();
        } // if
        restore_error_handler();

        if (trim($this->formrow->template_code_processed) == 'QuickMode' && $this->isMobile) {
            $contents = ob_get_contents();
            $ob = 0;
            while (ob_get_level() > 0 && $ob <= 32) {
                ob_end_clean();
                $ob++;
            }

            echo '<!DOCTYPE html> 
<html> 
<head> 
<title>' . Factory::getDocument()->getTitle() . '</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">';
            echo $quickMode->headers();
            echo $quickMode->fetchHead(Factory::getDocument()->getHeadData());
            echo '</head>' . "\n";
            echo '<body>' . "\n";
            echo $contents;
            echo '
</body>' . "\n" . '</html>';
            exit;
        }
    }

    // view

    function logToDatabase($cbResult = null)
    { // CONTENTBUILDER
        global $database, $ff_config;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        if ($this->dying)
            return;

        if (!is_object($cbResult['form']) && $this->editable && $this->editable_override) {
            $database->setQuery("Select id From #__facileforms_records Where form = " . $database->Quote($this->form) . " And user_id = " . $database->Quote(Factory::getUser()->get('id', 0)) . " And user_id <> 0");
            $records = $database->loadObjectList();
            foreach ($records as $record) {
                $database->setQuery("Delete From #__facileforms_subrecords Where record = " . $record->id);
                $database->execute();
                $database->setQuery("Delete From #__facileforms_records Where id = " . $record->id);
                $database->execute();
            }
        }

        $record = new facileFormsRecords($database);
        $record->submitted = $this->submitted;
        $record->form = $this->form;
        $record->title = $this->formrow->title;
        $record->name = $this->formrow->name;
        $record->ip = $this->ip;
        $record->browser = $this->browser;
        $record->opsys = $this->opsys;
        $record->provider = $this->provider;
        $record->viewed = 0;
        $record->exported = 0;
        $record->archived = 0;
        if (Factory::getUser()->get('id', 0) > 0) {
            $record->user_id = Factory::getUser()->get('id', 0);
            $record->username = Factory::getUser()->get('username', '');
            $record->user_full_name = Factory::getUser()->get('name', '');
        } else {
            $record->user_id = Factory::getUser()->get('id', 0);
            $record->username = '-';
            $record->user_full_name = '-';
        }
        // CONTENTBUILDER WILL TAKE OVER SAVING/UPDATE IF EXISTS
        $cbFileFields = array();
        if (!is_object($cbResult['form'])) {
            if (!$record->store()) {
                $this->status = _FF_STATUS_SAVERECORD_FAILED;
                $this->message = $record->getError();
                return;
            } // if

            $record_return = $record->id;

            if ($record_return && file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_contentbuilder' . DS . 'contentbuilder.xml')) {
                $last_update = Factory::getDate();
                $last_update = $last_update->toSql();
                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery("Select id From #__contentbuilder_records Where `type` = 'com_breezingforms' And `reference_id` = " . $db->Quote($this->form) . " And record_id = " . $db->Quote($record_return));
                $res = $db->loadResult();
                if (!$res) {
                    $db->setQuery("Insert Into #__contentbuilder_records (session_id,`type`,last_update, published, record_id, reference_id) Values ('" . Factory::getSession()->getId() . "','com_breezingforms'," . $db->Quote($last_update) . ",0, " . $db->Quote($record_return) . ", " . $db->Quote($this->form) . ")");
                    $db->execute();
                } else {
                    $db->setQuery("Update #__contentbuilder_records Set last_update = " . $db->Quote($last_update) . ",edited = edited + 1 Where `type` = 'com_breezingforms' And `reference_id` = " . $db->Quote($this->form) . " And record_id = " . $db->Quote($record_return));
                    $db->execute();
                }
            }
        }

        $this->record_id = $record->id;

        $names = array();
        $subrecord = new facileFormsSubrecords($database);
        $subrecord->record = $record->id;
        if (count($this->savedata)) {

            $cbData = array();

            // CONTENTBUILDER file deletion/upgrade
            if (is_object($cbResult['form'])) {

                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery('Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_forms Where id = ' . BFRequest::getInt('cb_form_id', 0) . ' And published = 1');
                $_settings = $db->loadObject();

                $_record = $cbResult['form']->getRecord(BFRequest::getInt('record_id', 0), $_settings->published_only, $cbResult['frontend'] ? ($_settings->own_only_fe ? Factory::getUser()->get('id', 0) : -1) : ($_settings->own_only ? Factory::getUser()->get('id', 0) : -1), true);
                foreach ($_record as $_rec) {
                    $_files_deleted = array();
                    if ($_rec->recType == 'File Upload') {
                        $_array = BFRequest::getVar('cb_delete_' . $_rec->recElementId, array(), '', 'ARRAY');
                        foreach ($_array as $_key => $_arr) {
                            if ($_arr == 1) {
                                $_values = explode("\n", $_rec->recValue);
                                if (isset($_values[$_key])) {
                                    if (strpos(strtolower($_values[$_key]), '{cbsite}') === 0) {
                                        $_values[$_key] = str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), $_values[$_key]);
                                    }
                                    if (file_exists($_values[$_key])) {
                                        File::delete($_values[$_key]);
                                    }
                                    if (!isset($_files_deleted[$_rec->recElementId])) {
                                        $_files_deleted[$_rec->recElementId] = array();
                                    }
                                    $_files_deleted[$_rec->recElementId][] = $_key;
                                }
                            }
                        }

                        if (isset($_files_deleted[$_rec->recElementId]) && is_array($_files_deleted[$_rec->recElementId]) && count($_files_deleted[$_rec->recElementId])) {
                            $_i = 0;
                            foreach ($this->savedata as $data) {
                                if ($data[_FF_DATA_ID] == $_rec->recElementId) {
                                    $_is_values = explode("\n", $_rec->recValue);
                                    $_j = 0;
                                    foreach ($_is_values as $_is_value) {
                                        if (!in_array($_j, $_files_deleted[$_rec->recElementId])) {
                                            $this->savedata[$_i][_FF_DATA_VALUE] .= $_is_value . "\n";
                                        }
                                        $_j++;
                                    }
                                    $this->savedata[$_i][_FF_DATA_VALUE] = rtrim($this->savedata[$_i][_FF_DATA_VALUE]);
                                    break;
                                }
                                $_i++;
                            }
                        } else {
                            if (true) {
                                $next = count($this->savedata);
                                $this->savedata[$next] = array();
                                $this->savedata[$next][_FF_DATA_ID] = $_rec->recElementId;
                                $this->savedata[$next][_FF_DATA_NAME] = $_rec->recName;
                                $this->savedata[$next][_FF_DATA_TITLE] = strip_tags($_rec->recTitle);
                                $this->savedata[$next][_FF_DATA_TYPE] = $_rec->recType;
                                $this->savedata[$next][_FF_DATA_VALUE] = '';
                                $_is_values = explode("\n", $_rec->recValue);
                                foreach ($_is_values as $_is_value) {
                                    $this->savedata[$next][_FF_DATA_VALUE] .= $_is_value . "\n";
                                }
                                $this->savedata[$next][_FF_DATA_VALUE] = rtrim($this->savedata[$next][_FF_DATA_VALUE]);
                            }
                        }
                    }
                }
            }
            $_savedata = array();
            if (!is_object($cbResult['form'])) {
                foreach ($this->savedata as $data) {
                    if ($data[_FF_DATA_TYPE] == 'File Upload') {
                        if (!isset($_savedata[$data[_FF_DATA_ID]])) {
                            $_savedata[$data[_FF_DATA_ID]] = '';
                        }
                        $_savedata[$data[_FF_DATA_ID]] .= $data[_FF_DATA_VALUE] . "\n";
                    }
                }
            }
            $isset = array();
            foreach ($this->savedata as $data) {
                // CONTENTBUILDER WILL TAKE OVER SAVING/UPDATE IF EXISTS
                if (!is_object($cbResult['form'])) {
                    $subrecord->id = NULL;
                    $subrecord->element = $data[_FF_DATA_ID];
                    $subrecord->name = $data[_FF_DATA_NAME];
                    $subrecord->title = strip_tags($data[_FF_DATA_TITLE]);
                    $subrecord->type = $data[_FF_DATA_TYPE];
                    if (isset($_savedata[$data[_FF_DATA_ID]]) && !isset($isset[$data[_FF_DATA_ID]])) {
                        $subrecord->value = trim($_savedata[$data[_FF_DATA_ID]]);
                    } else {
                        $subrecord->value = $data[_FF_DATA_VALUE];
                    }
                    if (!isset($isset[$data[_FF_DATA_ID]])) {
                        if (!$subrecord->store()) {
                            $this->status = _FF_STATUS_SAVESUBRECORD_FAILED;
                            $this->message = $subrecord->getError();
                            return;
                        }
                    }
                    if ($data[_FF_DATA_TYPE] == 'File Upload') {
                        $isset[$data[_FF_DATA_ID]] = true;
                    }
                } else {

                    require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
                    $cbNonEditableFields = contentbuilder::getListNonEditableElements($cbResult['data']['id']);

                    if (!in_array($data[_FF_DATA_ID], $cbNonEditableFields)) {

                        switch ($data[_FF_DATA_TYPE]) {
                            case 'Checkbox':
                            case 'Checkbox Group':
                            case 'Radio Button':
                            case 'Radio Group':
                            case 'Select List':
                                if (!isset($cbData[$data[_FF_DATA_ID]])) {
                                    $cbData[$data[_FF_DATA_ID]] = array();
                                }
                                $cbData[$data[_FF_DATA_ID]][] = $data[_FF_DATA_VALUE];
                                break;
                            case 'File Upload':
                                if (!isset($cbData[$data[_FF_DATA_ID]])) {
                                    $cbData[$data[_FF_DATA_ID]] = '';
                                    $cbFileFields[] = $data[_FF_DATA_ID];
                                }
                                $cbData[$data[_FF_DATA_ID]] .= $data[_FF_DATA_VALUE] . "\n";
                                break;
                            default:
                                $cbData[$data[_FF_DATA_ID]] = $data[_FF_DATA_VALUE];
                        }
                    }
                }
            } // foreach
            // CONTENTBUILDER BEGIN
            if (is_object($cbResult['form'])) {

                PluginHelper::importPlugin('contentbuilder_submit');

                $is15 = false;

                $values = array();
                $names = $cbResult['form']->getAllElements();

                foreach ($names as $id => $name) {
                    if (isset($cbData[$id])) {
                        if (in_array($id, $cbFileFields) && trim($cbData[$id]) == '') {
                            $values[$id] = '';
                        } else if (in_array($id, $cbFileFields) && trim($cbData[$id]) != '') {
                            $values[$id] = trim($cbData[$id]);
                        } else {
                            $values[$id] = $cbData[$id];
                        }
                    }
                }

                $dispatcher = Factory::getApplication()->getDispatcher();
                $dispatcher->dispatch('onBeforeSubmit', new Joomla\Event\Event('onBeforeSubmit', array(
                    BFRequest::getInt('cb_record_id', 0),
                    $cbResult['form'],
                    $values
                )
            ));

                $record_return = $cbResult['form']->saveRecord(BFRequest::getInt('cb_record_id', 0), $values);

                $db = Factory::getContainer()->get(DatabaseInterface::class);

                $db->setQuery('Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_forms Where id = ' . BFRequest::getInt('cb_form_id', 0) . ' And published = 1');
                $cbData = $db->loadObject();

                if ($record_return) {

                    $this->record_id = $record_return;

                    $sef = '';
                    $ignore_lang_code = '*';
                    if ($cbResult['data']['default_lang_code_ignore']) {

                        $db->setQuery("Select lang_code From #__languages Where published = 1 And sef = " . $db->Quote(trim(BFRequest::getCmd('lang', ''))));
                        $ignore_lang_code = $db->loadResult();
                        if (!$ignore_lang_code) {
                            $ignore_lang_code = '*';
                        }

                        $sef = trim(BFRequest::getCmd('lang', ''));
                        if ($ignore_lang_code == '*') {
                            $sef = '';
                        }
                        if ($ignore_lang_code == '*') {
                            $sef = '';
                        }
                    } else {
                        $db->setQuery("Select sef From #__languages Where published = 1 And lang_code = " . $db->Quote($cbResult['data']['default_lang_code']));
                        $sef = $db->loadResult();
                    }

                    $language = $cbResult['data']['default_lang_code_ignore'] ? $ignore_lang_code : $cbResult['data']['default_lang_code'];
                    $res = $db->loadResult();
                    $last_update = Factory::getDate();
                    $last_update = $last_update->toSql();
                    if (!$res) {

                        $is_future = 0;
                        $created_up = $created_up->toSql();
                        if (intval($cbData->default_publish_up_days) != 0) {
                            $is_future = 1;
                            $date = Factory::getDate(strtotime('now +' . intval($cbData->default_publish_up_days) . ' days'));
                            $created_up = $date->toSql();
                        }
                        $created_down = '0000-00-00 00:00:00';
                        if (intval($cbData->default_publish_down_days) != 0) {
                            $date = Factory::getDate(strtotime($created_up . ' +' . intval($cbData->default_publish_down_days) . ' days'));
                            $created_down = $date->toSql();
                        }

                        $db->setQuery("Insert Into #__contentbuilder_records (session_id,`type`,last_update,is_future,lang_code, sef, published, record_id, reference_id, publish_up, publish_down) Values ('" . Factory::getSession()->getId() . "','com_breezingforms'," . $db->Quote($last_update) . ",$is_future, " . $db->Quote($language) . "," . $db->Quote(trim($sef)) . "," . $db->Quote($cbData->auto_publish && !$is_future ? 1 : 0) . ", " . $db->Quote($record_return) . ", " . $db->Quote($cbResult['form']->getReferenceId()) . ", " . $db->Quote($created_up) . ", " . $db->Quote($created_down) . ")");
                        $db->execute();

                    } else {

                        $db->setQuery("Update #__contentbuilder_records Set last_update = " . $db->Quote($last_update) . ",lang_code = " . $db->Quote($language) . ", sef = " . $db->Quote(trim($sef)) . ", edited = edited + 1 Where `type` = 'com_breezingforms' And `reference_id` = " . $db->Quote($cbResult['form']->getReferenceId()) . " And record_id = " . $db->Quote($record_return));
                        $db->execute();
                    }
                }

                $article_id = 0;

                // creating the article
                if (is_object($cbData) && $cbData->create_articles) {

                    BFRequest::setVar('cb_category_id', null);
                    BFRequest::setVar('cb_controller', null);

                    if (Factory::getApplication()->isClient('site') && BFRequest::getInt('Itemid', 0)) {
                        $menu = Factory::getApplication()->getMenu();
                        $item = $menu->getActive();
                        if (is_object($item)) {
                            BFRequest::setVar('cb_category_id', $item->getParams()->get('cb_category_id', null));
                            BFRequest::setVar('cb_controller', $item->getParams()->get('cb_controller', null));
                        }
                    }

                    $cbData->page_title = $cbData->use_view_name_as_title ? $cbData->name : $cbResult['form']->getPageTitle();
                    $cbData->labels = $cbResult['form']->getElementLabels();
                    $ids = array();
                    foreach ($cbData->labels as $reference_id => $label) {
                        $ids[] = $db->Quote($reference_id);
                    }
                    $cbData->labels = array();
                    if (count($ids)) {
                        $db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . BFRequest::getInt('cb_form_id', 0) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                        $rows = $db->loadAssocList();
                        $ids = array();
                        foreach ($rows as $row) {
                            $ids[] = $row['reference_id'];
                        }
                    }
                    $cbData->items = $cbResult['form']->getRecord($record_return, $cbData->published_only, $cbResult['frontend'] ? ($cbData->own_only_fe ? Factory::getUser()->get('id', 0) : -1) : ($cbData->own_only ? Factory::getUser()->get('id', 0) : -1), true);
                    if (!count($cbData->items)) {
                        throw new Exception(Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'), 404);
                    }
                    $config = array();
                    foreach ($this->savedata as $data) {
                        if ($data[_FF_DATA_NAME] == 'cb_article_category_id') {
                            $config['bf_catid'] = intval($data[_FF_DATA_VALUE]);
                            break;
                        }
                    }
                    $full = false;
                    $article_id = contentbuilder::createArticle(BFRequest::getInt('cb_form_id', 0), $record_return, $cbData->items, $ids, $cbData->title_field, $cbResult['form']->getRecordMetadata($record_return), $config, $full, true, BFRequest::getVar('cb_category_id', null));

                    $cache = Factory::getCache('com_content');
                    $cache->clean();
                    $cache = Factory::getCache('com_contentbuilder');
                    $cache->clean();
                }

                $dispatcher = Factory::getApplication()->getDispatcher();
                $dispatcher->dispatch('onAfterSubmit', new Joomla\Event\Event('onAfterSubmit', 
                    array(
                        $record_return,
                        $article_id,
                        $cbResult['form'],
                        $values
                    )
                ));
            }
            // CONTENTBUILDER END
        }

        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFIntegrate.php');
        $integrate = new BFIntegrate($this->form);
        if (count($this->savedata))
            foreach ($this->savedata as $data) {
                $integrate->field($data);
            }
        $integrate->commit();

        if (isset($record_return)) {
            return $record_return;
        }
    }

    /*
     * https://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425
     */

    function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    // logToDatabase

    function sendMail($from, $fromname, $recipient, $subject, $body, $attachment = NULL, $html = NULL, $cc = NULL, $bcc = NULL, $alt_sender = '')
    {
        if ($this->dying)
            return;
        $mail = bf_createMail($from, $fromname, $subject, $body, $alt_sender);

        try {

            if (is_array($recipient))
                foreach ($recipient as $to)
                    $mail->AddAddress($to);
            else
                $mail->AddAddress($recipient);

            if ($attachment) {
                if (is_array($attachment)) {
                    $attCnt = count($attachment);
                    for ($i = 0; $i < $attCnt; $i++) {
                        if (trim($attachment[$i]) != '') {
                            $mail->AddAttachment($attachment[$i]);
                        }
                    }
                } else {
                    if ($attachment != '') {
                        $mail->AddAttachment($attachment);
                    }
                }
            } // if
            //***************************************************

            if (isset($html))
                $mail->IsHTML($html);

            if (isset($cc)) {
                if (is_array($cc))
                    foreach ($cc as $to)
                        $mail->AddCC($to);
                else
                    $mail->AddCC($cc);
            } // if

            if (isset($bcc)) {
                if (is_array($bcc))
                    foreach ($bcc as $to)
                        $mail->AddBCC($to);
                else
                    $mail->AddBCC($bcc);
            } // if

            if (!$mail->Send()) {
                $this->status = _FF_STATUS_SENDMAIL_FAILED;
                $this->message = $mail->ErrorInfo;
            } // if
        } catch (Exception $e) {

            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
    }

    // sendMail

    function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    function exppdf($filter = array(), $mailback = false, $translate = true)
    {
        global $ff_compath;

        $tz = 'UTC';
        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

        $file = JPATH_SITE . '/media/breezingforms/pdftpl/' . $this->formrow->name . '_pdf_attachment.php';
        if (!file_exists($file)) {
            $file = JPATH_SITE . '/media/breezingforms/pdftpl/pdf_attachment.php';
        }

        if ($mailback) {
            $mb_file = JPATH_SITE . '/media/breezingforms/pdftpl/' . $this->formrow->name . '_pdf_mailback_attachment.php';
            if (file_exists($mb_file)) {
                $file = $mb_file;
            } else {
                $mb_file = JPATH_SITE . '/media/breezingforms/pdftpl/pdf_mailback_attachment.php';
                if (file_exists($mb_file)) {
                    $file = $mb_file;
                }
            }
        }

        $processed = array();
        $xmldata = array();

        $_xmldata = $this->xmldata;
        if ($mailback) {
            $_xmldata = $this->mb_xmldata;
        }

        foreach ($_xmldata as $data) {
            if (!in_array($data[_FF_DATA_NAME], $filter) && !in_array($data[_FF_DATA_NAME], $processed)) {
                if ($translate) {
                    $title_translated = '';
                    $this->getFieldTranslated('label', $data[_FF_DATA_NAME], $title_translated);
                    $data[_FF_DATA_TITLE] = $title_translated != '' ? $title_translated : strip_tags($data[_FF_DATA_TITLE]);
                }
                $xmldata[] = $data;
                //$processed[] = $data[_FF_DATA_NAME];
            }
        }

        $date_stamp = date('YmdHis');
        $submitted = $this->submitted;
        $date_ = Factory::getDate($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $this->submitted = $date_->format('Y-m-d H:i:s', true);
        $date_stamp = $date_->format('YmdHis', true);

        $date_stamp2 = date('Ymd');
        $date_ = Factory::getDate($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $date_stamp2 = $date_->format('YmdHis', true);

        $this->submitted = $submitted;

        if (!class_exists('BFPDF')) {

            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFPDF.php');
        }

        $pdf = new BFPDF();
        $pdf->setMailback($mailback);
        $pdf->setFormName($this->formrow->name);

        ob_start();
        require($file);
        $c = ob_get_contents();
        ob_end_clean();

        $active_found = false;
        $font_loaded = false;
        $ttf_name = '';

        if (is_dir(JPATH_SITE . '/media/breezingforms/pdftpl/fonts/')) {

            $sourcePath = JPATH_SITE . '/media/breezingforms/pdftpl/fonts/';
            if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
                while (false !== ($file = @readdir($handle))) {
                    if ($file != "." && $file != ".." && $this->endsWith(strtolower($file), '.php')) {
                        $file_sep = explode('.', $file);
                        if (count($file_sep) > 1) {
                            unset($file_sep[count($file_sep) - 1]);
                            $pdf->AddFont(implode('_', $file_sep), '', $sourcePath . $file);
                            $font_loaded = true;
                        }
                    }
                    if ($file != "." && $file != ".." && $this->endsWith(strtolower($file), '.ttf')) {
                        $file_sep = explode('.', $file);
                        if (count($file_sep) > 1) {
                            unset($file_sep[count($file_sep) - 1]);
                            $ttf_name = TCPDF_FONTS::addTTFfont($sourcePath . $file, 'TrueTypeUnicode');
                            $font_loaded = true;
                        }
                    }
                    if ($this->endsWith(strtolower($file), '_active')) {
                        $active = explode('_', $file);
                        if (count($active) > 1) {
                            unset($active[count($active) - 1]);
                            $font_name = '';
                            if ($ttf_name != '') {
                                $font_name = $ttf_name;
                            } else {
                                $font_name = implode('_', $active);
                            }
                            $pdf->SetFont($font_name);
                            if ($font_loaded) {
                                $active_found = true;
                            }
                        }
                    }
                }
                @closedir($handle);
            }
        }

        if (!$active_found) {
            TCPDF_FONTS::addTTFfont(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/tcpdf/fonts/verdana.ttf', 'TrueTypeUnicode');
            $pdf->SetFont('verdana');
        }

        if ($pdf->getFooterTemplate() == '') {

            $pdf->setPrintFooter(false);
        } else {

            $pdf->setPrintFooter(true);
        }

        if ($pdf->getHeaderTemplate() == '') {

            $pdf->setPrintHeader(false);
        } else {

            $pdf->setPrintHeader(true);
        }

        $pdf->AddPage();
        $pdf->writeHTML($c);
        mt_srand();

        $matches_array = array();
        $regex = '<!--(.+?)=(.+?)-->';
        preg_match_all($regex, str_replace(' ', '', $c), $matches_array);

        if (isset($matches_array[1]) && isset($matches_array[1][0]) && trim($matches_array[1][0]) == 'fm' && isset($matches_array[2]) && isset($matches_array[2][0])) {

            $fm = $matches_array[2][0];

            if (substr(trim($fm), 0, strlen('{mospath}')) === '{mospath}') {
                $fm = str_replace('{mospath}', $this->mospath, $fm);
            }

            foreach ($_xmldata as $data) {

                $value = str_replace(array('.', 'ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', 'ß'), array('_', 'ae', 'ue', 'oe', 'Ae', 'Ue', 'Oe', 'ss'), $data[_FF_DATA_VALUE]);

                $fm = str_replace('{' . strtolower($data[_FF_DATA_NAME]) . ':value}', File::makeSafe(trim($value)), $fm);
            }

            $fm = str_replace('{filemask:_separator}', '_', $fm);
            $fm = str_replace('{filemask:_username}', trim(Factory::getUser()->get('username')), $fm);
            $fm = str_replace('{filemask:_userid}', trim(Factory::getUser()->get('id')), $fm);
            $fm = str_replace('{filemask:_name}', trim(Factory::getUser()->get('name')), $fm);
            $fm = str_replace('{filemask:_datetime}', trim($date_stamp), $fm);
            $fm = str_replace('{filemask:_date}', trim($date_stamp2), $fm);
            $fm = str_replace('{filemask:_timestamp}', trim(time()), $fm);
            $fm = str_replace('{filemask:_random}', trim(mt_rand(0, mt_getrandmax())), $fm);
            if ($fm == '') {
                $fm = '__empty__';
            }

            $uploads = $this->uploads . '/';

            if (substr(trim($fm), 0, 1) === '/' || substr(trim($fm), 1, 1) === ':') {
                $uploads = '';
            }

            $pdfname = $uploads . $fm . '.pdf';

            if (file_exists($pdfname)) {

                $pdfname = $uploads . $fm . '-' . $date_stamp . '-' . mt_rand(0, mt_getrandmax()) . '.pdf';
            }
        } else {

            $pdfname = $this->uploads . '/ffexport-pdf-' . $date_stamp . '-' . mt_rand(0, mt_getrandmax()) . '.pdf';
        }

        $pdf->lastPage();
        $pdf->Output($pdfname, "F");
        return $pdfname;
    }

    function expcsv($filter = array(), $mailback = false)
    {
        global $ff_config;

        $inverted = isset($ff_config->csvinverted) ? $ff_config->csvinverted : false;

        $tz = 'UTC';
        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

        $csvdelimiter = stripslashes($ff_config->csvdelimiter);
        $csvquote = stripslashes($ff_config->csvquote);
        $cellnewline = $ff_config->cellnewline == 0 ? "\n" : "\\n";

        $fields = array();
        $lines = array();

        $lineNum = count($lines);

        $fields['ZZZ_A_FORM'] = true;
        $fields['ZZZ_B_SUBMITTED'] = true;
        $fields['ZZZ_C_IP'] = true;
        $fields['ZZZ_D_BROWSER'] = true;
        $fields['ZZZ_E_OPSYS'] = true;

        $lines[$lineNum]['ZZZ_A_FORM'][] = $this->form;

        $date_stamp = date('YmdHis');
        $submitted = $this->submitted;
        $date_ = Factory::getDate($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $submitted = $date_->format('Y-m-d H:i:s', true);
        $date_stamp = $date_->format('YmdHis', true);

        $lines[$lineNum]['ZZZ_B_SUBMITTED'][] = $submitted;
        $lines[$lineNum]['ZZZ_C_IP'][] = $this->ip;
        $lines[$lineNum]['ZZZ_D_BROWSER'][] = $this->browser;
        $lines[$lineNum]['ZZZ_E_OPSYS'][] = $this->opsys;

        $xmldata = $this->xmldata;
        if ($mailback) {
            $xmldata = $this->mb_xmldata;
        }

        $processed = array();
        if (count($xmldata)) {
            foreach ($xmldata as $data) {
                if (!in_array($data[_FF_DATA_NAME], $filter) && !in_array($data[_FF_DATA_NAME], $processed)) {
                    $fields[strip_tags($data[_FF_DATA_TITLE])] = true;
                    $lines[$lineNum][strip_tags($data[_FF_DATA_TITLE])][] = is_array($data[_FF_DATA_VALUE]) ? implode('|', $data[_FF_DATA_VALUE]) : $data[_FF_DATA_VALUE];
                    //$processed[] = $data[_FF_DATA_NAME];
                }
            } // foreach
        }

        $head = '';
        ksort($fields);
        $lineLength = count($lines);
        foreach ($fields as $fieldName => $null) {
            if ($inverted == false) {
                $head .= $csvquote . $fieldName . $csvquote . $csvdelimiter;
            }
        }

        if ($inverted == false) {
            $head = substr($head, 0, strlen($head) - 1) . nl();
        }

        $out = '';
        for ($i = 0; $i < $lineLength; $i++) {
            ksort($lines[$i]);
            foreach ($lines[$i] as $fieldName => $line) {
                if ($inverted == true) {
                    $out .= $csvquote . str_replace($csvquote, $csvquote . $csvquote, str_replace("\n", $cellnewline, str_replace("\r", "", $fieldName))) . $csvquote . $csvdelimiter;
                }
                $out .= $csvquote . str_replace($csvquote, $csvquote . $csvquote, str_replace("\n", $cellnewline, str_replace("\r", "", implode('|', $line)))) . $csvquote . $csvdelimiter;

                if ($inverted == true) {
                    $out .= nl();
                }
            }

            if ($inverted == false) {
                $out = substr($out, 0, strlen($out) - 1);
                $out .= nl();
            }
        }
        mt_srand();
        $csvname = $this->uploads . '/ffexport-' . $date_stamp . '-' . mt_rand(0, mt_getrandmax()) . '.csv';
        File::makeSafe($csvname);
        if (function_exists('mb_convert_encoding')) {
            $to_encoding = 'UTF-16LE';
            $from_encoding = 'UTF-8';
            $chrchr = chr(255) . chr(254) . mb_convert_encoding($head . $out, $to_encoding, $from_encoding);
            if (!File::write($csvname, $chrchr)) {
                $this->status = _FF_STATUS_ATTACHMENT_FAILED;
            } // if
        } else {
            $head_out = $head . $out;
            if (!File::write($csvname, $head_out)) {
                $this->status = _FF_STATUS_ATTACHMENT_FAILED;
            } // if
        }
        return $csvname;
    }

    function expxml($filter = array(), $mailback = false, $translate = false)
    {
        global $ff_compath, $ff_version, $mosConfig_fileperms;

        $tz = 'UTC';
        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

        $date_stamp = date('YmdHis');
        $submitted = $this->submitted;
        $date_file = date('Y-m-d H:i:s');
        $date_ = Factory::getDate($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $submitted = $date_->format('Y-m-d H:i:s', true);
        $date_stamp = $date_->format('YmdHis', true);
        $date_file = $submitted;

        if ($this->dying)
            return '';
        mt_srand();
        $xmlname = $this->uploads . '/ffexport-' . $date_stamp . '-' . mt_rand(0, mt_getrandmax()) . '.xml';

        $xml = '<?xml version="1.0" encoding="utf-8" ?>' . nl() .
            '<FacileFormsExport type="records" version="' . $ff_version . '">' . nl() .
            indent(1) . '<exportdate>' . $date_file . '</exportdate>' . nl();
        if ($this->record_id != '')
            $xml .= indent(1) . '<record id="' . $this->record_id . '">' . nl();
        else
            $xml .= indent(1) . '<record>' . nl();

        $title_translated = $this->getFormTitleTranslated();

        $xml .= indent(2) . '<submitted>' . $submitted . '</submitted>' . nl() .
            indent(2) . '<form>' . $this->form . '</form>' . nl() .
            indent(2) . '<title>' . htmlspecialchars($title_translated != '' ? $title_translated : $this->formrow->title, ENT_QUOTES, 'UTF-8') . '</title>' . nl() .
            indent(2) . '<name>' . $this->formrow->name . '</name>' . nl() .
            indent(2) . '<ip>' . $this->ip . '</ip>' . nl() .
            indent(2) . '<browser>' . htmlspecialchars($this->browser, ENT_QUOTES, 'UTF-8') . '</browser>' . nl() .
            indent(2) . '<opsys>' . htmlspecialchars($this->opsys, ENT_QUOTES, 'UTF-8') . '</opsys>' . nl() .
            indent(2) . '<provider>' . $this->provider . '</provider>' . nl() .
            indent(2) . '<viewed>0</viewed>' . nl() .
            indent(2) . '<exported>0</exported>' . nl() .
            indent(2) . '<archived>0</archived>' . nl();
        $processed = array();

        $xmldata = $this->xmldata;
        if ($mailback) {
            $xmldata = $this->mb_xmldata;
        }

        if (count($xmldata))
            foreach ($xmldata as $data) {

                if ($translate) {
                    $title_translated = '';
                    $this->getFieldTranslated('label', $data[_FF_DATA_NAME], $title_translated);
                }

                if (!in_array($data[_FF_DATA_NAME], $filter) && !in_array($data[_FF_DATA_NAME], $processed)) {
                    $xml .= indent(2) . '<subrecord>' . nl() .
                        indent(3) . '<element>' . $data[_FF_DATA_ID] . '</element>' . nl() .
                        indent(3) . '<name>' . $data[_FF_DATA_NAME] . '</name>' . nl() .
                        indent(3) . '<title>' . htmlspecialchars($title_translated != '' ? $title_translated : strip_tags($data[_FF_DATA_TITLE]), ENT_QUOTES, 'UTF-8') . '</title>' . nl() .
                        indent(3) . '<type>' . $data[_FF_DATA_TYPE] . '</type>' . nl() .
                        indent(3) . '<value>' . htmlspecialchars(is_array($data[_FF_DATA_VALUE]) ? implode('|', $data[_FF_DATA_VALUE]) : $data[_FF_DATA_VALUE], ENT_QUOTES, 'UTF-8') . '</value>' . nl() .
                        indent(2) . '</subrecord>' . nl();
                    //$processed[] = $data[_FF_DATA_NAME];
                }
            } // foreach
        $xml .= indent(1) . '</record>' . nl() .
            '</FacileFormsExport>' . nl();

        File::makeSafe($xmlname);
        if (!File::write($xmlname, $xml)) {
            $this->status = _FF_STATUS_ATTACHMENT_FAILED;
        } // if

        return $xmlname;
    }

    // expxml

    function sendEmailNotification()
    {
        global $ff_config;

        $mainframe = Factory::getApplication();

        if ($this->dying)
            return;


        $from = $this->formrow->alt_mailfrom != '' ? $this->formrow->alt_mailfrom : $mainframe->getCfg('mailfrom');
        $fromname = $this->formrow->alt_fromname != '' ? $this->formrow->alt_fromname : $mainframe->getCfg('fromname');
        if ($this->formrow->emailntf == 2)
            $recipient = $this->formrow->emailadr;
        else
            $recipient = $ff_config->emailadr;

        $recipients = explode(';', $recipient);
        $cleaned_recipients = array();

        $alt_sender = '';

        foreach ($recipients as $recipient) {

            $test = explode(':', $recipient);
            if (count($test) == 2 && strtolower(trim($test[0])) == 'sender') {
                $alt_sender = trim($test[1]);
            } else {
                $cleaned_recipients[] = $recipient;
            }
        }

        /*
          $customSender = false;
          $sender = BFRequest::getVar('mailbackSender', array());

          for ($i = 0; $i < $this->rowcount; $i++) {
          $row = $this->rows[$i];
          $mb = BFRequest::getVar('ff_nm_' . $row->name, '');
          if ($row->mailback == 1) {
          $mbCnt = count($mb);
          for ($x = 0; $x < $mbCnt; $x++) {
          if (isset($mb[$x]) && trim($mb[$x]) != '' && bf_is_email(trim($mb[$x]))) {

          if (isset($sender[$row->name]) && !$customSender) {
          $alt_sender = trim($mb[$x]);
          $customSender = true;
          }
          }
          }
          }
          } */

        $recipients = $cleaned_recipients;
        $recipientsSize = count($recipients);

        // dynamic receipients
        $all_recipients = array();
        for ($i = 0; $i < $recipientsSize; $i++) {
            if (bf_startsWith(trim($recipients[$i]), '{') && bf_endsWith(trim($recipients[$i]), '}')) {
                $from_ = trim($recipients[$i]);
                $from_ = trim($from_, '{}');
                $froms = explode(':', $from_);
                $field = $froms[0];

                if (count($this->maildata)) {
                    foreach ($this->maildata as $DATA) {
                        if (strtolower($DATA[_FF_DATA_NAME]) == strtolower($field)) {
                            if (isset($froms[1])) {
                                $valuepairs = explode(',', $froms[1]);
                                foreach ($valuepairs as $valuepair) {
                                    $keyval = explode('>', trim($valuepair));
                                    $key = trim($keyval[0]);
                                    if (isset($keyval[1])) {
                                        $value = trim($keyval[1]);
                                        $value_exploded = explode("|", $value);

                                        if ($DATA[_FF_DATA_TYPE] == 'Checkbox Group') {

                                            $data_value = explode(', ', strtolower($DATA[_FF_DATA_VALUE]));

                                            if (in_array(strtolower($key), $data_value)) {
                                                foreach ($value_exploded as $value2) {
                                                    $all_recipients[] = trim($value2);
                                                    unset($recipients[$i]);
                                                }
                                            }
                                        } else {

                                            if (strtolower($key) == strtolower($DATA[_FF_DATA_VALUE])) {
                                                foreach ($value_exploded as $value2) {
                                                    $all_recipients[] = trim($value2);
                                                    unset($recipients[$i]);
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else {
                                $all_recipients[] = $DATA[_FF_DATA_VALUE];
                                unset($recipients[$i]);
                            }
                            break;
                        }
                    }
                }
            }
        }

        if (count($all_recipients)) {
            $recipients = array_merge($all_recipients, $recipients);
            $recipientsSize = count($recipients);
        }

        $subject = BFText::_('COM_BREEZINGFORMS_PROCESS_FORMRECRECEIVED');
        if ($this->formrow->custom_mail_subject != '') {
            $subject = $this->formrow->custom_mail_subject;
        }
        $body = '';
        $isHtml = false;

        if ($this->formrow->email_type == 0) {

            $foundTpl = false;
            $tplFile = '';
            $formTxtFile = JPATH_SITE . '/media/breezingforms/mailtpl/' . $this->formrow->name . '.txt.php';
            $formHtmlFile = JPATH_SITE . '/media/breezingforms/mailtpl/' . $this->formrow->name . '.html.php';
            $defaultTxtFile = JPATH_SITE . '/media/breezingforms/mailtpl/mailtpl.txt.php';
            $defaultHtmlFile = JPATH_SITE . '/media/breezingforms/mailtpl/mailtpl.html.php';

            if (@file_exists($formHtmlFile) && @is_readable($formHtmlFile)) {
                $tplFile = $formHtmlFile;
                $foundTpl = true;
                $isHtml = true;
            } else if (@file_exists($formTxtFile) && @is_readable($formTxtFile)) {
                $tplFile = $formTxtFile;
                $foundTpl = true;
            } else if (@file_exists($defaultHtmlFile) && @is_readable($defaultHtmlFile)) {
                $tplFile = $defaultHtmlFile;
                $foundTpl = true;
                $isHtml = true;
            } else if (@file_exists($defaultTxtFile) && @is_readable($defaultTxtFile)) {
                $tplFile = $defaultTxtFile;
                $foundTpl = true;
            }

            if ($foundTpl) {

                $NL = nl();

                $PROCESS_RECORDSAVEDID = '';
                $RECORD_ID = '';

                if ($this->record_id != '') {
                    $PROCESS_RECORDSAVEDID = BFText::_('COM_BREEZINGFORMS_PROCESS_RECORDSAVEDID');
                    $RECORD_ID = $this->record_id;
                }

                $PROCESS_FORMID = BFText::_('COM_BREEZINGFORMS_PROCESS_FORMID');
                $FORM = $this->form;

                $PROCESS_FORMTITLE = BFText::_('COM_BREEZINGFORMS_PROCESS_FORMTITLE');
                $TITLE = $this->formrow->title;

                $PROCESS_FORMNAME = BFText::_('COM_BREEZINGFORMS_PROCESS_FORMNAME');
                $NAME = $this->formrow->name;

                $PROCESS_SUBMITTEDAT = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTEDAT');

                $tz = 'UTC';
                $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

                $SUBMITTED = $this->submitted;
                $date_ = Factory::getDate($this->submitted, $tz);
                $offset = $date_->getOffsetFromGMT();
                if ($offset > 0) {
                    $date_->add(new DateInterval('PT' . $offset . 'S'));
                } else if ($offset < 0) {
                    $offset = $offset * -1;
                    $date_->sub(new DateInterval('PT' . $offset . 'S'));
                }

                $SUBMITTED = $date_->format('Y-m-d H:i:s', true);

                $PROCESS_SUBMITTERIP = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERIP');
                $IP = $this->ip;

                $PROCESS_PROVIDER = BFText::_('COM_BREEZINGFORMS_PROCESS_PROVIDER');
                $PROVIDER = $this->provider;

                $PROCESS_BROWSER = BFText::_('COM_BREEZINGFORMS_PROCESS_BROWSER');
                $BROWSER = $this->browser;

                $PROCESS_OPSYS = BFText::_('COM_BREEZINGFORMS_PROCESS_OPSYS');
                $OPSYS = $this->opsys;

                $PROCESS_SUBMITTERID = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERID');
                $SUBMITTERID = 0;

                $PROCESS_SUBMITTERUSERNAME = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERUSERNAME');
                $SUBMITTERUSERNAME = '-';

                $PROCESS_SUBMITTERFULLNAME = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERFULLNAME');
                $SUBMITTERFULLNAME = '-';

                if (Factory::getUser()->get('id', 0) > 0) {
                    $SUBMITTERID = Factory::getUser()->get('id', 0);
                    $SUBMITTERUSERNAME = Factory::getUser()->get('username', '');
                    $SUBMITTERFULLNAME = Factory::getUser()->get('name', '');
                }

                $MAILDATA = array();
                if (count($this->maildata)) {
                    foreach ($this->maildata as $DATA) {
                        $subject = str_replace('{' . $DATA[_FF_DATA_NAME] . ':label}', strip_tags($DATA[_FF_DATA_TITLE]), $subject);
                        $subject = str_replace('{' . $DATA[_FF_DATA_NAME] . ':title}', strip_tags($DATA[_FF_DATA_TITLE]), $subject);
                        $subject = str_replace('{' . $DATA[_FF_DATA_NAME] . ':value}', $DATA[_FF_DATA_VALUE], $subject);
                        $subject = str_replace('{' . $DATA[_FF_DATA_NAME] . '}', $DATA[_FF_DATA_VALUE], $subject);
                        $MAILDATA[] = $DATA;
                    }
                }

                ob_start();
                include($tplFile);
                $body = ob_get_contents();
                ob_end_clean();
            } else {
                // fallback if no template exists

                $tz = 'UTC';
                $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

                $submitted = $this->submitted;
                $date_ = Factory::getDate($this->submitted, $tz);
                $offset = $date_->getOffsetFromGMT();
                if ($offset > 0) {
                    $date_->add(new DateInterval('PT' . $offset . 'S'));
                } else if ($offset < 0) {
                    $offset = $offset * -1;
                    $date_->sub(new DateInterval('PT' . $offset . 'S'));
                }

                $submitted = $date_->format('Y-m-d H:i:s', true);

                if ($this->record_id != '')
                    $body .= BFText::_('COM_BREEZINGFORMS_PROCESS_RECORDSAVEDID') . " " . $this->record_id . nl() . nl();
                $body .= BFText::_('COM_BREEZINGFORMS_PROCESS_FORMID') . ": " . $this->form . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_FORMTITLE') . ": " . $this->formrow->title . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_FORMNAME') . ": " . $this->formrow->name . nl() . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTEDAT') . ": " . $submitted . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERIP') . ": " . $this->ip . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERID') . ": " . Factory::getUser()->get('id', 0) . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERUSERNAME') . ": " . Factory::getUser()->get('username', '') . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERFULLNAME') . ": " . Factory::getUser()->get('name', '') . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_PROVIDER') . ": " . $this->provider . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_BROWSER') . ": " . $this->browser . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_OPSYS') . ": " . $this->opsys . nl() . nl();
                if (count($this->maildata)) {
                    foreach ($this->maildata as $data) {
                        $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':label}', strip_tags($data[_FF_DATA_TITLE]), $subject);
                        $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':title}', strip_tags($data[_FF_DATA_TITLE]), $subject);
                        $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', $data[_FF_DATA_VALUE], $subject);
                        $subject = str_replace('{' . $data[_FF_DATA_NAME] . '}', $data[_FF_DATA_VALUE], $subject);
                        $body .= strip_tags($data[_FF_DATA_TITLE]) . ": " . $data[_FF_DATA_VALUE] . nl();
                    }
                }
            }
        } else {

            $body = $this->formrow->email_custom_template;

            $RECORD_ID = '';
            if ($this->record_id != '') {
                $RECORD_ID = $this->record_id;
            }

            $FORM = $this->form;
            $TITLE = $this->formrow->title;
            $FORMNAME = $this->formrow->name;
            $SUBMITTED = $this->submitted;

            $tz = 'UTC';
            $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

            $date_ = Factory::getDate($this->submitted, $tz);
            $offset = $date_->getOffsetFromGMT();
            if ($offset > 0) {
                $date_->add(new DateInterval('PT' . $offset . 'S'));
            } else if ($offset < 0) {
                $offset = $offset * -1;
                $date_->sub(new DateInterval('PT' . $offset . 'S'));
            }

            $SUBMITTED = $date_->format('Y-m-d H:i:s', true);

            $IP = $this->ip;
            $PROVIDER = $this->provider;
            $BROWSER = $this->browser;
            $OPSYS = $this->opsys;
            $SUBMITTERID = 0;
            $SUBMITTERUSERNAME = '-';
            $SUBMITTERFULLNAME = '-';
            if (Factory::getUser()->get('id', 0) > 0) {
                $SUBMITTERID = Factory::getUser()->get('id', 0);
                $SUBMITTERUSERNAME = Factory::getUser()->get('username', '');
                $SUBMITTERFULLNAME = Factory::getUser()->get('name', '');
            }

            $body = str_replace('{BF_RECORD_ID:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_RECORDSAVEDID'), $body);
            $body = str_replace('{BF_RECORD_ID:value}', $RECORD_ID, $body);

            $body = str_replace('{BF_FORM_ID:label}', BFText::_('Form ID'), $body);
            $body = str_replace('{BF_FORM_ID:value}', $this->form_id, $body);

            $body = str_replace('{BF_FORM:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_FORMID'), $body);
            $body = str_replace('{BF_FORM:value}', $FORM, $body);

            $body = str_replace('{BF_TITLE:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_FORMTITLE'), $body);
            $body = str_replace('{BF_TITLE:value}', $TITLE, $body);

            $body = str_replace('{BF_FORMNAME:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_FORMNAME'), $body);
            $body = str_replace('{BF_FORMNAME:value}', $FORMNAME, $body);

            $body = str_replace('{BF_SUBMITTED:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTEDAT'), $body);
            $body = str_replace('{BF_SUBMITTED:value}', $SUBMITTED, $body);

            $body = str_replace('{BF_IP:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERIP'), $body);
            $body = str_replace('{BF_IP:value}', $IP, $body);

            $body = str_replace('{BF_PROVIDER:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_PROVIDER'), $body);
            $body = str_replace('{BF_PROVIDER:value}', $PROVIDER, $body);

            $body = str_replace('{BF_BROWSER:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_BROWSER'), $body);
            $body = str_replace('{BF_BROWSER:value}', $BROWSER, $body);

            $body = str_replace('{BF_OPSYS:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_OPSYS'), $body);
            $body = str_replace('{BF_OPSYS:value}', $OPSYS, $body);

            $body = str_replace('{BF_SUBMITTERID:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERID'), $body);
            $body = str_replace('{BF_SUBMITTERID:value}', $SUBMITTERID, $body);

            $body = str_replace('{BF_SUBMITTERUSERNAME:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERUSERNAME'), $body);
            $body = str_replace('{BF_SUBMITTERUSERNAME:value}', $SUBMITTERUSERNAME, $body);

            $body = str_replace('{BF_SUBMITTERFULLNAME:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERFULLNAME'), $body);
            $body = str_replace('{BF_SUBMITTERFULLNAME:value}', $SUBMITTERFULLNAME, $body);

            if (count($this->savedata)) {
                foreach ($this->savedata as $data) {

                    $regex = "/([\{]hide " . $data[_FF_DATA_NAME] . "[\}])(.*)([\{][\/]hide[\}])/isU";

                    if ($data[_FF_DATA_VALUE] == '') {
                        $body = preg_replace($regex, "", $body);
                    } else {
                        $body = preg_replace($regex, '$2', $body);
                    }

                    if ($data[_FF_DATA_VALUE] == '') {
                        $subject = preg_replace($regex, "", $subject);
                    } else {
                        $subject = preg_replace($regex, '$2', $subject);
                    }
                }
            }

            if (count($this->maildata)) {
                foreach ($this->maildata as $data) {

                    $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':label}', strip_tags($data[_FF_DATA_TITLE]), $subject);
                    $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':title}', strip_tags($data[_FF_DATA_TITLE]), $subject);
                    $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', $data[_FF_DATA_VALUE], $subject);
                    $subject = str_replace('{' . $data[_FF_DATA_NAME] . '}', $data[_FF_DATA_VALUE], $subject);
                    $body = str_replace('{' . $data[_FF_DATA_NAME] . ':label}', strip_tags($data[_FF_DATA_TITLE]), $body);
                    if ($this->formrow->email_custom_html) {
                        $body = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', str_replace(array("\n", "\r"), array('<br/>', ''), $data[_FF_DATA_VALUE]), $body);
                    } else {
                        $body = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', $data[_FF_DATA_VALUE], $body);
                    }
                }
            }

            $body = preg_replace("/{([a-zA-Z0-9_\-])*:(label|value)}/", '', $body);

            if ($this->formrow->email_custom_html) {
                $isHtml = true;
            }
        }

        $attachment = NULL;
        if ($this->formrow->emailxml > 0 && $this->formrow->emailxml < 3) {
            $attachment = $this->expxml();
            if ($this->status != _FF_STATUS_OK)
                return;
        } else if ($this->formrow->emailxml == 3) {
            $attachment = $this->expcsv();
            if ($this->status != _FF_STATUS_OK)
                return;
        } else if ($this->formrow->emailxml == 4) {
            $attachment = $this->exppdf();
            if ($this->status != _FF_STATUS_OK)
                return;
        }

        $sender = BFRequest::getVar('mailbackSender', array());
        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            $mb = BFRequest::getVar('ff_nm_' . $row->name, '');
            if ($row->mailback == 1 && is_array($mb)) {
                $mbCnt = count($mb);
                for ($x = 0; $x < $mbCnt; $x++) {
                    if (isset($mb[$x]) && trim($mb[$x]) != '' && bf_is_email(trim($mb[$x]))) {
                        if (isset($sender[$row->name])) {
                            $from = trim($mb[$x]);
                            //$fromname = trim($mb[$x]);
                            break;
                        }
                    }
                }
            }
        }

        // dynamic mailfroms

        if (bf_startsWith(trim($from), '{') && bf_endsWith(trim($from), '}')) {
            $from_ = trim($from);
            $from_ = trim($from_, '{}');
            $froms = explode(':', $from_);
            $field = $froms[0];
            if (count($this->maildata)) {
                foreach ($this->maildata as $DATA) {
                    if (strtolower($DATA[_FF_DATA_NAME]) == strtolower($field)) {
                        if (isset($froms[1])) {
                            $valuepairs = explode(',', $froms[1]);
                            foreach ($valuepairs as $valuepair) {
                                $keyval = explode('>', trim($valuepair));
                                $key = trim($keyval[0]);
                                if (isset($keyval[1])) {
                                    $value = trim($keyval[1]);

                                    if ($DATA[_FF_DATA_TYPE] == 'Checkbox Group') {

                                        $data_value = explode(', ', strtolower($DATA[_FF_DATA_VALUE]));

                                        if (in_array(strtolower($key), $data_value)) {
                                            $from = $value;
                                        }
                                    } else {

                                        if (strtolower($key) == strtolower($DATA[_FF_DATA_VALUE])) {
                                            $from = $value;
                                            break;
                                        }
                                    }
                                }
                            }
                        } else {
                            $from = $DATA[_FF_DATA_VALUE];
                        }
                        break;
                    }
                }
            }
        }

        if (bf_startsWith(trim($fromname), '{') && bf_endsWith(trim($fromname), '}')) {
            $fromname_ = trim($fromname);
            $fromname_ = trim($fromname_, '{}');
            $froms = explode(':', $fromname_);
            $field = $froms[0];
            if (count($this->maildata)) {
                foreach ($this->maildata as $DATA) {
                    if (strtolower($DATA[_FF_DATA_NAME]) == strtolower($field)) {

                        if (isset($froms[1])) {
                            $valuepairs = explode(',', $froms[1]);
                            foreach ($valuepairs as $valuepair) {
                                $keyval = explode('>', trim($valuepair));
                                $key = trim($keyval[0]);
                                if (isset($keyval[1])) {
                                    $value = trim($keyval[1]);

                                    if ($DATA[_FF_DATA_TYPE] == 'Checkbox Group') {

                                        $data_value = explode(', ', strtolower($DATA[_FF_DATA_VALUE]));

                                        if (strtolower($key) == strtolower($DATA[_FF_DATA_VALUE])) {
                                            $fromname = $value;
                                        }
                                    } else {
                                        if (strtolower($key) == strtolower($DATA[_FF_DATA_VALUE])) {
                                            $fromname = $value;
                                            break;
                                        }
                                    }
                                }
                            }
                        } else {
                            $fromname = $DATA[_FF_DATA_VALUE];
                        }
                        break;
                    }
                }
            }
        }

        $signatures = array();

        $attachToAdminMail = BFRequest::getVar('attachToAdminMail', array());
        if (count($this->maildata)) {
            foreach ($this->maildata as $data) {
                if (isset($attachToAdminMail[$data[_FF_DATA_NAME]])) {
                    if (isset($data[_FF_DATA_FILE_SERVERPATH])) {
                        $testEx = explode("\n", trim($data[_FF_DATA_FILE_SERVERPATH]));
                        $cntTestEx = count($testEx);
                        if ($cntTestEx > 1) {
                            for ($ex = 0; $ex < $cntTestEx; $ex++) {

                                if (strpos(strtolower($testEx[$ex]), '{cbsite}') === 0) {
                                    $testEx[$ex] = str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), $testEx[$ex]);
                                }

                                if (strpos(strtolower($testEx[$ex]), '{site}') === 0) {
                                    $testEx[$ex] = str_replace(array('{site}', '{site}'), array(JPATH_SITE, JPATH_SITE), $testEx[$ex]);
                                }

                                if (!is_array($attachment) && $attachment != '') {
                                    $attachment = array_merge(array(trim($testEx[$ex])), array($attachment));
                                } else if (is_array($attachment)) {
                                    $attachment = array_merge(array(trim($testEx[$ex])), $attachment);
                                } else {
                                    $attachment = trim($testEx[$ex]);
                                }
                            }
                        } else {

                            if (strpos(strtolower(trim($data[_FF_DATA_FILE_SERVERPATH])), '{cbsite}') === 0) {
                                $data[_FF_DATA_FILE_SERVERPATH] = str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), trim($data[_FF_DATA_FILE_SERVERPATH]));
                            }

                            if (strpos(strtolower(trim($data[_FF_DATA_FILE_SERVERPATH])), '{site}') === 0) {
                                $data[_FF_DATA_FILE_SERVERPATH] = str_replace(array('{site}', '{site}'), array(JPATH_SITE, JPATH_SITE), trim($data[_FF_DATA_FILE_SERVERPATH]));
                            }

                            if (!is_array($attachment) && $attachment != '') {
                                $attachment = array_merge(array(trim($data[_FF_DATA_FILE_SERVERPATH])), array($attachment));
                            } else if (is_array($attachment)) {
                                $attachment = array_merge(array(trim($data[_FF_DATA_FILE_SERVERPATH])), $attachment);
                            } else {
                                $attachment = trim($data[_FF_DATA_FILE_SERVERPATH]);
                            }
                        }
                    }
                }

                if ($data[_FF_DATA_TYPE] == 'Signature' && $this->formrow->emailxml != 4) {

                    $signatures[] = JPATH_SITE . '/media/breezingforms/signatures/' . $data[_FF_DATA_VALUE];
                }
            }
        }

        if (is_array($attachment) && count($signatures) > 0) {

            $attachment = array_merge($attachment, $signatures);
        } else if (!is_array($attachment) && count($signatures) > 0) {
            $attachment = array_merge(array($attachment), $signatures);
        } else if (count($signatures) > 0) {
            $attachment = $signatures;
        }

        if (!$this->sendNotificationAfterPayment) {
            for ($i = 0; $i < $recipientsSize; $i++) {
                $this->sendMail($from, $fromname, $recipients[$i], $subject, $body, $attachment, $isHtml, null, null, $alt_sender);
            }
        } else {

            $paymentCache = JPATH_SITE . '/media/breezingforms/payment_cache/';
            mt_srand();
            $paymentFile = $this->form . '_' . $this->record_id . '_admin_' . md5(date('YmdHis') . mt_rand(0, mt_getrandmax())) . '.txt';
            $i = 0;
            while (file_exists($paymentCache . $paymentFile)) {
                if ($i > 1000) {
                    break;
                }
                mt_srand();
                $paymentFile = $this->form . '_' . $this->record_id . '_admin_' . md5(date('YmdHis') . mt_rand(0, mt_getrandmax())) . '.txt';
                $i++;
            }

            if (!file_exists($paymentCache . $paymentFile)) {
                $later_content = serialize(
                    array(
                        'from' => $from,
                        'fromname' => $fromname,
                        'recipients' => $recipients,
                        'subject' => $subject,
                        'body' => $body,
                        'attachment' => $attachment,
                        'isHtml' => $isHtml,
                        'alt_sender' => $alt_sender
                    )
                );
                File::write($paymentCache . $paymentFile, $later_content);
            }
        }
    }

    // sendEmailNotification

    function getFormTitleTranslated()
    {
        if (trim($this->formrow->template_code_processed) == 'QuickMode') {
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
            $dataObject = Zend_Json::decode(bf_b64dec($this->formrow->template_code));
            $rootMdata = $dataObject['properties'];

            $default = ComponentHelper::getParams('com_languages')->get('site');
            $language_tag = Factory::getApplication()->getLanguage()->getTag() != $default ? Factory::getApplication()->getLanguage()->getTag() : 'zz-ZZ';

            /* translatables */
            if (isset($rootMdata['title_translation' . $language_tag]) && $rootMdata['title_translation' . $language_tag] != '') {
                return $rootMdata['title_translation' . $language_tag];
            }
            /* translatables end */
            return '';
        }
    }

    function getFieldTranslated($field, $name, &$res, $dataObject = null, $childrenLength = 0)
    {

        if (count(LanguageHelper::getLanguages()) == 1) {
            return;
        }

        if (trim($this->formrow->template_code_processed) == 'QuickMode') {
            if ($dataObject === null && $childrenLength == 0) {
                require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
                require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
                $dataObject = Zend_Json::decode(bf_b64dec($this->formrow->template_code));
            }

            if (isset($dataObject['attributes']) && isset($dataObject['properties'])) {
                if ($dataObject['properties']['type'] == 'element' && isset($dataObject['properties']['bfName'])) {
                    $language_tag = '';
                    jimport('joomla.application.component.helper');
                    $default = ComponentHelper::getParams('com_languages')->get('site');
                    $language_tag = Factory::getApplication()->getLanguage()->getTag() != $default ? Factory::getApplication()->getLanguage()->getTag() : 'zz-ZZ';
                    if (trim($name) == trim($dataObject['properties']['bfName']) && isset($dataObject['properties'][$field . '_translation' . $language_tag]) && $dataObject['properties'][$field . '_translation' . $language_tag] != '') {
                        $res = addslashes($dataObject['properties'][$field . '_translation' . $language_tag]);
                        return;
                    }
                }
            }
            if (isset($dataObject['children']) && count($dataObject['children']) != 0) {
                $childrenAmount = count($dataObject['children']);
                for ($i = 0; $i < $childrenAmount; $i++) {
                    $this->getFieldTranslated($field, $name, $res, $dataObject['children'][$i], $childrenAmount);
                }
            }
        }
    }

    function sendMailbackNotification()
    {
        global $ff_config;

        $signatures = array();

        $mainframe = Factory::getApplication();

        if ($this->dying)
            return;
        $from = $this->formrow->mb_alt_mailfrom != '' ? $this->formrow->mb_alt_mailfrom : $mainframe->getCfg('mailfrom');
        $fromname = $this->formrow->mb_alt_fromname != '' ? $this->formrow->mb_alt_fromname : $mainframe->getCfg('fromname');

        $_senders = '';
        if ($this->formrow->emailntf == 2)
            $_senders = $this->formrow->emailadr;
        else
            $_senders = $ff_config->emailadr;

        $_senders = explode(';', $_senders);

        $alt_sender = '';
        foreach ($_senders as $_sender) {

            $test = explode(':', $_sender);
            if (count($test) == 2 && strtolower(trim($test[0])) == 'sender') {
                $alt_sender = trim($test[1]);
            }
        }

        $accept = BFRequest::getVar('mailbackConnectWith', array());
        $sender = BFRequest::getVar('mailbackSender', array());
        $attachToUserMail = BFRequest::getVar('attachToUserMail', array());

        $mailbackfiles = array();
        $recipients = array();
        for ($i = 0; $i < $this->rowcount; $i++) {
            $row = $this->rows[$i];
            $mb = BFRequest::getVar('ff_nm_' . $row->name, '');
            if ($row->mailback == 1) {
                $mbCnt = count($mb);
                for ($x = 0; $x < $mbCnt; $x++) {
                    if (isset($mb[$x]) && trim($mb[$x]) != '' && bf_is_email(trim($mb[$x]))) {
                        $yesno = array('false', '');
                        $checked = array('');
                        if (isset($accept[$row->name])) {
                            $yesno = explode('_', $accept[$row->name]);
                            $checked = BFRequest::getVar('ff_nm_' . $yesno[1], '');
                        }

                        //if (isset($sender[$row->name]) && !$customSender) {
                        //    $from = trim($mb[$x]);
                        //    $fromname = trim($mb[$x]);
                        //    $customSender = true;
                        //}
                        if (!isset($accept[$row->name]) || (isset($accept[$row->name]) && $yesno[0] == 'true' && $checked[0] != '')) {
                            $recipients[] = trim($mb[$x]);
                            if (!isset($mailbackfiles[trim($mb[$x])]))
                                $mailbackfiles[trim($mb[$x])] = array();
                            if (count($this->maildata)) {
                                foreach ($this->maildata as $data) {
                                    if (isset($data[_FF_DATA_FILE_SERVERPATH])) {
                                        if (isset($attachToUserMail[$data[_FF_DATA_NAME]])) {
                                            $testEx = explode("\n", trim($data[_FF_DATA_FILE_SERVERPATH]));
                                            $cntTestEx = count($testEx);
                                            if ($cntTestEx > 1) {
                                                for ($ex = 0; $ex < $cntTestEx; $ex++) {

                                                    if (strpos(strtolower(trim($testEx[$ex])), '{cbsite}') === 0) {
                                                        $testEx[$ex] = str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), trim($testEx[$ex]));
                                                    }

                                                    if (strpos(strtolower(trim($testEx[$ex])), '{site}') === 0) {
                                                        $testEx[$ex] = str_replace(array('{site}', '{site}'), array(JPATH_SITE, JPATH_SITE), trim($testEx[$ex]));
                                                    }

                                                    $mailbackfiles[trim($mb[$x])][] = trim($testEx[$ex]);
                                                }
                                            } else {

                                                if (strpos(strtolower(trim($data[_FF_DATA_FILE_SERVERPATH])), '{cbsite}') === 0) {
                                                    $data[_FF_DATA_FILE_SERVERPATH] = str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), trim($data[_FF_DATA_FILE_SERVERPATH]));
                                                }

                                                if (strpos(strtolower(trim($data[_FF_DATA_FILE_SERVERPATH])), '{site}') === 0) {
                                                    $data[_FF_DATA_FILE_SERVERPATH] = str_replace(array('{site}', '{site}'), array(JPATH_SITE, JPATH_SITE), trim($data[_FF_DATA_FILE_SERVERPATH]));
                                                }

                                                $mailbackfiles[trim($mb[$x])][] = trim($data[_FF_DATA_FILE_SERVERPATH]);
                                            }
                                        }
                                    }
                                }
                            }
                            if (trim($row->mailbackfile) != '' && file_exists(trim($row->mailbackfile))) {
                                $mailbackfiles[trim($mb[$x])][] = trim($row->mailbackfile);
                            }
                        }
                    }
                }
            }
        }

        $recipientsSize = count($recipients);

        $subject = BFText::_('COM_BREEZINGFORMS_PROCESS_FORMRECRECEIVED');
        if ($this->formrow->mb_custom_mail_subject != '') {
            $subject = $this->formrow->mb_custom_mail_subject;
        }

        $body = '';
        $isHtml = false;
        $filter = array();

        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
        $areas = Zend_Json::decode($this->formrow->template_areas);
        if (trim($this->formrow->template_code_processed) == 'QuickMode' && is_array($areas)) {
            foreach ($areas as $area) { // don't worry, size is only 1 in QM
                if (isset($area['elements'])) {
                    foreach ($area['elements'] as $element) {
                        if (isset($element['hideInMailback']) && $element['hideInMailback'] && isset($element['name'])) {
                            $filter[] = $element['name'];
                        }
                    }
                }
                break; // just in case
            }
        }

        // dynamic mailfroms

        if (bf_startsWith(trim($from), '{') && bf_endsWith(trim($from), '}')) {
            $from_ = trim($from);
            $from_ = trim($from_, '{}');
            $froms = explode(':', $from_);
            $field = $froms[0];
            if (count($this->maildata)) {
                foreach ($this->maildata as $DATA) {
                    if (!in_array($DATA[_FF_DATA_NAME], $filter)) {
                        if (strtolower($DATA[_FF_DATA_NAME]) == strtolower($field)) {
                            if (isset($froms[1])) {
                                $valuepairs = explode(',', $froms[1]);
                                foreach ($valuepairs as $valuepair) {
                                    $keyval = explode('>', trim($valuepair));
                                    $key = trim($keyval[0]);
                                    if (isset($keyval[1])) {
                                        $value = trim($keyval[1]);

                                        if ($DATA[_FF_DATA_TYPE] == 'Checkbox Group') {

                                            $data_value = explode(', ', strtolower($DATA[_FF_DATA_VALUE]));

                                            if (in_array(strtolower($key), $data_value)) {
                                                $from = $value;
                                            }
                                        } else {

                                            if (strtolower($key) == strtolower($DATA[_FF_DATA_VALUE])) {
                                                $from = $value;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else {
                                $from = $DATA[_FF_DATA_VALUE];
                            }
                            break;
                        }
                    }
                }
            }
        }

        if (bf_startsWith(trim($fromname), '{') && bf_endsWith(trim($fromname), '}')) {
            $fromname_ = trim($fromname);
            $fromname_ = trim($fromname_, '{}');
            $froms = explode(':', $fromname_);
            $field = $froms[0];
            if (count($this->maildata)) {
                foreach ($this->maildata as $DATA) {
                    if (!in_array($DATA[_FF_DATA_NAME], $filter)) {
                        if (strtolower($DATA[_FF_DATA_NAME]) == strtolower($field)) {
                            if (isset($froms[1])) {
                                $valuepairs = explode(',', $froms[1]);
                                foreach ($valuepairs as $valuepair) {
                                    $keyval = explode('>', trim($valuepair));
                                    $key = trim($keyval[0]);
                                    if (isset($keyval[1])) {
                                        $value = trim($keyval[1]);
                                        if ($DATA[_FF_DATA_TYPE] == 'Checkbox Group') {

                                            $data_value = explode(', ', strtolower($DATA[_FF_DATA_VALUE]));

                                            if (in_array(strtolower($key), $data_value)) {
                                                $fromname = $value;
                                            }
                                        } else {
                                            if (strtolower($key) == strtolower($DATA[_FF_DATA_VALUE])) {
                                                $fromname = $value;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else {
                                $fromname = $DATA[_FF_DATA_VALUE];
                            }
                            break;
                        }
                    }
                }
            }
        }

        if ($this->formrow->mb_email_type == 0) {

            $foundTpl = false;
            $tplFile = '';
            $formTxtFile = JPATH_SITE . '/media/breezingforms/mailtpl/' . $this->formrow->name . '_mailback.txt.php';
            $formHtmlFile = JPATH_SITE . '/media/breezingforms/mailtpl/' . $this->formrow->name . '_mailback.html.php';
            $defaultTxtFile = JPATH_SITE . '/media/breezingforms/mailtpl/mailbacktpl.txt.php';
            $defaultHtmlFile = JPATH_SITE . '/media/breezingforms/mailtpl/mailbacktpl.html.php';

            if (@file_exists($formHtmlFile) && @is_readable($formHtmlFile)) {
                $tplFile = $formHtmlFile;
                $foundTpl = true;
                $isHtml = true;
            } else if (@file_exists($formTxtFile) && @is_readable($formTxtFile)) {
                $tplFile = $formTxtFile;
                $foundTpl = true;
            } else if (@file_exists($defaultHtmlFile) && @is_readable($defaultHtmlFile)) {
                $tplFile = $defaultHtmlFile;
                $foundTpl = true;
                $isHtml = true;
            } else if (@file_exists($defaultTxtFile) && @is_readable($defaultTxtFile)) {
                $tplFile = $defaultTxtFile;
                $foundTpl = true;
            }

            if ($foundTpl) {

                $NL = nl();

                $PROCESS_RECORDSAVEDID = '';
                $RECORD_ID = '';

                if ($this->record_id != '') {
                    $PROCESS_RECORDSAVEDID = BFText::_('COM_BREEZINGFORMS_PROCESS_RECORDSAVEDID');
                    $RECORD_ID = $this->record_id;
                }

                $PROCESS_FORMID = BFText::_('COM_BREEZINGFORMS_PROCESS_FORMID');
                $FORM = $this->form;

                $PROCESS_FORMTITLE = BFText::_('COM_BREEZINGFORMS_PROCESS_FORMTITLE');

                $form_title_translated = $this->getFormTitleTranslated();
                $TITLE = $form_title_translated != '' ? $form_title_translated : $this->formrow->title;

                $PROCESS_FORMNAME = BFText::_('COM_BREEZINGFORMS_PROCESS_FORMNAME');
                $NAME = $this->formrow->name;

                $PROCESS_SUBMITTEDAT = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTEDAT');
                $SUBMITTED = $this->submitted;

                $tz = 'UTC';
                $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

                $date_ = Factory::getDate($this->submitted, $tz);
                $offset = $date_->getOffsetFromGMT();
                if ($offset > 0) {
                    $date_->add(new DateInterval('PT' . $offset . 'S'));
                } else if ($offset < 0) {
                    $offset = $offset * -1;
                    $date_->sub(new DateInterval('PT' . $offset . 'S'));
                }

                $SUBMITTED = $date_->format('Y-m-d H:i:s', true);

                $PROCESS_SUBMITTERIP = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERIP');
                $IP = $this->ip;

                $PROCESS_PROVIDER = BFText::_('COM_BREEZINGFORMS_PROCESS_PROVIDER');
                $PROVIDER = $this->provider;

                $PROCESS_BROWSER = BFText::_('COM_BREEZINGFORMS_PROCESS_BROWSER');
                $BROWSER = $this->browser;

                $PROCESS_OPSYS = BFText::_('COM_BREEZINGFORMS_PROCESS_OPSYS');
                $OPSYS = $this->opsys;

                $PROCESS_SUBMITTERID = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERID');
                $SUBMITTERID = 0;

                $PROCESS_SUBMITTERUSERNAME = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERUSERNAME');
                $SUBMITTERUSERNAME = '-';

                $PROCESS_SUBMITTERFULLNAME = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERFULLNAME');
                $SUBMITTERFULLNAME = '-';

                if (Factory::getUser()->get('id', 0) > 0) {
                    $SUBMITTERID = Factory::getUser()->get('id', 0);
                    $SUBMITTERUSERNAME = Factory::getUser()->get('username', '');
                    $SUBMITTERFULLNAME = Factory::getUser()->get('name', '');
                }

                $MAILDATA = array();
                if (count($this->maildata)) {
                    foreach ($this->maildata as $DATA) {
                        if (!in_array($DATA[_FF_DATA_NAME], $filter)) {
                            $trans_title = '';
                            $this->getFieldTranslated('label', $DATA[_FF_DATA_NAME], $trans_title);
                            $subject = str_replace('{' . $DATA[_FF_DATA_NAME] . ':label}', $trans_title != '' ? $trans_title : strip_tags($DATA[_FF_DATA_TITLE]), $subject);
                            $subject = str_replace('{' . $DATA[_FF_DATA_NAME] . ':title}', $trans_title != '' ? $trans_title : strip_tags($DATA[_FF_DATA_TITLE]), $subject);
                            $subject = str_replace('{' . $DATA[_FF_DATA_NAME] . ':value}', $DATA[_FF_DATA_VALUE], $subject);
                            $subject = str_replace('{' . $DATA[_FF_DATA_NAME] . '}', $DATA[_FF_DATA_VALUE], $subject);
                            $DATA[_FF_DATA_TITLE] = $trans_title != '' ? $trans_title : strip_tags($DATA[_FF_DATA_TITLE]);
                            $MAILDATA[] = $DATA;

                            if ($DATA[_FF_DATA_TYPE] == 'Signature' && $this->formrow->mb_emailxml != 4) {

                                $signatures[] = JPATH_SITE . '/media/breezingforms/signatures/' . $DATA[_FF_DATA_VALUE];
                            }
                        }
                    }
                }

                ob_start();
                include($tplFile);
                $body = ob_get_contents();
                ob_end_clean();
            } else {
                // fallback if no template exists

                if ($this->record_id != '')
                    $body .= BFText::_('COM_BREEZINGFORMS_PROCESS_RECORDSAVEDID') . " " . $this->record_id . nl() . nl();

                $form_title_translated = $this->getFormTitleTranslated();

                $tz = 'UTC';
                $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

                $submitted = $this->submitted;
                $date_ = Factory::getDate($this->submitted, $tz);
                $offset = $date_->getOffsetFromGMT();
                if ($offset > 0) {
                    $date_->add(new DateInterval('PT' . $offset . 'S'));
                } else if ($offset < 0) {
                    $offset = $offset * -1;
                    $date_->sub(new DateInterval('PT' . $offset . 'S'));
                }

                $submitted = $date_->format('Y-m-d H:i:s', true);

                $body .= BFText::_('COM_BREEZINGFORMS_PROCESS_FORMID') . ": " . $this->form . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_FORMTITLE') . ": " . ($form_title_translated != '' ? $form_title_translated : $this->formrow->title) . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_FORMNAME') . ": " . $this->formrow->name . nl() . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTEDAT') . ": " . $submitted . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERIP') . ": " . $this->ip . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERID') . ": " . Factory::getUser()->get('id', 0) . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERUSERNAME') . ": " . Factory::getUser()->get('username', '') . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERFULLNAME') . ": " . Factory::getUser()->get('name', '') . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_PROVIDER') . ": " . $this->provider . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_BROWSER') . ": " . $this->browser . nl() .
                    BFText::_('COM_BREEZINGFORMS_PROCESS_OPSYS') . ": " . $this->opsys . nl() . nl();
                if (count($this->maildata)) {
                    foreach ($this->maildata as $data) {
                        $trans_title = '';
                        $this->getFieldTranslated('label', $data[_FF_DATA_NAME], $trans_title);
                        $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':label}', $trans_title != '' ? $trans_title : strip_tags($data[_FF_DATA_TITLE]), $subject);
                        $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':title}', $trans_title != '' ? $trans_title : strip_tags($data[_FF_DATA_TITLE]), $subject);
                        $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', $data[_FF_DATA_VALUE], $subject);
                        $subject = str_replace('{' . $data[_FF_DATA_NAME] . '}', $data[_FF_DATA_VALUE], $subject);
                        if (!in_array($data[_FF_DATA_NAME], $filter)) {
                            $body .= strip_tags($data[_FF_DATA_TITLE]) . ": " . $data[_FF_DATA_VALUE] . nl();
                        }

                        if ($data[_FF_DATA_TYPE] == 'Signature' && $this->formrow->mb_emailxml != 4) {

                            $signatures[] = JPATH_SITE . '/media/breezingforms/signatures/' . $data[_FF_DATA_VALUE];
                        }
                    }
                }
            }
        } else {

            $body = $this->formrow->mb_email_custom_template;

            $RECORD_ID = '';
            if ($this->record_id != '') {
                $RECORD_ID = $this->record_id;
            }

            $FORM = $this->form;

            $form_title_translated = $this->getFormTitleTranslated();

            $TITLE = $form_title_translated != '' ? $form_title_translated : $this->formrow->title;
            $FORMNAME = $this->formrow->name;
            $SUBMITTED = $this->submitted;

            $tz = 'UTC';
            $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

            $submitted = $this->submitted;
            $date_ = Factory::getDate($this->submitted, $tz);
            $offset = $date_->getOffsetFromGMT();
            if ($offset > 0) {
                $date_->add(new DateInterval('PT' . $offset . 'S'));
            } else if ($offset < 0) {
                $offset = $offset * -1;
                $date_->sub(new DateInterval('PT' . $offset . 'S'));
            }

            $SUBMITTED = $date_->format('Y-m-d H:i:s', true);

            $IP = $this->ip;
            $PROVIDER = $this->provider;
            $BROWSER = $this->browser;
            $OPSYS = $this->opsys;
            $SUBMITTERID = 0;
            $SUBMITTERUSERNAME = '-';
            $SUBMITTERFULLNAME = '-';
            if (Factory::getUser()->get('id', 0) > 0) {
                $SUBMITTERID = Factory::getUser()->get('id', 0);
                $SUBMITTERUSERNAME = Factory::getUser()->get('username', '');
                $SUBMITTERFULLNAME = Factory::getUser()->get('name', '');
            }

            $body = str_replace('{BF_RECORD_ID:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_RECORDSAVEDID'), $body);
            $body = str_replace('{BF_RECORD_ID:value}', $RECORD_ID, $body);

            $body = str_replace('{BF_FORM_ID:label}', BFText::_('Form ID'), $body);
            $body = str_replace('{BF_FORM_ID:value}', $this->form_id, $body);

            $body = str_replace('{BF_FORM:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_FORMID'), $body);
            $body = str_replace('{BF_FORM:value}', $FORM, $body);

            $body = str_replace('{BF_TITLE:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_FORMTITLE'), $body);
            $body = str_replace('{BF_TITLE:value}', $TITLE, $body);

            $body = str_replace('{BF_FORMNAME:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_FORMNAME'), $body);
            $body = str_replace('{BF_FORMNAME:value}', $FORMNAME, $body);

            $body = str_replace('{BF_SUBMITTED:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTEDAT'), $body);
            $body = str_replace('{BF_SUBMITTED:value}', $SUBMITTED, $body);

            $body = str_replace('{BF_IP:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERIP'), $body);
            $body = str_replace('{BF_IP:value}', $IP, $body);

            $body = str_replace('{BF_PROVIDER:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_PROVIDER'), $body);
            $body = str_replace('{BF_PROVIDER:value}', $PROVIDER, $body);

            $body = str_replace('{BF_BROWSER:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_BROWSER'), $body);
            $body = str_replace('{BF_BROWSER:value}', $BROWSER, $body);

            $body = str_replace('{BF_OPSYS:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_OPSYS'), $body);
            $body = str_replace('{BF_OPSYS:value}', $OPSYS, $body);

            $body = str_replace('{BF_SUBMITTERID:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERID'), $body);
            $body = str_replace('{BF_SUBMITTERID:value}', $SUBMITTERID, $body);

            $body = str_replace('{BF_SUBMITTERUSERNAME:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERUSERNAME'), $body);
            $body = str_replace('{BF_SUBMITTERUSERNAME:value}', $SUBMITTERUSERNAME, $body);

            $body = str_replace('{BF_SUBMITTERFULLNAME:label}', BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERFULLNAME'), $body);
            $body = str_replace('{BF_SUBMITTERFULLNAME:value}', $SUBMITTERFULLNAME, $body);

            if (count($this->savedata)) {
                foreach ($this->savedata as $data) {

                    $regex = "/([\{]hide " . $data[_FF_DATA_NAME] . "[\}])(.*)([\{][\/]hide[\}])/isU";

                    if ($data[_FF_DATA_VALUE] == '') {
                        $body = preg_replace($regex, "", $body);
                    } else {
                        $body = preg_replace($regex, '$2', $body);
                    }

                    if ($data[_FF_DATA_VALUE] == '') {
                        $subject = preg_replace($regex, "", $subject);
                    } else {
                        $subject = preg_replace($regex, '$2', $subject);
                    }
                }
            }

            if (count($this->maildata)) {
                foreach ($this->maildata as $data) {

                    $trans_title = '';
                    $this->getFieldTranslated('label', $data[_FF_DATA_NAME], $trans_title);
                    $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':label}', $trans_title != '' ? $trans_title : strip_tags($data[_FF_DATA_TITLE]), $subject);
                    $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':title}', $trans_title != '' ? $trans_title : strip_tags($data[_FF_DATA_TITLE]), $subject);
                    $subject = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', $data[_FF_DATA_VALUE], $subject);
                    $subject = str_replace('{' . $data[_FF_DATA_NAME] . '}', $data[_FF_DATA_VALUE], $subject);
                    if (!in_array($data[_FF_DATA_NAME], $filter)) {
                        $body = str_replace('{' . $data[_FF_DATA_NAME] . ':label}', $trans_title != '' ? $trans_title : strip_tags($data[_FF_DATA_TITLE]), $body);
                        if ($this->formrow->mb_email_custom_html) {
                            $body = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', str_replace(array("\n", "\r"), array('<br/>', ''), $data[_FF_DATA_VALUE]), $body);
                        } else {
                            $body = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', $data[_FF_DATA_VALUE], $body);
                        }
                    } else {
                        $body = str_replace('{' . $data[_FF_DATA_NAME] . ':label}', '', $body);
                        $body = str_replace('{' . $data[_FF_DATA_NAME] . ':value}', '', $body);
                    }

                    if ($data[_FF_DATA_TYPE] == 'Signature' && $this->formrow->mb_emailxml != 4) {

                        $signatures[] = JPATH_SITE . '/media/breezingforms/signatures/' . $data[_FF_DATA_VALUE];
                    }
                }
            }

            $body = preg_replace("/{([a-zA-Z0-9_\-])*:(label|value)}/", '', $body);

            if ($this->formrow->mb_email_custom_html) {
                $isHtml = true;
            }
        }

        $attachment = NULL;
        if ($this->formrow->mb_emailxml > 0 && $this->formrow->mb_emailxml < 3) {
            $attachment = $this->expxml($filter, true, true);
            if ($this->status != _FF_STATUS_OK)
                return;
        } else if ($this->formrow->mb_emailxml == 3) {
            $attachment = $this->expcsv($filter, true);
            if ($this->status != _FF_STATUS_OK)
                return;
        } else if ($this->formrow->mb_emailxml == 4) {
            $attachment = $this->exppdf($filter, true, true);
            if ($this->status != _FF_STATUS_OK)
                return;
        }

        if (!$this->sendNotificationAfterPayment) {
            for ($i = 0; $i < $recipientsSize; $i++) {
                if (isset($mailbackfiles[$recipients[$i]])) {
                    if (!is_array($attachment) && $attachment != '') {
                        $attachment = array_merge($mailbackfiles[$recipients[$i]], array($attachment));
                    } else if (is_array($attachment)) {
                        $attachment = array_merge($mailbackfiles[$recipients[$i]], $attachment);
                    } else {
                        $attachment = $mailbackfiles[$recipients[$i]];
                    }
                }

                if (is_array($attachment) && count($signatures) > 0) {

                    $attachment = array_merge($attachment, $signatures);
                } else if (!is_array($attachment) && count($signatures) > 0) {
                    $attachment = array_merge(array($attachment), $signatures);
                } else if (count($signatures) > 0) {
                    $attachment = $signatures;
                }

                $this->sendMail($from, $fromname, $recipients[$i], $subject, $body, $attachment, $isHtml, null, null, $alt_sender);
            }
        } else {

            $paymentCache = JPATH_SITE . '/media/breezingforms/payment_cache/';
            mt_srand();
            $paymentFile = $this->form . '_' . $this->record_id . '_mailback_' . md5(date('YmdHis') . mt_rand(0, mt_getrandmax())) . '.txt';
            $i = 0;
            while (file_exists($paymentCache . $paymentFile)) {
                if ($i > 1000) {
                    break;
                }
                mt_srand();
                $paymentFile = $this->form . '_' . $this->record_id . '_mailback_' . md5(date('YmdHis') . mt_rand(0, mt_getrandmax())) . '.txt';
                $i++;
            }

            if (!file_exists($paymentCache . $paymentFile)) {

                for ($i = 0; $i < $recipientsSize; $i++) {
                    if (isset($mailbackfiles[$recipients[$i]])) {
                        if (!is_array($attachment) && $attachment != '') {
                            $attachment = array_merge($mailbackfiles[$recipients[$i]], array($attachment));
                        } else if (is_array($attachment)) {
                            $attachment = array_merge($mailbackfiles[$recipients[$i]], $attachment);
                        } else {
                            $attachment = $mailbackfiles[$recipients[$i]];
                        }
                    }
                }

                if (is_array($attachment) && count($signatures) > 0) {

                    $attachment = array_merge($attachment, $signatures);
                } else if (!is_array($attachment) && count($signatures) > 0) {
                    $attachment = array_merge(array($attachment), $signatures);
                } else if (count($signatures) > 0) {
                    $attachment = $signatures;
                }

                $later_content = serialize(
                    array(
                        'from' => $from,
                        'fromname' => $fromname,
                        'recipients' => $recipients,
                        'subject' => $subject,
                        'body' => $body,
                        'attachment' => $attachment,
                        'isHtml' => $isHtml,
                        'alt_sender' => $alt_sender
                    )
                );
                File::write($paymentCache . $paymentFile, $later_content);
            }
        }

        $this->mailbackRecipients = $recipients;
    }

    function sendSalesforceNotification()
    {

        if ($this->formrow->salesforce_enabled != 1) {
            return;
        }

        define("BF_SOAP_CLIENT_BASEDIR", JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_breezingforms' . DS . 'libraries' . DS . 'salesforce');

        if (!class_exists('SforcePartnerClient')) {
            require_once(BF_SOAP_CLIENT_BASEDIR . '/SforcePartnerClient.php');
        }
        if (!class_exists('SforceHeaderOptions')) {
            require_once(BF_SOAP_CLIENT_BASEDIR . '/SforceHeaderOptions.php');
        }

        try {

            $mySforceConnection = new SforcePartnerClient();
            $trunc = new AllowFieldTruncationHeader(true);
            $mySforceConnection->setAllowFieldTruncationHeader($trunc);
            $mySoapClient = $mySforceConnection->createConnection(BF_SOAP_CLIENT_BASEDIR . '/partner.wsdl.xml');
            $mylogin = $mySforceConnection->login($this->formrow->salesforce_username, $this->formrow->salesforce_password . $this->formrow->salesforce_token);
            $sobjects = $mySforceConnection->describeSObject($this->formrow->salesforce_type)->fields;

            $fields = array();
            $this->formrow->salesforce_fields = explode(',', $this->formrow->salesforce_fields);

            foreach ($this->formrow->salesforce_fields as $sfields) {
                foreach ($this->sfdata as $savedata) {
                    $sfield = explode('::', $sfields);
                    if ($sfield[0] == $savedata[1]) {
                        foreach ($sobjects as $sobject) {
                            // forcing some primitives
                            if ($sobject->name == $sfield[1]) {
                                switch ($sobject->type) {
                                    case 'boolean':
                                        $savedata[4] = ($savedata[4] ? 1 : 0);
                                        break;
                                    case 'int':
                                        $savedata[4] = intval($savedata[4]);
                                        break;
                                    case 'double':
                                        $savedata[4] = doubleval($savedata[4]);
                                        break;
                                }
                                break;
                            }
                        }
                        $fields[$sfield[1]] = '<![CDATA[' . $savedata[4] . ']]>'; // bug in SF Toolkit appeareantly requires CDATA
                        break;
                    }
                }
            }

            $sObject = new SObject();
            $sObject->fields = $fields;
            $sObject->type = $this->formrow->salesforce_type;

            $createResponse = $mySforceConnection->create(array($sObject));

            ob_start();
            print_r($createResponse);
            $c = date('Y-m-d H:i:s') . ': ' . ob_get_contents() . "\r\n";
            ob_end_clean();

            file_put_contents(JPATH_SITE . '/sf.log', $c, FILE_APPEND);
        } catch (Exception $e) {

            $c = date('Y-m-d H:i:s') . ': ' . $e->getMessage() . "\r\n";
            file_put_contents(JPATH_SITE . '/sf.log', $c, FILE_APPEND);

            echo 'Salesforce Exception: ' . $e->getMessage();
            session_write_close();
            exit;
        }
    }

    function sendMailChimpNotification()
    {

        $mainframe = Factory::getApplication();

        // listSubscribe(string apikey, string id, string email_address, array merge_vars, string email_type, boolean double_optin, boolean update_existing, boolean replace_interests, boolean send_welcome)

        if (!class_exists('MailChimp')) {
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/mailchimp/MCAPI.class.php');
        }



        if (trim($this->formrow->mailchimp_email_field) != '' && trim($this->formrow->mailchimp_api_key) != '' && trim($this->formrow->mailchimp_list_id) != '' && count($this->maildata)) {

            $email = '';
            $htmlTextMobile = 'text';
            $checked = true;
            $unsubscribe = false;
            $mergeVars = array();
            $htmlTextMobileField = trim($this->formrow->mailchimp_text_html_mobile_field);
            $checkboxField = trim($this->formrow->mailchimp_checkbox_field);
            $unsubscribeField = trim($this->formrow->mailchimp_unsubscribe_field);
            $emailField = trim($this->formrow->mailchimp_email_field);
            $mergeVarFields = explode(',', str_replace(' ', '', $this->formrow->mailchimp_mergevars));
            $api = new MailChimp(trim($this->formrow->mailchimp_api_key));
            $list_ids = explode(',', trim($this->formrow->mailchimp_list_id));

            if ($checkboxField != '') {
                $box = BFRequest::getVar('ff_nm_' . $checkboxField, array(''));
                if (isset($box[0]) && $box[0] != '') {
                    $checked = true;
                } else {
                    $checked = false;
                }
            }

            if ($unsubscribeField != '') {
                $box = BFRequest::getVar('ff_nm_' . $unsubscribeField, array(''));
                if (isset($box[0]) && $box[0] != '') {
                    $unsubscribe = true;
                }
            }

            if ($htmlTextMobileField != '') {
                $selection = BFRequest::getVar('ff_nm_' . $htmlTextMobileField, array(''));
                if (isset($selection[0]) && $selection[0] != '') {
                    $htmlTextMobile = $selection[0];
                }
            } else {
                $htmlTextMobile = $this->formrow->mailchimp_default_type;
            }

            foreach ($this->maildata as $data) {
                switch ($data[_FF_DATA_NAME]) {
                    case $emailField:
                        $email = bf_is_email(trim($data[_FF_DATA_VALUE])) ? trim($data[_FF_DATA_VALUE]) : '';
                        break;
                    default:
                        if (in_array($data[_FF_DATA_NAME], $mergeVarFields)) {
                            $mergeVars[$data[_FF_DATA_NAME]] = $data[_FF_DATA_VALUE];
                        }
                }
            }

            // MailChimp API v3 update
            foreach ($list_ids as $list_id) {
                $subHash = $api->subscriberHash($email);

                if ($email != '' && $checked) {
                    $try = $api->put('lists/' . trim($list_id) . '/members/' . $subHash, [
                        'email_address' => $email,
                        'merge_fields' => (count($mergeVars) == 0 ? new stdClass() : (object) $mergeVars),
                        'status_if_new' => ($this->formrow->mailchimp_double_optin ? 'pending' : 'subscribed'),
                        'status' => ($this->formrow->mailchimp_double_optin ? 'pending' : 'subscribed'),
                        'email_type' => $htmlTextMobile,
                    ]);
                } else if ($email != '' && $unsubscribe) {
                    $try = $api->put('lists/' . trim($list_id) . '/members/' . $subHash, [
                        'status' => 'unsubscribed',
                    ]);
                    if ($this->formrow->mailchimp_delete_member) {
                        $try = $api->delete('lists/' . trim($list_id) . '/members/' . $subHash);
                    }
                }
                if (!($api->success()) && $this->formrow->mailchimp_send_errors) {
                    $from = $this->formrow->alt_mailfrom != '' ? $this->formrow->alt_mailfrom : $mainframe->getCfg('mailfrom');
                    $fromname = $this->formrow->alt_fromname != '' ? $this->formrow->alt_fromname : $mainframe->getCfg('fromname');
                    $this->sendMail($from, $fromname, $from, 'MailChimp API Error', 'Could not send data to MailChimp for email: ' . $email . "\n\nReason: " . $api->getLastError() . "\n" . implode('', $api->getLastResponse()) . "\n" . implode(' ', $api->getLastRequest()));
                }
            }
        }
    }

    function saveUpload($filename, $userfile_name, $destpath, $timestamp, $useUrl = false, $useUrlDownloadDirectory = '', $resize_target_width = 0, $resize_target_height = 0, $resize_type = '', $resize_bgcolor = '#ffffff', $field_name = '')
    {
        global $ff_config, $mosConfig_fileperms;

        if ($this->dying)
            return '';

        $tz = 'UTC';
        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

        $date_stamp = date('Y_m_d_H_i_s');
        $date_ = Factory::getDate($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $date_stamp = $date_->format('Y_m_d_H_i_s', true);

        $date_stamp2 = date('Y_m_d');
        $date_ = Factory::getDate($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $date_stamp2 = $date_->format('Y_m_d', true);

        $baseDir = Path::clean(str_replace($this->findtags, $this->replacetags, $destpath));

        // test if there is a filemask and remove it from the basepath
        $_baseDir = $baseDir;
        $fmtest = str_replace('{filemask:', '', basename($baseDir));
        if ($fmtest != basename($baseDir)) {
            $baseDir = rtrim(rtrim(str_replace(basename($baseDir), '', $baseDir), '/'), "\\");
        }

        if (!file_exists($baseDir)) {
            $this->status = _FF_STATUS_UPLOAD_FAILED;
            $this->message = BFText::_('COM_BREEZINGFORMS_PROCESS_DIRNOTEXISTS');
            return '';
        } // if

        if ($fmtest != basename($_baseDir)) {
            $fm = basename($_baseDir);
            foreach ($this->rows as $row) {
                $fname = BFRequest::getVar('ff_nm_' . $row->name, array(), 'POST', 'HTML', BFREQUEST_ALLOWHTML);

                foreach ($fname as $_fname) {
                    $fm = str_replace('{filemask:' . strtolower($row->name) . '}', File::makeSafe(trim($_fname)), $fm);
                    // so it works the same like for folders
                    $fm = str_replace('{' . strtolower($row->name) . ':value}', File::makeSafe(trim($_fname)), $fm);
                }
            }
            $fm = str_replace('{filemask:_separator}', '_', $fm);
            $fm = str_replace('{filemask:_username}', trim(Factory::getUser()->get('username')), $fm);
            $fm = str_replace('{filemask:_userid}', trim(Factory::getUser()->get('id')), $fm);
            $fm = str_replace('{filemask:_name}', trim(Factory::getUser()->get('name')), $fm);
            $fm = str_replace('{filemask:_datetime}', trim($date_stamp), $fm);
            $fm = str_replace('{filemask:_date}', trim($date_stamp2), $fm);
            $fm = str_replace('{filemask:_timestamp}', trim(time()), $fm);
            $fm = str_replace('{filemask:_random}', trim(mt_rand(0, mt_getrandmax())), $fm);
            $fm = str_replace('{filemask:_filename}', trim(basename($userfile_name, '.' . File::getExt($userfile_name))), $fm);
            if ($fm == '') {
                $fm = '__empty__';
            }
            $userfile_name = $fm . '.' . File::getExt($userfile_name);
        }

        //if ($timestamp)
        //	$userfile_name = $date_stamp . '_' . $userfile_name;
        $path = $baseDir . DS . $userfile_name;
        //if ($timestamp) $path .= '.'.date('YmdHis');
        if (file_exists($path) && Factory::getSession()->get('bfFileUploadOverride', true)) {
            $rnd = md5(mt_rand(0, mt_getrandmax()));
            $path = $baseDir . DS . $rnd . '_' . $userfile_name;
            //if ($timestamp) $path .= '.'.date('YmdHis');
            if (file_exists($path)) {
                $this->status = _FF_STATUS_UPLOAD_FAILED;
                $this->message = BFText::_('COM_BREEZINGFORMS_PROCESS_FILEEXISTS');
                return '';
            }
        } else if (file_exists($path) && !Factory::getSession()->get('bfFileUploadOverride', true)) {
            unlink($path);
        }

        if (!move_uploaded_file($filename, $path)) {
            $this->status = _FF_STATUS_UPLOAD_FAILED;
            $this->message = BFText::_('COM_BREEZINGFORMS_PROCESS_FILEMOVEFAILED');
            return '';
        } // if

        $filemode = NULL;
        if (isset($mosConfig_fileperms)) {
            if ($mosConfig_fileperms != '')
                $filemode = octdec($mosConfig_fileperms);
        } else
            $filemode = 0644;
        if (isset($filemode)) {
            if (!@chmod($path, $filemode)) {
                $this->status = _FF_STATUS_UPLOAD_FAILED;
                $this->message = BFText::_('COM_BREEZINGFORMS_PROCESS_FILECHMODFAILED');
                return '';
            } // if
        } // if

        $serverPath = $path;
        if ($useUrl) {

            $cleaned = str_replace(JPATH_SITE . '/', '', $baseDir);

            $path = Uri::root() . rtrim($cleaned, '/') . '/' . basename($path);
        }

        // resize if image
        // last param = crop or simple. Nothing for exact.
        if (intval($resize_target_height) > 0 && intval($resize_target_width) > 0) {
            $this->resizeFile($serverPath, intval($resize_target_width), intval($resize_target_height), $resize_bgcolor, $resize_type);
        }
        return array('default' => $path, 'server' => $serverPath);
    }

    public function exifImageType($filename)
    {
        // some hosting providers think it is a good idea not to compile in exif with php...
        if (!function_exists('exif_imagetype')) {
            if ((list($width, $height, $type, $attr) = getimagesize($filename)) !== false) {
                return $type;
            }
            return false;
        } else {
            return exif_imagetype($filename);
        }
    }

    public function resizeFile($path, $width, $height, $bgcolor = '#ffffff', $type = '')
    {
        $image = @getimagesize($path);

        if ($image !== false) {

            /*
              if($image[0] > 16384){
              return;
              }

              if($image[1] > 16384){
              return;
              } */

            $col_ = $bgcolor;
            if ($bgcolor !== null) {
                $col = array();
                $col[0] = intval(@hexdec(@substr($bgcolor, 1, 2)));
                $col[1] = intval(@hexdec(@substr($bgcolor, 3, 2)));
                $col[2] = intval(@hexdec(@substr($bgcolor, 5, 2)));
                $col_ = $col;
            }
            $exif_type = $this->exifImageType($path);
            // try to prevent memory issues
            $memory = true;

            $imageInfo = $image;

            $MB = 1048576;
            $K64 = 65536;
            $TWEAKFACTOR = 1.5;
            $channels = isset($image['channels']) ? $image['channels'] : 0;
            $memoryNeeded = round(
                ($image[0] * $image[1] * $image['bits'] * ($channels / 8) + $K64
                ) * $TWEAKFACTOR
            );

            $ini = 8 * $MB;
            if (ini_get('memory_limit') !== false) {
                $ini = $this->returnBytes(ini_get('memory_limit'));
            }
            $memoryLimit = $ini;
            if (
                function_exists('memory_get_usage') &&
                memory_get_usage() + $memoryNeeded > $memoryLimit
            ) {
                $memory = false;
            }
            if ($memory) {
                switch ($exif_type) {
                    case IMAGETYPE_JPEG2000:
                    case IMAGETYPE_JPEG:
                        $resource = @imagecreatefromjpeg($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ($type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagejpeg($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                File::write($path, $buffer);
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                    case IMAGETYPE_GIF:
                        $resource = @imagecreatefromgif($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ($type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagegif($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                File::write($path, $buffer);
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                    case IMAGETYPE_PNG:
                        $resource = @imagecreatefrompng($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ($type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagepng($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                File::write($path, $buffer);
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                }
            }
        }
    }

    public function resize_image($source_image, $destination_width, $destination_height, $type = 0, $bgcolor = array(0, 0, 0))
    {
        // $type (1=crop to fit, 2=letterbox)
        $source_width = imagesx($source_image);
        $source_height = imagesy($source_image);
        $source_ratio = $source_width / $source_height;
        if ($destination_height == 0 && $type == 3) {
            $destination_height = $source_height;
        }
        $destination_ratio = $destination_width / $destination_height;
        if ($type == 3) {

            $old_width = $source_width;
            $old_height = $source_height;

            // Target dimensions
            $max_width = $destination_width;
            $max_height = $destination_height;
            // Get current dimensions
            // Calculate the scaling we need to do to fit the image inside our frame
            $scale = min($max_width / $old_width, $max_height / $old_height);

            // Get the new dimensions
            $destination_width = ceil($scale * $old_width);
            $destination_height = ceil($scale * $old_height);

            $new_destination_width = $destination_width;
            $new_destination_height = $destination_height;

            $source_x = 0;
            $source_y = 0;
            $destination_x = 0;
            $destination_y = 0;
        } else if ($type == 1) {
            // crop to fit
            if ($source_ratio > $destination_ratio) {
                // source has a wider ratio
                $temp_width = (int) ($source_height * $destination_ratio);
                $temp_height = $source_height;
                $source_x = (int) (($source_width - $temp_width) / 2);
                $source_y = 0;
            } else {
                // source has a taller ratio
                $temp_width = $source_width;
                $temp_height = (int) ($source_width * $destination_ratio);
                $source_x = 0;
                $source_y = (int) (($source_height - $temp_height) / 2);
            }
            $destination_x = 0;
            $destination_y = 0;
            $source_width = $temp_width;
            $source_height = $temp_height;
            $new_destination_width = $destination_width;
            $new_destination_height = $destination_height;
        } else {
            // letterbox
            if ($source_ratio < $destination_ratio) {
                // source has a taller ratio
                $temp_width = (int) ($destination_height * $source_ratio);
                $temp_height = $destination_height;
                $destination_x = (int) (($destination_width - $temp_width) / 2);
                $destination_y = 0;
            } else {
                // source has a wider ratio
                $temp_width = $destination_width;
                $temp_height = (int) ($destination_width / $source_ratio);
                $destination_x = 0;
                $destination_y = (int) (($destination_height - $temp_height) / 2);
            }
            $source_x = 0;
            $source_y = 0;
            $new_destination_width = $temp_width;
            $new_destination_height = $temp_height;
        }
        $destination_image = imagecreatetruecolor($destination_width, $destination_height);
        if ($type == 2) {
            imagefill($destination_image, 0, 0, imagecolorallocate($destination_image, $bgcolor[0], $bgcolor[1], $bgcolor[2]));
        }
        imagecopyresampled($destination_image, $source_image, $destination_x, $destination_y, $source_x, $source_y, $new_destination_width, $new_destination_height, $source_width, $source_height);
        return $destination_image;
    }

    public function returnBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = str_replace($val[strlen($val) - 1], '', $val);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024 * 1048576;
                break;
            case 'm':
                $val *= 1048576;
                break;
            case 'k':
                $val *= 1024;
                break;
            default:
                $val = 8 * 1048576;
        }

        return $val;
    }

    public function findQuickModeElement(array $dataObject, $needle)
    {

        if ($dataObject['properties']['type'] == 'element' && isset($dataObject['properties']['bfName']) && $dataObject['properties']['bfName'] == $needle) {
            return $dataObject;
        }

        if (isset($dataObject['children']) && count($dataObject['children']) != 0) {

            $childrenAmount = count($dataObject['children']);

            for ($i = 0; $i < $childrenAmount; $i++) {
                $child = $this->findQuickModeElement($dataObject['children'][$i], $needle);
                if ($child !== null) {
                    return $child;
                }
            }
        }
        return null;
    }

    // saveUpload

    public function measureTime()
    {
        $a = explode(' ', microtime());
        return ((double) $a[0] + $a[1]) / 1000;
    }

    function collectSubmitdata($cbResult = null)
    {
        if ($this->dying || $this->submitdata)
            return;

        $this->submitdata = array();
        $this->savedata = array();
        $this->maildata = array();
        $this->sfdata = array();
        $this->xmldata = array();
        $names = array();
        if (count($this->rows)) {
            $time_passed = 0;
            $start_time = $this->measureTime();
            $max_exec_time = 15;
            if (function_exists('ini_get')) {
                $max_exec_time = @ini_get('max_execution_time');
            }
            $max_time = !empty($max_exec_time) ? intval($max_exec_time) / 2 : 15;
            foreach ($this->rows as $row) {
                if (!in_array($row->name, $names)) {
                    switch ($row->type) {
                        case 'File Upload':

                            // CONTENTBUILDER
                            if ($cbResult !== null && isset($cbResult['data']) && $cbResult['data'] != null) {
                                $rowdata1 = Path::clean(str_replace($this->findtags, $this->replacetags, $row->data1));
                                if ($cbResult['data']['protect_upload_directory']) {
                                    if (is_dir($rowdata1) && !file_exists($rowdata1 . '/' . '.htaccess'))
                                        File::write($rowdata1 . '/' . '.htaccess', $def = 'deny from all');
                                } else {
                                    if (is_dir($rowdata1) && file_exists($rowdata1 . '/' . '.htaccess'))
                                        File::delete($rowdata1 . '/' . '.htaccess');
                                }
                            }

                            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
                            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
                            $areas = Zend_Json::decode($this->formrow->template_areas);
                            $useUrl = false;
                            $useUrlDownloadDirectory = '';
                            $resize_target_width = 0;
                            $resize_target_height = 0;
                            $resize_type = '';
                            $resize_bgcolor = '#ffffff';
                            if (trim($this->formrow->template_code_processed) == 'QuickMode' && is_array($areas)) {
                                foreach ($areas as $area) { // don't worry, size is only 1 in QM
                                    if (isset($area['elements'])) {
                                        foreach ($area['elements'] as $element) {
                                            if (isset($element['options']) && isset($element['options']['useUrl']) && isset($element['name']) && trim($element['name']) == trim($row->name) && isset($element['internalType']) && $element['internalType'] == 'bfFile') {
                                                $useUrl = $element['options']['useUrl'];
                                                $useUrlDownloadDirectory = $element['options']['useUrlDownloadDirectory'];
                                                $resize_target_width = $element['options']['resize_target_width'];
                                                $resize_target_height = $element['options']['resize_target_height'];
                                                $resize_type = $element['options']['resize_type'];
                                                $resize_bgcolor = $element['options']['resize_bgcolor'];
                                                break;
                                            }
                                        }
                                    }
                                    break; // just in case
                                }
                            }

                            $uploadfiles = isset($_FILES['ff_nm_' . $row->name]) ? $_FILES['ff_nm_' . $row->name] : null;

                            if ($this->formrow->template_code != '' && isset($_FILES['ff_nm_' . $row->name]) && $_FILES['ff_nm_' . $row->name]['tmp_name'][0] != '' && trim($row->data2) != '') {
                                $fileName = $_FILES['ff_nm_' . $row->name]['name'][0];
                                $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
                                $allowedExtensions = explode(',', strtolower(str_replace(' ', '', trim($row->data2))));

                                if (!in_array($ext, $allowedExtensions)) {
                                    $this->status = _FF_STATUS_FILE_EXTENSION_NOT_ALLOWED;
                                    return;
                                }
                            }

                            $paths = array();
                            $serverPaths = array();
                            // CONTENTBUILDER
                            $is_relative = array();

                            if ($uploadfiles) {
                                $name = $uploadfiles['name'];
                                $tmp_name = $uploadfiles['tmp_name'];
                                $cnt = count($name);
                                for ($i = 0; $i < $cnt; $i++) {
                                    $path = '';
                                    if ($name[$i] != '') {
                                        $rowpath1 = $row->data1;
                                        //if ($cbResult !== null && isset($cbResult['data']) && $cbResult['data'] != null) {
                                        $rowpath1 = $this->cbCreatePathByTokens($rowpath1, $this->rows, $row->name);
                                        //}

                                        $pathInfo = $this->saveUpload($tmp_name[$i], bf_sanitizeFilename($name[$i]), $rowpath1, $row->flag1, $useUrl, $useUrlDownloadDirectory, $resize_target_width, $resize_target_height, $resize_type, $resize_bgcolor, $row->name);
                                        $path = $pathInfo['default'];
                                        $serverPath = $pathInfo['server'];
                                        if ($this->status != _FF_STATUS_OK)
                                            return;
                                        $paths[] = $path;

                                        $serverPaths[] = $serverPath;
                                        $this->submitdata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $path);
                                        // CONTENTBUILDER
                                        if (strpos(strtolower($row->data1), '{cbsite}') === 0) {
                                            $is_relative[$serverPath] = true;
                                        }
                                    } // if
                                } // for
                            } // if
                            if (BFRequest::getVar('bfFlashUploadTicket', '') != '') {
                                $tickets = Factory::getSession()->get('bfFlashUploadTickets', array());
                                mt_srand();
                                if (isset($tickets[BFRequest::getVar('bfFlashUploadTicket', mt_rand(0, mt_getrandmax()))])) {
                                    $sourcePath = JPATH_SITE . '/components/com_breezingforms/uploads/';
                                    if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath)) {

                                        $tz = 'UTC';
                                        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

                                        $date_stamp = date('Y_m_d_H_i_s');
                                        $date_ = Factory::getDate($this->submitted, $tz);
                                        $offset = $date_->getOffsetFromGMT();
                                        if ($offset > 0) {
                                            $date_->add(new DateInterval('PT' . $offset . 'S'));
                                        } else if ($offset < 0) {
                                            $offset = $offset * -1;
                                            $date_->sub(new DateInterval('PT' . $offset . 'S'));
                                        }
                                        $date_stamp = $date_->format('Y_m_d_H_i_s', true);

                                        $date_stamp2 = date('Y_m_d');
                                        $date_ = Factory::getDate($this->submitted, $tz);
                                        $offset = $date_->getOffsetFromGMT();
                                        if ($offset > 0) {
                                            $date_->add(new DateInterval('PT' . $offset . 'S'));
                                        } else if ($offset < 0) {
                                            $offset = $offset * -1;
                                            $date_->sub(new DateInterval('PT' . $offset . 'S'));
                                        }
                                        $date_stamp2 = $date_->format('Y_m_d', true);

                                        // trying glob instead of readdir()

                                        foreach (glob($sourcePath . '*') as $glob_file) {

                                            $file = basename($glob_file);

                                            if ($file != "." && $file != "..") {
                                                $parts = explode('_', $file);
                                                if (count($parts) >= 5) {
                                                    if ($parts[count($parts) - 1] == 'flashtmp') {

                                                        if ($parts[count($parts) - 3] == BFRequest::getVar('bfFlashUploadTicket', '')) {


                                                            if ($parts[count($parts) - 4] == $row->name) {

                                                                unset($parts[count($parts) - 1]);
                                                                unset($parts[count($parts) - 1]);
                                                                unset($parts[count($parts) - 1]);
                                                                unset($parts[count($parts) - 1]);
                                                                $userfile_name = implode('_', $parts);
                                                                $rowpath1 = $row->data1;
                                                                //if ($cbResult !== null && isset($cbResult['data']) && $cbResult['data'] != null) {
                                                                $rowpath1 = $this->cbCreatePathByTokens($rowpath1, $this->rows, $row->name);
                                                                //}
                                                                $baseDir = Path::clean(str_replace($this->findtags, $this->replacetags, $rowpath1));

                                                                // test if there is a filemask and remove it from the basepath
                                                                $_baseDir = $baseDir;

                                                                $fmtest = str_replace('{filemask:', '', basename($baseDir));
                                                                if ($fmtest != basename($baseDir)) {
                                                                    $baseDir = rtrim(rtrim(str_replace(basename($baseDir), '', $baseDir), '/'), "\\");
                                                                }

                                                                if ($fmtest != basename($_baseDir)) {
                                                                    $fm = basename($_baseDir);

                                                                    foreach ($this->rows as $row2) {

                                                                        $fname = BFRequest::getVar('ff_nm_' . $row2->name, array(), 'POST', 'HTML', BFREQUEST_ALLOWHTML);

                                                                        foreach ($fname as $_fname) {
                                                                            $fm = str_replace('{filemask:' . strtolower($row2->name) . '}', File::makeSafe(trim($_fname)), $fm);
                                                                            // so it works the same like for folders
                                                                            $fm = str_replace('{' . strtolower($row2->name) . ':value}', File::makeSafe(trim($_fname)), $fm);
                                                                        }
                                                                    }

                                                                    $fm = str_replace('{filemask:_separator}', '_', $fm);
                                                                    $fm = str_replace('{filemask:_username}', trim(Factory::getUser()->get('username')), $fm);
                                                                    $fm = str_replace('{filemask:_userid}', trim(Factory::getUser()->get('id')), $fm);
                                                                    $fm = str_replace('{filemask:_name}', trim(Factory::getUser()->get('name')), $fm);
                                                                    $fm = str_replace('{filemask:_datetime}', trim($date_stamp), $fm);
                                                                    $fm = str_replace('{filemask:_date}', trim($date_stamp2), $fm);
                                                                    $fm = str_replace('{filemask:_timestamp}', trim(time()), $fm);
                                                                    $fm = str_replace('{filemask:_random}', trim(mt_rand(0, mt_getrandmax())), $fm);
                                                                    $fm = str_replace('{filemask:_filename}', trim(basename($userfile_name, '.' . File::getExt($userfile_name))), $fm);
                                                                    if ($fm == '') {
                                                                        $fm = '__empty__';
                                                                    }
                                                                    $userfile_name = $fm . '.' . File::getExt($userfile_name);
                                                                }

                                                                //if ($row->flag1)
                                                                //	$userfile_name = $date_stamp . '_' . $userfile_name;
                                                                $path = $baseDir . DS . $userfile_name;
                                                                //if ($row->flag1) $path .= '.'.date('YmdHis');
                                                                if (file_exists($path) && Factory::getSession()->get('bfFileUploadOverride', true)) {
                                                                    $rnd = md5(mt_rand(0, mt_getrandmax()));
                                                                    $path = $baseDir . DS . $rnd . '_' . $userfile_name;
                                                                    //if ($row->flag1) $path .= '.'.date('YmdHis');
                                                                    if (file_exists($path)) {
                                                                        $this->status = _FF_STATUS_UPLOAD_FAILED;
                                                                        $this->message = BFText::_('COM_BREEZINGFORMS_PROCESS_FILEEXISTS');
                                                                        return '';
                                                                    }
                                                                } else if (file_exists($path) && !Factory::getSession()->get('bfFileUploadOverride', true)) {
                                                                    unlink($path);
                                                                }

                                                                $ext = strtolower(substr($userfile_name, strrpos($userfile_name, '.') + 1));
                                                                $allowedExtensions = explode(',', strtolower(str_replace(' ', '', trim($row->data2))));

                                                                if (!in_array($ext, $allowedExtensions)) {
                                                                    $this->status = _FF_STATUS_FILE_EXTENSION_NOT_ALLOWED;
                                                                }

                                                                if ($this->status != _FF_STATUS_OK)
                                                                    return;

                                                                if (@is_readable($sourcePath . $file) && @file_exists($baseDir) && @is_dir($baseDir)) {
                                                                    @File::copy($sourcePath . $file, $path);
                                                                } else {
                                                                    $this->status = _FF_STATUS_UPLOAD_FAILED;
                                                                    $this->message = BFText::_('COM_BREEZINGFORMS_PROCESS_FILEMOVEFAILED');
                                                                    return;
                                                                }
                                                                @File::delete($sourcePath . $file);

                                                                $serverPath = $path;

                                                                if ($useUrl) {

                                                                    $cleaned = str_replace(JPATH_SITE . '/', '', $baseDir);

                                                                    $path = Uri::root() . rtrim($cleaned, '/') . '/' . basename($path);
                                                                }

                                                                $paths[] = $path;
                                                                $serverPaths[] = $serverPath;
                                                                $this->submitdata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $path);

                                                                // resize if image
                                                                // last param = crop or simple. Nothing for exact.
                                                                if (intval($resize_target_height) > 0 && intval($resize_target_width) > 0) {
                                                                    $this->resizeFile($serverPath, intval($resize_target_width), intval($resize_target_height), $resize_bgcolor, $resize_type);
                                                                }

                                                                // CONTENTBUILDER
                                                                if (strpos(strtolower($row->data1), '{cbsite}') === 0) {
                                                                    $is_relative[$serverPath] = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if (!count($paths))
                                $paths = array();
                            if ($row->logging == 1) {
                                // db and attachment
                                // DROPBOX SUPPORT request v2 API

                                require_once JPATH_SITE . '/administrator/components/com_breezingforms/libraries/dropbox/v2/autoload.php';

                                foreach ($serverPaths as $serverPath) {

                                    // DROPBOX File Upload
                                    if ($this->formrow->dropbox_email) {

                                        Alorel\Dropbox\Operation\AbstractOperation::setDefaultToken($this->formrow->dropbox_email);
                                        $upload = new Alorel\Dropbox\Operation\Files\Upload();
                                        $file_to_upload = fopen($serverPath, "rb");
                                        $upload->raw(
                                            '/' . ($this->formrow->dropbox_folder != '' ? $this->formrow->dropbox_folder : $this->formrow->name) . '/' . basename($serverPath),
                                            $file_to_upload
                                        );
                                        try {
                                            fclose($file_to_upload);
                                        } catch (Error $e) {
                                        }
                                    }

                                    // CONTENTBUILDER: to keep the relative path with prefix
                                    $savedata_path = $serverPath;
                                    foreach ($this->findtags as $tag) {
                                        if (strtolower($tag) == '{cbsite}' && isset($is_relative[$serverPath]) && $is_relative[$serverPath]) {
                                            $savedata_path = Path::clean(str_replace(array(JPATH_SITE, JPATH_SITE), array('{cbsite}', '{CBSite}'), $savedata_path));
                                        }
                                    }

                                    if (
                                        ($this->formrow->dblog == 1 && $savedata_path != '') ||
                                        $this->formrow->dblog == 2 || ($cbResult != null && $cbResult['record'] != null)
                                    )
                                        $this->savedata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $savedata_path);
                                }

                                foreach ($paths as $path) {
                                    if (
                                        (($this->formrow->emaillog == 1 && $this->trim($path)) ||
                                            $this->formrow->emaillog == 2) && ($this->formrow->emailxml == 1 ||
                                            $this->formrow->emailxml == 2 || $this->formrow->emailxml == 3 || $this->formrow->emailxml == 4)
                                    )
                                        $this->xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $path);
                                    if (
                                        (($this->formrow->emaillog == 1 && $this->trim($path)) ||
                                            $this->formrow->mb_emaillog == 2) && ($this->formrow->mb_emailxml == 1 ||
                                            $this->formrow->mb_emailxml == 2 || $this->formrow->mb_emailxml == 3 || $this->formrow->mb_emailxml == 4)
                                    )
                                        $this->mb_xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $path);
                                } // foreach

                                if (!count($paths)) {
                                    if (
                                        ($this->formrow->dblog == 1) ||
                                        $this->formrow->dblog == 2
                                    )
                                        $this->savedata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, '');
                                    if (
                                        $this->formrow->emaillog == 2 && ($this->formrow->emailxml == 1 ||
                                            $this->formrow->emailxml == 2 || $this->formrow->emailxml == 3 || $this->formrow->emailxml == 4)
                                    )
                                        $this->xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, '');
                                    if (
                                        $this->formrow->mb_emaillog == 2 && ($this->formrow->mb_emailxml == 1 ||
                                            $this->formrow->mb_emailxml == 2 || $this->formrow->mb_emailxml == 3 || $this->formrow->mb_emailxml == 4)
                                    )
                                        $this->mb_xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, '');
                                }
                                // mail
                                $paths = implode(nl(), $paths);
                                $serverPaths = implode(nl(), $serverPaths);

                                if ($this->trim($paths)) {
                                    $this->sfadata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $paths, $serverPaths);
                                }

                                if (
                                    ($this->formrow->emaillog == 1 && $this->trim($paths)) ||
                                    $this->formrow->emaillog == 2
                                ) {
                                    $this->maildata[] = array(
                                        $row->id,
                                        $row->name,
                                        strip_tags($row->title),
                                        $row->type,
                                        $paths,
                                        $serverPaths
                                    );
                                }
                            } // if
                            break;
                        case 'Text':
                        case 'Textarea':
                        case 'Checkbox':
                        case 'Radio Button':
                        case 'Select List':
                        case 'Query List':
                        case 'Radio Group':
                        case 'Checkbox Group':
                        case 'Number Input':
                        case 'Calendar':
                        case 'Hidden Input':
                        case 'Signature':
                            if ($row->logging == 1) {

                                $values = @BFRequest::getVar("ff_nm_" . $row->name, array(''), 'POST', 'HTML', BFREQUEST_ALLOWHTML);

                                if ($row->type == 'Textarea') {
                                    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
                                    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
                                    require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/functions/helpers.php');

                                    if (trim($this->formrow->template_code_processed) == 'QuickMode') {
                                        $dataObject = Zend_Json::decode(bf_b64dec($this->formrow->template_code));
                                        $qmelement = $this->findQuickModeElement($dataObject, $row->name);

                                        if ($qmelement !== null && isset($qmelement['properties']['is_html']) && $qmelement['properties']['is_html']) {
                                            $values = BFRequest::getVar("ff_nm_" . $row->name, array(''), 'POST', 'HTML', BFREQUEST_ALLOWHTML);

                                            $html_value_cnt = count($values);

                                            for ($html_i = 0; $html_i < $html_value_cnt; $html_i++) {

                                                //$values[$html_i] = $this->removeDangerousHtml($values[$html_i]);

                                                /*
                                                  $input = Factory::getApplication()->input;
                                                  $input->set('cbCleanVar', $values[$html_i]);
                                                  $values[$html_i] = $input->getHtml('cbCleanVar'); */

                                                BFRequest::setVar('bfCleanVar', $values[$html_i], 'POST');
                                                $values[$html_i] = BFRequest::getVar('bfCleanVar', '', 'POST', 'HTML', BFREQUEST_ALLOWHTML);
                                            }
                                        }
                                    }
                                }

                                $sigValues = '';

                                foreach ($values as $value) {

                                    if ($row->type == 'Signature' && $value != '') {
                                        if (!is_dir(JPATH_SITE . '/media/breezingforms/signatures/')) {
                                            Folder::create(JPATH_SITE . '/media/breezingforms/signatures/');
                                            $def = '';
                                            File::write(JPATH_SITE . '/media/breezingforms/signatures/index.html', $def);
                                        }
                                        $sig_decoded = bf_b64dec($value);
                                        $sig_file = JPATH_SITE . '/media/breezingforms/signatures/' . $row->name . '-' . md5($value) . '.png';
                                        File::write($sig_file, $sig_decoded);
                                        $value = basename($sig_file);

                                        $sigValues .= $value;

                                        // DROPBOX SUPPORT request v2 API
                                        if ($this->formrow->dropbox_email) {

                                            require_once JPATH_SITE . '/administrator/components/com_breezingforms/libraries/dropbox/v2/autoload.php';
                                        }

                                        // DROPBOX Signature upload
                                        if ($this->formrow->dropbox_email) {

                                            Alorel\Dropbox\Operation\AbstractOperation::setDefaultToken($this->formrow->dropbox_email);
                                            $upload = new Alorel\Dropbox\Operation\Files\Upload();
                                            $file_to_upload = fopen($sig_file, "rb");
                                            $upload->raw(
                                                '/' . ($this->formrow->dropbox_folder != '' ? $this->formrow->dropbox_folder : $this->formrow->name) . '/' . basename($sig_file),
                                                $file_to_upload
                                            );
                                            try {
                                                fclose($file_to_upload);
                                            } catch (Error $e) {
                                            }
                                        }
                                    }

                                    // for db
                                    if (
                                        ($this->formrow->dblog == 1 && $value != '') ||
                                        $this->formrow->dblog == 2 || ($cbResult != null && $cbResult['record'] != null)
                                    )
                                        $this->savedata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $value);

                                    // CONTENTBUILDER
                                    $loadData = true;
                                    switch ($row->type) {
                                        case 'Checkbox':
                                        case 'Checkbox Group':
                                        case 'Radio Button':
                                        case 'Radio Group':
                                        case 'Select List':
                                            if ($value == 'cbGroupMark') {
                                                $loadData = false;
                                            }
                                            break;
                                    }

                                    if ($loadData) {
                                        // submitdata
                                        if ($this->trim($value) != '')
                                            $this->submitdata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $value);

                                        if (
                                            ($this->formrow->emaillog == 1 && $this->trim($value) != '') ||
                                            $this->formrow->emaillog == 2 && (($this->formrow->emailxml == 1 ||
                                                $this->formrow->emailxml == 2 || $this->formrow->emailxml == 3 || $this->formrow->emailxml == 4))
                                        )
                                            $this->xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $value);
                                        if (
                                            ($this->formrow->mb_emaillog == 1 && $this->trim($value) != '') ||
                                            $this->formrow->mb_emaillog == 2 && (($this->formrow->mb_emailxml == 1 ||
                                                $this->formrow->mb_emailxml == 2 || $this->formrow->mb_emailxml == 3 || $this->formrow->mb_emailxml == 4))
                                        )
                                            $this->mb_xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $value);
                                    }
                                } // foreach
                                // for mail

                                $sfvalues = $values;

                                if ($row->type == 'Signature') {

                                    $values = $sigValues;
                                    $sfvalues = $sigValues;
                                } else if ($row->type == 'Textarea') {

                                    $values = implode(nl(), $values);
                                    $sfvalues = implode(nl(), $sfvalues);
                                } else {

                                    // CONTENTBUILDER
                                    $useNewValues = false;
                                    $newValues = array();
                                    $sfnewValues = array();

                                    foreach ($values as $value) {
                                        switch ($row->type) {
                                            case 'Checkbox':
                                            case 'Checkbox Group':
                                            case 'Radio Button':
                                            case 'Radio Group':
                                            case 'Select List':
                                                if ($value != 'cbGroupMark') {
                                                    $newValues[] = $value;
                                                    $sfnewValues[] = $value;
                                                } else {
                                                    $useNewValues = true;
                                                }
                                                break;
                                        }
                                    }

                                    if ($useNewValues) {
                                        $values = is_array($newValues) ? implode(', ', $newValues) : '';
                                        $sfvalues = is_array($sfnewValues) ? implode(';', $sfnewValues) : '';
                                    } else {
                                        $values = is_array($values) ? implode(', ', $values) : '';
                                        $sfvalues = is_array($sfvalues) ? implode(';', $sfvalues) : '';
                                    }
                                }

                                if ($this->trim($sfvalues)) {
                                    $this->sfdata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $sfvalues);
                                }

                                if (
                                    ($this->formrow->emaillog == 1 && $this->trim($values)) ||
                                    $this->formrow->emaillog == 2
                                ) {
                                    $this->maildata[] = array(
                                        $row->id,
                                        $row->name,
                                        strip_tags($row->title),
                                        $row->type,
                                        $values
                                    );
                                }
                            } // if logging
                            break;
                        default:
                            ;
                    } // switch
                    $names[] = $row->name;
                } // if
                $time_passed = $this->measureTime();
                if (($time_passed - $start_time) > $max_time) {
                    //break;
                }
            } // for
        }
    }

    // collectSubmitdata

    function submit()
    {
        global $database, $ff_config, $ff_comsite, $ff_mossite, $ff_otherparams;

        // CONTENTBUILDER BEGIN
        $cbRecordId = 0;
        $cbEmailNotifications = false;
        $cbEmailUpdateNotifications = false;
        $cbResult = $this->cbCheckPermissions();
        if ($cbResult['data'] !== null && $cbResult['data']['email_notifications']) {
            if (!BFRequest::getInt('cb_record_id', 0)) {
                $cbEmailNotifications = true;
            } else {
                $cbEmailNotifications = false;
            }
        }
        if ($cbResult['data'] !== null && $cbResult['data']['email_update_notifications']) {
            if (BFRequest::getInt('cb_record_id', 0)) {
                $cbEmailUpdateNotifications = true;
            } else {
                $cbEmailUpdateNotifications = false;
            }
        }
        if ($cbResult['data'] === null) {
            $cbEmailNotifications = true;
            $cbEmailUpdateNotifications = true;
        }
        // CONTENTBUILDER END

        $database = Factory::getContainer()->get(DatabaseInterface::class);
        if (!$this->okrun)
            return;

        // currently only available in classic mode
        if (trim($this->formrow->template_code_processed) == '') {
            set_error_handler('_ff_errorHandler');
        }

        ob_start();
        $this->record_id = '';
        $this->status = _FF_STATUS_OK;
        $this->message = '';
        $this->sendNotificationAfterPayment = false;

        // handle Begin Submit piece
        $halt = false;
        $this->collectSubmitdata($cbResult);

        if (!$halt) {

            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/functions/helpers.php');

            $dataObject = Zend_Json::decode(bf_b64dec($this->formrow->template_code));
            $rootMdata = $dataObject['properties'];

            if (BFRequest::getVar('ff_applic', '') != 'mod_facileforms' && BFRequest::getInt('ff_frame', 0) != 1 && bf_is_mobile()) {
                $is_device = true;
                $this->isMobile = isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && $rootMdata['forceMobile'] ? true : (isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && Factory::getSession()->get('com_breezingforms.mobile', false) ? true : false);
            } else
                $this->isMobile = false;



            for ($i = 0; $i < $this->rowcount; $i++) {
                $row = $this->rows[$i];
                if ($row->type == "Captcha") {
                    require_once(JPATH_SITE . '/components/com_breezingforms/images/captcha/securimage.php');
                    $securimage = new Securimage();
                    if (!$securimage->check(BFRequest::getVar('bfCaptchaEntry', ''))) {
                        $halt = true;
                        $this->status = _FF_STATUS_CAPTCHA_FAILED;
                        exit;
                    }
                    break;
                } else
                    if ($row->type == "ReCaptcha") {

                        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
                        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

                        $areas = Zend_Json::decode($this->formrow->template_areas);

                        foreach ($areas as $area) {
                            foreach ($area['elements'] as $element) {
                                if ($element['bfType'] == 'ReCaptcha') {

                                    if (!class_exists('ReCaptcha')) {

                                        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/recaptcha/newrecaptchalib.php');
                                    }

                                    $reCaptcha = new ReCaptcha($element['privkey']);

                                    $resp = @$reCaptcha->verifyResponse(
                                        $_SERVER["REMOTE_ADDR"],
                                        BFRequest::getVar('g-recaptcha-response', '')
                                    );

                                    if ($resp != null && $resp->success) {

                                        // all good
                                    } else {

                                        $halt = true;
                                        $this->status = _FF_STATUS_CAPTCHA_FAILED;
                                        exit;
                                    }

                                    break;
                                }
                            }
                        }

                        break;
                    }
            }

            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

            $areas = Zend_Json::decode($this->formrow->template_areas);

            if (is_array($areas)) {
                switch (BFRequest::getVar('ff_payment_method', '')) {
                    case 'Stripe':
                    case 'PayPal':
                    case 'Sofortueberweisung':
                        foreach ($areas as $area) {
                            foreach ($area['elements'] as $element) {
                                if ($element['internalType'] == 'bfStripe' || $element['internalType'] == 'bfPayPal' || $element['internalType'] == 'bfSofortueberweisung') {
                                    $options = $element['options'];
                                    if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                                        $this->sendNotificationAfterPayment = true;
                                    }
                                }
                            }
                        }
                }
            }
        }

        if (!$halt) {

            $code = '';

            switch ($this->formrow->piece3cond) {
                case 1: // library
                    $database->setQuery(
                        "select name, code from #__facileforms_pieces " .
                        "where id=" . $this->formrow->piece3id . " and published=1 "
                    );
                    $rows = $database->loadObjectList();
                    if (count($rows))
                        echo $this->execPiece(
                            $rows[0]->code,
                            BFText::_('COM_BREEZINGFORMS_PROCESS_BSPIECE') . " " . $rows[0]->name,
                            'p',
                            $this->formrow->piece3id,
                            null
                        );
                    break;
                case 2: // custom code
                    echo $this->execPiece(
                        $this->formrow->piece3code,
                        BFText::_('COM_BREEZINGFORMS_PROCESS_BSPIECEC'),
                        'f',
                        $this->form,
                        3
                    );
                    break;
                default:
                    break;
            } // switch
            if ($this->bury())
                return;

            if ($this->status == _FF_STATUS_OK) {
                if (!$this->formrow->published) {
                    $this->status = _FF_STATUS_UNPUBLISHED;
                } else {
                    if ($this->status == _FF_STATUS_OK) {
                        if ($this->formrow->dblog > 0)
                            $cbRecordId = $this->logToDatabase($cbResult);

                        if ($this->status == _FF_STATUS_OK) {
                            if ($this->formrow->emailntf > 0 && ($cbEmailNotifications || $cbEmailUpdateNotifications)) { // CONTENTBUILDER
                                $this->sendEmailNotification();
                            }
                            if ($this->formrow->mb_emailntf > 0 && ($cbEmailNotifications || $cbEmailUpdateNotifications)) { // CONTENTBUILDER
                                $this->sendMailbackNotification();
                            }

                            // DROPBOX request v2 API and PDF,CSV, XML upload
                            if ($this->formrow->dropbox_submission_enabled) {
                                if ($this->formrow->dropbox_email) {
                                    try {
                                        require_once JPATH_SITE . '/administrator/components/com_breezingforms/libraries/dropbox/v2/autoload.php';
                                        // $space = "Dropbox\\Client";
                                        // $dbxClient = new $space($this->formrow->dropbox_email, "BreezingForms/1.8.5");
                                        Alorel\Dropbox\Operation\AbstractOperation::setDefaultToken($this->formrow->dropbox_email);

                                        $dropbox_types = explode(',', $this->formrow->dropbox_submission_types);
                                        foreach ($dropbox_types as $dropbox_type) {
                                            $dropbox_file = '';
                                            switch ($dropbox_type) {
                                                case 'pdf':
                                                    $dropbox_file = $this->exppdf();
                                                    break;
                                                case 'csv':
                                                    $dropbox_file = $this->expcsv();
                                                    break;
                                                case 'xml':
                                                    $dropbox_file = $this->expxml();
                                                    break;
                                            }
                                            if ($dropbox_file != '') {
                                                try {

                                                    $upload = new Alorel\Dropbox\Operation\Files\Upload();
                                                    $file_to_upload = fopen($dropbox_file, "rb");
                                                    $upload->raw(
                                                        '/' . ($this->formrow->dropbox_folder != '' ? $this->formrow->dropbox_folder : $this->formrow->name) . '/' . basename($dropbox_file),
                                                        $file_to_upload
                                                    );
                                                    try {
                                                        fclose($file_to_upload);
                                                    } catch (Error $e) {
                                                    }
                                                } catch (Exception $e) {

                                                }
                                            }
                                        }
                                    } catch (Exception $e) {

                                    }
                                }
                            }


                            $this->sendMailChimpNotification();
                            $this->sendSalesforceNotification();

                            PluginHelper::importPlugin('breezingforms_addons');
                            $dispatcher = Factory::getApplication()->getDispatcher();
                            $dispatcher->dispatch('onPropertiesExecute', new Joomla\Event\Event('onPropertiesExecute',
                                array(
                                    $this
                                )
                            ));

                            $tickets = Factory::getSession()->get('bfFlashUploadTickets', array());
                            mt_srand();
                            if (isset($tickets[BFRequest::getVar('bfFlashUploadTicket', mt_rand(0, mt_getrandmax()))])) {
                                unset($tickets[BFRequest::getVar('bfFlashUploadTicket')]);
                                Factory::getSession()->set('bfFlashUploadTickets', $tickets);
                            }
                        }
                    } // if
                } // if
            } // if
            // handle End Submit piece
            // DOUBLE OPT-IN

            if ($this->formrow->double_opt) {


                $uri = Uri::getInstance();
                $domainAddress = $uri->toString(array('scheme', 'host', 'port', 'path'));

                $mailer = Factory::getMailer();
                $config = Factory::getConfig();

                $recipient = '';
                $email_field_name = $this->formrow->opt_mail;

                // getting the email address from the form based on the setting in admin
                foreach ($this->submitdata as $data) {

                    if ($data[_FF_DATA_NAME] == $email_field_name) {
                        $recipient = $data[_FF_DATA_VALUE];
                        break;
                    }
                }

                if (bf_is_email($recipient)) {

                    $database->setQuery("Select s.record From #__facileforms_subrecords As s, #__facileforms_records As r Where r.form = " . $database->quote($this->form) . " And r.id = s.record And s.`name` = " . $database->quote($email_field_name) . " And s.`value` = " . $database->quote($recipient) . " And r.`opted` = 1");
                    $exists = $database->loadResult();

                    if (!$exists) {

                        $mailer->addRecipient($recipient);
                        $sender = array(
                            $config->get('mailfrom'),
                            $config->get('fromname')
                        );

                        $lastID = $this->record_id;
                        $token = $this->random_str(20);
                        $database = Factory::getContainer()->get(DatabaseInterface::class);
                        $database->setQuery("UPDATE #__facileforms_records SET opt_token=" . $database->quote(bf_b64enc($token)) . " WHERE id=" . $database->quote($lastID));
                        $database->execute();

                        $opt_in_link = $domainAddress . '?option=com_breezingforms&opt_in=true&id=' . $lastID . '&' . 'token=' . bf_b64enc($token);
                        $opt_out_link = $domainAddress . '?option=com_breezingforms&opt_out=true&id=' . $lastID . '&' . 'token=' . bf_b64enc($token);

                        $message = Text::_('COM_BREEZINGFORMS_FORMS_DOUBLE_OPT_EMAIL_TEXT');
                        $message .= '<a href="' . $opt_in_link . '">' . Text::_('COM_BREEZINGFORMS_FORMS_DOUBLE_OPT_VERIFY_HERE') . '</a>';
                        $message .= Text::_('COM_BREEZINGFORMS_FORMS_DOUBLE_OPT_OUT_EMAIL_TEXT');
                        $message .= '<a href="' . $opt_out_link . '">' . Text::_('COM_BREEZINGFORMS_FORMS_DOUBLE_OPT_UNVERIFY_HERE') . '</a>';
                        $message .= Text::_('COM_BREEZINGFORMS_FORMS_DOUBLE_OPT_EMAIL_TEXT_FOOTER');

                        $body = $message;
                        $mailer->isHtml(true);
                        $mailer->setSubject(Text::_('COM_BREEZINGFORMS_FORMS_DOUBLE_OPT_EMAIL_SUBJECT'));
                        $mailer->setBody($body);
                        $mailer->setSender($sender);
                        $mailer->Send();
                    }
                }
            }

            // DOUBLE OPT-INT END

            Factory::getContainer()->get(DatabaseInterface::class)->setQuery("SELECT MAX(id) FROM #__facileforms_records");
            $lastid = Factory::getContainer()->get(DatabaseInterface::class)->loadResult();
            $_SESSION['virtuemart_bf_id'] = $lastid;
            $session = Factory::getSession();
            $session->set('virtuemart_bf_id', $lastid);

            $code = '';
            switch ($this->formrow->piece4cond) {
                case 1: // library
                    $database->setQuery(
                        "select name, code from #__facileforms_pieces " .
                        "where id=" . $this->formrow->piece4id . " and published=1 "
                    );
                    $rows = $database->loadObjectList();
                    if (count($rows))
                        echo $this->execPiece(
                            $rows[0]->code,
                            BFText::_('COM_BREEZINGFORMS_PROCESS_ESPIECE') . " " . $rows[0]->name,
                            'p',
                            $this->formrow->piece4id,
                            null
                        );
                    break;
                case 2: // custom code
                    echo $this->execPiece(
                        $this->formrow->piece4code,
                        BFText::_('COM_BREEZINGFORMS_PROCESS_ESPIECEC'),
                        'f',
                        $this->form,
                        3
                    );
                    break;
                default:
                    break;
            } // switch

            if ($this->bury())
                return;
        }

        switch ($this->status) {
            case _FF_STATUS_OK:
                $message = BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITSUCCESS');
                break;
            case _FF_STATUS_UNPUBLISHED:
                $message = BFText::_('COM_BREEZINGFORMS_PROCESS_UNPUBLISHED');
                break;
            case _FF_STATUS_SAVERECORD_FAILED:
                $message = BFText::_('COM_BREEZINGFORMS_PROCESS_SAVERECFAILED');
                break;
            case _FF_STATUS_SAVESUBRECORD_FAILED:
                $message = BFText::_('COM_BREEZINGFORMS_PROCESS_SAVESUBFAILED');
                break;
            case _FF_STATUS_UPLOAD_FAILED:
                $message = BFText::_('COM_BREEZINGFORMS_PROCESS_UPLOADFAILED');
                break;
            case _FF_STATUS_SENDMAIL_FAILED:
                $message = BFText::_('COM_BREEZINGFORMS_PROCESS_SENDMAILFAILED');
                break;
            case _FF_STATUS_ATTACHMENT_FAILED:
                $message = BFText::_('COM_BREEZINGFORMS_PROCESS_ATTACHMTFAILED');
                break;
            case _FF_STATUS_CAPTCHA_FAILED:
                $message = BFText::_('COM_BREEZINGFORMS_CAPTCHA_ENTRY_FAILED');
                break;
            case _FF_STATUS_FILE_EXTENSION_NOT_ALLOWED:
                $message = BFText::_('COM_BREEZINGFORMS_FILE_EXTENSION_NOT_ALLOWED');
                break;
            default:
                $message = '';
                // custom piece status and message
                break;
        } // switch
        // built in PayPal action
        $paymentAction = false;

        if ($this->formrow->template_code != '') {

            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

            $areas = Zend_Json::decode($this->formrow->template_areas);
            $head = Zend_Json::decode(bf_b64dec($this->formrow->template_code));

            if (is_array($areas)) {
                $j15 = false;


                $paymentAction = true;

                switch (BFRequest::getVar('ff_payment_method', '')) {


                    case 'Stripe':

                        foreach ($area['elements'] as $element) {

                            if ($element['internalType'] == 'bfStripe') {

                                $options = $element['options'];

                                $ppselect = BFRequest::getVar('ff_nm_bfPaymentSelect', array());
                                if (count($ppselect) != 0) {
                                    $ppselected = explode('|', $ppselect[0]);
                                    if (count($ppselected) == 4) {
                                        // XDA - BUG : useless with normal itemname
                                        // Replace item name for Stripe button field with TEXT1 TEXT2 values from bfPaymentSelect text build as TEXT1|TEXT2|MNT1|MNT2
                                        // $options['itemname'] = $ppselected[0] . ' ' . $ppselected[1];
                                        $options['amount'] = floatval($ppselected[2]) + floatval($ppselected[3]);
                                    }
                                }

                                $options['amount'] = round(floatval($options['amount']), 2) * 100;

                                Factory::getSession()->set('bf_stripe_last_payment_amount' . $this->record_id, $options['amount']);

                                $html = '';

                                if (!$this->inline)
                                    /*$html .= '<html><head><style> .stripe_checkout_app { height: 580px !important; } </style>
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style type="text/css">
.thebutton {
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
-ms-box-sizing: border-box;
-o-box-sizing: border-box;
box-sizing: border-box;
padding: 0;
margin: 0;
width: 120px;
height: 37px;
border: 0;
text-decoration: none;
background: #45b1e8;
cursor: pointer;
background-image: -webkit-linear-gradient(#45b1e8,#3097de);
background-image: -moz-linear-gradient(#45b1e8,#3097de);
background-image: -ms-linear-gradient(#45b1e8,#3097de);
background-image: -o-linear-gradient(#45b1e8,#3097de);
background-image: -webkit-linear-gradient(#45b1e8,#3097de);
background-image: -moz-linear-gradient(#45b1e8,#3097de);
background-image: -ms-linear-gradient(#45b1e8,#3097de);
background-image: -o-linear-gradient(#45b1e8,#3097de);
background-image: linear-gradient(#45b1e8,#3097de);
-webkit-border-radius: 4px;
-moz-border-radius: 4px;
-ms-border-radius: 4px;
-o-border-radius: 4px;
border-radius: 4px;
-webkit-font-smoothing: antialiased;
-webkit-touch-callout: none;
-webkit-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
-o-user-select: none;
user-select: none;
cursor: pointer;
font-family: "Helvetica Neue","Helvetica",Arial,sans-serif;
font-weight: bold;
font-size: 17px;
color: #fff;
text-shadow: 0 -1px 0 rgba(46,86,153,0.3);
-webkit-box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
-moz-box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
-ms-box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
-o-box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
-webkit-transition: box-shadow .15s linear;
-moz-transition: box-shadow .15s linear;
-ms-transition: box-shadow .15s linear;
-o-transition: box-shadow .15s linear;
transition: box-shadow .15s linear;
}
</style></head><body>';*/
                                    require_once JPATH_SITE . '/administrator/components/com_breezingforms/libraries/stripe/vendor/autoload.php';
                                \Stripe\Stripe::setApiKey($options['secretKey']);
                                $stripeemail = strtolower(BFRequest::getVar('ff_nm_' . $options['emailfield'], '')[0]);
                                //header('Content-Type: application/json');
                                $returnurl = Uri::root() . "index.php?option=com_breezingforms&confirmStripe=true&form_id=" . $this->form . "&record_id=" . $this->record_id;
                                if (isset($options['emailfield']) && $options['emailfield'] !== '') {
                                    $stripeemail = strtolower(BFRequest::getVar('ff_nm_' . $options['emailfield'], '')[0]);
                                    Factory::getSession()->set('emailfield', $stripeemail);
                                }

                                // XDA BEGIN
// preg_match('#\((.*?)\)#', $_POST['ff_nm_donationAmount'][0], $match);
// $productName = $match[1];
                                $productName = $options['itemname'];
                                // XDA END

                                // XDA $pricearr = explode(" ", $$_POST['ff_nm_donationAmount'][0], 2);
// XDA $productPrice = $pricearr[0];

                                // ---------------------------------------------------------------------------------------------------------------------------------------------
// XDA : in the Stripe Checkout session, 2 changes :
// 1 - To disable address collection we will either need to pass billing_address_collection parameter with value auto or send API request without it.
//      billing_address_collection' => 'required' -> 'auto'
// 2 - Email determination
// 'customer_email' => $_POST['ff_nm_email'][0] -> 'customer_email' => $stripeemail
// ---------------------------------------------------------------------------------------------------------------------------------------------


                                $checkout_session = \Stripe\Checkout\Session::create([
                                    'customer_email' => $stripeemail,
                                    'billing_address_collection' => 'auto',
                                    /*
                                    'line_items' => [[
                                      'price' => 'price_1JYA3UDkYxK6vMJ2QF2S6fNh',
                                      'quantity' => 1,
                                      //'description' => var_dump($stripeemail);
                                    ]],*/
                                    'line_items' => [
                                        [
                                            'price_data' => [
                                                'currency' => strtolower($options['currencyCode']),
                                                'product_data' => [
                                                    'name' => "$productName",
                                                ],
                                                'unit_amount' => $options['amount'],
                                            ],
                                            'quantity' => 1,
                                        ]
                                    ],
                                    'payment_method_types' => [
                                        'card',
                                    ],
                                    'mode' => 'payment',
                                    'success_url' => $returnurl . "&session_id={CHECKOUT_SESSION_ID}",
                                    'cancel_url' => $returnurl . "&session_id={CHECKOUT_SESSION_ID}",
                                ]);

                                //$html .=  $_POST['ff_nm_email'][0];
                                $html .= header("HTTP/1.1 303 See Other");
                                $html .= header("Location: " . $checkout_session->url);

                                $current_tag = Factory::getApplication()->getLanguage()->getTag();
                                $exploded = explode('-', $current_tag);

                                $locale = 'auto';

                                if (in_array(strtolower($exploded[0]), array('zh', 'nl', 'en', 'fr', 'de', 'it', 'ja', 'es'))) {

                                    $locale = strtolower($exploded[0]);
                                }

                                $returnurl = Uri::root() . "index.php?option=com_breezingforms&confirmStripe=true&form_id=" . $this->form . "&record_id=" . $this->record_id;
                                if (isset($options['emailfield']) && $options['emailfield'] !== '') {
                                    $stripeemail = strtolower(BFRequest::getVar('ff_nm_' . $options['emailfield'], '')[0]);
                                    Factory::getSession()->set('emailfield', $stripeemail);
                                }

                                $html .= '
                        
                        <script src="https://checkout.stripe.com/checkout.js"></script>
        
        <script>
        var submitted_form = false;
        
        var handler = StripeCheckout.configure({
            key: ' . json_encode($options['publishableKey']) . ',
            image: ' . json_encode(Uri::root() . 'components/com_breezingforms/images/icon_card.png') . ',
            locale: ' . json_encode($locale) . ',
            token: function(token) {
            submitted_form = true;
            location.href = ' . json_encode($returnurl) . '+"&token="+token.id
            }
                                                                });
                            
                            var options = {
                                        name: ' . json_encode(isset($head['properties']['title_translation' . Factory::getApplication()->getLanguage()->getTag()]) ? $head['properties']['title_translation' . Factory::getApplication()->getLanguage()->getTag()] : $this->formrow->title) . ',
                                        description: ' . json_encode($options['itemname']) . ',
                                        currency: ' . json_encode(strtolower($options['currencyCode'])) . ',
                                        amount: ' . json_encode($options['amount']) . ',
                                        email: ' . (isset($stripeemail) ? json_encode($stripeemail) : json_encode('')) . ',
									    zipCode : true,
									    billingAddress: true,
									    closed: function () { 
									        if( !submitted_form ){
									        
									            location.href = ' . json_encode(Uri::root()) . '; 
                                                                                }
                                                                            },
                                                                            opened: function(){
                                                                                document.querySelector(".thebutton").style.display = "none";
                                                                            }
                                                                  };
                                                                
								// Close Checkout on page navigation:
								window.addEventListener(\'popstate\', function() {
								  handler.close();
								});
								
								window.onload = function(){
								  handler.open(options);
								};
								</script>
			                	
			                	';

                                if (!$this->inline)
                                    $html .= "</form><div style='margin-top: 25%; text-align: center; width: 100%;'><button class='thebutton' onclick='handler.open(options);'>Click To Pay</button></body></div></html>";

                                echo $html;
                            }
                        }

                        break;

                    case 'PayPal':

                        foreach ($areas as $area) {

                            foreach ($area['elements'] as $element) {

                                if ($element['internalType'] == 'bfPayPal') {

                                    $options = $element['options'];

                                    $business = $options['business'];
                                    $paypal = 'https://www.paypal.com';

                                    if ($options['testaccount']) {
                                        $paypal = 'https://www.sandbox.paypal.com';
                                        $business = $options['testBusiness'];
                                    }

                                    $returnurl = htmlentities(Uri::root() . "index.php?option=com_breezingforms&confirmPayPal=true&form_id=" . $this->form . "&record_id=" . $this->record_id);
                                    // $cancelurl = htmlentities(Uri::root() . "index.php?msg=" . BFText::_('Transaction Cancelled'));
                                    $cancelurl = $options['cancelURL'];

                                    $html = '';
                                    if (!$this->inline)
                                        $html .= '<html><head><meta charset="UTF-8"></head><body>';

                                    HTMLHelper::_('bootstrap.modal');

                                    $ppselect = BFRequest::getVar('ff_nm_bfPaymentSelect', array());
                                    if (count($ppselect) != 0) {
                                        $ppselected = explode('|', $ppselect[0]);
                                        if (count($ppselected) == 4) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                        } else if (count($ppselected) == 5) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                            $options['shipping'] = htmlentities($ppselected[4] == 1 ? 1 : 0, ENT_QUOTES, 'UTF-8');
                                        } else if (count($ppselected) == 6) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                            $options['shipping'] = htmlentities($ppselected[4] == 1 ? 1 : 0, ENT_QUOTES, 'UTF-8');
                                            $options['currencyCode'] = htmlentities($ppselected[5] == '' ? 'USD' : $ppselected[5], ENT_QUOTES, 'UTF-8');
                                        }
                                    }

                                    // keeping this for compat reasons
                                    $ppselect = BFRequest::getVar('ff_nm_PayPalSelect', array());
                                    if (count($ppselect) != 0) {
                                        $ppselected = explode('|', $ppselect[0]);
                                        if (count($ppselected) == 4) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                        } else if (count($ppselected) == 5) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                            $options['shipping'] = htmlentities($ppselected[4] == 1 ? 1 : 0, ENT_QUOTES, 'UTF-8');
                                        } else if (count($ppselected) == 6) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                            $options['shipping'] = htmlentities($ppselected[4] == 1 ? 1 : 0, ENT_QUOTES, 'UTF-8');
                                            $options['currencyCode'] = htmlentities($ppselected[5] == '' ? 'USD' : $ppselected[5], ENT_QUOTES, 'UTF-8');
                                        }
                                    }
                                    // compat end

                                    $html .= "<form name=\"ff_submitform\" action=\"" . $paypal . "/cgi-bin/webscr\" method=\"post\" accept-charset=\"UTF-8\">";
                                    $html .= "<input type=\"hidden\" name=\"cmd\" value=\"_xclick\"/>";
                                    $html .= "<input type=\"hidden\" name=\"business\" value=\"" . $business . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $options['itemname'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"item_number\" value=\"" . $options['itemnumber'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"amount\" value=\"" . $options['amount'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"tax\" value=\"" . $options['tax'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"no_shipping\" value=\"" . ($options['shipping'] == 1 ? 0 : 1) . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"no_note\" value=\"1\"/>";
                                    if ($options['useIpn']) {
                                        $html .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . htmlentities(Uri::root() . "index.php?option=com_breezingforms&confirmPayPalIpn=true&raw=true&form_id=" . $this->form . "&record_id=" . $this->record_id) . "\"/>";
                                        if ($options['testaccount']) {
                                            $html .= "<input type=\"hidden\" name=\"test_ipn\" value=\"1\"/>";
                                        }

                                        $html .= "<input type=\"hidden\" name=\"return\" value=\"" . $returnurl . "\"/>";
                                    } else {

                                        $html .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . $returnurl . "\"/>";
                                        $html .= "<input type=\"hidden\" name=\"return\" value=\"" . $returnurl . "\"/>";
                                    }

                                    $html .= "<input type=\"hidden\" name=\"cancel_return\" value=\"" . $cancelurl . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"rm\" value=\"2\"/>";
                                    $html .= "<input type=\"hidden\" name=\"lc\" value=\"" . $options['locale'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"currency_code\" value=\"" . strtoupper($options['currencyCode']) . "\"/>";

                                    if (!$this->inline)
                                        $html .= "</form></body></html>";

                                    // TODO: let the user decide to use modal or simple alert
                                    if ($j15) {
                                        $html .= '<script type="text/javascript">' . nl() .
                                            indentc(1) . '<!--' . nl() .
                                            indentc(2) . '

										    SqueezeBox.initialize({});

										    SqueezeBox.loadModal = function(modalUrl,handler,x,y) {
										    		this.initialize();
										      		var options = $merge(options || {}, Json.evaluate("{handler: \'" + handler + "\', size: {x: " + x +", y: " + y + "}}"));
													this.setOptions(this.presets, options);
													this.assignOptions();
													this.setContent(handler,modalUrl);
										   	};

										    SqueezeBox.loadModal("' . Uri::root() . 'index.php?raw=true&option=com_breezingforms&showPayPalConnectMsg=true","iframe",300,100);

										 	

										' . nl() .
                                            indentc(1) . '// -->' . nl() .
                                            '</script>' . nl();
                                    }
                                    $html .= '<script type="text/javascript"><!--' . nl() . 'document.ff_submitform.submit();' . nl() . '//--></script>';
                                    echo $html;

                                    break;
                                }
                            }
                        }

                        break;

                    case 'Sofortueberweisung':

                        foreach ($areas as $area) {
                            foreach ($area['elements'] as $element) {
                                if ($element['internalType'] == 'bfSofortueberweisung') {

                                    $html = '';
                                    if (!$this->inline)
                                        $html .= '<html><head></head><body>';

                                    HTMLHelper::_('bootstrap.modal');

                                    $options = $element['options'];

                                    $ppselect = BFRequest::getVar('ff_nm_bfPaymentSelect', array());
                                    if (count($ppselect) != 0) {
                                        $ppselected = explode('|', $ppselect[0]);
                                        if (count($ppselected) == 4) {
                                            $options['reason_1'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['reason_2'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            if ($ppselected[3] != '' && intval($ppselected[3]) > 0) {
                                                $options['amount'] = '' . (doubleval($options['amount']) + doubleval($ppselected[3]));
                                            }
                                        }
                                    }

                                    $options['amount'] = str_replace('.', ',', $options['amount']);

                                    $hash = '';
                                    if (isset($options['project_password']) && trim($options['project_password']) != '') {

                                        $data = array(
                                            $options['user_id'], // user_id
                                            $options['project_id'], // project_id
                                            '', // sender_holder
                                            '', // sender_account_number
                                            '', // sender_bank_code
                                            '', // sender_country_id
                                            $options['amount'], // amount
                                            // currency_id, Pflichtparameter bei Hash-Berechnung
                                            $options['currency_id'],
                                            $options['reason_1'], // reason_1
                                            $options['reason_2'], // reason_2
                                            $this->form, // user_variable_0
                                            $this->record_id, // user_variable_1
                                            (isset($options['mailback']) && $options['mailback'] ? implode('###', $this->mailbackRecipients) : ''), // user_variable_2
                                            '', // user_variable_3
                                            '', // user_variable_4
                                            '', // user_variable_5
                                            $options['project_password']    // project_password
                                        );
                                        $data_implode = implode('|', $data);

                                        $gen = sha1($data_implode);

                                        $hash = '<input type="hidden" name="hash" value="' . $gen . '" />';
                                    }

                                    $mailback = '';
                                    if (isset($options['mailback']) && $options['mailback']) {
                                        $mailback = '<input type="hidden" name="user_variable_2" value="' . implode('###', $this->mailbackRecipients) . '" />';
                                    }

                                    $html .= '
									<!-- sofortüberweisung.de -->
									<form method="post" name="ff_submitform" action="https://www.sofortueberweisung.de/payment/start">
									<input type="hidden" name="user_id" value="' . $options['user_id'] . '" />
									<input type="hidden" name="project_id" value="' . $options['project_id'] . '" />
									<input type="hidden" name="reason_1" value="' . $options['reason_1'] . '" />
									<input type="hidden" name="reason_2" value="' . $options['reason_2'] . '" />
									<input type="hidden" name="amount" value="' . $options['amount'] . '" />
									<input type="hidden" name="currency_id" value="' . $options['currency_id'] . '" />
									<input type="hidden" name="language_id" value="' . $options['language_id'] . '" />
									<input type="hidden" name="user_variable_0" value="' . $this->form . '" />
									<input type="hidden" name="user_variable_1" value="' . $this->record_id . '" />
									' . $mailback . '
									' . $hash . '
									</form>
									<!-- sofortüberweisung.de -->
									';

                                    if ($j15) {
                                        // TODO: let the user decide to use modal or simple alert
                                        $html .= '<script type="text/javascript">' . nl() .
                                            indentc(1) . '<!--' . nl() .
                                            indentc(2) . '

										    SqueezeBox.initialize({});

										    SqueezeBox.loadModal = function(modalUrl,handler,x,y) {
										    		this.initialize();
										      		var options = $merge(options || {}, Json.evaluate("{handler: \'" + handler + "\', size: {x: " + x +", y: " + y + "}}"));
													this.setOptions(this.presets, options);
													this.assignOptions();
													this.setContent(handler,modalUrl);
										   	};

										    SqueezeBox.loadModal("' . Uri::root() . 'index.php?raw=true&option=com_breezingforms&showPayPalConnectMsg=true","iframe",300,100);

										' . nl() .
                                            indentc(1) . '// -->' . nl() .
                                            '</script>' . nl();
                                    }
                                    $html .= '<script type="text/javascript"><!--' . nl() . 'document.ff_submitform.submit();' . nl() . '//--></script>';

                                    if (!$this->inline)
                                        $html .= "</form></body></html>";

                                    echo $html;

                                    break;
                                }
                            }
                        }

                        break;

                    default:
                        $paymentAction = false;
                }
            }
        }

        // CONTENTBUILDER
        if (BFRequest::getVar('cb_controller', null) != 'edit' && $cbRecordId && is_array($cbResult) && isset($cbResult['data']) && isset($cbResult['data']['id']) && $cbResult['data']['id']) {
            if ($cbRecordId) {
                $return = BFRequest::getVar('return', '');
                if ($return) {
                    $return = bf_b64dec($return);
                    if (Uri::isInternal($return)) {
                        Factory::getApplication()->redirect($return);
                    }
                }
            }

            if ($cbResult['data']['force_login']) {
                $is15 = false;

                if (!Factory::getUser()->get('id', 0)) {
                    Factory::getApplication()->redirect(Route::_('index.php?option=com_users&view=login&Itemid=' . BFRequest::getInt('Itemid', 0), false));
                } else {

                    Factory::getApplication()->redirect(Route::_('index.php?option=com_users&view=profile&Itemid=' . BFRequest::getInt('Itemid', 0), false));
                }
            } else if (trim($cbResult['data']['force_url'])) {
                Factory::getApplication()->redirect(trim($cbResult['data']['force_url']));
            }

            Factory::getApplication()->enqueueMessage(BFText::_('COM_CONTENTBUILDER_SAVED'), 'success');
            Factory::getApplication()->redirect(Route::_('index.php?option=com_contentbuilder&controller=details&Itemid=' . BFRequest::getInt('Itemid', 0) . '&backtolist=' . BFRequest::getInt('backtolist', 0) . '&id=' . $cbResult['data']['id'] . '&record_id=' . $cbRecordId . '&limitstart=' . BFRequest::getInt('limitstart', 0) . '&filter_order=' . BFRequest::getCmd('filter_order'), false));
        }

        if (!$paymentAction) {

            if (defined('CRBCBF_INLINE')) {

                return;
            }

            if (!defined('VMBFCF_RUNNING')) {
                $ob = 0;
                while (@ob_get_level() > 0 && $ob <= 32) {
                    @ob_end_clean();
                    $ob++;
                }
                ob_start();
                echo '<!DOCTYPE html>
                    <html>
                    <head></head>
                    <body>';
            }

            if ($message == '')
                $message = $this->message;
            else {
                if ($this->message != '')
                    $message .= ":" . nl() . $this->message;
            } // if

            if (!$this->inline) {
                $url = ($this->inframe) ? $ff_mossite . '/index.php?format=html&tmpl=component' : (($this->runmode == _FF_RUNMODE_FRONTEND) ? '' : 'index.php?format=html' . (BFRequest::getCmd('tmpl', '') ? '&tmpl=' . BFRequest::getCmd('tmpl', '') : ''));
                echo '<form name="ff_submitform" action="' . $url . '" method="post">' . nl();
            } // if

            switch ($this->runmode) {
                case _FF_RUNMODE_FRONTEND:
                    echo indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->target > 1)
                        echo indentc(1) . '<input type="hidden" name="ff_target" value="' . htmlentities($this->target, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->inframe)
                        echo indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl();
                    if ($this->border)
                        echo indentc(1) . '<input type="hidden" name="ff_border" value="1"/>' . nl();
                    if ($this->page != 1)
                        indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->align != 1)
                        echo indentc(1) . '<input type="hidden" name="ff_align" value="' . htmlentities($this->align, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->top != 0)
                        echo indentc(1) . '<input type="hidden" name="ff_top" value="' . htmlentities($this->top, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    reset($ff_otherparams);
                    foreach ($ff_otherparams as $prop => $val)
                        echo indentc(1) . '<input type="hidden" name="' . htmlentities($prop, ENT_QUOTES, 'UTF-8') . '" value="' . htmlentities($val, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    break;

                case _FF_RUNMODE_BACKEND:
                    echo indentc(1) . '<input type="hidden" name="option" value="com_breezingforms"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="act" value="run"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_runmode" value="' . htmlentities($this->runmode, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->target > 1)
                        echo indentc(1) . '<input type="hidden" name="ff_target" value="' . htmlentities($this->target, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->inframe)
                        echo indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl();
                    if ($this->border)
                        echo indentc(1) . '<input type="hidden" name="ff_border" value="1"/>' . nl();
                    if ($this->page != 1)
                        indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->align != 1)
                        echo indentc(1) . '<input type="hidden" name="ff_align" value="' . htmlentities($this->align, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->top != 0)
                        echo indentc(1) . '<input type="hidden" name="ff_top" value="' . htmlentities($this->top, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    break;

                default: // _FF_RUNMODE_PREVIEW:
                    if ($this->inframe) {
                        echo indentc(1) . '<input type="hidden" name="option" value="com_breezingforms"/>' . nl() .
                            indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl() .
                            indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                            indentc(1) . '<input type="hidden" name="ff_runmode" value="' . htmlentities($this->runmode, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                        if ($this->page != 1)
                            indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    } // if
            } // if

            echo indentc(1) . '<input type="hidden" name="ff_contentid" value="' . BFRequest::getInt('ff_contentid', 0) . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_applic" value="' . BFRequest::getWord('ff_applic', '') . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_record_id" value="' . $this->record_id . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_module_id" value="' . BFRequest::getInt('ff_module_id', 0) . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_status" value="' . htmlentities($this->status, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_message" value="' . htmlentities($message, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_form_submitted" value="1"/>' . nl();

            if (BFRequest::getVar('tmpl') == 'component') {
                echo indentc(1) . '<input type="hidden" name="tmpl" value="component"/>' . nl();
            }
            if (isset($_REQUEST['cb_form_id']) && isset($_REQUEST['cb_record_id'])) {
                echo indentc(1) . '<input type="hidden" name="cb_form_id" value="' . BFRequest::getInt('cb_form_id', 0) . '"/>' . nl();
                echo indentc(1) . '<input type="hidden" name="cb_record_id" value="' . BFRequest::getInt('cb_record_id', 0) . '"/>' . nl();
                echo indentc(1) . '<input type="hidden" name="return" value="' . htmlentities(BFRequest::getVar('return', ''), ENT_QUOTES, 'UTF-8') . '"/>' . nl();
            }
            // TODO: turn off tracing in the options
            if ($this->traceMode & _FF_TRACEMODE_DIRECT) {
                $this->dumpTrace();
                ob_end_flush();
                echo '</pre>';
            } else {

                ob_end_flush();
                $this->dumpTrace();
            } // if
            restore_error_handler();

            if (!$this->inline) {
                echo '</form>' . nl() .
                    '<script type="text/javascript">' . nl() .
                    indentc(1) . '<!--' . nl() .
                    indentc(2) . 'document.ff_submitform.submit();' . nl() .
                    indentc(1) . '// -->' . nl() .
                    '</script>' . nl();
            } // if

            if (!defined('VMBFCF_RUNNING')) {
                $c = @ob_get_contents();
                @ob_end_clean();
                echo $c;

                echo '</body>
                      </html>';
            }
        }

        unset($_SESSION['ff_editable_overridePlg' . BFRequest::getInt('ff_contentid', 0) . $this->form_id]);
        unset($_SESSION['ff_editablePlg' . BFRequest::getInt('ff_contentid', 0) . $this->form_id]);
        Factory::getSession()->set('ff_editableMod' . BFRequest::getInt('ff_module_id', 0) . $this->form_id, 0);
        Factory::getSession()->set('ff_editable_overrideMod' . BFRequest::getInt('ff_module_id', 0) . $this->form_id, 0);

        if (!defined('VMBFCF_RUNNING')) {
            exit;
        }
    }

    private function getEvent(string $name): EventInterface
    {

        return new Event($name);
    }

    function removeDangerousHtml($value)
    {

        if (trim($value) == '')
            return '';

        $doc = new DOMDocument();
        $ret = @$doc->loadHTML('<div>' . $value . '</div>');

        if ($ret) {

            $script_tags = $doc->getElementsByTagName('script');
            $length = $script_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $script_tags->item($ii)->parentNode->removeChild($script_tags->item($ii));
            }
            $style_tags = $doc->getElementsByTagName('style');
            $length = $style_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $style_tags->item($ii)->parentNode->removeChild($style_tags->item($ii));
            }
            $iframe_tags = $doc->getElementsByTagName('iframe');
            $length = $iframe_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $iframe_tags->item($ii)->parentNode->removeChild($iframe_tags->item($ii));
            }
            $applet_tags = $doc->getElementsByTagName('applet');
            $length = $applet_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $applet_tags->item($ii)->parentNode->removeChild($applet_tags->item($ii));
            }
            $link_tags = $doc->getElementsByTagName('link');
            $length = $link_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $link_tags->item($ii)->parentNode->removeChild($link_tags->item($ii));
            }

            $mock = new DOMDocument;
            $body = $doc->getElementsByTagName('body')->item(0);
            foreach ($body->childNodes as $child) {
                $mock->appendChild($mock->importNode($child, true));
            }

            $container = $mock->getElementsByTagName('div')->item(0);

            $container = $container->parentNode->removeChild($container);

            while ($mock->firstChild) {
                $mock->removeChild($mock->firstChild);
            }

            while ($container->firstChild) {
                $mock->appendChild($container->firstChild);
            }

            return $mock->saveHTML();
        }

        return strip_tags($value);
    }

    // submit
}
