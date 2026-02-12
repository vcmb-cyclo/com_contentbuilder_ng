<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder_ng\Site\Controller;

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\DatabaseInterface;
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderLegacyHelper;

class ListController extends BaseController
{
    public function delete(): void
    {
        ContentbuilderLegacyHelper::checkPermissions(
            'delete',
            Text::_('COM_CONTENTBUILDER_NG_PERMISSIONS_DELETE_NOT_ALLOWED'),
            '_fe'
        );

        $model = $this->getModel('Edit', 'Site', ['ignore_request' => true])
            ?: $this->getModel('Edit', 'Contentbuilder_ng', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('EditModel not found');
        }

        if (method_exists($model, 'setIds')) {
            $model->setIds(
                $this->input->getInt('id', 0),
                $this->input->getCmd('record_id', 0)
            );
        }

        $ok = true;
        try {
            // Legacy model may not return a strict boolean; treat "no exception" as success.
            $model->delete();
        } catch (\Throwable $e) {
            $ok = false;
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }

        $msg = $ok ? Text::_('COM_CONTENTBUILDER_NG_ENTRIES_DELETED') : Text::_('COM_CONTENTBUILDER_NG_ERROR');
        $type = $ok ? 'message' : 'warning';

        // Clear record context to avoid redirects back to a deleted record.
        $this->input->set('record_id', 0);
        Factory::getApplication()->input->set('record_id', 0);

        $list = (array) $this->input->get('list', [], 'array');
        $option = 'com_contentbuilder_ng';
        $limit = isset($list['limit']) ? $this->input->getInt('list[limit]', 0) : 0;
        if ($limit === 0) {
            $limit = (int) $this->app->getUserState($option . '.list.limit', 0);
        }
        if ($limit === 0) {
            $limit = (int) $this->app->get('list_limit');
        }
        $start = isset($list['start']) ? $this->input->getInt('list[start]', 0) : 0;
        if (!$start) {
            $start = (int) $this->app->getUserState($option . '.list.start', 0);
        }
        $ordering = isset($list['ordering']) ? $this->input->getCmd('list[ordering]', '') : '';
        if ($ordering === '') {
            $ordering = (string) $this->app->getUserState($option . '.formsd_filter_order', '');
        }
        $direction = isset($list['direction']) ? $this->input->getCmd('list[direction]', '') : '';
        if ($direction === '') {
            $direction = (string) $this->app->getUserState($option . '.formsd_filter_order_Dir', '');
        }
        $link = Route::_(
            'index.php?option=com_contentbuilder_ng&task=list.display&id='
            . $this->input->getInt('id', 0)
            . '&list[limit]=' . $limit
            . '&list[start]=' . $start
            . '&list[ordering]=' . $ordering
            . '&list[direction]=' . $direction
            . '&Itemid=' . $this->input->getInt('Itemid', 0),
            false
        );

        $this->setRedirect($link, $msg, $type);
    }

