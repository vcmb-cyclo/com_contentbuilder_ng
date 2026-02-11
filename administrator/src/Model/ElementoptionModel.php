<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\Model;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;
use CB\Component\Contentbuilder_ng\Administrator\Helper\Logger;
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderLegacyHelper;

class ElementoptionModel extends BaseDatabaseModel
{
    private $_element_id = 0;

    public function __construct(
        $config,
        MVCFactoryInterface $factory
    ) {
        // IMPORTANT : on transmet factory/app/input à BaseController
        parent::__construct($config, $factory);

        $this->_db = Factory::getContainer()->get(DatabaseInterface::class);

        $app = Factory::getApplication();
        $option = 'com_contentbuilder_ng';

        $this->setIds(Factory::getApplication()->input->getInt('id', 0), Factory::getApplication()->input->getInt('element_id', ''));
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
        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_ng_elements Where id = ' . intval($this->_element_id);
    }

    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->getDatabase()->setQuery($query);
            $this->_data = $this->getDatabase()->loadObject();
            if (is_object($this->_data)) {
                $this->_data->options = $this->_data->options ? unserialize(base64_decode($this->_data->options)) : null;
            }
            return $this->_data;

        }
        return null;
    }

    function getValidationPlugins()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_ng_validation' And `enabled` = 1");

        $res = $db->loadColumn();
        return $res;
    }

    function getGroupDefinition()
    {
        $this->getDatabase()->setQuery("Select `type`, `reference_id` From #__contentbuilder_ng_forms Where id = " . intval($this->_id));
        $form = $this->getDatabase()->loadAssoc();
        $form = ContentbuilderLegacyHelper::getForm($form['type'], $form['reference_id']);
        if ($form->isGroup($this->_data->reference_id)) {
            return $form->getGroupDefinition($this->_data->reference_id);
        }
        return array();
    }

    function store()
    {
        if (Factory::getApplication()->input->getInt('type_change', 0)) {
            $this->getDatabase()->setQuery("Update #__contentbuilder_ng_elements Set `type`=" . $this->getDatabase()->Quote(Factory::getApplication()->input->getCmd('type_selection', '')) . " Where id = " . $this->_element_id);
            $this->getDatabase()->execute();
            return 1;
        }
        $query = '';
        $plugins = ContentbuilderLegacyHelper::getFormElementsPlugins();
        $type = Factory::getApplication()->input->getCmd('field_type', '');
        switch ($type) {
            case in_array(Factory::getApplication()->input->getCmd('field_type', ''), ContentbuilderLegacyHelper::getFormElementsPlugins()):

                $hint = Factory::getApplication()->input->post->get('hint', '', 'html');

                \Joomla\CMS\Plugin\PluginHelper::importPlugin('contentbuilder_ng_form_elements', Factory::getApplication()->input->getCmd('field_type', ''));

                $dispatcher = Factory::getApplication()->getDispatcher();
                $eventResult = $dispatcher->dispatch('onSettingsStore', new \Joomla\Event\Event('onSettingsStore', array()));
                $results = $eventResult->getArgument('result') ?: [];
                Factory::getApplication()->getDispatcher()->clearListeners('onSettingsStore');

                if (count($results)) {
                    $results = $results[0];
                }

                $the_item = $results;

                $query = " `options`='" . base64_encode(serialize($the_item['options'])) . "', `type`=" . $this->getDatabase()->Quote(Factory::getApplication()->input->getCmd('field_type', '')) . ", `change_type`=" . $this->getDatabase()->Quote(Factory::getApplication()->input->getCmd('field_type', '')) . ", `hint`=" . $this->getDatabase()->Quote($hint) . ", `default_value`=" . $this->getDatabase()->Quote($the_item['default_value']) . " ";
                break;

            case '':
            case 'text':
                $length = Factory::getApplication()->input->get('length', '', 'string');
                $maxlength = Factory::getApplication()->input->getInt('maxlength', '');
                $password = Factory::getApplication()->input->getInt('password', 0);
                $readonly = Factory::getApplication()->input->getInt('readonly', 0);
                $default_value = Factory::getApplication()->input->post->get('default_value', '', 'raw');
                $class = Factory::getApplication()->input->get('class', '', 'string');
                $allow_raw = Factory::getApplication()->input->getInt('allow_encoding', 0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html = Factory::getApplication()->input->getInt('allow_encoding', 0) == 1 ? true : false;
                $hint = Factory::getApplication()->input->post->get('hint', '', 'html');

                $options = new \stdClass();
                $options->length = $length;
                $options->class = $class;
                $options->maxlength = $maxlength;
                $options->password = $password;
                $options->readonly = $readonly;
                $options->allow_raw = $allow_raw;
                $options->allow_html = $allow_html;

                $query = " `options`='" . base64_encode(serialize($options)) . "', `type`='text', `change_type`='text', `hint`=" . $this->getDatabase()->Quote($hint) . ", `default_value`=" . $this->getDatabase()->Quote($default_value) . " ";
                break;

            case 'textarea':
                $maxlength = Factory::getApplication()->input->getInt('maxlength', '');
                $width = Factory::getApplication()->input->get('width', '', 'string');
                $height = Factory::getApplication()->input->get('height', '', 'string');
                $default_value = Factory::getApplication()->input->post->get('default_value', '', 'raw');
                $class = Factory::getApplication()->input->get('class', '', 'string');
                $readonly = Factory::getApplication()->input->getInt('readonly', 0);
                $allow_raw = Factory::getApplication()->input->getInt('allow_encoding', 0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html = Factory::getApplication()->input->getInt('allow_encoding', 0) == 1 ? true : false;
                $hint = Factory::getApplication()->input->post->get('hint', '', 'html');

                $options = new \stdClass();
                $options->class = $class;
                $options->maxlength = $maxlength;
                $options->width = $width;
                $options->height = $height;
                $options->readonly = $readonly;
                $options->allow_raw = $allow_raw;
                $options->allow_html = $allow_html;

                $query = " `options`='" . base64_encode(serialize($options)) . "', `type`='textarea', `change_type`='textarea', `hint`=" . $this->getDatabase()->Quote($hint) . ", `default_value`=" . $this->getDatabase()->Quote($default_value) . " ";
                break;

            case 'checkboxgroup':
            case 'radiogroup':
            case 'select':
                $seperator = Factory::getApplication()->input->post->get('seperator', ',', 'raw');

                if ($seperator == '\n') {
                    $seperator = "\n";
                }

                $defaultValues = Factory::getApplication()->input->post->get('default_value', [], 'array');
                $default_value = implode($seperator, $defaultValues);
                $class = Factory::getApplication()->input->get('class', '', 'string');
                $allow_raw = Factory::getApplication()->input->getInt('allow_encoding', 0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html = Factory::getApplication()->input->getInt('allow_encoding', 0) == 1 ? true : false;
                $hint = Factory::getApplication()->input->post->get('hint', '', 'html');

                $options = new \stdClass();
                $options->class = $class;
                $options->seperator = $seperator;
                $options->allow_raw = $allow_raw;
                $options->allow_html = $allow_html;

                if ($type == 'select') {
                    $multi = Factory::getApplication()->input->getInt('multiple', 0);
                    $options->multiple = $multi;
                    $options->length = Factory::getApplication()->input->get('length', '', 'string');
                }

                if ($type == 'checkboxgroup' || $type == 'radiogroup') {
                    $options->horizontal = Factory::getApplication()->input->getBool('horizontal', 0);
                    $options->horizontal_length = Factory::getApplication()->input->get('horizontal_length', '', 'string');
                }

                $query = " `options`='" . base64_encode(serialize($options)) . "', `type`='" . $type . "', `change_type`='" . $type . "', `hint`=" . $this->getDatabase()->Quote($hint) . ", `default_value`=" . $this->getDatabase()->Quote($default_value) . " ";
                break;

            case 'upload':
                $this->getDatabase()->setQuery("Select upload_directory, protect_upload_directory From #__contentbuilder_ng_forms Where id = " . $this->_id);
                $setup = $this->getDatabase()->loadAssoc();

                // rel check for setup

                $tokens = '';

                $upl_ex = explode('|', $setup['upload_directory']);
                $setup['upload_directory'] = $upl_ex[0];

                $upl_ex2 = explode('|', trim(Factory::getApplication()->input->get('upload_directory', '', 'string')));

                Factory::getApplication()->input->set('upload_directory', $upl_ex2[0]);

                $is_relative = strpos(strtolower($setup['upload_directory']), '{cbsite}') === 0;
                $tmp_upload_directory = $setup['upload_directory'];
                $upload_directory = $is_relative ? str_replace(array('{CBSite}', '{cbsite}'), JPATH_SITE, $setup['upload_directory']) : $setup['upload_directory'];

                // rel check for element options
                $is_opt_relative = strpos(strtolower(trim(Factory::getApplication()->input->get('upload_directory', '', 'string'))), '{cbsite}') === 0;
                $tmp_opt_upload_directory = trim(Factory::getApplication()->input->get('upload_directory', '', 'string'));
                Factory::getApplication()->input->set('upload_directory', $is_relative ? str_replace(array('{CBSite}', '{cbsite}'), JPATH_SITE, trim(Factory::getApplication()->input->get('upload_directory', '', 'string'))) : trim(Factory::getApplication()->input->get('upload_directory', '', 'string')));


                $protect = $setup['protect_upload_directory'];

                if (!trim(Factory::getApplication()->input->get('upload_directory', '', 'string')) && !is_dir($upload_directory)) {

                    if (!is_dir(JPATH_SITE .'/media/contentbuilder_ng')) {
                        Folder::create(JPATH_SITE .'/media/contentbuilder_ng');
                        File::write(JPATH_SITE .'/media/contentbuilder_ng/index.html', $def = '');
                    }

                    if (!is_dir(JPATH_SITE .'/media/contentbuilder_ng/upload')) {
                        Folder::create(JPATH_SITE .'/media/contentbuilder_ng/upload');
                        File::write(JPATH_SITE .'/media/contentbuilder_ng/upload/index.html', $def = '');
                    }

                    $upload_directory = JPATH_SITE .'/media/contentbuilder_ng/upload';

                    if ($is_opt_relative) {
                        $is_relative = 1;
                        $tmp_upload_directory = '{CBSite}/media/contentbuilder_ng/upload';
                    }

                    if (isset($upl_ex[1])) {
                        $tokens = '|' . $upl_ex[1];
                    }

                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_FALLBACK_UPLOAD_CREATED') . ' (/media/contentbuilder_ng/upload' . ')', 'warning');

                } else if (trim(Factory::getApplication()->input->get('upload_directory', '', 'string')) != '' && !is_dir(ContentbuilderLegacyHelper::makeSafeFolder(Factory::getApplication()->input->get('upload_directory', '', 'string')))) {

                    $upload_directory = ContentbuilderLegacyHelper::makeSafeFolder(Factory::getApplication()->input->get('upload_directory', '', 'string'));

                    Folder::create($upload_directory);
                    File::write($upload_directory .'/index.html', $def = '');

                    if ($is_opt_relative) {
                        $is_relative = 1;
                        $tmp_upload_directory = $tmp_opt_upload_directory;
                    }

                    if (isset($upl_ex2[1])) {
                        $tokens = '|' . $upl_ex2[1];
                    }

                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_FALLBACK_UPLOAD_CREATED') . ' (' . $upload_directory . ')', 'warning');

                } else if (trim(Factory::getApplication()->input->get('upload_directory', '', 'string')) != '' && is_dir(ContentbuilderLegacyHelper::makeSafeFolder(Factory::getApplication()->input->get('upload_directory', '', 'string')))) {

                    $upload_directory = ContentbuilderLegacyHelper::makeSafeFolder(Factory::getApplication()->input->get('upload_directory', '', 'string'));

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

                    File::write(ContentbuilderLegacyHelper::makeSafeFolder($upload_directory) .'/.htaccess', $def = 'deny from all');

                } else if (!$protect && is_dir($upload_directory)) {
                    if (file_exists(ContentbuilderLegacyHelper::makeSafeFolder($upload_directory) .'/.htaccess')) {
                        File::delete(ContentbuilderLegacyHelper::makeSafeFolder($upload_directory) .'/.htaccess');
                    }

                }

                $default_value = Factory::getApplication()->input->get('default_value', '', 'string');
                $hint = Factory::getApplication()->input->post->get('hint', '', 'html');

                $options = new \stdClass();
                $options->upload_directory = is_dir($upload_directory) ? ($is_relative ? $tmp_upload_directory : $upload_directory) . $tokens : '';
                $options->allowed_file_extensions = Factory::getApplication()->input->get('allowed_file_extensions', '', 'string');
                $options->max_filesize = Factory::getApplication()->input->get('max_filesize', '', 'string');

                $query = " `options`='" . base64_encode(serialize($options)) . "', `type`='" . $type . "', `change_type`='" . $type . "', `hint`=" . $this->getDatabase()->Quote($hint) . ", `default_value`=" . $this->getDatabase()->Quote($default_value) . " ";
                break;
            case 'captcha':
                $default_value = Factory::getApplication()->input->get('default_value', '', 'string');
                $hint = Factory::getApplication()->input->post->get('hint', '', 'html');

                $options = new \stdClass();

                $query = " `options`='" . base64_encode(serialize($options)) . "', `type`='" . $type . "', `change_type`='" . $type . "', `hint`=" . $this->getDatabase()->Quote($hint) . ", `default_value`=" . $this->getDatabase()->Quote($default_value) . " ";
                break;
            case 'calendar':
                $length = Factory::getApplication()->input->get('length', '', 'string');
                $format = Factory::getApplication()->input->get('format', '', 'string');
                $transfer_format = Factory::getApplication()->input->get('transfer_format', '', 'string');
                $maxlength = Factory::getApplication()->input->getInt('maxlength', '');
                $readonly = Factory::getApplication()->input->getInt('readonly', 0);
                $default_value = Factory::getApplication()->input->post->get('default_value', '', 'raw');
                $hint = Factory::getApplication()->input->post->get('hint', '', 'html');

                $options = new \stdClass();
                $options->length = $length;
                $options->maxlength = $maxlength;
                $options->readonly = $readonly;
                $options->format = $format;
                $options->transfer_format = $transfer_format;

                $query = " `options`='" . base64_encode(serialize($options)) . "', `type`='calendar', `change_type`='calendar', `hint`=" . $this->getDatabase()->Quote($hint) . ", `default_value`=" . $this->getDatabase()->Quote($default_value) . " ";

                break;
            case 'hidden':
                $allow_raw = Factory::getApplication()->input->getInt('allow_encoding', 0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html = Factory::getApplication()->input->getInt('allow_encoding', 0) == 1 ? true : false;
                $default_value = Factory::getApplication()->input->post->get('default_value', '', 'raw');
                $hint = '';

                $options = new \stdClass();
                $options->allow_raw = $allow_raw;
                $options->allow_html = $allow_html;

                $query = " `options`='" . base64_encode(serialize($options)) . "', `type`='" . $type . "', `change_type`='" . $type . "', `hint`=" . $this->getDatabase()->Quote($hint) . ", `default_value`=" . $this->getDatabase()->Quote($default_value) . " ";
                break;
        }

        if ($query) {
            $custom_init_script = Factory::getApplication()->input->post->get('custom_init_script', '', 'raw');
            $custom_action_script = Factory::getApplication()->input->post->get('custom_action_script', '', 'raw');
            $custom_validation_script = Factory::getApplication()->input->post->get('custom_validation_script', '', 'raw');
            $validation_message = Factory::getApplication()->input->get('validation_message', '', 'string');
            $validations = Factory::getApplication()->input->get('validations', [], 'array');
            $validations = is_array($validations) ? $validations : [];

            $other = " `validations`=" . $this->getDatabase()->Quote(implode(',', $validations)) . ", ";
            $other .= " `custom_init_script`=" . $this->getDatabase()->Quote($custom_init_script) . ", ";
            $other .= " `custom_action_script`=" . $this->getDatabase()->Quote($custom_action_script) . ", ";
            $other .= " `custom_validation_script`=" . $this->getDatabase()->Quote($custom_validation_script) . ", ";
            $other .= " `validation_message`=" . $this->getDatabase()->Quote($validation_message) . ", ";

            $this->getDatabase()->setQuery("Update #__contentbuilder_ng_elements Set $other $query Where id = " . $this->_element_id);
            $this->getDatabase()->execute();
            return true;
        }
        return false;
    }

    /**
     * Publie ou dépublie plusieurs Elements.
     */
    public function publish(array $pks, int $value = 1): bool
    {
        $pks = (array) $pks;

        if (empty($pks)) {
            throw new \RuntimeException(
              Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED')
            );
        }

        ArrayHelper::toInteger($pks);
        $pks = array_filter($pks);

        Logger::info('DB publish', [
            'value' => $value,
            'pks'   => $pks,
        ]);

        $value = (int) $value;
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__contentbuilder_ng_elements'))
            ->set($db->quoteName('published') . ' = ' . $value)
            ->where($db->quoteName('id') . ' IN (' . implode(',', $pks) . ')');

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\Throwable $e) {
            Logger::exception($e);
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }


    public function fieldUpdate(array $pks, string $field, int $value): bool
    {
        $pks = (array) $pks;

        if (empty($pks)) {
            throw new \RuntimeException(
              Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED')
            );
        }

        ArrayHelper::toInteger($pks);
        $pks = array_filter($pks);

        Logger::info('DB publish', [
            'value' => $value,
            'pks'   => $pks,
        ]);

        $value = (int) $value;
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__contentbuilder_ng_elements'))
            ->set($db->quoteName($field) . ' = ' . (int) $value)
            ->where($db->quoteName('id') . ' IN (' . implode(',', $pks) . ')');

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (\Throwable $e) {
            Logger::exception($e);
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }
}
