<?php
/**
 * ContentBuilder Forms controller.
 *
 * Handles actions (copy, delete, publish, ...) for forms list in the admin interface.
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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use CB\Component\Contentbuilder\Administrator\Controller\BaseAdminController;
use CB\Component\Contentbuilder\Administrator\Helper\Logger;

final class FormsController extends BaseAdminController
{
    /**
     * Nom de la vue liste et item (convention Joomla 6).
     */
    protected $view_list = 'forms';
    protected $view_item = 'form';

    public function __construct($config = [])
    {
        parent::__construct($config);

        // Si tu veux absolument garder ces paramètres en session (legacy),
        // tu peux le faire proprement via $this->input.
        $session = Factory::getApplication()->getSession();

        if ($this->input->getInt('email_users', -1) !== -1) {
            $session->set('email_users', $this->input->get('email_users', 'none'), 'com_contentbuilder');
        }

        if ($this->input->getInt('email_admins', -1) !== -1) {
            $session->set('email_admins', $this->input->get('email_admins', ''), 'com_contentbuilder');
        }

        if ($this->input->getInt('slideStartOffset', -1) !== -1) {
            $session->set('slideStartOffset', $this->input->getInt('slideStartOffset', 1));
        }

        if ($this->input->getInt('tabStartOffset', -1) !== -1) {
            $session->set('tabStartOffset', $this->input->getInt('tabStartOffset', 0));
        }
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|false  Model object on success; otherwise false on failure.
     */
/*
    public function getModel($name = 'Form', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
*/

    public function display($cachable = false, $urlparams = []): void
    {
        $this->input->set('view', $this->view_list);
        parent::display($cachable, $urlparams);
    }

    // Publish methode : manage both publish and unpublish
    public function publish()
    {
        // Vérif CSRF.
        $this->checkToken();

        $cid = (array) $this->input->get('cid', [], 'array');
        $cid = array_values(array_filter(array_map('intval', $cid)));
        $task = $this->input->getCmd('task'); // forms.publish / forms.unpublish

        Logger::debug('Click [Un]Publish action', [
            'task' => $task,
            'cid'  => $cid,
        ]);

        $model = $this->getModel('Form', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        error_log('TASK=' . $task);
        error_log('MODEL=' . ($model ? get_class($model) : 'false'));

        $value = str_contains($task, 'unpublish') ? 0 : 1;

        try {
            $result = $model->publish($cid, $value);
            // Message OK
            /* $count = count((array) $cid);
            $this->setMessage(Text::sprintf(
                $value ? 'COM_CONTENTBUILDER_N_ITEMS_PUBLISHED' : 'COM_CONTENTBUILDER_N_ITEMS_UNPUBLISHED',
                $count
            ));*/

            $this->setMessage(
                $value ? Text::_('COM_CONTENTBUILDER_PUBLISHED')
                       : Text::_('COM_CONTENTBUILDER_UNPUBLISHED'),
                'message'
            );
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_contentbuilder&view=forms');
    }

    public function delete(): void
    {
        // Vérif CSRF.
        $this->checkToken();

        $cid = (array) $this->input->get('cid', [], 'array');
        $cid = array_values(array_filter(array_map('intval', $cid)));

        Logger::debug('Click Delete action', [
            'task' => $this->input->getCmd('task'),
            'cid'  => $cid,
        ]);

        $model = $this->getModel('Form', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        try {
            $model->delete($cid);

            $count = count($cid);
            // Message Joomla standard (tu peux aussi faire tes propres Text::sprintf)
            $this->setMessage(
                Text::plural('JLIB_APPLICATION_N_ITEMS_DELETED', $count),
                'message'
            );
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_contentbuilder&view=forms');
    }

    /**
     * Copie (custom)
     */
    public function copy(): void
    {
        // Vérif CSRF.
        $this->checkToken();

        $cid = (array) $this->input->get('cid', [], 'array');
        $cid = array_values(array_filter(array_map('intval', $cid)));

        Logger::debug('Click Copy action', [
            'task' => $this->input->getCmd('task'),
            'cid'  => $cid,
        ]);

        $model = $this->getModel('Form', 'Contentbuilder', ['ignore_request' => true]);
        if (!$model) {
            throw new \RuntimeException('FormModel introuvable');
        }

        try {
            $model->copy($cid);

            // Message Joomla standard (tu peux aussi faire tes propres Text::sprintf)
            $this->setMessage(
                Text::plural('COM_CONTENTBUILDER_COPIED'),
                'message'
            );
        } catch (\Throwable $e) {
            $this->setMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&limitstart=' . $this->input->getInt('limitstart'), false),
            Text::_('COM_CONTENTBUILDER_COPIED')
        );
    }


}
