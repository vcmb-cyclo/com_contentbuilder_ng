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
use Joomla\CMS\MVC\View\HtmlView;

require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

class ContentbuilderViewExport extends HtmlView
{
    function display($tpl = null)
    {
        // Get data from the model
        $data = $this->get('Data');
        $this->data = $data;
        parent::display($tpl);
    }
}
