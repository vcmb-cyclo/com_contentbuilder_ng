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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;

class ListController extends BaseController
{
    public function __construct($config = [])
    {
        ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id',0),0, class_exists('cbFeMarker') ? '_fe' : '' );
        parent::__construct($config);
    }

    function display($cachable = false, $urlparams = array())
    {
        ContentbuilderLegacyHelper::checkPermissions('listaccess', Text::_('COM_CONTENTBUILDER_PERMISSIONS_LISTACCESS_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl',null));
        CBRequest::setVar('layout', CBRequest::getWord('layout',null) == 'latest' ? null : CBRequest::getWord('layout',null));
        CBRequest::setVar('view', 'list');

        parent::display();
    }
}
