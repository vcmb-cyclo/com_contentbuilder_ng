<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// Fichier d’entrée du composant (Site) - Joomla 6 Modern Dispatcher

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use CB\Component\Contentbuilder_ng\Administrator\CBRequest;

$app = Factory::getApplication();

// ----------------------------------------------------
// 1) Compat : reset des variables CBRequest.
// ----------------------------------------------------
Factory::getApplication()->input->set('cb_controller', null);
Factory::getApplication()->input->set('cb_category_id', null);
Factory::getApplication()->input->set('cb_list_filterhidden', null);
Factory::getApplication()->input->set('cb_list_orderhidden', null);
Factory::getApplication()->input->set('cb_show_author', null);
Factory::getApplication()->input->set('cb_show_top_bar', null);
Factory::getApplication()->input->set('cb_show_details_top_bar', null);
Factory::getApplication()->input->set('cb_show_bottom_bar', null);
Factory::getApplication()->input->set('cb_show_details_bottom_bar', null);
Factory::getApplication()->input->set('cb_latest', null);
Factory::getApplication()->input->set('cb_show_details_back_button', null);
Factory::getApplication()->input->set('cb_list_limit', null);
Factory::getApplication()->input->set('cb_filter_in_title', null);
Factory::getApplication()->input->set('cb_prefix_in_title', null);
Factory::getApplication()->input->set('force_menu_item_id', null);
Factory::getApplication()->input->set('cb_category_menu_filter', null);

// ----------------------------------------------------
// 2) Menu actif / Itemid.
// ----------------------------------------------------
$menu = $app->getMenu();
$item = $menu->getActive();

if (is_object($item)) {
    Factory::getApplication()->input->set('Itemid', $item->id);
}

// ----------------------------------------------------
// 3) Récup des params menu + logique "latest/details".
// ----------------------------------------------------
if (Factory::getApplication()->input->getInt('Itemid', 0)) {

    if (Factory::getApplication()->input->get('layout', null, 'string') !== null) {
        $app->getSession()->set(
            'com_contentbuilder_ng.layout.' . Factory::getApplication()->input->getInt('Itemid', 0) . Factory::getApplication()->input->get('layout', null, 'string'),
            Factory::getApplication()->input->get('layout', null, 'string')
        );
    }

    if ($app->getSession()->get(
        'com_contentbuilder_ng.layout.' . Factory::getApplication()->input->getInt('Itemid', 0) . Factory::getApplication()->input->get('layout', null, 'string'),
        null
    ) !== null) {
        Factory::getApplication()->input->set(
            'layout',
            $app->getSession()->get(
                'com_contentbuilder_ng.layout.' . Factory::getApplication()->input->getInt('Itemid', 0) . Factory::getApplication()->input->get('layout', null, 'string'),
                null
            )
        );
    }

    if (is_object($item)) {

        if ($item->getParams()->get('form_id', null) !== null) {
            Factory::getApplication()->input->set('id', $item->getParams()->get('form_id', null));
        }

        if ($item->getParams()->get('record_id', null) !== null && ($item->query['view'] ?? '') === 'details' && !isset($_REQUEST['view'])) {
            Factory::getApplication()->input->set('record_id', $item->getParams()->get('record_id', null));
            Factory::getApplication()->input->set('controller', 'details');
        }

        if ($item->getParams()->get('record_id', null) !== null && ($item->query['view'] ?? '') === 'details' && isset($_REQUEST['view'])) {
            Factory::getApplication()->input->set('record_id', $item->getParams()->get('record_id', null));
            Factory::getApplication()->input->set('controller', 'edit');
        }

        if (($item->query['view'] ?? '') === 'latest' && !isset($_REQUEST['view'])) {
            Factory::getApplication()->input->set('view', 'latest');
            Factory::getApplication()->input->set('controller', 'details');
        }

        if (($item->query['view'] ?? '') === 'latest' && isset($_REQUEST['view']) && $_REQUEST['view'] === 'edit' && isset($_REQUEST['record_id'])) {
            Factory::getApplication()->input->set('record_id', $_REQUEST['record_id']);
            Factory::getApplication()->input->set('view', 'latest');
            Factory::getApplication()->input->set('controller', 'edit');
        }

        Factory::getApplication()->input->set('cb_category_id', $item->getParams()->get('cb_category_id', null));
        Factory::getApplication()->input->set('cb_controller', $item->getParams()->get('cb_controller', null));
        Factory::getApplication()->input->set('cb_list_filterhidden', $item->getParams()->get('cb_list_filterhidden', null));
        Factory::getApplication()->input->set('cb_list_orderhidden', $item->getParams()->get('cb_list_orderhidden', null));
        Factory::getApplication()->input->set('cb_show_author', $item->getParams()->get('cb_show_author', null));
        Factory::getApplication()->input->set('cb_show_bottom_bar', $item->getParams()->get('cb_show_bottom_bar', null));
        Factory::getApplication()->input->set('cb_show_top_bar', $item->getParams()->get('cb_show_top_bar', null));
        Factory::getApplication()->input->set('cb_show_details_bottom_bar', $item->getParams()->get('cb_show_details_bottom_bar', null));
        Factory::getApplication()->input->set('cb_show_details_top_bar', $item->getParams()->get('cb_show_details_top_bar', null));
        Factory::getApplication()->input->set('cb_show_details_back_button', $item->getParams()->get('cb_show_details_back_button', null));
        Factory::getApplication()->input->set('cb_list_limit', $item->getParams()->get('cb_list_limit', 20));
        Factory::getApplication()->input->set('cb_filter_in_title', $item->getParams()->get('cb_filter_in_title', 1));
        Factory::getApplication()->input->set('cb_prefix_in_title', $item->getParams()->get('cb_prefix_in_title', 1));
        Factory::getApplication()->input->set('force_menu_item_id', $item->getParams()->get('force_menu_item_id', 0));
        Factory::getApplication()->input->set('cb_category_menu_filter', $item->getParams()->get('cb_category_menu_filter', 0));
    }
}

// ----------------------------------------------------
// 4) Compat : déduire le "controller" logique.
// ----------------------------------------------------
$controller = trim(Factory::getApplication()->input->getWord('controller'));

if (Factory::getApplication()->input->getCmd('view', '') === 'details' || (Factory::getApplication()->input->getCmd('view', '') === 'latest' && Factory::getApplication()->input->getCmd('controller', '') === '')) {
    $controller = 'details';
}

if (Factory::getApplication()->input->getString('cb_controller', '') === 'edit') {
    $controller = 'edit';
} elseif (Factory::getApplication()->input->getString('cb_controller', '') === 'publicforms' && Factory::getApplication()->input->getInt('id', 0) <= 0) {
    $controller = 'publicforms';
}

if (!$controller) {
    $controller = 'list';
}

// ----------------------------------------------------
// 5) Joomla input (IMPORTANT)
// ----------------------------------------------------
$input = $app->input;

// Si un task est explicitement fourni (ex: task=details.display), on ne touche pas.
$task = $input->getCmd('task', '');

if ($task === '') {
    // Mapping simple : controller => view
    // (à ajuster si ton routing moderne diffère)
    $input->set('view', $controller);

    // Assure un task "standard" si nécessaire
    // Beaucoup de composants supportent juste display par défaut
    $input->set('task', $controller . '.display');
}

// ----------------------------------------------------
// 6) DISPATCH MODERNE (Joomla 6)
// ----------------------------------------------------
$component = $app->bootComponent('com_contentbuilder_ng');
$component->getDispatcher($input)->dispatch();
