<?php
/**
 * ContentBuilder Storage controller.
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
namespace CB\Component\Contentbuilder\Administrator\Controller;

// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController as BaseFormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;
use CB\Component\Contentbuilder\Administrator\Helper\Logger;


class StorageController extends BaseFormController
{
    /**
     * Vue item et vue liste utilisées par les redirects du core
     */
    protected $view_list = 'storages';
    protected $view_item = 'storage';

    /**
     * Surcharge save pour rester compatible avec ton modèle legacy (store/storeCsv).
     * Task: storage.save / storage.apply
     */
    public function save($key = null, $urlVar = null)
    {
        // Sécurité token
        $this->checkToken();

        Logger::info('save called', ['key' => $key, 'urlVar' => $urlVar]);

        $model = $this->getModel('Storage', '', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        // Lecture fichier upload (legacy)
        $file = Factory::getApplication()->getInput()->files->get('csv_file', null, 'array');

        if (!is_array($file) || empty($file['name']) || (int) ($file['size'] ?? 0) <= 0) {
            try {
                $id = $model->store(); // Méthode legacy

                if (!$id) {
                    $this->setRedirect(
                        Route::_('index.php?option=com_contentbuilder&task=storage.edit&id=' . (int) $this->input->getInt('id', 0), false),
                        $model->getError() ?: 'Store failed (no id returned)',
                        'error'
                    );
                    return false;
                }
            } catch (\Throwable $e) {
                Logger::exception($e);
                $this->setMessage($e->getMessage(), 'warning');
                return false;
            }
        } else {
            // sécurise le nom
            $file['name'] = File::makeSafe($file['name']);

            try {
                $id = $model->storeCsv($file); // Méthode legacy

                if (!$id) {
                    $this->setRedirect(
                        Route::_('index.php?option=com_contentbuilder&task=storage.edit&id=' . (int) $this->input->getInt('id', 0), false),
                        $model->getError() ?: 'Store failed (no id returned)',
                        'error'
                    );
                    return false;
                }
            } catch (\Throwable $e) {
                Logger::exception($e);
                $this->setMessage($e->getMessage(), 'warning');
                return false;
            }
        }

        // Message
        if (is_numeric($id) && (int) $id > 0) {
            $msg = Text::_('COM_CONTENTBUILDER_SAVED');
        } elseif (is_string($id) && $id !== '') {
            // storeCsv peut renvoyer un texte d’erreur
            $msg = $id;
        } else {
            $msg = Text::_('COM_CONTENTBUILDER_ERROR');
        }

        // Apply vs Save
        $task = $this->getTask();
        if ($task === 'apply' && is_numeric($id) && (int) $id > 0) {
            // Retour en édition
            $link = Route::_('index.php?option=com_contentbuilder&task=storage.edit&id=' . (int) $id, false);
        } else {
            // Retour liste
            $link = Route::_('index.php?option=com_contentbuilder&view=storages', false);
        }

        $this->setRedirect($link, $msg);

        return true;
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
                Route::_('index.php?option=com_contentbuilder&view=storages', false),
                Text::_('JERROR_NO_ITEMS_SELECTED'),
                'warning'
            );
            return false;
        }

        /** @var \CB\Component\Contentbuilder\Administrator\Model\StorageModel $model */
        $model = $this->getModel('Storage', 'Contentbuilder');

        // IMPORTANT : ton model delete() doit utiliser $pks, pas CBRequest (je t’ai donné le patch)
        try {
            $ok = $model->delete($cid);
            if (!$ok) {
                $this->setRedirect(
                    Route::_('index.php?option=com_contentbuilder&view=storages', false),
                    Text::_('COM_CONTENTBUILDER_ERROR'),
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
            Route::_('index.php?option=com_contentbuilder&view=storages', false),
            Text::_('COM_CONTENTBUILDER_DELETED'),
            'message'
        );

        return $ok;
    }

    public function save2new(){
        $model = $this->getModel('Storage', 'Contentbuilder');
        $model->store();

        $this->setRedirect('index.php?option=com_contentbuilder&view=storage&layout=edit&id=0');
        return true;
    }

 
    public function add()
    {
        $this->setRedirect('index.php?option=com_contentbuilder&view=storage&layout=edit&id=0');
        return true;
    }
   
    public function publish()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $cid = $input->get('cid', [], 'array');
        ArrayHelper::toInteger($cid);

        if (count($cid) == 1) {
            $model = $this->getModel('Storage', 'Contentbuilder');
            $model->setPublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('Storage', 'Contentbuilder');
            $model->setPublished();
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=storage&limitstart=' . $this->input->getInt('limitstart'), false),
            Text::_('COM_CONTENTBUILDER_PUBLISHED'));
    }

    public function unpublish()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $cid = $input->get('cid', [], 'array');
        ArrayHelper::toInteger($cid);

        if (count($cid) == 1) {
            $model = $this->getModel('Storage', 'Contentbuilder');
            $model->setUnpublished();
        } else if (count($cid) > 1) {
            $model = $this->getModel('Storage', 'Contentbuilder');
            $model->setUnpublished();
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=storage&limitstart=' . $this->input->getInt('limitstart'), false),
            Text::_('COM_CONTENTBUILDER_UNPUBLISHED'));
    }


    public function apply()
    {
        $this->save(true);
    }

}
