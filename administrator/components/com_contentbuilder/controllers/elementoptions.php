<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');

CBCompat::requireController();

class ContentbuilderControllerElementoptions extends CBController
{
    function __construct()
    {
        jimport('joomla.html.pane');
        parent::__construct();
    }

    function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl',null));
        CBRequest::setVar('layout', CBRequest::getWord('layout',null));
        CBRequest::setVar('view', 'elementoptions');

        parent::display();
    }
    
    function save()
    {
        $model = $this->getModel('elementoptions');
        $id = $model->store();
        
        if ($id) {
            $msg = Text::_( 'COM_CONTENTBUILDER_SAVED' );
        } else {
            $msg = Text::_( 'COM_CONTENTBUILDER_ERROR' );
        }

        
        $type_change_url = '';
        $type_change = CBRequest::getInt('type_change',0);
        if($type_change){
            $type_change_url = '&type_change=1&type_selection='.CBRequest::getCmd('type_selection','');
        }
        
        // Check the table in so it can be edited.... we are done with it anyway
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=elementoptions&tabStartOffset='.CBRequest::getInt('tabStartOffset',0).'&tmpl=component&element_id='.CBRequest::getInt('element_id',  0).'&id='.CBRequest::getInt('id',  0).$type_change_url, false);
        $this->setRedirect($link, $msg);
    }
}
