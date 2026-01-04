<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

namespace Component\Contentbuilder\Administrator\Controller;


// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use Component\Contentbuilder\Administrator\CBRequest;
use Component\Contentbuilder\Administrator\Controller\BaseAdminController;

final class StoragesController extends BaseAdminController
{
    
    /**
     * Nom de la vue liste et item (convention Joomla 6).
     */
    protected $view_list = 'storages';
    protected $view_item = 'storage';


    public function __construct($config = [])
    {
        parent::__construct($config);

        // Register Extra tasks
        $this->registerTask('add', 'edit');
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|false  Model object on success; otherwise false on failure.
     */
    public function getModel($name = 'Storage', $prefix = '', $config = ['ignore_request' => true])
    {
        // On force explicitement le bon namespace complet
        $className = 'Component\\Contentbuilder\\Administrator\\Model\\StorageModel';

        if (!class_exists($className)) {
            // Si la classe n'existe pas, on laisse le parent essayer (mais ça plantera proprement)
            return parent::getModel($name, 'Administrator', $config);
        }

        // On instancie manuellement le modèle avec la factory
        $model = new $className($config);

        return $model;
    }

    public function orderup()
    {
        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->move(-1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storages&limitstart=' . CBRequest::getInt('limitstart'), false));
    }

    public function listdelete()
    {
        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->listDelete();
        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_DELETED'));
        CBRequest::setVar('view', 'Storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));
        parent::display();
    }

    public function listorderup()
    {
        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->listMove(-1);
        CBRequest::setVar('view', 'Storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));
        parent::display();
    }

    public function orderdown()
    {
        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->move(1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storages&limitstart=' . CBRequest::getInt('limitstart'), false));
    }

    public function listorderdown()
    {
        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->listMove(1);
        CBRequest::setVar('view', 'Storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));
        parent::display();
    }

     public function listsaveorder()
    {
        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->listSaveOrder();
        CBRequest::setVar('view', 'Storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    public function publish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        if (count($cid) == 1) {
            $model = $this->getModel('Storage', 'Contentbuilder');
            $model->setPublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('Storage', 'Contentbuilder');
            $model->setPublished();
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storages&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_PUBLISHED'));
    }

    public function listpublish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->setListPublished();

        CBRequest::setVar('view', 'Storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }

    public function unpublish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        if (count($cid) == 1) {
            $model = $this->getModel('Storage', 'Contentbuilder');
            $model->setUnpublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('Storage', 'Contentbuilder');
            $model->setUnpublished();
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storages&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_UNPUBLISHED'));
    }

    public function listunpublish()
    {
        $cid = CBRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->setListUnpublished();

        CBRequest::setVar('view', 'Storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart'));

        parent::display();
    }


    public function listremove()
    {
        $model = $this->getModel('Storage', 'Contentbuilder');
        if (!$model->listDelete()) {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_DELETED');
        }

        CBRequest::setVar('view', 'Storage');
        CBRequest::setVar('layout', 'form');
        CBRequest::setVar('hidemainmenu', 0);
        CBRequest::setVar('filter_order', 'ordering');
        CBRequest::setVar('filter_order_Dir', 'asc');
        CBRequest::setVar('limitstart', CBRequest::getInt('limitstart', 0));

        parent::display();
    }

    public function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl', null));
        CBRequest::setVar('layout', CBRequest::getWord('layout', null));
        CBRequest::setVar('view', 'storages');

        parent::display();
    }
}
