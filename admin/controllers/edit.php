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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;


require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'controllerlegacy.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderControllerEdit extends CBController
{
    function __construct()
    {
        CBRequest::setVar('cbIsNew', 0);

        if (CBRequest::getCmd('task', '') == 'delete' || CBRequest::getCmd('task', '') == 'publish') {
            $items = CBRequest::getVar('cid', array(), 'request', 'array');
            contentbuilder::setPermissions(CBRequest::getInt('id', 0), $items, class_exists('cbFeMarker') ? '_fe' : '');
        } else {
            if (CBRequest::getCmd('record_id', '')) {
                contentbuilder::setPermissions(CBRequest::getInt('id', 0), CBRequest::getCmd('record_id', ''), class_exists('cbFeMarker') ? '_fe' : '');
            } else {
                CBRequest::setVar('cbIsNew', 1);
                contentbuilder::setPermissions(CBRequest::getInt('id', 0), 0, class_exists('cbFeMarker') ? '_fe' : '');
            }
        }
        parent::__construct();
    }

    function save($apply = false)
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
        CBRequest::setVar('cbInternalCheck', 1);

        if (CBRequest::getCmd('record_id', '')) {
            contentbuilder::checkPermissions('edit', Text::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        } else {
            CBRequest::setVar('cbIsNew', 1);
            contentbuilder::checkPermissions('new', Text::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        }

        $model = $this->getModel('edit');
        $id = $model->store();

        $submission_failed = CBRequest::getBool('cb_submission_failed', false);
        $cb_submit_msg = CBRequest::setVar('cb_submit_msg', '');

        $type = 'message';
        if ($id && !$submission_failed) {

            $msg = Text::_('COM_CONTENTBUILDER_SAVED');
            $return = CBRequest::getVar('return', '');
            if ($return) {
                $return = cb_b64dec($return);

                if (!CBRequest::getBool('cbInternalCheck', 1)) {
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

        if (CBRequest::getVar('cb_controller') == 'edit') {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&controller=edit&return=' . CBRequest::getVar('return', '') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        } else if ($apply) {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&controller=edit&return=' . CBRequest::getVar('return', '') . '&backtolist=' . CBRequest::getInt('backtolist', 0) . '&id=' . CBRequest::getInt('id', 0) . '&record_id=' . $id . '&Itemid=' . CBRequest::getInt('Itemid', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'), false);
        } else {
            $link = Route::_('index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&controller=list&id=' . CBRequest::getInt('id', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        }
        $this->setRedirect($link, $msg, $type);
    }

    function apply()
    {
        $this->save(true);
    }

    function delete()
    {

        contentbuilder::checkPermissions('delete', Text::_('COM_CONTENTBUILDER_PERMISSIONS_DELETE_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        $model = $this->getModel('edit');
        $id = $model->delete();
        $msg = Text::_('COM_CONTENTBUILDER_ENTRIES_DELETED');
        $link = Route::_('index.php?option=com_contentbuilder&controller=list&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    function state()
    {

        contentbuilder::checkPermissions('state', Text::_('COM_CONTENTBUILDER_PERMISSIONS_STATE_CHANGE_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        $model = $this->getModel('edit');
        $model->change_list_states();
        $msg = Text::_('COM_CONTENTBUILDER_STATES_CHANGED');
        $link = Route::_('index.php?option=com_contentbuilder&controller=list&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    function publish()
    {

        contentbuilder::checkPermissions('publish', Text::_('COM_CONTENTBUILDER_PERMISSIONS_PUBLISHING_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        $model = $this->getModel('edit');
        $model->change_list_publish();
        if (CBRequest::getInt('list_publish', 0)) {
            $msg = Text::_('PUBLISHED');
        } else {
            $msg = Text::_('UNPUBLISHED');
        }
        $link = Route::_('index.php?option=com_contentbuilder&controller=list&id=' . CBRequest::getInt('id', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    function language()
    {

        contentbuilder::checkPermissions('language', Text::_('COM_CONTENTBUILDER_PERMISSIONS_CHANGE_LANGUAGE_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        $model = $this->getModel('edit');
        $model->change_list_language();
        $msg = Text::_('COM_CONTENTBUILDER_LANGUAGE_CHANGED');
        $link = Route::_('index.php?option=com_contentbuilder&controller=list&id=' . CBRequest::getInt('id', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&Itemid=' . CBRequest::getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    function display($cachable = false, $urlparams = array())
    {

        if (CBRequest::getCmd('record_id', '')) {
            contentbuilder::checkPermissions('edit', Text::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        } else {
            contentbuilder::checkPermissions('new', Text::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        }

        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl', null));
        CBRequest::setVar('layout', CBRequest::getWord('layout', null) == 'latest' ? null : CBRequest::getWord('layout', null));
        CBRequest::setVar('view', 'edit');

        parent::display();
    }
}
