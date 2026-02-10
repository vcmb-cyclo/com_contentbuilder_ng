<?php

/**
 * ContentBuilder NG Storage controller.
 *
 * Handles actions for storage in the admin interface.
 *
 * @package     ContentBuilder
 * @subpackage  Administrator.Controller
 * @author      Xavier DANO
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */

namespace CB\Component\Contentbuilder_ng\Administrator\Controller;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController as BaseFormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;
use CB\Component\Contentbuilder_ng\Administrator\Helper\Logger;

class StorageController extends BaseFormController
{
    /**
     * Vue item et vue liste utilisées par les redirects du core
     */
    protected $view_list = 'storages';
    protected $view_item = 'storage';

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
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder_ng&task=storages.display', false));
            return false;
        }
    }

    /**
     * Surcharge save pour rester compatible avec ton modèle legacy (save/storeCsv).
     * Task: storage.save / storage.apply
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        $file = $this->input->files->get('csv_file', null, 'array');

        // Ensure required fields exist when using bytable (name/title may be disabled in the form).
        $data = $this->input->post->get('jform', [], 'array');
        if (!empty($data['bytable'])) {
            if (empty($data['name'])) {
                $data['name'] = $data['bytable'];
            }
            if (empty($data['title'])) {
                $data['title'] = $data['bytable'];
            }
            $this->input->post->set('jform', $data);
        }

        // Pas de CSV → core
        if (!is_array($file) || empty($file['name']) || (int) ($file['size'] ?? 0) <= 0) {
            $result = parent::save($key, $urlVar);
            if ($result === false) {
                return false;
            }

            // Récupère l'id
            $jform = $this->input->post->get('jform', [], 'array');
            $id = (int) ($jform['id'] ?? $this->input->getInt('id'));

            if ($id) {
                /** @var \CB\Component\Contentbuilder_ng\Administrator\Model\StorageModel $model */
                $model = $this->getModel('Storage', 'Administrator', ['ignore_request' => true]);
                $model->ensureDataTable($id);
            }

            return $result;
        }

        // CSV → 1) sauvegarde core du storage, 2) import
        $file['name'] = File::makeSafe($file['name']);

        try {
            // (A) Sauver l'item via le core (ça gère jform + table + hooks)
            $data  = $this->input->post->get('jform', [], 'array');
            $model = $this->getModel('Storage', 'Administrator', ['ignore_request' => true]);

            Logger::info('Controller got model class', ['class' => get_class($model)]);
            $id = $model->save($data);
            if (!$id) {
                $this->setRedirect(
                    Route::_('index.php?option=com_contentbuilder_ng&task=storage.edit&id=' . (int) ($data['id'] ?? 0), false),
                    $model->getError() ?: 'Save failed',
                    'error'
                );
                return false;
            }

            // (B) Import CSV (en sachant sur quel storage bosser)
            $ok = $model->storeCsv($file, (int) $id); // <= idéalement tu changes la signature
            if (!$ok) {
                $this->setRedirect(
                    Route::_('index.php?option=com_contentbuilder_ng&task=storage.edit&id=' . (int) $id, false),
                    $model->getError() ?: 'CSV import failed',
                    'error'
                );
                return false;
            }
        } catch (\Throwable $e) {
            Logger::exception($e);
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder_ng&task=storages.display', false),
                $e->getMessage(),
                'error'
            );
            return false;
        }

        // Redirect apply/save
        $task = $this->getTask();
        $link = ($task === 'apply')
            ? Route::_('index.php?option=com_contentbuilder_ng&task=storage.edit&id=' . (int) $id, false)
            : Route::_('index.php?option=com_contentbuilder_ng&task=storages.display', false);

        $this->setRedirect($link, Text::_('COM_CONTENTBUILDER_NG_SAVED'));
        return true;
    }


    public function addfield(): bool
    {
        $this->checkToken();

        $jform = $this->input->post->get('jform', [], 'array');
        $storageId = (int) ($jform['id'] ?? $this->input->getInt('id'));

        /** @var \CB\Component\Contentbuilder_ng\Administrator\Model\StorageModel $model */
        $model = $this->getModel('Storage', 'Administrator', ['ignore_request' => true]);

        if (!$model) {
            throw new \RuntimeException('StorageModel not found');
        }

        $ok = $model->addFieldFromRequest($storageId);

        $msg = $ok
            ? Text::_('COM_CONTENTBUILDER_NG_FIELD_ADDED')
            : ($model->getError() ?: Text::_('COM_CONTENTBUILDER_NG_FIELD_ADD_FAILED'));

        $type = $ok ? 'message' : 'warning';

        // Redirect vers l’édition du storage
        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder_ng&task=storage.edit&id=' . (int) $storageId, false),
            $msg,
            $type
        );

        return $ok;
    }


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
     * Task: storage.delete (au lieu de remove)
     * Joomla va passer cid[] dans l’input.
     */
    public function delete()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $input = $app->getInput();

        $cid = $input->get('cid', [], 'array');
        ArrayHelper::toInteger($cid);

        if (!$cid) {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder_ng&task=storages.display', false),
                Text::_('JERROR_NO_ITEMS_SELECTED'),
                'warning'
            );
            return false;
        }

        /** @var \CB\Component\Contentbuilder_ng\Administrator\Model\StorageModel $model */
        $model = $this->getModel('Storage', 'Administrator', ['ignore_request' => true])
            ?: $this->getModel('Storage', 'Contentbuilder_ng', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('StorageModel not found');
        }

        // IMPORTANT : ton model delete() doit utiliser $pks, pas CBRequest (je t’ai donné le patch)
        try {
            $ok = $model->delete($cid);
            if (!$ok) {
                $this->setRedirect(
                    Route::_('index.php?option=com_contentbuilder_ng&task=storages.display', false),
                    Text::_('COM_CONTENTBUILDER_NG_ERROR'),
                    'error'
                );
                return false;
            }
        } catch (\Throwable $e) {
            Logger::exception($e);
            $this->setMessage($e->getMessage(), 'warning');
            return false;
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder_ng&task=storages.display', false),
            Text::_('COM_CONTENTBUILDER_NG_DELETED'),
            'message'
        );

        return $ok;
    }

    public function save2new()
    {
        $model = $this->getModel('Storage', 'Administrator', ['ignore_request' => true])
            ?: $this->getModel('Storage', 'Contentbuilder_ng', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('StorageModel not found');
        }
        $model->save();

        $this->setRedirect('index.php?option=com_contentbuilder_ng&task=storage.display&layout=edit&id=0');
        return true;
    }


    public function add()
    {
        $this->setRedirect('index.php?option=com_contentbuilder_ng&task=storage.display&layout=edit&id=0');
        return true;
    }


    public function publish(): bool
    {
        return $this->storagesPublish(1, 'COM_CONTENTBUILDER_NG_PUBLISHED');
    }

    public function unpublish(): bool
    {
        return $this->storagesPublish(0, 'COM_CONTENTBUILDER_NG_UNPUBLISHED');
    }

    /* 
    public function publish()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $cid = $input->get('cid', [], 'array');
        ArrayHelper::toInteger($cid);

        if (count($cid) == 1) {
            $model = $this->getModel('Storage', 'Contentbuilder_ng');
            $model->setPublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('Storage', 'Contentbuilder_ng');
            $model->setPublished();
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder_ng&task=storage.display&limitstart=' . $this->input->getInt('limitstart'), false),
            Text::_('COM_CONTENTBUILDER_NG_PUBLISHED'));
    }

    public function unpublish()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $cid = $input->get('cid', [], 'array');
        ArrayHelper::toInteger($cid);

        if (count($cid) == 1) {
            $model = $this->getModel('Storage', 'Contentbuilder_ng');
            $model->setUnpublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('Storage', 'Contentbuilder_ng');
            $model->setUnpublished();
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder_ng&task=storage.display&limitstart=' . $this->input->getInt('limitstart'), false),
            Text::_('COM_CONTENTBUILDER_NG_UNPUBLISHED'));
    }
*/

    // Passe par le modèle.
    private function storagesPublish(int $state, string $successMsgKey)
    {
        try {
            $cids = $this->input->get('cid', [], 'array');
            ArrayHelper::toInteger($cids);

            $storageId = (int) $this->input->getInt('id');

            if (empty($cids)) {
                $this->setMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');
                $this->setRedirect(Route::_('index.php?option=com_contentbuilder_ng&task=storage.display' . '&id=' . $storageId, false));
                return false;
            }

            $model = $this->getModel('Storagefields', 'Administrator', ['ignore_request' => true]);
            if (!$model) {
                throw new \RuntimeException('StoragefieldsModel introuvable');
            }
            $model->setStorageId($storageId);
            $model->publish($cids, $state);

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder_ng&task=storage.display&layout=edit&id=' . $storageId, false),
                Text::_($successMsgKey)
            );

            return true;
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder_ng&task=storage.display', false));
            return false;
        }
    }
}
