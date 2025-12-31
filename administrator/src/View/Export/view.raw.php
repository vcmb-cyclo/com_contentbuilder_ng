<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\Export;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;

require_once (JPATH_SITE .'/administrator/components/com_contentbuilder/classes/viewlegacy.php');

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
