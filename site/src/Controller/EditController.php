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
       
        CBRequest::setVar('cbIsNew', 0);

        if (CBRequest::getCmd('task', '') == 'delete' || CBRequest::getCmd('task', '') == 'publish') {
            $items = CBRequest::getVar('cid', array(), 'request', 'array');
            ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id', 0), $items, $this->frontend ? '_fe' : '');
        } else {
            if (CBRequest::getCmd('record_id', '')) {
                ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id', 0), CBRequest::getCmd('record_id', ''), $this->frontend ? '_fe' : '');
            } else {
                CBRequest::setVar('cbIsNew', 1);
                ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id', 0), 0, $this->frontend ? '_fe' : '');
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
    public function getModel($name = 'Edit', $prefix = 'Contentbuilder', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save($apply = false)
    {

        if (Factory::getApplication()->isClient('site') && CBRequest::getInt('Itemid', 0)) {
            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();
            if (is_object($item)) {
                CBRequest::setVar('cb_controller', $item->getParams()->get('cb_controller', null));
                CBRequest::setVar('cb_category_id', $item->getParams()->get('cb_category_id', null));
            }
        }

        CBRequest::setVar('cbIsNew', 0);
        CBRequest::setVar('ContentbuilderHelper::cbinternalCheck', 1);

        if (CBRequest::getCmd('record_id', '')) {
            ContentbuilderLegacyHelper::checkPermissions('Edit', Text::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        } else {
            CBRequest::setVar('cbIsNew', 1);
            ContentbuilderLegacyHelper::checkPermissions('new', Text::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        }

        $model = $this->getModel('Edit', 'Contentbuilder');
        $id = $model->store();

        $submission_failed = CBRequest::getBool('cb_submission_failed', false);
        $cb_submit_msg = CBRequest::setVar('cb_submit_msg', '');

        $type = 'message';
        if ($id && !$submission_failed) {

            $msg = Text::_('COM_CONTENTBUILDER_SAVED');
            $return = CBRequest::getVar('return', '');
            if ($return) {
                $return = base64_decode($return);

                if (!CBRequest::getBool('ContentbuilderHelper::cbinternalCheck', 1)) {
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

        if (CBRequest::getVar('cb_controller') == 'Edit') {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&task=edit.display&return=' . CBRequest::getVar('return', '') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        } else if ($apply) {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&task=edit.display&return=' . CBRequest::getVar('return', '') . '&backtolist=' . CBRequest::getInt('backtolist', 0) . '&id=' . CBRequest::getInt('id', 0) . '&record_id=' . $id . '&Itemid=' . CBRequest::getInt('Itemid', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'), false);
        } else {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&task=list.display&id=' . CBRequest::getInt('id', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
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

        $model = $this->getModel('Edit', 'Contentbuilder');
        $id = $model->delete();
        $msg = Text::_('COM_CONTENTBUILDER_ENTRIES_DELETED');
        $link = Route::_('index.php?option=com_contentbuilder&task=list.display&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    public function state()
    {
        ContentbuilderLegacyHelper::checkPermissions('state', Text::_('COM_CONTENTBUILDER_PERMISSIONS_STATE_CHANGE_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        $model = $this->getModel('Edit', 'Contentbuilder');
        $model->change_list_states();
        $msg = Text::_('COM_CONTENTBUILDER_STATES_CHANGED');
        $link = Route::_('index.php?option=com_contentbuilder&task=list.display&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    public function publish()
    {

        ContentbuilderLegacyHelper::checkPermissions('publish', Text::_('COM_CONTENTBUILDER_PERMISSIONS_PUBLISHING_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        $model = $this->getModel('Edit', 'Contentbuilder');
        $model->change_list_publish();
        if (CBRequest::getInt('list_publish', 0)) {
            $msg = Text::_('COM_CONTENTBUILDER_PUBLISHED');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_PUNPUBLISHED');
        }
        $link = Route::_('index.php?option=com_contentbuilder&task=list.display&id=' . CBRequest::getInt('id', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    public function language()
    {

        ContentbuilderLegacyHelper::checkPermissions('language', Text::_('COM_CONTENTBUILDER_PERMISSIONS_CHANGE_LANGUAGE_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        $model = $this->getModel('Edit', 'Contentbuilder');
        $model->change_list_language();
        $msg = Text::_('COM_CONTENTBUILDER_LANGUAGE_CHANGED');
        $link = Route::_('index.php?option=com_contentbuilder&task=list.display&id=' . CBRequest::getInt('id', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
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
        CBRequest::setVar('id', $formId);
        $this->input->set('view', 'edit');

        if ($recordId) {
            $this->input->set('record_id', $recordId);
            CBRequest::setVar('record_id', $recordId);
        }

        // Contexte CB correct pour cette page
        CBRequest::setVar('view', 'list');

        // Permissions
        ContentbuilderLegacyHelper::setPermissions($formId, $recordId, $suffix);
        
        if (CBRequest::getCmd('record_id', '')) {
            ContentbuilderLegacyHelper::checkPermissions('Edit', Text::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        } else {
            ContentbuilderLegacyHelper::checkPermissions('new', Text::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        }

        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl', null));
        CBRequest::setVar('layout', CBRequest::getWord('layout', null) == 'latest' ? null : CBRequest::getWord('layout', null));
        CBRequest::setVar('view', 'Edit');

        parent::display();
    }
}
