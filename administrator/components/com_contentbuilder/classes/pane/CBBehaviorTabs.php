<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\HTML\HTMLHelper;

class CBBehaviorTabs
{

	public static function start($group = 'tabs', $params = array())
	{
		return HTMLHelper::_('uitab.startTabSet', $group, array('active' => 'tab_settings'));
	}


	public static function end()
	{
		return HTMLHelper::_('uitab.endTabSet');
	}


	public static function startPanel($name_pane, $text, $id)
	{

//		return HTMLHelper::_('uitab.addTab', 'tabs ' . $id, 'tab_settings', $text);
		return HTMLHelper::_('uitab.addTab', $name_pane, $id, $text);
	}

	public static function endPanel()
	{
		return HTMLHelper::_('uitab.endTab');
	}
}
