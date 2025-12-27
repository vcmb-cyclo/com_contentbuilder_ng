<?php

/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2025 by XDA+GIL
 * @license     GNU/GPL
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;

// === LOG POUR DÉBOGAGE ===
$logFile = JPATH_SITE . '/administrator/logs/contentbuilder_install.log';
$logMessage = '[' . date('Y-m-d H:i:s') . '] postflight appelé - Type: ' . $type . PHP_EOL;
$logMessage .= 'User Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'CLI') . PHP_EOL;
$logMessage .= '--------------------------------------------------' . PHP_EOL;

if (!Folder::exists(dirname($logFile))) {
    Folder::create(dirname($logFile));
}
file_put_contents($logFile, $logMessage. PHP_EOL, FILE_APPEND);


if (!class_exists('CBFactory')) {

  class CBFactory
  {

    private static $dbo = null;

    public static function getDbo()
    {
      if (static::$dbo == null) {
        static::$dbo = new CBDbo();
      }

      return static::$dbo;
    }
  }

  class CBFile extends File
  {

    public static function read($file)
    {
      return file_get_contents($file);
    }
  }

  class CBDbo
  {

    private $errNo = 0;
    private $errMsg = '';
    private $dbo = null;
    private $last_query = true;
    private $last_failed_query = '';

    function __construct()
    {
      $this->dbo = Factory::getContainer()->get(DatabaseInterface::class);
    }

    public function setQuery($query, $offset = 0, $limit = 0)
    {

      try {

        $this->dbo->setQuery($query, $offset, $limit);
      } catch (Exception $e) {

        $this->last_query = false;
        $this->last_failed_query = $query;
        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }
    }

    public function loadObjectList()
    {

      if (!$this->last_query)
        return array();

      $this->errNo = 0;
      $this->errMsg = '';

      try {

        return $this->dbo->loadObjectList();
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return array();
    }

    public function loadObject($class = 'stdClass')
    {
      if (!$this->last_query)
        return null;

      $this->errNo = 0;
      $this->errMsg = '';

      try {

        return $this->dbo->loadObject($class);
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return null;
    }

    public function loadColumn($offset = 0)
    {
      if (!$this->last_query)
        return null;

      $this->errNo = 0;
      $this->errMsg = '';

      try {

        return $this->dbo->loadColumn($offset);
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return null;
    }

    public function loadAssocList($key = null, $column = null)
    {
      if (!$this->last_query)
        return array();

      $this->errNo = 0;
      $this->errMsg = '';

      try {

        return $this->dbo->loadAssocList($key, $column);
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return array();
    }

    public function loadAssoc()
    {
      if (!$this->last_query)
        return null;

      $this->errNo = 0;
      $this->errMsg = '';

      try {

        return $this->dbo->loadAssoc();
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return null;
    }

    public function query()
    {

      return $this->execute();
    }

    public function execute()
    {
      $this->errNo = 0;
      $this->errMsg = '';

      try {

        return $this->dbo->execute();
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return false;
    }

    public function updateObject($table, &$object, $key, $nulls = false)
    {
      $this->errNo = 0;
      $this->errMsg = '';

      try {

        return $this->dbo->updateObject($table, $object, $key, $nulls);
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return false;
    }

    public function insertObject($table, &$object, $key = null)
    {
      $this->errNo = 0;
      $this->errMsg = '';

      try {

        return $this->dbo->insertObject($table, $object, $key);
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return false;
    }

    public function quote($query, $esc = true)
    {
      return $this->dbo->quote($query, $esc);
    }

    public function getQuery($new = false)
    {
      if (!$this->last_query)
        return $this->last_failed_query;

      return $this->dbo->getQuery($new);
    }

    public function getPrefix()
    {
      return $this->dbo->getPrefix();
    }

    public function getNullDate()
    {
      return $this->dbo->getNullDate();
    }

    public function getNumRows()
    {
      if (!$this->last_query)
        return 0;

      return $this->dbo->getNumRows();
    }

    public function getCount()
    {
      if (!$this->last_query)
        return 0;

      return $this->dbo->getCount();
    }

    public function getAffectedRows()
    {
      if (!$this->last_query)
        return array();

      return $this->dbo->getAffectedRows();
    }

    public function getTableColumns($table, $typeOnly = true)
    {
      $this->errNo = 0;
      $this->errMsg = '';

      try {
        return $this->dbo->getTableColumns($table, $typeOnly);
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return array();
    }

    public function getTableList()
    {
      $this->errNo = 0;
      $this->errMsg = '';

      try {
        return $this->dbo->getTableList();
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return array();
    }

    public function loadResult()
    {
      if (!$this->last_query)
        return null;

      $this->errNo = 0;
      $this->errMsg = '';

      try {
        return $this->dbo->loadResult();
      } catch (Exception $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      } catch (Error $e) {

        $this->errNo = $e->getCode();
        $this->errMsg = $e->getMessage();
      }

      return null;
    }

    public function getErrorNum()
    {
      return $this->errNo;
    }

    public function getErrorMsg()
    {
      return $this->errMsg;
    }

    public function stderr()
    {

      return $this->errMsg;
    }

    public function insertid()
    {

      if (!$this->last_query)
        return 0;

      return $this->dbo->insertid();
    }
  }
}


if (!function_exists('contentbuilder_install_db')) {
  function contentbuilder_install_db()
  {

    require_once(JPATH_SITE . '/administrator/components/com_contentbuilder/classes/joomla_compat.php');

    $db = Factory::getContainer()->get(DatabaseInterface::class);

    $tables = CBCompat::getTableFields($db->getTableList());

    if (isset($tables[$db->getPrefix() . 'contentbuilder_forms'])) {

      return true;
    }

    $query1 = "

CREATE TABLE `#__contentbuilder_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL DEFAULT '0',
  `record_id` varchar(255) NOT NULL DEFAULT '0',
  `form_id` int(11) NOT NULL DEFAULT '0',
  `last_update` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`,`form_id`),
  KEY `article_id` (`article_id`,`record_id`),
  KEY `record_id_2` (`record_id`)
)";


    $query2 = "
CREATE TABLE `#__contentbuilder_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL DEFAULT '0',
  `reference_id` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `change_type` varchar(255) NOT NULL DEFAULT '',
  `options` text NOT NULL,
  `custom_init_script` text NOT NULL,
  `custom_action_script` text NOT NULL,
  `custom_validation_script` text NOT NULL,
  `validation_message` text NOT NULL,
  `default_value` text NOT NULL,
  `hint` text NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT '',
  `list_include` tinyint(1) NOT NULL DEFAULT '0',
  `search_include` tinyint(1) NOT NULL DEFAULT '1',
  `item_wrapper` text NOT NULL,
  `wordwrap` int(11) NOT NULL DEFAULT '0',
  `linkable` tinyint(1) NOT NULL DEFAULT '1',
  `editable` tinyint(1) NOT NULL DEFAULT '0',
  `validations` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`),
  KEY `form_id` (`form_id`,`reference_id`)
)";


    $query3 = "
CREATE TABLE `#__contentbuilder_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL DEFAULT '',
  `reference_id` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `details_template` longtext NOT NULL,
  `details_prepare` longtext NOT NULL,
  `editable_template` longtext NOT NULL,
  `editable_prepare` longtext NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NULL DEFAULT NULL,
  `created_by` varchar(255) NOT NULL DEFAULT '',
  `modified_by` varchar(255) NOT NULL DEFAULT '',
  `metadata` tinyint(1) NOT NULL DEFAULT '1',
  `export_xls` tinyint(1) NOT NULL DEFAULT '0',
  `print_button` tinyint(1) NOT NULL DEFAULT '1',
  `show_id_column` tinyint(1) NOT NULL DEFAULT '0',
  `use_view_name_as_title` tinyint(1) NOT NULL DEFAULT '0',
  `display_in` tinyint(1) NOT NULL DEFAULT '0',
  `edit_button` tinyint(1) NOT NULL DEFAULT '0',
  `list_state` tinyint(1) NOT NULL DEFAULT '0',
  `list_publish` tinyint(1) NOT NULL DEFAULT '0',
  `list_language` tinyint(1) NOT NULL DEFAULT '0',
  `list_article` tinyint(1) NOT NULL DEFAULT '0',
  `list_author` tinyint(1) NOT NULL DEFAULT '0',
  `select_column` tinyint(1) NOT NULL DEFAULT '0',
  `published_only` tinyint(1) NOT NULL DEFAULT '0',
  `own_only` tinyint(1) NOT NULL DEFAULT '0',
  `own_only_fe` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `intro_text` text NOT NULL,
  `config` longtext NOT NULL,
  `default_section` int(11) NOT NULL DEFAULT '0',
  `default_category` int(11) NOT NULL DEFAULT '0',
  `default_lang_code` varchar(7) NOT NULL DEFAULT '*',
  `default_lang_code_ignore` tinyint(1) NOT NULL DEFAULT '0',
  `create_articles` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `initial_sort_order` varchar(255) NOT NULL DEFAULT '-1',
  `title_field` int(11) NOT NULL DEFAULT '0',
  `delete_articles` tinyint(1) NOT NULL DEFAULT '1',
  `edit_by_type` tinyint(1) NOT NULL DEFAULT '0',
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `email_update_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `limited_article_options` tinyint(1) NOT NULL DEFAULT '1',
  `limited_article_options_fe` tinyint(1) NOT NULL DEFAULT '1',
  `upload_directory` text NOT NULL,
  `protect_upload_directory` tinyint(1) NOT NULL DEFAULT '1',
  `last_update` datetime NULL DEFAULT NULL,
  `limit_add` int(11) NOT NULL DEFAULT '0',
  `limit_edit` int(11) NOT NULL DEFAULT '0',
  `verification_required_view` tinyint(1) NOT NULL DEFAULT '0',
  `verification_days_view` float NOT NULL DEFAULT '0',
  `verification_required_new` tinyint(1) NOT NULL DEFAULT '0',
  `verification_days_new` float NOT NULL DEFAULT '0',
  `verification_required_edit` tinyint(1) NOT NULL DEFAULT '0',
  `verification_days_edit` float NOT NULL DEFAULT '0',
  `verification_url_view` text NOT NULL,
  `verification_url_new` text NOT NULL,
  `verification_url_edit` text NOT NULL,
  `show_all_languages_fe` tinyint(1) NOT NULL DEFAULT '1',
  `default_publish_up_days` int(11) NOT NULL DEFAULT '0',
  `default_publish_down_days` int(11) NOT NULL DEFAULT '0',
  `default_access` int(11) NOT NULL DEFAULT '0',
  `default_featured` tinyint(1) NOT NULL DEFAULT '0',
  `email_admin_template` text NOT NULL,
  `email_admin_subject` varchar(255) NOT NULL DEFAULT '',
  `email_admin_alternative_from` varchar(255) NOT NULL DEFAULT '',
  `email_admin_alternative_fromname` varchar(255) NOT NULL DEFAULT '',
  `email_admin_recipients` text NOT NULL,
  `email_admin_recipients_attach_uploads` text NOT NULL,
  `email_admin_html` tinyint(1) NOT NULL DEFAULT '0',
  `email_template` text NOT NULL,
  `email_subject` varchar(255) NOT NULL DEFAULT '',
  `email_alternative_from` varchar(255) NOT NULL DEFAULT '',
  `email_alternative_fromname` varchar(255) NOT NULL,
  `email_recipients` text NOT NULL,
  `email_recipients_attach_uploads` text NOT NULL,
  `email_html` tinyint(1) NOT NULL DEFAULT '0',
  `act_as_registration` tinyint(1) NOT NULL DEFAULT '0',
  `registration_username_field` varchar(255) NOT NULL DEFAULT '',
  `registration_password_field` varchar(255) NOT NULL DEFAULT '',
  `registration_password_repeat_field` varchar(255) NOT NULL DEFAULT '',
  `registration_name_field` varchar(255) NOT NULL DEFAULT '',
  `registration_email_field` varchar(255) NOT NULL DEFAULT '',
  `registration_email_repeat_field` varchar(255) NOT NULL DEFAULT '',
  `auto_publish` tinyint(1) NOT NULL DEFAULT '0',
  `force_login` tinyint(1) NOT NULL DEFAULT '0',
  `force_url` text NOT NULL,
  `registration_bypass_plugin` varchar(255) NOT NULL DEFAULT '',
  `registration_bypass_plugin_params` text NOT NULL,
  `registration_bypass_verification_name` varchar(255) NOT NULL DEFAULT '',
  `registration_bypass_verify_view` varchar(32) NOT NULL DEFAULT '',
  `theme_plugin` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`)
)";


    $query4 = "
CREATE TABLE `#__contentbuilder_list_records` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL DEFAULT '0',
  `record_id` varchar(255) NOT NULL DEFAULT '',
  `state_id` int(11) NOT NULL DEFAULT '0',
  `reference_id` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`,`record_id`,`state_id`)
)";


    $query5 = "
CREATE TABLE `#__contentbuilder_list_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(255) NOT NULL DEFAULT '',
  `action` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)";


    $query6 = "
CREATE TABLE `#__contentbuilder_records` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `record_id` varchar(255) NOT NULL DEFAULT '',
  `reference_id` varchar(255) NOT NULL DEFAULT '',
  `edited` int(11) NOT NULL DEFAULT '0',
  `sef` varchar(50) NOT NULL DEFAULT '',
  `lang_code` varchar(7) NOT NULL DEFAULT '*',
  `publish_up` datetime NULL DEFAULT NULL,
  `publish_down` datetime NULL DEFAULT NULL,
  `last_update` datetime NULL DEFAULT NULL,
  `is_future` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`),
  KEY `reference_id` (`reference_id`)
)";


    $query7 = "
CREATE TABLE `#__contentbuilder_registered_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `record_id` varchar(255) NOT NULL DEFAULT '',
  `form_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`record_id`,`form_id`)
)";


    $query8 = "
CREATE TABLE `#__contentbuilder_resource_access` (
  `form_id` int(11) NOT NULL DEFAULT '0',
  `element_id` varchar(100) NOT NULL DEFAULT '',
  `resource_id` varchar(100) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `form_id` (`form_id`,`element_id`,`resource_id`)
)";

    $query9 = "
CREATE TABLE `#__contentbuilder_storages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
)";

    $query10 = "

CREATE TABLE `#__contentbuilder_storage_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `is_group` tinyint(1) NOT NULL DEFAULT '0',
  `group_definition` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `storage_id` (`storage_id`,`name`)
)";

    $query11 = "
CREATE TABLE `#__contentbuilder_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `form_id` int(11) NOT NULL DEFAULT '0',
  `records` int(11) NOT NULL DEFAULT '0',
  `verified_view` tinyint(1) NOT NULL DEFAULT '0',
  `verification_date_view` datetime NULL DEFAULT NULL,
  `verified_new` tinyint(1) NOT NULL DEFAULT '0',
  `verification_date_new` datetime NULL DEFAULT NULL,
  `verified_edit` tinyint(1) NOT NULL DEFAULT '0',
  `verification_date_edit` datetime NULL DEFAULT NULL,
  `limit_add` int(11) NOT NULL DEFAULT '0',
  `limit_edit` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`,`form_id`)
)";


    $query12 = "
CREATE TABLE `#__contentbuilder_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verification_hash` varchar(255) NOT NULL DEFAULT '',
  `start_date` datetime NULL DEFAULT NULL,
  `verification_date` datetime NULL DEFAULT NULL,
  `verification_data` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `plugin` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `is_test` tinyint(1) NOT NULL DEFAULT '0',
  `setup` text NOT NULL,
  `client` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `verification_hash` (`verification_hash`),
  KEY `user_id` (`user_id`)
)
";

    try {
      $db->setQuery($query1);
      $db->execute();
      $db->setQuery($query2);
      $db->execute();
      $db->setQuery($query3);
      $db->execute();
      $db->setQuery($query4);
      $db->execute();
      $db->setQuery($query5);
      $db->execute();
      $db->setQuery($query6);
      $db->execute();
      $db->setQuery($query7);
      $db->execute();
      $db->setQuery($query8);
      $db->execute();
      $db->setQuery($query9);
      $db->execute();
      $db->setQuery($query10);
      $db->execute();
      $db->setQuery($query11);
      $db->execute();
      $db->setQuery($query12);
      $db->execute();
    } catch (Exception $e) {
    }

    Factory::getApplication()->enqueueMessage($db->getErrorMsg(), 'error');
    // exit;
  }
}

