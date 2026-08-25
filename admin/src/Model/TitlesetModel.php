<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Model;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Service\CbStatsTitleSetManagerService;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

final class TitlesetModel extends BaseDatabaseModel
{
    private ?array $dataCache = null;

    public function getForm(): Form
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_contentbuilderng/forms');
        $form = Form::getInstance('com_contentbuilderng.titleset', 'titleset', ['control' => 'jform']);
        $form->bind($this->getData());

        return $form;
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        if ($this->dataCache !== null) {
            return $this->dataCache;
        }

        $app = Factory::getApplication();
        if (!$app instanceof AdministratorApplication) {
            throw new \RuntimeException('Unexpected application instance.');
        }
        $saved = $app->getUserState('com_contentbuilderng.titleset.data', []);
        if (is_array($saved) && $saved !== []) {
            $app->setUserState('com_contentbuilderng.titleset.data', []);

            return $this->dataCache = $saved;
        }

        $filename = trim($app->getInput()->getString('filename', ''));
        $source = trim($app->getInput()->getCmd('source', ''));
        if ($filename === '') {
            return $this->dataCache = [
                'filename' => '',
                'name' => '',
                'description' => '',
                'locale' => $app->getLanguage()->getTag(),
                'version' => '1.0',
                'author' => '',
                'comments' => '',
                'modified' => null,
                'source' => 'custom',
                'titles' => [['value' => '', 'label' => '']],
            ];
        }

        $data = (new CbStatsTitleSetManagerService(JPATH_SITE))->load($filename, $source);
        if ($source === 'provided' && $app->getInput()->getBool('duplicate', false)) {
            $data['source'] = 'custom';
        }
        if ($source === 'custom' && $app->getInput()->getBool('copy', false)) {
            $data['filename'] = (new CbStatsTitleSetManagerService(JPATH_SITE))->suggestCopyFilename($filename);
        }

        return $this->dataCache = $data;
    }
}
