<?php
/**
* @version		$Id: helper.php 17261 2010-05-25 15:06:51Z ian $
* @package		Joomla.Framework
* @subpackage	Plugin
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @copyright   Copyright (C) 2024 by XDA+GIL
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;


// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
* Plugin helper class
*
* @static
* @package		Joomla.Framework
* @subpackage	Plugin
* @since		1.5
*/
class CBPluginHelper
{
	/**
	 * Get the plugin data of a specific type if no specific plugin is specified
	 * otherwise only the specific plugin data is returned
	 *
	 * @access public
	 * @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	 * @param string 	$plugin	The plugin name
	 * @return mixed 	An array of plugin data objects, or a plugin data object
	 */
	function &getPlugin($type, $plugin = null)
	{
		$result = array();

		$plugins = CBPluginHelper::_load();

		$total = count($plugins);
		for($i = 0; $i < $total; $i++)
		{
			if(is_null($plugin))
			{
				if($plugins[$i]->type == $type) {
					$result[] = $plugins[$i];
				}
			}
			else
			{
				if($plugins[$i]->type == $type && $plugins[$i]->name == $plugin) {
					$result = $plugins[$i];
					break;
				}
			}

		}

		return $result;
	}
        
        public static function getPlugin16($type, $plugin = null)
	{
		$result		= array();
		$plugins	= self::_load16();

		// Find the correct plugin(s) to return.
		if (!$plugin) {
			foreach($plugins as $p) {
				// Is this the right plugin?
				if ($p->type == $type) {
					$result[] = $p;
				}
			}
		} else {
			foreach($plugins as $p) {
				// Is this plugin in the right group?
				if ($p->type == $type && $p->name == $plugin) {
					$result = $p;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Checks if a plugin is enabled
	 *
	 * @access	public
	 * @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	 * @param string 	$plugin	The plugin name
	 * @return	boolean
	 */
	function isEnabled( $type, $plugin = null )
	{
		$result = &CBPluginHelper::getPlugin( $type, $plugin);
		return (!empty($result));
	}

	/**
	* Loads all the plugin files for a particular type if no specific plugin is specified
	* otherwise only the specific pugin is loaded.
	*
	* @access public
	* @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	* @param string 	$plugin	The plugin name
	* @return boolean True if success
	*/
	function importPlugin($type, $plugin = null, $autocreate = true, $dispatcher = null)
	{
       
		$result = array();
        $plugins = CBPluginHelper::_load16();
		$total = count($plugins);
		for($i = 0; $i < $total; $i++) {
			if($plugins[$i]->type == $type && ($plugins[$i]->name == $plugin ||  $plugin === null)) {
				$o = CBPluginHelper::_import16( $plugins[$i], $autocreate, $dispatcher );
				
				if(!in_array($o, $result)){
					$result[] = $o;
				}
			}
		}

		return $result;
	}

	/**
	 * Loads the plugin file
	 *
	 * @access private
	 * @return boolean True if success
	 */
	function _import( &$plugin, $autocreate = true, $dispatcher = null )
	{
		$paths = array();

		$result	= false;
		$plugin->type = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->type);
		$plugin->name  = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->name);

		$path	= JPATH_PLUGINS.DS.$plugin->type.DS.$plugin->name.'.php';

		if (!isset( $paths[$path] ))
		{
			if (file_exists( $path ))
			{
				//needed for backwards compatibility
				global $_MAMBOTS, $mainframe;

				require_once( $path );
				$paths[$path] = true;

				if($autocreate)
				{
					// Makes sure we have an event dispatcher
					if(!is_object($dispatcher)) {
						$dispatcher = & JDispatcher::getInstance();
					}

					$className = 'plg'.$plugin->type.$plugin->name;
					if(class_exists($className))
					{
						// load plugin parameters
						$plugin =& CBPluginHelper::getPlugin($plugin->type, $plugin->name);

						// create the plugin
						$instance = new $className($dispatcher, (array)($plugin));
                                                return $instance;
					}
				}
			}
			else
			{
				$paths[$path] = false;
			}
		}
	}
        
        protected static function _import16(&$plugin, $autocreate = true, $dispatcher = null)
	{
		static $paths = array();

		$plugin->type = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->type);
		$plugin->name = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->name);

		$legacypath	= JPATH_PLUGINS.DS.$plugin->type.DS.$plugin->name.'.php';
		$path = JPATH_PLUGINS.DS.$plugin->type.DS.$plugin->name.DS.$plugin->name.'.php';

		if (!isset( $paths[$path] ) || !isset($paths[$legacypath])) {
			$pathExists = file_exists($path);
			if ($pathExists || file_exists($legacypath)) {
				$path = $pathExists ? $path : $legacypath;

				if (!isset($paths[$path])) {
					require_once $path;
				}
				$paths[$path] = true;

				if ($autocreate) {
					// Makes sure we have an event dispatcher
					if (!is_object($dispatcher)) {
						$dispatcher = JDispatcher::getInstance();
					}

					$className = 'plg'.$plugin->type.$plugin->name;
					if (class_exists($className)) {
						// Load the plugin from the database.
						$plugin = self::getPlugin16($plugin->type, $plugin->name);

						// Instantiate and register the plugin.
						$instance = new $className($dispatcher, (array)($plugin));
					
                                                return $instance;
                                        }
				}
			} else {
				$paths[$path] = false;
			}
		}
	}

