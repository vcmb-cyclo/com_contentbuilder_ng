<?php
/**
 * ContentBuilder Storages controller.
 *
 * Handles actions (copy, delete, publish, ...) for storage list in the admin interface.
 *
 * @package     ContentBuilder
 * @subpackage  Administrator.Controller
 * @author      Markus Bopp / XDA+GIL
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use CB\Component\Contentbuilder\Administrator\Helper\Logger;

final class StoragesController extends AdminController
{
    /**
     * Nom de la vue liste et item (convention Joomla 6).
     */
    protected $view_list = 'storages';
    protected $view_item = 'storage';

    public function __construct(
        $config,
        MVCFactoryInterface $factory,
        CMSApplicationInterface $app,
        Input $input
    ) {
        // IMPORTANT : on transmet factory/app/input à BaseController
        parent::__construct($config, $factory, $app, $input);

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
    public function getModel($name = 'Storage', $prefix = 'Contentbuilder', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }


    /**
     * Retourne les conditions pour limiter le reorder aux enregistrements du même groupe
     * Si tu veux que TOUS les storages soient réordonnés ensemble (pas de groupe), retourne un tableau vide ou ['1 = 1']
     */
    protected function getReorderConditions($table): array
    {
        return [];
    }

    public function display($cachable = false, $urlparams = []): void
    {
        $this->input->set('view', $this->view_list);
        parent::display($cachable, $urlparams);
    }

    // Publish methode : manage both publish and unpublish
    public function publish()
    {
        // Vérif CSRF.
        $this->checkToken();

        $cid = (array) $this->input->get('cid', [], 'array');
        $cid = array_values(array_filter(array_map('intval', $cid)));
        $task = $this->input->getCmd('task'); // storages.publish / storages.unpublish

        Logger::debug('Click [Un]Publish action', [
            'task' => $task,
            'cid'  => $cid,
        ]);

        $model = $this->getModel('Storage', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('StorageModel introuvable');
        }

        $value = str_contains($task, 'unpublish') ? 0 : 1;

        try {
            $result = $model->publish($cid, $value);
            // Message OK
            /* $count = count((array) $cid);
            $this->setMessage(Text::sprintf(
                $value ? 'COM_CONTENTBUILDER_N_ITEMS_PUBLISHED' : 'COM_CONTENTBUILDER_N_ITEMS_UNPUBLISHED',
                $count
            ));*/

            $this->setMessage(
                $value ? Text::_('COM_CONTENTBUILDER_PUBLISHED')
                       : Text::_('COM_CONTENTBUILDER_UNPUBLISHED'),
                'message'
            );
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_contentbuilder&task=storages.display');
    }

    public function delete(): void
    {
        // Vérif CSRF.
        $this->checkToken();

        $cid = (array) $this->input->get('cid', [], 'array');
        $cid = array_values(array_filter(array_map('intval', $cid)));

        Logger::debug('Click Delete action', [
            'task' => $this->input->getCmd('task'),
            'cid'  => $cid,
        ]);

        $model = $this->getModel('Storage', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('StorageModel introuvable');
        }

        try {
            $model->delete($cid);

            $count = count($cid);
            // Message Joomla standard (tu peux aussi faire tes propres Text::sprintf)
            $this->setMessage(
                Text::plural('JLIB_APPLICATION_N_ITEMS_DELETED', $count),
                'message'
            );
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_contentbuilder&task=storages.display');
    }

    /**
     * Copie (custom)
     */
    public function copy(): void
    {
        // Vérif CSRF.
        $this->checkToken();

        $cid = (array) $this->input->get('cid', [], 'array');
        $cid = array_values(array_filter(array_map('intval', $cid)));

        Logger::debug('Click Copy action', [
            'task' => $this->input->getCmd('task'),
            'cid'  => $cid,
        ]);

        $model = $this->getModel('Storage', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('StorageModel introuvable');
        }

        try {
            $model->copy($cid);

            // Message Joomla standard (tu peux aussi faire tes propres Text::sprintf)
            $this->setMessage(
                Text::plural('COM_CONTENTBUILDER_COPIED'),
                'message'
            );
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&task=storages.display&limitstart=' . $this->input->getInt('limitstart'), false),
            Text::_('COM_CONTENTBUILDER_COPIED')
        );
    }
}

    /*
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

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&task=storages.display&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_PUBLISHED'));
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

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&task=storages.display&limitstart=' . CBRequest::getInt('limitstart'), false), Text::_('COM_CONTENTBUILDER_UNPUBLISHED'));
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
*/