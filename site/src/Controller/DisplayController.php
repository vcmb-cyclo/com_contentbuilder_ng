<?php
namespace CB\Component\Contentbuilder\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
class DisplayController extends BaseController
{
    protected $default_view = 'forms';

    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }
}
