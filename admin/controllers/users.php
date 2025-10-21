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
use Joomla\CMS\Router\Route;

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'controllerlegacy.php');

class ContentbuilderControllerUsers extends CBController
{
    function __construct()
    {
        parent::__construct();
        
        // Register Extra tasks
        $this->registerTask( 'add', 'edit' );
    }
    
    function verified_view() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListVerifiedView();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    function not_verified_view() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListNotVerifiedView();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    function verified_new() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListVerifiedNew();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    function not_verified_new() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListNotVerifiedNew();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    function verified_edit() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListVerifiedEdit();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    function not_verified_edit() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListNotVerifiedEdit();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    function edit()
    {
        CBRequest::setVar( 'view', 'user' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        parent::display();
    }

    function apply()
    {
        $this->save(true);
    }

    function publish() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        if(count($cid) == 1)
        {
            $model = $this->getModel( 'users' );
            $model->setPublished();
        }
        else if(count($cid) > 1)
        {
            $model = $this->getModel( 'users' );
            $model->setPublished();
        }

        $this->setRedirect( Route::_('index.php?option=com_contentbuilder&controller=users&form_id='.CBRequest::getInt('form_id',0).'&tmpl='.CBRequest::getCmd('tmpl','').'&limitstart='.CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_PUBLISHED') );
    }
    
    function unpublish() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        if(count($cid) == 1)
        {
            $model = $this->getModel( 'users' );
            $model->setUnpublished();
        }
        else if(count($cid) > 1)
        {
            $model = $this->getModel( 'users' );
            $model->setUnpublished();
        }

        $this->setRedirect( Route::_('index.php?option=com_contentbuilder&controller=users&form_id='.CBRequest::getInt('form_id',0).'&tmpl='.CBRequest::getCmd('tmpl','').'&limitstart='.CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_UNPUBLISHED') );
    }
    
    function save($keep_task = false)
    {
        $model = $this->getModel('user');
        $id = $model->store();
        
        if ($id) {
            $msg = Text::_( 'COM_CONTENTBUILDER_SAVED' );
        } else {
            $msg = Text::_( 'COM_CONTENTBUILDER_ERROR' );
        }

        $limit = 0;
        $additionalParams = '';
        if($keep_task){
            if($id){
                $additionalParams = '&task=edit&joomla_userid='.$id;
                $limit = CBRequest::getInt('limitstart');
            }
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = 'index.php?option=com_contentbuilder&controller=users&form_id='.CBRequest::getInt('form_id',0).'&tmpl='.CBRequest::getCmd('tmpl','').'&limitstart='.$limit.$additionalParams;
        $this->setRedirect(Route::_($link, false), $msg);
    }

    function cancel()
    {
        $msg = Text::_( 'COM_CONTENTBUILDER_CANCELLED' );
        $this->setRedirect( Route::_('index.php?option=com_contentbuilder&controller=users&form_id='.CBRequest::getInt('form_id',0).'&tmpl='.CBRequest::getCmd('tmpl','').'&limitstart=0', false), $msg );
    }

    function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl',null));
        CBRequest::setVar('layout', CBRequest::getWord('layout',null));
        CBRequest::setVar('view', 'users');

        parent::display();
    }
}
