<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// shouldn't be required no longer in Joomla 3.0 Stable
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'pane' . DS . 'CBBehaviorTabs.php');

class CBTabs
{

    private $type = '';
    private $options = array();

    // Pane Name.
    private $name_pane = null;

    function __construct($type = 'tabs', $options = array())
    {
        $this->options = $options;
        $this->type = $type;
    }

    public static function getInstance($type, $options = array())
    {
        static $instance;

        if (!$instance) {
            $instance = new CBTabs($type, $options);
        }
        return $instance;
    }

    function startPanel($tabText, $paneid)
    {
        return CBBehaviorTabs::startPanel($this->name_pane, $tabText, $paneid);
    }

    function endPanel()
    {
        return CBBehaviorTabs::endPanel();
    }

    function startPane($tabText)
    {
        $this->name_pane = $tabText;
        return CBBehaviorTabs::start($this->type, $this->options);
    }

    function endPane()
    {
        return CBBehaviorTabs::end();
    }
}
