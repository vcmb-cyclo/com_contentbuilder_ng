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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

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
    /*public function apply($key = null, $urlVar = null)
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
                Text::_('JLIB_APPLICATION_SAVE_SUCCESS'),
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
*/
    /*
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
                Text::_('JLIB_APPLICATION_SAVE_SUCCESS'),
                'message'
            );

            // 👉 Si tu veux plutôt revenir à la liste après Save :
            // $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=forms', false), Text::_('JLIB_APPLICATION_SAVE_SUCCESS'));

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
    }*/


    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        // Si le core ne passe pas l'id, on tente de le retrouver
        if (!$recordId) {
            // 1) depuis jform (POST)
            $jform = $this->input->post->get('jform', [], 'array');
            $recordId = (int) ($jform[$urlVar] ?? 0);

            // 2) depuis l'input (GET/POST)
            if (!$recordId) {
                $recordId = (int) $this->input->getInt($urlVar, 0);
            }

            // 3) depuis le model state (si dispo)
            if (!$recordId) {
                $model = $this->getModel($this->view_item, '', ['ignore_request' => true]);
                if ($model) {
                    $recordId = (int) $model->getState($model->getName() . '.id', 0);
                }
            }
        }

        // Appel au core pour conserver tmpl, return, etc.
        $append = parent::getRedirectToItemAppend($recordId, $urlVar);

        // Filet de sécurité : si le parent n’a pas ajouté id=...
        if ($recordId && strpos($append, $urlVar . '=') === false) {
            $append .= '&' . $urlVar . '=' . (int) $recordId;
        }

        return $append;
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
                Text::_('JLIB_APPLICATION_SAVE_SUCCESS'),
                'message'
            );

            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=0', false));
            return false;
        }
    }

    public function editable_include(): void
    {
        $this->editable();
    }


    // ==================================================================
    // Toutes les tâches sur la liste des ÉLÉMENTS (champs du formulaire)
    // ==================================================================
    // Ces tâches agissent sur les éléments sélectionnés dans l'édition d'un form
    // Elles doivent utiliser Elements
    public function listorderup(): void
    {
        $model = $this->getModel('Elements', 'Administrator');
        $model->move(-1); // ou utilise reorder si tu préfères
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=' . $this->view_item . '&id=' . $this->input->getInt('id'), false));
    }

    public function listorderdown(): void
    {
        $model = $this->getModel('Elements', 'Administrator');
        $model->move(1);
        $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=' . $this->view_item . '&id=' . $this->input->getInt('id'), false));
    }

    // ==================================================================
    // Toutes les tâches sur les ÉLÉMENTS (champs du formulaire)
    // ==================================================================
    // Ces tâches agissent sur les éléments sélectionnés dans l'édition d'un form
    // Elles doivent utiliser ElementoptionModel
 
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
        $model = $this->getModel('Elements', 'Administrator', ['ignore_request' => true]);

        $orderMap = $this->input->post->get('order', [], 'array'); // [id => ordering]
        if (empty($orderMap)) {
            return;
        }

        $pks   = array_keys($orderMap);
        $order = array_values($orderMap);

        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        if (!$model->saveorder($pks, $order)) {
            $this->enqueueMessage($model->getError() ?: 'Saveorder failed', 'warning');
        }
    }


    // Les tâches batch sur les éléments (linkable, editable, etc.)
    public function linkable(): void
    {
        $this->elementsUpdate('linkable', 1);
    }

    public function not_linkable(): void
    {
        $this->elementsUpdate('linkable', 0);
    }

    public function editable(): void
    {
        $this->elementsUpdate('editable', 1);
    }

    public function not_editable(): void
    {
        $this->elementsUpdate('editable', 0);
    }

    public function list_include(): void
    {
        $this->elementsUpdate('list_include', 1);
    }

    public function no_list_include(): void
    {
        $this->elementsUpdate('list_include', 0);
    }

    public function search_include(): void
    {
        $this->elementsUpdate('search_include', 1);
    }

    public function no_search_include(): void
    {
        $this->elementsUpdate('search_include', 0);
    }

    public function listpublish(): bool
    {
        return $this->elementsPublish(1, 'COM_CONTENTBUILDER_PUBLISHED');
    }

    public function listunpublish(): bool
    {
        return $this->elementsPublish(0, 'COM_CONTENTBUILDER_UNPUBLISHED');
    }

    public function publish(): bool
    {
        return $this->elementsPublish(1, 'COM_CONTENTBUILDER_PUBLISHED');
    }

    public function unpublish(): bool
    {
        return $this->elementsPublish(0, 'COM_CONTENTBUILDER_UNPUBLISHED');
    }


    // Devrait migrer dans Element*Controller ?
    private function elementsUpdate(string $field, int $value): bool
    {
        try {
            $cids = $this->input->get('cid', [], 'array');
            $formId = (int) $this->input->getInt('id');
            ArrayHelper::toInteger($cids);

            $formId = $this->input->getInt('id');

            if (empty($cids)) {
                $this->setMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');
                $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form' . '&id=' . $formId, false));
                return false;
            }

            $model = $this->getModel('Elementoption', 'Administrator', ['ignore_request' => true]);
            $model->fieldUpdate($cids, $field, $value);

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=' . $this->view_item . '&id=' . $formId, false),
                Text::_('JLIB_APPLICATION_SAVE_SUCCESS')
            );
            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form' . '&id=' . $formId, false));
            return false;
        }
    }

    // Passe par le modèle.
    private function elementsPublish(int $state, string $successMsgKey)
    {
        try {
            $cids = $this->input->get('cid', [], 'array');
            ArrayHelper::toInteger($cids);

            $formId = (int) $this->input->getInt('id');

            if (empty($cids)) {
                $this->setMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');
                $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form' . '&id=' . $formId, false));
                return false;
            }

            $model = $this->getModel('Elementoption', 'Administrator', ['ignore_request' => true]);
            $model->publish($cids, $state);

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=form&layout=edit&id=' . $formId, false),
                Text::_($successMsgKey)
            );

            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=form', false));
            return false;
        }
    }
}
