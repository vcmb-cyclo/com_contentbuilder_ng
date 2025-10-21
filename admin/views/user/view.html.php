<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// no direct access

defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;

require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

class ContentbuilderViewUser extends HtmlView
{
    function display($tpl = null)
    {
        echo '<link rel="stylesheet" href="' . Uri::root(true) . '/administrator/components/com_contentbuilder/views/bluestork.fix.css" type="text/css" />';

        // Get data from the model
        $subject = $this->get('Data');
        $this->subject = $subject;
        parent::display($tpl);
    }
}
