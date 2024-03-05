<?php

/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @license Released under the terms of the GNU General Public License
 * */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Joomla\Utilities\ArrayHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

require_once($ff_admpath . '/admin/form.html.php');

function resetQuickModeDbId(&$dataObject) {
    $db = Factory::getContainer()->get(DatabaseInterface::class);

    if (isset($dataObject['attributes']) && isset($dataObject['properties'])) {
        $mdata = $dataObject['properties'];
        if ($mdata['type'] == 'element') {
            //print_r($mdata);
            //exit;
            if (!isset($mdata['validationFunctionName'])) {
                $mdata['validationFunctionName'] = '';
            }
            $db->setQuery("Select id From #__facileforms_scripts Where `name` = " . $db->Quote($mdata['validationFunctionName']) . " Limit 1");
            $id = $db->loadResult();
            if ($id) {
                $mdata['validationId'] = $id;
            } else {
                $mdata['validationId'] = 0;
            }
            if (!isset($mdata['initScript'])) {
                $mdata['initScript'] = '';
            }
            $db->setQuery("Select id From #__facileforms_scripts Where `name` = " . $db->Quote($mdata['initScript']) . " Limit 1");
            $id = $db->loadResult();
            if ($id) {
                $mdata['initId'] = $id;
            } else {
                $mdata['initId'] = 0;
            }
            if (!isset($mdata['actionFunctionName'])) {
                $mdata['actionFunctionName'] = '';
            }
            $db->setQuery("Select id From #__facileforms_scripts Where `name` = " . $db->Quote($mdata['actionFunctionName']) . " Limit 1");
            $id = $db->loadResult();
            if ($id) {
                $mdata['actionId'] = $id;
            } else {
                $mdata['actionId'] = 0;
            }
            // this might collide with quickmode.class.php => save2 function and restoring the IDs
            //$mdata['dbId'] = 0;
            $dataObject['properties'] = $mdata;
        }
    }

    if (isset($dataObject['children']) && count($dataObject['children']) != 0) {
        $childrenAmount = count($dataObject['children']);
        for ($i = 0; $i < $childrenAmount; $i++) {
            resetQuickModeDbId($dataObject['children'][$i]);
        }
    }
}

class facileFormsForm {

