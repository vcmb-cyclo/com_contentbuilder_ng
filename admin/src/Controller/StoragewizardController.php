<?php

/**
 * @package     ContentBuilderNG
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace CB\Component\Contentbuilderng\Administrator\Controller;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use CB\Component\Contentbuilderng\Administrator\Extension\ContentbuilderngComponent;
use CB\Component\Contentbuilderng\Administrator\Helper\Logger;
use CB\Component\Contentbuilderng\Administrator\Model\StorageModel;
use CB\Component\Contentbuilderng\Administrator\Service\DirectStorageFormProvisioningService;
use CB\Component\Contentbuilderng\Administrator\Service\StorageWizardService;
use CB\Component\Contentbuilderng\Administrator\Service\ExternalTableService;

use CB\Component\Contentbuilderng\Administrator\Controller\Traits\ComponentAccessTrait;
use CB\Component\Contentbuilderng\Administrator\Controller\Traits\SafeErrorMessageTrait;
/**
 * "Assistant" wizard: guides the admin from a blank Storage through fields
 * (CSV import or manual, reusing the existing Storage edit screen), a
 * consultation form, and a site menu item — all in one guided flow.
 *
 * The wizard is a thin orchestrator: it owns only the state (session) and
 * the storage-creation / form-creation / menu-creation actions that don't
 * already exist elsewhere. Field management (CSV import, manual add,
 * reorder) is not reimplemented — the wizard links out to the existing,
 * fully-functional Storage edit screen for that step.
 */
final class StoragewizardController extends BaseController
{
    use ComponentAccessTrait;
    use SafeErrorMessageTrait;

    protected $default_view = 'storagewizard';

