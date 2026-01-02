<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;

// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

class FormController extends BaseController
{
    /**
     * Vue item et vue liste utilisées par les redirects du core
     */
    protected $view_item = 'form';
    protected $view_list = 'forms';

    public function save()
    {
        $model = $this->getModel('Form');
        $id = $model->store();

        if (!$id) {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $this->input->getInt('id', 0), false),
                $model->getError() ?: 'Store failed (no id returned)',
                'error'
            );
            return false;
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $id, false),
            'Saved',
            'message'
        );
        return true;
    }
    

    public function apply()
    {
        $model = $this->getModel('Form');
        $id = $model->store();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&id=' . (int) $id, false), 
            'Saved');
        return true;
    }

    public function cancel()
    {
        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms', false));
        return true;
    }

    public function save2new(){
        $model = $this->getModel('storage');
        $model->store();

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false));
        return true;
    }

    public function edit()
    {
        $cid = (array) $this->input->get('cid', [], 'array');
        $id = (int) ($cid[0] ?? 0);

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . $id, false));
        return true;
    }

    public function add()
    {
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false));
        return true;
    }
   
    public function publish()
    {
        $id = $this->input->getInt('id', 0);
        $model = $this->getModel('Storage');
        $model->setPublished([$id]); // ou $model->setId($id) + méthode single

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . $id, false),
            Text::_('COM_CONTENTBUILDER_PUBLISHED'));
    }

    public function unpublish()
    {
        $id = $this->input->getInt('id', 0);
        $model = $this->getModel('Storage');
        $model->setUnpublished([$id]); // ou $model->setId($id) + méthode single

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . $id, false),
            Text::_('COM_CONTENTBUILDER_UNPUBLISHED'));
    }

    public function editable_include(): void
    {
        $this->editable();
    }
}
