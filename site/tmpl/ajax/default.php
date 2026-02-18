<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/



// No direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;

Factory::getApplication()->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);

echo (string) ($this->data ?? '');