class com_contentbuilderInstallerScript
{

  function getPlugins()
  {
    $plugins = array();
    $plugins['contentbuilder_verify'] = array();
    $plugins['contentbuilder_verify'][] = 'paypal';
    $plugins['contentbuilder_verify'][] = 'passthrough';
    $plugins['contentbuilder_validation'] = array();
    $plugins['contentbuilder_validation'][] = 'notempty';
    $plugins['contentbuilder_validation'][] = 'equal';
    $plugins['contentbuilder_validation'][] = 'email';
    $plugins['contentbuilder_validation'][] = 'date_not_before';
    $plugins['contentbuilder_validation'][] = 'date_is_valid';
    $plugins['contentbuilder_themes'] = array();
    $plugins['contentbuilder_themes'][] = 'khepri';
    $plugins['contentbuilder_themes'][] = 'blank';
    $plugins['contentbuilder_themes'][] = 'joomla3';
    $plugins['system'] = array();
    $plugins['system'][] = 'contentbuilder_system';
    $plugins['contentbuilder_submit'] = array();
    $plugins['contentbuilder_submit'][] = 'submit_sample';
    $plugins['contentbuilder_listaction'] = array();
    $plugins['contentbuilder_listaction'][] = 'trash';
    $plugins['contentbuilder_listaction'][] = 'untrash';
    $plugins['content'] = array();
    $plugins['content'][] = 'contentbuilder_verify';
    $plugins['content'][] = 'contentbuilder_permission_observer';
    $plugins['content'][] = 'contentbuilder_image_scale';
    $plugins['content'][] = 'contentbuilder_download';
    $plugins['content'][] = 'contentbuilder_rating';
    return $plugins;
  }

