<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\Controller;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\AdminController;

class ElementoptionsController extends AdminController
{

    function display($cachable = false, $urlparams = array())
    {
        Factory::getApplication()->input->set('tmpl', Factory::getApplication()->input->getWord('tmpl', null));
        Factory::getApplication()->input->set('layout', Factory::getApplication()->input->getWord('layout', null));
        Factory::getApplication()->input->set('view', 'elementoptions');

        parent::display();
    }

    function save()
    {
        $model = $this->getModel('Elementoption', 'Administrator', ['ignore_request' => true])
            ?: $this->getModel('Elementoption', 'Contentbuilder_ng', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('ElementoptionModel not found');
        }
        $id = $model->store();

        if ($id) {
            $msg = Text::_('COM_CONTENTBUILDER_NG_SAVED');
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_NG_ERROR');
        }


        $type_change_url = '';
        $type_change = Factory::getApplication()->input->getInt('type_change', 0);
        if ($type_change) {
            $type_change_url = '&type_change=1&type_selection=' . Factory::getApplication()->input->getCmd('type_selection', '');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = Route::_('index.php?option=com_contentbuilder_ng&view=elementoptions&tabStartOffset=' . Factory::getApplication()->input->getInt('tabStartOffset', 0) . '&tmpl=component&element_id=' . Factory::getApplication()->input->getInt('element_id', 0) . '&id=' . Factory::getApplication()->input->getInt('id', 0) . $type_change_url, false);
        $this->setRedirect($link, $msg);
    }
}
