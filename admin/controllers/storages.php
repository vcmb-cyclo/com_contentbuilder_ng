<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright Copyright (C) 2024 by XDA+GIL 
 * @license     GNU/GPL
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\Router\Route;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'controllerlegacy.php');

class ContentbuilderControllerStorages extends CBController
{
    function __construct()
    {
        parent::__construct();

        // Register Extra tasks
        $this->registerTask('add', 'edit');
    }

    function orderup()
    {
        $model = $this->getModel('storage');
        $model->move(-1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=storages&limitstart=' . CBRequest::getInt('limitstart'), false));
    }

    function listdelete()
    {
        $model = $this->getModel('storage');
        $model->listDelete();
        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_DELETED'));
        CBRequest::setVar('view', 'storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));
        parent::display();
    }

    function listorderup()
    {
        $model = $this->getModel('storage');
        $model->listMove(-1);
        CBRequest::setVar('view', 'storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));
        parent::display();
    }

    function orderdown()
    {
        $model = $this->getModel('storage');
        $model->move(1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=storages&limitstart=' . CBRequest::getInt('limitstart'), false));
    }

    function listorderdown()
    {
        $model = $this->getModel('storage');
        $model->listMove(1);
        CBRequest::setVar('view', 'storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));
        parent::display();
    }

    function saveorder()
    {
        $model = $this->getModel('storages');
        $model->saveOrder();
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=storages&limitstart=' . CBRequest::getInt('limitstart'), false));
    }


    function listsaveorder()
    {
        $model = $this->getModel('storage');
        $model->listSaveOrder();
        CBRequest::setVar('view', 'storage');
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
            $model = $this->getModel('storage');
            $model->setPublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('storage');
            $model->setPublished();
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=storages&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_PUBLISHED'));
    }

    function listpublish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('storage');
        $model->setListPublished();

        CBRequest::setVar('view', 'storage');
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
            $model = $this->getModel('storage');
            $model->setUnpublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('storage');
            $model->setUnpublished();
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=storages&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_UNPUBLISHED'));
    }

    function listunpublish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('storage');
        $model->setListUnpublished();

        CBRequest::setVar('view', 'storage');
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
        CBRequest::setVar('view', 'storage');
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
        $model = $this->getModel('storage');

        $file = CBRequest::getVar('csv_file', null, 'files', 'array');

        if (trim(File::makeSafe($file['name'])) == '' || $file['size'] <= 0) {
            $id = $model->store();
        } else {
            $id = $model->storeCsv($file);
        }

        if (is_numeric($id) && $id) {
            $msg = Text::_('COM_CONTENTBUILDER_SAVED');
        } else if (!is_numeric($id) && !is_bool($id) && is_string($id)) {
            $msg = $id;
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        }

        $limit = 0;
        $additionalParams = '';
        if ($keep_task) {
            if (!is_string($id) && $id) {
                $additionalParams = '&task=edit&cid[]=' . $id;
                $limit = CBRequest::getInt('limitstart');
            }
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = Route::_('index.php?option=com_contentbuilder&controller=storages&limitstart=' . $limit . $additionalParams, false);
        $this->setRedirect($link, $msg);
    }

    function saveNew()
    {
        $model = $this->getModel('storage');

        if ($model->store()) {
            $msg = Text::_('COM_CONTENTBUILDER_SAVED');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = Route::_('index.php?option=com_contentbuilder&controller=storages&task=edit&limitstart=' . CBRequest::getInt('limitstart'), false);
        $this->setRedirect($link, $msg);
    }

    function remove()
    {
        $model = $this->getModel('storage');
        if (!$model->delete()) {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_DELETED');
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=storages&limitstart=' . CBRequest::getInt('limitstart'), false), $msg);
    }

    function listremove()
    {
        $model = $this->getModel('storage');
        if (!$model->listDelete()) {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_DELETED');
        }

        CBRequest::setVar('view', 'storage');
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
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&controller=storages&limitstart=0', false), $msg);
    }

    function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl', null));
        CBRequest::setVar('layout', CBRequest::getWord('layout', null));
        CBRequest::setVar('view', 'storages');

        parent::display();
    }
}