  private function getCurrentInstalledVersion()
  {
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $query = $db->getQuery(true)
      ->select($db->quoteName('manifest_cache'))
      ->from($db->quoteName('#__extensions'))
      ->where($db->quoteName('element') . ' = ' . $db->quote('com_contentbuilder'));

    $db->setQuery($query);
    $manifest = $db->loadResult();

    if ($manifest) {
      $manifest = json_decode($manifest, true);
      return $manifest['version'] ?? '0.0.0';
    }

    return '0.0.0';
  }

  function installAndUpdate()
  {
    require_once(JPATH_SITE . '/administrator/components/com_contentbuilder/classes/joomla_compat.php');

    $db = CBFactory::getDBO();
    $plugins = $this->getPlugins();

    $base_path = JPATH_SITE . '/administrator/components/com_contentbuilder/plugins';

    $folders = Folder::folders($base_path);

    $installer = new Installer();
    $installer->setDatabase(\Joomla\CMS\Factory::getContainer()->get('DatabaseDriver'));
    
    foreach ($folders as $folder) {
      Factory::getApplication()->enqueueMessage('Installing plugin <b>' . $folder . '</b>', 'message');
      $success = $installer->install($base_path . '/' . $folder);
      if (!$success) {
        Factory::getApplication()->enqueueMessage('Install failed for plugin <b>' . $folder . '</b>', 'error');
      }
    }

    foreach ($plugins as $folder => $subplugs) {
      foreach ($subplugs as $plugin) {
        $query = 'UPDATE #__extensions SET `enabled` = 1 WHERE `type` = "plugin" AND `element` = ' . $db->quote($plugin) . ' AND `folder` = ' . $db->quote($folder);
        $db->setQuery($query);
        $db->execute();
        Factory::getApplication()->enqueueMessage('Published plugin <b>' . $plugin . '</b>', 'message');
      }
    }
  }

