<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// Compatibility mode class for Joomla 2. Useless ???

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Version;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'CBFile.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'CBFactory.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'CBRequest.php');

if (!function_exists('cb_b64enc')) {

    function cb_b64enc($str)
    {
        $base = 'base';
        $sixty_four = '64_encode';
        return call_user_func($base . $sixty_four, $str ?? '');
    }

}

if (!function_exists('cb_b64dec')) {
    function cb_b64dec($str)
    {
        $base = 'base';
        $sixty_four = '64_decode';
        return call_user_func($base . $sixty_four, $str ?? '');
    }
}

$version = new Version();
define('CBJOOMLAVERSION', $version->getShortVersion());

class CBCompat
{

    protected $pane = null;

    // Pane Name.
    protected $name_pane = null;


    public function startPane($key)
    {
        $this->name_pane = $key;
        return HTMLHelper::_('uitab.startTabSet', $key, array('active' => 'tab_settings'));
    }

    public function startPanel($title, $id)
    {
        //        return HTMLHelper::_('uitab.addTab', 'tabs ' . $id, 'tab_settings', $title);
        return HTMLHelper::_('uitab.addTab', $this->name_pane, $id, $title);
    }

    public function endPane()
    {
        return HTMLHelper::_('uitab.endTabSet');
    }

    public function endPanel()
    {
        return HTMLHelper::_('uitab.endTab');
    }

    public static function loadColumn()
    {
        return Factory::getContainer()->get(DatabaseInterface::class)->loadColumn();
    }

    public static function getCheckAll($rows)
    {
        return 'Joomla.checkAll(this);';
    }

    public static function getParams($attribs)
    {
        $params = new Registry;
        $params->loadString($attribs);
        return $params;
    }

    public static function getPluginParams(CMSPlugin $plgObj, $dir, $plg)
    {
        return $plgObj->params;
    }

    public static function setJoomlaConfig($key, $value)
    {
        Factory::getConfig()->set($key, $value);
    }

    public static function getJoomlaConfig($key, $value = null)
    {
        return Factory::getConfig()->get(preg_replace("/^config./", '', $key, 1), $value);
    }

    public static function toSql(Date $dateObj)
    {
        return $dateObj->toSql();
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

    public static function requireController()
    {
        require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'controllerlegacy.php');
    }

    public static function requireView()
    {
        require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');
    }

    public static function requireModel()
    {
        require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');
    }
}
