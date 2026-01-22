<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA + GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder\Administrator\Controller;

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use CB\Component\Contentbuilder\Administrator\CBRequest;

class UsersController extends BaseController
{
    public function __construct(
        $config,
        MVCFactoryInterface $factory,
        CMSApplicationInterface $app,
        Input $input
    ) {
        // IMPORTANT : on transmet factory/app/input à BaseController
        parent::__construct($config, $factory, $app, $input);
        
        // Register Extra tasks
        $this->registerTask( 'add', 'edit' );
    }
    
    public function verified_view() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'User' );
        $model->setListVerifiedView();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    public function not_verified_view() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'User' );
        $model->setListNotVerifiedView();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    public function verified_new() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'User' );
        $model->setListVerifiedNew();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    public function not_verified_new() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'User' );
        $model->setListNotVerifiedNew();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    public function verified_edit() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'User' );
        $model->setListVerifiedEdit();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    public function not_verified_edit() {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'User' );
        $model->setListNotVerifiedEdit();

        CBRequest::setVar( 'view', 'users' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        CBRequest::setVar( 'limitstart', CBRequest::getInt('limitstart') );

        parent::display();
    }
    
    public function edit()
    {
        CBRequest::setVar( 'view', 'User' );
        CBRequest::setVar( 'layout', 'default'  );
        CBRequest::setVar( 'hidemainmenu', 1 );
        CBRequest::setVar( 'filter_order', 'ordering' );
        CBRequest::setVar( 'filter_order_Dir', 'asc' );
        parent::display();
    }

    public function apply()
    {
        $this->save(true);
    }

    public function publish() {
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

        $this->setRedirect( Route::_('index.php?option=com_contentbuilder&view=users&form_id='.CBRequest::getInt('form_id',0).'&tmpl='.CBRequest::getCmd('tmpl','').'&limitstart='.CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_PUBLISHED') );
    }
    
    public function unpublish() {
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

        $this->setRedirect( Route::_('index.php?option=com_contentbuilder&view=users&form_id='.CBRequest::getInt('form_id',0).'&tmpl='.CBRequest::getCmd('tmpl','').'&limitstart='.CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_UNPUBLISHED') );
    }
    
    public function save($keep_task = false)
    {
        $model = $this->getModel('User', 'Contentbuilder');
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
                $additionalParams = '&task=User.edit&joomla_userid='.$id;
                $limit = CBRequest::getInt('limitstart');
            }
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = 'index.php?option=com_contentbuilder&view=users&form_id='.CBRequest::getInt('form_id',0).'&tmpl='.CBRequest::getCmd('tmpl','').'&limitstart='.$limit.$additionalParams;
        $this->setRedirect(Route::_($link, false), $msg);
    }

    public function cancel()
    {
        $msg = Text::_( 'COM_CONTENTBUILDER_CANCELLED' );
        $this->setRedirect( Route::_('index.php?option=com_contentbuilder&view=users&form_id='.CBRequest::getInt('form_id',0).'&tmpl='.CBRequest::getCmd('tmpl','').'&limitstart=0', false), $msg );
    }

    public function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl',null));
        CBRequest::setVar('layout', CBRequest::getWord('layout',null));
        CBRequest::setVar('view', 'users');

        parent::display();
    }
}
