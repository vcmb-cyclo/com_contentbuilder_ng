<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Controller;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Service\CbStatsTitleSetManagerService;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

final class TitlesetController extends BaseController
{
    private function getApp(): AdministratorApplication
    {
        if (!$this->app instanceof AdministratorApplication) {
            throw new \RuntimeException('Unexpected application instance.');
        }

        return $this->app;
    }

    public function apply(): void
    {
        $this->saveAndRedirect(true);
    }

    public function save(): void
    {
        $this->saveAndRedirect(false);
    }

    public function validateFile(): void
    {
        $this->checkToken();
        $this->assertAuthorized();
        $data = $this->input->post->get('jform', [], 'array');
        $result = (new CbStatsTitleSetManagerService(JPATH_SITE))->validate($data);
        $this->getApp()->setUserState('com_contentbuilderng.titleset.data', $data);
        $this->setMessage(
            Text::_($result['valid']
                ? 'COM_CONTENTBUILDERNG_TITLESETS_VALID'
                : 'COM_CONTENTBUILDERNG_TITLESETS_INVALID'),
            $result['valid'] ? 'message' : 'warning'
        );
        $this->setRedirect(Route::_('index.php?option=com_contentbuilderng&view=titleset', false));
    }

    public function deleteFile(): void
    {
        $this->checkToken();
        $this->assertAuthorized();
        $data = $this->input->post->get('jform', [], 'array');
        (new CbStatsTitleSetManagerService(JPATH_SITE))->delete((string) ($data['filename'] ?? ''));
        $this->setMessage(Text::_('COM_CONTENTBUILDERNG_TITLESETS_DELETED'));
        $this->setRedirect(Route::_('index.php?option=com_contentbuilderng&view=titlesets', false));
    }

    public function cancel(): void
    {
        $this->setRedirect(Route::_('index.php?option=com_contentbuilderng&view=titlesets', false));
    }

    private function saveAndRedirect(bool $apply): void
    {
        $this->checkToken();
        $this->assertAuthorized();
        $data = $this->input->post->get('jform', [], 'array');

        try {
            $filename = (new CbStatsTitleSetManagerService(JPATH_SITE))->save($data);
            $this->setMessage(Text::_('COM_CONTENTBUILDERNG_TITLESETS_SAVED'));
            $url = $apply
                ? 'index.php?option=com_contentbuilderng&view=titleset&source=custom&filename='
                    . rawurlencode($filename)
                : 'index.php?option=com_contentbuilderng&view=titlesets';
        } catch (\InvalidArgumentException $exception) {
            $this->getApp()->setUserState('com_contentbuilderng.titleset.data', $data);
            $errorKey = match (true) {
                str_contains($exception->getMessage(), 'titles') => 'COM_CONTENTBUILDERNG_TITLESETS_SAVE_FAILED_MAPPINGS',
                str_contains($exception->getMessage(), 'name') => 'COM_CONTENTBUILDERNG_TITLESETS_SAVE_FAILED_NAME',
                default => 'COM_CONTENTBUILDERNG_TITLESETS_SAVE_FAILED_FILENAME',
            };
            $this->setMessage(Text::_($errorKey), 'error');
            $url = 'index.php?option=com_contentbuilderng&view=titleset';
        } catch (\Throwable) {
            $this->getApp()->setUserState('com_contentbuilderng.titleset.data', $data);
            $this->setMessage(Text::_('COM_CONTENTBUILDERNG_TITLESETS_SAVE_FAILED'), 'error');
            $url = 'index.php?option=com_contentbuilderng&view=titleset';
        }

        $this->setRedirect(Route::_($url, false));
    }

    private function assertAuthorized(): void
    {
        if (!$this->getApp()->getIdentity()->authorise('core.manage', 'com_contentbuilderng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
