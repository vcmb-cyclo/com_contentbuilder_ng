<?php

/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @link        https://www.crosstec.org
 * @copyright   (C) 2025 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator;

// no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Controller\TestController;

if ((CBRequest::getCmd('controller', '') == 'elementoptions' || 
    CBRequest::getCmd('controller', '') == 'storages' || 
    CBRequest::getCmd('controller', '') == 'forms' || 
    CBRequest::getCmd('controller', '') == 'users') && !Factory::getApplication()->getIdentity()->authorise('contentbuilder.manage', 'com_contentbuilder')) {
    Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
}

if (!(CBRequest::getCmd('controller', '') == 'elementoptions' ||
    CBRequest::getCmd('controller', '') == 'storages' ||
    CBRequest::getCmd('controller', '') == 'forms' ||
    CBRequest::getCmd('controller', '') == 'users') && !Factory::getApplication()->getIdentity()->authorise('contentbuilder.admin', 'com_contentbuilder')) {
    Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
}

require_once(JPATH_SITE . '/administrator/components/com_contentbuilder/src/contentbuilder.php');

$db     = Factory::getContainer()->get(DatabaseInterface::class);
$db->setQuery("Select `id`,`name` From #__contentbuilder_forms Where display_in In (1,2) And published = 1");
$forms = $db->loadAssocList();

/*
foreach($forms As $form){

    contentbuilder::createBackendMenuItem($form['id'], $form['name'], true);
}*/

// Require the base controller
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/controller.php');

if (CBRequest::getWord('task') === 'test') {

    // Charger les classes modernes
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Controller/TestController.php';
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Test/HtmlView.php';

    // Créer un controller temporaire pour task=test
    $container = \Joomla\CMS\Factory::getContainer();

    $db = $container->get(\Joomla\Database\DatabaseInterface::class);
    $factory = $container->get(\Joomla\CMS\MVC\Factory\MVCFactoryInterface::class);

    $controller = new TestController([], $db, $factory);
    $controller->display();

    // Stoppe le reste du legacy dispatcher
    return;
}

// Require specific controller if requested
$controller = trim(CBRequest::getWord('controller'));

// Vérifier si c'est la task "test" pour le MVC moderne
if (CBRequest::getWord('task') === 'test') {

    // Charger les classes nécessaires si besoin
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Controller/TestController.php';
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Test/HtmlView.php';

    // Récupérer les services Joomla
    $factory = Factory::getContainer()->get(\Joomla\CMS\MVC\Factory\MVCFactoryInterface::class);
    $db      = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

    // Instancier le contrôleur moderne
    $controller = new \CB\Component\Contentbuilder\Administrator\Controller\TestController([], $db, $factory);

    // Exécuter la display() du TestController
    $controller->display();

    // Stopper l’exécution pour éviter que le reste du Legacy Dispatcher s’exécute
    return;
}


if ($controller) {
    $path = JPATH_COMPONENT_ADMINISTRATOR . '/controllers/' . $controller . '.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$classname    = 'ContentbuilderController' . ucfirst($controller);
$controller   = new $classname();

// Perform the Request task
$controller->execute(CBRequest::getWord('task'));

// Redirect if set by the controller
$controller->redirect();
