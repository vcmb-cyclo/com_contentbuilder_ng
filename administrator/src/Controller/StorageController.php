<?php

/**
 * @package     ContentBuilder
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;

class StorageController extends FormController
{
    protected $view_list = 'storages';

    /**
     * Surcharge save pour rester compatible avec ton modèle legacy (store/storeCsv).
     * Task: storage.save / storage.apply
     */
    public function save($key = null, $urlVar = null)
    {
        // Sécurité token
        $this->checkToken();

        /** @var \CB\Component\Contentbuilder\Administrator\Model\StorageModel $model */
        $model = $this->getModel('Storage');

        // Lecture fichier upload (legacy)
        $file = Factory::getApplication()->getInput()->files->get('csv_file', null, 'array');

        if (!is_array($file) || empty($file['name']) || (int) ($file['size'] ?? 0) <= 0) {
            $id = $model->store();
        } else {
            // sécurise le nom
            $file['name'] = File::makeSafe($file['name']);
            $id = $model->storeCsv($file);
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
            $link = Route::_('index.php?option=com_contentbuilder&task=storage.edit&cid[]=' . (int) $id, false);
        } else {
            // Retour liste
            $link = Route::_('index.php?option=com_contentbuilder&view=storages', false);
        }

        $this->setRedirect($link, $msg);

        return true;
    }

    /**
     * Task: storage.cancel (signature compatible)
     */
    public function cancel($key = null)
    {
        $this->checkToken();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=storages', false),
            Text::_('COM_CONTENTBUILDER_CANCELLED')
        );

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
        $model = $this->getModel('Storage');

        // IMPORTANT : ton model delete() doit utiliser $pks, pas CBRequest (je t’ai donné le patch)
        $ok = $model->delete($cid);

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=storages', false),
            $ok ? Text::_('COM_CONTENTBUILDER_DELETED') : Text::_('COM_CONTENTBUILDER_ERROR'),
            $ok ? 'message' : 'error'
        );

        return $ok;
    }
}