    private function getApp(): AdministratorApplication
    {
        $app = $this->app;

        if (!$app instanceof AdministratorApplication) {
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

    private function getWizardService(): StorageWizardService
    {
        return new StorageWizardService($this->getApp());
    }

    private function requireManagePermission(): void
    {
        if (!$this->getApp()->getIdentity()->authorise('core.manage', 'com_contentbuilderng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    private function redirectToWizard(string $msg = '', string $type = 'message'): void
    {
        $link = Route::_('index.php?option=com_contentbuilderng&view=storagewizard', false);
        $this->setRedirect($link, $msg, $type);
    }

    /**
     * Task: storagewizard.back — revient à l'étape précédente sans perdre
     * les données déjà collectées (storage_id/form_id restent en état).
     */
    public function back(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $currentIndex = $wizardService->stepIndex((string) ($state['current_step'] ?? StorageWizardService::STEP_STORAGE));

        // Ne jamais redescendre jusqu'à l'étape "storage" : saveStorage() crée
        // toujours un nouveau storage (pas d'édition), y retourner puis
        // cliquer "Suivant" créerait un doublon. L'étape "fields" est le
        // plancher pour "Précédent".
        if ($currentIndex > 1) {
            $state = $wizardService->advanceTo($state, StorageWizardService::STEPS[$currentIndex - 1]);
            $wizardService->saveState($state);
        }

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.start — (re)starts the wizard from a clean state.
     */
    public function start(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $this->getWizardService()->reset();

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.begin — starts the guided setup from its welcome page.
     */
    public function begin(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $state['started'] = true;
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.chooseStorageMode — sous-étape 1 de STEP_STORAGE :
     * reprendre un storage existant, ou en créer un nouveau.
     */
    public function chooseStorageMode(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $mode = trim((string) $this->input->post->getCmd('storage_mode', ''));

        if (!in_array($mode, [StorageWizardService::MODE_RESUME, StorageWizardService::MODE_NEW], true)) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_STORAGE_MODE_REQUIRED'), 'error');

            return;
        }

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $state['storage_mode'] = $mode;
        $state['storage_substep'] = $mode === StorageWizardService::MODE_RESUME
            ? StorageWizardService::SUBSTEP_PICK_EXISTING
            : StorageWizardService::SUBSTEP_CREATION_MODE;
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.selectExistingStorage — sous-étape "reprise" :
     * choisit un storage déjà créé et saute directement à l'étape "fields",
     * sans repasser par la création (nom/titre/table).
     */
    public function selectExistingStorage(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $storageId = (int) $this->input->post->getInt('existing_storage_id', 0);

        if ($storageId < 1) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_PICK_EXISTING_STORAGE_REQUIRED'), 'error');

            return;
        }

        $db = $this->getComponent()->getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__contentbuilderng_storages'))
            ->where($db->quoteName('id') . ' = :storageId')
            ->bind(':storageId', $storageId, ParameterType::INTEGER);
        $db->setQuery($query);

        if ((int) $db->loadResult() < 1) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_RESUME_INVALID_STORAGE'), 'error');

            return;
        }

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $state['storage_id'] = $storageId;
        $state = $wizardService->advanceTo($state, StorageWizardService::STEP_FIELDS);
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.chooseCreationMode — choisit la source du nouveau
     * storage : table interne gérée par ContentBuilder NG ou table existante.
     */
    public function chooseCreationMode(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $source = trim((string) $this->input->post->getCmd('storage_source', ''));

        if (
            !in_array($source, [
            StorageWizardService::STORAGE_SOURCE_INTERNAL,
            StorageWizardService::CREATION_MODE_EXISTING_TABLE,
            ], true)
        ) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_REQUIRED'), 'error');

            return;
        }

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $state['storage_source'] = $source;
        $state['creation_mode'] = $source === StorageWizardService::CREATION_MODE_EXISTING_TABLE
            ? StorageWizardService::CREATION_MODE_EXISTING_TABLE
            : '';
        $state['storage_substep'] = StorageWizardService::SUBSTEP_NAME;
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.chooseInitializationMode — choisit comment initialiser
     * les champs d'une nouvelle table interne : manuellement ou par fichier.
     */
    public function chooseInitializationMode(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $mode = trim((string) $this->input->post->getCmd('creation_mode', ''));

        if (
            !in_array($mode, [
            StorageWizardService::CREATION_MODE_MANUAL,
            StorageWizardService::CREATION_MODE_FILE,
            ], true)
        ) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_INITIALIZATION_MODE_REQUIRED'), 'error');

            return;
        }

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $state['creation_mode'] = $mode;
        $pendingStorageInput = $state['pending_storage_input'] ?? [];
        $wizardService->saveState($state);

        if (!is_array($pendingStorageInput) || trim((string) ($pendingStorageInput['name'] ?? '')) === '') {
            $state['storage_substep'] = StorageWizardService::SUBSTEP_NAME;
            $wizardService->saveState($state);
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_STORAGE_FIELDS_REQUIRED'), 'error');

            return;
        }

        $this->createStorage(
            trim((string) $pendingStorageInput['name']),
            trim((string) ($pendingStorageInput['title'] ?? '')),
            trim((string) ($pendingStorageInput['bytable'] ?? ''))
        );
    }

    /**
     * Task: storagewizard.saveStorageDetails — stores the name and title
     * before the admin chooses how to create the columns of an internal table.
     */
    public function saveStorageDetails(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();

        if (($state['storage_source'] ?? '') !== StorageWizardService::STORAGE_SOURCE_INTERNAL) {
            $this->redirectToWizard();

            return;
        }

        $input = $this->prepareStorageInput(
            $wizardService,
            trim((string) $this->input->post->getString('name', '')),
            trim((string) $this->input->post->getString('title', '')),
            ''
        );

        if ($input === null) {
            return;
        }

        $state['pending_storage_input'] = $input;
        $state['storage_substep'] = StorageWizardService::SUBSTEP_INITIALIZATION_MODE;
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.backSubstep — revient à la sous-étape précédente
     * de STEP_STORAGE.
     */
    public function backSubstep(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $substep = (string) ($state['storage_substep'] ?? StorageWizardService::SUBSTEP_MODE);
        $mode = (string) ($state['storage_mode'] ?? '');

        $state['storage_substep'] = match ($substep) {
            StorageWizardService::SUBSTEP_PICK_EXISTING, StorageWizardService::SUBSTEP_CREATION_MODE => StorageWizardService::SUBSTEP_MODE,
            StorageWizardService::SUBSTEP_INITIALIZATION_MODE => StorageWizardService::SUBSTEP_NAME,
            StorageWizardService::SUBSTEP_NAME => $mode === StorageWizardService::MODE_NEW
                ? StorageWizardService::SUBSTEP_CREATION_MODE
                : StorageWizardService::SUBSTEP_MODE,
            default => StorageWizardService::SUBSTEP_MODE,
        };
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.saveStorage — saves the name and title (optionally
     * imposed by an existing table), creates the storage, then advances to
     * the "fields" step.
     */
    public function saveStorage(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $input = $this->prepareStorageInput(
            $wizardService,
            trim((string) $this->input->post->getString('name', '')),
            trim((string) $this->input->post->getString('title', '')),
            trim((string) $this->input->post->getString('bytable', ''))
        );

        if ($input === null) {
            return;
        }

        $this->createStorage($input['name'], $input['title'], $input['bytable']);
    }

    /**
     * Validates and normalizes the storage details collected by the wizard.
     *
     * @return array{name:string,title:string,bytable:string}|null
     */
    private function prepareStorageInput(
        StorageWizardService $wizardService,
        string $name,
        string $title,
        string $bytable
    ): ?array {
        $db = $this->getComponent()->getContainer()->get(DatabaseInterface::class);

        if (
            $bytable !== ''
            && !$this->getComponent()->getContainer()->get(ExternalTableService::class)->isSelectable($bytable)
        ) {
            $bytable = '';
        }

        // Comme sur l'écran Storage classique : le nom est facultatif quand
        // une table existante est choisie, StorageModel::prepareTable() le
        // reprend depuis "bytable".
        if ($bytable !== '' && $name === '') {
            $name = $bytable;
        }

        if ($name === '') {
            $this->rememberStorageInput($wizardService, $name, $title, $bytable);
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_STORAGE_FIELDS_REQUIRED'), 'error');

            return null;
        }

        // Le titre est facultatif : à défaut, on reprend le nom.
        if ($title === '') {
            $title = $name;
        }

        // MySQL/MariaDB limite le nom de table physique à 64 caractères.
        if ($bytable === '') {
            $maxNameLength = 64 - strlen($db->getPrefix());

            if (strlen(StorageModel::normalizeStorageName($name)) > $maxNameLength) {
                $this->rememberStorageInput($wizardService, $name, $title, $bytable);
                $this->redirectToWizard(
                    Text::sprintf('COM_CONTENTBUILDERNG_STORAGE_NAME_TOO_LONG', $maxNameLength),
                    'error'
                );

                return null;
            }
        }

        return ['name' => $name, 'title' => $title, 'bytable' => $bytable];
    }

    private function createStorage(string $name, string $title, string $bytable): void
    {
        $wizardService = $this->getWizardService();
        $wizardState = $wizardService->getState();
        $creationMode = (string) ($wizardState['creation_mode'] ?? '');
        $db = $this->getComponent()->getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__contentbuilderng_storages'))
            ->where($db->quoteName('name') . ' = :name')
            ->bind(':name', $name, ParameterType::STRING);
        $db->setQuery($query);

        $duplicateStorageTitle = $db->loadResult();

        if ($duplicateStorageTitle !== null) {
            $duplicateStorageTitle = trim((string) $duplicateStorageTitle);
            $this->rememberStorageInput($wizardService, $name, $title, $bytable);
            $this->redirectToWizard(
                Text::sprintf(
                    'COM_CONTENTBUILDERNG_WIZARD_STORAGE_NAME_DUPLICATE',
                    $name,
                    $duplicateStorageTitle !== '' ? $duplicateStorageTitle : $name
                ),
                'error'
            );

            return;
        }

        /** @var StorageModel|null $model */
        $model = $this->getModel('Storage', 'Administrator', ['ignore_request' => true]);

        if (!$model) {
            throw new \RuntimeException('StorageModel introuvable');
        }

        $data = [
            'id' => 0,
            'name' => $name,
            'title' => $title,
            'bytable' => $bytable,
            'published' => 1,
            'ordering' => 0,
        ];

        if (!$model->save($data)) {
            $error = (string) ($model->getError() ?: Text::_('COM_CONTENTBUILDERNG_ERROR'));

            if (str_contains($error, 'Duplicate entry')) {
                $error = Text::sprintf('COM_CONTENTBUILDERNG_WIZARD_STORAGE_NAME_DUPLICATE', $name, $name);
            }

            $this->rememberStorageInput($wizardService, $name, $title, $bytable);
            $this->redirectToWizard($error, 'error');

            return;
        }

        $storageId = (int) $model->getState($model->getName() . '.id', 0);

        if (!$storageId) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_ERROR'), 'error');

            return;
        }

        $model->ensureDataTable($storageId, true, null);

        $state = $wizardService->getState();
        $state['storage_id'] = $storageId;
        unset($state['pending_storage_input']);
        $state = $wizardService->advanceTo($state, StorageWizardService::STEP_FIELDS);
        $wizardService->saveState($state);

        if ($creationMode === StorageWizardService::CREATION_MODE_FILE) {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_contentbuilderng&view=storage&layout=edit&id=' . $storageId
                    . '&wizard=1&tabStartOffset=tab1&csv_import=1',
                    false
                ),
                Text::_('COM_CONTENTBUILDERNG_WIZARD_STORAGE_CREATED')
            );

            return;
        }

        $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_STORAGE_CREATED'));
    }

    /**
     * Conserve le nom/titre saisis en session pour repeupler le formulaire
     * après un redirect d'erreur (name requis, doublon, etc.), au lieu de
     * les effacer.
     */
    private function rememberStorageInput(StorageWizardService $wizardService, string $name, string $title, string $bytable = ''): void
    {
        $state = $wizardService->getState();
        $state['pending_storage_input'] = ['name' => $name, 'title' => $title, 'bytable' => $bytable];
        $wizardService->saveState($state);
    }

    /**
     * Task: storagewizard.confirmFields — étape 2, l'admin a géré les champs
     * sur l'écran Storage (CSV ou manuel) puis revient confirmer.
     */
    public function confirmFields(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $storageId = (int) ($state['storage_id'] ?? 0);

        if (!$storageId) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_NO_STORAGE'), 'error');

            return;
        }

        $db = $this->getComponent()->getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__contentbuilderng_storage_fields'))
            ->where($db->quoteName('storage_id') . ' = ' . (int) $storageId);
        $db->setQuery($query);
        $fieldCount = (int) $db->loadResult();

        if ($fieldCount < 1) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_NO_FIELDS'), 'error');

            return;
        }

        $state = $wizardService->advanceTo($state, StorageWizardService::STEP_FORM);
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.createForm — étape 3, provisionne (ou réutilise)
     * le #__contentbuilderng_forms du storage via le même service que le
     * mode "storage direct" côté site.
     */
    public function createForm(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $storageId = (int) ($state['storage_id'] ?? 0);

        if (!$storageId) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_NO_STORAGE'), 'error');

            return;
        }

        try {
            $formId = $this->getComponent()->getContainer()
                ->get(DirectStorageFormProvisioningService::class)
                ->resolveOrCreateFormId($storageId, 'thoth', true);
        } catch (\Throwable $e) {
            Logger::exception($e);
            $this->redirectToWizard($this->safeErrorMessage($e), 'error');

            return;
        }

        if (!$formId) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_ERROR'), 'error');

            return;
        }

        // On reste sur l'étape "form" (pas d'avance automatique) : l'utilisateur
        // peut maintenant ouvrir l'écran Formulaire pour le personnaliser avant
        // de passer à l'étape menu, comme pour l'étape "fields"/Storage.
        $state['form_id'] = $formId;
        $wizardService->saveState($state);

        $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_FORM_CREATED'));
    }

    /**
     * Task: storagewizard.confirmForm — étape 3 → 4, valide qu'un formulaire
     * a bien été créé et passe à l'étape menu.
     */
    public function confirmForm(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();

        if ((int) ($state['form_id'] ?? 0) < 1) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_NO_FORM'), 'error');

            return;
        }

        $state = $wizardService->advanceTo($state, StorageWizardService::STEP_MENU);
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.createMenu — étape 4, crée un item de menu de site
     * pointant vers la liste du storage.
     */
    public function createMenu(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->getState();
        $storageId = (int) ($state['storage_id'] ?? 0);

        if (!$storageId) {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_NO_STORAGE'), 'error');

            return;
        }

        $menutype = trim((string) $this->input->post->getCmd('menutype', ''));
        $title = trim((string) $this->input->post->getString('menu_title', ''));
        $parentId = (int) $this->input->post->getInt('parent_id', 1);

        if ($menutype === '' || $title === '') {
            $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_MENU_FIELDS_REQUIRED'), 'error');

            return;
        }

        try {
            $menuItemId = $this->createMenuItem($storageId, $menutype, $title, $parentId);
        } catch (\Throwable $e) {
            Logger::exception($e);
            $this->redirectToWizard($this->safeErrorMessage($e), 'error');

            return;
        }

        $state['menu_item_id'] = $menuItemId;
        $state = $wizardService->advanceTo($state, StorageWizardService::STEP_DONE);
        $wizardService->saveState($state);

        $this->redirectToWizard(Text::_('COM_CONTENTBUILDERNG_WIZARD_MENU_CREATED'));
    }

    /**
     * Task: storagewizard.skipMenu — passe directement à l'étape finale
     * sans créer d'item de menu.
     */
    public function skipMenu(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $wizardService = $this->getWizardService();
        $state = $wizardService->advanceTo($wizardService->getState(), StorageWizardService::STEP_DONE);
        $wizardService->saveState($state);

        $this->redirectToWizard();
    }

    /**
     * Task: storagewizard.finish — termine l'assistant et revient à la liste
     * des storages.
     */
    public function finish(): void
    {
        $this->checkToken();
        $this->requireManagePermission();

        $this->getWizardService()->reset();

        $this->setRedirect(Route::_('index.php?option=com_contentbuilderng&view=storages', false));
    }

    /**
     * Réutilise un item de menu existant pointant déjà vers ce storage (même
     * lien exact) dans le menutype choisi, plutôt que d'en créer un doublon
     * à chaque nouveau passage dans l'assistant pour le même storage.
     */
    private function findExistingMenuItemId(DatabaseInterface $db, string $menutype, string $link): int
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('menutype') . ' = :menutype')
            ->where($db->quoteName('link') . ' = :link')
            ->where($db->quoteName('client_id') . ' = 0')
            ->bind(':menutype', $menutype)
            ->bind(':link', $link);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    /**
     * Résout le menutype effectif d'un item de menu parent (ignore la valeur
     * choisie séparément dans le select "Type de menu" si elle diverge :
     * un item de menu appartient physiquement à l'arbre imbriqué de SON
     * menutype, on ne peut pas le rattacher à un autre).
     */
    private function resolveParentMenutype(DatabaseInterface $db, int $parentId, string $fallbackMenutype): string
    {
        if ($parentId <= 1) {
            return $fallbackMenutype;
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('menutype'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('id') . ' = :parentId')
            ->where($db->quoteName('client_id') . ' = 0')
            ->bind(':parentId', $parentId, ParameterType::INTEGER);
        $db->setQuery($query);
        $actual = (string) $db->loadResult();

        return $actual !== '' ? $actual : $fallbackMenutype;
    }

    private function createMenuItem(int $storageId, string $menutype, string $title, int $parentId = 1): int
    {
        $db = $this->getComponent()->getContainer()->get(DatabaseInterface::class);
        $link = 'index.php?option=com_contentbuilderng&task=list.display&storage_id=' . $storageId;
        $menutype = $this->resolveParentMenutype($db, $parentId, $menutype);

        $existingId = $this->findExistingMenuItemId($db, $menutype, $link);

        if ($existingId > 0) {
            return $existingId;
        }

        $menusComponent = $this->getApp()->bootComponent('com_menus');
        $table = $menusComponent->getMVCFactory()->createTable('Menu', 'Administrator', ['dbo' => $db]);

        $alias = OutputFilter::stringUrlSafe($title);
        if (trim($alias) === '') {
            $alias = 'item-' . time();
        }

        $componentId = (int) ComponentHelper::getComponent('com_contentbuilderng')->id;

        $data = [
            'id' => 0,
            'menutype' => $menutype,
            'title' => $title,
            'alias' => $alias,
            'note' => '',
            'link' => $link,
            'type' => 'component',
            'published' => 1,
            'component_id' => $componentId,
            'checked_out' => 0,
            'checked_out_time' => null,
            'browserNav' => 0,
            'access' => 1,
            'img' => '',
            'template_style_id' => 0,
            'params' => '{}',
            'home' => 0,
            'language' => '*',
            'client_id' => 0,
            'publish_up' => null,
            'publish_down' => null,
        ];

        $table->setLocation($parentId > 0 ? $parentId : 1, 'last-child');

        if (!$table->bind($data) || !$table->check() || !$table->store()) {
            throw new \RuntimeException($table->getError() ?: Text::_('COM_CONTENTBUILDERNG_ERROR'));
        }

        $table->rebuildPath((int) $table->id);

        return (int) $table->id;
    }
}