    public function state(): void
    {
        ContentbuilderLegacyHelper::checkPermissions(
            'state',
            Text::_('COM_CONTENTBUILDER_NG_PERMISSIONS_STATE_CHANGE_NOT_ALLOWED'),
            '_fe'
        );

        $model = $this->getModel('Edit', 'Site', ['ignore_request' => true])
            ?: $this->getModel('Edit', 'Contentbuilder_ng', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('EditModel not found');
        }

        if (method_exists($model, 'setIds')) {
            $model->setIds(
                $this->input->getInt('id', 0),
                $this->input->getCmd('record_id', 0)
            );
        }

        $model->change_list_states();

        $list = (array) $this->input->get('list', [], 'array');
        $option = 'com_contentbuilder_ng';
        $limit = isset($list['limit']) ? $this->input->getInt('list[limit]', 0) : 0;
        if ($limit === 0) {
            $limit = (int) $this->app->getUserState($option . '.list.limit', 0);
        }
        if ($limit === 0) {
            $limit = (int) $this->app->get('list_limit');
        }
        $start = isset($list['start']) ? $this->input->getInt('list[start]', 0) : 0;
        if (!$start) {
            $start = (int) $this->app->getUserState($option . '.list.start', 0);
        }
        $ordering = isset($list['ordering']) ? $this->input->getCmd('list[ordering]', '') : '';
        if ($ordering === '') {
            $ordering = (string) $this->app->getUserState($option . '.formsd_filter_order', '');
        }
        $direction = isset($list['direction']) ? $this->input->getCmd('list[direction]', '') : '';
        if ($direction === '') {
            $direction = (string) $this->app->getUserState($option . '.formsd_filter_order_Dir', '');
        }
        $link = Route::_(
            'index.php?option=com_contentbuilder_ng&task=list.display&id='
            . $this->input->getInt('id', 0)
            . '&list[limit]=' . $limit
            . '&list[start]=' . $start
            . '&list[ordering]=' . $ordering
            . '&list[direction]=' . $direction
            . '&Itemid=' . $this->input->getInt('Itemid', 0),
            false
        );
        $this->setRedirect($link, Text::_('COM_CONTENTBUILDER_NG_STATES_CHANGED'), 'message');
    }

    public function publish(): void
    {
        ContentbuilderLegacyHelper::checkPermissions(
            'publish',
            Text::_('COM_CONTENTBUILDER_NG_PERMISSIONS_PUBLISHING_NOT_ALLOWED'),
            '_fe'
        );

        $model = $this->getModel('Edit', 'Site', ['ignore_request' => true])
            ?: $this->getModel('Edit', 'Contentbuilder_ng', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('EditModel not found');
        }

        if (method_exists($model, 'setIds')) {
            $model->setIds(
                $this->input->getInt('id', 0),
                $this->input->getCmd('record_id', 0)
            );
        }

        $model->change_list_publish();

        $msg = $this->input->getInt('list_publish', 0)
            ? Text::_('COM_CONTENTBUILDER_NG_PUBLISHED')
            : Text::_('COM_CONTENTBUILDER_NG_PUNPUBLISHED');

        $list = (array) $this->input->get('list', [], 'array');
        $option = 'com_contentbuilder_ng';
        $limit = isset($list['limit']) ? $this->input->getInt('list[limit]', 0) : 0;
        if ($limit === 0) {
            $limit = (int) $this->app->getUserState($option . '.list.limit', 0);
        }
        if ($limit === 0) {
            $limit = (int) $this->app->get('list_limit');
        }
        $start = isset($list['start']) ? $this->input->getInt('list[start]', 0) : 0;
        if (!$start) {
            $start = (int) $this->app->getUserState($option . '.list.start', 0);
        }
        $ordering = isset($list['ordering']) ? $this->input->getCmd('list[ordering]', '') : '';
        if ($ordering === '') {
            $ordering = (string) $this->app->getUserState($option . '.formsd_filter_order', '');
        }
        $direction = isset($list['direction']) ? $this->input->getCmd('list[direction]', '') : '';
        if ($direction === '') {
            $direction = (string) $this->app->getUserState($option . '.formsd_filter_order_Dir', '');
        }
        $link = Route::_(
            'index.php?option=com_contentbuilder_ng&task=list.display&id='
            . $this->input->getInt('id', 0)
            . '&list[limit]=' . $limit
            . '&list[start]=' . $start
            . '&list[ordering]=' . $ordering
            . '&list[direction]=' . $direction
            . '&Itemid=' . $this->input->getInt('Itemid', 0),
            false
        );
        $this->setRedirect($link, $msg, 'message');
    }

    public function display($cachable = false, $urlparams = [])
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

        // Synchroniser l'input pour les appels legacy encore présents.
        $this->input->set('id', $formId);
        Factory::getApplication()->input->set('id', $formId);

        if ($recordId) {
            $this->input->set('record_id', $recordId);
            Factory::getApplication()->input->set('record_id', $recordId);
        }