  /**
   * method to install the component
   *
   * @return void
   */
  function install($parent)
  {
    if (!version_compare(PHP_VERSION, '5.2.0', '>=')) {
      Factory::getApplication()->enqueueMessage('"WARNING: YOU ARE RUNNING PHP VERSION "' . PHP_VERSION . '". ContentBuilder WON\'T WORK WITH THIS VERSION. PLEASE UPGRADE TO AT LEAST PHP 5.2.0, SORRY BUT YOU BETTER UNINSTALL THIS COMPONENT NOW!"', 'error');
    }

    require_once(JPATH_SITE . '/administrator/components/com_contentbuilder/classes/joomla_compat.php');

    //contentbuilder_install_db();

    //exit;
    $this->installAndUpdate();
  }

  /**
   * method to update the component
   *
   * @return void
   */
  function update($parent)
  {
    if (!version_compare(PHP_VERSION, '5.2.0', '>=')) {
      Factory::getApplication()->enqueueMessage('"WARNING: YOU ARE RUNNING PHP VERSION "' . PHP_VERSION . '". ContentBuilder WON\'T WORK WITH THIS VERSION. PLEASE UPGRADE TO AT LEAST PHP 5.2.0, SORRY BUT YOU BETTER UNINSTALL THIS COMPONENT NOW!"', 'error');
    }

    $this->installAndUpdate();
  }