    static function edit($option, $tabpane, $pkg, $ids, $caller) {
        global $database;
        ArrayHelper::toInteger($ids);
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $row = new facileFormsForms($database);
        if ($ids[0]) {
            $row->load($ids[0]);
        } else {
            $row->package = $pkg;
            $row->class1 = 'content_outline';
            $row->width = 400;
            $row->widthmode = 0;
            $row->height = 500;
            $row->heightmode = 0;
            $row->pages = 1;
            $row->emailntf = 1;
            $row->mb_emailntf = 1;
            $row->emaillog = 1;
            $row->mb_emaillog = 1;
            $row->emailxml = 0;
            $row->mb_emailxml = 0;
            $row->dblog = 1;
            $row->script1cond = 0;
            $row->script2cond = 0;
            $row->piece1cond = 0;
            $row->piece2cond = 0;
            $row->piece3cond = 0;
            $row->piece4cond = 0;
            $row->published = 1;
            $row->runmode = 0;
            $row->prevmode = 2;
            $row->prevwidth = 400;
            $row->custom_mail_subject = '';
            $row->mb_custom_mail_subject = '';
            $row->alt_mailfrom = '';
            $row->mb_alt_mailfrom = '';
            $row->alt_fromname = '';
            $row->mb_alt_fromname = '';
            $row->email_type = 0;
            $row->mb_email_type = 0;
            $row->email_custom_html = 0;
            $row->mb_email_custom_html = 0;
            $row->email_custom_template = '';
            $row->mb_email_custom_template = '';
            $row->salesforce_token = '';
            $row->salesforce_enabled = 0;
            $row->salesforce_fields = '';
            $row->salesforce_username = '';
            $row->salesforce_password = '';
            $row->dropbox_email = '';
            $row->dropbox_password = '';
            $row->dropbox_folder = '';
            $row->dropbox_submission_enabled = 0;
            $row->dropbox_submission_types = 'pdf';
            $row->autoheight = 0;

            $database->setQuery("select max(ordering)+1 from #__facileforms_forms");
            $row->ordering = $database->loadResult();
        } // if

        $row->dropbox_submission_types = explode(',', $row->dropbox_submission_types);

        $row->salesforce_types = array();
        $row->salesforce_type_fields = array();
        $row->breezingforms_fields = array();
        $row->salesforce_error = '';

        if ($row->salesforce_enabled && $ids[0]) {
            try {
                define("BF_SOAP_CLIENT_BASEDIR", JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_breezingforms' . DS . 'libraries' . DS . 'salesforce');
                if (!class_exists('SforcePartnerClient')) {
                    require_once (BF_SOAP_CLIENT_BASEDIR . '/SforcePartnerClient.php');
                }
                if (!class_exists('SforceHeaderOptions')) {
                    require_once (BF_SOAP_CLIENT_BASEDIR . '/SforceHeaderOptions.php');
                }
                $mySforceConnection = new SforcePartnerClient();
                $mySoapClient = $mySforceConnection->createConnection(BF_SOAP_CLIENT_BASEDIR . '/partner.wsdl.xml');
                $mylogin = $mySforceConnection->login($row->salesforce_username, $row->salesforce_password . $row->salesforce_token);
                $row->salesforce_types = $mySforceConnection->describeGlobal()->sobjects;
                // retrieve fields from Lead by default
                $sobjects = array();
                if ($row->salesforce_type == '') {
                    $row->salesforce_type = 'Lead';
                    $sobjects = $mySforceConnection->describeSObject('Lead')->fields;
                } else {
                    $sobjects = $mySforceConnection->describeSObject($row->salesforce_type)->fields;
                }
                $row->salesforce_type_fields = $sobjects;
                $row->salesforce_fields = explode(',', $row->salesforce_fields);
                $database->setQuery("Select `title`,`name` From #__facileforms_elements Where form = " . $ids[0] . " And `title` Not In ('bfFakeTitle','bfFakeTitle2','bfFakeTitle3','bfFakeTitle4','bfFakeTitle5') Order By ordering");
                $row->breezingforms_fields = $database->loadObjectList();
            } catch (Exception $e) {
                $row->salesforce_error = $e->getMessage();
            }
        }

        $lists = array();

        $database->setQuery(
                "select id, concat(package,'::',name) as text " .
                "from #__facileforms_scripts " .
                "where published=1 and type='Form Init' " .
                "order by text, id desc"
        );
        $lists['init'] = $database->loadObjectList();

        $database->setQuery(
                "select id, concat(package,'::',name) as text " .
                "from #__facileforms_scripts " .
                "where published=1 and type='Form Submitted' " .
                "order by text, id desc"
        );
        $lists['submitted'] = $database->loadObjectList();

        $database->setQuery(
                "select id, concat(package,'::',name) as text " .
                "from #__facileforms_pieces " .
                "where published=1 and type='Before Form' " .
                "order by text, id desc"
        );
        $lists['piece1'] = $database->loadObjectList();

        $database->setQuery(
                "select id, concat(package,'::',name) as text " .
                "from #__facileforms_pieces " .
                "where published=1 and type='After Form' " .
                "order by text, id desc"
        );
        $lists['piece2'] = $database->loadObjectList();

        $database->setQuery(
                "select id, concat(package,'::',name) as text " .
                "from #__facileforms_pieces " .
                "where published=1 and type='Begin Submit' " .
                "order by text, id desc"
        );
        $lists['piece3'] = $database->loadObjectList();

        $database->setQuery(
                "select id, concat(package,'::',name) as text " .
                "from #__facileforms_pieces " .
                "where published=1 and type='End Submit' " .
                "order by text, id desc"
        );
        $lists['piece4'] = $database->loadObjectList();

        $order = JHTML::_('list.genericordering',
                        "select ordering as value, title as text " .
                        "from #__facileforms_forms " .
                        "where package = " . $database->quote($pkg) . " " .
                        "order by ordering"
        );
        $lists['ordering'] = JHTML::_('select.genericlist',
                        $order, 'ordering', 'class="inputbox" size="1"',
                        'value', 'text', intval($row->ordering)
        );

        HTML_facileFormsForm::edit($option, $tabpane, $pkg, $row, $lists, $caller);
    }

// edit

    static function save($option, $pkg, $caller, $nonclassic, $quickmode) {
        global $database;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $row = new facileFormsForms($database);

        if (isset($_POST['salesforce_fields']) && is_array($_POST['salesforce_fields'])) {
            $i = 0;
            foreach ($_POST['salesforce_fields'] As $sfield) {
                if ($sfield == '') {
                    unset($_POST['salesforce_fields'][$i]);
                }
                $i++;
            }
            $_POST['salesforce_fields'] = implode(',', $_POST['salesforce_fields']);
        }

        if (!isset($_POST['salesforce_enabled'])) {
            $_POST['salesforce_enabled'] = 0;
        }

        if (isset($_POST['dropbox_submission_types']) && is_array($_POST['dropbox_submission_types'])) {
            $_POST['dropbox_submission_types'] = implode(',', $_POST['dropbox_submission_types']);
        } else {
            $_POST['dropbox_submission_types'] = '';
        }

        if (BFRequest::getInt('dropbox_reset_auth', 0)) {
            $_POST['dropbox_password'] = '';
            $_POST['dropbox_email'] = '';
        }

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

        $row->reorder("");

        JPluginHelper::importPlugin('breezingforms_addons');
        Factory::getApplication()->triggerEvent('onPropertiesSave', array(BFRequest::getInt('id', 0)));

        if (trim($caller) == '') {
            $caller = "index.php?option=$option&act=manageforms&pkg=$pkg";
            Factory::getApplication()->redirect($caller);
        }

        Factory::getApplication()->redirect('index.php?option=com_breezingforms&task=editform&act=editpage&form=' . intval($_POST['id']) . ($quickmode ? '&pkg=QuickModeForms' : ''));
    }

// save

    static function cancel($option, $pkg, $caller, $nonclassic, $quickmode) {
        if (!$nonclassic) {
            if (trim($caller) == '')
                $caller = "index.php?option=$option&act=manageforms&pkg=$pkg";
            Factory::getApplication()->redirect($caller);
        } else {
            if (!$quickmode) {
                Factory::getApplication()->redirect('index.php?option=com_breezingforms&format=html&act=easymode&formName=' . BFRequest::getVar('name', '') . '&form=' . BFRequest::getInt('id', 0));
            } else {
                Factory::getApplication()->redirect('index.php?option=com_breezingforms&act=quickmode&formName=' . BFRequest::getVar('name', '') . '&form=' . BFRequest::getInt('id', 0));
            }
        }
    }

// cancel

    static function copy($option, $pkg, $ids) {
        global $database;
        ArrayHelper::toInteger($ids);
        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/admin/quickmode.class.php');
        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
        require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $total = count($ids);
        $row = new facileFormsForms($database);
        $elem = new facileFormsElements($database);

        if (count($ids))
            foreach ($ids as $id) {
                $row->load(intval($id));
                $row->id = NULL;
                $row->ordering = 999999;
                $row->title = 'Copy of ' . $row->title;
                $row->name = 'copy_' . $row->name;
                $row->store();
                $row->reorder('');

                $database->setQuery("select id from #__facileforms_elements where form=$id");
                $eids = $database->loadObjectList();
                for ($i = 0; $i < count($eids); $i++) {
                    $eid = $eids[$i];
                    $elem->load(intval($eid->id));
                    $elem->id = NULL;
                    $elem->form = $row->id;
                    $elem->store();
                } // for
                // resetting easy and quickmode database ids
                Factory::getContainer()->get(DatabaseInterface::class)->setQuery("Select template_areas, template_code_processed, template_code From #__facileforms_forms Where id = " . intval($row->id));
                $row_ = Factory::getContainer()->get(DatabaseInterface::class)->loadObject();

                if (trim($row_->template_code) != '') {
                    $areas = Zend_Json::decode($row_->template_areas);
                    $i = 0;
                    foreach ($areas As $area) {
                        $j = 0;
                        foreach ($area['elements'] As $element) {
                            $areas[$i]['elements'][$j]['dbId'] = 0;
                            $j++;
                        }
                        $i++;
                    }

                    $template_areas = Zend_Json::encode($areas);
                    $template_code = $row_->template_code;

                    if ($row_->template_code_processed == 'QuickMode') {
                        $dataObject = Zend_Json::decode(bf_b64dec($row_->template_code));
                        $dataObject['properties']['name'] = 'copy_' . $dataObject['properties']['name'];
                        $dataObject['properties']['title'] = 'Copy of ' . $dataObject['properties']['title'];
                        resetQuickModeDbId($dataObject);
                        $template_code = bf_b64enc(Zend_Json::encode($dataObject));
                    }

                    Factory::getContainer()->get(DatabaseInterface::class)->setQuery("Update #__facileforms_forms Set template_code = " . Factory::getContainer()->get(DatabaseInterface::class)->Quote($template_code) . ", template_areas = " . Factory::getContainer()->get(DatabaseInterface::class)->Quote($template_areas) . " Where id = " . intval($row->id));
                    Factory::getContainer()->get(DatabaseInterface::class)->execute();

                    if ($row_ && $row_->template_code_processed == 'QuickMode') {
                        $quickMode = new QuickMode();
                        $quickMode->save(
                                $row->id, Zend_Json::decode(bf_b64dec($template_code))
                        );
                    }
                }
                // reset end
            } // foreach
        $msg = $total . ' ' . BFText::_('COM_BREEZINGFORMS_FORMS_SUCOPIED');
        Factory::getApplication()->redirect("index.php?option=$option&act=manageforms&pkg=$pkg&mosmsg=$msg");
    }

// copy

    static function del($option, $pkg, $ids) {
        global $database;
        ArrayHelper::toInteger($ids);
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        if (count($ids)) {
            $ids = implode(',', $ids);
            $database->setQuery("delete from #__facileforms_elements where form in ($ids)");
            if (!$database->execute()) {
                echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
            } // if
            $database->setQuery("delete from #__facileforms_forms where id in ($ids)");
            if (!$database->execute()) {
                echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
            } // if
        } // if
        Factory::getApplication()->redirect("index.php?option=$option&act=manageforms&pkg=$pkg");
    }

// del

    static function order($option, $pkg, $ids, $inc) {
        global $database;
        ArrayHelper::toInteger($ids);
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $row = new facileFormsForms($database);
        $row->load($ids[0]);
        $row->move($inc, "package=" . $database->Quote($pkg) . "");
        Factory::getApplication()->redirect("index.php?option=$option&act=manageforms&pkg=$pkg");
    }

// order

    static function publish($option, $pkg, $ids, $publish) {
        global $database, $my;
        ArrayHelper::toInteger($ids);
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $ids = implode(',', $ids);
        $database->setQuery(
                "update #__facileforms_forms set published='$publish' where id in ($ids)"
        );
        if (!$database->execute()) {
            echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
            exit();
        } // if
        Factory::getApplication()->redirect("index.php?option=$option&act=manageforms&pkg=$pkg");
    }

// publish

    static function listitems($option, $pkg) {
        global $database;
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $database->setQuery(
                "select distinct package as name " .
                "from #__facileforms_forms " .
                "where package is not null and package!='' " .
                "order by name"
        );


        try {
            $pkgs = $database->loadObjectList();
        } catch (\Exception $e) {
            try {
                Log::add(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), Log::WARNING, 'jerror');
            } catch (\RuntimeException $exception) {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                    'warning'
                );
            }

            return false;
        }

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

