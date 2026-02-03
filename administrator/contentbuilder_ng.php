

<?php
/**
 * @package     ContentBuilder
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$app = Factory::getApplication();

// Boot moderne : charge services/provider.php, extension class, MVCFactory, etc.
$app->bootComponent('com_contentbuilder_ng')
    ->getDispatcher($app)
    ->dispatch();
