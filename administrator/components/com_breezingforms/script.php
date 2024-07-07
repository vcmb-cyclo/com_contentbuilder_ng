<?php
/**
 * @package     BreezingForms
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */
defined('_JEXEC') or die ('Direct Access to this location is not allowed.');

use \Joomla\CMS\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Installer\Installer;

class BFFactory
{
    private static $dbo = null;

    public static function getDbo()
    {
        if (static::$dbo == null) {
            static::$dbo = new BFDbo();
        }

        return static::$dbo;
    }

}

class BFFile extends File
{

    public static function read($file)
    {

        return file_get_contents($file);
    }
}

class BFDbo
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

    public function getConnection()
    {
        return $this->dbo->getConnection();
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




if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

class com_breezingformsInstallerScript
{
    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent)
    {
    }

    /**
     * method to update the component
     *
     * @return void
     */
    function update($parent)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $tables = self::getTableFields(Factory::getContainer()->get(DatabaseInterface::class)->getTableList());

        if (isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_records'])) {
            if (!isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_records']['opted'])) {
                $db->setQuery("ALTER TABLE `#__facileforms_records` ADD `opted` TINYINT(1) NOT NULL DEFAULT '0' AFTER `paypal_download_tries`, ADD INDEX (`opted`)");
                $db->execute();
            }
        }

        if (isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_records'])) {
            if (!isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_records']['opt_ip'])) {
                $db->setQuery("ALTER TABLE `#__facileforms_records` ADD `opt_ip` VARCHAR(255) NOT NULL DEFAULT '' AFTER `opted`, ADD INDEX (`opt_ip`)");
                $db->execute();
            }
        }

        if (isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_records'])) {
            if (!isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_records']['opt_date'])) {
                $db->setQuery("ALTER TABLE `#__facileforms_records` ADD `opt_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `opt_ip`, ADD INDEX (`opt_date`)");
                $db->execute();
            }
        }

        if (isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_records'])) {
            if (!isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_records']['opt_token'])) {
                $db->setQuery("ALTER TABLE `#__facileforms_records` ADD `opt_token` VARCHAR(255) NOT NULL DEFAULT '' AFTER `opt_date`, ADD INDEX (`opt_token`)");
                $db->execute();
            }
        }

        if (isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_forms'])) {
            if (!isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_forms']['double_opt'])) {
                $db->setQuery("ALTER TABLE `#__facileforms_forms` ADD `double_opt` TINYINT(1) NOT NULL DEFAULT '0' AFTER `filter_state`, ADD INDEX (`double_opt`)");
                $db->execute();
            }
        }

        if (isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_forms'])) {
            if (!isset ($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . 'facileforms_forms']['opt_mail'])) {
                $db->setQuery("ALTER TABLE `#__facileforms_forms` ADD `opt_mail` VARCHAR(128) NOT NULL DEFAULT '' AFTER `double_opt`, ADD INDEX (`opt_mail`)");
                $db->execute();
            }
        }
    }

    /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent)
    {

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $plugins = $this->getPlugins();

        $installer = new Installer();

        foreach ($plugins as $folder => $subplugs) {

            if (is_array($subplugs)) {
                foreach ($subplugs as $plugin) {

                    $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "' . $plugin . '" AND `folder` = "' . $folder . '"');

                    $id = $db->loadResult();

                    if ($id) {
                        $installer->uninstall('plugin', $id, 1);
                    }
                }
            }
        }

        if (BFFile::exists(JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . 'facileforms.config.php')) {
            BFFile::delete(JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . 'facileforms.config.php');
        }
    }

    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    function preflight($type, $parent)
    {

    }

    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $plugins = $this->getPlugins();

        $base_path = JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_breezingforms' . DS . 'plugins';

        if (file_exists($base_path)) {

            $folders = @Folder::folders($base_path);

            if (count($folders) != 0) {

                $installer = new Installer();

                foreach ($folders as $folder) {
                    $installer->install($base_path . DS . $folder);
                }

                foreach ($plugins as $folder => $subplugs) {
                    foreach ($subplugs as $plugin) {
                        $db->setQuery('Update #__extensions Set `enabled` = 1 WHERE `type` = "plugin" AND `element` = "' . $plugin . '" AND `folder` = "' . $folder . '"');
                        $db->execute();
                    }
                }

            }
        }

        $db->setQuery("Select update_site_id From #__update_sites Where `name` = 'BreezingForms Free' And `type` = 'extension'");
        $site_id = $db->loadResult();

        if ($site_id) {

            $db->setQuery("Delete From #__update_sites Where update_site_id = " . $db->quote($site_id));
            $db->execute();
            $db->setQuery("Delete From #__update_sites_extensions Where update_site_id = " . $db->quote($site_id));
            $db->execute();
            $db->setQuery("Delete From #__updates Where update_site_id = " . $db->quote($site_id));
            $db->execute();
        }
    }

    function getPlugins()
    {
        $plugins = array();
        $plugins['system'] = array();
        $plugins['system'][] = 'sysbreezingforms';
        return $plugins;
    }

    public static function getTableFields($tables, $typeOnly = true)
    {

        $results = array();

        settype($tables, 'array');

        foreach ($tables as $table) {
            $results[$table] = Factory::getContainer()->get(DatabaseInterface::class)->getTableColumns($table, $typeOnly);
        }

        return $results;
    }
}

