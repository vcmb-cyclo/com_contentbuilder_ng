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

namespace CB\Component\Contentbuilder\Administrator\Controller;

// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController as BaseFormController;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

class FormController extends BaseFormController
{
    /**
     * Vues utilisées par les redirects du core
     */
    protected $view_list = 'forms';
    protected $view_item = 'form';

    public function edit($key = null, $urlVar = null)
    {
        try {
            $input = $this->input;

            // Remap cid[] -> id si besoin
            if (!$input->getInt('id')) {
                $cid = $input->get('cid', [], 'array');
                if (!empty($cid)) {
                    $input->set('id', (int) $cid[0]);
                }
            }

            return parent::edit($key, $urlVar);
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=forms', false));
            return false;
        }
    }

    /**
     * Nouveau
     */
    public function add()
    {
        try {
            // Tu peux aussi faire: return parent::add();
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false));
            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=forms', false));
            return false;
        }
    }

    /**
     * Apply : sauvegarde et reste sur l'édition
     */
    public function apply($key = null, $urlVar = null)
    {
        $model = $this->getModel('Form', '', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        try {
            $id = $model->store(); // legacy

            if (!$id) {
                $this->setRedirect(
                    Route::_(
                        'index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $this->input->getInt('id', 0),
                        false
                    ),
                    $model->getError() ?: 'Store failed (no id returned)',
                    'error'
                );
                return false;
            }

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $id, false),
                Text::_('JSAVE_SUCCESS'),
                'message'
            );

            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $this->input->getInt('id', 0),
                    false
                )
            );
            return false;
        }
    }

    /**
     * Save : par défaut tu voulais rester sur l'édition (je garde ton comportement).
     * Si tu veux retour liste, je te donne la ligne à changer juste après.
     */
    public function save($key = null, $urlVar = null)
    {
        $model = $this->getModel('Form', '', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        try {
            $id = $model->store(); // legacy

            if (!$id) {
                $this->setRedirect(
                    Route::_(
                        'index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $this->input->getInt('id', 0),
                        false
                    ),
                    $model->getError() ?: 'Store failed (no id returned)',
                    'error'
                );
                return false;
            }

            // ✅ Comportement actuel: rester sur l'édition
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $id, false),
                Text::_('JSAVE_SUCCESS'),
                'message'
            );

            // 👉 Si tu veux plutôt revenir à la liste après Save :
            // $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=forms', false), Text::_('JSAVE_SUCCESS'));

            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $this->input->getInt('id', 0),
                    false
                )
            );
            return false;
        }
    }

    /**
     * Save & New : sauvegarde puis ouvre un nouvel item vide
     */
    public function save2new($key = null, $urlVar = null)
    {
        $model = $this->getModel('Form', '', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        try {
            $id = $model->store(); // legacy

            if (!$id) {
                $this->setMessage($model->getError() ?: 'Store failed (no id returned)', 'error');
                $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false));
                return false;
            }

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false),
                Text::_('JSAVE_SUCCESS'),
                'message'
            );

            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false));
            return false;
        }
    }

    /**
     * (Optionnel) Publish/unpublish sur écran d'édition.
     * Idéalement à mettre dans FormsController (AdminController) pour du multi-cid[].
     */
    public function publish()
    {
        try {
            $id = $this->input->getInt('id', 0);

            if (!$id) {
                $this->setMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
                $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=forms', false));
                return false;
            }

            $model = $this->getModel('Form', '');
            $model->setPublished([$id]);

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $id, false),
                Text::_('COM_CONTENTBUILDER_PUBLISHED')
            );

            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=forms', false));
            return false;
        }
    }

    public function unpublish()
    {
        try {
            $id = $this->input->getInt('id', 0);

            if (!$id) {
                $this->setMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
                $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=forms', false));
                return false;
            }

            $model = $this->getModel('Form', '');
            $model->setUnpublished([$id]);

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . (int) $id, false),
                Text::_('COM_CONTENTBUILDER_UNPUBLISHED')
            );

            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=forms', false));
            return false;
        }
    }

    public function editable_include(): void
    {
        $this->editable();
    }



    // ==================================================================
    // Toutes les tâches sur les ÉLÉMENTS (champs du formulaire)
    // ==================================================================

    // Ces tâches agissent sur les éléments sélectionnés dans l'édition d'un form
    // Elles doivent utiliser ElementoptionsModel

    private function getElementsModel()
    {
        return $this->getModel('Elementoptions', 'Contentbuilder');
    }

    public function listorderup(): void
    {
        $model = $this->getElementsModel();
        $model->move(-1); // ou utilise reorder si tu préfères
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=' . $this->view_item . '&id=' . $this->input->getInt('id'), false));
    }

    public function listorderdown(): void
    {
        $model = $this->getElementsModel();
        $model->move(1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=' . $this->view_item . '&id=' . $this->input->getInt('id'), false));
    }

    public function listsaveorder(): void
    {
        $model = $this->getElementsModel();
        $model->saveorder($this->input->get('cid', [], 'array'), $this->input->get('order', [], 'array'));
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=' . $this->view_item . '&id=' . $this->input->getInt('id'), false));
    }

    // Les tâches batch sur les éléments (linkable, editable, etc.)
    // Tu peux les factoriser ou les garder séparées

    public function linkable(): void
    {
        $this->batchElementUpdate('linkable', 1);
    }

    public function not_linkable(): void
    {
        $this->batchElementUpdate('linkable', 0);
    }

    public function editable(): void
    {
        $this->batchElementUpdate('editable', 1);
    }

    public function not_editable(): void
    {
        $this->batchElementUpdate('editable', 0);
    }

    public function list_include(): void
    {
        $this->batchElementUpdate('list_include', 1);
    }

    public function no_list_include(): void
    {
        $this->batchElementUpdate('list_include', 0);
    }

    public function search_include(): void
    {
        $this->batchElementUpdate('search_include', 1);
    }

    public function no_search_include(): void
    {
        $this->batchElementUpdate('search_include', 0);
    }

    private function batchElementUpdate(string $field, int $value): void
    {
        $cids = $this->input->get('cid', [], 'array');
        ArrayHelper::toInteger($cids);

        if ($cids) {
            $db = $this->getDatabase();
            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__contentbuilder_elements'))
                    ->set($db->quoteName($field) . ' = ' . $value)
                    ->where($db->quoteName('id') . ' IN (' . implode(',', $cids) . ')')
            );
            $db->execute();
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=' . $this->view_item . '&id=' . $this->input->getInt('id'), false));
    }
    
}
