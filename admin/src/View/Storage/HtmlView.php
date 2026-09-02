<?php

/**
 * @package     ContentBuilderNG
 * @author      Markus Bopp
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

namespace CB\Component\Contentbuilderng\Administrator\View\Storage;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\Logger;
use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use CB\Component\Contentbuilderng\Site\Helper\PreviewLinkHelper;
use CB\Component\Contentbuilderng\Administrator\Extension\ContentbuilderngComponent;
use CB\Component\Contentbuilderng\Administrator\Model\StoragefieldsModel;
use CB\Component\Contentbuilderng\Administrator\Service\ExternalTableService;
use CB\Component\Contentbuilderng\Administrator\View\Contentbuilderng\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $form;
    public $fields;
    /** @var array<int,string> */
    public array $storageFieldNames = [];
    public $tables;
    public array $tableModes = [];
    public array $tableSourceTypes = [];
    public array $tableSourceLabels = [];
    public string $tableSourceType = '';
    public $pagination;
    public $ordering;
    public $item;
    public $state;
    public bool $frontend = false;
    public ?int $storageRecordsCount = null;
    public ?bool $storageTableExists = null;
    public string $storageTableLookupName = '';
    public string $storageTableErrorMessage = '';
    public string $wizardReturnUrl = '';
    public string $dataTableName = '';
    /** Data tab: paginated view of the storage's physical records. */
    public bool $showDataTab = false;
    /** @var array<int,object> */
    public array $recordItems = [];
    /** @var array<string,string> column name => label */
    public array $recordColumnLabels = [];
    public bool $recordsHavePrimaryKey = false;
    public ?object $recordPagination = null;
    public int $recordListLimit = 20;
    public int $recordListStart = 0;
    public string $recordSearch = '';
    public string $recordEditBaseUrl = '';
    public string $recordDeleteFormAction = '';
    public string $recordDeleteHiddenFields = '';
    /** @var array<int,array{name:string,columns:array<int,string>,unique:bool}> */
    public array $indexes = [];
    /** @var array<int,string> */
    public array $indexableColumns = [];
    /** @var array<int,object{id:int,name:string,title:string}> */
    public array $usingForms = [];

    private function getApp(): CMSApplicationInterface
    {
        $app = RuntimeContextHelper::getApplication();

        if (!$app instanceof CMSApplicationInterface) {
            throw new \RuntimeException('Unexpected application instance');
        }

        return $app;
    }

    private function getComponent(): ContentbuilderngComponent
    {
        $component = $this->getApp()->bootComponent('com_contentbuilderng');

        if (!$component instanceof ContentbuilderngComponent) {
            throw new \RuntimeException('Unexpected component instance');
        }

        return $component;
    }

    private function getDatabase(): DatabaseInterface
    {
        return $this->getComponent()->getContainer()->get(DatabaseInterface::class);
    }

    #[\Override]
    public function display($tpl = null): void
    {
        if ($this->getLayout() === 'help') {
            parent::display($tpl);
            return;
        }

        $app = $this->getApp();
        $input = $app->getInput();
        $identity = $app->getIdentity();
        $app->getInput()->set('hidemainmenu', true);

        $wa = $this->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_contentbuilderng');
        $wa->useScript('com_contentbuilderng.admin-ui');
        HTMLHelper::_(
            'script',
            'com_contentbuilderng/admin-ui.js',
            ['version' => 'auto', 'relative' => true],
            ['defer' => true]
        );
        Text::script('COM_CONTENTBUILDERNG_CONFIRM_DELETE_ONE');
        Text::script('COM_CONTENTBUILDERNG_CONFIRM_DELETE_MANY');

        if (!$this->frontend) {
            $wa->addInlineStyle(
                '.icon-logo_left{
                    background-image:url(' . Uri::root(true) . '/media/com_contentbuilderng/images/logo_left.png);
                    background-size:contain;
                    background-repeat:no-repeat;
                    background-position:center;
                    display:inline-block;
                    width:48px;
                    height:48px;
                    vertical-align:middle;
                }
                /* The admin template adds an external-link icon to every target="_blank"
                   anchor; the Preview button already has its own eye icon for that. */
                #toolbar-link::before{content:none;}'
            );
        }

        // Formulaire JForm
        $this->form = $this->getModel()->getForm();

        // Données (l’item)
        $this->item = $this->getModel()->getItem();
        $this->loadStorageTableStatus($this->item);
        $this->storageRecordsCount = $this->getStorageRecordsCount($this->item);

        $storageName = trim((string) ($this->item->name ?? ''));
        $isBytableStorage = ((int) ($this->item->bytable ?? 0) > 0);
        // La table interne n'est jamais stockée préfixée en base
        // (StorageModel utilise "#__" + name) ; une table externe (bytable)
        // vient de getTableList(), qui renvoie déjà, selon les pilotes, un
        // nom potentiellement préfixé : ne pas la re-préfixer.
        $this->dataTableName = $storageName === ''
            ? ''
            : ($isBytableStorage ? $storageName : $this->getDatabase()->getPrefix() . $storageName);

        $this->tables = $this->get('DbTables');
        $externalTableService = $this->getComponent()->getContainer()->get(ExternalTableService::class);
        foreach ((array) $this->tables as $tableName) {
            $this->tableModes[(string) $tableName] = $externalTableService->getBytableMode((string) $tableName);
            $this->tableSourceTypes[(string) $tableName] = $externalTableService->getSourceType((string) $tableName);
            $this->tableSourceLabels[(string) $tableName] = $externalTableService->getSourceLabel((string) $tableName);
        }
        if ($isBytableStorage && $storageName !== '') {
            $this->tableSourceType = $externalTableService->getSourceType($storageName);
        }

        // Chargement sécurisé des éléments
        $storageId = (int) ($this->item->id ?? $input->getInt('id', 0));

        $this->fields = [];
        $this->pagination = null;
        $this->state = null;

        try {
            $storageId = (int) ($this->item->id ?? $input->getInt('id', 0));
            if ($storageId > 0) {
                $factory = $this->getComponent()->getMVCFactory();
                $fieldsModel = $factory->createModel('Storagefields', 'Administrator');

                if (!$fieldsModel instanceof StoragefieldsModel) {
                    throw new \RuntimeException(Text::_('COM_CONTENTBUILDERNG_STORAGEFIELDS_MODEL_NOT_FOUND'));
                }

                // IMPORTANT : fournir le form id au ListModel
                $fieldsModel->setStorageId($storageId);

                // Charge les items
                $this->fields     = $fieldsModel->getItems();
                $this->storageFieldNames = $fieldsModel->getFieldNames();
                $this->pagination = $fieldsModel->getPagination();
                $this->state      = $fieldsModel->getState();
                $this->ordering   = ($this->state && $this->state->get('list.ordering') === 'ordering');
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage(
                Text::sprintf('COM_CONTENTBUILDERNG_STORAGE_LOAD_FIELDS_ERROR', $e->getMessage()),
                'warning'
            );
        }

        // Onglet "Data" : lecture paginée des enregistrements de la table
        // physique, dès qu'elle existe et est lisible (interne ou externe).
        if ($storageId > 0 && $this->storageTableExists === true) {
            try {
                $dataModel = $this->getComponent()->getMVCFactory()
                    ->createModel('Storagedata', 'Administrator', ['ignore_request' => true]);

                if ($dataModel instanceof \CB\Component\Contentbuilderng\Administrator\Model\StoragedataModel) {
                    $dataModel->setStorageId($storageId);

                    if ($dataModel->isReadable()) {
                        $this->showDataTab = true;
                        $this->recordItems = (array) $dataModel->getItems();
                        $this->recordColumnLabels = $dataModel->getColumnLabels();
                        $this->recordsHavePrimaryKey = $dataModel->hasPrimaryKey();
                        $this->recordPagination = $dataModel->getPagination();
                        $this->recordListLimit = (int) $dataModel->getState('list.limit', 20);
                        $this->recordListStart = (int) $dataModel->getState('list.start', 0);
                        $this->recordSearch = (string) $dataModel->getState('data.search', '');
                    }
                }
            } catch (\Throwable $e) {
                $app->enqueueMessage(
                    Text::sprintf('COM_CONTENTBUILDERNG_STORAGE_LOAD_FIELDS_ERROR', $e->getMessage()),
                    'warning'
                );
            }
        }

        // Onglet Index : uniquement pour un storage interne (bytable=0),
        // seul cas où un ALTER TABLE sur la table physique est sûr.
        if (!$isBytableStorage && $storageId > 0) {
            try {
                /** @var \CB\Component\Contentbuilderng\Administrator\Model\StorageModel $storageModel */
                $storageModel = $this->getModel();
                $this->indexes = $storageModel->getPhysicalIndexes($storageId);

                $indexedColumns = [];
                foreach ($this->indexes as $index) {
                    if (count($index['columns']) === 1) {
                        $indexedColumns[strtolower($index['columns'][0])] = true;
                    }
                }

                $physicalColumns = $this->getDatabase()->getTableColumns($this->dataTableName, true);
                foreach (array_keys((array) $physicalColumns) as $columnName) {
                    $columnName = (string) $columnName;
                    if (
                        $columnName === ''
                        || strtolower($columnName) === 'id'
                        || isset($indexedColumns[strtolower($columnName)])
                    ) {
                        continue;
                    }
                    $this->indexableColumns[] = $columnName;
                }
            } catch (\Throwable $e) {
                $app->enqueueMessage(
                    Text::sprintf('COM_CONTENTBUILDERNG_STORAGE_LOAD_INDEXES_ERROR', $e->getMessage()),
                    'warning'
                );
            }
        }

        // Formulaires construits sur ce storage (type=com_contentbuilderng,
        // reference_id=storage.id), pour le lien "Formulaires liés" de
        // l'onglet Stockage.
        if ($storageId > 0) {
            try {
                $db = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName(['id', 'name', 'title']))
                    ->from($db->quoteName('#__contentbuilderng_forms'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('com_contentbuilderng'))
                    ->where($db->quoteName('reference_id') . ' = ' . $storageId)
                    ->order($db->quoteName('title'));
                $db->setQuery($query);
                $this->usingForms = $db->loadObjectList() ?: [];
            } catch (\Throwable $e) {
                Logger::exception($e);
            }
        }

        $isNew = ((int) ($this->item->id ?? 0) < 1);
        $text  = $isNew ? Text::_('COM_CONTENTBUILDERNG_NEW') : Text::_('COM_CONTENTBUILDERNG_EDIT');
        $storageLabel = trim((string) ($this->item->title ?? ''));
        if ($storageLabel === '') {
            $storageLabel = trim((string) ($this->item->name ?? ''));
        }
        if ($storageLabel === '') {
            $storageLabel = $isNew ? Text::_('COM_CONTENTBUILDERNG_STORAGES') : ('#' . $storageId);
        }

        $isFromWizard = $input->getBool('wizard', false);
        $breadcrumbMiddle = $isFromWizard
            ? '<a href="' . htmlspecialchars(
                Route::_('index.php?option=com_contentbuilderng&view=storagewizard', false),
                ENT_QUOTES,
                'UTF-8'
            ) . '">'
                . Text::_('COM_CONTENTBUILDERNG_WIZARD_TITLE')
                . ' <span class="fa-solid fa-wand-magic-sparkles mx-2" aria-hidden="true"></span></a>'
            : Text::_('COM_CONTENTBUILDERNG_STORAGES')
                . ' <span class="fa-solid fa-database mx-2" aria-hidden="true"></span>';

        ToolbarHelper::title(
            Text::_('COM_CONTENTBUILDERNG') . ' › ' . $breadcrumbMiddle . ' › ' . $storageLabel
            . ' <small><small>[ ' . $text . ' ]</small></small>',
            'logo_left'
        );

        // Le retour au fil de l'assistant (bouton "Fermer"/"Enregistrer",
        // géré nativement par FormController::cancel()/save() via `return`)
        // doit continuer sur l'assistant plutôt que sur la liste Storages.
        $this->wizardReturnUrl = $isFromWizard
            ? base64_encode('index.php?option=com_contentbuilderng&view=storagewizard')
            : '';

        $saveButtons = [
            ['apply', 'storage.apply', 'JTOOLBAR_APPLY', 'COM_CONTENTBUILDERNG_STORAGE_APPLY_TIP'],
            ['save', 'storage.save', 'JTOOLBAR_SAVE', 'COM_CONTENTBUILDERNG_STORAGE_SAVE_TIP'],
        ];

        if (!$isFromWizard) {
            $saveButtons[] = [
                'save2new',
                'storage.save2new',
                'JTOOLBAR_SAVE_AND_NEW',
                'COM_CONTENTBUILDERNG_STORAGE_SAVE_AND_NEW_TIP',
            ];
        }

        $toolbar = $this->getDocument()->getToolbar('toolbar');

        $toolbar->dropdownButton('save-group')->configure(
            function ($childBar) use ($saveButtons): void {
                foreach ($saveButtons as $button) {
                    $childBar->{$button[0]}($button[1])
                        ->text($button[2])
                        ->attributes(['title' => Text::_($button[3])]);
                }
            }
        );
        $dropdown = $toolbar->dropdownButton('storage-status-group');
        $dropdown->text(Text::_('COM_CONTENTBUILDERNG_TOOLBAR_ACTIONS'));
        $dropdown->toggleSplit(false);
        $dropdown->icon('fa-solid fa-ellipsis');
        $dropdown->buttonClass('btn btn-action');
        $dropdown->listCheck(true);
        $dropdown->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_TOOLBAR_ACTIONS_TIP')]);

        $childToolbar = $dropdown->getChildToolbar();
        $childToolbar->publish('storage.publish')
            ->icon('fa-solid fa-check text-success')
            ->listCheck(true)
            ->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_PUBLISH_ELEMENTS_TIP')]);
        $childToolbar->unpublish('storage.unpublish')
            ->icon('fa-solid fa-circle-xmark text-danger')
            ->listCheck(true)
            ->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_UNPUBLISH_ELEMENTS_TIP')]);
        if ((int) ($this->item->bytable ?? 0) !== 2) {
            $childToolbar->delete('storage.listDelete', 'COM_CONTENTBUILDERNG_DELETE_FIELDS')
                ->message('COM_CONTENTBUILDERNG_DELETE_FIELDS_CONFIRM')
                ->listCheck(true)
                ->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_DELETE_FIELDS_TIP')]);
        }

        $id = (int) ($this->item->id ?? 0);
        $isExternalTable = ((int) ($this->item->bytable ?? 0) > 0);

        if ($id > 0) {
            $previewUntil = time() + 600;
            $previewActorId = (int) ($identity->id ?? 0);
            $previewActorName = trim((string) ($identity->name ?? ''));
            if ($previewActorName === '') {
                $previewActorName = trim((string) ($identity->username ?? ''));
            }
            if ($previewActorName === '') {
                $previewActorName = 'administrator';
            }
            $previewUserId = (int) ($identity->id ?? 0);
            $previewPayload = PreviewLinkHelper::buildPayload(
                'storage:' . $id,
                $previewUntil,
                $previewActorId,
                $previewActorName,
                $previewUserId
            );
            $previewSig = hash_hmac('sha256', $previewPayload, (string) $app->get('secret'));
            $previewSignedQuery = PreviewLinkHelper::buildQuery(
                $previewUntil,
                $previewActorId,
                $previewActorName,
                $previewUserId,
                $previewSig
            );
            $previewUrl = Route::link(
                'site',
                'index.php?option=com_contentbuilderng&view=list&storage_id=' . $id . $previewSignedQuery,
                false,
                Route::TLS_IGNORE,
                true
            );

            // Onglet "Data" : mêmes ACL et philosophie que la prévisualisation.
            // L'affichage lit la table physique en direct ; l'ajout/édition
            // ouvrent l'éditeur front-end signé ; la suppression passe par le
            // pipeline front-end (task=list.delete), qui applique le vrai ACL _fe.
            if ($this->showDataTab) {
                $this->recordEditBaseUrl = Route::link(
                    'site',
                    'index.php?option=com_contentbuilderng&view=edit&storage_id=' . $id . $previewSignedQuery,
                    false,
                    Route::TLS_IGNORE,
                    true
                );
                $this->recordDeleteFormAction = Route::link(
                    'site',
                    'index.php?option=com_contentbuilderng',
                    false,
                    Route::TLS_IGNORE,
                    true
                );
                $this->recordDeleteHiddenFields = PreviewLinkHelper::buildHiddenFields(
                    $previewUntil,
                    $previewActorId,
                    $previewActorName,
                    $previewUserId,
                    $previewSig
                );
            }

            $toolbar->link(Text::_('COM_CONTENTBUILDERNG_PREVIEW'), $previewUrl)
                ->icon('icon-eye')
                ->target('_blank')
                ->attributes([
                    'title' => Text::_('COM_CONTENTBUILDERNG_PREVIEW_TIP'),
                    'data-cb-open-preview' => 'true',
                ]);

            if (!$isExternalTable) {
            // Regroupe "Synchroniser la table" et "Mettre à jour depuis un
            // fichier" (panneau CSV/XLS existant sur l'onglet Stockage de
            // données) sous un seul bouton "Mise à jour", plutôt que deux
            // entrées séparées dans la barre d'outils.
                $updateDropdown = $toolbar->dropdownButton('storage-update-group');
                $updateDropdown->text('COM_CONTENTBUILDERNG_STORAGE_UPDATE_GROUP');
                $updateDropdown->toggleSplit(false);
                $updateDropdown->icon('fa-solid fa-sync');
                $updateDropdown->buttonClass('btn btn-action');
                $updateDropdown->listCheck(false);
                $updateDropdown->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_GROUP_TIP')]);

                $updateChildToolbar = $updateDropdown->getChildToolbar();
                $updateChildToolbar->standardButton('datatable.sync')
                ->task('datatable.sync')
                ->text('COM_CONTENTBUILDERNG_DATATABLE_SYNC')
                ->icon('fa-solid fa-sync')
                ->listCheck(false)
                ->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_DATATABLE_SYNC_TIP')]);

                $csvUpdateUrl = Route::_(
                    'index.php?option=com_contentbuilderng&view=storage&layout=edit&id=' . $id
                    . '&tabStartOffset=tab1&csv_import=1#csvUploadHead',
                    false
                );
                $updateChildToolbar->link(Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV'), $csvUpdateUrl)
                ->icon('fa-solid fa-file-excel')
                ->attributes([
                    'title' => Text::_('COM_CONTENTBUILDERNG_STORAGE_CSV_TOGGLE_TOOLTIP'),
                ]);
            }
        }

        $cancelTipKey = $isNew
            ? 'COM_CONTENTBUILDERNG_STORAGE_CANCEL_TIP'
            : 'COM_CONTENTBUILDERNG_STORAGE_CLOSE_TIP';
        $toolbar->cancel('storage.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE')
            ->attributes([
                'title' => Text::_($cancelTipKey),
            ]);
        ToolbarHelper::help(
            'COM_CONTENTBUILDERNG_HELP_STORAGES_TITLE',
            false,
            Uri::base() . 'index.php?option=com_contentbuilderng&view=storage&layout=help&tmpl=component'
        );

        parent::display($tpl);
    }

    private function getStorageRecordsCount(object $item): ?int
    {
        if ($this->storageTableExists !== true || $this->storageTableLookupName === '') {
            return null;
        }

        try {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select('COUNT(1)')
                ->from($db->quoteName($this->storageTableLookupName));

            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function loadStorageTableStatus(object $item): void
    {
        $name = trim((string) ($item->name ?? ''));

        $this->storageTableExists = null;
        $this->storageTableLookupName = '';
        $this->storageTableErrorMessage = '';

        if ($name === '') {
            return;
        }

        $isExternalTable = ((int) ($item->bytable ?? 0) > 0);
        $lookupName = $isExternalTable ? $name : ('#__' . $name);
        $this->storageTableLookupName = $lookupName;

        try {
            $db = $this->getDatabase();
            $tableList = array_map('strtolower', (array) $db->getTableList());
            $resolvedName = strtolower($db->replacePrefix($lookupName));

            $this->storageTableExists = in_array($resolvedName, $tableList, true);

            if ($this->storageTableExists === false) {
                $this->storageTableErrorMessage = Text::sprintf(
                    'COM_CONTENTBUILDERNG_STORAGE_TABLE_DOES_NOT_EXIST',
                    $db->replacePrefix($lookupName)
                );
            }
        } catch (\Throwable $e) {
            $this->storageTableExists = null;
            $this->storageTableErrorMessage = $e->getMessage();
        }
    }
}