	/**
	 * Loads the published plugins
	 *
	 * @access private
	 */
	function _load()
	{
    	$version = new JVersion();

		if(version_compare($version->getShortVersion(), '1.6', '>=') && version_compare($version->getShortVersion(), '1.7', '<')){
			return CBPluginHelper::_load16();
		} 
		else
		if(version_compare($version->getShortVersion(), '1.6', '<')){
			return CBPluginHelper::_load15();
		}
		return;
	}
        
        function _load15(){
            static $plugins;

		if (isset($plugins)) {
			return $plugins;
		}

		$db		=& Factory::getContainer()->get(DatabaseInterface::class);
		$user	=& Factory::getApplication()->getIdentity();

		if (isset($user))
		{
			$aid = $user->get('aid', 0);

			$query = 'SELECT folder AS type, element AS name, params'
				. ' FROM #__plugins'
				. ' WHERE published >= 1'
				. ' AND access <= ' . (int) $aid
				. ' ORDER BY ordering';
		}
		else
		{
			$query = 'SELECT folder AS type, element AS name, params'
				. ' FROM #__plugins'
				. ' WHERE published >= 1'
				. ' ORDER BY ordering';
		}

		$db->setQuery( $query );

		if (!($plugins = $db->loadObjectList())) {
			Factory::getApplication()->enqueueMessage("Error loading Plugins: " . $db->getErrorMsg(), 'error');
			return false;
		}

		return $plugins;
        }
        
        function _load16(){
            static $plugins;

		$user	= Factory::getApplication()->getIdentity();
		$cache 	= Factory::getCache('com_plugins', '');

		$levels = implode(',', $user->getAuthorisedViewLevels());

		if (!$plugins = $cache->get($levels)) {
                    
			$db		= Factory::getContainer()->get(DatabaseInterface::class);
			$query	= $db->getQuery(true);

			$query->select('folder AS type, element AS name, params')
				->from('#__extensions')
				->where('enabled >= 1')
				->where('type ='.$db->Quote('plugin'))
				->where('state >= 0')
				->where('access IN ('.$levels.')')
				->order('ordering');

			$plugins = $db->setQuery($query)
				->loadObjectList();

			if ($error = $db->getErrorMsg()) {
				Factory::getApplication()->enqueueMessage($error, 'error');
				return false;
			}

			$cache->store($plugins, $levels);
		}

		return $plugins;
        }

}
