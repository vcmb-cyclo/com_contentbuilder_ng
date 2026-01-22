<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Site\Controller;

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
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;

class DetailsController extends BaseController
{
    protected $default_view = 'details';

    // ✅ IMPORTANT : force le prefix PSR-4 des vues
    protected $viewPrefix = 'CB\\Component\\Contentbuilder\\Site\\View';

    private bool $frontend;

    public function __construct(
        $config,
        MVCFactoryInterface $factory,
        CMSApplicationInterface $app,
        Input $input) {

            // IMPORTANT : on transmet factory/app/input à BaseController
        parent::__construct($config, $factory, $app, $input);

        $this->frontend = Factory::getApplication()->isClient('site');

        if ($this->frontend && CBRequest::getInt('Itemid', 0)) {

            $option = 'com_contentbuilder';

            // try menu item
            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();
            if (is_object($item)) {
                if ($item->getParams()->get('record_id', null) !== null) {
                    CBRequest::setVar('record_id', $item->getParams()->get('record_id', null));
                    $this->_show_back_button = $item->getParams()->get('show_back_button', null);
                }
            }
        }

        if (CBRequest::getWord('view', '') == 'latest') {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $db->setQuery('Select `type`, `reference_id` From #__contentbuilder_forms Where id = ' . intval(CBRequest::getInt('id', 0)) . ' And published = 1');
            $form = $db->loadAssoc();
            $form = ContentbuilderLegacyHelper::getForm($form['type'], $form['reference_id']);

            $labels = $form->getElementLabels();
            $ids = array();
            foreach ($labels as $reference_id => $label) {
                $ids[] = $db->Quote($reference_id);
            }

            if (count($ids)) {
                $db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . intval(CBRequest::getInt('id', 0)) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
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
                CBRequest::setVar('record_id', $record_id);
            }

            if (!CBRequest::getCmd('record_id', '')) {
                CBRequest::setVar('cbIsNew', 1);
                ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id', 0), 0, $this->frontend ? '_fe' : '');
                $auth = $this->frontend ? ContentbuilderLegacyHelper::authorizeFe('new') : ContentbuilderLegacyHelper::authorize('new');

                if ($auth) {
                    Factory::getApplication()->redirect(Route::_('index.php?option=com_contentbuilder&task=edit.display&latest=1&backtolist=' . CBRequest::getInt('backtolist', 0) . '&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&record_id=&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getVar('filter_order', ''), false));
                } else {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_ADD_ENTRY_FIRST'));
                    Factory::getApplication()->redirect(Route::_('index.php'));
                }
            }
        }

        ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id', 0), CBRequest::getCmd('record_id', 0), $this->frontend ? '_fe' : '');
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

        // Synchroniser Input + CBRequest (legacy)
        $this->input->set('id', $form_id);
        CBRequest::setVar('id', $form_id);

        $recordId = (int) $this->input->getInt('record_id', 0);
        if (!$recordId) {
            $menu = $this->app->getMenu()->getActive();
            if ($menu) {
                $recordId = (int) $menu->getParams()->get('record_id', 0);
            }
        }
        if ($recordId) {
            $this->input->set('record_id', $recordId);
            CBRequest::setVar('record_id', $recordId);
        }

        ContentbuilderLegacyHelper::setPermissions($form_id, $recordId, $suffix);
        ContentbuilderLegacyHelper::checkPermissions('view', Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED'), $this->frontend ? '_fe' : '');

        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl', null));
        CBRequest::setVar('layout', CBRequest::getWord('layout', null) == 'latest' ? null : CBRequest::getWord('layout', null));
        if (CBRequest::getWord('view', '') == 'latest') {
            CBRequest::setVar('cb_latest', 1);
        }

        parent::display();
    }
}
