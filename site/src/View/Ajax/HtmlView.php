<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder_ng\Site\View\Ajax;

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use CB\Component\Contentbuilder_ng\Administrator\View\Contentbuilder_ng\HtmlView as BaseHtmlView;
use CB\Component\Contentbuilder_ng\Site\Model\AjaxModel;

class HtmlView extends BaseHtmlView
{
    function display($tpl = null)
    {
        /** @var AjaxModel $model */
        $model = $this->getModel();

        // Get data from the model
        $data = $model->getData();
        $this->data = $data;
        parent::display($tpl);
    }
}