  /**
   * method to uninstall the component
   *
   * @return void
   */
  function uninstall($parent)
  {
    $db = CBFactory::getDBO();

    $db->setQuery("DELETE FROM #__menu WHERE `link` LIKE 'index.php?option=com_contentbuilder%'");
    $db->execute();

    $plugins = $this->getPlugins();
    $installer = new Installer();
    $installer->setDatabase(\Joomla\CMS\Factory::getContainer()->get('DatabaseDriver'));

    foreach ($plugins as $folder => $subplugs) {
      foreach ($subplugs as $plugin) {
        $query = 'SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = ' . $db->quote($plugin) . ' AND `folder` = ' . $db->quote($folder);
        $db->setQuery($query);
        $id = $db->loadResult();

        if ($id) {
          $installer->uninstall('plugin', $id, 1);
        }
      }
    }

    $db->setQuery("SELECT id FROM `#__menu` WHERE `alias` = 'root'");
    if (!$db->loadResult()) {
      $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, NULL, 0, 0, '', 0, '', 0, (SELECT MAX(mlft.rgt)+1 FROM #__menu AS mlft), 0, '*', 0)");
      $db->execute();
    }
  }
  /**
   * method to run before an install/update/uninstall method
   *
   * @return void
   */
  function preflight($type, $parent)
  {
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
    if (!$db->loadResult()) {
      $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, NULL, 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
      $db->execute();
    }
  }

  /**
   * method to run after an install/update/uninstall method
   *
   * @return void
   */
  function postflight($type, $parent)
  {
    $logFile = JPATH_SITE . '/administrator/logs/contentbuilder_install.log';
    $app = Factory::getApplication();
    $db = Factory::getContainer()->get(DatabaseInterface::class);

    // === LOG POUR DÉBOGAGE ===
    $logMessage = 'Version actuelle dans manifest_cache : ' . $this->getCurrentInstalledVersion() . PHP_EOL;
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);

    /*
             $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
             if(!$db->loadResult()){
                 $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, NULL, 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
                 $db->execute();
             }*/

    $db->setQuery("Update #__menu Set `title` = 'COM_CONTENTBUILDER' Where `alias`='contentbuilder'");
    $db->execute();

    // Suppression propre de l'ancienne librairie PHPExcel
    $classesPath    = JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes';
    $phpexcelFolder = $classesPath . '/PHPExcel';
    $phpexcelFile   = $classesPath . '/PHPExcel.php';

    if (Folder::exists($phpexcelFolder)) {
        if (Folder::delete($phpexcelFolder)) {
            file_put_contents($logFile, 'Old PHPExcel folder successfully deleted.' . PHP_EOL, FILE_APPEND);
            $app->enqueueMessage('Old PHPExcel folder successfully deleted.', 'message');
        } else {
            file_put_contents($logFile, 'Failed to delete PHPExcel folder.' . PHP_EOL, FILE_APPEND);
            $app->enqueueMessage('Failed to delete PHPExcel folder.', 'warning');
        }
    }

    if (File::exists($phpexcelFile)) {
        if (File::delete($phpexcelFile)) {
            file_put_contents($logFile, 'Old PHPExcel.php file successfully deleted.' . PHP_EOL, FILE_APPEND);
            $app->enqueueMessage('Old PHPExcel.php file successfully deleted.', 'message');
        } else {
            file_put_contents($logFile, 'Failed to delete PHPExcel.php file.' . PHP_EOL, FILE_APPEND);
            $app->enqueueMessage('Failed to delete PHPExcel.php file.', 'warning');
        }
    }

    // Suppression propre de l'ancienne librairie PhpSpreadsheet
    $oldFolder = JPATH_ADMINISTRATOR . '/components/com_contentbuilder/librairies/PhpSpreadsheet';

    if (Folder::exists($oldFolder)) {
        if (Folder::delete($oldFolder)) {
            $msg = 'Ancienne librairie PhpSpreadsheet supprimée avec succès : ' . $oldFolder;
            file_put_contents($logFile, $msg . PHP_EOL, FILE_APPEND);
            Factory::getApplication()->enqueueMessage($msg, 'message');
        } else {
            $msg = 'ÉCHEC de suppression de l\'ancienne librairie PhpSpreadsheet : ' . $oldFolder . ' (vérifiez les permissions/ownership du serveur)';
            file_put_contents($logFile, $msg . PHP_EOL, FILE_APPEND);
            Factory::getApplication()->enqueueMessage($msg, 'warning');
            
            // Diagnostic supplémentaire dans le log
            $filesLeft = Folder::files($oldFolder, '.', true, true);
            $foldersLeft = Folder::folders($oldFolder, '.', true, true);
            file_put_contents($logFile, 'Fichiers restants : ' . print_r($filesLeft, true) . PHP_EOL, FILE_APPEND);
            file_put_contents($logFile, 'Dossiers restants : ' . print_r($foldersLeft, true) . PHP_EOL, FILE_APPEND);
        }
    } else {
        file_put_contents($logFile, 'Aucune ancienne librairie PhpSpreadsheet à supprimer.' . PHP_EOL, FILE_APPEND);
    }


    $alterQueries = [
      // Table #__contentbuilder_forms
      "ALTER TABLE `#__contentbuilder_forms` MODIFY `created` DATETIME NULL DEFAULT CURRENT_TIMESTAMP",
      "UPDATE `#__contentbuilder_forms` SET `created` = NULL WHERE `created` = '0000-00-00'",

      "ALTER TABLE `#__contentbuilder_forms` MODIFY `modified` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_forms` SET `modified` = NULL WHERE `modified` = '0000-00-00'",

      "ALTER TABLE `#__contentbuilder_forms` MODIFY `last_update` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_forms` SET `last_update` = NULL WHERE `last_update` = '0000-00-00'",

      "ALTER TABLE `#__contentbuilder_forms` MODIFY `rand_date_update` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_forms` SET `rand_date_update` = NULL WHERE `rand_date_update` = '0000-00-00'",

      // Table #__contentbuilder_records
      "ALTER TABLE `#__contentbuilder_records` MODIFY `publish_up` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_records` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_records` MODIFY `publish_down` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_records` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_records` MODIFY `last_update` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_records` SET `last_update` = NULL WHERE `last_update` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_records` MODIFY `rand_date` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_records` SET `rand_date` = NULL WHERE `rand_date` = '0000-00-00'",

      // Table #__contentbuilder_articles (si présent)
      "ALTER TABLE `#__contentbuilder_articles` MODIFY `last_update` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_articles` SET `last_update` = NULL WHERE `last_update` = '0000-00-00'",

      // Table #__contentbuilder_users (dates de vérification)
      "ALTER TABLE `#__contentbuilder_users` MODIFY `verification_date_view` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_users` SET `verification_date_view` = NULL WHERE `verification_date_view` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_users` MODIFY `verification_date_new` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_users` SET `verification_date_new` = NULL WHERE `verification_date_new` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_users` MODIFY `verification_date_edit` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_users` SET `verification_date_edit` = NULL WHERE `verification_date_edit` = '0000-00-00'",

      "ALTER TABLE `#__contentbuilder_rating_cache` MODIFY COLUMN `date` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_rating_cache` SET `date` = NULL WHERE `date` = '0000-00-00'",

      // Table #__contentbuilder_verifications
      "ALTER TABLE `#__contentbuilder_verifications` MODIFY `start_date` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_verifications` SET `start_date` = NULL WHERE `start_date` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_verifications` MODIFY `verification_date` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_verifications` SET `verification_date` = NULL WHERE `verification_date` = '0000-00-00'"
    ];

    foreach ($alterQueries as $query) {
      try {
        $db->setQuery($query)->execute();
      } catch (Exception $e) {
        // Silencieux si la colonne est déjà correcte ou table inexistante
        Factory::getApplication()->enqueueMessage('Warning: Could not alter date column: ' . $e->getMessage(), 'warning');
      }
    }

    Factory::getApplication()->enqueueMessage('Champs date mis à jour pour supporter NULL correctement.', 'message');


    // try to restore the main menu items if they got lost
    /*
    $db->setQuery("Select component_id From #__menu Where `link`='index.php?option=com_contentbuilder' And parent_id = 1");
    $result = $db->loadResult();

    if(!$result) {
        
        $db->setQuery("Select extension_id From #__extensions Where `type` = 'component' And `element` = 'com_contentbuilder'");
        $comp_id = $db->loadResult();
        
        if($comp_id){
            
                
            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES ('main', 'COM_CONTENTBUILDER', 'contentbuilder', '', 'contentbuilder', 'index.php?option=com_contentbuilder', 'component', 0, 1, 1, ".$comp_id.", 0, NULL, 0, 1, 'components/com_contentbuilder/views/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();
            $parent_id = $db->insertid();

            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES ('main', 'COM_CONTENTBUILDER_STORAGES', 'comcontentbuilderstorages', '', 'contentbuilder/comcontentbuilderstorages', 'index.php?option=com_contentbuilder&controller=storages', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, NULL, 0, 1, 'components/com_contentbuilder/views/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();

            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'COM_CONTENTBUILDER_LIST', 'comcontentbuilderlist', '', 'contentbuilder/comcontentbuilderlist', 'index.php?option=com_contentbuilder&controller=forms', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, NULL, 0, 1, 'components/com_contentbuilder/views/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();

            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'Try BreezingForms!', 'try-breezingforms', '', 'contentbuilder/try-breezingforms', 'index.php?option=com_contentbuilder&view=contentbuilder&market=true', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, NULL, 0, 1, 'class:component', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();

            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'COM_CONTENTBUILDER_ABOUT', 'comcontentbuilderabout', '', 'contentbuilder/comcontentbuilderabout', 'index.php?option=com_contentbuilder&view=contentbuilder', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, NULL, 0, 1, 'class:component', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();

            $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
            $rgt = $db->loadResult();

            $db->setQuery("Update `#__menu` Set rgt = ".$rgt." Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
            $db->execute();
        }
    }*/
  }
}
