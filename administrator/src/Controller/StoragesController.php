<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;


// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use CB\Component\Contentbuilder\Administrator\CBRequest;

class StoragesController extends BaseController
{
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Register Extra tasks
        $this->registerTask('add', 'edit');
    }

    function orderup()
    {
        $model = $this->getModel('storage');
        $model->move(-1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storages&limitstart=' . CBRequest::getInt('limitstart'), false));
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
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storages&limitstart=' . CBRequest::getInt('limitstart'), false));
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

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storages&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_PUBLISHED'));
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

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storages&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_UNPUBLISHED'));
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

    function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl', null));
        CBRequest::setVar('layout', CBRequest::getWord('layout', null));
        CBRequest::setVar('view', 'storages');

        parent::display();
    }
}
