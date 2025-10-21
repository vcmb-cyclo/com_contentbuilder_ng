<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// use Joomla\CMS\HTML\HTMLHelper;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'controllerlegacy.php');

class ContentbuilderControllerForms extends CBController
{
    function __construct()
    {
        parent::__construct();

        //HTMLHelper::_('bootstrap.modal');

        if (CBRequest::getInt('email_users', -1) != -1) {
            Factory::getApplication()->getSession()->set('email_users', CBRequest::getVar('email_users', 'none'), 'com_contentbuilder');
        }
        if (CBRequest::getInt('email_admins', -1) != -1) {
            Factory::getApplication()->getSession()->set('email_admins', CBRequest::getVar('email_admins', ''), 'com_contentbuilder');
        }
        if (CBRequest::getInt('slideStartOffset', -1) != -1) {
            Factory::getApplication()->getSession()->set('slideStartOffset', CBRequest::getInt('slideStartOffset', 1));
        }
        if (CBRequest::getInt('tabStartOffset', -1) != -1) {
            Factory::getApplication()->getSession()->set('tabStartOffset', CBRequest::getInt('tabStartOffset', 0));
        }
        // Register Extra tasks
        $this->registerTask('add', 'edit');
    }

    function copy()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        if (count($cid) > 0) {
            $model = $this->getModel('forms');
            $model->copy();
        }

        $this->setRedirect('index.php?option=com_contentbuilder&controller=forms&limitstart=' . CBRequest::getInt('limitstart'), Text::_('COM_CONTENTBUILDER_COPIED'));
    }

    function orderup()
    {
        $model = $this->getModel('form');
        $model->move(-1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=forms&limitstart=' . CBRequest::getInt('limitstart'), false));
    }

    function listorderup()
    {
        $model = $this->getModel('form');
        $model->listMove(-1);
        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));
        parent::display();
    }

    function orderdown()
    {
        $model = $this->getModel('form');
        $model->move(1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=forms&limitstart=' . CBRequest::getInt('limitstart'), false));
    }

    function listorderdown()
    {
        $model = $this->getModel('form');
        $model->listMove(1);
        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));
        parent::display();
    }

    function saveorder()
    {
        $model = $this->getModel('forms');
        $model->saveOrder();
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=forms&limitstart=' . CBRequest::getInt('limitstart'), false));
    }


    function listsaveorder()
    {
        $model = $this->getModel('form');
        $model->listSaveOrder();
        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function publish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        if (count($cid) == 1) {
            $model = $this->getModel('form');
            $model->setPublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('form');
            $model->setPublished();
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=forms&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_PUBLISHED'));
    }

    function listpublish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListPublished();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function linkable()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListLinkable();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function editable()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListEditable();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function editable_include()
    {
        $this->editable();
    }

    function list_include()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListListInclude();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function search_include()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListSearchInclude();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function unpublish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        if (count($cid) == 1) {
            $model = $this->getModel('form');
            $model->setUnpublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('form');
            $model->setUnpublished();
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=forms&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_UNPUBLISHED'));
    }

    function listunpublish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListUnpublished();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function not_linkable()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListNotLinkable();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function not_editable()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListNotEditable();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function no_list_include()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListNoListInclude();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    function no_search_include()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('form');
        $model->setListNoSearchInclude();

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    /**
     * display the edit form
     * @return void
     */
    function edit()
    {
        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        parent::display();
    }

    function apply()
    {
        $this->save(true);
    }

    function save($keep_task = false)
    {

        $model = $this->getModel('form');
        $id = $model->store();

        if ($id) {
            $msg = Text::_('COM_CONTENTBUILDER_SAVED');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        }

        $limit = 0;
        $additionalParams = '';
        if ($keep_task) {
            if ($id) {
                $additionalParams = '&task=edit&cid[]=' . $id;
                $limit = CBRequest::getInt('limitstart');
            }
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = Route::_('index.php?option=com_contentbuilder&controller=forms&limitstart=' . $limit . $additionalParams, false);
        $this->setRedirect($link, $msg);
    }

    function saveNew()
    {
        $model = $this->getModel('form');

        if ($model->store()) {
            $msg = Text::_('COM_CONTENTBUILDER_SAVED');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = Route::_('index.php?option=com_contentbuilder&controller=forms&task=edit&limitstart=' . CBRequest::getInt('limitstart'), false);
        $this->setRedirect($link, $msg);
    }

    function remove()
    {
        $model = $this->getModel('form');
        if (!$model->delete()) {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_DELETED');
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=forms&limitstart=' . CBRequest::getInt('limitstart'), false), $msg);
    }

    function listremove()
    {
        $model = $this->getModel('form');
        if (!$model->listDelete()) {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_DELETED');
        }

        CBRequest::setVar('view', 'form');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart', 0));

        parent::display();
    }

    function cancel()
    {
        $msg = Text::_('COM_CONTENTBUILDER_CANCELLED');
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=forms&limitstart=0', false), $msg);
    }

    function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl', null));
        CBRequest::setVar('layout', CBRequest::getWord('layout', null));
        CBRequest::setVar('view', 'forms');

        parent::display();
    }
}
