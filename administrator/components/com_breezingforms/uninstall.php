<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

function com_uninstall()
{
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $db->setQuery("Delete From #__menu Where `link` Like 'index.php?option=com_breezingforms&act=%'");
    $db->execute();
    $db->setQuery("Delete From #__menu Where `alias` Like 'BreezingForms' And `path` Like 'breezingforms'");
    $db->execute();

    if (file_exists(JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . 'facileforms.config.php')) {
        File::delete(JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . 'facileforms.config.php');
    }

    if (file_exists(JPATH_SITE . "/components/com_sh404sef/sef_ext/com_breezingforms.php")) {
        File::delete(JPATH_SITE . "/components/com_sh404sef/sef_ext/com_breezingforms.php");
    }

    if (file_exists(JPATH_SITE . '/ff_secimage.php'))
        File::delete(JPATH_SITE . '/ff_secimage.php');
    if (file_exists(JPATH_SITE . '/templates/system/ff_secimage.php'))
        File::delete(JPATH_SITE . '/templates/system/ff_secimage.php');
    if (file_exists(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/breezingforms_elements.xml"))
        File::delete(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/breezingforms_elements.xml");
    if (file_exists(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/translationFformFilter.php"))
        File::delete(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/translationFformFilter.php");
    if (file_exists(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/translationFformoptions_emptyFilter.php"))
        File::delete(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/translationFformoptions_emptyFilter.php");
}
