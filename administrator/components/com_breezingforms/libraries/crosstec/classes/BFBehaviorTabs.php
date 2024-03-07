<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;


class BFBehaviorTabs
{

	public static function start($group = 'tabs', $params = array())
	{
		self::_loadBehavior($group, $params);

		return '<dl class="tabs" id="' . $group . '"><dt style="display:none;"></dt><dd style="display:none;">';
	}


	public static function end()
	{
		return '</dd></dl>';
	}


	public static function panel($text, $id)
	{
		return '</dd><dt class="tabs ' . $id . '"><span><h3><a href="javascript:void(0);">' . $text . '</a></h3></span></dt><dd class="tabs">';
	}


	protected static function _loadBehavior($group, $params = array())
	{
		static $loaded = array();

		if (!array_key_exists((string) $group, $loaded)) {
			// Include MooTools framework
			///*** HTMLHelper::_('behavior.framework', true); ***/

			$opt['onActive'] = (isset($params['onActive'])) ? '\\' . $params['onActive'] : null;
			$opt['onBackground'] = (isset($params['onBackground'])) ? '\\' . $params['onBackground'] : null;
			$opt['display'] = (isset($params['startOffset'])) ? (int) $params['startOffset'] : null;
			$opt['useStorage'] = (isset($params['useCookie']) && $params['useCookie']) ? 'true' : 'false';
			$opt['titleSelector'] = "'dt.tabs'";
			$opt['descriptionSelector'] = "'dd.tabs'";
			$options = '{';
			foreach ($opt as $k => $v) {
				if ($v) {
					$options .= $k . ': ' . $v . ',';
				}
			}

			if (substr($options, -1) == ',') {
				$options = substr($options, 0, -1);
			}

			$options .= '}';

			$js = '	jQuery(document).ready( function(){
						jQuery(\'dl#' . $group . '.tabs\').each(function(tabs){
							Joomla.Tabs(tabs, ' . $options . ');
						});
					});';

			$document = Factory::getDocument();
			$document->addScriptDeclaration($js);
			HTMLHelper::_('script', 'system/tabs.js', false, true);

			$loaded[(string) $group] = true;
		}
	}
}
