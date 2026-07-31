<?php

/**
 * @package     ContentBuilderNG
 * @author      Markus Bopp
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace CB\Component\Contentbuilderng\Administrator\Controller;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\AdminController;
use CB\Component\Contentbuilderng\Administrator\Model\ElementoptionsModel;
use CB\Component\Contentbuilderng\Administrator\Service\FormSupportService;

use CB\Component\Contentbuilderng\Administrator\Controller\Traits\ComponentAccessTrait;

class ElementoptionsController extends AdminController
{
    use ComponentAccessTrait;

    private function getElementoptionsModelForSave(): ElementoptionsModel
    {
        $model = $this->getModel('Elementoptions', 'Administrator', ['ignore_request' => true])
            ?: $this->getModel('Elementoptions', 'Contentbuilderng', ['ignore_request' => true]);

        if (!$model instanceof ElementoptionsModel) {
            throw new \RuntimeException('ElementoptionsModel not found');
        }

        return $model;
    }

    /**
     * Regenerate templates locked to the source form after an element change.
     *
     * @return array<int, array{type: string, message: string}>
     */
    private function resyncLockedTemplatesAfterSave(ElementoptionsModel $model): array
    {
        try {
            $element = $model->getData();
            $formId = (int) ($element->form_id ?? 0);

            if ($formId <= 0) {
                return [];
            }

            $component = $this->app->bootComponent('com_contentbuilderng');
            $service = $component->getContainer()->get(FormSupportService::class);

            return $service->resyncLockedTemplates($formId, (int) $this->app->getIdentity()->id);
        } catch (\Throwable $e) {
            return [[
                'type' => 'warning',
                'message' => Text::sprintf(
                    'COM_CONTENTBUILDERNG_TEMPLATE_LOCKED_RESYNC_UNAVAILABLE',
                    $e->getMessage()
                ),
            ]];
        }
    }

    function display($cachable = false, $urlparams = array())
    {
        $this->input->set('tmpl', $this->input->getWord('tmpl', null));
        $this->input->set('layout', $this->input->getWord('layout', null));
        $this->input->set('view', 'elementoptions');

        parent::display();
    }

    function save()
    {
        $this->checkToken();

        $model = $this->getElementoptionsModelForSave();
        $id = $model->store();

        if ($id) {
            $msg = Text::_('COM_CONTENTBUILDERNG_SAVED');
            $resyncMessages = $this->resyncLockedTemplatesAfterSave($model);
            $msgType = 'message';
        } else {
            $msg = Text::_('COM_CONTENTBUILDERNG_ERROR');
            $resyncMessages = [];
            $msgType = 'error';
        }


        $type_change_url = '';
        $type_change = $this->input->getInt('type_change', 0);
        if ($type_change) {
            $type_change_url = '&type_change=1&type_selection=' . $this->input->getCmd('type_selection', '');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = Route::_('index.php?option=com_contentbuilderng&view=elementoptions&tabStartOffset=' . $this->input->getInt('tabStartOffset', 0) . '&tmpl=component&element_id=' . $this->input->getInt('element_id', 0) . '&id=' . $this->input->getInt('id', 0) . $type_change_url, false);
        $this->setRedirect($link, $msg, $msgType);

        foreach ($resyncMessages as $resyncMessage) {
            $this->app->enqueueMessage(
                (string) ($resyncMessage['message'] ?? ''),
                (string) ($resyncMessage['type'] ?? 'message')
            );
        }
    }
}
