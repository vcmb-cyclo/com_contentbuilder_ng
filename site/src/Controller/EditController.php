<?php

/**
 * @package     ContentBuilderNG
 * @author      Markus Bopp
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace CB\Component\Contentbuilderng\Site\Controller;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\Database\DatabaseInterface;
use CB\Component\Contentbuilderng\Administrator\Extension\ContentbuilderngComponent;
use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use CB\Component\Contentbuilderng\Administrator\Service\DirectStorageFormProvisioningService;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;
use CB\Component\Contentbuilderng\Site\Helper\MenuParamHelper;
use CB\Component\Contentbuilderng\Site\Helper\NavigationLinkHelper;
use CB\Component\Contentbuilderng\Site\Helper\PreviewColorModeHelper;
use CB\Component\Contentbuilderng\Site\Helper\PreviewLinkHelper;
use CB\Component\Contentbuilderng\Site\Model\EditModel;
use CB\Component\Contentbuilderng\Site\Service\EmbeddedListContextService;

class EditController extends BaseController
{
    private SiteApplication $siteApp;
    private bool $frontend;

    private function getPermissionService(): PermissionService
    {
        return PermissionService::createFromRuntimeContext();
    }

    private function getDirectStorageFormProvisioningService(): DirectStorageFormProvisioningService
    {
        $component = RuntimeContextHelper::getApplication()->bootComponent('com_contentbuilderng');

        if (!$component instanceof ContentbuilderngComponent) {
            throw new \RuntimeException('Unexpected component instance');
        }

        return $component->getContainer()->get(DirectStorageFormProvisioningService::class);
    }

    private function getComponentDatabase(): DatabaseInterface
    {
        $component = RuntimeContextHelper::getApplication()->bootComponent('com_contentbuilderng');

        if (!$component instanceof ContentbuilderngComponent) {
            throw new \RuntimeException('Unexpected component instance');
        }

        return $component->getContainer()->get(DatabaseInterface::class);
    }

    /**
     * En mode storage direct, garder "storage_id=" (et non le vrai id de
     * #__contentbuilderng_forms résolu à l'affichage) sur les liens vers une
     * autre page, pour que celle-ci redétecte isDirectStorageMode et valide
     * la signature preview.
     */
    private function buildIdOrStorageIdQueryParam(): string
    {
        $storageId = (int) $this->siteApp->getInput()->getInt('storage_id', 0);

        return $storageId > 0
            ? '&storage_id=' . $storageId
            : '&id=' . $this->siteApp->getInput()->getInt('id', 0);
    }

    private function getEditModel(array $config = ['ignore_request' => true]): EditModel
    {
        $model = $this->getModel('Edit', 'Site', $config)
            ?: $this->getModel('Edit', 'Contentbuilderng', $config);

        if (!$model instanceof EditModel) {
            throw new \RuntimeException('EditModel not found');
        }

        return $model;
    }

    private function applyPreviewContextForAction(): bool
    {
        $formId = (int) $this->input->getInt('id', 0);
        $storageId = (int) $this->input->getInt('storage_id', 0);

        if ($storageId > 0) {
            $db = $this->getComponentDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('bytable'))
                ->from($db->quoteName('#__contentbuilderng_storages'))
                ->where($db->quoteName('id') . ' = ' . $storageId);
            $query->setLimit(1);
            $db->setQuery($query);

            if ((int) $db->loadResult() === 2) {
                throw new \RuntimeException(Text::_('COM_CONTENTBUILDERNG_READONLY_EXTERNAL_STORAGE_MSG'), 403);
            }
        }

        $isAdminPreview = $this->isValidAdminPreviewRequest($formId, $storageId);
        $this->input->set('cb_preview_ok', $isAdminPreview ? 1 : 0);
        $this->siteApp->getInput()->set('cb_preview_ok', $isAdminPreview ? 1 : 0);

        if ($storageId > 0 && !$isAdminPreview) {
            $formId = $this->getDirectStorageFormProvisioningService()->resolveOrCreateFormId($storageId);
            $recordId = $this->input->getCmd('record_id', '');

            if ($formId < 1) {
                throw new \RuntimeException(Text::_('COM_CONTENTBUILDERNG_FORM_NOT_FOUND'), 404);
            }

            $this->input->set('id', $formId);
            $this->siteApp->getInput()->set('id', $formId);
            $this->getPermissionService()->setPermissions(
                $formId,
                $recordId,
                $this->frontend ? '_fe' : ''
            );
        }

        return $isAdminPreview;
    }

    private function checkPermissionForAjax(string $action, string $message): bool
    {
        try {
            $this->getPermissionService()->checkPermissions($action, $message, $this->frontend ? '_fe' : '');
        } catch (NotAllowed $e) {
            if ($this->isAjaxCall()) {
                $this->respondAjax(false, $e->getMessage());
                return false;
            }

            throw $e;
        }

        return true;
    }

    public function __construct(
        array $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSWebApplicationInterface $app = null,
        ?Input $input = null
    ) {
        // IMPORTANT : on transmet factory/app/input à BaseController
        parent::__construct($config, $factory, $app, $input);

        if (!$app instanceof SiteApplication) {
            throw new \RuntimeException('Unexpected application instance');
        }

        $this->siteApp = $app;
        $this->frontend = $this->siteApp->isClient('site');

        $this->siteApp->getInput()->set('cbIsNew', 0);
        $storageId = (int) $this->siteApp->getInput()->getInt('storage_id', 0);
        $isDirectStorageMode = $storageId > 0 && $this->siteApp->getInput()->getInt('id', 0) <= 0;
        $isAdminPreview = $isDirectStorageMode ? $this->isValidAdminPreviewRequest(0, $storageId) : false;

        $task = (string) $this->siteApp->getInput()->getCmd('task', '');
        $taskAction = str_contains($task, '.') ? substr($task, strrpos($task, '.') + 1) : $task;

        if ($isDirectStorageMode && $isAdminPreview) {
            $this->getPermissionService()->setStoragePreviewPermissions($storageId, $this->frontend ? '_fe' : '');
        } elseif (in_array($taskAction, ['delete', 'state', 'publish', 'language'], true)) {
            $items = $this->siteApp->getInput()->get('cid', [], 'array');
            $formId = (int) $this->siteApp->getInput()->getInt('id', 0);

            // Direct-storage mode dispatches with storage_id and no form id;
            // permissions still have to be computed against the backing form.
            if ($formId <= 0 && $storageId > 0) {
                $formId = (int) $this->getDirectStorageFormProvisioningService()->resolveOrCreateFormId($storageId);
            }

            $this->getPermissionService()->setPermissions($formId, $items, $this->frontend ? '_fe' : '');
        } else {
            if (!$isDirectStorageMode && $this->siteApp->getInput()->getCmd('record_id', '')) {
                $this->getPermissionService()->setPermissions($this->siteApp->getInput()->getInt('id', 0), $this->siteApp->getInput()->getCmd('record_id', ''), $this->frontend ? '_fe' : '');
            } elseif (!$isDirectStorageMode) {
                $this->siteApp->getInput()->set('cbIsNew', 1);
                $this->getPermissionService()->setPermissions($this->siteApp->getInput()->getInt('id', 0), 0, $this->frontend ? '_fe' : '');
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
    #[\Override]
    public function getModel($name = 'Edit', $prefix = 'Site', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save($apply = false)
    {
        $isAdminPreview = $this->applyPreviewContextForAction();

        if ($this->siteApp->isClient('site') && $this->siteApp->getInput()->getInt('Itemid', 0)) {
            $menu = $this->siteApp->getMenu();
            $item = $menu->getActive();
            if (is_object($item)) {
                $params = $item->getParams();
                $this->siteApp->getInput()->set('cb_controller', MenuParamHelper::getMenuParam($params, 'cb_controller', null));
                $this->siteApp->getInput()->set('cb_category_id', (int) MenuParamHelper::getMenuParam($params, 'cb_category_id', 0));
            }
        }

        $this->siteApp->getInput()->set('cbIsNew', 0);
        $this->siteApp->getInput()->set('ContentbuilderngHelper::cbinternalCheck', 1);

        $isEdit = (bool) $this->siteApp->getInput()->getCmd('record_id', '');
        if (!$isEdit) {
            $this->siteApp->getInput()->set('cbIsNew', 1);
        }

        if (!$isAdminPreview) {
            if ($isEdit) {
                $this->getPermissionService()->checkPermissions('edit', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_EDIT_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
            } else {
                $this->getPermissionService()->checkPermissions('new', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_NEW_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
            }
        }

        $model = $this->getEditModel(['ignore_request' => true]);
        if (!$model->isEditableTemplateConfigured()) {
            $link = Route::_('index.php?option=com_contentbuilderng&task=edit.display&id='
                . $this->siteApp->getInput()->getInt('id', 0)
                . '&record_id=' . $this->siteApp->getInput()->getCmd('record_id', '')
                . $this->buildEmbeddedListQuery()
                . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0), false);
            $this->setRedirect($link, Text::_('COM_CONTENTBUILDERNG_EDITABLE_TEMPLATE_NOT_SET'), 'error');
            return;
        }

        $id = $model->store();

        $submission_failed = $this->siteApp->getInput()->getBool('cb_submission_failed', false);
        $this->siteApp->getInput()->set('cb_submit_msg', '');

        $type = 'message';
        if ($id && !$submission_failed) {
            $msg = Text::_('COM_CONTENTBUILDERNG_SAVED');
            $return = NavigationLinkHelper::decodeInternalReturn((string) $this->siteApp->getInput()->get('return', '', 'string'));
            if ($return !== '') {
                $this->siteApp->enqueueMessage($msg, 'message');
                $this->siteApp->redirect($return);
            }
        } else {
            $apply = true; // forcing to stay in form on errors
            $type = 'error';
        }

        $previewQuery = $this->buildPreviewQuery();
        $listQuery = $this->buildListQuery();
        $embeddedListQuery = $this->buildEmbeddedListQuery();

        // En mode storage direct, retomber sur "id=<form résolu>" ferait perdre
        // le contexte storage_id sur la page suivante (isDirectStorageMode
        // redeviendrait faux), et donc la validation de la signature preview.
        $directStorageId = (int) $this->siteApp->getInput()->getInt('storage_id', 0);
        $idParam = $directStorageId > 0
            ? '&storage_id=' . $directStorageId
            : '&id=' . $this->siteApp->getInput()->getInt('id', 0);

        if ($this->siteApp->getInput()->getString('cb_controller', '') == 'edit') {
            $link = Route::_('index.php?option=com_contentbuilderng&title=' . $this->siteApp->getInput()->get('title', '', 'string') . ($this->siteApp->getInput()->get('tmpl', '', 'string') != '' ? '&tmpl=' . $this->siteApp->getInput()->get('tmpl', '', 'string') : '') . ($this->siteApp->getInput()->get('layout', '', 'string') != '' ? '&layout=' . $this->siteApp->getInput()->get('layout', '', 'string') : '') . '&task=edit.display&return=' . NavigationLinkHelper::encodeInternalReturn((string) $this->siteApp->getInput()->get('return', '', 'string')) . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0) . $previewQuery . $embeddedListQuery, false);
        } elseif ($apply) {
            $link = Route::_('index.php?option=com_contentbuilderng&title=' . $this->siteApp->getInput()->get('title', '', 'string') . ($this->siteApp->getInput()->get('tmpl', '', 'string') != '' ? '&tmpl=' . $this->siteApp->getInput()->get('tmpl', '', 'string') : '') . ($this->siteApp->getInput()->get('layout', '', 'string') != '' ? '&layout=' . $this->siteApp->getInput()->get('layout', '', 'string') : '') . '&task=edit.display&return=' . NavigationLinkHelper::encodeInternalReturn((string) $this->siteApp->getInput()->get('return', '', 'string')) . '&backtolist=' . $this->siteApp->getInput()->getInt('backtolist', 0) . $idParam . '&record_id=' . $id . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0) . ($listQuery !== '' ? '&' . $listQuery : '') . $previewQuery . $embeddedListQuery, false);
        } else {
            $link = Route::_('index.php?option=com_contentbuilderng&title=' . $this->siteApp->getInput()->get('title', '', 'string') . ($this->siteApp->getInput()->get('tmpl', '', 'string') != '' ? '&tmpl=' . $this->siteApp->getInput()->get('tmpl', '', 'string') : '') . ($this->siteApp->getInput()->get('layout', '', 'string') != '' ? '&layout=' . $this->siteApp->getInput()->get('layout', '', 'string') : '') . '&task=list.display' . $idParam . ($listQuery !== '' ? '&' . $listQuery : '') . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0) . $previewQuery . $embeddedListQuery, false);
        }
        $this->setRedirect($link, $msg, $type);
    }

    public function apply()
    {
        $this->save(true);
    }

    public function delete()
    {
        $this->checkToken('post');

        // Never bypassed for an admin-preview session: the preview permission
        // set (setStoragePreviewPermissions()) intentionally does not grant
        // delete, so this still correctly denies a preview link from deleting
        // records.
        $this->applyPreviewContextForAction();
        $this->getPermissionService()->checkPermissions('delete', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_DELETE_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        $selectedItems = array_values(
            array_filter(
                array_map('intval', (array) $this->input->get('cid', [], 'array')),
                static fn(int $id): bool => $id > 0
            )
        );

        if ($selectedItems === []) {
            $listQuery = $this->buildListQuery();
            $previewQuery = $this->buildPreviewQuery();
            $link = Route::_(
                'index.php?option=com_contentbuilderng&task=list.display&backtolist=1'
                . $this->buildIdOrStorageIdQueryParam()
                . ($this->siteApp->getInput()->get('tmpl', '', 'string') != '' ? '&tmpl=' . $this->siteApp->getInput()->get('tmpl', '', 'string') : '')
                . ($this->siteApp->getInput()->get('layout', '', 'string') != '' ? '&layout=' . $this->siteApp->getInput()->get('layout', '', 'string') : '')
                . '&record_id='
                . ($listQuery !== '' ? '&' . $listQuery : '')
                . $previewQuery
                . $this->buildEmbeddedListQuery()
                . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0),
                false
            );
            $this->setRedirect($link, Text::_('JERROR_NO_ITEMS_SELECTED'), 'warning');

            return;
        }

        $model = $this->getEditModel(['ignore_request' => true]);
        $ok = true;
        try {
            // The model may not return a strict boolean; treat "no exception" as success.
            $model->delete();
        } catch (\Throwable $e) {
            $ok = false;
            $this->siteApp->enqueueMessage($e->getMessage(), 'warning');
        }
        if ($ok) {
            $deletedCount = count($selectedItems);
            $msg = Text::plural('COM_CONTENTBUILDERNG_N_ITEMS_DELETED', $deletedCount);
        } else {
            $msg = Text::_('COM_CONTENTBUILDERNG_ERROR');
        }
        $type = $ok ? 'message' : 'warning';

        // Clear record context to avoid redirects back to details/edit for a deleted record.
        $this->input->set('record_id', 0);
        $this->siteApp->getInput()->set('record_id', 0);

        $listQuery = $this->buildListQuery();
        $previewQuery = $this->buildPreviewQuery();
        $link = Route::_(
            'index.php?option=com_contentbuilderng&task=list.display&backtolist=1'
            . $this->buildIdOrStorageIdQueryParam()
            . ($this->siteApp->getInput()->get('tmpl', '', 'string') != '' ? '&tmpl=' . $this->siteApp->getInput()->get('tmpl', '', 'string') : '')
            . ($this->siteApp->getInput()->get('layout', '', 'string') != '' ? '&layout=' . $this->siteApp->getInput()->get('layout', '', 'string') : '')
            . '&record_id='
            . ($listQuery !== '' ? '&' . $listQuery : '')
            . $previewQuery
            . $this->buildEmbeddedListQuery()
            . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0),
            false
        );

        $this->setRedirect($link, $msg, $type);
    }

    public function state()
    {
        if (!$this->checkToken('post', false)) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        // Never bypassed for an admin-preview session: the preview permission
        // set does not grant state changes, so this still correctly denies a
        // preview link from changing record state.
        $this->applyPreviewContextForAction();
        if (!$this->checkPermissionForAjax('state', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_STATE_CHANGE_NOT_ALLOWED'))) {
            return;
        }

        $model = $this->getEditModel(['ignore_request' => true]);
        if (!$model->isEditableTemplateConfigured()) {
            $message = Text::_('COM_CONTENTBUILDERNG_EDITABLE_TEMPLATE_NOT_SET');
            if ($this->isAjaxCall()) {
                $this->respondAjax(false, $message);
                return;
            }

            $this->setRedirect(Route::_('index.php?option=com_contentbuilderng&task=edit.display&id='
                . $this->siteApp->getInput()->getInt('id', 0)
                . '&record_id=' . $this->siteApp->getInput()->getCmd('record_id', '')
                . $this->buildEmbeddedListQuery()
                . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0), false), $message, 'error');
            return;
        }

        $changedCount = (int) $model->change_list_states();
        $msg = Text::plural('COM_CONTENTBUILDERNG_N_STATES_CHANGED', $changedCount);

        if ($this->isAjaxCall()) {
            $this->respondAjax(true, $msg);
            return;
        }

        $listQuery = $this->buildListQuery();
        $link = Route::_('index.php?option=com_contentbuilderng&task=list.display&id=' . $this->siteApp->getInput()->getInt('id', 0) . ($this->siteApp->getInput()->get('tmpl', '', 'string') != '' ? '&tmpl=' . $this->siteApp->getInput()->get('tmpl', '', 'string') : '') . ($this->siteApp->getInput()->get('layout', '', 'string') != '' ? '&layout=' . $this->siteApp->getInput()->get('layout', '', 'string') : '') . ($listQuery !== '' ? '&' . $listQuery : '') . $this->buildEmbeddedListQuery() . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    public function publish()
    {
        // "request" (not "post"): the per-row publish toggle is a GET link, so
        // the token is carried in the query string. The list template appends
        // it via Session::getFormToken().
        if (!$this->checkToken('request', false)) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $storageId = (int) $this->siteApp->getInput()->getInt('storage_id', 0);
        $isDirectStorageMode = $storageId > 0 && $this->siteApp->getInput()->getInt('id', 0) <= 0;
        // Never bypassed for an admin-preview session: both gates below are
        // still enforced, and the preview permission set does not grant
        // publish, so this still correctly denies a preview link from
        // publishing/unpublishing records.
        $this->applyPreviewContextForAction();

        // Direct-storage records require both the site-wide Joomla ACL rule
        // (core.edit.state) AND the form's own per-group "publish" permission
        // — neither one alone is sufficient.
        if ($isDirectStorageMode) {
            if (!$this->siteApp->getIdentity()->authorise('core.edit.state', 'com_contentbuilderng')) {
                if ($this->isAjaxCall()) {
                    $this->respondAjax(false, Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_PUBLISHING_NOT_ALLOWED'));
                    return;
                }

                throw new \RuntimeException(Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_PUBLISHING_NOT_ALLOWED'), 403);
            }
        }

        if (!$this->checkPermissionForAjax('publish', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_PUBLISHING_NOT_ALLOWED'))) {
            return;
        }

        $model = $this->getEditModel(['ignore_request' => true]);
        $model->setIds($this->siteApp->getInput()->getInt('id', 0), $this->siteApp->getInput()->getCmd('record_id', 0));
        $model->change_list_publish();
        if ($this->siteApp->getInput()->getInt('list_publish', 0)) {
            $msg = Text::_('COM_CONTENTBUILDERNG_LIST_STATES_PUBLISHED');
        } else {
            $msg = Text::_('COM_CONTENTBUILDERNG_UNPUBLISHED');
        }

        if ($this->isAjaxCall()) {
            $this->respondAjax(true, $msg);
            return;
        }

        $listQuery = $this->buildListQuery();
        $previewQuery = $this->buildPreviewQuery();
        $link = Route::_(
            'index.php?option=com_contentbuilderng&task=list.display&'
            . ($isDirectStorageMode ? 'storage_id=' . $storageId : 'id=' . $this->siteApp->getInput()->getInt('id', 0))
            . ($listQuery !== '' ? '&' . $listQuery : '')
            . ($this->siteApp->getInput()->get('tmpl', '', 'string') != '' ? '&tmpl=' . $this->siteApp->getInput()->get('tmpl', '', 'string') : '')
            . ($this->siteApp->getInput()->get('layout', '', 'string') != '' ? '&layout=' . $this->siteApp->getInput()->get('layout', '', 'string') : '')
            . $previewQuery
            . $this->buildEmbeddedListQuery()
            . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0),
            false
        );
        $this->setRedirect($link, $msg, 'message');
    }

    public function language()
    {
        if (!$this->checkToken('post', false)) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        // Never bypassed for an admin-preview session: the preview permission
        // set does not grant language changes, so this still correctly
        // denies a preview link from changing record language.
        $this->applyPreviewContextForAction();
        if (!$this->checkPermissionForAjax('language', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_CHANGE_LANGUAGE_NOT_ALLOWED'))) {
            return;
        }

        $model = $this->getEditModel(['ignore_request' => true]);
        $model->change_list_language();
        $msg = Text::_('COM_CONTENTBUILDERNG_LANGUAGE_CHANGED');
        $listQuery = $this->buildListQuery();
        $link = Route::_('index.php?option=com_contentbuilderng&task=list.display&id=' . $this->siteApp->getInput()->getInt('id', 0) . ($listQuery !== '' ? '&' . $listQuery : '') . ($this->siteApp->getInput()->get('tmpl', '', 'string') != '' ? '&tmpl=' . $this->siteApp->getInput()->get('tmpl', '', 'string') : '') . ($this->siteApp->getInput()->get('layout', '', 'string') != '' ? '&layout=' . $this->siteApp->getInput()->get('layout', '', 'string') : '') . $this->buildEmbeddedListQuery() . '&Itemid=' . $this->siteApp->getInput()->getInt('Itemid', 0), false);
        $this->setRedirect($link, $msg, 'message');
    }

    private function buildEmbeddedListQuery(): string
    {
        $query = EmbeddedListContextService::buildQuery(
            (string) $this->input->getCmd('cblist_embed', ''),
            (string) $this->input->getString('cblist_fields', ''),
            (string) $this->input->getString('cblist_actions', '')
        );

        $title = trim((string) $this->input->getString('cblist_title', ''));

        return $title === '' ? $query : $query . '&cblist_title=' . rawurlencode($title);
    }

    private function buildListQuery(): string
    {
        $state = $this->resolveListState();

        return http_build_query(['list' => [
            'limit' => $state['limit'],
            'start' => $state['start'],
            'ordering' => $state['ordering'],
            'direction' => $state['direction'],
        ]]);
    }

    private function resolveListState(): array
    {
        $option = 'com_contentbuilderng';
        $list = (array) $this->input->get('list', [], 'array');
        $stateKeyPrefix = $this->getPaginationStateKeyPrefix();
        $limitKey = $stateKeyPrefix . '.limit';
        $startKey = $stateKeyPrefix . '.start';
        $configuredLimit = MenuParamHelper::getConfiguredListLimit($this->siteApp, (int) $this->input->getInt('id', 0));
        $explicitLimitRequest = MenuParamHelper::hasExplicitListLimitRequest($this->siteApp);

        $limit = $explicitLimitRequest && isset($list['limit']) ? (int) $list['limit'] : 0;
        if ($limit === 0) {
            $limit = $configuredLimit;
        }
        if ($limit === 0) {
            $limit = (int) $this->siteApp->getUserState($limitKey, 0);
        }
        if ($limit === 0) {
            $limit = (int) $this->siteApp->get('list_limit');
        }
        if ($limit < 1) {
            $limit = 20;
        }

        if ($explicitLimitRequest && array_key_exists('start', $list)) {
            $start = max(0, (int) $list['start']);
        } elseif ($configuredLimit > 0) {
            $start = 0;
        } else {
            $start = (int) $this->siteApp->getUserState($startKey, 0);
        }

        $ordering = isset($list['ordering']) ? preg_replace('/[^A-Za-z0-9_\\.]/', '', (string) $list['ordering']) : '';
        if ($ordering === '') {
            $ordering = (string) $this->siteApp->getUserState($option . 'formsd_filter_order', '');
        }

        $direction = isset($list['direction']) ? strtolower((string) $list['direction']) : '';
        if ($direction === '') {
            $direction = (string) $this->siteApp->getUserState($option . 'formsd_filter_order_Dir', '');
        }
        if ($ordering === '' && isset($list['fullordering'])) {
            $parts = preg_split('/\s+/', trim((string) $list['fullordering']));
            $ordering = isset($parts[0]) ? preg_replace('/[^A-Za-z0-9_\\.]/', '', (string) $parts[0]) : '';
            $direction = isset($parts[1]) ? strtolower((string) $parts[1]) : $direction;
        }

        return [
            'limit' => (int) $limit,
            'start' => (int) $start,
            'ordering' => (string) $ordering,
            'direction' => (string) $direction,
        ];
    }

    private function getPaginationStateKeyPrefix(): string
    {

        $option = 'com_contentbuilderng';

        $formId = (int) $this->input->getInt('id', 0);
        if ($formId < 1) {
            $menu = $this->siteApp->getMenu()->getActive();
            if ($menu) {
                $formId = (int) MenuParamHelper::getMenuParam($menu->getParams(), 'form_id', 0);
            }
        }

        $layout = (string) $this->input->getCmd('layout', 'default');
        if ($layout === '') {
            $layout = 'default';
        }

        $itemId = (int) $this->input->getInt('Itemid', 0);

        return $option . '.liststate.' . $formId . '.' . $layout . '.' . $itemId;
    }

    private function buildPreviewQuery(): string
    {
        if (!$this->input->getBool('cb_preview', false)) {
            return '';
        }

        $until = (int) $this->input->getInt('cb_preview_until', 0);
        $sig = trim((string) $this->input->getString('cb_preview_sig', ''));
        if ($until <= 0 || $sig === '') {
            return '';
        }

        $actorId = (int) $this->input->getInt('cb_preview_actor_id', 0);
        $actorName = trim((string) $this->input->getString('cb_preview_actor_name', ''));
        $userId = (int) $this->input->getInt('cb_preview_user_id', 0);
        $adminReturn = trim((string) $this->input->getCmd('cb_admin_return', ''));

        if ($userId < 1) {
            return '';
        }

        $query = PreviewLinkHelper::buildQuery($until, $actorId, $actorName, $userId, $sig, $adminReturn);
        $colorMode = PreviewColorModeHelper::resolve($this->input, true);

        return PreviewColorModeHelper::appendQuery($query, $colorMode);
    }

    private function isAjaxCall(): bool
    {
        return (bool) $this->input->getInt('cb_ajax', 0);
    }

    private function respondAjax(bool $success, string $message = ''): void
    {
        echo new JsonResponse(['ok' => $success], $message, !$success);
        $this->siteApp->close();
    }

    #[\Override]
    public function display($cachable = false, $urlparams = array())
    {
        $app   = $this->siteApp;

        $suffix = '_fe';
        $storageId = (int) $this->input->getInt('storage_id', 0);
        $isDirectStorageMode = $storageId > 0 && $this->input->getInt('id', 0) <= 0;

        // 1) d'abord depuis l'URL
        $formId   = $this->input->getInt('id', 0);
        $recordId = $this->input->getInt('record_id', 0);

        if ($isDirectStorageMode) {
            // Édition directe d'un storage : résout (ou crée à la volée, avec
            // ses éléments et ses templates par défaut) le #__contentbuilderng_forms
            // lié, pour que le reste du pipeline (EditModel, save/apply) fonctionne
            // comme pour un formulaire classique.
            $formId = $this->getDirectStorageFormProvisioningService()->resolveOrCreateFormId($storageId);
        } elseif (!$formId) {
            // 2) sinon depuis les params du menu actif
            $menu = $this->siteApp->getMenu()->getActive();
            if ($menu) {
                $formId = (int) MenuParamHelper::getMenuParam($menu->getParams(), 'form_id', 0);
            }
        }

        // Keep both input bags aligned for downstream model/view access.
        $this->input->set('id', $formId);
        $this->siteApp->getInput()->set('id', $formId);
        $this->input->set('view', 'edit');

        if ($recordId) {
            $this->input->set('record_id', $recordId);
            $this->siteApp->getInput()->set('record_id', $recordId);
        }

        // Contexte CB correct pour cette page
        $this->siteApp->getInput()->set('view', 'edit');

        // Permissions
        $isAdminPreview = $isDirectStorageMode
            ? $this->isValidAdminPreviewRequest(0, $storageId)
            : $this->isValidAdminPreviewRequest($formId);
        $this->input->set('cb_preview_ok', $isAdminPreview ? 1 : 0);
        $this->siteApp->getInput()->set('cb_preview_ok', $isAdminPreview ? 1 : 0);

        if (!$isAdminPreview && $this->siteApp->getInput()->getBool('cb_preview', false)) {
            // Le lien portait bien les paramètres de prévisualisation, mais la
            // validation a échoué (signature invalide ou lien expiré après
            // 10 minutes) : prévenir plutôt que de perdre silencieusement le
            // bandeau "Prévisualisation".
            $this->siteApp->enqueueMessage(Text::_('COM_CONTENTBUILDERNG_PREVIEW_LINK_EXPIRED'), 'warning');
        }

        // En mode storage direct, $formId pointe désormais vers un vrai
        // #__contentbuilderng_forms (résolu/créé plus haut) : les droits
        // configurés dessus (admin > Formulaires) doivent donc être chargés
        // comme pour n'importe quel formulaire classique.
        $this->getPermissionService()->setPermissions($formId, $recordId, $suffix);

        if ($isDirectStorageMode && $isAdminPreview) {
            $this->getPermissionService()->setStoragePreviewPermissions($storageId, $this->frontend ? '_fe' : '');
        }
        if (!$isAdminPreview) {
            if ($this->siteApp->getInput()->getCmd('record_id', '')) {
                $this->getPermissionService()->checkPermissions('edit', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_EDIT_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
            } else {
                $this->getPermissionService()->checkPermissions('new', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_NEW_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
            }
        }

        $this->siteApp->getInput()->set('tmpl', $this->siteApp->getInput()->getWord('tmpl', null));
        $this->siteApp->getInput()->set('layout', $this->siteApp->getInput()->getWord('layout', null) == 'latest' ? null : $this->siteApp->getInput()->getWord('layout', null));
        $this->siteApp->getInput()->set('view', 'edit');

        parent::display();
    }

    /**
     * Validates a short-lived preview signature generated in admin toolbar.
     */
    private function isValidAdminPreviewRequest(int $formId, int $storageId = 0): bool
    {
        if (($formId < 1 && $storageId < 1) || !$this->input->getBool('cb_preview', false)) {
            return false;
        }

        $until = (int) $this->input->getInt('cb_preview_until', 0);
        $sig   = (string) $this->input->getString('cb_preview_sig', '');
        $actorId = (int) $this->input->getInt('cb_preview_actor_id', 0);
        $actorName = trim((string) $this->input->getString('cb_preview_actor_name', ''));
        $userId = (int) $this->input->getInt('cb_preview_user_id', 0);
        if ($userId < 1) {
            return false;
        }

        if ($until < time() || $sig === '') {
            return false;
        }

        $secret = (string) $this->siteApp->get('secret');
        if ($secret === '') {
            return false;
        }

        $targets = [];
        if ($formId > 0) {
            $targets[] = (string) $formId;
        }
        if ($storageId > 0) {
            $targets[] = 'storage:' . $storageId;
        }

        foreach ($targets as $target) {
            $payload = PreviewLinkHelper::buildPayload($target, $until, $actorId, $actorName, $userId);
            if (hash_equals(hash_hmac('sha256', $payload, $secret), $sig)) {
                $this->input->set('cb_preview_actor_id', $actorId);
                $this->input->set('cb_preview_actor_name', $actorName);
                $this->siteApp->getInput()->set('cb_preview_actor_id', $actorId);
                $this->siteApp->getInput()->set('cb_preview_actor_name', $actorName);
                return true;
            }
        }

        return false;
    }
}