        // Contexte CB correct pour cette page
        Factory::getApplication()->input->set('view', 'list');

        // Permissions
        ContentbuilderLegacyHelper::setPermissions($formId, $recordId, $suffix);
        $isAdminPreview = $this->isValidAdminPreviewRequest($formId);
        $this->input->set('cb_preview_ok', $isAdminPreview ? 1 : 0);
        Factory::getApplication()->input->set('cb_preview_ok', $isAdminPreview ? 1 : 0);
        if ($isAdminPreview) {
            $this->enqueueUnpublishedPreviewNotice($formId);
        }
        if (!$isAdminPreview) {
            ContentbuilderLegacyHelper::checkPermissions(
                'listaccess',
                Text::_('COM_CONTENTBUILDER_NG_PERMISSIONS_LISTACCESS_NOT_ALLOWED'),
                $suffix
            );
        }

        // Piloter le rendu via l'input Joomla
        $layout = $this->input->getCmd('layout', null);
        $this->input->set('layout', ($layout === 'latest') ? null : $layout);

        return parent::display($cachable, $urlparams);
    }

    /**
     * Validates a short-lived preview signature generated in admin toolbar.
     */
    private function isValidAdminPreviewRequest(int $formId): bool
    {
        if ($formId < 1 || !$this->input->getBool('cb_preview', false)) {
            return false;
        }

        $until = (int) $this->input->getInt('cb_preview_until', 0);
        $sig   = (string) $this->input->getString('cb_preview_sig', '');
        $actorId = (int) $this->input->getInt('cb_preview_actor_id', 0);
        $actorName = trim((string) $this->input->getString('cb_preview_actor_name', ''));

        if ($until < time() || $sig === '') {
            return false;
        }

        $secret = (string) Factory::getApplication()->get('secret');
        if ($secret === '') {
            return false;
        }

        $payload  = $formId . '|' . $until;
        $expected = hash_hmac('sha256', $payload, $secret);
        $actorPayload = $payload . '|' . $actorId . '|' . $actorName;
        $actorExpected = hash_hmac('sha256', $actorPayload, $secret);

        if (($actorId > 0 || $actorName !== '') && hash_equals($actorExpected, $sig)) {
            $this->input->set('cb_preview_actor_id', $actorId);
            $this->input->set('cb_preview_actor_name', $actorName);
            Factory::getApplication()->input->set('cb_preview_actor_id', $actorId);
            Factory::getApplication()->input->set('cb_preview_actor_name', $actorName);
            return true;
        }

        if (hash_equals($expected, $sig)) {
            $this->input->set('cb_preview_actor_id', 0);
            $this->input->set('cb_preview_actor_name', '');
            Factory::getApplication()->input->set('cb_preview_actor_id', 0);
            Factory::getApplication()->input->set('cb_preview_actor_name', '');
            return true;
        }

        return false;
    }

    /**
     * Shows the warning once per preview link when the form is unpublished.
     */
    private function enqueueUnpublishedPreviewNotice(int $formId): void
    {
        if ($formId < 1 || $this->isFormPublished($formId)) {
            return;
        }

        $until = (int) $this->input->getInt('cb_preview_until', 0);
        $sig = (string) $this->input->getString('cb_preview_sig', '');
        $noticeKey = 'com_contentbuilder_ng.preview_notice.' . hash('sha256', $formId . '|' . $until . '|' . $sig);
        $session = $this->app->getSession();

        if ($session->get($noticeKey, false)) {
            return;
        }

        $this->app->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_PREVIEW_UNPUBLISHED_NOTICE'), 'warning');
        $session->set($noticeKey, true);
    }

    private function isFormPublished(int $formId): bool
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('published'))
                ->from($db->quoteName('#__contentbuilder_ng_forms'))
                ->where($db->quoteName('id') . ' = ' . (int) $formId);
            $db->setQuery($query);
            $published = $db->loadResult();
        } catch (\Throwable $e) {
            return true;
        }

        return (int) $published === 1;
    }
}
