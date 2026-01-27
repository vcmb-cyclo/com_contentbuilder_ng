<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Site\Controller;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;

class EditController extends BaseController
{
    private bool $frontend;

    public function __construct(
        $config,
        MVCFactoryInterface $factory,
        CMSApplicationInterface $app,
        Input $input
    ) {
        // IMPORTANT : on transmet factory/app/input à BaseController
        parent::__construct($config, $factory, $app, $input);

        $this->frontend = Factory::getApplication()->isClient('site');
       
        Factory::getApplication()->input->set('cbIsNew', 0);

        if (Factory::getApplication()->input->getCmd('task', '') == 'delete' || Factory::getApplication()->input->getCmd('task', '') == 'publish') {
            $items = Factory::getApplication()->input->get('cid', [], 'array');
            ContentbuilderLegacyHelper::setPermissions(Factory::getApplication()->input->getInt('id', 0), $items, $this->frontend ? '_fe' : '');
        } else {
            if (Factory::getApplication()->input->getCmd('record_id', '')) {
                ContentbuilderLegacyHelper::setPermissions(Factory::getApplication()->input->getInt('id', 0), Factory::getApplication()->input->getCmd('record_id', ''), $this->frontend ? '_fe' : '');
            } else {
                Factory::getApplication()->input->set('cbIsNew', 1);
                ContentbuilderLegacyHelper::setPermissions(Factory::getApplication()->input->getInt('id', 0), 0, $this->frontend ? '_fe' : '');
            }
        }
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
    public function getModel($name = 'Edit', $prefix = 'Site', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save($apply = false)
    {

        if (Factory::getApplication()->isClient('site') && Factory::getApplication()->input->getInt('Itemid', 0)) {
            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();
            if (is_object($item)) {
                Factory::getApplication()->input->set('cb_controller', $item->getParams()->get('cb_controller', null));
                Factory::getApplication()->input->set('cb_category_id', $item->getParams()->get('cb_category_id', null));
            }
        }

        Factory::getApplication()->input->set('cbIsNew', 0);
        Factory::getApplication()->input->set('ContentbuilderHelper::cbinternalCheck', 1);

        if (Factory::getApplication()->input->getCmd('record_id', '')) {
            ContentbuilderLegacyHelper::checkPermissions('edit', Text::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        } else {
            Factory::getApplication()->input->set('cbIsNew', 1);
            ContentbuilderLegacyHelper::checkPermissions('new', Text::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        }

        $model = $this->getModel('Edit', 'Site', ['ignore_request' => true])
            ?: $this->getModel('Edit', 'Contentbuilder', ['ignore_request' => true]);

        if (!$model) {
            throw new \RuntimeException('EditModel not found');
        }
        $id = $model->store();

        $submission_failed = Factory::getApplication()->input->getBool('cb_submission_failed', false);
        $cb_submit_msg = Factory::getApplication()->input->set('cb_submit_msg', '');

        $type = 'message';
        if ($id && !$submission_failed) {

            $msg = Text::_('COM_CONTENTBUILDER_SAVED');
            $return = Factory::getApplication()->input->get('return', '', 'string');
            if ($return) {
                $return = base64_decode($return);

                if (!Factory::getApplication()->input->getBool('ContentbuilderHelper::cbinternalCheck', 1)) {
                    Factory::getApplication()->enqueueMessage($msg, 'warning');
                    Factory::getApplication()->redirect($return);
                }
                if (Uri::isInternal($return)) {
                    Factory::getApplication()->enqueueMessage($msg, 'warning');
                    Factory::getApplication()->redirect($return);
                }
            }

        } else {
            $apply = true; // forcing to stay in form on errors
            $type = 'error';
        }

        $app = Factory::getApplication();
        $option = 'com_contentbuilder';
        $list = (array) $app->input->get('list', [], 'array');
        $limit = isset($list['limit']) ? $app->input->getInt('list[limit]', 0) : 0;
        if ($limit === 0) {
            $limit = (int) $app->getUserState($option . '.list.limit', 0);
        }
        if ($limit === 0) {
            $limit = (int) $app->get('list_limit');
        }
        $start = isset($list['start']) ? $app->input->getInt('list[start]', 0) : 0;
        if ($start === 0) {
            $start = (int) $app->getUserState($option . '.list.start', 0);
        }
        $ordering = isset($list['ordering']) ? $app->input->getCmd('list[ordering]', '') : '';
        if ($ordering === '') {
            $ordering = (string) $app->getUserState($option . 'formsd_filter_order', '');
        }
        $direction = isset($list['direction']) ? $app->input->getCmd('list[direction]', '') : '';
        if ($direction === '') {
            $direction = (string) $app->getUserState($option . 'formsd_filter_order_Dir', '');
        }
        $listQuery = http_build_query(['list' => [
            'limit' => $limit,
            'start' => $start,
            'ordering' => $ordering,
            'direction' => $direction,
        ]]);

        if (Factory::getApplication()->input->getString('cb_controller', '') == 'edit') {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . Factory::getApplication()->input->get('title', '', 'string') . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&task=edit.display&return=' . Factory::getApplication()->input->get('return', '', 'string') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0), false);
        } else if ($apply) {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . Factory::getApplication()->input->get('title', '', 'string') . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&task=edit.display&return=' . Factory::getApplication()->input->get('return', '', 'string') . '&backtolist=' . Factory::getApplication()->input->getInt('backtolist', 0) . '&id=' . Factory::getApplication()->input->getInt('id', 0) . '&record_id=' . $id . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . ($listQuery !== '' ? '&' . $listQuery : ''), false);
        } else {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . Factory::getApplication()->input->get('title', '', 'string') . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&task=list.display&id=' . Factory::getApplication()->input->getInt('id', 0) . ($listQuery !== '' ? '&' . $listQuery : '') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0), false);
        }
        $this->setRedirect($link, $msg, $type);
    }

    public function apply()
    {
        $this->save(true);
    }

    public function delete()
    {
        ContentbuilderLegacyHelper::checkPermissions('delete', Text::_('COM_CONTENTBUILDER_PERMISSIONS_DELETE_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        $model = $this->getModel('Edit', 'Site', ['ignore_request' => true])
            ?: $this->getModel('Edit', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('EditModel not found');
        }
        $ok = true;
        try {
            // Legacy model may not return a strict boolean; treat "no exception" as success.
            $model->delete();
        } catch (\Throwable $e) {
            $ok = false;
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }
        $msg = $ok ? Text::_('COM_CONTENTBUILDER_ENTRIES_DELETED') : Text::_('COM_CONTENTBUILDER_ERROR');
        $type = $ok ? 'message' : 'warning';

        // Clear record context to avoid redirects back to details/edit for a deleted record.
        $this->input->set('record_id', 0);
        Factory::getApplication()->input->set('record_id', 0);

        $listQuery = $this->buildListQuery();
        $link = Route::_(
            'index.php?option=com_contentbuilder&task=list.display&backtolist=1&id='
            . Factory::getApplication()->input->getInt('id', 0)
            . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '')
            . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '')
            . '&record_id='
            . ($listQuery !== '' ? '&' . $listQuery : '')
            . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0),
            false
        );

        $this->setRedirect($link, $msg, $type);
    }

    public function state()
    {
        ContentbuilderLegacyHelper::checkPermissions('state', Text::_('COM_CONTENTBUILDER_PERMISSIONS_STATE_CHANGE_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        $model = $this->getModel('Edit', 'Site', ['ignore_request' => true])
            ?: $this->getModel('Edit', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('EditModel not found');
        }
        $model->change_list_states();
        $msg = Text::_('COM_CONTENTBUILDER_STATES_CHANGED');
        $listQuery = $this->buildListQuery();
        $link = Route::_('index.php?option=com_contentbuilder&task=list.display&id=' . Factory::getApplication()->input->getInt('id', 0) . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . ($listQuery !== '' ? '&' . $listQuery : '') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    public function publish()
    {

        ContentbuilderLegacyHelper::checkPermissions('publish', Text::_('COM_CONTENTBUILDER_PERMISSIONS_PUBLISHING_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        $model = $this->getModel('Edit', 'Site');
        if (!$model) {
            $model = $this->getModel('Edit', 'Contentbuilder');
        }
        if (!$model) {
            throw new \RuntimeException('EditModel introuvable');
        }
        if (method_exists($model, 'setIds')) {
            $model->setIds(Factory::getApplication()->input->getInt('id', 0), Factory::getApplication()->input->getCmd('record_id', 0));
        }
        $model->change_list_publish();
        if (Factory::getApplication()->input->getInt('list_publish', 0)) {
            $msg = Text::_('COM_CONTENTBUILDER_PUBLISHED');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_PUNPUBLISHED');
        }
        $listQuery = $this->buildListQuery();
        $link = Route::_('index.php?option=com_contentbuilder&task=list.display&id=' . Factory::getApplication()->input->getInt('id', 0) . ($listQuery !== '' ? '&' . $listQuery : '') . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    public function language()
    {

        ContentbuilderLegacyHelper::checkPermissions('language', Text::_('COM_CONTENTBUILDER_PERMISSIONS_CHANGE_LANGUAGE_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        $model = $this->getModel('Edit', 'Site', ['ignore_request' => true])
            ?: $this->getModel('Edit', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('EditModel not found');
        }
        $model->change_list_language();
        $msg = Text::_('COM_CONTENTBUILDER_LANGUAGE_CHANGED');
        $listQuery = $this->buildListQuery();
        $link = Route::_('index.php?option=com_contentbuilder&task=list.display&id=' . Factory::getApplication()->input->getInt('id', 0) . ($listQuery !== '' ? '&' . $listQuery : '') . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    private function buildListQuery(): string
    {
        $app = Factory::getApplication();
        $option = 'com_contentbuilder';
        $list = (array) $app->input->get('list', [], 'array');

        $limit = isset($list['limit']) ? $app->input->getInt('list[limit]', 0) : 0;
        if ($limit === 0) {
            $limit = (int) $app->getUserState($option . '.list.limit', 0);
        }
        if ($limit === 0) {
            $limit = (int) $app->get('list_limit');
        }

        $start = isset($list['start']) ? $app->input->getInt('list[start]', 0) : 0;
        if ($start === 0) {
            $start = (int) $app->getUserState($option . '.list.start', 0);
        }

        $ordering = isset($list['ordering']) ? $app->input->getCmd('list[ordering]', '') : '';
        if ($ordering === '') {
            $ordering = (string) $app->getUserState($option . 'formsd_filter_order', '');
        }

        $direction = isset($list['direction']) ? $app->input->getCmd('list[direction]', '') : '';
        if ($direction === '') {
            $direction = (string) $app->getUserState($option . 'formsd_filter_order_Dir', '');
        }

        return http_build_query(['list' => [
            'limit' => $limit,
            'start' => $start,
            'ordering' => $ordering,
            'direction' => $direction,
        ]]);
    }

    public function display($cachable = false, $urlparams = array())
    {
        $app   = Factory::getApplication();

        // Si tu gardes le suffixe pour compat legacy :
        //$frontend = Factory::getApplication()->isClient('site');
        $suffix = '_fe';

        // 1) d'abord depuis l'URL
        $formId   = $this->input->getInt('id', 0);
        $recordId = $this->input->getInt('record_id', 0);

        // 2) sinon depuis les params du menu actif
        if (!$formId) {
            $menu = $app->getMenu()->getActive();
            if ($menu) {
                $formId = (int) $menu->getParams()->get('form_id', 0);
            }
        }

        // Synchroniser Joomla Input + CBRequest (legacy)
        $this->input->set('id', $formId);
        Factory::getApplication()->input->set('id', $formId);
        $this->input->set('view', 'edit');

        if ($recordId) {
            $this->input->set('record_id', $recordId);
            Factory::getApplication()->input->set('record_id', $recordId);
        }

        // Contexte CB correct pour cette page
        Factory::getApplication()->input->set('view', 'list');

        // Permissions
        ContentbuilderLegacyHelper::setPermissions($formId, $recordId, $suffix);
        
        if (Factory::getApplication()->input->getCmd('record_id', '')) {
            ContentbuilderLegacyHelper::checkPermissions('edit', Text::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        } else {
            ContentbuilderLegacyHelper::checkPermissions('new', Text::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        }

        Factory::getApplication()->input->set('tmpl', Factory::getApplication()->input->getWord('tmpl', null));
        Factory::getApplication()->input->set('layout', Factory::getApplication()->input->getWord('layout', null) == 'latest' ? null : Factory::getApplication()->input->getWord('layout', null));
        Factory::getApplication()->input->set('view', 'Edit');

        parent::display();
    }
}
