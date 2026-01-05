<?php

/**
 * @package     ContentBuilder
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2025 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

\defined('_JEXEC') or die;

class TestController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }
}