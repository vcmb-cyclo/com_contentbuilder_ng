<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;

// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;

class DetailsController extends BaseController
{
    public function __construct($config = [])
    {

        if (class_exists('cbFeMarker') && CBRequest::getInt('Itemid', 0)) {

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
                ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id', 0), 0, class_exists('cbFeMarker') ? '_fe' : '');
                $auth = class_exists('cbFeMarker') ? ContentbuilderLegacyHelper::authorizeFe('new') : ContentbuilderLegacyHelper::authorize('new');

                if ($auth) {
                    Factory::getApplication()->redirect(Route::_('index.php?option=com_contentbuilder&view=edit&latest=1&backtolist=' . CBRequest::getInt('backtolist', 0) . '&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&record_id=&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getVar('filter_order', ''), false));
                } else {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_ADD_ENTRY_FIRST'));
                    Factory::getApplication()->redirect('index.php');
                }
            }
        }

        ContentbuilderLegacyHelper::setPermissions(CBRequest::getInt('id', 0), CBRequest::getCmd('record_id', 0), class_exists('cbFeMarker') ? '_fe' : '');
        parent::__construct($config);
    }

    function display($cachable = false, $urlparams = array())
    {
        ContentbuilderLegacyHelper::checkPermissions('view', Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl', null));
        CBRequest::setVar('layout', CBRequest::getWord('layout', null) == 'latest' ? null : CBRequest::getWord('layout', null));
        if (CBRequest::getWord('view', '') == 'latest') {
            CBRequest::setVar('cb_latest', 1);
        }
        CBRequest::setVar('view', 'details');

        parent::display();
    }
}
