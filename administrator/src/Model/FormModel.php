<?php

/**
 * ContentBuilder Form Model.
 *
 * Handles CRUD and publish state for form in the admin interface.
 *
 * @package     ContentBuilder NG
 * @subpackage  Administrator.Model
 * @author      Markus Bopp / XDA+GIL
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */


namespace CB\Component\Contentbuilder_ng\Administrator\Model;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use CB\Component\Contentbuilder_ng\Administrator\CBRequest;
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderLegacyHelper;
use CB\Component\Contentbuilder_ng\Administrator\Helper\Logger;

class FormModel extends AdminModel
{
    protected int $formId = 0;

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

    public function __construct(
        $config,
        MVCFactoryInterface $factory
    ) {
        // IMPORTANT : on transmet factory/app/input à ListModel
        parent::__construct($config, $factory);
        $this->option = 'com_contentbuilder_ng';
    }

    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm(
            'com_contentbuilder_ng.form',
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
        $data = $app->getUserState('com_contentbuilder_ng.edit.form.data', []);

        return !empty($data) ? $data : (array) $this->getItem();
    }


    function setListEditable()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }


        $db = $this->getDatabase();
        $items = Factory::getApplication()->input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_ng_elements ' .
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
        $items = Factory::getApplication()->input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_ng_elements ' .
                '  Set list_include = 1 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function setListSearchInclude()
    {
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $items = Factory::getApplication()->input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_ng_elements ' .
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
        $items = Factory::getApplication()->input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_ng_elements ' .
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
        $items = Factory::getApplication()->input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_ng_elements ' .
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
        $items = Factory::getApplication()->input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_ng_elements ' .
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
        $items = Factory::getApplication()->input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $db->setQuery(' Update #__contentbuilder_ng_elements ' .
                '  Set search_include = 0 Where form_id = ' . $formId . ' And id In ( ' . implode(',', $items) . ')');
            $db->execute();
        }
    }

    function getListStatesActionPlugins()
    {
        $db = $this->getDatabase();
        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_ng_listaction' And `enabled` = 1");
        $res = $db->loadColumn();
        return $res;
    }

    function getThemePlugins()
    {
        $db = $this->getDatabase();

        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_ng_themes' And `enabled` = 1");
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
        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_ng_verify' And `enabled` = 1");
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
            ->from($db->quoteName('#__contentbuilder_ng_forms'))
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
            $data->upload_directory = JPATH_SITE . '/media/contentbuilder_ng/upload';
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
            if (!$data->form || !$data->form->exists) {
                if ((string) $data->type === 'com_breezingforms') {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf('COM_CONTENTBUILDER_NG_BREEZINGFORMS_SOURCE_NOT_FOUND', (int) $data->reference_id),
                        'warning'
                    );
                } else {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_FORM_NOT_FOUND'), 'warning');
                }

                // Keep the form editable and let admin choose a new source.
                $data->reference_id = 0;
                $data->form = null;
                $data->type_name = '';
            } else {
                if (isset($data->form->properties) && isset($data->form->properties->name)) {
                    $data->type_name = trim($data->form->properties->name);
                } else {
                    $data->type_name = '';
                }
                $data->title = trim($data->form->getPageTitle());

                // En charge de la sauvegarde de la partie Element
                if (is_object($data->form)) {
                    ContentbuilderLegacyHelper::synchElements($data->id, $data->form);
                    $elements_table = $this->getTable('Elementoption');
                    $elements_table->reorder('form_id=' . $data->id);
                }
            }
        }

        $db->setQuery(
            $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__contentbuilder_ng_list_states'))
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


    // Nettoie les données avant sauvegarde.
    protected function prepareTable($table): void
    {
        parent::prepareTable($table);

        $now  = Factory::getDate()->toSql();
        $user = Factory::getApplication()->getIdentity();

        $table->name  = trim((string) $table->name);
        $table->title = trim((string) $table->title);
        $table->tag   = trim((string) ($table->tag ?? ''));
        
        // Si tes champs existent bien en JTable (c'est le cas)
        if (empty($table->id)) {
            // Création
            if (empty($table->created)) {
                $table->created = $now;
            }
            if (empty($table->created_by)) {
                $table->created_by = (int) $user->id;
            }

            // En création, tu peux laisser modified vide (standard) ou l'aligner sur created
            // $table->modified = null;
            // $table->modified_by = 0;
        } else {
            // Modification
            $table->modified = $now;
            $table->modified_by = (int) $user->id;
        }
    }

    public function save($data): bool
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        $db    = $this->getDatabase();

        // 1) Récupération standard + RAW/HTML (nécessaire pour tes éditeurs)
        $jform     = (array) $input->post->get('jform', [], 'array');
        $jformRaw  = (array) $input->post->getRaw('jform');
        $jformHtml = (array) $input->post->getHtml('jform');

        // ID (standard admin)
        $id = (int) ($input->getInt('id', 0) ?: (int) ($jform['id'] ?? 0));
        $jform['id'] = $id;

        Logger::info('Form save flags', [
            'task' => $input->getCmd('task', ''),
            'create_sample' => $jform['create_sample'] ?? null,
            'create_sample_raw' => $jformRaw['create_sample'] ?? null,
            'theme_plugin' => $jform['theme_plugin'] ?? null,
        ]);

        // 2) Override champs sensibles : on force RAW pour les templates/scripts
        $rawFields = [
            'intro_text',
            'details_template',
            'editable_template',
            'details_prepare',
            'editable_prepare',
            'email_admin_template',
            'email_template',
        ];

        foreach ($rawFields as $f) {
            if (array_key_exists($f, $jformRaw)) {
                $jform[$f] = $jformRaw[$f];
            }
        }

        // 3) Normalisation des checkboxes / flags (standardiser en 0/1)
        $boolFields = [
            'protect_upload_directory',
            'create_articles',
            'verification_required_view',
            'verification_required_new',
            'verification_required_edit',
            'show_all_languages_fe',
            'default_lang_code_ignore',
            'edit_by_type',
            'act_as_registration',
            'email_notifications',
            'email_update_notifications',
            'limited_article_options',
            'limited_article_options_fe',
            'own_only',
            'own_only_fe',
            'email_html',
            'email_admin_html',
            'show_filter',
            'show_records_per_page',
            'metadata',
            'export_xls',
            'print_button',
            'auto_publish',
            'force_login',
            'protect_upload_directory',
            'allow_external_filter',
            'show_id_column',
            'select_column',
            'edit_button',
            'list_state',
            'list_publish',
            'list_rating',
            'list_language',
            'list_article',
            'list_author',
        ];

        foreach ($boolFields as $bf) {
            if (array_key_exists($bf, $jform)) {
                $jform[$bf] = !empty($jform[$bf]) ? 1 : 0;
            }
        }

        // Tag défaut
        if (empty($jform['tag'])) {
            $jform['tag'] = 'default';
        }

        // 4) Capture list_states puis on l'enlève du bind principal (car stocké ailleurs)
        $list_states = (array) ($jform['list_states'] ?? []);
        unset($jform['list_states']);

        // 5) Upload directory : on garde ta logique, mais en version compacte
        //    (ton système {CBSite} + fallback /media/contentbuilder_ng/upload)
        if (!isset($jform['upload_directory']) || $jform['upload_directory'] === '') {
            $jform['upload_directory'] = 'media/com_contentbuilder_ng/upload';
        }

        $upl_ex = explode('|', (string) $jform['upload_directory']);
        $basePath = $upl_ex[0];
        $tokens   = isset($upl_ex[1]) ? '|' . $upl_ex[1] : '';

        $is_relative = (stripos($basePath, '{cbsite}') === 0);
        $tmp_upload_directory = $basePath;

        $resolved = $is_relative
            ? str_ireplace(['{CBSite}', '{cbsite}'], JPATH_SITE, $basePath)
            : $basePath;

        $protect = !empty($jform['protect_upload_directory']);

        // Crée fallback si dossier inexistant
        if (!is_dir($resolved)) {
            // /media/contentbuilder
            if (!is_dir(JPATH_SITE . '/media/contentbuilder_ng')) {
                Folder::create(JPATH_SITE . '/media/contentbuilder_ng');
                File::write(JPATH_SITE . '/media/contentbuilder_ng/index.html', '');
            }

            // /media/contentbuilder_ng/upload
            if (!is_dir(JPATH_SITE . '/media/contentbuilder_ng/upload')) {
                Folder::create(JPATH_SITE . '/media/contentbuilder_ng/upload');
                File::write(JPATH_SITE . '/media/contentbuilder_ng/upload/index.html', '');

                if ($protect) {
                    File::write(JPATH_SITE . '/media/contentbuilder_ng/upload/.htaccess', 'deny from all');
                }
            }

            // On force le chemin fallback
            $resolved = JPATH_SITE . '/media/contentbuilder_ng/upload';
            $jform['upload_directory'] = $resolved;

            // Et on restaure la version "tokenisée" si c'était relatif
            if ($is_relative) {
                $tmp_upload_directory = '{CBSite}/media/contentbuilder_ng/upload';
            }

            $app->enqueueMessage(
                Text::_('COM_CONTENTBUILDER_NG_FALLBACK_UPLOAD_CREATED') . ' (/media/contentbuilder_ng/upload)',
                'warning'
            );
        }

        // Applique protection (safe folder) si besoin
        if ($protect) {
            $safe = ContentbuilderLegacyHelper::makeSafeFolder($resolved);

            if (is_dir($safe)) {
                if (!file_exists($safe . '/index.html')) {
                    File::write($safe . '/index.html', '');
                }
                if (!file_exists($safe . '/.htaccess')) {
                    File::write($safe . '/.htaccess', 'deny from all');
                }
            }
        } else {
            $safe = ContentbuilderLegacyHelper::makeSafeFolder($resolved);
            if (file_exists($safe . '/.htaccess')) {
                File::delete($safe . '/.htaccess');
            }
        }

        // On restaure le format legacy upload_directory avec tokens (comme avant)
        $jform['upload_directory'] = $tmp_upload_directory . $tokens;

        // 6) Permissions/config legacy : on reconstruit proprement (sans 200 if)
        $config = [
            'permissions'    => [],
            'permissions_fe' => [],
            'own'            => [],
            'own_fe'         => [],
        ];

        // own / own_fe (valeurs bool)
        $ownKeys = [
            'view',
            'edit',
            'delete',
            'state',
            'publish',
            'fullarticle',
            'listaccess',
            'new',
            'language',
            'rating'
        ];
        $own    = (array) ($jform['own'] ?? []);
        $own_fe = (array) ($jform['own_fe'] ?? []);

        foreach ($ownKeys as $k) {
            $config['own'][$k]    = !empty($own[$k]) ? true : false;
            $config['own_fe'][$k] = !empty($own_fe[$k]) ? true : false;
        }

        // permissions / permissions_fe par usergroup (structure: perms[group][action]=1)
        $perms    = (array) ($jform['perms'] ?? []);
        $perms_fe = (array) ($jform['perms_fe'] ?? []);

        // Liste des groupes (tu l’utilises déjà)
        $q = $db->getQuery(true)
            ->select("node.id AS value")
            ->from($db->quoteName('#__usergroups', 'node'));
        $db->setQuery($q);
        $groupIds = $db->loadColumn() ?: [];

        foreach ($groupIds as $gid) {
            $gid = (int) $gid;

            $config['permissions'][$gid] = [];
            $config['permissions_fe'][$gid] = [];

            foreach ($ownKeys as $k) {
                $config['permissions'][$gid][$k] =
                    !empty($perms[$gid][$k]) ? true : false;

                $config['permissions_fe'][$gid][$k] =
                    !empty($perms_fe[$gid][$k]) ? true : false;
            }
        }

        // Nettoyage des champs temporaires (on ne les stocke pas en colonnes)
        unset($jform['perms'], $jform['perms_fe'], $jform['own'], $jform['own_fe']);

        $formObj = null;
        if (!empty($jform['type']) && !empty($jform['reference_id'])) {
            $formObj = ContentbuilderLegacyHelper::getForm($jform['type'], $jform['reference_id']);
        }

        $createSample = !empty($jform['create_sample']);
        if ($createSample) {
            if (!$formObj) {
                $app->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_FORM_NOT_FOUND'), 'warning');
            }
            $sample = ContentbuilderLegacyHelper::createDetailsSample($id, $formObj, $jform['theme_plugin']);
            Logger::info('Details sample requested', [
                'form_id' => $id,
                'theme_plugin' => $jform['theme_plugin'] ?? null,
                'sample_length' => is_string($sample) ? strlen($sample) : null,
            ]);
            if ($sample === '' || $sample === null) {
                $app->enqueueMessage('Details sample generation returned empty output (theme: ' . ($jform['theme_plugin'] ?? 'none') . ').', 'warning');
            }
            $jform['details_template'] = (string) $sample;
        }

        $createEditableSample = !empty($jform['create_editable_sample']);
        if ($createEditableSample) {
            if (!$formObj) {
                $app->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_FORM_NOT_FOUND'), 'warning');
            }
            $jform['editable_template'] = ContentbuilderLegacyHelper::createEditableSample($id, $formObj, $jform['theme_plugin']);
        }

        $emailAdminHtml = !empty($jform['email_admin_html']);
        $emailAdminTemplate = !empty($jform['email_admin_create_sample']);
        if ($emailAdminTemplate) {
            if (!$formObj) {
                $app->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_FORM_NOT_FOUND'), 'warning');
            }
            $jform['email_admin_template'] = ContentbuilderLegacyHelper::createEmailSample($id, $formObj, $emailAdminHtml);
        }

        $emailCreateSample = !empty($jform['email_create_sample']);
        if ($emailCreateSample) {
            if (!$formObj) {
                $app->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_FORM_NOT_FOUND'), 'warning');
            }
            $jform['email_template'] = ContentbuilderLegacyHelper::createEmailSample($id, $formObj, Factory::getApplication()->input->getBool('email_html', false));
        }

        // Config legacy
        $jform['config'] = base64_encode(serialize($config));

        // Last_update.
        $jform['last_update'] = Factory::getDate()->toSql();

        // 7) Ajustements legacy divers (si nécessaire)
        // - default_category depuis sectioncategories (comme avant)
        if (isset($jform['sectioncategories'])) {
            $jform['default_category'] = (int) $jform['sectioncategories'];
            unset($jform['sectioncategories']);
        }

        // 8) Sauvegarde STANDARD Joomla (bind/check/store + prepareTable() + events)
        // IMPORTANT: parent::save() prend un array "jform-like"
        $ok = parent::save($jform);
        if (!$ok) {
            return false;
        }

        // 9) POST-SAVE : on récupère l'ID officiel
        $formId = (int) $this->getState($this->getName() . '.id');
        if ($formId < 1) {
            // ne devrait pas arriver, mais on sécurise
            $this->setError('Form ID not available after save');
            return false;
        }

        // 10) Mettre à jour/insérer list_states (même logique que ton code)
        if (!empty($list_states)) {
            foreach ($list_states as $state_id => $item) {
                $sid = (int) $state_id;
                if ($sid > 0) {
                    $db->setQuery(
                        "UPDATE #__contentbuilder_ng_list_states
                     SET published = " . (isset($item['published']) && $item['published'] ? 1 : 0) . ",
                         `title`    = " . $db->quote(stripslashes(strip_tags((string) ($item['title'] ?? '')))) . ",
                         color      = " . $db->quote(stripslashes(strip_tags((string) ($item['color'] ?? 'FFFFFF')))) . ",
                         action     = " . $db->quote((string) ($item['action'] ?? '')) . "
                     WHERE form_id = " . (int) $formId . " AND id = " . (int) $sid
                    );
                    $db->execute();
                }
            }
        }

        // Fallback: si pas assez d'états, on complète
        $db->setQuery("SELECT COUNT(id) FROM #__contentbuilder_ng_list_states WHERE form_id = " . (int) $formId);
        $existingCount = (int) $db->loadResult();

        $defaultCount = count($this->_default_list_states);
        if ($existingCount < 1) {
            // rien du tout -> on insert tout
            for ($i = 0; $i < $defaultCount; $i++) {
                $db->setQuery(
                    "INSERT INTO #__contentbuilder_ng_list_states (form_id, `title`, color, action)
                 VALUES (" . (int) $formId . ", " . $db->quote('State') . ", " . $db->quote('FFFFFF') . ", " . $db->quote('') . ")"
                );
                $db->execute();
            }
        } elseif ($existingCount < $defaultCount) {
            // on complète le delta
            $add = $defaultCount - $existingCount;
            for ($i = 0; $i < $add; $i++) {
                $db->setQuery(
                    "INSERT INTO #__contentbuilder_ng_list_states (form_id, `title`, color, action)
                 VALUES (" . (int) $formId . ", " . $db->quote('State') . ", " . $db->quote('FFFFFF') . ", " . $db->quote('') . ")"
                );
                $db->execute();
            }
        }

        // 11) Reorder de la table forms (si tu le faisais)
        try {
            $row = $this->getTable('Form', '');
            $row->reorder();
        } catch (\Throwable $e) {
            // non bloquant
        }

        // 12) Update elements (ton bloc, inchangé mais avec $formId)
        $item_wrapper = (array) ($jformRaw['itemWrapper'] ?? []);
        $wordwrap     = (array) ($jform['itemWordwrap'] ?? []);
        $labels       = (array) ($jform['itemLabels'] ?? []);
        $order_types  = (array) ($jform['itemOrderTypes'] ?? []);
        $order        = (array) ($jform['order'] ?? []);

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

            $label   = $labels[$elementId] ?? '';
            $wrap    = $wordwrap[$elementId] ?? 0;
            $otype   = $order_types[$elementId] ?? '';
            $wrapper = $item_wrapper[$elementId] ?? '';
            $ord     = isset($order[$elementId]) ? (int) $order[$elementId] : 0;

            $db->setQuery(
                "UPDATE #__contentbuilder_ng_elements
             SET `order_type`   = " . $db->quote((string) $otype) . ",
                 `label`        = " . $db->quote((string) $label) . ",
                 `wordwrap`     = " . (int) $wrap . ",
                 `item_wrapper` = " . $db->quote(trim((string) $wrapper)) . ",
                 `ordering`     = " . (int) $ord . "
             WHERE form_id = " . (int) $formId . " AND id = " . (int) $elementId
            );
            $db->execute();
        }

        // 13) Synchronisation éventuelle des éléments (si tu en as besoin)
        // IMPORTANT: Evite de le faire dans getItem() (effets de bord).
        // Ici tu peux le déclencher si tu as type/reference_id stables :
        // - récupérer l'item fraîchement sauvegardé (standard)
        // $item = $this->getItem($formId);
        // if (!empty($item->type) && !empty($item->reference_id)) {
        //     $form = ContentbuilderLegacyHelper::getForm($item->type, $item->reference_id);
        //     if (is_object($form) && !empty($form->exists)) {
        //         ContentbuilderLegacyHelper::synchElements($formId, $form);
        //     }
        // }

        // 14) Mettre à jour l'état du modèle / input (utile pour save2new/apply)
        $this->setState($this->getName() . '.id', $formId);
        $input->set('id', $formId);

        $jform['id'] = $formId;
        $input->post->set('jform', $jform);

        return true;
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
        $row = $this->getTable('Form', '');
        $db = $this->getDatabase();

        foreach ($cids as $cid) {
            $db->setQuery("Select article.article_id From #__contentbuilder_ng_articles As article, #__contentbuilder_ng_forms As form Where form.delete_articles > 0 And form.id = article.form_id And article.form_id = " . intval($cid));
            $articles = $db->loadColumn();
            if (count($articles)) {
                $article_items = array();
                foreach ($articles as $article) {
                    $article_items[] = $db->Quote('com_content.article.' . $article);
                    $table = Table::getInstance('content');

                    // Trigger the onContentBeforeDelete event.
                    if ($table->load($article)) {
                        $dispatcher = Factory::getApplication()->getDispatcher();
                        $eventObj = new \Joomla\CMS\Event\Model\BeforeDeleteEvent('onContentBeforeDelete', [
                            'context' => 'com_content.article',
                            'subject' => $table,
                        ]);
                        $dispatcher->dispatch('onContentBeforeDelete', $eventObj);
                    }
                    $db->setQuery("Delete From #__content Where id = " . intval($article));
                    $db->execute();

                    // Trigger the onContentAfterDelete event.
                    $table->reset();
                    $dispatcher = Factory::getApplication()->getDispatcher();
                    $eventObj = new \Joomla\CMS\Event\Model\AfterDeleteEvent('onContentAfterDelete', [
                        'context' => 'com_content.article',
                        'subject' => $table,
                    ]);
                    $dispatcher->dispatch('onContentAfterDelete', $eventObj);
                }
                $db->setQuery("Delete From #__assets Where `name` In (" . implode(',', $article_items) . ")");
                $db->execute();
            }


            $db->setQuery("
                Delete
                    `elements`.*
                From
                    #__contentbuilder_ng_elements As `elements`
                Where
                    `elements`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `states`.*
                From
                    #__contentbuilder_ng_list_states As `states`
                Where
                    `states`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `records`.*
                From
                    #__contentbuilder_ng_list_records As `records`
                Where
                    `records`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `access`.*
                From
                    #__contentbuilder_ng_resource_access As `access`
                Where
                    `access`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `users`.*
                From
                    #__contentbuilder_ng_users As `users`
                Where
                    `users`.form_id = " . $cid);

            $db->execute();

            $db->setQuery("
                Delete
                    `users`.*
                From
                    #__contentbuilder_ng_registered_users As `users`
                Where
                    `users`.form_id = " . $cid);

            $db->execute();

            $this->getTable('Elementoption')->reorder('form_id = ' . $cid);

            $db->setQuery("Delete From #__menu Where `link` = 'index.php?option=com_contentbuilder_ng&task=list.display&id=" . intval($cid) . "'");
            $db->execute();
            $db->setQuery("Select count(id) From #__menu Where `link` Like 'index.php?option=com_contentbuilder_ng&task=list.display&id=%'");
            $amount = $db->loadResult();
            if (!$amount) {
                $db->setQuery("Delete From #__menu Where `link` = 'index.php?option=com_contentbuilder_ng&viewcontainer=true'");
                $db->execute();
            }

            if (!$row->delete($cid)) {
                $this->setError($row->getErrorMsg());
                return false;
            }
        }

        $row->reorder();

        // article deletion if required
        $db->setQuery("Select `id` From #__contentbuilder_ng_forms");
        $references = $db->loadColumn();

        $cnt = count($references);
        if ($cnt) {
            $new_items = array();
            for ($i = 0; $i < $cnt; $i++) {
                $new_items[] = $db->Quote($references[$i]);
            }
            $db->setQuery("Delete From #__contentbuilder_ng_articles Where `form_id` Not In (" . implode(',', $new_items) . ") ");
            $db->execute();
        } else {
            $db->setQuery("Delete From #__contentbuilder_ng_articles");
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
        $cids = Factory::getApplication()->input->get('cid', [], 'array');
        ArrayHelper::toInteger($cids);

        if (!count($cids))
            return false;

        $db = $this->getDatabase();
        $table = $this->getTable('Form', '');
        $db->setQuery(' Select * From #__contentbuilder_ng_forms ' .
            '  Where id In ( ' . implode(',', $cids) . ')');
        $result = $db->loadObjectList();

        foreach ($result as $obj) {
            $origId = $obj->id;
            unset($obj->id);

            $obj->name = 'Copy of ' . $obj->name;
            $obj->published = 0;

            // $obj->created = Factory::getDate()->toSql();
            // $obj->created_by = Factory::getApplication()->getIdentity()->id;
            $obj->modified = Factory::getDate()->toSql();
            $obj->modified_by = Factory::getApplication()->getIdentity()->id;
            
            $db->insertObject('#__contentbuilder_ng_forms', $obj);
            $insertId = $db->insertid();

            // Elements
            $db->setQuery(' Select * From #__contentbuilder_ng_elements ' .
                '  Where form_id = ' . $origId);
            $elements = $db->loadObjectList();
            foreach ($elements as $element) {
                unset($element->id);
                $element->form_id = $insertId;
                $db->insertObject('#__contentbuilder_ng_elements', $element);
            }

            // list states
            $db->setQuery(' Select * From #__contentbuilder_ng_list_states ' .
                '  Where form_id = ' . $origId);
            $elements = $db->loadObjectList();
            foreach ($elements as $element) {
                unset($element->id);
                $element->form_id = $insertId;
                $db->insertObject('#__contentbuilder_ng_list_states', $element);
            }
            // XDA-Gil fix 'Copy of Form' in Component Menu in Backen CB View
            // ContentbuilderLegacyHelper::createBackendMenuItem($insertId, $obj->name, true);
        }

        $table->reorder();

        return true;
    }
}
