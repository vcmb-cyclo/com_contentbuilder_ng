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

    public function save2copy(): void
    {
        $this->checkToken();
        $this->assertAuthorized();
        $data = $this->input->post->get('jform', [], 'array');

        try {
            $filename = (new CbStatsTitleSetManagerService(JPATH_SITE))->saveCopy($data);
            $this->setMessage(Text::_('COM_CONTENTBUILDERNG_TITLESETS_COPY_SAVED'));
            $url = 'index.php?option=com_contentbuilderng&view=titleset&source=custom&filename='
                . rawurlencode($filename);
        } catch (\Throwable $exception) {
            $this->getApp()->setUserState('com_contentbuilderng.titleset.data', $data);
            $this->setMessage($this->saveFailureMessage($exception), 'error');
            $url = 'index.php?option=com_contentbuilderng&view=titleset';
        }

        $this->setRedirect(Route::_($url, false));
    }

    public function deleteSelected(): void
    {
        $this->checkToken();
        $this->assertAuthorized();
        $selected = $this->input->post->get('cid', [], 'array');
        $service = new CbStatsTitleSetManagerService(JPATH_SITE);
        $deleted = 0;

        foreach ($selected as $identifier) {
            [$source, $filename] = array_pad(explode(':', (string) $identifier, 2), 2, '');
            if ($source !== 'custom') {
                continue;
            }
            $service->delete($filename);
            $deleted++;
        }

        $this->setMessage(Text::plural('COM_CONTENTBUILDERNG_TITLESETS_N_DELETED', $deleted));
        $this->setRedirect(Route::_('index.php?option=com_contentbuilderng&view=titlesets', false));
    }

    public function exportSelected(): void
    {
        $this->checkToken();
        $this->assertAuthorized();
        $selected = $this->input->post->get('cid', [], 'array');
        $service = new CbStatsTitleSetManagerService(JPATH_SITE);
        $files = [];

        try {
            foreach ($selected as $identifier) {
                [$source, $filename] = array_pad(explode(':', (string) $identifier, 2), 2, '');
                $files[] = [
                    'filename' => $filename,
                    'source' => $source,
                    'contents' => $service->getFileContents($filename, $source),
                ];
            }
            if ($files === []) {
                throw new \RuntimeException(Text::_('COM_CONTENTBUILDERNG_TITLESETS_EXPORT_SELECT'));
            }

            if (count($files) === 1) {
                $file = $files[0];
                $this->sendDownload($file['filename'], 'text/plain; charset=utf-8', $file['contents']);
                return;
            }

            $temporary = tempnam(sys_get_temp_dir(), 'cbng-titlesets-');
            if ($temporary === false) {
                throw new \RuntimeException('Unable to create the title set export archive.');
            }
            $archive = new \ZipArchive();
            if ($archive->open($temporary, \ZipArchive::OVERWRITE) !== true) {
                @unlink($temporary);
                throw new \RuntimeException('Unable to open the title set export archive.');
            }
            $filenameCounts = array_count_values(array_column($files, 'filename'));
            foreach ($files as $file) {
                $entryName = $filenameCounts[$file['filename']] > 1
                    ? $file['source'] . '-' . $file['filename']
                    : $file['filename'];
                $archive->addFromString($entryName, $file['contents']);
            }
            $archive->close();
            $contents = file_get_contents($temporary);
            @unlink($temporary);
            if (!is_string($contents)) {
                throw new \RuntimeException('Unable to read the title set export archive.');
            }
            $this->sendDownload('cbng-titlesets.zip', 'application/zip', $contents);
        } catch (\Throwable) {
            $this->setMessage(Text::_('COM_CONTENTBUILDERNG_TITLESETS_EXPORT_FAILED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_contentbuilderng&view=titlesets', false));
        }
    }

    public function importFiles(): void
    {
        $this->checkToken();
        $this->assertAuthorized();
        $uploads = $this->normalizeUploads((array) $this->input->files->get('titleset_files', [], 'array'));
        $service = new CbStatsTitleSetManagerService(JPATH_SITE);
        $overwrite = $this->input->post->getBool('titleset_overwrite', false);

        try {
            if ($uploads === []) {
                throw new \RuntimeException(Text::_('COM_CONTENTBUILDERNG_TITLESETS_IMPORT_SELECT'));
            }
            $filenames = [];
            foreach ($uploads as $upload) {
                if (
                    $upload['error'] !== UPLOAD_ERR_OK
                    || $upload['size'] > 1048576
                    || !is_uploaded_file($upload['tmp_name'])
                ) {
                    throw new \RuntimeException('upload_invalid');
                }
                $filename = $service->validateImportFile($upload['tmp_name'], $upload['name'], $overwrite);
                if (isset($filenames[strtolower($filename)])) {
                    throw new \RuntimeException('batch_duplicate');
                }
                $filenames[strtolower($filename)] = true;
            }
            foreach ($uploads as $upload) {
                $service->importFile($upload['tmp_name'], $upload['name'], $overwrite);
            }
            $this->setMessage(Text::plural('COM_CONTENTBUILDERNG_TITLESETS_N_IMPORTED', count($uploads)));
        } catch (\Throwable $exception) {
            $key = match ($exception->getMessage()) {
                'invalid_filename' => 'COM_CONTENTBUILDERNG_TITLESETS_IMPORT_ERROR_FILENAME',
                'invalid_contents' => 'COM_CONTENTBUILDERNG_TITLESETS_IMPORT_ERROR_CONTENTS',
                'already_exists' => 'COM_CONTENTBUILDERNG_TITLESETS_IMPORT_ERROR_EXISTS',
                'upload_invalid' => 'COM_CONTENTBUILDERNG_TITLESETS_IMPORT_INVALID',
                'batch_duplicate' => 'COM_CONTENTBUILDERNG_TITLESETS_IMPORT_DUPLICATE',
                'read_failed', 'write_failed' => 'COM_CONTENTBUILDERNG_TITLESETS_IMPORT_ERROR_WRITE',
                default => 'COM_CONTENTBUILDERNG_TITLESETS_IMPORT_FAILED',
            };
            $this->setMessage(Text::_($key), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilderng&view=titlesets', false));
    }

    private function sendDownload(string $filename, string $contentType, string $contents): void
    {
        $app = $this->getApp();
        $app->setHeader('Content-Type', $contentType, true);
        $app->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"', true);
        $app->setHeader('Pragma', 'no-cache', true);
        $app->setHeader('Expires', '0', true);
        $app->sendHeaders();
        echo $contents;
        $app->close();
    }

    /** @return list<array{name: string, tmp_name: string, error: int, size: int}> */
    private function normalizeUploads(array $files): array
    {
        if (array_is_list($files)) {
            $uploads = [];
            foreach ($files as $file) {
                if (!is_array($file)) {
                    continue;
                }
                $uploads[] = [
                    'name' => (string) ($file['name'] ?? ''),
                    'tmp_name' => (string) ($file['tmp_name'] ?? ''),
                    'error' => (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE),
                    'size' => (int) ($file['size'] ?? 0),
                ];
            }

            return $uploads;
        }

        $names = (array) ($files['name'] ?? []);
        $temporaryNames = (array) ($files['tmp_name'] ?? []);
        $errors = (array) ($files['error'] ?? []);
        $sizes = (array) ($files['size'] ?? []);
        $uploads = [];

        foreach ($names as $index => $name) {
            $uploads[] = [
                'name' => (string) $name,
                'tmp_name' => (string) ($temporaryNames[$index] ?? ''),
                'error' => (int) ($errors[$index] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($sizes[$index] ?? 0),
            ];
        }

        return $uploads;
    }

    public function validateFile(): void
    {
        $this->checkToken();
        $this->assertAuthorized();
        $data = $this->input->post->get('jform', [], 'array');
        $result = (new CbStatsTitleSetManagerService(JPATH_SITE))->validate($data);
        $this->getApp()->setUserState('com_contentbuilderng.titleset.data', $data);
        $this->setMessage(
            $result['valid']
                ? Text::_('COM_CONTENTBUILDERNG_TITLESETS_VALID')
                : $this->validationErrorsMessage((array) $result['errors']),
            $result['valid'] ? 'message' : 'error'
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
        } catch (\Throwable $exception) {
            $this->getApp()->setUserState('com_contentbuilderng.titleset.data', $data);
            $this->setMessage($this->saveFailureMessage($exception), 'error');
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

    /** @param list<string> $errors */
    private function validationErrorsMessage(array $errors): string
    {
        $messages = [];
        foreach (array_unique($errors) as $error) {
            $messages[] = Text::_('COM_CONTENTBUILDERNG_TITLESETS_ERROR_' . strtoupper($error));
        }

        return implode(' ', $messages);
    }

    private function saveFailureMessage(\Throwable $exception): string
    {
        if ($exception instanceof \InvalidArgumentException) {
            return $this->validationErrorsMessage(array_values(array_filter(explode(',', $exception->getMessage()))));
        }

        return Text::_('COM_CONTENTBUILDERNG_TITLESETS_SAVE_FAILED_WRITE');
    }
}
