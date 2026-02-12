<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Site\Controller;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderLegacyHelper;

class DetailsController extends BaseController
{
    protected $default_view = 'details';

    // ✅ IMPORTANT : force le prefix PSR-4 des vues
    protected $viewPrefix = 'CB\\Component\\Contentbuilder_ng\\Site\\View';

    private bool $frontend;

    public function __construct(
        $config,
        MVCFactoryInterface $factory,
        CMSApplicationInterface $app,
        Input $input) {

            // IMPORTANT : on transmet factory/app/input à BaseController
        parent::__construct($config, $factory, $app, $input);

        $this->frontend = Factory::getApplication()->isClient('site');

        if ($this->frontend && Factory::getApplication()->input->getInt('Itemid', 0)) {

            $option = 'com_contentbuilder_ng';

            // try menu item
            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();
            if (is_object($item)) {
                if ($item->getParams()->get('record_id', null) !== null) {
                    Factory::getApplication()->input->set('record_id', $item->getParams()->get('record_id', null));
                    $this->_show_back_button = $item->getParams()->get('show_back_button', null);
                }
            }
        }

        if (Factory::getApplication()->input->getWord('view', '') == 'latest') {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $db->setQuery('Select `type`, `reference_id` From #__contentbuilder_ng_forms Where id = ' . intval(Factory::getApplication()->input->getInt('id', 0)) . ' And published = 1');
            $form = $db->loadAssoc();
            $form = ContentbuilderLegacyHelper::getForm($form['type'], $form['reference_id']);

            $labels = $form->getElementLabels();
            $ids = array();
            foreach ($labels as $reference_id => $label) {
                $ids[] = $db->Quote($reference_id);
            }

            if (count($ids)) {
                $db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_ng_elements Where form_id = " . intval(Factory::getApplication()->input->getInt('id', 0)) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                $rows = $db->loadAssocList();
                $ids = array();
                foreach ($rows as $row) {
                    $ids[] = $row['reference_id'];
                }
            }

            $rec = $form->getListRecords($ids, '', array(), 0, 1, '', array(), 'desc', 0, false, Factory::getApplication()->getIdentity()->get('id', 0), 0, -1, -1, -1, -1, array(), true, null);

            if (count($rec) > 0) {
                $rec = $rec[0];
                $rec2 = $form->getRecord($rec->colRecord, false, -1, true);

                $record_id = $rec->colRecord;
                Factory::getApplication()->input->set('record_id', $record_id);
            }

            if (!Factory::getApplication()->input->getCmd('record_id', '')) {
                Factory::getApplication()->input->set('cbIsNew', 1);
                ContentbuilderLegacyHelper::setPermissions(Factory::getApplication()->input->getInt('id', 0), 0, $this->frontend ? '_fe' : '');
                $auth = $this->frontend ? ContentbuilderLegacyHelper::authorizeFe('new') : ContentbuilderLegacyHelper::authorize('new');

                if ($auth) {
                    $app = Factory::getApplication();
                    $option = 'com_contentbuilder_ng';
                    $list = (array) $app->input->get('list', [], 'array');
                    $limit = isset($list['limit']) ? $app->input->getInt('list[limit]', 0) : (int) $app->getUserState($option . '.list.limit', 0);
                    if ($limit === 0) {
                        $limit = (int) $app->get('list_limit');
                    }
                    $start = isset($list['start']) ? $app->input->getInt('list[start]', 0) : (int) $app->getUserState($option . '.list.start', 0);
                    $ordering = isset($list['ordering']) ? $app->input->getCmd('list[ordering]', '') : (string) $app->getUserState($option . 'formsd_filter_order', '');
                    $direction = isset($list['direction']) ? $app->input->getCmd('list[direction]', '') : (string) $app->getUserState($option . 'formsd_filter_order_Dir', '');
                    $listQuery = http_build_query(['list' => [
                        'limit' => $limit,
                        'start' => $start,
                        'ordering' => $ordering,
                        'direction' => $direction,
                    ]]);

                    Factory::getApplication()->redirect(Route::_('index.php?option=com_contentbuilder_ng&task=edit.display&latest=1&backtolist=' . Factory::getApplication()->input->getInt('backtolist', 0) . '&id=' . Factory::getApplication()->input->getInt('id', 0) . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&record_id=' . ($listQuery !== '' ? '' : '') . ($listQuery !== '' ? '&' . $listQuery : ''), false));
                } else {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_NG_ADD_ENTRY_FIRST'));
                    Factory::getApplication()->redirect(Route::_('index.php'));
                }
            }
        }

        ContentbuilderLegacyHelper::setPermissions(Factory::getApplication()->input->getInt('id', 0), Factory::getApplication()->input->getCmd('record_id', 0), $this->frontend ? '_fe' : '');
    }

    function display($cachable = false, $urlparams = array())
    {
        $this->input->set('view', 'details');

        // Si tu gardes le suffixe pour compat legacy :
        //$frontend = Factory::getApplication()->isClient('site');
        $suffix = '_fe';

        // 1) d'abord depuis l'URL
        $form_id = $this->input->getInt('id', 0);

        // 2) sinon depuis les params du menu actif
        if (!$form_id) {
            $menu = $this->app->getMenu()->getActive();
            if ($menu) {
                $form_id = (int) $menu->getParams()->get('form_id', 0);
            }
        }

        // Synchroniser l'input pour les appels legacy encore présents.
        $this->input->set('id', $form_id);
        Factory::getApplication()->input->set('id', $form_id);

        $recordId = (int) $this->input->getInt('record_id', 0);
        if (!$recordId) {
            $menu = $this->app->getMenu()->getActive();
            if ($menu) {
                $recordId = (int) $menu->getParams()->get('record_id', 0);
            }
        }
        if ($recordId) {
            $this->input->set('record_id', $recordId);
            Factory::getApplication()->input->set('record_id', $recordId);
        }

        ContentbuilderLegacyHelper::setPermissions($form_id, $recordId, $suffix);
        $isAdminPreview = $this->isValidAdminPreviewRequest($form_id);
        $this->input->set('cb_preview_ok', $isAdminPreview ? 1 : 0);
        Factory::getApplication()->input->set('cb_preview_ok', $isAdminPreview ? 1 : 0);
        if (!$isAdminPreview) {
            ContentbuilderLegacyHelper::checkPermissions('view', Text::_('COM_CONTENTBUILDER_NG_PERMISSIONS_VIEW_NOT_ALLOWED'), $this->frontend ? '_fe' : '');
        }

        Factory::getApplication()->input->set('tmpl', Factory::getApplication()->input->getWord('tmpl', null));
        Factory::getApplication()->input->set('layout', Factory::getApplication()->input->getWord('layout', null) == 'latest' ? null : Factory::getApplication()->input->getWord('layout', null));
        if (Factory::getApplication()->input->getWord('view', '') == 'latest') {
            Factory::getApplication()->input->set('cb_latest', 1);
        }

        parent::display();
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
}
