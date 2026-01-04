<?php
/**
 * ContentBuilder Form controller.
 *
 * Handles CRUD and publish state for form in the admin interface.
 *
 * @package     ContentBuilder
 * @subpackage  Administrator.Controller
 * @author      Markus Bopp / XDA+GIL
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */

namespace Component\Contentbuilder\Administrator\Controller;

// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

class FormController extends AdminController
{
    /**
     * Vue item et vue liste utilisées par les redirects du core
     */
    protected $view_list = 'forms';
    protected $view_item = 'form';

    public function apply(): bool
    {
        $model = $this->getModel('Form', '', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        try {
            $id = $model->store(); // Méthode legacy

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
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            return false;
        }
    }


    public function save(): bool
    {
        $model = $this->getModel('Form', '', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        try {
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
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            return false;
        }
    }
    
    public function cancel(): bool
    {
        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms', false));
        return true;
    }

    public function save2new(): bool
    {
        $model = $this->getModel('Form', '', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        try {
            $model->store();
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false), 'Saved', 'message');
            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false));
            return false;
        }
    }

    public function edit(): bool
    {
        $cid = (array) $this->input->get('cid', [], 'array');
        $id = (int) ($cid[0] ?? 0);

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . $id, false));
        return true;
    }

    public function add(): bool
    {
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false));
        return true;
    }
   
    public function publish(): bool
    {
        $id = $this->input->getInt('id', 0);
        $model = $this->getModel('Form', '');
        $model->setPublished([$id]); // ou $model->setId($id) + méthode single

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . $id, false),
            Text::_('COM_CONTENTBUILDER_PUBLISHED'));
        return true;
    }

    public function unpublish(): bool
    {
        $id = $this->input->getInt('id', 0);
        $model = $this->getModel('Form', '');
        $model->setUnpublished([$id]); // ou $model->setId($id) + méthode single

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . $id, false),
            Text::_('COM_CONTENTBUILDER_UNPUBLISHED'));
        return true;
    }

    public function editable_include(): void
    {
        $this->editable();
    }

    /*
    public function display($cachable = false, $urlparams = false)
    {
        // Chargez le modèle principal (celui de la vue "form", souvent automatique)
        $model = $this->getModel('Form', 'Contentbuilder'); // ou juste $this->getModel() si nom identique

        // Chargez le modèle secondaire "elements"
        $elementsModel = $this->getModel('Elements', 'Contentbuilder'); // Joomla chargera ElementsModel

        // Attachez-le à la vue (false = pas default, true = default mais un seul peut l'être)
        $view = $this->getView('Form', 'html');
        $view->setModel($elementsModel); // Maintenant accessible via $view->getModel('Elements', 'Contentbuilder')

        // Optionnel : si besoin, récupérez les données ici et passez-les manuellement
        // $view->elements = $elementsModel->getItems();
        // $view->pagination = $elementsModel->getPagination();
        // etc.

        parent::display($cachable, $urlparams);
    }*/
}
