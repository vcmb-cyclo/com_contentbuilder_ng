<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.8
 * @package BreezingForms
 * @copyright (C) 2008-2012 by Markus Bopp
 * @license Released under the terms of the GNU General Public License
 * */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Uri\Uri;

class bfRecordManagement {

    private $version = '1.5';
    private $tz = 'UTC';

    function __construct() {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        jimport('joomla.version');
        $version = new JVersion();
        
        $this->version = $version->getShortVersion();
        $this->tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

        // bfRecordsTableContainer
    }

    function saveFilterState() {
        @ob_end_clean();

        if (BFRequest::getInt('form_id') > 0) {

            $db = BFFactory::getdbo();

            $db->setQuery("Update #__facileforms_forms Set filter_state = " . $db->quote(serialize($_POST)) . " Where id = " . $db->quote(BFRequest::getInt('form_id')));
            $db->query();
        }
        exit;
    }

    function getAvailableFields() {

        @ob_end_clean();

        $out = array();

        $db = BFFactory::getdbo();

        $db->setQuery("Select * From #__facileforms_elements Where published = 1 And `name` <> 'bfFakeName' And `name` <> 'bfFakeName2' And `name` <> 'bfFakeName3' And `name` <> 'bfFakeName4' And `name` <> 'bfFakeName5' And  form = " . BFRequest::getInt('form_id', 0) . " Order By `ordering`");

        $out['fields'] = $db->loadAssocList();

        $db->setQuery("Select filter_state From #__facileforms_forms Where id = " . BFRequest::getInt('form_id', 0));

        $out['filter_state'] = @unserialize($db->loadResult());

        echo json_encode($out);

        exit;
    }

    function setFlag() {
        @ob_end_clean();

        $column = explode('bfrecord_', BFRequest::getCmd('column', ''));
        if (count($column) == 2) {
            $db = BFFactory::getdbo();
            $db->setQuery("Update #__facileforms_records Set `" . $column[1] . "` = " . $db->quote(BFRequest::getInt('flag', 0)) . " Where id = " . $db->quote(BFRequest::getInt('record_id')));
            $db->query();
        }

        exit;
    }

    function setFlags($column) {
        $db = BFFactory::getdbo();

        $ids = BFRequest::getVar('cid', array());
        ArrayHelper::toInteger($ids);
        if (count($ids)) {
            $db = BFFactory::getdbo();
            $db->setQuery("Update #__facileforms_records Set `" . $column . "` = 1 Where id In (" . implode(',', $ids) . ")");
            $db->query();
        }
    }

