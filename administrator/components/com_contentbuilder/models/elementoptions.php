<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;

require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'plugin_helper.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'plugin_helper4.php');

class ContentbuilderModelElementoptions extends CBModel
{
    private $_element_id = 0;

    function __construct($config)
    {
        parent::__construct();

        $this->setIds(CBRequest::getInt('id', 0), CBRequest::getInt('element_id', ''));
    }

    /*
     * MAIN DETAILS AREA
     */

    /**
     *
     * @param int $id
     */
    function setIds($id, $element_id)
    {
        // Set id and wipe data
        $this->_id = $id;
        $this->_element_id = $element_id;
        $this->_data = null;
    }

    private function _buildQuery()
    {
        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_elements Where id = ' . intval($this->_element_id);
    }

    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
            if (is_object($this->_data)) {
                $this->_data->options = $this->_data->options ? unserialize(cb_b64dec($this->_data->options)) : null;
            }
            return $this->_data;

        }
        return null;
    }

    function getValidationPlugins()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_validation' And `enabled` = 1");

        $res = $db->loadColumn();
        return $res;
    }

    function getGroupDefinition()
    {
        $this->_db->setQuery("Select `type`, `reference_id` From #__contentbuilder_forms Where id = " . intval($this->_id));
        $form = $this->_db->loadAssoc();
        $form = contentbuilder::getForm($form['type'], $form['reference_id']);
        if ($form->isGroup($this->_data->reference_id)) {
            return $form->getGroupDefinition($this->_data->reference_id);
        }
        return array();
    }

    function store()
    {
        if (CBRequest::getInt('type_change', 0)) {
            $this->_db->setQuery("Update #__contentbuilder_elements Set `type`=" . $this->_db->Quote(CBRequest::getCmd('type_selection', '')) . " Where id = " . $this->_element_id);
            $this->_db->execute();
            return 1;
        }
        $query = '';
        $plugins = contentbuilder::getFormElementsPlugins();
        $type = CBRequest::getCmd('field_type', '');
        switch ($type) {
            case in_array(CBRequest::getCmd('field_type', ''), contentbuilder::getFormElementsPlugins()):

                $hint = CBRequest::getVar('hint', '', 'POST', 'STRING', CBREQUEST_ALLOWHTML);

                \Joomla\CMS\Plugin\PluginHelper4::importPlugin('contentbuilder_form_elements', CBRequest::getCmd('field_type', ''));

                $dispatcher = Factory::getApplication()->getDispatcher();
                $eventResult = $dispatcher->dispatch('onSettingsStore', new Joomla\Event\Event('onSettingsStore', array()));
                $results = $eventResult->getArgument('result') ?: [];
                Factory::getApplication()->getDispatcher()->clearListeners('onSettingsStore');

                if (count($results)) {
                    $results = $results[0];
                }

                $the_item = $results;

                $query = " `options`='" . cb_b64enc(serialize($the_item['options'])) . "', `type`=" . $this->_db->Quote(CBRequest::getCmd('field_type', '')) . ", `change_type`=" . $this->_db->Quote(CBRequest::getCmd('field_type', '')) . ", `hint`=" . $this->_db->Quote($hint) . ", `default_value`=" . $this->_db->Quote($the_item['default_value']) . " ";

                break;
            case '':
            case 'text':
                $length = CBRequest::getVar('length', '');
                $maxlength = CBRequest::getInt('maxlength', '');
                $password = CBRequest::getInt('password', 0);
                $readonly = CBRequest::getInt('readonly', 0);
                $default_value = CBRequest::getVar('default_value', '', 'POST', 'STRING', CBREQUEST_ALLOWRAW);
                $class = CBRequest::getVar('class', '');
                $allow_raw = CBRequest::getInt('allow_encoding', 0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html = CBRequest::getInt('allow_encoding', 0) == 1 ? true : false;
                $hint = CBRequest::getVar('hint', '', 'POST', 'STRING', CBREQUEST_ALLOWHTML);

                $options = new stdClass();
                $options->length = $length;
                $options->class = $class;
                $options->maxlength = $maxlength;
                $options->password = $password;
                $options->readonly = $readonly;
                $options->allow_raw = $allow_raw;
                $options->allow_html = $allow_html;

                $query = " `options`='" . cb_b64enc(serialize($options)) . "', `type`='text', `change_type`='text', `hint`=" . $this->_db->Quote($hint) . ", `default_value`=" . $this->_db->Quote($default_value) . " ";

                break;
            case 'textarea':
                $maxlength = CBRequest::getInt('maxlength', '');
                $width = CBRequest::getVar('width', '');
                $height = CBRequest::getVar('height', '');
                $default_value = CBRequest::getVar('default_value', '', 'POST', 'STRING', CBREQUEST_ALLOWRAW);
                $class = CBRequest::getVar('class', '');
                $readonly = CBRequest::getInt('readonly', 0);
                $allow_raw = CBRequest::getInt('allow_encoding', 0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html = CBRequest::getInt('allow_encoding', 0) == 1 ? true : false;
                $hint = CBRequest::getVar('hint', '', 'POST', 'STRING', CBREQUEST_ALLOWHTML);

                $options = new stdClass();
                $options->class = $class;
                $options->maxlength = $maxlength;
                $options->width = $width;
                $options->height = $height;
                $options->readonly = $readonly;
                $options->allow_raw = $allow_raw;
                $options->allow_html = $allow_html;

                $query = " `options`='" . cb_b64enc(serialize($options)) . "', `type`='textarea', `change_type`='textarea', `hint`=" . $this->_db->Quote($hint) . ", `default_value`=" . $this->_db->Quote($default_value) . " ";
                break;
            case 'checkboxgroup':
            case 'radiogroup':
            case 'select':
                $seperator = CBRequest::getVar('seperator', ',', 'POST', 'STRING', CBREQUEST_ALLOWRAW);

                if ($seperator == '\n') {
                    $seperator = "\n";
                }

                $default_value = implode($seperator, CBRequest::getVar('default_value', array()));
                $class = CBRequest::getVar('class', '');
                $allow_raw = CBRequest::getInt('allow_encoding', 0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html = CBRequest::getInt('allow_encoding', 0) == 1 ? true : false;
                $hint = CBRequest::getVar('hint', '', 'POST', 'STRING', CBREQUEST_ALLOWHTML);

                $options = new stdClass();
                $options->class = $class;
                $options->seperator = $seperator;
                $options->allow_raw = $allow_raw;
                $options->allow_html = $allow_html;

                if ($type == 'select') {
                    $multi = CBRequest::getInt('multiple', 0);
                    $options->multiple = $multi;
                    $options->length = CBRequest::getVar('length', '');
                }

                if ($type == 'checkboxgroup' || $type == 'radiogroup') {
                    $options->horizontal = CBRequest::getBool('horizontal', 0);
                    $options->horizontal_length = CBRequest::getVar('horizontal_length', '');
                }

                $query = " `options`='" . cb_b64enc(serialize($options)) . "', `type`='" . $type . "', `change_type`='" . $type . "', `hint`=" . $this->_db->Quote($hint) . ", `default_value`=" . $this->_db->Quote($default_value) . " ";
                break;
            case 'upload':
                $this->_db->setQuery("Select upload_directory, protect_upload_directory From #__contentbuilder_forms Where id = " . $this->_id);
                $setup = $this->_db->loadAssoc();

                // rel check for setup

                $tokens = '';

                $upl_ex = explode('|', $setup['upload_directory']);
                $setup['upload_directory'] = $upl_ex[0];

                $upl_ex2 = explode('|', trim(CBRequest::getVar('upload_directory', '')));

                CBRequest::setVar('upload_directory', $upl_ex2[0]);

                $is_relative = strpos(strtolower($setup['upload_directory']), '{cbsite}') === 0;
                $tmp_upload_directory = $setup['upload_directory'];
                $upload_directory = $is_relative ? str_replace(array('{CBSite}', '{cbsite}'), JPATH_SITE, $setup['upload_directory']) : $setup['upload_directory'];

                // rel check for element options
                $is_opt_relative = strpos(strtolower(trim(CBRequest::getVar('upload_directory', ''))), '{cbsite}') === 0;
                $tmp_opt_upload_directory = trim(CBRequest::getVar('upload_directory', ''));
                CBRequest::setVar('upload_directory', $is_relative ? str_replace(array('{CBSite}', '{cbsite}'), JPATH_SITE, trim(CBRequest::getVar('upload_directory', ''))) : trim(CBRequest::getVar('upload_directory', '')));


                $protect = $setup['protect_upload_directory'];

                if (!trim(CBRequest::getVar('upload_directory', '')) && !is_dir($upload_directory)) {

                    if (!is_dir(JPATH_SITE . DS . 'media' . DS . 'contentbuilder')) {
                        Folder::create(JPATH_SITE . DS . 'media' . DS . 'contentbuilder');
                        File::write(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'index.html', $def = '');
                    }

                    if (!is_dir(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload')) {
                        Folder::create(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload');
                        File::write(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload' . DS . 'index.html', $def = '');
                    }

                    $upload_directory = JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload';

                    if ($is_opt_relative) {
                        $is_relative = 1;
                        $tmp_upload_directory = '{CBSite}' . DS . 'media' . DS . 'contentbuilder' . DS . 'upload';
                    }

                    if (isset($upl_ex[1])) {
                        $tokens = '|' . $upl_ex[1];
                    }

                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_FALLBACK_UPLOAD_CREATED') . ' (' . DS . 'media' . DS . 'contentbuilder' . DS . 'upload' . ')', 'warning');

                } else if (trim(CBRequest::getVar('upload_directory', '')) != '' && !is_dir(contentbuilder::makeSafeFolder(CBRequest::getVar('upload_directory', '')))) {

                    $upload_directory = contentbuilder::makeSafeFolder(CBRequest::getVar('upload_directory', ''));

                    Folder::create($upload_directory);
                    File::write($upload_directory . DS . 'index.html', $def = '');

                    if ($is_opt_relative) {
                        $is_relative = 1;
                        $tmp_upload_directory = $tmp_opt_upload_directory;
                    }

                    if (isset($upl_ex2[1])) {
                        $tokens = '|' . $upl_ex2[1];
                    }

                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_FALLBACK_UPLOAD_CREATED') . ' (' . $upload_directory . ')', 'warning');

                } else if (trim(CBRequest::getVar('upload_directory', '')) != '' && is_dir(contentbuilder::makeSafeFolder(CBRequest::getVar('upload_directory', '')))) {

                    $upload_directory = contentbuilder::makeSafeFolder(CBRequest::getVar('upload_directory', ''));

                    if ($is_opt_relative) {
                        $is_relative = 1;
                        $tmp_upload_directory = $tmp_opt_upload_directory;
                    }

                    if (isset($upl_ex2[1])) {
                        $tokens = '|' . $upl_ex2[1];
                    }

                } else {
                    if (isset($upl_ex[1])) {
                        $tokens = '|' . $upl_ex[1];
                    }
                }

                if ($protect && is_dir($upload_directory)) {

                    File::write(contentbuilder::makeSafeFolder($upload_directory) . DS . '.htaccess', $def = 'deny from all');

                } else if (!$protect && is_dir($upload_directory)) {
                    if (file_exists(contentbuilder::makeSafeFolder($upload_directory) . DS . '.htaccess')) {
                        File::delete(contentbuilder::makeSafeFolder($upload_directory) . DS . '.htaccess');
                    }

                }

                $default_value = CBRequest::getVar('default_value', '');
                $hint = CBRequest::getVar('hint', '', 'POST', 'STRING', CBREQUEST_ALLOWHTML);

                $options = new stdClass();
                $options->upload_directory = is_dir($upload_directory) ? ($is_relative ? $tmp_upload_directory : $upload_directory) . $tokens : '';
                $options->allowed_file_extensions = CBRequest::getVar('allowed_file_extensions', '');
                $options->max_filesize = CBRequest::getVar('max_filesize', '');

                $query = " `options`='" . cb_b64enc(serialize($options)) . "', `type`='" . $type . "', `change_type`='" . $type . "', `hint`=" . $this->_db->Quote($hint) . ", `default_value`=" . $this->_db->Quote($default_value) . " ";
                break;
            case 'captcha':
                $default_value = CBRequest::getVar('default_value', '');
                $hint = CBRequest::getVar('hint', '', 'POST', 'STRING', CBREQUEST_ALLOWHTML);

                $options = new stdClass();

                $query = " `options`='" . cb_b64enc(serialize($options)) . "', `type`='" . $type . "', `change_type`='" . $type . "', `hint`=" . $this->_db->Quote($hint) . ", `default_value`=" . $this->_db->Quote($default_value) . " ";
                break;
            case 'calendar':
                $length = CBRequest::getVar('length', '');
                $format = CBRequest::getVar('format', '');
                $transfer_format = CBRequest::getVar('transfer_format', '');
                $maxlength = CBRequest::getInt('maxlength', '');
                $readonly = CBRequest::getInt('readonly', 0);
                $default_value = CBRequest::getVar('default_value', '', 'POST', 'STRING', CBREQUEST_ALLOWRAW);
                $hint = CBRequest::getVar('hint', '', 'POST', 'STRING', CBREQUEST_ALLOWHTML);

                $options = new stdClass();
                $options->length = $length;
                $options->maxlength = $maxlength;
                $options->readonly = $readonly;
                $options->format = $format;
                $options->transfer_format = $transfer_format;

                $query = " `options`='" . cb_b64enc(serialize($options)) . "', `type`='calendar', `change_type`='calendar', `hint`=" . $this->_db->Quote($hint) . ", `default_value`=" . $this->_db->Quote($default_value) . " ";

                break;
            case 'hidden':
                $allow_raw = CBRequest::getInt('allow_encoding', 0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html = CBRequest::getInt('allow_encoding', 0) == 1 ? true : false;
                $default_value = CBRequest::getVar('default_value', '', 'POST', 'STRING', CBREQUEST_ALLOWRAW);
                $hint = '';

                $options = new stdClass();
                $options->allow_raw = $allow_raw;
                $options->allow_html = $allow_html;

                $query = " `options`='" . cb_b64enc(serialize($options)) . "', `type`='" . $type . "', `change_type`='" . $type . "', `hint`=" . $this->_db->Quote($hint) . ", `default_value`=" . $this->_db->Quote($default_value) . " ";
                break;
        }
        if ($query) {

            $custom_init_script = CBRequest::getVar('custom_init_script', '', 'POST', 'STRING', CBREQUEST_ALLOWRAW);
            $custom_action_script = CBRequest::getVar('custom_action_script', '', 'POST', 'STRING', CBREQUEST_ALLOWRAW);
            $custom_validation_script = CBRequest::getVar('custom_validation_script', '', 'POST', 'STRING', CBREQUEST_ALLOWRAW);
            $validation_message = CBRequest::getVar('validation_message', '');
            $validations = CBRequest::getVar('validations', array());

            $other = " `validations`=" . $this->_db->Quote(implode(',', $validations)) . ", ";
            $other .= " `custom_init_script`=" . $this->_db->Quote($custom_init_script) . ", ";
            $other .= " `custom_action_script`=" . $this->_db->Quote($custom_action_script) . ", ";
            $other .= " `custom_validation_script`=" . $this->_db->Quote($custom_validation_script) . ", ";
            $other .= " `validation_message`=" . $this->_db->Quote($validation_message) . ", ";

            $this->_db->setQuery("Update #__contentbuilder_elements Set $other $query Where id = " . $this->_element_id);
            $this->_db->execute();
            return true;
        }
        return false;
    }
}
