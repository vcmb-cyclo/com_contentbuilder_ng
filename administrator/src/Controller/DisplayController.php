<?php

/**
 * @package     Extension
 * @author      Xavier DANO
 * @link        
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// administrator/src/Controller/DisplayController.php

namespace CB\Component\Contentbuilder\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class DisplayController extends BaseController
{
    protected $default_view = 'test';

    public function display($cachable = false, $urlparams = [])
    {
        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.manage', 'com_contentbuilder')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return parent::display($cachable, $urlparams);
    }
}