        $limit = Factory::getApplication()->getUserStateFromRequest('global.list.limit', 'limit', Factory::getApplication()->getCfg('list_limit'), 'int');
        $limitstart = 0;
        if (isset($_REQUEST['limit']) && isset($_REQUEST['limitstart'])) {
            $limit = intval($_REQUEST['limit']);
            $limitstart = intval($_REQUEST['limitstart']);
        }

        $database->setQuery(
                "select SQL_CALC_FOUND_ROWS * from #__facileforms_forms " .
                "where package = " . $database->Quote($pkg) . " " .
                "order by ordering",
                $limitstart,
                $limit
        );
        $rows = $database->loadObjectList();

        try {
			$rows = $database->loadObjectList();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

        $database->setQuery('SELECT FOUND_ROWS();');
        $total = $database->loadResult();

        HTML_facileFormsForm::listitems($option, $rows, $pkglist, $total);
    }

// listitems

    static function getPagination($total, $limit, $limitstart) {

        static $pagination;

        // Load the content if it doesn't already exist
        if (empty($pagination)) {

            // using a different chrome to bypass problems with pagination in frontend 
            if (Factory::getApplication()->isClient('site')) {
                require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_breezingforms' . DS . 'libraries' . DS . 'crosstec' . DS . 'classes' . DS . 'BFPagination.php');
                $pagination = new BFPagination($total, $limitstart, $limit);
            } else {
                jimport('joomla.html.pagination');
                $pagination = new JPagination($total, $limitstart, $limit);
            }
        }
        return $pagination;
    }

}