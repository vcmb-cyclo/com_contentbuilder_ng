<?php

/**
 * Contrôleur servant à intéragir sur la table décrite par Storage.
 * @package     ContentBuilder
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use CB\Component\Contentbuilder\Administrator\Service\DatatableService;

class DatatableController extends BaseController
{
    public function create(): bool
    {
        $this->checkToken();

        $storageId = (int) $this->input->getInt('id', 0);

        if ($storageId < 1) {
            $jform = $this->input->post->get('jform', [], 'array');
            $storageId = (int) ($jform['id'] ?? 0);
        }

        if (!$storageId) {
            $this->setRedirect(Route::_('index.php?option=com_contentbuilder&view=storage', false), 'Missing storage_id', 'error');
            return false;
        }

        try {
            $container = Factory::getApplication()->bootComponent('com_contentbuilder')->getContainer();
            $service   = $container->get(DatatableService::class);

            $breturn = $service->createForStorage($storageId);
            if ($breturn) {
                $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&task=storage.edit&id=' . $storageId, false),
                Text::_('COM_CONTENTBUILDER_TABLE_CREATED'),
                'message'
                );
            }
            return true;

        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&task=storage.edit&id=' . $storageId, false),
                $e->getMessage(),
                'error'
            );
            return false;
        }
    }

    public function sync(): bool
    {
        $this->checkToken();

        $storageId = (int) $this->input->getInt('id', 0);

        if ($storageId < 1) {
            $jform = $this->input->post->get('jform', [], 'array');
            $storageId = (int) ($jform['id'] ?? 0);
        }

        if (!$storageId) {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=storage', false),
                'Missing storage_id',
                'error'
            );
            return false;
        }

        try {
            (new DatatableService())->syncColumnsFromFields($storageId);

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&task=storage.edit&id=' . $storageId, false),
                Text::_('COM_CONTENTBUILDER_DATATABLE_SYNCED'),
                'message'
            );
            return true;
        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&task=storage.edit&id=' . $storageId, false),
                $e->getMessage(),
                'error'
            );
            return false;
        }
    }
}