    function getTableBar() {

        $out = '';

        $db = BFFactory::getdbo();

        // available forms

        $db->setQuery("Select * From #__facileforms_forms Order By `title`");
        $forms = $db->loadAssocList();

        $out = '<form onsubmit="return false;" style="display: inline;">';
        $out .= '<select name="form_selection" id="bfFormSelection">' . "\n";
        $out .= '<option value="0">' . htmlentities(BFText::_('COM_BREEZINGFORMS_ALL'), ENT_QUOTES, 'UTF-8') . '</option>' . "\n";
        foreach ($forms As $form) {

            $out .= '<option value="' . $form['id'] . '">' . htmlentities($form['title'], ENT_QUOTES, 'UTF-8') . ' (' . htmlentities($form['name'], ENT_QUOTES, 'UTF-8') . ')</option>' . "\n";
        }

        $out .= '</select>' . "\n";
        $out .= '</form>';

        // text search
        $out .= '<form onsubmit="return false;">';
        $out .= '<div id="bfSearchMenu">';
        $out .= '<div id="bfSearchWrapper"><div id="bfSearchOpen">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHRECORDS'), ENT_QUOTES, 'UTF-8') . '</div>';
        $out .= '<div id="bfSearch">' . "\n";
        $out .= '<label for="bfrecordsearch">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHTEXT'), ENT_QUOTES, 'UTF-8') . '</label>';
        $out .= '<input type="text" value="" name="bfrecordsearch" id="bfrecordsearch"/>';

        $out .= '<label for="bfrecordsearchintext">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERIN'), ENT_QUOTES, 'UTF-8') . '</label>';

        $out .= '<br/>';

        $out .= '<table style="width: 100%" border="0">';
        $out .= '<tr>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinuserid" id="bfrecordsearchinuserid" /> <label for="bfrecordsearchinuserid">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINUSERID'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinusername" id="bfrecordsearchinusername" /> <label for="bfrecordsearchinusername">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINUSERNAME'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinuserfullname" id="bfrecordsearchinuserfullname" /> <label for="bfrecordsearchinuserfullname">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINUSERFULLNAME'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '</tr>';
        $out .= '<tr>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinid" id="bfrecordsearchinid" /> <label for="bfrecordsearchinid">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINID'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinip" id="bfrecordsearchinip" /> <label for="bfrecordsearchinip">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINIP'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinviewed" id="bfrecordsearchinviewed" /> <label for="bfrecordsearchinviewed">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINVIEWED'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '</tr>';
        $out .= '<tr>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinexported" id="bfrecordsearchinexported" /> <label for="bfrecordsearchinexported">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINEXPORTED'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinarchived" id="bfrecordsearchinarchived" /> <label for="bfrecordsearchinarchived">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINARCHIVED'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinpayment" id="bfrecordsearchinpayment" /> <label for="bfrecordsearchinpayment">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINPAYMENT'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';
        $out .= '</tr>';
        $out .= '<tr>';

        $out .= '<td>';

        $out .= '<input type="checkbox" value="1" name="bfrecordsearchinopted" id="bfrecordsearchinopted" /> <label for="bfrecordsearchinopted">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINOPTED'), ENT_QUOTES, 'UTF-8') . '</label> ';

        $out .= '</td>';

	    $out .= '<td>';

	    $out .= '<input type="checkbox" value="1" name="bfrecordsearchinuntouched" id="bfrecordsearchinuntouched" /> <label for="bfrecordsearchinuntouched">' . htmlentities('untouched', ENT_QUOTES, 'UTF-8') . '</label> ';

	    $out .= '</td>';

        $out .= '<td>';

        $out .= '<span id="bfrecordsearchintextspan">';
        $out .= '<input type="checkbox" checked="checked" value="1" name="bfrecordsearchintext" id="bfrecordsearchintext" /> <label for="bfrecordsearchintext">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHFILTERINTEXT'), ENT_QUOTES, 'UTF-8') . '</label> ';
        $out .= '</span>';

        $out .= '</td>';
        $out .= '</tr>';


        $out .= '</table>';

        $out .= '<br/>';

        // date & time based search

        $out .= '<div id="bfrecordsearchdatefromtowrap">';

        $out .= '<div id="bfrecordsearchdatefromwrap">';
        $out .= '<label for="bfrecordsearchdatefrom">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHDATEFROM'), ENT_QUOTES, 'UTF-8') . '</label>';
        $out .= '<br/>';
        $out .= '<input type="text" value="" name="bfrecordsearchdatefrom" id="bfrecordsearchdatefrom"/>';
        $out .= '</div>';

        $out .= '<div id="bfrecordsearchtimefromwrap">';
        $out .= '<label for="bfrecordsearchtimefrom">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHTIMEFROM'), ENT_QUOTES, 'UTF-8') . '</label>';
        $out .= '<br/>';
        $out .= '<input type="text" value="" name="bfrecordsearchtimefrom" id="bfrecordsearchtimefrom"/>';
        $out .= '</div>';

        $out .= '</div>';

        $out .= '<div style="clear:both;"></div>';

        $out .= '<div id="bfrecordsearchtimefromtowrap">';

        $out .= '<div id="bfrecordsearchdatetowrap">';
        $out .= '<label for="bfrecordsearchdateto">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHDATETO'), ENT_QUOTES, 'UTF-8') . '</label>';
        $out .= '<br/>';
        $out .= '<input type="text" value="" name="bfrecordsearchdateto" id="bfrecordsearchdateto"/>';
        $out .= '</div>';

        $out .= '<div id="bfrecordsearchtimetowrap">';
        $out .= '<label for="bfrecordsearchtimeto">' . htmlentities(BFText::_('COM_BREEZINGFORMS_SEARCHTIMETO'), ENT_QUOTES, 'UTF-8') . '</label>';
        $out .= '<br/>';
        $out .= '<input type="text" value="" name="bfrecordsearchtimeto" id="bfrecordsearchtimeto"/>';
        $out .= '</div>';

        $out .= '</div>';

        $out .= '<div style="clear: both;"></div>';

        $out .= '<button class="btn btn-primary button bfFilterTriggerer">' . htmlentities(BFText::_('COM_BREEZINGFORMS_BUTTONFILTER'), ENT_QUOTES, 'UTF-8') . '</button> ';
        $out .= '<input type="reset" class="btn btn-secondary" value="' . htmlentities(BFText::_('COM_BREEZINGFORMS_BUTTONFILTERRESET'), ENT_QUOTES, 'UTF-8') . '"/se> ';
        $out .= '</div>';
        $out .= '</div>';
        $out .= '</div>';

        $out .= '<div id="bfAvailableFieldsMenu">';
        $out .= '<div id="bfAvailableFieldsWrapper"><div id="bfAvailableFieldsOpen">' . htmlentities(BFText::_('COM_BREEZINGFORMS_OPENFIELDS'), ENT_QUOTES, 'UTF-8') . '</div>';
        $out .= '<div id="bfAvailableFields"></div>' . "\n";
        $out .= '</div>';
        $out .= '</div>';

        $out .= '<div style="clear: both;"></div>';

        $out .= '</form>';

        $out .= '<form name="bfSelectionForm" id="bfSelectionForm" method="post" action="">';
        $out .= '</form>';


        return $out;
    }

    function editRecord() {

    }

    function getCsvImport() {

        global $ff_config;

        $form = BFRequest::getInt('form_selection');
        JFactory::getSession()->set('form', $form);
        if ($form == 0) {
            echo BFText::_('COM_BREEZINGFORMS_IMPORT_CSV_MSG');
            return;
        }
        ?>

        <form action="index.php?option=com_breezingforms&act=managerecords&task=setcsvimport&tmpl=component" method="post" enctype="multipart/form-data">
            <select name=encoding>
                <option value="0">UTF-8</option>
                <option selected="selected" value="UTF-16LE">UTF-16LE (BreezingForms default)</option>
                <option value="WINDOWS-1250">WINDOWS-1250</option>
                <option value="WINDOWS-1251">WINDOWS-1251</option>
                <option value="WINDOWS-1252">WINDOWS-1252</option>
                <option value="WINDOWS-1253">WINDOWS-1253</option>
                <option value="WINDOWS-1254">WINDOWS-1254</option>
                <option value="WINDOWS-1255">WINDOWS-1255</option>
                <option value="WINDOWS-1256">WINDOWS-1256</option>
                <option value="ISO-8859-1">ISO-8859-1</option>
                <option value="ISO-8859-2">ISO-8859-2</option>
                <option value="ISO-8859-3">ISO-8859-3</option>
                <option value="ISO-8859-4">ISO-8859-4</option>
                <option value="ISO-8859-5">ISO-8859-5</option>
                <option value="ISO-8859-6">ISO-8859-6</option>
                <option value="ISO-8859-7">ISO-8859-7</option>
                <option value="ISO-8859-8">ISO-8859-8</option>
                <option value="ISO-8859-9">ISO-8859-9</option>
                <option value="ISO-8859-10">ISO-8859-10</option>
                <option value="ISO-8859-11">ISO-8859-11</option>
                <option value="ISO-8859-12">ISO-8859-12</option>
                <option value="ISO-8859-13">ISO-8859-13</option>
                <option value="ISO-8859-14">ISO-8859-14</option>
                <option value="ISO-8859-15">ISO-8859-15</option>
                <option value="ISO-8859-16">ISO-8859-16</option>
                <option value="UTF-8-MAC">UTF-8-MAC</option>
                <option value="UTF-16">UTF-16</option>
                <option value="UTF-16BE">UTF-16BE</option>
                <option value="UTF-32">UTF-32</option>
                <option value="UTF-32BE">UTF-32BE</option>
                <option value="UTF-32LE">UTF-32LE</option>
                <option value="ASCII">ASCII</option>
                <option value="BIG-5">BIG-5</option>
                <option value="HEBREW">HEBREW</option>
                <option value="CYRILLIC">CYRILLIC</option>
                <option value="ARABIC">ARABIC</option>
                <option value="GREEK">GREEK</option>
                <option value="CHINESE">CHINESE</option>
                <option value="KOREAN">KOREAN</option>
                <option value="KOI8-R">KOI8-R</option>
                <option value="KOI8-U">KOI8-U</option>
                <option value="KOI8-RU">KOI8-RU</option>
                <option value="EUC-JP">EUC-JP</option>
            </select><br>
            <?php echo BFText::_('COM_BREEZINGFORMS_CSV_ENCODING_MSG') . '<br><br>'; ?>
            <input type="hidden" name="form_id" value="<?php echo BFRequest::getInt('form_selection',0);?>" />
            <?php echo BFText::_('COM_BREEZINGFORMS_UPLOAD_MSG'); ?> <input type="file" name="csv_file" accept=".csv" />
            <input type="submit" value="<?php echo BFText::_('COM_BREEZINGFORMS_UPLOAD_FILE_MSG'); ?>" />
        </form>
        <?php
    }

    function utf8_fopen_read($fileName, $encoding) {
        $fc = iconv($encoding, 'UTF-8//TRANSLIT', file_get_contents($fileName));
        $handle = fopen("php://memory", "rw");
        fwrite($handle, $fc);
        fseek($handle, 0);
        return $handle;
    }

    function closeSquBox() {
        ?>
        <script type="text/javascript">
            window.top.location.href = "index.php?option=com_breezingforms&act=managerecords&task=";
        </script>
        <?php
    }

    function setCsvImport() {

        $db = BFFactory::getdbo();

        $form = JFactory::getSession()->get('form');
        $encoding = $_POST["encoding"];
        $file = $_FILES['csv_file']['tmp_name'];

        if (!@fopen($file, 'r')) {
            echo BFText::_('COM_BREEZINGFORMS_FILE_ERROR_MSG');
            return;
        }

        if ($encoding != '0') {
            if (!function_exists('iconv')) {
                echo BFText::_('COM_BREEZINGFORMS_NO_ICONV_MSG');
                return;
            }
            $handle = $this->utf8_fopen_read("$file", $encoding);
        } else {
            $handle = fopen("$file", "rb");
        }

        $i = 0;
        while (!feof($handle)) {
            $lines[$i] = fgets($handle);
            $i++;
        }

        if ($lines[0] == '') {
            echo BFText::_('COM_BREEZINGFORMS_EMPTY_FILE_MSG');
            return;
        }

        fclose($handle);

        $title = array();
        $record = array();
        $first = true;
        $j = 0;
        $records = array();

        foreach ($lines as $line) {

            if ($first) {
                $line = strtolower($line);
                $line = str_replace('"', '', $line);
                $title = explode(';', $line);
                $first = false;
            } else {
                $records[$j] = str_replace('"', '', explode('";"', $line));
                $j++;
            }
        }

        if (count($title) == 1) {
            echo BFText::_('COM_BREEZINGFORMS_EMPTY_FILE_MSG');
            return;
        }

        $db->setQuery("Select `title`,`name` From #__facileforms_forms Where id = " . BFRequest::getInt('form_id',0));
        $the_form = $db->loadObject();

        $recordcolumns = 'id, submitted, form, title, name, ip, browser, opsys, provider, viewed, exported, archived, user_id, username, user_full_name, paypal_tx_id, paypal_payment_date, paypal_testaccount, paypal_download_tries';
        $columns = explode(', ', strtolower($recordcolumns));
        $columns[3] = 'bf_form_title';
        $columns[4] = 'bf_form_name';

        foreach ($records as $record) { // Insert in Record Table
            if (count($record) <= 1) {
                continue;
            }

            $query = 'Insert Into #__facileforms_records (' . $recordcolumns . ') VALUES (';

            $first = true;

            //Adding empty variable which will contain form name later while exporting specific form
            $formname = '';
            for ($i = 0; $i < count($columns); $i++) {
                if (!$first) {
                    $query = $query . ', ';
                }

                if ($columns[$i] === 'id' || $columns[$i] === 'form') {
                    if ($columns[$i] === 'id') {
                        $query = $query . 'NULL';
                    }
                    if ($columns[$i] === 'form') {
                        $query = $query . $db->Quote($form);
                    }
                } else if ($columns[$i] === 'viewed' || $columns[$i] === 'exported' || $columns[$i] === 'archived') {

                    $query = $query . $db->Quote($record[$j] ? 1 : 0);

                } else if ($columns[$i] === 'paypal_testaccount' || $columns[$i] === 'paypal_download_tries') {

                    $query = $query . $db->Quote($record[$j] ? 1 : 0);

                }  else if ($columns[$i] === 'paypal_payment_date') {

                    $query = $query . $db->Quote(!$record[$j] || $record[$j] == '-' ? '1970-01-01 00:00:00' : $record[$j] );

                } else {
                    if (in_array($columns[$i], $title) ) {
                        if ($columns[$i] === 'bf_form_title') {
                            $j = array_search($columns[$i], $title);
                            $query = $query . $db->Quote($record[$j]);
                            // $i++;
                        }
                        else if ($columns[$i] === 'bf_form_name') {
                            $query = $query . $db->Quote($record[$j]);
                            $formname = $record[$j];
                            // $i++;
                        }
                        else {
                            $j = array_search($columns[$i], $title);
                            $query = $query . $db->Quote($record[$j]);
                        }
                    } else {

                        $value = '';
                        switch($columns[$i]){
                            case 'bf_form_title': $value = $the_form->title; break;
                            case 'bf_form_name': $value = $the_form->name; break;
                            case 'submitted': $value = date('Y-m-d H:i:s'); break;
                            case 'ip': $value = $_SERVER['REMOTE_ADDR']; break;
                            case 'user_id': $value = JFactory::getUser()->get('id',0); break;
                            case 'username': $value = JFactory::getUser()->get('username',''); break;
                        }

                        $query = $query . '"'.$value.'"';
                    }
                }

                $first = false;
            }

            $query = $query . ')';

            $db->setQuery($query);
            $db->query();

            if ($db->getErrorMsg() != '') {
                echo 'Import Error: ' . $db->getErrorMsg();
                return;
            }

            //Insert Subrecord
            $query = 'SELECT MAX(id) AS lastentry FROM #__facileforms_records';
            $db->setQuery($query);
            $id = $db->loadAssoc();


            $subrecordcolumns = 'record, element, title, name, type, value';
            $record_size = count($record);

            for ($i = array_search('download_tries', $title) + 1; $i < $record_size; $i++) {
                $db->setQuery("Select * From #__facileforms_elements Where form = " . $db->Quote($form) . " And `name` = " . $db->Quote(trim($title[$i])));
                $values = $db->loadAssoc();

                $query = 'Insert Into #__facileforms_subrecords (' . $subrecordcolumns . ') VALUES (' . $db->Quote($id['lastentry']);

                $query = $query . ' ,';
                $query = $query . $db->Quote(!$values['id'] ? 0 : $values['id']);
                $query = $query . ' ,';
                $query = $query . $db->Quote($values['title']);
                $query = $query . ' ,';
                $query = $query . $db->Quote($values['name']);
                $query = $query . ' ,';
                $query = $query . $db->Quote($values['type']);


                $query = $query . ' ,';
                $query = $query . $db->Quote($record[$i]);
                $query = $query . ')';

                $db->setQuery($query);
                $db->query();

                if ($db->getErrorMsg() != '') {
                    echo 'Import Error (subrecords): ' . $db->getErrorMsg();
                    return;
                }

                $j++;
            }
        } // End Insert
        // Start Cleanup

        $query = 'SELECT id FROM #__facileforms_records WHERE title = "" AND name = ""';
        $db->setQuery($query);
        $delID = $db->loadAssocList();

        foreach ($delID as $id) {
            $query = 'DELETE FROM #__facileforms_records WHERE id = ' . $db->Quote($id['id']);
            $db->setQuery($query);
            $db->query();
        }
        // End Cleanup

        $this->closeSquBox();
    }

    function listRecords() {

        JHTML::_('behavior.keepalive');


        if (version_compare($this->version, '3.0', '>=')) {
            JToolBarHelper::custom('exportPdf', 'download', 'download', BFText::_('COM_BREEZINGFORMS_PDF'), false);
            JToolBarHelper::custom('exportCsv', 'download', 'download', BFText::_('COM_BREEZINGFORMS_CSV'), false);
            JToolBarHelper::custom('exportXml', 'download', 'download', BFText::_('COM_BREEZINGFORMS_XML'), false);
            JToolBarHelper::custom('csvimport', 'upload', 'upload', BFText::_('COM_BREEZINGFORMS_CSV'), false);
            JToolBarHelper::custom('viewed', 'eye-open', 'eye-open', BFText::_('COM_BREEZINGFORMS_TOOLBAR_VIEW'), false);
            JToolBarHelper::custom('exported', 'share', 'share', BFText::_('COM_BREEZINGFORMS_TOOLBAR_EXPORT'), false);
            JToolBarHelper::custom('archived', 'archive', 'archive', BFText::_('COM_BREEZINGFORMS_TOOLBAR_ARCHIVE'), false);
            JToolBarHelper::custom('remove', 'delete.png', 'delete_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_DELETE'), false);
        } else {
            JToolBarHelper::title('<img src="' . Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/themes/easymode/i/logo-breezingforms.png' . '" align="top"/>');
            JToolBarHelper::custom('exportPdf', 'ff_download', 'ff_download_f2', BFText::_('COM_BREEZINGFORMS_PDF'), false);
            JToolBarHelper::custom('exportCsv', 'ff_download', 'ff_download_f2', BFText::_('COM_BREEZINGFORMS_CSV'), false);
            JToolBarHelper::custom('exportXml', 'ff_download', 'ff_download_f2', BFText::_('COM_BREEZINGFORMS_XML'), false);
            JToolBarHelper::custom('csvimport', 'ff_upload', 'ff_upload_f2', BFText::_('COM_BREEZINGFORMS_CSV'), false);
            JToolBarHelper::custom('viewed', 'ff_switch', 'ff_switch_f2', BFText::_('COM_BREEZINGFORMS_TOOLBAR_VIEW'), false);
            JToolBarHelper::custom('exported', 'ff_switch', 'ff_switch_f2', BFText::_('COM_BREEZINGFORMS_TOOLBAR_EXPORT'), false);
            JToolBarHelper::custom('archived', 'ff_switch', 'ff_switch_f2', BFText::_('COM_BREEZINGFORMS_TOOLBAR_ARCHIVE'), false);
            JToolBarHelper::custom('remove', 'delete.png', 'delete_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_DELETE'), false);
            JFactory::getDocument()->addStyleDeclaration(
                '

                            .icon-32-ff_switch {
                                    background-image:url(components/com_breezingforms/images/icons/switch.png);
                            }

                            .icon-32-ff_switch_f2 {
                                    background-image:url(components/com_breezingforms/images/icons/switch_f2.png);
                            }

                            .icon-32-ff_download {
                                    background-image:url(components/com_breezingforms/images/icons/download.png);
                            }

                            .icon-32-ff_download_f2 {
                                    background-image:url(components/com_breezingforms/images/icons/download_f2.png);
                            }
                            '
            );
        }

        //JFactory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jq.migrate.js');
        //JFactory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jq.min.js');
        //JFactory::getDocument()->addScriptDeclaration('jQuery.noConflict();' . "\n");
        JFactory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jq-ui.min.js');
        JFactory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jtable/jq.jtable.js');

        $lang = JFactory::getLanguage()->getTag();
        $lang = explode('-', $lang);
        $lang = strtolower($lang[0]);
        if (JFile::exists(JPATH_SITE . '/components/com_breezingforms/libraries/jquery/jtable/localization/jquery.jtable.' . $lang . '.js')) {
            JFactory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jtable/localization/jquery.jtable.' . $lang . '.js');
        }

        JFactory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/pickadate/picker.js');
        JFactory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/pickadate/picker.date.js');
        JFactory::getDocument()->addScript(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/pickadate/picker.time.js');
        JFactory::getDocument()->addScript(Uri::root(true) . '/administrator/components/com_breezingforms/libraries/jquery/plugins/json.js');

        JFactory::getDocument()->addStyleSheet(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jtable/themes/metro/jq.ui.css');
        JFactory::getDocument()->addStyleSheet(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/jtable/themes/metro/blue/jtable.css');
        JFactory::getDocument()->addStyleSheet(Uri::root() . 'administrator/components/com_breezingforms/admin/style.css');

        JFactory::getDocument()->addStyleSheet(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/pickadate/themes/default.css');
        JFactory::getDocument()->addStyleSheet(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/pickadate/themes/default.date.css');
        JFactory::getDocument()->addStyleSheet(Uri::root(true) . '/components/com_breezingforms/libraries/jquery/pickadate/themes/default.time.css');
        ?>
        <script type="text/javascript">
            function ct_quote(str) {
                return (str + '').replace(/[\"]/g, '&quot;').replace(/\u0000/g, '\\0');
            }
        </script>
        <?php

        echo JHtmlBootstrap::renderModal(
            'bfCsvModal',
            array(
                'url' => 'index.php',
                'width' => '300px',
                'height' => '250px'
            )
        );

        // faking an existing adminform for the toolbar
        echo '<form name="adminForm" id="adminForm"><input type="hidden" name="task" value=""/></form>';

        JFactory::getDocument()->addScriptDeclaration( '  
            Array.prototype.bfinsert = function (index, item) {
                this.splice(index, 0, item);
            };
            
            jQuery(document).ready(function(){
            
                jQuery("#adminForm").get(0).onsubmit = function(){ return false; };
            
                jQuery("joomla-toolbar-button").click( function(e){

                    e.preventDefault();

                    let pressbutton = jQuery(this).attr("task");

                    var form_selection = jQuery("#bfFormSelection").val();

                    switch (pressbutton) {
                        case "csvimport":

                            jQuery("#adminForm").get(0).onsubmit = function(){ return false; };

                            jQuery("#bfCsvModal").modal("show");
                            
                            let new_url = "index.php?option=com_breezingforms&act=managerecords&task=csvimport&form_selection="+form_selection+"&tmpl=component";

                            jQuery("#bfCsvModal").attr("data-url", new_url);
                            if(typeof jQuery("#bfCsvModal iframe").get(0) != "undefined"){
                            }
                            else{
                                jQuery("#bfCsvModal").attr("data-iframe", jQuery("#bfCsvModal").attr("data-iframe").replace("index.php", new_url));
                            }
                            
                            if(typeof jQuery("#bfCsvModal iframe").get(0) != "undefined"){
                                jQuery("#bfCsvModal iframe").get(0).src = new_url;
                            }

                            break;
                        case "viewed":
                        case "exported":
                        case "archived":
                            var html = "";
                            jQuery("#adminForm").get(0).onsubmit = function(){ return false; };
                            jQuery("#bfRecordsTableContainer").jtable("selectedRows").each(
                                function(){
                                    var record = jQuery(this).data("record");
                                    html += "<input type=\"hidden\" name=\"cid[]\" value=\""+record.bfrecord_id+"\" />";

                                }
                            );
                            jQuery("#bfSelectionForm").attr("action", "index.php?option=com_breezingforms&act=managerecs&task="+pressbutton+"&form_selection="+form_selection);
                            jQuery("#bfSelectionForm").html(html);
                            document.bfSelectionForm.submit();
                            break;
                        case "exportPdf":
                        case "exportCsv":
                        case "exportXml":
                            var html = "";
                            jQuery("#adminForm").get(0).onsubmit = function(){ return false; };
                            jQuery("#bfRecordsTableContainer").jtable("selectedRows").each(
                                function(){
                                    var record = jQuery(this).data("record");
                                    html += "<input type=\"hidden\" name=\"cid[]\" value=\""+record.bfrecord_id+"\" />";

                                }
                            );
                            jQuery("#bfSelectionForm").attr("action", "index.php?option=com_breezingforms&act=managerecs&task="+pressbutton+"&form_selection="+form_selection);
                            jQuery("#bfSelectionForm").html(html);
                            document.bfSelectionForm.submit();
                            break;
                        case "remove":
                                jQuery("#adminForm").get(0).onsubmit = function(){ return false; };
                                if (confirm(' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_ASKDELETE')) . ')) {
                                    var selectedRows = jQuery("#bfRecordsTableContainer").jtable("selectedRows");
                                    jQuery("#bfRecordsTableContainer").jtable("deleteRows", selectedRows);
                                };
                                break;
                        default:
                                jQuery("#adminForm").get(0).onsubmit = function(){ return false; };
                                break;
                    }

                    return false;
                });
            
            });
            
            function bf_set_flag(column,flag,record_id,flag_div_id){
                jQuery.ajax({
                    type: "POST",
                    url: "index.php",
                    data: {
                        option: "com_breezingforms",
                        act: "recordmanagement",
                        task: "setFlag",
                        record_id: record_id,
                        flag: flag ? 1 : 0,
                        column: column
                    },
					error:function(jqXHR, textStatus, errorThrown) 
					{
						console.log(textStatus);
						console.log(errorThrown);
						console.log(jqXHR);
						//alert(errorThrown);
					},
                    success: function(){
                        var viewed = "<a href=\"javascript:bf_set_flag(\'"+column+"\',true,\'"+record_id+"\',\'"+flag_div_id+"\')\"><img src=\"components/com_breezingforms/images/icons/publish_x.png\" border=\"0\"></a>";
                        if(flag == 1){
                            viewed = "<a href=\"javascript:bf_set_flag(\'"+column+"\',false,\'"+record_id+"\',\'"+flag_div_id+"\')\"><img src=\"components/com_breezingforms/images/icons/publish_g.png\" border=\"0\"></a>";
                         }
                        jQuery("#"+flag_div_id).html(viewed);
                    }
              });
            }
            
            function bf_editable_record_field(fieldname){
                switch(fieldname){
                    case "bfrecord_id":
                    case "bfrecord_ip":
                    case "bfrecord_submitted":
                    case "bfrecord_title":
                    case "bfrecord_name":
                    case "bfrecord_user_id":
                    case "bfrecord_username":
                    case "bfrecord_user_full_name":
                        return false;
                        break;
                }
                return true;
            }
            
         
            
            var form_selection = 0;
            jQuery(document).ready(function () {
            
                jQuery("#bfrecordsearchdatefrom").on("mousedown",function(event){
                    event.preventDefault();
                });
                
                jQuery("#bfrecordsearchdateto").on("mousedown",function(event){
                    event.preventDefault();
                });
                
                jQuery("#bfrecordsearchtimefrom").on("mousedown",function(event){
                    event.preventDefault();
                });
                
                jQuery("#bfrecordsearchtimeto").on("mousedown",function(event){
                    event.preventDefault();
                });
            
                jQuery("#bfrecordsearchdatefrom").pickadate({format: "yyyy-mm-dd", selectYears: true, selectMonths: true, onClose: function() {
                    jQuery(this).blur();
                }});
                
                jQuery("#bfrecordsearchdateto").pickadate({format: "yyyy-mm-dd", selectYears: true, selectMonths: true, onClose: function() {
                    jQuery(this).blur();
                }});
                
                jQuery("#bfrecordsearchtimefrom").pickatime({format: "H:i", interval: 15, onClose: function() {
                    jQuery(".bfCalendar").blur();
                }});
                
                jQuery("#bfrecordsearchtimeto").pickatime({format: "H:i", interval: 15, onClose: function() {
                    jQuery(".bfCalendar").blur();
                }});
                
                var openOrigText = jQuery("#bfAvailableFieldsOpen").html();
                var searchOrigText = jQuery("#bfSearchOpen").html();
                
                jQuery("#bfAvailableFieldsOpen").html(openOrigText+" +");
                jQuery("#bfSearchOpen").html(searchOrigText+" +");
                
                var fieldsOpen = false;
                var searchOpen = false;
                
                jQuery("#bfSearchOpen").mousedown(function(e){ e.preventDefault(); });
                jQuery("#bfSearchOpen").off("click");
                jQuery("#bfSearchOpen").on("click",function(e){
                
                    if(document.selection && document.selection.empty) {
                        document.selection.empty();
                    } else if(window.getSelection) {
                        var sel = window.getSelection();
                        sel.removeAllRanges();
                    }
                
                    if(!searchOpen){
                    
                        // hiding available fields if search is open
                        jQuery("#bfAvailableFields").css("display","none");
                        jQuery("#bfAvailableFieldsOpen").html(openOrigText+" +");
                        fieldsOpen = false;
                        
                        jQuery("#bfSearch").css("display","block");
                        searchOpen = true;
                        jQuery("#bfSearchOpen").html(searchOrigText+" -");
                    }else{
                        jQuery("#bfSearch").css("display","none");
                        searchOpen = false;
                        jQuery("#bfSearchOpen").html(searchOrigText+" +");
                    }
                });

                var default_fields = {
                    bfrecord_id: {
                        title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_ID')) . ',
                        key: true,
                        edit: false,
                        create: false,
                    },
                    bfrecord_submitted: {
                        title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_SUBMITTED')) . ',
                        type: "date",
                        create: false,
                        edit: false,
                        type: "text"
                    },
                    bfrecord_ip: {
                        title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_IP')) . ',
                        create: false,
                        edit: false
                    },
                    bfrecord_title: {
                        title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_TITLE')) . ',
                        create: false,
                        edit: false
                    },
                    bfrecord_name: {
                        title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_NAME')) . ',
                        create: false,
                        edit: false
                    },
                    
                    //CHILD TABLE DEFINITION FOR "DETAILS"
                    bfrecord_details: {
                        title: "",
                        width: "1%",
                        sorting: false,
                        edit: false,
                        create: false,
                        "delete": false,
                        listClass: "bfrecord-details-button",
                        display: function (detail_data) {
                        
                            //Create an image that will be used to open child table
                            var $img = jQuery("<img style=\"width: 16px !important; min-width: 16px !important; max-width: 16px !important; cursor: pointer;opacity: 0.5;\" onmouseover=\"this.style.opacity=\'1.0\'\" onmouseout=\"this.style.opacity=\'0.5\'\" src=\"' . Uri::root() . '/components/com_breezingforms/libraries/jquery/jtable/themes/metro/list_metro.png' . '\" title=\"\" />");
                            
                            //Open child table when user clicks the image
                            $img.click(function () {
                            
                                jQuery.ajax({
                                    type: "POST",
                                    url: "index.php",
                                    data: {
                                        option: "com_breezingforms",
                                        act: "recordmanagement",
                                        task: "getAvailableFields",
                                        form_id: detail_data.record.bfrecord_form_id
                                    },
									error:function(jqXHR, textStatus, errorThrown) 
									{
										console.log(textStatus);
										console.log(errorThrown);
										console.log(jqXHR);
										//alert(errorThrown);
									},
                                    success: function(available_fields_data){
                                        var jsondata = jQuery.parseJSON(available_fields_data);
                                        var detail_fields_raw = jsondata.fields;
                                        
                                        var detail_fields = {
                                            bfrecord_id: {
                                                title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_ID')) . ',
                                                key: true,
                                                edit: false,
                                                create: false,
                                                visibility: "hidden"
                                            },
                                            custom: {
                                                title: "",
                                                width: "100%",
                                                create: false,
                                                edit: false,
                                                display: function(data){
                                                    var out = "<table class=\"bfDetailsTable\">";
                                                    for(var i = 0; i < detail_fields_raw.length; i++){
                                                        var the_name = detail_fields_raw[i]["name"];
                                                        if(the_name.length !== null && data.record["bfrecord_custom_"+the_name] !== null){
                                                       
                                                            out += "<tr>";
                                                            
                                                            var the_title = detail_fields_raw[i]["title"];
                                                            var the_value = data.record["bfrecord_custom_"+the_name] !== null ? data.record["bfrecord_custom_"+the_name] : "";
                                                            
                                                            out += "<td class=\"bfDetailsTableLabelCol\"><strong>"+jQuery("<div/>").text(the_title).text()+"</strong>";
                                                            out += "<br /><small>' . addslashes(BFText::_('COM_BREEZINGFORMS_ELEMENT_NAME')) . ': "+the_name+"</small>";
                                                            out += "<br /><small>' . addslashes(BFText::_('COM_BREEZINGFORMS_RECORDS_ELEMENTID')) . ': "+data.record["bfrecord_custom_element_id_"+the_name]+"</small>";
                                                            out += "<br /><small>' . addslashes(BFText::_('COM_BREEZINGFORMS_RECORDS_TYPE')) . ': "+data.record["bfrecord_custom_element_type_"+the_name]+"</small></td>";
                                                            out += "<td class=\"bfDetailsTableValueCol\">";
                                                            
                                                            if(typeof data.record["bfrecord_custom_element_type_"+the_name] != "undefined" && data.record["bfrecord_custom_element_type_"+the_name] != "File Upload" && data.record["bfrecord_custom_element_type_"+the_name] != "Signature" && data.record["bfrecord_custom_element_type_"+the_name] !== null){
                                                                out += jQuery("<div/>").text(the_value).html().replace(/\\n/g,"<br />");
                                                            }else{
                                                                out += the_value;
                                                            }
                                                            
                                                            out += "</td>";
                                                            out += "</tr>";
                                                        }
                                                    }
                                                    
                                                    out += "<tr>";
                                                    out += "<td colspan=\"2\" class=\"bfDetailsTableHead\">";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_SUBMINFO')) . ';
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // submitted;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_SUBMITTED')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_submitted"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // ip;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_IP')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_ip"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // browser;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_BROWSER')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_browser"];
                                                    out += "</td>";
                                                    out += "</tr>";

                                                    // opsys;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_OPSYS')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_opsys"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // user id
                                                    out += "<tr>";
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERID')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_user_id"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // user name
                                                    out += "<tr>";
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERUSERNAME')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_username"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // user full name
                                                    out += "<tr>";
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERFULLNAME')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_user_full_name"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    

                                                    // record info

                                                    out += "<tr>";
                                                    out += "<td colspan=\"2\" class=\"bfDetailsTableHead\">";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_RECORDINFO')) . ';
                                                    out += "</td>";
                                                    out += "</tr>";

                                                    // record id;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_RECORDID')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_id"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // viewed;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_VIEWED')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_viewed"] == 1 ? ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_YES')) . ' : ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_NO')) . ' ;
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // exported;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_EXPORTED')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_exported"] == 1 ? ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_YES')) . ' : ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_NO')) . ' ;
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // archived;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_ARCHIVED')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_archived"] == 1 ? ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_YES')) . ' : ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_NO')) . ' ;
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // opted;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_OPTED')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_opted"] == 1 ? ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_YES')) . ' : ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_NO')) . ' ;
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // payment info

                                                    out += "<tr>";
                                                    out += "<td colspan=\"2\" class=\"bfDetailsTableHead\">";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_PAYMENT_INFORMATION')) . ';
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_TRANSACTION_ID')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_payment_tx_id"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_TRANSACTION_DATE')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_payment_date"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_TESTACCOUNT')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_payment_test"] == 1 ? ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_YES')) . ' : ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_NO')) . ' ;
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_DOWNLOAD_TRIES')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_payment_download_tries"];
                                                    out += "</td>";
                                                    out += "</tr>";

                                                    // form info

                                                    out += "<tr>";
                                                    out += "<td colspan=\"2\" class=\"bfDetailsTableHead\">";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_FORMINFO')) . ';
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                     // form id;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_ID')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_form_id"];
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // form title;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_TITLE')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += jQuery("<div/>").text(data.record["bfrecord_title"]).html();
                                                    out += "</td>";
                                                    out += "</tr>";
                                                    
                                                    // form name;
                                                    out += "<td class=\"bfDetailsTableLabelCol\"><strong>";
                                                    out += ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_NAME')) . ';
                                                    out += "</strong></td>";
                                                    out += "<td class=\"bfDetailsTableValueCol\">";
                                                    out += data.record["bfrecord_name"];
                                                    out += "</td>";
                                                    out += "</tr>";

                                                    out += "</table>";
                                                    return out;
                                                }   
                                            }
                                        };
                                        
                                        for(var i = 0; i < detail_fields_raw.length; i++){
                                            var pfx = detail_fields_raw[i]["name"].indexOf("bfrecord_") == 0 ? "" : "bfrecord_custom_";
                                            var field_name = detail_fields_raw[i]["name"];
                                            var the_func = function(pfx, field_name){
                                                detail_fields[pfx+detail_fields_raw[i]["name"]] = {
                                                    title: jQuery("<div/>").text(detail_fields_raw[i]["title"]).html(),
                                                    edit: bf_editable_record_field(pfx+detail_fields_raw[i]["name"]),
                                                    key: detail_fields_raw[i]["name"] == "bfrecord_id" ? true : false,
                                                    create: false,
                                                    visibility: "hidden",
                                                    input: function(data){
                                                        if (data.record) {
                                                            if(data.record["bfrecord_custom_"+field_name] === null){
                                                                return "<strong>Not submitted</strong>";
                                                            } else
                                                            if(typeof data.record["bfrecord_custom_file_upload_raw_"+field_name] !== "undefined"){ // bfrecord_custom_file_upload_raw_
                                                                return "<textarea style=\"min-width: 200px; height: 60px;\" name=\""+pfx+field_name+"\">"+ct_quote(jQuery("<div/>").text(data.record["bfrecord_custom_file_upload_raw_"+field_name]).html())+"</textarea>";
                                                            } else if (typeof data.record["bfrecord_custom_element_type_"+field_name] !== "undefined" && data.record["bfrecord_custom_element_type_"+field_name] == "Textarea"){
                                                                return "<textarea style=\"min-width: 200px; height: 60px;\" name=\""+pfx+field_name+"\">"+ct_quote(jQuery("<div/>").text(data.value).html())+"</textarea>";
                                                            } else {
                                                                return "<input style=\"min-width: 200px\" type=\"text\" name=\""+pfx+field_name+"\" value=\""+ct_quote(jQuery("<div/>").text(data.value).html())+"\" />";
                                                            }
                                                        }
                                                    }
                                                };
                                            }
                                            // we have to call each field within a function, otherwise indexes and field names won\'t be recognized appropriatly
                                            the_func(pfx, field_name);
                                        }
                                        
                                        jQuery("#bfRecordsTableContainer").jtable("openChildTable",
                                            $img.closest("tr"), //Parent row
                                            {
                                                title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_VIEWRECORD')) . ',
                                                actions: {
                                                    listAction: "index.php?option=com_breezingforms&act=recordmanagement&task=getListRecords&form_selection="+detail_data.record.bfrecord_form_id+"&record_id=" + detail_data.record.bfrecord_id,
                                                    updateAction: "index.php?option=com_breezingforms&act=recordmanagement&task=getListRecords&update=1&form_selection="+detail_data.record.bfrecord_form_id+"&record_id=" + detail_data.record.bfrecord_id
                                                },
                                                fields: detail_fields
                                            }, 
                                            function (data) { //opened handler
                                            setTimeout(function(){
                                                data.childTable.jtable("load");
                                            }, 500);
                                        });
                                    }
                                });
                            });
                            
                            //Return image to show on the person row
                            return $img;
                        }
                    }
                };

                var default_object = {
                    title: "",
                    actions: {
                        listAction: "index.php?option=com_breezingforms&act=recordmanagement&task=getListRecords&form_selection=0",
                        deleteAction: "index.php?option=com_breezingforms&act=recordmanagement&task=action&action=delete"
                    },
                    paging: true, //Enable paging
                    pageSize: 10, //Set page size (default: 10)
                    pageSizes: [10, 25, 50, 100, 250, 500, 1000, 5000, 10000, 100000],
                    sorting: true, //Enable sorting
                    defaultSorting: "id DESC", //Set default sorting,
                    selecting: true, //Enable selecting
                    multiselect: true, //Allow multiple selecting
                    selectingCheckboxes: true,
                    animationsEnabled:true,
                    fields: default_fields,
                    recordsLoaded: function(){
                        jQuery(".jtable th:nth-child(n+5)").addClass("hidden-phone");
                        jQuery(".jtable tr").each(
                            function(){
                                jQuery(this).children("td:nth-child(n+5)").addClass("hidden-phone");
                                jQuery(".bfrecord-details-button").removeClass("hidden-phone");
                                jQuery(".jtable-command-column").removeClass("hidden-phone");
                                jQuery(".jtable-command-column-header").removeClass("hidden-phone");

                            }
                        );
                    }
                };
                
                // cloning the default_object
                
                var custom_object = jQuery.extend(true, {}, default_object);
                
                // on form selection change, re-draw everything
                
                jQuery("#bfFormSelection").off("change");
                jQuery("#bfFormSelection").on("change",function(){
                    
                    // storing the selected form
                    
                    form_selection = jQuery(this).val();

                    // resetting custom fields if new form has been selected
                    
                    delete custom_object["fields"];
                    
                    // cloning the default fields
                    
                    custom_object["fields"] = jQuery.extend(true, {}, default_fields);

                    // retrieving new data for default object
                    
                    default_object["actions"]["listAction"] = "index.php?option=com_breezingforms&act=recordmanagement&task=getListRecords&form_selection="+form_selection;
                    
                    // re-rendering the table
                    
                    jQuery("#bfRecordsTableContainer").jtable({});
                    jQuery("#bfRecordsTableContainer").jtable("destroy");
                    jQuery("#bfRecordsTableContainer").jtable(default_object);
                    
                    setTimeout(function(){
                        jQuery("#bfRecordsTableContainer").jtable("load");
                    }, 500);
                    
                    // populating available fields options
                    
                    jQuery("#bfAvailableFieldsWrapper").css("display","none");
                    jQuery.ajax({
                        type: "POST",
                        url: "index.php",
                        data: {
                            option: "com_breezingforms",
                            act: "recordmanagement",
                            task: "getAvailableFields",
                            form_id: jQuery(this).val()
                        },
						error:function(jqXHR, textStatus, errorThrown) 
						{
							console.log(textStatus);
							console.log(errorThrown);
							console.log(jqXHR);
							//alert(errorThrown);
						},
                        success: function(data){
                        
                            // rendering the field selection
                            
                            var html = "<table style=\"width:100%;border:0;\">";
                            
                            // default fields
                            var field1 = {id: "bfDisplayFieldIDbfrecord_id", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_ID')) . ', name: "bfrecord_id"};
                            var field2 = {id: "bfDisplayFieldIDbfrecord_submitted", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_SUBMITTED')) . ', name: "bfrecord_submitted"};
                            var field3 = {id: "bfDisplayFieldIDbfrecord_user_id", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERID')) . ', name: "bfrecord_user_id"};
                            var field4 = {id: "bfDisplayFieldIDbfrecord_username", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERUSERNAME')) . ', name: "bfrecord_username"};
                            var field5 = {id: "bfDisplayFieldIDbfrecord_user_full_name", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_PROCESS_SUBMITTERFULLNAME')) . ', name: "bfrecord_user_full_name"};
                            var field6 = {id: "bfDisplayFieldIDbfrecord_ip", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_IP')) . ', name: "bfrecord_ip"};
                            var field7 = {id: "bfDisplayFieldIDbfrecord_title", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_TITLE')) . ', name: "bfrecord_title"};
                            var field8 = {id: "bfDisplayFieldIDbfrecord_name", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_NAME')) . ', name: "bfrecord_name"};
                            var field9 = {id: "bfDisplayFieldIDbfrecord_payment_tx_id", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_TRANSACTION_ID')) . ', name: "bfrecord_payment_tx_id"};
                            var field10 = {id: "bfDisplayFieldIDbfrecord_payment_date", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_TRANSACTION_DATE')) . ', name: "bfrecord_payment_date"};
                            var field11 = {id: "bfDisplayFieldIDbfrecord_payment_test", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_TESTACCOUNT')) . ', name: "bfrecord_payment_test"};
                            var field12 = {id: "bfDisplayFieldIDbfrecord_payment_download_tries", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_DOWNLOAD_TRIES')) . ', name: "bfrecord_payment_download_tries"};
                            var field13 = {id: "bfDisplayFieldIDbfrecord_viewed", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_VIEWED')) . ', name: "bfrecord_viewed"};
                            var field14 = {id: "bfDisplayFieldIDbfrecord_exported", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_EXPORTED')) . ', name: "bfrecord_exported"};
                            var field15 = {id: "bfDisplayFieldIDbfrecord_archived", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_ARCHIVED')) . ', name: "bfrecord_archived"};
                            var field16 = {id: "bfDisplayFieldIDbfrecord_opted", title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_RECORDS_OPTED')) . ', name: "bfrecord_opted"};

                            var jsondata = jQuery.parseJSON(data);
                            var fields = jsondata.fields;
                            var filter_state = jsondata.filter_state;

                            if(typeof fields.length != "undefined"){

                                fields.bfinsert(0, field1);
                                fields.bfinsert(1, field2);
                                fields.bfinsert(2, field3);
                                fields.bfinsert(3, field4);
                                fields.bfinsert(4, field5);
                                fields.bfinsert(5, field6);

                                // default fields (not so important to the right)
                                fields.bfinsert(fields.length, field7);
                                fields.bfinsert(fields.length, field8);
                                fields.bfinsert(fields.length, field9);
                                fields.bfinsert(fields.length, field10);
                                fields.bfinsert(fields.length, field11);
                                fields.bfinsert(fields.length, field12);
                                fields.bfinsert(fields.length, field13);
                                fields.bfinsert(fields.length, field14);
                                fields.bfinsert(fields.length, field15);
                                fields.bfinsert(fields.length, field16);
                                
                                for(var i = 0; i < fields.length; i++){
                                    if(i%3 == 0){
                                        html += "<tr>";
                                    }
                                    var checked = "";
                                    if(typeof filter_state.bfDisplayFieldsSelected != "undefined"){
                                        for(var j = 0; j < filter_state.bfDisplayFieldsSelected.length; j++){
                                            if(fields[i].id == filter_state.bfDisplayFieldsSelected[j]){
                                                checked = " checked=\"checked\" ";
                                                break;
                                            }
                                        }
                                    }
                                    html += "<td><input"+checked+" type=\"checkbox\" class=\"bfDisplayField bfFilterTriggerer\" id=\""+fields[i].id+"\" value=\""+fields[i].title+":::"+fields[i].name+"\" /></td>";
                                    html += "<td><label for=\""+fields[i].id+"\"><div class=\"bfAvailableField\">"+fields[i].title+"</div></label></td>";
                                    if(i+1%3 == 0){
                                        html += "</tr>";
                                    }
                                }
                            }
                            
                            html += "</table>";
                            html += "&nbsp;<br /><br /><br /><br />";

                            jQuery("#bfAvailableFields").html(html);
                            
                            if(form_selection > 0){
                                jQuery("#bfAvailableFieldsWrapper").css("display","block");
                            }
                            
                            jQuery("#bfAvailableFieldsOpen").off("click");
                            jQuery("#bfAvailableFieldsOpen").on("click",function(){
                                if(!fieldsOpen){
                                
                                    // hiding filter if fields is displayed
                                    jQuery("#bfSearch").css("display","none");
                                    jQuery("#bfSearchOpen").html(searchOrigText+" +");
                                    searchOpen = false;
                                    
                                    jQuery("#bfAvailableFields").css("display","block");
                                    fieldsOpen = true;
                                    jQuery("#bfAvailableFieldsOpen").html(openOrigText+" -");
                                }else{
                                    jQuery("#bfAvailableFields").css("display","none");
                                    fieldsOpen = false;
                                    jQuery("#bfAvailableFieldsOpen").html(openOrigText+" +");
                                }
                            });
                            
                            // updating filter from filter state
                            if(typeof filter_state.searchterm != "undefined"){
                                jQuery("#bfrecordsearch").val(filter_state.searchterm);
                            }else{
                                jQuery("#bfrecordsearch").val("");
                            }
                            if(typeof filter_state.searchintext != "undefined"){
                                jQuery("#bfrecordsearchintext").get(0).checked = filter_state.searchintext == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchintext").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinuserid != "undefined"){
                                jQuery("#bfrecordsearchinuserid").get(0).checked = filter_state.searchinuserid == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinuserid").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinusername != "undefined"){
                                jQuery("#bfrecordsearchinusername").get(0).checked = filter_state.searchinusername == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinusername").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinuserfullname != "undefined"){
                                jQuery("#bfrecordsearchinuserfullname").get(0).checked = filter_state.searchinuserfullname == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinuserfullname").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinid != "undefined"){
                                jQuery("#bfrecordsearchinid").get(0).checked = filter_state.searchinid == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinid").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinip != "undefined"){
                                jQuery("#bfrecordsearchinip").get(0).checked = filter_state.searchinip == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinip").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinviewed != "undefined"){
                                jQuery("#bfrecordsearchinviewed").get(0).checked = filter_state.searchinviewed == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinviewed").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinexported != "undefined"){
                                jQuery("#bfrecordsearchinexported").get(0).checked = filter_state.searchinexported == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinexported").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinarchived != "undefined"){
                                jQuery("#bfrecordsearchinarchived").get(0).checked = filter_state.searchinarchived == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinarchived").get(0).checked = false;
                            }
                            if(typeof filter_state.searchinpayment != "undefined"){
                                jQuery("#bfrecordsearchinpayment").get(0).checked = filter_state.searchinpayment == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinpayment").get(0).checked = false;
                            }
                            
                            if(typeof filter_state.bfrecordsearchinuntouched != "undefined"){
                                jQuery("#bfrecordsearchinuntouched").get(0).checked = filter_state.bfrecordsearchinuntouched == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinuntouched").get(0).checked = false;
                            }
                            
                            if(typeof filter_state.searchinopted != "undefined"){
                                jQuery("#bfrecordsearchinopted").get(0).checked = filter_state.searchinopted == "false" ? false : true;
                            }else{
                                jQuery("#bfrecordsearchinopted").get(0).checked = false;
                            }
                            
                            if(typeof filter_state.searchdatefrom != "undefined"){
                                jQuery("#bfrecordsearchdatefrom").val(filter_state.searchdatefrom);
                            }else{
                                jQuery("#bfrecordsearchdatefrom").val("");
                            }
                            if(typeof filter_state.searchtimefrom != "undefined"){
                                jQuery("#bfrecordsearchtimefrom").val(filter_state.searchtimefrom);
                            }else{
                                jQuery("#bfrecordsearchtimefrom").val("");
                            }
                            if(typeof filter_state.searchdateto != "undefined"){
                                jQuery("#bfrecordsearchdateto").val(filter_state.searchdateto);
                            }else{
                                jQuery("#bfrecordsearchdateto").val("");
                            }
                            if(typeof filter_state.searchtimeto != "undefined"){
                                jQuery("#bfrecordsearchtimeto").val(filter_state.searchtimeto);
                            }else{
                                jQuery("#bfrecordsearchtimeto").val("");
                            }

                            bfupdatetable();
                            
                            // general table update (filter, field selection, etc.)
                            jQuery(".bfFilterTriggerer").off("click");
                            jQuery(".bfFilterTriggerer").on("click",bfupdatetable);
                        }
                    });
                });
                
                // general table update (filter, field selection, etc.)
                jQuery(".bfFilterTriggerer").off("click");
                jQuery(".bfFilterTriggerer").on("click",bfupdatetable);

                function bfupdatetable(){

                    if(form_selection == 0){
                        jQuery("#bfrecordsearchintextspan").css("display","none");
                    } else{
                        jQuery("#bfrecordsearchintextspan").css("display","inline");
                    }

                    // applying filter rules if any

                    var searchterm = "";
                    
                    if(jQuery("#bfrecordsearch").val() != ""){
                        searchterm += "&searchterm="+encodeURIComponent(jQuery("#bfrecordsearch").val());
                    }
                    
                    // filter by checkbox results
                    
                    if(jQuery("#bfrecordsearchintext").get(0).checked){
                        searchterm += "&searchintext=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinuserid").get(0).checked){
                        searchterm += "&searchinuserid=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinusername").get(0).checked){
                        searchterm += "&searchinusername=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinuserfullname").get(0).checked){
                        searchterm += "&searchinuserfullname=1";
                    }

                    if(jQuery("#bfrecordsearchinid").get(0).checked){
                        searchterm += "&searchinid=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinip").get(0).checked){
                        searchterm += "&searchinip=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinviewed").get(0).checked){
                        searchterm += "&searchinviewed=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinexported").get(0).checked){
                        searchterm += "&searchinexported=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinarchived").get(0).checked){
                        searchterm += "&searchinarchived=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinuntouched").get(0).checked){
                        searchterm += "&searchinuntouched=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinpayment").get(0).checked){
                        searchterm += "&searchinpayment=1";
                    }
                    
                    if(jQuery("#bfrecordsearchinopted").get(0).checked){
                        searchterm += "&searchinopted=1";
                    }

                    // date / time from

                    var searchdatefrom = "";
                    var searchtimefrom = "";
                    if(jQuery("#bfrecordsearchdatefrom").val() != ""){
                        searchdatefrom = "&searchdatefrom="+encodeURIComponent(jQuery("#bfrecordsearchdatefrom").val());
                    }
                    if(jQuery("#bfrecordsearchtimefrom").val() != ""){
                        searchtimefrom = "&searchtimefrom="+encodeURIComponent(jQuery("#bfrecordsearchtimefrom").val());
                    }

                    // date / time to

                    var searchdateto = "";
                    var searchtimeto = "";
                    if(jQuery("#bfrecordsearchdateto").val() != ""){
                        searchdateto = "&searchdateto="+encodeURIComponent(jQuery("#bfrecordsearchdateto").val());
                    }
                    if(jQuery("#bfrecordsearchtimeto").val() != ""){
                        searchtimeto = "&searchtimeto="+encodeURIComponent(jQuery("#bfrecordsearchtimeto").val());
                    }

                    // signal the server to deliver records based on the field selection

                    custom_object["actions"]["listAction"]  = "index.php?option=com_breezingforms&act=recordmanagement&task=getListRecords&form_selection="+form_selection+searchterm+searchdatefrom+searchtimefrom+searchdateto+searchtimeto;
                    default_object["actions"]["listAction"] = "index.php?option=com_breezingforms&act=recordmanagement&task=getListRecords&form_selection="+form_selection+searchterm+searchdatefrom+searchtimefrom+searchdateto+searchtimeto;
                    //alert(default_object["actions"]["listAction"]);
                    // reset the fields

                    delete custom_object["fields"];
                    custom_object["fields"] = {};
                    
                    // assigning the selected fields to the visible tables

                    var hasChecked = false;
                    var hasID = false;
                    jQuery(".bfDisplayField").each(function(){
                        var field_title_name = jQuery(this).val().split(":::");
                        if(field_title_name.length == 2){
                            var field_name = field_title_name[1];
                            var field_title = field_title_name[0];
                            var pfx = field_name.indexOf("bfrecord_") == 0 ? "" : "bfrecord_custom_";
                            if(jQuery(this).get(0).checked){
                                
                                if(field_name == "bfrecord_id"){
                                    hasID = true;
                                }
                                var clazz = "ct-records-cell-normal";
                                if(field_name == "bfrecord_viewed" || field_name == "bfrecord_exported" || field_name == "bfrecord_archived"){
                                    clazz = "ct-records-cell-centered";
                                }
                                custom_object["fields"][pfx+field_name] = {
                                    title: jQuery("<div/>").text(field_title).html(),
                                    edit: bf_editable_record_field(pfx+field_name),
                                    key: field_name == "bfrecord_id" ? true : false,
                                    create: false,
                                    visibility: "visible",
                                    defaultValue: "",
                                    listClass: clazz,
                                    display: function(data){
                                        // apply htmlentities on all fields except those that include generated HTML from the server side
                                        if(typeof data.record["bfrecord_custom_element_type_"+field_name] != "undefined" && data.record["bfrecord_custom_element_type_"+field_name] != "File Upload" && data.record["bfrecord_custom_element_type_"+field_name] !== null){
                                            return jQuery("<div/>").text(data.record[pfx+field_name]).html().replace(/\\n/g,"<br />");
                                        }else if(field_name == "bfrecord_viewed" || field_name == "bfrecord_exported" || field_name == "bfrecord_archived"){
                                            var viewed = "<div id=\"bfFlag"+field_name+data.record["bfrecord_id"]+"\"><a href=\"javascript:bf_set_flag(\'"+field_name+"\',true,\'"+data.record["bfrecord_id"]+"\',\'bfFlag"+field_name+data.record["bfrecord_id"]+"\')\"><img src=\"components/com_breezingforms/images/icons/publish_x.png\" border=\"0\"></a></div>";
                                            if(data.record[pfx+field_name] == 1){
                                                viewed = "<div id=\"bfFlag"+field_name+data.record["bfrecord_id"]+"\"><a href=\"javascript:bf_set_flag(\'"+field_name+"\',false,\'"+data.record["bfrecord_id"]+"\',\'bfFlag"+field_name+data.record["bfrecord_id"]+"\')\"><img src=\"components/com_breezingforms/images/icons/publish_g.png\" border=\"0\"></a></div>";
                                            }
                                            return viewed;
                                        }else{
                                            return data.record[pfx+field_name] === null ? "" : data.record[pfx+field_name];
                                        }
                                    }
                                };
                                hasChecked = true;
                            }
                        }
                    });
                    
                    // restore the details button for custom views
                    custom_object["fields"]["bfrecord_details"] = default_object["fields"]["bfrecord_details"];

                    // make sure the KEY (bfrecord_id) is always included, even invisible, 
                    // such that things like deleting works

                    if(!hasID){
                        custom_object["fields"]["bfrecord_id"] = {
                            title: ' . json_encode(BFText::_('COM_BREEZINGFORMS_ID')) . ',
                            edit: false,
                            key: true,
                            create: false,
                            visibility: "hidden"
                        };
                    }

                    // re-paint the table. if no custom fields have been checked, 
                    // use the default setup

                    var theBfDisplayFieldsSelected = [];

                    jQuery(".bfDisplayField").each(
                        function(){
                            if(jQuery(this).get(0).checked){
                                theBfDisplayFieldsSelected.push(jQuery(this).attr("id"));
                            }
                        }
                    );

                    // store the current filter state only on successfully loaded data
                    jQuery.ajax({
                        type: "POST",
                        url: "index.php",
                        data: {
                            option: "com_breezingforms",
                            act: "recordmanagement",
                            task: "saveFilterState",
                            form_id: jQuery("#bfFormSelection").val(),
                            searchterm: jQuery("#bfrecordsearch").val(),
                            searchintext: jQuery("#bfrecordsearchintext").get(0).checked,
                            searchinuserid: jQuery("#bfrecordsearchinuserid").get(0).checked,
                            searchinusername: jQuery("#bfrecordsearchinusername").get(0).checked,
                            searchinuserfullname: jQuery("#bfrecordsearchinuserfullname").get(0).checked,
                            searchinid: jQuery("#bfrecordsearchinid").get(0).checked,
                            searchinip: jQuery("#bfrecordsearchinip").get(0).checked,
                            searchinviewed: jQuery("#bfrecordsearchinviewed").get(0).checked,
                            searchinexported: jQuery("#bfrecordsearchinexported").get(0).checked,
                            searchinarchived: jQuery("#bfrecordsearchinarchived").get(0).checked,
                            searchinuntouched: jQuery("#bfrecordsearchinuntouched").get(0).checked,
                            searchinpayment: jQuery("#bfrecordsearchinpayment").get(0).checked,
                            searchinopted: jQuery("#bfrecordsearchinopted").get(0).checked,
                            searchdatefrom: jQuery("#bfrecordsearchdatefrom").val(),
                            searchtimefrom: jQuery("#bfrecordsearchtimefrom").val(),
                            searchdateto: jQuery("#bfrecordsearchdateto").val(),
                            searchtimeto: jQuery("#bfrecordsearchtimeto").val(),
                            bfDisplayFieldIDbfrecord_id: typeof jQuery("#bfDisplayFieldIDbfrecord_id").get(0) != "undefined" ? jQuery("#bfDisplayFieldIDbfrecord_id").get(0).checked : false,
                            bfDisplayFieldIDbfrecord_submitted: typeof jQuery("#bfDisplayFieldIDbfrecord_submitted").get(0) != "undefined" ? jQuery("#bfDisplayFieldIDbfrecord_submitted").get(0).checked : false,
                            bfDisplayFieldIDbfrecord_user_id: typeof jQuery("#bfDisplayFieldIDbfrecord_user_id").get(0) != "undefined" ? jQuery("#bfDisplayFieldIDbfrecord_user_id").get(0).checked : false,
                            bfDisplayFieldIDbfrecord_username: typeof jQuery("#bfDisplayFieldIDbfrecord_username").get(0) != "undefined" ? jQuery("#bfDisplayFieldIDbfrecord_username").get(0).checked : false,
                            bfDisplayFieldIDbfrecord_user_full_name: typeof jQuery("#bfDisplayFieldIDbfrecord_user_full_name").get(0) != "undefined" ? jQuery("#bfDisplayFieldIDbfrecord_user_full_name").get(0).checked : false,
                            bfDisplayFieldIDbfrecord_ip: typeof jQuery("#bfDisplayFieldIDbfrecord_ip").get(0) != "undefined" ? jQuery("#bfDisplayFieldIDbfrecord_ip").get(0).checked : false,
                            bfDisplayFieldIDbfrecord_title: typeof jQuery("#bfDisplayFieldIDbfrecord_title").get(0) != "undefined" ? jQuery("#bfDisplayFieldIDbfrecord_title").get(0).checked : false,
                            bfDisplayFieldIDbfrecord_name: typeof jQuery("#bfDisplayFieldIDbfrecord_name").get(0) != "undefined" ? jQuery("#bfDisplayFieldIDbfrecord_name").get(0).checked : false,
                            bfDisplayFieldsSelected: theBfDisplayFieldsSelected
                         }
                    });
                    

                    jQuery("#bfRecordsTableContainer").jtable({});
                    jQuery("#bfRecordsTableContainer").jtable("destroy");
                    
                    if(hasChecked){
                        jQuery("#bfRecordsTableContainer").jtable(custom_object);
                    }else{
                        jQuery("#bfRecordsTableContainer").jtable(default_object);
                    }
                    
                    setTimeout(function(){
                        jQuery("#bfRecordsTableContainer").jtable("load");
                    }, 500);
                    
                }

                // initial load
                bfupdatetable();
            });');

        echo $this->getTableBar() . ' <div id="bfRecordsTableContainer"></div>';

    }

    function getListRecords() {

        @ob_end_clean();

        if (BFRequest::getInt('update', 0) == 1 && BFRequest::getInt('record_id', 0) > 0) {
            $db = BFFactory::getdbo();
            $record_id = BFRequest::getInt('record_id', 0);
            $form = BFRequest::getInt('form_selection', 0);
            $db = BFFactory::getdbo();

            $db->setQuery("Select * From #__facileforms_elements Where published = 1 And `name` <> 'bfFakeName' And `name` <> 'bfFakeName2' And `name` <> 'bfFakeName3' And `name` <> 'bfFakeName4' And `name` <> 'bfFakeName5' And  form = " . intval($form) . " Order By `ordering`");
            $elements = $db->loadAssocList();

            foreach ($elements As $element) {
                $value = BFRequest::getVar('bfrecord_custom_' . $element['name'], null, 'REQUEST', 'HTML', BFREQUEST_ALLOWHTML);

                if ($value !== null) {
                    if ($element['type'] == 'Checkbox' || $element['type'] == 'Checkbox Group' || $element['type'] == 'Select List') {
                        $db->setQuery("Select id From #__facileforms_subrecords Where `name` = " . $db->quote($element['name']) . " And record = " . $record_id . " Order By id");
                        $group_ids = $db->loadAssocList();
                        $values = explode(', ', $value);
                        $i = 0;
                        foreach ($group_ids As $group_id) {
                            if (isset($values[$i])) {
                                $db->setQuery("Update #__facileforms_subrecords Set value = " . $db->quote($values[$i]) . " Where id = " . $db->quote($group_id['id']));
                                $db->query();
                            }
                            $i++;
                        }
                    } else {
                        $db->setQuery("Update #__facileforms_subrecords Set value = " . $db->quote($value) . " Where name = " . $db->quote($element['name']) . " And record = " . $record_id);
                        $db->query();
                    }
                }
            }
        }

        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Pragma: no-cache'); // HTTP 1.0.
        header('Expires: 0');

        $db = BFFactory::getdbo();

        $order = explode(" ", str_replace("`", "", BFRequest::getVar('jtSorting', 'submitted Desc')));
        BFRequest::setVar('cbrecord_order_by', $order[0]);
        $order[0] = BFRequest::getCmd('cbrecord_order_by', 'submitted Desc');

        $searchterm = BFRequest::getVar('searchterm', '');

        // date search

        jimport('joomla.version');
        $version = new JVersion();
        $_version = $version->getShortVersion();
        $tz = 'UTC';
        if (version_compare($_version, '3.2', '>=')) {
            $tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
        }

        $now = JFactory::getDate();
        if (version_compare($_version, '3.2', '>=')) {
            $now = JFactory::getDate('now', $tz);
        }

        $now_date = '';

        if (version_compare($this->version, '3.0', '>=')) {
            $now_date = $now->toSql();
        } else {
            $now_date = $now->toMySQL();
        }

        // from date / time

        $searchdatefrom = BFRequest::getVar('searchdatefrom', '');
        $searchtimefrom = BFRequest::getVar('searchtimefrom', '');

        if (version_compare($_version, '3.2', '>=')) {
            $tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

            if ($searchdatefrom != '') {
                $searchdatefrom = JFactory::getDate($searchdatefrom, $tz);
                $searchdatefrom = $searchdatefrom->format('Y-m-d', true);
            }

            if ($searchtimefrom) {
                $searchtimefrom = JFactory::getDate($searchtimefrom, $tz);
                $searchtimefrom = $searchtimefrom->format('H:i:s', true);
            }

            $now_date = $now->format('Y-m-d', true);
        } else {
            if ($searchtimefrom != '') {
                $searchtimefrom = date('H:i:s', strtotime('1970-01-01 ' . $searchtimefrom));
            }

            if ($searchdatefrom != '') {
                $searchdatefrom = date('Y-m-d', strtotime($searchdatefrom));
            }
        }

        if ($searchdatefrom == '' && $searchtimefrom != '') {
            $searchdatefrom = $now_date . ' ' . $searchtimefrom;
        } else if ($searchdatefrom != '' && $searchtimefrom != '') {
            $searchdatefrom = $searchdatefrom . ' ' . $searchtimefrom;
        } else if ($searchdatefrom != '' && $searchtimefrom == '') {
            $searchdatefrom = $searchdatefrom . ' 00:00:00';
        }

        // to date / time

        $searchdateto = BFRequest::getVar('searchdateto', '');
        $searchtimeto = BFRequest::getVar('searchtimeto', '');

        if (version_compare($_version, '3.2', '>=')) {
            $tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

            if ($searchdateto != '') {
                $searchdateto = JFactory::getDate($searchdateto, $tz);
                $searchdateto = $searchdateto->format('Y-m-d', true);
            }

            if ($searchtimeto != '') {
                $searchtimeto = JFactory::getDate($searchtimeto, $tz);
                $searchtimeto = $searchtimeto->format('H:i:s', true);
            }

            $now_date = $now->format('Y-m-d', true);
        } else {
            if ($searchtimeto != '') {
                $searchtimeto = date('H:i:s', strtotime('1970-01-01 ' . $searchtimeto));
            }
            if ($searchdateto != '') {
                $searchdateto = date('Y-m-d', strtotime($searchdateto));
            }
        }

        if ($searchdateto == '' && $searchtimeto != '') {
            $searchdateto = $now_date . ' ' . $searchtimeto;
        } else if ($searchdateto != '' && $searchtimeto != '') {
            $searchdateto = $searchdateto . ' ' . $searchtimeto;
        } else if ($searchdateto != '' && $searchtimeto == '') {
            $searchdateto = $searchdateto . ' 23:59:59';
        }

        $db->setQuery("SET SESSION group_concat_max_len = 9999999");
        $db->query();

        $db->setQuery("Select * From #__facileforms_elements Where published = 1 And `name` <> 'bfFakeName' And `name` <> 'bfFakeName2' And `name` <> 'bfFakeName3' And `name` <> 'bfFakeName4' And `name` <> 'bfFakeName5' And  form = " . BFRequest::getInt('form_selection', 0) . " Order By `ordering`");
        $elements = $db->loadAssocList();

        $selectors = '';

        $x = 0;
        $elements_size = count($elements);
        foreach ($elements As $element) {

            if ($element['type'] == 'Checkbox' || $element['type'] == 'Checkbox Group' || $element['type'] == 'Select List') {
                $selectors .= "Trim( Both ', ' From GROUP_CONCAT( ( Case When subrecords.`name` = '{$element['name']}' Then subrecords.`value` Else '' End ) Order By subrecords.`id` SEPARATOR ', ' ) ) As `bfrecord_custom_{$element['name']}` ";
            } else {
                $selectors .= " max( case when subrecords.`element` = '{$element['id']}' then subrecords.`value` end ) As `bfrecord_custom_{$element['name']}` ";
            }

            $selectors .= ", ";

            $selectors .= " max( case when subrecords.`element` = '{$element['id']}' then subrecords.`element` end ) As `bfrecord_custom_element_id_{$element['name']}` ";

            $selectors .= ", ";

            $selectors .= " max( case when subrecords.`type` = " . $db->quote($element['type']) . " And subrecords.`name` = " . $db->quote($element['name']) . " then subrecords.`type` end ) As `bfrecord_custom_element_type_{$element['name']}` ";

            $selectors .= ", ";

            $selectors .= " max( case when subrecords.`title` = " . $db->quote($element['title']) . " And subrecords.`name` = " . $db->quote($element['name']) . " then subrecords.`title` end ) As `bfrecord_custom_element_title_{$element['name']}` ";

            $selectors .= ", ";

            $x++;
        }

        $the_search_term = '';
        $the_having_term = '';

        if (BFRequest::getBool('searchintext', false) ||
            BFRequest::getBool('searchinuserid', false) ||
            BFRequest::getBool('searchinusername', false) ||
            BFRequest::getBool('searchinuserfullname', false) ||
            BFRequest::getBool('searchinid', false) ||
            BFRequest::getBool('searchinip', false) ||
            BFRequest::getBool('searchinviewed', false) ||
            BFRequest::getBool('searchinexported', false) ||
            BFRequest::getBool('searchinarchived', false) ||
	        BFRequest::getBool('searchinuntouched', false) ||
            BFRequest::getBool('searchinpayment', false) ||
            BFRequest::getBool('searchinopted', false)
        ) {

            foreach ($elements As $element) {
                $the_having_term .= $searchterm && BFRequest::getBool('searchintext', false) ? " `bfrecord_custom_{$element['name']}` Like " . $db->quote('%' . $searchterm . '%') . " Or " : '';
            }

            $the_search_term .= $searchterm && BFRequest::getBool('searchinid', false) ? " records.`id` = " . $db->quote($searchterm) . " Or " : '';
            $the_search_term .= $searchterm && BFRequest::getBool('searchinip', false) ? " records.`ip` = " . $db->quote($searchterm) . " Or " : '';
            $the_search_term .= $searchterm && BFRequest::getBool('searchinuserid', false) ? " records.`user_id` = " . $db->quote($searchterm) . " Or " : '';
            $the_search_term .= $searchterm && BFRequest::getBool('searchinusername', false) ? " records.`username` Like " . $db->quote('%' . $searchterm . '%') . " Or " : '';
            $the_search_term .= $searchterm && BFRequest::getBool('searchinuserfullname', false) ? " records.`user_full_name` Like " . $db->quote('%' . $searchterm . '%') . " Or " : '';
            $the_search_term .= BFRequest::getBool('searchinviewed', false) ? " records.`viewed` = 1 Or " : '';
            $the_search_term .= BFRequest::getBool('searchinexported', false) ? " records.`exported` = 1 Or " : '';
            $the_search_term .= BFRequest::getBool('searchinarchived', false) ? " records.`archived` = 1 Or " : '';
	        $the_search_term .= BFRequest::getBool('searchinuntouched', false) ? " ( records.`archived` = 0 And records.`archived` = 0 And records.`viewed` = 0 ) Or " : '';
            $the_search_term .= BFRequest::getBool('searchinopted', false) ? " records.`opted` = 1 Or " : '';
            if ($searchterm && BFRequest::getBool('searchinpayment', false)) {
                $the_search_term .= " records.`paypal_tx_id` Like " . $db->quote('%' . $searchterm . '%') . " Or ";
                $the_search_term .= " records.`paypal_payment_date` Like " . $db->quote('%' . $searchterm . '%') . " Or ";
                $the_search_term .= " records.`paypal_testaccount` = " . $db->quote($searchterm) . " Or ";
                $the_search_term .= " records.`paypal_download_tries` = " . $db->quote($searchterm) . " Or ";
            }
            $the_search_term = substr($the_search_term, 0, -3);
            $the_having_term = substr($the_having_term, 0, -3);
        }

        if ($the_search_term) {
            $the_search_term = ' And ( ' . $the_search_term . ' ) ';
        }

        if ($the_having_term) {
            $the_having_term = ' Having ( ' . $the_having_term . ' ) ';
        }


        if ($searchdatefrom != '' && version_compare($this->version, '3.2', '>=')) {
            $date_ = JFactory::getDate($searchdatefrom, $this->tz);
            $searchdatefrom = $date_->format('Y-m-d H:i:s');
        }

        if ($searchdateto != '' && version_compare($this->version, '3.2', '>=')) {
            $date_ = JFactory::getDate($searchdateto, $this->tz);
            $searchdateto = $date_->format('Y-m-d H:i:s');
        }

        //$now__ = JFactory::getDate('now', $this->tz);
        //echo $now__->format('Y-m-d H:i:s', true);
        //echo $searchdate;

        $db->setQuery(
            "   Select SQL_CALC_FOUND_ROWS "
            . $selectors
            . " records.user_id As bfrecord_user_id, "
            . " records.username As bfrecord_username, "
            . " records.user_full_name As bfrecord_user_full_name, "
            . " records.id As bfrecord_id, "
            . " records.submitted As bfrecord_submitted, "
            . " records.ip As bfrecord_ip, "
            . " records.opsys As bfrecord_opsys, "
            . " records.browser As bfrecord_browser, "
            . " records.viewed As bfrecord_viewed, "
            . " records.exported As bfrecord_exported, "
            . " records.paypal_tx_id As bfrecord_payment_tx_id, "
            . " records.paypal_payment_date As bfrecord_payment_date, "
            . " records.paypal_testaccount As bfrecord_payment_test, "
            . " records.paypal_download_tries As bfrecord_payment_download_tries, "
            . " records.archived As bfrecord_archived, "
            . " records.opted As bfrecord_opted, "
            . " forms.title As bfrecord_title, "
            . " forms.name As bfrecord_name, "
            . " forms.id As bfrecord_form_id "
            . " From  "
            . " #__facileforms_forms As forms, "
            . " #__facileforms_records As records, "
            . " #__facileforms_subrecords As subrecords "
            . " Where "
            . " records.id = subrecords.record "
            . " And "
            . " forms.id = records.form "
            . ( $searchdatefrom ? " And records.submitted >= " . $db->quote($searchdatefrom) . " " : '' )
            . ( $searchdateto ? " And records.submitted <= " . $db->quote($searchdateto) . " " : '' )
            . $the_search_term
            . ( BFRequest::getInt('record_id', 0) > 0 ? " And records.id = " . BFRequest::getInt('record_id', 0) : "" )
            . ( BFRequest::getInt('form_selection', 0) > 0 ? ' And records.form = ' . BFRequest::getInt('form_selection', 0) : '' )
            . " Group By subrecords.record "
            . $the_having_term
            . " Order By `" . $order[0] . "` " . ( isset($order[1]) && strtolower($order[1]) == 'asc' ? 'Asc' : 'Desc' )
            . " Limit " . BFRequest::getInt('jtStartIndex', 0) . ", " . BFRequest::getInt('jtPageSize', 10)
        );

        //echo $db->getQuery();

        $result = array();
        $result['Result'] = 'OK';

        try {
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');

            $result['Records'] = $db->loadAssocList();
            $i = 0;

            foreach ($result['Records'] As $rec_key => $record) {
                $name = '';
                foreach ($record As $key => $val) {
                    $name = explode('bfrecord_custom_element_id_', $key);
                    if (isset($name[1])) {
                        $name = $name[1];
                        //echo $result['Records'][$rec_key]['bfrecord_custom_' . $name];
                        //$result['Records'][$rec_key]['bfrecord_custom_' . $name] = $this->removeDangerousHtml($record['bfrecord_custom_' . $name]);

                        BFRequest::setVar('san_bfrecord_custom', $result['Records'][$rec_key]['bfrecord_custom_' . $name]);
                        $result['Records'][$rec_key]['bfrecord_custom_' . $name] = BFRequest::getVar('san_bfrecord_custom', '', '', 'HTML', BFREQUEST_ALLOWHTML);

                        if ($record['bfrecord_custom_element_type_' . $name] == 'File Upload' && trim($record['bfrecord_custom_' . $name])) {
                            $out = '';
                            $out .= '<div style="white-space: nowrap; overflow: auto; max-height: 300px; width: 100%;">';
                            $files = explode("\n", str_replace("\r", "", $record['bfrecord_custom_' . $name]));
                            $fileIdx = 0;
                            foreach ($files As $file) {

                                if (strpos(strtolower($file), '{cbsite}') === 0) {
                                    $file = str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), $file);
                                }

                                if (strpos(strtolower($file), '{site}') === 0) {
                                    $file = str_replace(array('{site}', '{site}'), array(JPATH_SITE, JPATH_SITE), $file);
                                }

                                if (!JFile::exists($file)) {
                                    $out .= 'file not found on server:<br/>' . basename($file) . '<br/>';
                                } else {
                                    $out .= $this->renderFile($file, $record['bfrecord_id'], $record['bfrecord_custom_element_id_' . $name], $fileIdx);
                                }
                                $out .= '<br/>';
                                $fileIdx++;
                            }
                            $out .= '</div>';
                            $result['Records'][$i]['bfrecord_custom_file_upload_raw_' . $name] = $result['Records'][$i]['bfrecord_custom_' . $name];
                            $result['Records'][$i]['bfrecord_custom_' . $name] = $out;
                        }
                        else
                            if ($record['bfrecord_custom_element_type_' . $name] == 'Signature' && trim($record['bfrecord_custom_' . $name])) {
                                $out = '';
                                $out .= '<div style="white-space: nowrap; overflow: auto; max-height: 300px; width: 100%;">';
                                $file = trim($record['bfrecord_custom_' . $name]);
                                $fileIdx = 0;

                                $file = JPATH_SITE . '/media/breezingforms/signatures/' . $file;

                                if (!JFile::exists($file)) {
                                    $out .= 'file not found on server:<br/>' . basename($file) . '<br/>';
                                } else {
                                    $out .= $this->renderFile($file, $record['bfrecord_id'], $record['bfrecord_custom_element_id_' . $name], $fileIdx);
                                }

                                $out .= '</div>';
                                $result['Records'][$i]['bfrecord_custom_file_upload_raw_' . $name] = $result['Records'][$i]['bfrecord_custom_' . $name];
                                $result['Records'][$i]['bfrecord_custom_' . $name] = $out;
                            }
                    }
                }
                if (version_compare($this->version, '3.2', '>=')) {
                    $date_ = JFactory::getDate($result['Records'][$i]['bfrecord_submitted'], $this->tz);
                    $offset = $date_->getOffsetFromGMT();
                    if ($offset > 0) {
                        $date_->add(new DateInterval('PT' . $offset . 'S'));
                    } else if ($offset < 0) {
                        $offset = $offset * -1;
                        $date_->sub(new DateInterval('PT' . $offset . 'S'));
                    }
                    $result['Records'][$i]['bfrecord_submitted'] = $date_->format('Y-m-d H:i:s', true);
                }
                $i++;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }

        $db->setQuery("SELECT FOUND_ROWS();");
        $record_count = $db->loadResult();
        $result['TotalRecordCount'] = intval($record_count);

        if (BFRequest::getInt('record_id', 0) > 0) {
            $db->setQuery("Update #__facileforms_records Set viewed = 1 Where id = " . BFRequest::getInt('record_id', 0));
            $db->query();
        }

        echo json_encode($result);

        exit;
    }

    function deleteRecord() {

        @ob_end_clean();

        $db = BFFactory::getdbo();

        // CONTENTBUILDER
        $isContentBuilder = false;
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        jimport('joomla.database.table');

        if (JFile::exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php')) {
            $isContentBuilder = true;
        }

        $is15 = true;
        if (version_compare($this->version, '1.6', '>=')) {
            $is15 = false;
        }

        if ($isContentBuilder) {
            $db->setQuery("Select `form`.id As form_id, `form`.reference_id, `form`.delete_articles From #__facileforms_records As r, #__contentbuilder_forms As form Where form.reference_id = r.form And r.id =  " . $db->Quote(BFRequest::getInt('bfrecord_id')));
            $cbRecords = $db->loadAssocList();
            foreach ($cbRecords As $cbRecord) {
                $db->setQuery("Delete From #__contentbuilder_list_records Where form_id = " . intval($cbRecord['form_id']) . " And record_id = " . $db->Quote(BFRequest::getInt('bfrecord_id')));
                $db->query();
                $db->setQuery("Delete From #__contentbuilder_records Where `type` = 'com_breezingforms' And `reference_id` = " . $db->Quote($cbRecord['reference_id']) . " And record_id = " . $db->Quote(BFRequest::getInt('bfrecord_id')));
                $db->query();
                if ($cbRecord['delete_articles']) {
                    $db->setQuery("Select article_id From #__contentbuilder_articles Where form_id = " . intval($cbRecord['form_id']) . " And record_id = " . $db->Quote(BFRequest::getInt('bfrecord_id')));
	                $articles = $db->loadColumn();
                    if (count($articles)) {
                        $article_items = array();
                        foreach ($articles As $article) {
                            $article_items[] = $db->Quote('com_content.article.' . $article);

                            $table = JTable::getInstance('content');

                            // Trigger the onContentBeforeDelete event.
                            if ($table->load($article)) {
                                Factory::getApplication()->triggerEvent('onContentBeforeDelete', array('com_content.article', $table));
                            }

                            $db->setQuery("Delete From #__content Where id = " . intval($article));
                            $db->query();
                            // Trigger the onContentAfterDelete event.
                            $table->reset();

                            Factory::getApplication()->triggerEvent('onContentAfterDelete', array('com_content.article', $table));

                        }
                        $db->setQuery("Delete From #__assets Where `name` In (" . implode(',', $article_items) . ")");
                        $db->query();
                    }
                }

                $db->setQuery("Delete From #__contentbuilder_articles Where form_id = " . intval($cbRecord['form_id']) . " And record_id = " . $db->Quote(BFRequest::getInt('bfrecord_id')));
                $db->query();
            }
        }
        // CONTENTBUILDER END

        $db->setQuery("Delete From #__facileforms_records Where id = " . BFRequest::getInt('bfrecord_id'));
        $db->query();

        $db->setQuery("Delete From #__facileforms_subrecords Where record = " . BFRequest::getInt('bfrecord_id'));
        $db->query();

        $result = array();
        $result['Result'] = 'OK';

        echo json_encode($result);

        exit;
    }

    function renderFile($file, $record_id, $element_id, $file_index) {
        if (BFRequest::getVar('renderFile', '') != '' && md5(basename($file) . $record_id . $element_id . $file_index) == BFRequest::getVar('renderFile', '')) {
            @ob_end_clean();
            $this->resizeFile($file, 300, 300, '#ffffff', 'simple');
            exit;
        }
        if (BFRequest::getVar('downloadFile', '') != '' && md5(basename($file) . $record_id . $element_id . $file_index) == BFRequest::getVar('downloadFile', '')) {
            @ob_end_clean();
            $this->downloadFile($file);
            exit;
        }
        $image = @getimagesize($file);
        if ($image !== false) {
            return '<a href="' . Uri::getInstance()->toString() . '&downloadFile=' . md5(basename($file) . $record_id . $element_id . $file_index) . '"><img src="' . Uri::getInstance()->toString() . '&renderFile=' . md5(basename($file) . $record_id . $element_id . $file_index) . '" border=\"0\"/></a><br/>';
        } else {
            return '<a href="' . Uri::getInstance()->toString() . '&downloadFile=' . md5(basename($file) . $record_id . $element_id . $file_index) . '">' . basename($file) . '</a><br />';
        }
    }

    public function downloadFile($filename) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        header('Content-Length: ' . @filesize($filename));
        $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
        $buffer = '';
        $handle = @fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!@feof($handle)) {
            $buffer = @fread($handle, $chunksize);
            print $buffer;
        }
        return @fclose($handle);
    }

    public function exifImageType($filename) {
        // some hosting providers think it is a good idea not to compile in exif with php...
        if (!function_exists('exif_imagetype')) {
            if (( list($width, $height, $type, $attr) = getimagesize($filename) ) !== false) {
                return $type;
            }
            return false;
        } else {
            return exif_imagetype($filename);
        }
    }

    public function resizeFile($path, $width, $height, $bgcolor = '#ffffff', $type = '') {

        $image = @getimagesize($path);

        if ($image !== false) {

            /*
            if ($image[0] > 16384) {
                return;
            }

            if ($image[1] > 16384) {
                return;
            }*/

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
            $memoryNeeded = round(( $image[0] * $image[1] * $image['bits'] * ($channels / 8) + $K64
                ) * $TWEAKFACTOR
            );

            $ini = 8 * $MB;
            if (ini_get('memory_limit') !== false) {
                $ini = $this->returnBytes(ini_get('memory_limit'));
            }
            $memoryLimit = $ini;
            if (function_exists('memory_get_usage') &&
                memory_get_usage() + $memoryNeeded > $memoryLimit) {
                $memory = false;
            }
            if ($memory) {
                switch ($exif_type) {
                    case IMAGETYPE_JPEG2000 :
                    case IMAGETYPE_JPEG :
                        $resource = @imagecreatefromjpeg($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ( $type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagejpeg($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                if ($exif_type == IMAGETYPE_JPEG2000) {
                                    header('Content-Type: ' . @image_type_to_mime_type(IMAGETYPE_JPEG2000));
                                } else {
                                    header('Content-Type: ' . @image_type_to_mime_type(IMAGETYPE_JPEG));
                                }
                                header('Content-Disposition: inline; filename="' . basename($path) . '"');
                                echo $buffer;
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                    case IMAGETYPE_GIF :
                        $resource = @imagecreatefromgif($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ( $type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagegif($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                header('Content-Type: ' . @image_type_to_mime_type(IMAGETYPE_GIF));
                                header('Content-Disposition: inline; filename="' . basename($path) . '"');
                                echo $buffer;
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                    case IMAGETYPE_PNG :
                        $resource = @imagecreatefrompng($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ( $type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagepng($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                header('Content-Type: ' . @image_type_to_mime_type(IMAGETYPE_PNG));
                                header('Content-Disposition: inline; filename="' . basename($path) . '"');
                                echo $buffer;
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                }
            }
        }
    }

    public function resize_image($source_image, $destination_width, $destination_height, $type = 0, $bgcolor = array(0, 0, 0)) {
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

    public function returnBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = str_replace($val[strlen($val) - 1],'',$val);
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

    function exportPdf() {
        global $ff_compath;

        $db = BFFactory::getdbo();

        $ids = BFRequest::getVar('cid', array());
        ArrayHelper::toInteger($ids);

        $file = JPATH_SITE . '/media/breezingforms/pdftpl/export_custom_pdf.php';
        if (!JFile::exists($file)) {
            $file = JPATH_SITE . '/media/breezingforms/pdftpl/export_pdf.php';
        }

        $form_name = '';

        if (isset($ids[0])) {
            $ids = implode(',', $ids);
            $db->setQuery(
                "select * from #__facileforms_records where id in ($ids) order by submitted Desc"
            );
        } else if (BFRequest::getInt('form_selection', 0)) {
            $db->setQuery(
                "select * from #__facileforms_records where form = " . $db->Quote(BFRequest::getInt('form_selection', 0)) . " order by submitted Desc"
            );
        } else {
            $db->setQuery(
                "select * from #__facileforms_records order by submitted Desc"
            );
        }
        $recs = $db->loadObjectList();

        if (BFRequest::getInt('form_selection', 0) && count($recs)) {

            $form_name = $recs[0]->name;
        }

        if($form_name != ''){

            $file2 = JPATH_SITE . '/media/breezingforms/pdftpl/'.$form_name.'_export_pdf.php';
            if (JFile::exists($file2)) {
                $file = JPATH_SITE . '/media/breezingforms/pdftpl/'.$form_name.'_export_pdf.php';
            }
        }

        $updIds = array();

        $i = 0;
        foreach ($recs As $rec) {
            $updIds[] = $rec->id;
            if (version_compare($this->version, '3.2', '>=')) {
                $date_ = JFactory::getDate($rec->submitted, $this->tz);
                $offset = $date_->getOffsetFromGMT();
                if ($offset > 0) {
                    $date_->add(new DateInterval('PT' . $offset . 'S'));
                } else if ($offset < 0) {
                    $offset = $offset * -1;
                    $date_->sub(new DateInterval('PT' . $offset . 'S'));
                }
                $recs[$i]->submitted = $date_->format('Y-m-d H:i:s', true);
            }
            $i++;
        }

        if (isset($updIds[0])) {
            $updIds = implode(',', $updIds);
            $db->setQuery(
                "update #__facileforms_records set exported=1 where id in ($updIds)"
            );
            $db->query();
        }

        if(!class_exists('BFPDF')){

            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFPDF.php');
        }

        jimport('joomla.version');
        $version = new JVersion();
        $_version = $version->getShortVersion();
        $tz = 'UTC';
        if (version_compare($_version, '3.2', '>=')) {
            $tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
        }

        $date_stamp = date('YmdHis');
        if (version_compare($_version, '3.2', '>=')) {
            $date_ = JFactory::getDate('now', $tz);
            $offset = $date_->getOffsetFromGMT();
            if ($offset > 0) {
                $date_->add(new DateInterval('PT' . $offset . 'S'));
            } else if ($offset < 0) {
                $offset = $offset * -1;
                $date_->sub(new DateInterval('PT' . $offset . 'S'));
            }
            $date_stamp = $date_->format('YmdHis', true);
        }

        $pdf = new BFPDF();
        $pdf->setFormName($form_name);
        $pdf->setWhich('export');

        @ob_end_clean();
        ob_start();
        require_once($file);
        $c = ob_get_contents();
        ob_end_clean();

        $active_found = false;
        $font_loaded = false;
        $ttf_name = '';

        if (JFolder::exists(JPATH_SITE . '/media/breezingforms/pdftpl/fonts/')) {

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
                            if( $ttf_name != '' ){
                                $font_name = $ttf_name;
                            }else{
                                $font_name = implode('_',$active);
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

        if($pdf->getFooterTemplate() == '') {

            $pdf->setPrintFooter(false);

        }else{

            $pdf->setPrintFooter(true);
        }

        if($pdf->getHeaderTemplate() == '') {

            $pdf->setPrintHeader(false);

        }else{

            $pdf->setPrintHeader(true);
        }

        $pdf->AddPage();
        $pdf->writeHTML($c);
        $pdfname = ( $form_name != '' ? $form_name . '-' : '' ).'ffexport-pdf-' . $date_stamp . '.pdf';
        $pdf->lastPage();
        $pdf->Output($pdfname, "D");
        exit;
    }

    function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    function getSubrecords($recordId) {
        $db = BFFactory::getdbo();
        $db->setQuery(
            "select Distinct subs.* from #__facileforms_subrecords As subs, #__facileforms_elements as els where els.id=subs.element And subs.record = " . intval($recordId) . " order by els.ordering"
        );
        return $db->loadObjectList();
    }

    function exportCsv() {
        // echo '<pre>';
        global $ff_config;

        $inverted = isset($ff_config->csvinverted) ? $ff_config->csvinverted : false;

        $db = BFFactory::getdbo();

        $ids = BFRequest::getVar('cid', array());
        ArrayHelper::toInteger($ids);

        $csvdelimiter = stripslashes($ff_config->csvdelimiter);
        $csvquote = stripslashes($ff_config->csvquote);
        $cellnewline = $ff_config->cellnewline == 0 ? "\n" : "\\n";

        $form_name = '';

        $fields = array();
        $lines = array();
        $element_fields = array();
        $updIds = array();

        if (isset($ids[0])) {
            $ids = implode(',', $ids);
            $db->setQuery(
                "select * from #__facileforms_records where id in ($ids) order by submitted Desc"
            );
            $recs = $db->loadObjectList();

            if (BFRequest::getInt('form_selection', 0) && count($recs)) {

                $form_name = $recs[0]->name;
            }

            $forms = array();
            foreach ($recs As $rec) {
                $forms[] = $rec->form;
            }

            $db->setQuery(
                "select Distinct * from #__facileforms_elements where form In (" . implode(',', $forms) . ")  And published = 1 And `name` <> 'bfFakeName' And `name` <> 'bfFakeName2' And `name` <> 'bfFakeName3' And `name` <> 'bfFakeName4' And `name` <> 'bfFakeName5' order by ordering"
            );

            $element_fields = $db->loadObjectList();
        } else if (BFRequest::getInt('form_selection', 0)) {
            $db->setQuery(
                "select * from #__facileforms_records where form = " . $db->Quote(BFRequest::getInt('form_selection', 0)) . " order by submitted Desc"
            );
            $recs = $db->loadObjectList();

            if(count($recs)){
                $form_name = $recs[0]->name;
            }

            $db->setQuery(
                "select Distinct * from #__facileforms_elements where form = " . $db->Quote(BFRequest::getInt('form_selection', 0)) . " And published = 1 And `name` <> 'bfFakeName' And `name` <> 'bfFakeName2' And `name` <> 'bfFakeName3' And `name` <> 'bfFakeName4' And `name` <> 'bfFakeName5' order by ordering"
            );
            $element_fields = $db->loadObjectList();
        } else {
            $db->setQuery(
                "select * from #__facileforms_records order by submitted Desc"
            );
            $recs = $db->loadObjectList();

            $db->setQuery(
                "select Distinct * from #__facileforms_elements Where published = 1 And `name` <> 'bfFakeName' And `name` <> 'bfFakeName2' And `name` <> 'bfFakeName3' And `name` <> 'bfFakeName4' And `name` <> 'bfFakeName5'"
            );
            $element_fields = $db->loadObjectList();
        }

        $fields['ID'] = true;
        $fields['SUBMITTED'] = true;
        $fields['USER_ID'] = true;
        $fields['USERNAME'] = true;
        $fields['USER_FULL_NAME'] = true;
        $fields['TITLE'] = true;
        $fields['IP'] = true;
        $fields['BROWSER'] = true;
        $fields['OPSYS'] = true;
        $fields['TRANSACTION_ID'] = true;
        $fields['DATE'] = true;
        $fields['TEST_ACCOUNT'] = true;
        $fields['DOWNLOAD_TRIES'] = true;
        $fields['DOUBLE_OPT_IN'] = true;

        $head_keys = array();

        foreach ($element_fields As $element_field) {
            if (!isset($fields[strip_tags($element_field->name)])) {
                $field_key = md5(strip_tags($element_field->name));
                $fields[$field_key] = true;
                $head_keys[$field_key] = strip_tags($element_field->name);
            }
        }

        $recsSize = count($recs);
        for ($r = 0; $r < $recsSize; $r++) {

            $rec = $recs[$r];



            if (version_compare($this->version, '3.2', '>=')) {
                $date_ = JFactory::getDate($rec->submitted, $this->tz);
                $offset = $date_->getOffsetFromGMT();
                if ($offset > 0) {
                    $date_->add(new DateInterval('PT' . $offset . 'S'));
                } else if ($offset < 0) {
                    $offset = $offset * -1;
                    $date_->sub(new DateInterval('PT' . $offset . 'S'));
                }
                $rec->submitted = $date_->format('Y-m-d H:i:s', true);
            }

            $updIds[] = $rec->id;

            $lineNum = count($lines);

            $lines[$lineNum]['ID'][] = $rec->id;
            $lines[$lineNum]['SUBMITTED'][] = $rec->submitted;
            $lines[$lineNum]['USER_ID'][] = $rec->user_id;
            $lines[$lineNum]['USERNAME'][] = $rec->username;
            $lines[$lineNum]['USER_FULL_NAME'][] = $rec->user_full_name;
            $lines[$lineNum]['TITLE'][] = $rec->title;
            $lines[$lineNum]['IP'][] = $rec->ip;
            $lines[$lineNum]['BROWSER'][] = $rec->browser;
            $lines[$lineNum]['OPSYS'][] = $rec->opsys;
            $lines[$lineNum]['TRANSACTION_ID'][] = $rec->paypal_tx_id;
            $lines[$lineNum]['DATE'][] = $rec->paypal_payment_date;
            $lines[$lineNum]['TEST_ACCOUNT'][] = $rec->paypal_testaccount;
            $lines[$lineNum]['DOWNLOAD_TRIES'][] = $rec->paypal_download_tries;
            $lines[$lineNum]['DOUBLE_OPT_IN'][] = $rec->opted;

            foreach ($fields As $fieldName => $null) {
                switch ($fieldName) {
                    case 'ID':
                    case 'SUBMITTED':
                    case 'USER_ID':
                    case 'USERNAME':
                    case 'USER_FULL_NAME':
                    case 'TITLE':
                    case 'IP':
                    case 'BROWSER':
                    case 'OPSYS':
                    case 'TRANSACTION_ID':
                    case 'DATE':
                    case 'TEST_ACCOUNT':
                    case 'DOWNLOAD_TRIES':
                    case 'DOUBLE_OPT_IN':
                        break;
                    default:
                        $lines[$lineNum][$fieldName] = array();
                }
            }

            $rec = $recs[$r];
            $db->setQuery(
                "select Distinct s.*, e.title As title from #__facileforms_subrecords As s, #__facileforms_elements As e where e.id = s.element And s.record = $rec->id Order By e.ordering"
            );

            $subs = $db->loadObjectList();


            $subsSize = count($subs);
            for ($s = 0; $s < $subsSize; $s++) {
                $sub = $subs[$s];
                if ($sub->name != 'bfFakeName' && $sub->name != 'bfFakeName2' && $sub->name != 'bfFakeName3' && $sub->name != 'bfFakeName4') {
                    if (!isset($fields[ md5( strip_tags( $sub->name ) ) ])) {
                        $fields[ md5( strip_tags( $sub->name ) ) ] = true;
                    }
                    if ($sub->type == 'File Upload' && strpos(strtolower($sub->value), '{cbsite}') === 0) {
                        $out = '';
                        $nl = '';
                        $_values = explode("\n", str_replace("\r", '', $sub->value));
                        $length = count($_values);
                        $i = 0;
                        foreach ($_values As $_value) {
                            if ($i + 1 < $length) {
                                $nl = "\n";
                            } else {
                                $nl = '';
                            }
                            $out .= str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), $_value) . $nl;
                            $i++;
                        }
                        $sub->value = $out;
                    }
                    $lines[$lineNum][md5( strip_tags( $sub->name ) )][] = $sub->value;
                }
            }
        }

        $head = '';
        //ksort($fields);

        $lineLength = count($lines);
        foreach ($fields As $fieldName => $null) {
            if ($inverted == false) {

                $head .= $csvquote . (isset($head_keys[$fieldName]) ? $head_keys[$fieldName] : $fieldName) . $csvquote . $csvdelimiter;
            }

            for ($i = 0; $i < $lineLength; $i++) {
                if (!isset($lines[$i][$fieldName])) {
                    $lines[$i][$fieldName] = array();
                }
            }
        }

        $head = substr($head, 0, strlen($head) - 1) . nl();

        $out = '';
        for ($i = 0; $i < $lineLength; $i++) {
            foreach ($lines[$i] As $fieldName => $line) {

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

            if ($inverted == true) {
                $out .= $csvquote . '' . $csvquote . $csvdelimiter . $csvquote . '' . $csvquote . $csvdelimiter . nl();
            }
        }

        jimport('joomla.version');
        $version = new JVersion();
        $_version = $version->getShortVersion();
        $tz = 'UTC';
        if (version_compare($_version, '3.2', '>=')) {
            $tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
        }

        $date_stamp = date('YmdHis');
        if (version_compare($_version, '3.2', '>=')) {
            $date_ = JFactory::getDate('now', $tz);
            $offset = $date_->getOffsetFromGMT();
            if ($offset > 0) {
                $date_->add(new DateInterval('PT' . $offset . 'S'));
            } else if ($offset < 0) {
                $offset = $offset * -1;
                $date_->sub(new DateInterval('PT' . $offset . 'S'));
            }
            $date_stamp = $date_->format('YmdHis', true);
        }

        $csvname = JPATH_SITE . '/components/com_breezingforms/exports/'.( $form_name != '' ? $form_name . '-' : '' ).'ffexport-' . $date_stamp . '.csv';
        JFile::makeSafe($csvname);

        //if (!JFile::write($csvname,$headout = $head.$out)) {
        //	echo "<script> alert('".addslashes(BFText::_('COM_BREEZINGFORMS_RECORDS_XMLNORWRTBL'))."'); window.history.go(-1);</script>\n";
        //	exit();
        //} // if

        if (isset($updIds[0])) {
            $updIds = implode(',', $updIds);

            $db->setQuery(
                "update #__facileforms_records set exported=1 where id in ($updIds)"
            );

            $db->query();
        }
        /*
          $data = BFFile::read($csvname);
          $files[] = array('name' => basename($csvname), 'data' => $data);

          $zip = JArchive::getAdapter('zip');
          $path = JPATH_SITE.'/components/com_breezingforms/exports/ffexport-csv-'.date('YmdHis').'.zip';
          $zip->create($path, $files);
          JFile::delete($csvname);
         */
        @ob_end_clean();

        //$_size = filesize($csvname);
        $_name = basename($csvname);
        @ini_set("zlib.output_compression", "Off");

        Header("Content-Type: text/comma-separated-values; charset=utf-8");
        Header("Content-Disposition: attachment;filename=\"$_name\"");
        Header("Content-Transfer-Encoding: 8bit");

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: private");
        //header("Content-Type: application/octet-stream");
        //header("Content-Disposition: attachment; filename=$_name");
        //header("Accept-Ranges: bytes");
        //header("Content-Length: $_size");
        ob_start();
        echo $head . $out;
        $c = ob_get_contents();
        ob_end_clean();
        if (function_exists('mb_convert_encoding')) {
            $to_encoding = 'UTF-16LE';
            $from_encoding = 'UTF-8';
            echo chr(255) . chr(254) . mb_convert_encoding($c, $to_encoding, $from_encoding);
        } else {
            echo $c;
        }
        exit;
    }

    function exportXml() {
        global $database, $ff_admsite, $ff_compath, $ff_version, $mosConfig_fileperms;

        $ids = BFRequest::getVar('cid', array());
        ArrayHelper::toInteger($ids);

        jimport('joomla.version');
        $version = new JVersion();
        $_version = $version->getShortVersion();
        $tz = 'UTC';
        if (version_compare($_version, '3.2', '>=')) {
            $tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
        }

        $date_stamp = date('YmdHis');
        $date_file = date('Y-m-d H:i:s');
        if (version_compare($_version, '3.2', '>=')) {
            $date_ = JFactory::getDate('now', $tz);
            $offset = $date_->getOffsetFromGMT();
            if ($offset > 0) {
                $date_->add(new DateInterval('PT' . $offset . 'S'));
            } else if ($offset < 0) {
                $offset = $offset * -1;
                $date_->sub(new DateInterval('PT' . $offset . 'S'));
            }
            $date_stamp = $date_->format('YmdHis', true);
            $date_file = $date_->format('Y-m-d H:i:s', true);
        }

        $form_name = '';

        $database = BFFactory::getdbo();

        if (isset($ids[0])) {
            $ids = implode(',', $ids);
            $database->setQuery(
                "select * from #__facileforms_records where id in ($ids) order by submitted Desc"
            );
        } else if (BFRequest::getInt('form_selection', 0)) {
            $database->setQuery(
                "select * from #__facileforms_records where form = " . $database->Quote(BFRequest::getInt('form_selection', 0)) . " order by submitted Desc"
            );
        } else {
            $database->setQuery(
                "select * from #__facileforms_records order by submitted Desc"
            );
        }

        $recs = $database->loadObjectList();

        if (BFRequest::getInt('form_selection', 0) && count($recs)) {

            $form_name = $recs[0]->name;
        }

        $xmlname = $ff_compath . '/exports/'.( $form_name != '' ? $form_name . '-' : '' ).'ffexport-' . $date_stamp . '.xml';

        if ($database->getErrorNum()) {
            echo $database->stderr();
            return false;
        } // if

        $xml = '<?xml version="1.0" encoding="utf-8" ?>' . nl() .
            '<FacileFormsExport type="records" version="' . $ff_version . '">' . nl() .
            indent(1) . '<exportdate>' . $date_file . '</exportdate>' . nl();
        $updIds = array();
        $form = '';
        for ($r = 0; $r < count($recs); $r++) {
            $rec = $recs[$r];

            if (version_compare($this->version, '3.2', '>=')) {
                $date_ = JFactory::getDate($rec->submitted, $this->tz);
                $offset = $date_->getOffsetFromGMT();
                if ($offset > 0) {
                    $date_->add(new DateInterval('PT' . $offset . 'S'));
                } else if ($offset < 0) {
                    $offset = $offset * -1;
                    $date_->sub(new DateInterval('PT' . $offset . 'S'));
                }
                $rec->submitted = $date_->format('Y-m-d H:i:s', true);
            }

            $updIds[] = $rec->id;
            $xml .= indent(1) . '<record id="' . $rec->id . '">' . nl() .
                indent(2) . '<submitted>' . $rec->submitted . '</submitted>' . nl() .
                indent(2) . '<user_id>' . $rec->user_id . '</user_id>' . nl() .
                indent(2) . '<username>' . htmlspecialchars($rec->username) . '</username>' . nl() .
                indent(2) . '<user_full_name>' . htmlspecialchars($rec->user_full_name) . '</user_full_name>' . nl() .
                indent(2) . '<form>' . $rec->form . '</form>' . nl() .
                indent(2) . '<title>' . htmlspecialchars($rec->title) . '</title>' . nl() .
                indent(2) . '<name>' . $rec->name . '</name>' . nl() .
                indent(2) . '<ip>' . $rec->ip . '</ip>' . nl() .
                indent(2) . '<browser>' . htmlspecialchars($rec->browser) . '</browser>' . nl() .
                indent(2) . '<opsys>' . htmlspecialchars($rec->opsys) . '</opsys>' . nl() .
                indent(2) . '<provider>' . $rec->provider . '</provider>' . nl() .
                indent(2) . '<viewed>' . $rec->viewed . '</viewed>' . nl() .
                indent(2) . '<exported>' . $rec->exported . '</exported>' . nl() .
                indent(2) . '<archived>' . $rec->archived . '</archived>' . nl() .
                indent(2) . '<pptxid>' . $rec->paypal_tx_id . '</pptxid>' . nl() .
                indent(2) . '<pppdate>' . $rec->paypal_payment_date . '</pppdate>' . nl() .
                indent(2) . '<pptestacc>' . $rec->paypal_testaccount . '</pptestacc>' . nl() .
                indent(2) . '<ppdltries>' . $rec->paypal_download_tries . '</ppdltries>' . nl() .
                indent(2) . '<opted>' . $rec->opted . '</opted>' . nl();
            $database->setQuery(
                "select subs.* from #__facileforms_subrecords As subs, #__facileforms_elements As els where els.id=subs.element And subs.record = $rec->id order by ordering"
            );
            $subs = $database->loadObjectList();
            for ($s = 0; $s < count($subs); $s++) {
                $sub = $subs[$s];
                if ($sub->type == 'File Upload' && strpos(strtolower($sub->value), '{cbsite}') === 0) {
                    $out = '';
                    $nl = '';
                    $_values = explode("\n", str_replace("\r", '', $sub->value));
                    $length = count($_values);
                    $i = 0;
                    foreach ($_values As $_value) {
                        if ($i + 1 < $length) {
                            $nl = "\n";
                        } else {
                            $nl = '';
                        }
                        $out .= str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), $_value) . $nl;
                        $i++;
                    }
                    $sub->value = $out;
                }
                $xml .= indent(2) . '<subrecord id="' . $sub->id . '">' . nl() .
                    indent(3) . '<element>' . $sub->element . '</element>' . nl() .
                    indent(3) . '<name>' . $sub->name . '</name>' . nl() .
                    indent(3) . '<title>' . htmlspecialchars($sub->title) . '</title>' . nl() .
                    indent(3) . '<type>' . $sub->type . '</type>' . nl() .
                    indent(3) . '<value>' . htmlspecialchars($sub->value) . '</value>' . nl() .
                    indent(2) . '</subrecord>' . nl();
            } // for
            $xml .= indent(1) . '</record>' . nl();
        } // for
        $xml .= '</FacileFormsExport>' . nl();

        //$xmlname = JFile::makeSafe($xmlname);
        //if (!JFile::write($xmlname,$xml)) {
        //	echo "<script> alert('".addslashes(BFText::_('COM_BREEZINGFORMS_RECORDS_XMLNORWRTBL'))."'); window.history.go(-1);</script>\n";
        //	exit();
        //} // if

        if (isset($updIds[0])) {
            $updIds = implode(',', $updIds);
            $database->setQuery(
                "update #__facileforms_records set exported=1 where id in ($updIds)"
            );
            $database->query();
        }

        @ob_end_clean();
        //$_size = filesize($xmlname);
        $_name = basename($xmlname);
        @ini_set("zlib.output_compression", "Off");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: private");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$_name");
        header("Accept-Ranges: bytes");
        //header("Content-Length: $_size");
        echo $xml;
        exit;
    }

    function removeDangerousHtml($value){

        if(trim($value) == '') return '';

        $doc = new DOMDocument();
        $ret = @$doc->loadHTML('<div>'.$value.'</div>');
        if($ret) {
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

            if ($container && $container->parentNode) {
                $container = $container->parentNode->removeChild($container);
            }

            if ($mock) {
                while ($mock->firstChild) {
                    $mock->removeChild($mock->firstChild);
                }
            }

            if ($container) {
                while ($container->firstChild) {
                    $mock->appendChild($container->firstChild);
                }
            }

            return $mock->saveHTML();

        }

        return strip_tags($value);
    }

// expxml
}