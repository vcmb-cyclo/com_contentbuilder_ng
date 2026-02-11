<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder_ng\Site\View\Ajax;

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use CB\Component\Contentbuilder_ng\Administrator\View\Contentbuilder_ng\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    function display($tpl = null)
    {
        // Get data from the model
        $data = $this->get('Data');
        $this->data = $data;
        parent::display($tpl);
    }
}
