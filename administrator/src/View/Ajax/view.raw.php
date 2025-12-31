<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder\Administrator\View\Ajax;

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\MVC\View\HtmlView;


class ContentbuilderViewAjax extends HtmlView
{
    function display($tpl = null)
    {
        // Get data from the model
        $data = $this->get('Data');
        $this->data = $data;
        parent::display($tpl);
    }
}
