<?php
/**
 *
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/
final class BFText
{

	const COMPONENT_NAME = 'com_breezingforms';

	/**
	 * @var JLanguage
	 */
	private $language = null;

	/**
	 * @var BFText
	 */
	static private $bftext = null;

	/**
	 * Constructor is private, only BFText::_(string) is allowed to be used from outside
	 */
	private function __construct()
	{
	}

	/**
	 * Returns a new BFText object containing the language object.
	 * 
	 * @return BFText
	 */
	private static function getInstance()
	{
		if (!(self::$bftext instanceof BFText)) {
			self::$bftext = new BFText();
			self::$bftext->language = Factory::getApplication()->getLanguage();
		}

		return self::$bftext;
	}

	/**
	 * BFText::_(string) does the same like Text::_(string), except that it reloads the
	 * language for com_breezingforms if a key is not set. A key could be not set from within the HTML_* view functions because the language
	 * is not loaded there. So if one text request comes from one of these view functions, 
	 * it makes sure that the language is always been loaded (of course in a lazy way).
	 *
	 * @param string
	 * @return string
	 */
	public static function _($name)
	{
		$bftext = BFText::getInstance();
		if (!$bftext->language->hasKey($name)) {
			$bftext->language->load(BFText::COMPONENT_NAME);
		}

		// ok, loaded and ready to go
		return Text::_($name);
	}
}