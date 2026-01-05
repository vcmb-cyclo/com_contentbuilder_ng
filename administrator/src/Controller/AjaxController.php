<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder\Administrator\Controller;

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\MVC\Controller\BaseController;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;

class AjaxController extends BaseController
{
    public function __construct($config = [])
    {
        parent::__construct($config);
        
        ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id',0),0, class_exists('cbFeMarker') ? '_fe' : '');
    }

    function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl',null));
        CBRequest::setVar('layout', CBRequest::getWord('layout',null));
        CBRequest::setVar('view', 'ajax');
        CBRequest::setVar('format', 'raw');
        
        parent::display();
    }
}
