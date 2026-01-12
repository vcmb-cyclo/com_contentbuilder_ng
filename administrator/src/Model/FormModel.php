<?php

/**
 * ContentBuilder Form Model.
 *
 * Handles CRUD and publish state for form in the admin interface.
 *
 * @package     ContentBuilder
 * @subpackage  Administrator.Model
 * @author      Markus Bopp / XDA+GIL
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */


namespace CB\Component\Contentbuilder\Administrator\Model;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;
use CB\Component\Contentbuilder\Administrator\Helper\Logger;
use CB\Component\Contentbuilder\Administrator\Table\FormTable;
use CB\Component\Contentbuilder\Administrator\Table\ElementoptionTable;

class FormModel extends AdminModel
{
    private $_default_list_states = array(
        array('id' => -1, 'action' => '', 'title' => 'State 1', 'color' => '60E309', 'published' => 1),
        array('id' => -2, 'action' => '', 'title' => 'State 2', 'color' => 'FCFC00', 'published' => 1),
        array('id' => -3, 'action' => '', 'title' => 'State 3', 'color' => 'FC0000', 'published' => 1),
        array('id' => -4, 'action' => '', 'title' => 'State 4', 'color' => 'FFFFFF', 'published' => 0),
        array('id' => -5, 'action' => '', 'title' => 'State 5', 'color' => 'FFFFFF', 'published' => 0),
        array('id' => -6, 'action' => '', 'title' => 'State 6', 'color' => 'FFFFFF', 'published' => 0),
        array('id' => -7, 'action' => '', 'title' => 'State 7', 'color' => 'FFFFFF', 'published' => 0),
        array('id' => -8, 'action' => '', 'title' => 'State 8', 'color' => 'FFFFFF', 'published' => 0),
        array('id' => -9, 'action' => '', 'title' => 'State 9', 'color' => 'FFFFFF', 'published' => 0),
        array('id' => -10, 'action' => '', 'title' => 'State 10', 'color' => 'FFFFFF', 'published' => 0)
    );

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->option = 'com_contentbuilder';
    }

    /**
     * Joomla 6 compatibility:
     * Force direct table instantiation because MVCFactory
     * resolves this component in Legacy mode.
     */

    public function getTable($name = 'Form', $prefix = 'Contentbuilder', $options = [])
    {
        $db = $this->getDatabase();
        switch ($name) {
            case 'Form':
                return new FormTable($db);

            case 'Elementoption':
                return new ElementoptionTable($db);
        }

        return parent::getTable($name, $prefix, $options);
    }

    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm(
            'com_contentbuilder.form',
            'form',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }


    protected function populateState(): void
    {
        // Déjà le parent.
        parent::populateState();

        // 2) ID depuis l'URL (standard Joomla en admin)
        $app   = Factory::getApplication();
        $input = $app->input;
        $formId = $input->getInt('id', 0);

        // 3) Fallback si on arrive via POST (save/apply etc.)
        if (!$formId) {
            $jform = $input->post->get('jform', [], 'array');
            $formId = (int) ($jform['id'] ?? 0);
        }

        // 4) État standard Joomla pour un AdminModel
        $this->setState($this->getName() . '.id', $formId);
    }


    protected function loadFormData()
    {
        $app = Factory::getApplication();
        $data = $app->getUserState('com_contentbuilder.edit.form.data', []);

        return !empty($data) ? $data : (array) $this->getItem();
    }


    public function reorder($pks = null, $delta = 0)
    {
        $table = $this->getTable();

        // Sécurité : clés primaires
        $pks = (array) $pks;

        foreach ($pks as $pk) {
            $table->load((int) $pk);

            // Réordonne à l'intérieur du même id
            $table->move($delta);
        }

        return true;
    }


    function setListEditable()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }


        $db = $this->getDatabase();
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_elements ' .
                '  Set editable = 1 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function setListListInclude()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_elements ' .
                '  Set list_include = 1 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function setListSearchInclude()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_elements ' .
                '  Set search_include = 1 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function setListNotLinkable()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_elements ' .
                '  Set linkable = 0 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function setListNotEditable()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_elements ' .
                '  Set editable = 0 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function setListNoListInclude()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_elements ' .
                '  Set list_include = 0 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function setListNoSearchInclude()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_elements ' .
                '  Set search_include = 0 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function getListStatesActionPlugins()
    {
        $db = $this->getDatabase();
        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_listaction' And `enabled` = 1");
        $res = $db->loadColumn();
        return $res;
    }

    function getThemePlugins()
    {
        $db = $this->getDatabase();

        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_themes' And `enabled` = 1");
        $res = $db->loadColumn();

        $i = 0;
        foreach ($res as $theme) {
            if ($theme == 'joomla3') {
                unset($res[$i]);
                $res = array_merge(array('joomla3'), $res);
                break;
            }
            $i++;
        }

        return $res;
    }

    function getVerificationPlugins()
    {
        $db = $this->getDatabase();
        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_verify' And `enabled` = 1");
        $res = $db->loadColumn();
        return $res;
    }


    /*
     * MAIN DETAILS AREA
     */

    public function getItem($formId = null)
    {
        if ($formId === null) {
            $formId = (int) $this->getState($this->getName() . '.id');
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__contentbuilder_forms'))
            ->where($db->quoteName('id') . ' = ' . (int)$formId);

        $db->setQuery($query);
        $data = $db->loadObject();

        if (!$data) {
            $data = new \stdClass();
            $data->id = 0;
            $data->type = null;
            $data->reference_id = null;
            $data->name = null;
            $data->tag = null;
            $data->details_template = null;
            $data->details_prepare = null;
            $data->intro_text = null;
            $data->title = null;
            $data->created = null;
            $data->modified = null;
            $data->metadata = true;
            $data->export_xls = null;
            $data->print_button = true;
            $data->created_by = null;
            $data->modified_by = null;
            $data->published = null;
            $data->display_in = null;
            $data->published_only = null;
            $data->show_id_column = true;
            $data->select_column = false;
            $data->edit_button = false;
            $data->list_states = false;
            $data->config = null;
            $data->editable_prepare = null;
            $data->editable_template = null;
            $data->use_view_name_as_title = false;
            $data->list_states = $this->_default_list_states;
            $data->own_only = false;
            $data->own_only_fe = false;
            $data->list_state = false;
            $data->list_publish = false;
            $data->initial_sort_order = -1;
            $data->initial_sort_order2 = -1;
            $data->initial_sort_order3 = -1;
            $data->initial_order_dir = 'desc';
            $data->default_section = 0;
            $data->default_category = 0;
            $data->create_articles = 1;
            $data->title_field = 0;
            $data->delete_articles = 1;
            $data->edit_by_type = 0;
            $data->email_notifications = 1;
            $data->email_update_notifications = 0;
            $data->limited_article_options = 1;
            $data->limited_article_options_fe = 1;
            $data->upload_directory = JPATH_SITE . '/media/contentbuilder/upload';
            $data->protect_upload_directory = 1;
            $data->limit_add = 0;
            $data->limit_edit = 0;
            $data->verification_required_view = 0;
            $data->verification_days_view = 0;
            $data->verification_required_new = 0;
            $data->verification_days_new = 0;
            $data->verification_required_edit = 0;
            $data->verification_days_edit = 0;
            $data->verification_url_new = '';
            $data->verification_url_view = '';
            $data->verification_url_edit = '';
            $data->default_lang_code = '*';
            $data->default_lang_code_ignore = 0;
            $data->show_all_languages_fe = 1;
            $data->list_language = 0;
            $data->default_publish_up_days = 0;
            $data->default_publish_down_days = 0;
            $data->default_access = 0;
            $data->default_featured = 0;
            $data->list_article = 0;
            $data->list_author = 0;
            $data->list_rating = 0;
            $data->email_template = '';
            $data->email_subject = '';
            $data->email_alternative_from = '';
            $data->email_alternative_fromname = '';
            $data->email_recipients = '';
            $data->email_recipients_attach_uploads = '';
            $data->email_html = '';

            $data->email_admin_template = '';
            $data->email_admin_subject = '';
            $data->email_admin_alternative_from = '';
            $data->email_admin_alternative_fromname = '';
            $data->email_admin_recipients = '';
            $data->email_admin_recipients_attach_uploads = '';
            $data->email_admin_html = '';

            $data->act_as_registration = 0;
            $data->registration_username_field = '';
            $data->registration_password_field = '';
            $data->registration_password_repeat_field = '';
            $data->registration_email_field = '';
            $data->registration_email_repeat_field = '';
            $data->registration_name_field = '';

            $data->auto_publish = 0;

            $data->force_login = 0;
            $data->force_url = '';

            $data->registration_bypass_plugin = '';
            $data->registration_bypass_plugin_params = '';
            $data->registration_bypass_verification_name = '';
            $data->registration_bypass_verify_view = '';

            $data->theme_plugin = '';

            $data->rating_slots = 5;

            $data->rand_date_update = null;

            $data->rand_update = '86400';

            $data->article_record_impact_publish = 0;
            $data->article_record_impact_language = 0;

            $data->allow_external_filter = 0;

            $data->show_filter = 1;

            $data->show_records_per_page = 1;

            $data->initial_list_limit = 20;

            $data->save_button_title = '';

            $data->apply_button_title = '';

            $data->filter_exact_match = 0;

            $data->ordering = 0;
        }

        $data->forms = array();
        $data->types = ContentbuilderLegacyHelper::getTypes();

        if ($data->type) {
            $data->forms = ContentbuilderLegacyHelper::getForms($data->type);
        }

        $data->form = null;
        if ($data->type && $data->reference_id) {
            $data->form = ContentbuilderLegacyHelper::getForm($data->type, $data->reference_id);
            if (!$data->form->exists) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'), 'error');
                Factory::getApplication()->redirect('index.php?option=com_contentbuilder&view=forms&limitstart=' . $this->getState('limitstart', 0));
            }
            if (isset($data->form->properties) && isset($data->form->properties->name)) {
                $data->type_name = $data->form->properties->name;
            } else {
                $data->type_name = '';
            }
            $data->title = $data->form->getPageTitle();

            // En charge de la sauvegarde de la partie Element
            if (is_object($data->form)) {
                ContentbuilderLegacyHelper::synchElements($data->id, $data->form);
                $elements_table = $this->getTable('Elementoption');
                $elements_table->reorder('form_id=' . $data->id);
            }
        }

        $db->setQuery(
            $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__contentbuilder_list_states'))
                ->where($db->quoteName('form_id') . ' = ' . $formId)
                ->order('id ASC')
        );

        $list_states = $db->loadAssocList();

        if (count($list_states)) {
            $data->list_states = $list_states;
        } else {
            $data->list_states = $this->_default_list_states;
        }

        $data->language_codes = ContentbuilderLegacyHelper::getLanguageCodes();

        $data->sectioncategories = $this->getOptions();
        $data->accesslevels = array();

        return $data;
    }

    private function getOptions()
    {
        // Initialise variables.
        $options = array();

        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('a.id AS value, a.title AS text, a.level');
        $query->from('#__categories AS a');
        $query->join('LEFT', '`#__categories` AS b ON a.lft > b.lft AND a.rgt < b.rgt');

        // Filter by the type
        $query->where('(a.extension = ' . $db->quote('com_content') . ' OR a.parent_id = 0)');

        $query->where('a.published IN (0,1)');
        $query->group('a.id');
        $query->order('a.lft ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\Exception $e) {
            Logger::exception($e);
            // Check for a database error.
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Pad the option text with spaces using depth level as a multiplier.
        for ($i = 0, $n = count($options); $i < $n; $i++) {
            // Translate ROOT
            if ($options[$i]->level == 0) {
                $options[$i]->text = Text::_('JGLOBAL_ROOT_PARENT');
            }

            $options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
        }

        if (isset($row) && !isset($options[0])) {
            if ($row->parent_id == '1') {
                $parent = new \stdClass();
                $parent->text = Text::_('JGLOBAL_ROOT_PARENT');
                array_unshift($options, $parent);
            }
        }

        return $options;
    }

    public function save($data): bool
    {
        $app = Factory::getApplication();
        $db = $this->getDatabase();
        error_log('MVCFactory=' . (is_object($this->getMVCFactory()) ? get_class($this->getMVCFactory()) : 'NULL'));
        error_log('getTable(Form) from ' . __METHOD__);

        $row = $this->getTable('Form', '');
        $form_id = 0;

        // $data = CBRequest::get('post');
        $input = $app->getInput();
        $id = $input->getInt('id');

        $form  = $this->getItem($id);

        $jform    = (array) $app->input->post->get('jform', [], 'array');
        $jformRaw = (array) $app->input->post->getRaw('jform');
        $jformHtml = (array) $app->input->post->getHtml('jform');

        // Override champs sensibles
        $jform['details_template']     = $jformRaw['details_template'] ?? '';
        $jform['editable_template']    = $jformRaw['editable_template'] ?? '';
        $jform['details_prepare']      = $jformRaw['details_prepare'] ?? '';
        $jform['editable_prepare']     = $jformRaw['editable_prepare'] ?? '';
        $jform['email_admin_template'] = $jformRaw['email_admin_template'] ?? '';
        $jform['email_template']       = $jformRaw['email_template'] ?? '';

        $jform['intro_text']           = $jformHtml['intro_text'] ?? '';
        $jform['editable']             = $jformHtml['editable'] ?? '';

        $data = $jform;

        #### SETTINGS
        $data['protect_upload_directory'] = !empty($jform['protect_upload_directory']) ? 1 : 0;
        $data['create_articles']          = !empty($jform['create_articles']) ? 1 : 0;

        ####### PERMISSIONS
        $data['own']      = (array) ($jform['own'] ?? []);
        $data['own_fe']   = (array) ($jform['own_fe'] ?? []);
        $data['perms']    = (array) ($jform['perms'] ?? []);
        $data['perms_fe'] = (array) ($jform['perms_fe'] ?? []);

        //$data['upload_directory'] = JPATH_SITE .'/media/contentbuilder/upload';
        //$data['protect_upload_directory'] = 1;

        // determine if it contains a replacement
        $tokens = '';

        $upl_ex = explode('|', $data['upload_directory']);
        $data['upload_directory'] = $upl_ex[0];

        $is_relative = strpos(strtolower($data['upload_directory']), '{cbsite}') === 0;

        $tmp_upload_directory = $data['upload_directory'];
        $upload_directory = $is_relative ? str_replace(array('{CBSite}', '{cbsite}'), JPATH_SITE, $data['upload_directory']) : $data['upload_directory'];
        $data['upload_directory'] = $upload_directory;

        $protect = $data['protect_upload_directory'];

        // if not existing, we create the fallback directory
        if (!is_dir($upload_directory)) {
            if (!is_dir(JPATH_SITE . '/media/contentbuilder')) {
                Folder::create(JPATH_SITE . '/media/contentbuilder');
                File::write(JPATH_SITE . '/media/contentbuilder/index.html', $def = '');
            }

            if (!is_dir(JPATH_SITE . '/media/contentbuilder/upload')) {
                Folder::create(JPATH_SITE . '/media/contentbuilder/upload');
                File::write(JPATH_SITE . '/media/contentbuilder/upload/index.html', $def = '');

                if ($protect) {
                    File::write(JPATH_SITE . '/media/contentbuilder/upload/.htaccess', $def = 'deny from all');
                }
            }

            $data['upload_directory'] = JPATH_SITE . '/media/contentbuilder/upload';

            if ($is_relative) {
                $tmp_upload_directory = '{CBSite}/media/contentbuilder/upload';
            }

            Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_FALLBACK_UPLOAD_CREATED') . ' (/media/contentbuilder/upload' . ')', 'warning');
        }

        if (isset($upl_ex[1])) {
            $tokens = '|' . $upl_ex[1];
        }

        if ($data['protect_upload_directory'] && is_dir(ContentbuilderLegacyHelper::makeSafeFolder($data['upload_directory']))) {
            if (!file_exists(ContentbuilderLegacyHelper::makeSafeFolder($data['upload_directory']) . '/index.html'))
                File::write(ContentbuilderLegacyHelper::makeSafeFolder($data['upload_directory']) . '/index.html', $def = '');
        }

        if ($data['protect_upload_directory'] && is_dir(ContentbuilderLegacyHelper::makeSafeFolder($data['upload_directory']))) {

            if (!file_exists(ContentbuilderLegacyHelper::makeSafeFolder($data['upload_directory']) . '/.htaccess'))
                File::write(ContentbuilderLegacyHelper::makeSafeFolder($data['upload_directory']) . '/.htaccess', $def = 'deny from all');
        } else {

            if (file_exists(ContentbuilderLegacyHelper::makeSafeFolder($data['upload_directory']) . '/.htaccess'))
                File::delete(ContentbuilderLegacyHelper::makeSafeFolder($data['upload_directory']) . '/.htaccess');
        }

        // reverting back to possibly including cbsite replacement
        $data['upload_directory'] = $tmp_upload_directory . $tokens;

        #### USERS
        $data['verification_required_view'] = !empty($jform['verification_required_view']) ? 1 : 0;
        $data['verification_required_new']  = !empty($jform['verification_required_new']) ? 1 : 0;
        $data['verification_required_edit'] = !empty($jform['verification_required_edit']) ? 1 : 0;

        #### MISC
        $data['show_all_languages_fe'] = !empty($jform['show_all_languages_fe']) ? 1 : 0;
        $data['default_lang_code_ignore'] = !empty($jform['default_lang_code_ignore']) ? 1 : 0;

        if (!$data['show_all_languages_fe'] && !$data['default_lang_code_ignore']) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_LANGUAGE_WARNING'), 'warning');
        }

        #### PERMISSIONS
        $gmap = array();
        $config = array();
        $config['permissions'] = array();
        $config['permissions_fe'] = array();
        $config['own'] = array();
        $config['own_fe'] = array();

        // Backend
        if (isset($data['own']) && isset($data['own']['view']) && intval($data['own']['view']) == 1) {
            $config['own']['view'] = true;
        }
        if (isset($data['own']) && isset($data['own']['edit']) && intval($data['own']['edit']) == 1) {
            $config['own']['edit'] = true;
        }
        if (isset($data['own']) && isset($data['own']['delete']) && intval($data['own']['delete']) == 1) {
            $config['own']['delete'] = true;
        }
        if (isset($data['own']) && isset($data['own']['state']) && intval($data['own']['state']) == 1) {
            $config['own']['state'] = true;
        }
        if (isset($data['own']) && isset($data['own']['publish']) && intval($data['own']['publish']) == 1) {
            $config['own']['publish'] = true;
        }
        if (isset($data['own']) && isset($data['own']['fullarticle']) && intval($data['own']['fullarticle']) == 1) {
            $config['own']['fullarticle'] = true;
        }
        if (isset($data['own']) && isset($data['own']['listaccess']) && intval($data['own']['listaccess']) == 1) {
            $config['own']['listaccess'] = true;
        }
        if (isset($data['own']) && isset($data['own']['new']) && intval($data['own']['new']) == 1) {
            $config['own']['new'] = true;
        }
        if (isset($data['own']) && isset($data['own']['language']) && intval($data['own']['language']) == 1) {
            $config['own']['language'] = true;
        }
        if (isset($data['own']) && isset($data['own']['rating']) && intval($data['own']['rating']) == 1) {
            $config['own']['rating'] = true;
        }

        // Frontend
        if (isset($data['own_fe']) && isset($data['own_fe']['view']) && intval($data['own_fe']['view']) == 1) {
            $config['own_fe']['view'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['edit']) && intval($data['own_fe']['edit']) == 1) {
            $config['own_fe']['edit'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['delete']) && intval($data['own_fe']['delete']) == 1) {
            $config['own_fe']['delete'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['state']) && intval($data['own_fe']['state']) == 1) {
            $config['own_fe']['state'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['publish']) && intval($data['own_fe']['publish']) == 1) {
            $config['own_fe']['publish'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['fullarticle']) && intval($data['own_fe']['fullarticle']) == 1) {
            $config['own_fe']['fullarticle'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['listaccess']) && intval($data['own_fe']['listaccess']) == 1) {
            $config['own_fe']['listaccess'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['new']) && intval($data['own_fe']['new']) == 1) {
            $config['own_fe']['new'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['language']) && intval($data['own_fe']['language']) == 1) {
            $config['own_fe']['language'] = true;
        }
        if (isset($data['own_fe']) && isset($data['own_fe']['rating']) && intval($data['own_fe']['rating']) == 1) {
            $config['own_fe']['rating'] = true;
        }

        $query = 'SELECT CONCAT( REPEAT(\'..\', COUNT(parent.id) - 1), node.title) as text, node.id as value'
            . ' FROM #__usergroups AS node, #__usergroups AS parent'
            . ' WHERE node.lft BETWEEN parent.lft AND parent.rgt'
            . ' GROUP BY node.id'
            . ' ORDER BY node.lft';
        $db->setQuery($query);
        $gmap = $db->loadObjectList();

        foreach ($gmap as $entry) {
            // Backend
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['listaccess']) && intval($data['perms'][$entry->value]['listaccess']) == 1) {
                $config['permissions'][$entry->value]['listaccess'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['view']) && intval($data['perms'][$entry->value]['view']) == 1) {
                $config['permissions'][$entry->value]['view'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['new']) && intval($data['perms'][$entry->value]['new']) == 1) {
                $config['permissions'][$entry->value]['new'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['edit']) && intval($data['perms'][$entry->value]['edit']) == 1) {
                $config['permissions'][$entry->value]['edit'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['delete']) && intval($data['perms'][$entry->value]['delete']) == 1) {
                $config['permissions'][$entry->value]['delete'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['state']) && intval($data['perms'][$entry->value]['state']) == 1) {
                $config['permissions'][$entry->value]['state'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['publish']) && intval($data['perms'][$entry->value]['publish']) == 1) {
                $config['permissions'][$entry->value]['publish'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['fullarticle']) && intval($data['perms'][$entry->value]['fullarticle']) == 1) {
                $config['permissions'][$entry->value]['fullarticle'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['language']) && intval($data['perms'][$entry->value]['language']) == 1) {
                $config['permissions'][$entry->value]['language'] = true;
            }
            if (isset($data['perms'][$entry->value]) && isset($data['perms'][$entry->value]['rating']) && intval($data['perms'][$entry->value]['rating']) == 1) {
                $config['permissions'][$entry->value]['rating'] = true;
            }


            // Frontend
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['listaccess']) && intval($data['perms_fe'][$entry->value]['listaccess']) == 1) {
                $config['permissions_fe'][$entry->value]['listaccess'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['view']) && intval($data['perms_fe'][$entry->value]['view']) == 1) {
                $config['permissions_fe'][$entry->value]['view'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['new']) && intval($data['perms_fe'][$entry->value]['new']) == 1) {
                $config['permissions_fe'][$entry->value]['new'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['edit']) && intval($data['perms_fe'][$entry->value]['edit']) == 1) {
                $config['permissions_fe'][$entry->value]['edit'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['delete']) && intval($data['perms_fe'][$entry->value]['delete']) == 1) {
                $config['permissions_fe'][$entry->value]['delete'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['state']) && intval($data['perms_fe'][$entry->value]['state']) == 1) {
                $config['permissions_fe'][$entry->value]['state'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['publish']) && intval($data['perms_fe'][$entry->value]['publish']) == 1) {
                $config['permissions_fe'][$entry->value]['publish'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['fullarticle']) && intval($data['perms_fe'][$entry->value]['fullarticle']) == 1) {
                $config['permissions_fe'][$entry->value]['fullarticle'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['language']) && intval($data['perms_fe'][$entry->value]['language']) == 1) {
                $config['permissions_fe'][$entry->value]['language'] = true;
            }
            if (isset($data['perms_fe'][$entry->value]) && isset($data['perms_fe'][$entry->value]['rating']) && intval($data['perms_fe'][$entry->value]['rating']) == 1) {
                $config['permissions_fe'][$entry->value]['rating'] = true;
            }
        }

        // remove perms
        if (isset($data['perms'])) {
            unset($data['perms']);
        }
        if (isset($data['perms_fe'])) {
            unset($data['perms_fe']);
        }
        if (isset($data['own'])) {
            unset($data['own']);
        }
        if (isset($data['own_fe'])) {
            unset($data['own_fe']);
        }

        ### PERMISSIONS END

        $data['list_states'] = (array) ($jform['list_states'] ?? []);
        $list_states = $data['list_states'];
        unset($data['list_states']);

        $data['default_category'] = (int) ($jform['sectioncategories'] ?? 0);

        $data['edit_by_type']        = !empty($jform['edit_by_type']) ? 1 : 0;
        if ($data['edit_by_type'] && $data['type'] == 'com_breezingforms') {
            if (isset($data['type_name'])) {
                $data['editable_template'] = "{BreezingForms: " . $data['type_name'] . "}";
            }
        }

        $data['act_as_registration'] = !empty($jform['act_as_registration']) ? 1 : 0;
        if ($data['edit_by_type'] && $data['act_as_registration']) {
            $data['act_as_registration'] = 0;
            Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_ACT_AS_REGISTRATION_WARNING'), 'warning');
        }

        if (
            $data['act_as_registration'] && (
                !$data['registration_name_field'] ||
                !$data['registration_username_field'] ||
                !$data['registration_email_field'] ||
                !$data['registration_email_repeat_field'] ||
                !$data['registration_password_field'] ||
                !$data['registration_password_repeat_field']
            )
        ) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_ACT_AS_REGISTRATION_MISSING_FIELDS_WARNING'), 'warning');
        }

        $data['email_notifications'] = !empty($jform['email_notifications']) ? 1 : 0;
        $data['limited_article_options']    = !empty($jform['limited_article_options']) ? 1 : 0;
        $data['limited_article_options_fe'] = !empty($jform['limited_article_options_fe']) ? 1 : 0;

        $data['own_only']    = !empty($jform['own_only']) ? 1 : 0;
        $data['own_only_fe'] = !empty($jform['own_only_fe']) ? 1 : 0;

        $data['config'] = base64_encode(serialize($config));

        //ContentbuilderLegacyHelper::createBackendMenuItem($form->id, $form->name, CBRequest::getInt('display_in',0));

        if (!empty($jform['create_sample'])) {
            $data['details_template'] .= ContentbuilderLegacyHelper::createDetailsSample($form->id, $form->form, $data['theme_plugin']);
        }

        if (!empty($jform['create_editable_sample'])) {
            $data['editable_template'] .= ContentbuilderLegacyHelper::createEditableSample($form->id, $form->form, $data['theme_plugin']);
        }

        if (!empty($jform['email_admin_create_sample'])) {
            $data['email_admin_template'] .= ContentbuilderLegacyHelper::createEmailSample(
                $form->id,
                $form->form,
                !empty($jform['email_admin_html'])
            );
        }

        if (!empty($jform['email_create_sample'])) {
            $data['email_template'] .= ContentbuilderLegacyHelper::createEmailSample(
                $form->id,
                $form->form,
                !empty($jform['email_html'])
            );
        }

        $data['email_html']            = !empty($jform['email_html']) ? 1 : 0;
        $data['email_admin_html']      = !empty($jform['email_admin_html']) ? 1 : 0;

        $data['show_filter']           = !empty($jform['show_filter']) ? 1 : 0;
        $data['show_records_per_page'] = !empty($jform['show_records_per_page']) ? 1 : 0;
        $data['metadata']              = !empty($jform['metadata']) ? 1 : 0;
        $data['export_xls']            = !empty($jform['export_xls']) ? 1 : 0;
        $data['print_button']          = !empty($jform['print_button']) ? 1 : 0;

        if (empty($jform['tag'])) {
            $data['tag'] = 'default';
        }

        if ($form->form) {
            $data['title'] = $form->form->getPageTitle();
        }

        $last_update = Factory::getDate()->toSql();
        $data['last_update'] = $last_update;

        try {
            if (!$row->bind($data)) {
                $this->setError($row->getError());
                return false;
            }
        } catch (\Throwable $e) {
            Logger::exception($e);
            // En debug tu peux garder le message brut
            $this->setError($e->getMessage());
            return false;
        }

        try {
            if (!$row->check()) {
                $this->setError($row->getError());
                return false;
            }
        } catch (\Throwable $e) {
            Logger::exception($e);
            // En debug tu peux garder le message brut
            $this->setError($e->getMessage());
            return false;
        }

        $form_id = 0;

        try {
            if (!$row->store()) {
                $this->setError($row->getError());
                return false;
            }
        } catch (\Throwable $e) {
            Logger::exception($e);
            // En debug tu peux garder le message brut
            $this->setError($e->getMessage());
            return false;
        }


        $form_id = (int) $row->{$row->getKeyName()};
        if ($form_id) {
            foreach ($list_states as $state_id => $item) {
                if (intval($state_id)) {
                    $db->setQuery("Update #__contentbuilder_list_states Set published = " . $db->Quote(isset($item['published']) && $item['published'] ? 1 : 0) . ", `title` = " . $db->Quote(stripslashes(strip_tags($item['title']))) . ", color = " . $db->Quote(stripslashes(strip_tags($item['color']))) . ", action = " . $db->Quote($item['action']) . " Where form_id = $form_id And id = " . intval($state_id));
                    $db->execute();
                }
            }

            // FALLBACK IF SOMEHOW REMOVED FROM DATABASE
            if (count($list_states) < count($this->_default_list_states)) {
                $add_count = count($this->_default_list_states) - count($list_states);
                for ($i = 0; $i <= $add_count; $i++) {
                    $db->setQuery("Insert Into #__contentbuilder_list_states (form_id,`title`,color,action) Values ($form_id," . $db->Quote('State') . "," . $db->Quote('FFFFFF') . "," . $db->Quote('') . ")");
                    $db->execute();
                }
            }
        } else {
            $form_id = $db->insertid();
            foreach ($list_states as $item) {
                $db->setQuery("Insert Into #__contentbuilder_list_states (form_id,`title`,color,action, published) Values ($form_id," . $db->Quote(stripslashes(strip_tags($item['title']))) . "," . $db->Quote($item['color']) . "," . $db->Quote($item['action']) . "," . $db->Quote(isset($item['published']) && $item['published'] ? 1 : 0) . ")");
                $db->execute();
            }

            // FALLBACK IF SOMEHOW REMOVED FROM DATABASE
            if (count($list_states) < count($this->_default_list_states)) {
                $add_count = count($this->_default_list_states) - count($list_states);
                for ($i = 0; $i <= $add_count; $i++) {
                    $db->setQuery("Insert Into #__contentbuilder_list_states (form_id,`title`,color,action) Values ($form_id," . $db->Quote('State') . "," . $db->Quote('FFFFFF') . "," . $db->Quote('') . ")");
                    $db->execute();
                }
            }
        }

        // is the list states empty?
        $db->setQuery("Select id From #__contentbuilder_list_states Where form_id = " . $form_id . " Limit 1");
        $has_states = $db->loadResult();
        if (!$has_states) {
            $add_count = count($this->_default_list_states);
            for ($i = 0; $i <= $add_count; $i++) {
                $db->setQuery("Insert Into #__contentbuilder_list_states (form_id,`title`,color,action) Values ($form_id," . $db->Quote('State') . "," . $db->Quote('FFFFFF') . "," . $db->Quote('') . ")");
                $db->execute();
            }
        }

        $row->reorder();

        $item_wrapper = (array) ($jformRaw['itemWrapper'] ?? []);    // wrapper peut être vide

        $wordwrap     = (array) ($jform['itemWordwrap'] ?? []);
        $labels       = (array) ($jform['itemLabels'] ?? []);
        $order_types  = (array) ($jform['itemOrderTypes'] ?? []);
        $order        = (array) ($jform['order'] ?? []);

        // ✅ union de toutes les clés possibles
        $elementIds = array_unique(array_merge(
            array_keys($labels),
            array_keys($wordwrap),
            array_keys($order_types),
            array_keys($item_wrapper),
            array_keys($order)
        ));

        ArrayHelper::toInteger($wordwrap);

        foreach ($elementIds as $elementId) {
            $elementId = (int) $elementId;
            if ($elementId <= 0) {
                continue;
            }

            $label     = $labels[$elementId]      ?? '';
            $wrap      = $wordwrap[$elementId]    ?? 0;
            $otype     = $order_types[$elementId] ?? '';
            $wrapper   = $item_wrapper[$elementId] ?? ''; // peut ne pas exister
            $ord       = isset($order[$elementId]) ? (int) $order[$elementId] : null;

            $db->setQuery(
                "UPDATE #__contentbuilder_elements
                SET `order_type`    = " . $db->quote($otype) . ",
                    `label`         = " . $db->quote($label) . ",
                    `wordwrap`      = " . (int) $wrap . ",
                    `item_wrapper`  = " . $db->quote(trim($wrapper)) . ",
                    `ordering`      = " . (int) ($ord ?? 0) . "
                WHERE form_id = " . (int) $form_id . "
                AND id      = " . (int) $elementId
            );
            $db->execute();

        }

        if ($form_id > 0) {
            $this->setState($this->getName() . '.id', $form_id);
            $app->input->set('id', $form_id);

            $jform['id'] = $form_id;
            $app->input->post->set('jform', $jform);
        }


        // ✅ IMPORTANT : respecter la signature attendue (bool)
        return $form_id > 0;
    }


    public function delete(&$pks)
    {
        if (empty($pks)) {
            throw new \RuntimeException(
                Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED')
            );
        }

        $pks = array_filter(array_map('intval', $pks));

        return $this->deleteByIds($pks);
    }

    private function deleteByIds(array $cids): bool
    {
        // Mode Joomla5
        $is15 = false;

        $row = $this->getTable('Form', '');
        $db = $this->getDatabase();

        foreach ($cids as $cid) {
            $db->setQuery("Select article.article_id From #__contentbuilder_articles As article, #__contentbuilder_forms As form Where form.delete_articles > 0 And form.id = article.form_id And article.form_id = " . intval($cid));
            $articles = $db->loadColumn();
            if (count($articles)) {
                $article_items = array();
                foreach ($articles as $article) {
                    $article_items[] = $db->Quote('com_content.article.' . $article);
                    $table = Table::getInstance('content');
                    // Trigger the onContentBeforeDelete event.
                    if (!$is15 && $table->load($article)) {
                        Factory::getApplication()->getDispatcher()->dispatch('onContentBeforeDelete', array('com_content.article', $table));
                    }
                    $db->setQuery("Delete From #__content Where id = " . intval($article));
                    $db->execute();
                    // Trigger the onContentAfterDelete event.
                    $table->reset();
                    if (!$is15) {
                        Factory::getApplication()->getDispatcher()->dispatch('onContentAfterDelete', array('com_content.article', $table));
                    }
                }
                $db->setQuery("Delete From #__assets Where `name` In (" . implode(',', $article_items) . ")");
                $db->execute();
            }


            $db->setQuery("
                Delete
                    `elements`.*
                From
                    #__contentbuilder_elements As `elements`
                Where
                    `elements`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `states`.*
                From
                    #__contentbuilder_list_states As `states`
                Where
                    `states`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `records`.*
                From
                    #__contentbuilder_list_records As `records`
                Where
                    `records`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `access`.*
                From
                    #__contentbuilder_resource_access As `access`
                Where
                    `access`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `users`.*
                From
                    #__contentbuilder_users As `users`
                Where
                    `users`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `users`.*
                From
                    #__contentbuilder_registered_users As `users`
                Where
                    `users`.form_id = " . $cid);

            $db->execute();

            $this->getTable('Elementoption')->reorder('form_id = ' . $cid);

            $db->setQuery("Delete From #__menu Where `link` = 'index.php?option=com_contentbuilder&view=list&id=" . intval($cid) . "'");
            $db->execute();
            $db->setQuery("Select count(id) From #__menu Where `link` Like 'index.php?option=com_contentbuilder&view=list&id=%'");
            $amount = $db->loadResult();
            if (!$amount) {
                $db->setQuery("Delete From #__menu Where `link` = 'index.php?option=com_contentbuilder&viewcontainer=true'");
                $db->execute();
            }

            if (!$row->delete($cid)) {
                $this->setError($row->getErrorMsg());
                return false;
            }
        }

        $row->reorder();

        // article deletion if required
        $db->setQuery("Select `id` From #__contentbuilder_forms");
        $references = $db->loadColumn();

        $cnt = count($references);
        if ($cnt) {
            $new_items = array();
            for ($i = 0; $i < $cnt; $i++) {
                $new_items[] = $db->Quote($references[$i]);
            }
            $db->setQuery("Delete From #__contentbuilder_articles Where `form_id` Not In (" . implode(',', $new_items) . ") ");
            $db->execute();
        } else {
            $db->setQuery("Delete From #__contentbuilder_articles");
            $db->execute();
        }

        return true;
    }

    public function move($direction): bool
    {
        $pk = (int) $this->getState($this->getName() . '.id');

        $row = $this->getTable('Form', '');

        if (!$row->load($pk)) {
            $this->setError($row->getError());
            return false;
        }

        if (!$row->move((int) $direction)) {
            $this->setError($row->getError());
            return false;
        }

        return true;
    }



    public function copy(array $pks): bool
    {
        if (empty($pks)) {
            throw new \RuntimeException(
                Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED')
            );
        }

        $pks = array_filter(array_map('intval', $pks));

        return $this->copyByIds($pks);
    }

    private function copyByIds($cids): bool
    {
        $cids = CBRequest::getVar('cid', array(), '', 'array');
        ArrayHelper::toInteger($cids);

        if (!count($cids))
            return false;

        $db = $this->getDatabase();
        $table = $this->getTable('Form', '');
        $db->setQuery(' Select * From #__contentbuilder_forms ' .
            '  Where id In ( ' . implode(',', $cids) . ')');
        $result = $db->loadObjectList();

        foreach ($result as $obj) {
            $origId = $obj->id;
            unset($obj->id);

            $obj->name = 'Copy of ' . $obj->name;
            $obj->published = 0;
            $db->insertObject('#__contentbuilder_forms', $obj);
            $insertId = $db->insertid();

            // elements
            $db->setQuery(' Select * From #__contentbuilder_elements ' .
                '  Where form_id = ' . $origId);
            $elements = $db->loadObjectList();
            foreach ($elements as $element) {
                unset($element->id);
                $element->form_id = $insertId;
                $db->insertObject('#__contentbuilder_elements', $element);
            }

            // list states
            $db->setQuery(' Select * From #__contentbuilder_list_states ' .
                '  Where form_id = ' . $origId);
            $elements = $db->loadObjectList();
            foreach ($elements as $element) {
                unset($element->id);
                $element->form_id = $insertId;
                $db->insertObject('#__contentbuilder_list_states', $element);
            }
            // XDA-Gil fix 'Copy of Form' in Component Menu in Backen CB View
            // ContentbuilderLegacyHelper::createBackendMenuItem($insertId, $obj->name, true);
        }

        $table->reorder();

        return true;
    }
}
