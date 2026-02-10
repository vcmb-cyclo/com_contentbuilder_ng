<?php
namespace CB\Component\Contentbuilder_ng\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use CB\Component\Contentbuilder_ng\Administrator\Service\StorageFieldService;

class StoragefieldController extends BaseController
{
    public function add(): bool
    {
        $this->checkToken();

        $storageId = (int) $this->input->getInt('storage_id', 0);
        if (!$storageId) {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder_ng&view=storages', false),
                'Missing storage_id',
                'error'
            );
            return false;
        }

        // Les données peuvent venir en POST "jform" (recommandé) ou en POST direct.
        $jform = $this->input->post->get('jform', [], 'array');

        $fieldname  = trim((string) ($jform['fieldname'] ?? $this->input->post->getString('fieldname', '')));
        $fieldtitle = trim((string) ($jform['fieldtitle'] ?? $this->input->post->getString('fieldtitle', '')));
        $isGroup    = (int)   ($jform['is_group'] ?? $this->input->post->getInt('is_group', 0));
        $groupDef   = (string)($jform['group_definition'] ?? $this->input->post->getString('group_definition', ''));

        if ($fieldname === '') {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder_ng&task=storage.edit&id=' . $storageId, false),
                Text::_('COM_CONTENTBUILDER_NG_FIELDNAME_REQUIRED'),
                'warning'
            );
            return false;
        }

        try {
            $service = new StorageFieldService();
            $service->addField($storageId, [
                'name'             => $fieldname,
                'title'            => $fieldtitle,
                'is_group'         => $isGroup,
                'group_definition' => $groupDef,
            ]);

            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder_ng&task=storage.edit&id=' . $storageId, false),
                Text::_('COM_CONTENTBUILDER_NG_FIELD_ADDED'),
                'message'
            );
            return true;

        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder_ng&task=storage.edit&id=' . $storageId, false),
                $e->getMessage(),
                'error'
            );
            return false;
        }
    }
}
