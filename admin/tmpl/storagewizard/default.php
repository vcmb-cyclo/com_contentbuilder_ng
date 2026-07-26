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

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use CB\Component\Contentbuilderng\Administrator\Service\StorageWizardService;

$currentStep = (string) ($this->wizardState['current_step'] ?? StorageWizardService::STEP_STORAGE);
$currentIndex = array_search($currentStep, $this->steps, true);
$currentIndex = $currentIndex === false ? 0 : (int) $currentIndex;

$stepLabels = [
    StorageWizardService::STEP_STORAGE => Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_STORAGE'),
    StorageWizardService::STEP_FIELDS => Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_FIELDS'),
    StorageWizardService::STEP_FORM => Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_FORM'),
    StorageWizardService::STEP_MENU => Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_MENU'),
    StorageWizardService::STEP_DONE => Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_DONE'),
];
$pendingStorageInput = $this->wizardState['pending_storage_input'] ?? [];
$stepIcons = [
    StorageWizardService::STEP_STORAGE => 'fa-database',
    StorageWizardService::STEP_FIELDS => 'fa-table-list',
    StorageWizardService::STEP_FORM => 'fa-file-lines',
    StorageWizardService::STEP_MENU => 'fa-bars',
    StorageWizardService::STEP_DONE => 'fa-flag-checkered',
];
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="cb-wizard mt-3">
        <ul class="cb-wizard-steps">
            <?php foreach ($this->steps as $index => $stepId) :
                $stateClass = '';
                if ($index < $currentIndex) {
                    $stateClass = ' is-done';
                } elseif ($index === $currentIndex) {
                    $stateClass = ' is-active';
                }
            ?>
                <li class="<?php echo $stateClass; ?>">
                    <?php if ($index < $currentIndex) : ?>
                        <span class="fa-solid fa-check" aria-hidden="true"></span>
                    <?php else : ?>
                        <span class="fa-solid <?php echo $stepIcons[$stepId] ?? 'fa-circle'; ?>" aria-hidden="true"></span>
                    <?php endif; ?>
                    <span class="cb-wizard-step-num"><?php echo (int) $index + 1; ?>.</span>
                    <?php echo htmlspecialchars($stepLabels[$stepId] ?? $stepId, ENT_QUOTES, 'UTF-8'); ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="card">
            <div class="card-body">
                <?php if ($currentStep === StorageWizardService::STEP_STORAGE) :
                    $storageSubstep = (string) ($this->wizardState['storage_substep'] ?? StorageWizardService::SUBSTEP_MODE);
                    $storageMode = (string) ($this->wizardState['storage_mode'] ?? '');
                    $creationMode = (string) ($this->wizardState['creation_mode'] ?? '');
                ?>

                    <?php if ($storageSubstep === StorageWizardService::SUBSTEP_PICK_EXISTING) : ?>
                        <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_PICK_EXISTING_STORAGE_TITLE'); ?></h2>
                        <p class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_PICK_EXISTING_STORAGE_DESC'); ?></p>
                        <?php if (empty($this->existingStorages)) : ?>
                            <div class="alert alert-warning"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_PICK_EXISTING_STORAGE_NONE'); ?></div>
                        <?php else : ?>
                            <div class="mb-3">
                                <label class="form-label" for="cb-wizard-existing-storage"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGES'); ?></label>
                                <select class="form-select" id="cb-wizard-existing-storage" name="existing_storage_id" required>
                                    <option value="">— <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_PICK_EXISTING_STORAGE_PLACEHOLDER'); ?> —</option>
                                    <?php foreach ($this->existingStorages as $existingStorage) : ?>
                                        <option value="<?php echo (int) $existingStorage->id; ?>">
                                            <?php echo htmlspecialchars((string) ($existingStorage->title !== '' ? $existingStorage->title : $existingStorage->name), ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button
                                type="button"
                                class="btn btn-primary"
                                onclick="Joomla.submitbutton('storagewizard.selectExistingStorage')"
                                title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                            >
                                <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT'); ?>
                                <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                            </button>
                        <?php endif; ?>

                    <?php elseif ($storageSubstep === StorageWizardService::SUBSTEP_CREATION_MODE) : ?>
                        <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_TITLE'); ?></h2>
                        <p class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_DESC'); ?></p>
                        <div class="list-group mb-3">
                            <label class="list-group-item d-flex gap-3">
                                <input class="form-check-input flex-shrink-0" type="radio" name="storage_source" value="<?php echo StorageWizardService::STORAGE_SOURCE_INTERNAL; ?>" checked>
                                <span>
                                    <strong class="d-block"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STORAGE_SOURCE_INTERNAL'); ?></strong>
                                    <small class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STORAGE_SOURCE_INTERNAL_DESC'); ?></small>
                                </span>
                            </label>
                            <?php if (!empty($this->tables)) : ?>
                                <label class="list-group-item d-flex gap-3">
                                    <input class="form-check-input flex-shrink-0" type="radio" name="storage_source" value="<?php echo StorageWizardService::CREATION_MODE_EXISTING_TABLE; ?>">
                                    <span>
                                        <strong class="d-block"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_EXISTING_TABLE'); ?></strong>
                                        <small class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_EXISTING_TABLE_DESC'); ?></small>
                                    </span>
                                </label>
                            <?php endif; ?>
                        </div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="Joomla.submitbutton('storagewizard.chooseCreationMode')"
                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                        >
                            <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT'); ?>
                            <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                        </button>

                    <?php elseif ($storageSubstep === StorageWizardService::SUBSTEP_INITIALIZATION_MODE) : ?>
                        <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_INITIALIZATION_MODE_TITLE'); ?></h2>
                        <p class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_INITIALIZATION_MODE_DESC'); ?></p>
                        <div class="list-group mb-3">
                            <label class="list-group-item d-flex gap-3">
                                <input class="form-check-input flex-shrink-0" type="radio" name="creation_mode" value="<?php echo StorageWizardService::CREATION_MODE_MANUAL; ?>" checked>
                                <span>
                                    <strong class="d-block"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_MANUAL'); ?></strong>
                                    <small class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_MANUAL_DESC'); ?></small>
                                </span>
                            </label>
                            <label class="list-group-item d-flex gap-3">
                                <input class="form-check-input flex-shrink-0" type="radio" name="creation_mode" value="<?php echo StorageWizardService::CREATION_MODE_FILE; ?>">
                                <span>
                                    <strong class="d-block"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_FILE'); ?></strong>
                                    <small class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_FILE_DESC'); ?></small>
                                </span>
                            </label>
                        </div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="Joomla.submitbutton('storagewizard.chooseInitializationMode')"
                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                        >
                            <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT'); ?>
                            <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                        </button>

                    <?php elseif ($storageSubstep === StorageWizardService::SUBSTEP_NAME) : ?>
                        <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_STORAGE'); ?></h2>
                        <p class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_STORAGE_DESC'); ?></p>
                        <div class="mb-3">
                            <label class="form-label" for="cb-wizard-title"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_TITLE'); ?></label>
                            <input
                                class="form-control"
                                type="text"
                                id="cb-wizard-title"
                                name="title"
                                required
                                maxlength="255"
                                value="<?php echo htmlspecialchars((string) ($pendingStorageInput['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        </div>
                        <?php if ($creationMode === StorageWizardService::CREATION_MODE_EXISTING_TABLE && !empty($this->tables)) : ?>
                            <div class="mb-3">
                                <label class="form-label" for="cb-wizard-bytable"><b><?php echo Text::_('COM_CONTENTBUILDERNG_CHOOSE_TABLE'); ?></b></label>
                                <select
                                    class="form-select"
                                    id="cb-wizard-bytable"
                                    name="bytable"
                                    required
                                    onchange="
                                        var nameField = document.getElementById('cb-wizard-name');
                                        if (this.value !== '') {
                                            nameField.value = this.value;
                                            nameField.disabled = true;
                                            var selectedOption = this.options[this.selectedIndex];
                                            var selectedMode = selectedOption.dataset.bytableMode;
                                            var joomlaIcon = document.getElementById('cb-wizard-selected-joomla-table-icon');
                                            if (joomlaIcon) {
                                                joomlaIcon.classList.toggle('d-none', selectedOption.dataset.sourceType !== 'joomla');
                                            }
                                            alert(selectedMode === '2'
                                                ? '<?php echo addslashes(Text::_('COM_CONTENTBUILDERNG_READONLY_EXTERNAL_STORAGE_MSG')); ?>'
                                                : '<?php echo addslashes(Text::_('COM_CONTENTBUILDERNG_CUSTOM_STORAGE_MSG')); ?>');
                                        } else {
                                            nameField.disabled = false;
                                            var joomlaIcon = document.getElementById('cb-wizard-selected-joomla-table-icon');
                                            if (joomlaIcon) {
                                                joomlaIcon.classList.add('d-none');
                                            }
                                        }
                                    "
                                >
                                    <option value=""> - <?php echo Text::_('COM_CONTENTBUILDERNG_NONE'); ?> -</option>
                                    <?php foreach ($this->tables as $table) : ?>
                                        <option
                                            value="<?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-bytable-mode="<?php echo (int) ($this->tableModes[$table] ?? 1); ?>"
                                            data-source-type="<?php echo htmlspecialchars((string) ($this->tableSourceTypes[$table] ?? 'external'), ENT_QUOTES, 'UTF-8'); ?>"
                                            <?php echo ($pendingStorageInput['bytable'] ?? '') === $table ? 'selected' : ''; ?>
                                        >
                                            <?php
                                            $sourceLabel = (string) ($this->tableSourceLabels[$table] ?? '');
                                            echo htmlspecialchars(
                                                $table . ($sourceLabel !== '' ? ' (' . $sourceLabel . ')' : ''),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span id="cb-wizard-selected-joomla-table-icon" class="icon-joomla fs-4 ms-2 d-none" aria-label="Joomla"></span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="cb-wizard-name"><?php echo Text::_('COM_CONTENTBUILDERNG_NAME'); ?></label>
                                <input
                                    class="form-control"
                                    type="text"
                                    id="cb-wizard-name"
                                    name="name"
                                    required
                                    maxlength="255"
                                    value="<?php echo htmlspecialchars((string) ($pendingStorageInput['name'] ?? $pendingStorageInput['bytable'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    disabled
                                >
                            </div>
                        <?php else : ?>
                            <?php if ($creationMode === StorageWizardService::CREATION_MODE_FILE) : ?>
                                <p class="alert alert-info">
                                    <span class="fa-solid fa-circle-info me-1" aria-hidden="true"></span>
                                    <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATION_MODE_FILE_HINT'); ?>
                                </p>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label" for="cb-wizard-name"><?php echo Text::_('COM_CONTENTBUILDERNG_NAME'); ?></label>
                                <input
                                    class="form-control"
                                    type="text"
                                    id="cb-wizard-name"
                                    name="name"
                                    required
                                    maxlength="255"
                                    value="<?php echo htmlspecialchars((string) ($pendingStorageInput['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                >
                            </div>
                        <?php endif; ?>
                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="Joomla.submitbutton('storagewizard.saveStorage')"
                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                        >
                            <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT'); ?>
                            <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                        </button>

                    <?php else : ?>
                        <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_STORAGE_MODE_TITLE'); ?></h2>
                        <p class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_STORAGE_MODE_DESC'); ?></p>
                        <div class="list-group mb-3">
                            <label class="list-group-item d-flex gap-3">
                                <input class="form-check-input flex-shrink-0" type="radio" name="storage_mode" value="<?php echo StorageWizardService::MODE_NEW; ?>" checked>
                                <span>
                                    <strong class="d-block"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_MODE_NEW'); ?></strong>
                                    <small class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_MODE_NEW_DESC'); ?></small>
                                </span>
                            </label>
                            <label class="list-group-item d-flex gap-3">
                                <input class="form-check-input flex-shrink-0" type="radio" name="storage_mode" value="<?php echo StorageWizardService::MODE_RESUME; ?>">
                                <span>
                                    <strong class="d-block"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_MODE_RESUME'); ?></strong>
                                    <small class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_MODE_RESUME_DESC'); ?></small>
                                </span>
                            </label>
                        </div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="Joomla.submitbutton('storagewizard.chooseStorageMode')"
                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                        >
                            <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT'); ?>
                            <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>

                    <?php if ($storageSubstep !== StorageWizardService::SUBSTEP_MODE) : ?>
                        <button
                            type="button"
                            class="btn btn-link mt-2 ms-n2"
                            onclick="Joomla.submitbutton('storagewizard.backSubstep')"
                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_PREVIOUS_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                        >
                            <span class="fa-solid fa-arrow-left me-1" aria-hidden="true"></span>
                            <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_PREVIOUS'); ?>
                        </button>
                    <?php endif; ?>

                <?php elseif ($currentStep === StorageWizardService::STEP_FIELDS) : ?>
                    <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_FIELDS'); ?></h2>
                    <p class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_FIELDS_DESC'); ?></p>
                    <?php if ($this->storage) : ?>
                        <p>
                            <strong><?php echo htmlspecialchars((string) $this->storage->title, ENT_QUOTES, 'UTF-8'); ?></strong>
                            &mdash;
                            <?php echo Text::plural('COM_CONTENTBUILDERNG_WIZARD_FIELDS_COUNT', $this->fieldCount); ?>
                        </p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a
                                class="btn btn-outline-primary"
                                href="<?php echo Route::_('index.php?option=com_contentbuilderng&view=storage&layout=edit&id=' . (int) $this->storage->id . '&wizard=1'); ?>"
                            >
                                <span class="fa-solid fa-table-list me-1" aria-hidden="true"></span>
                                <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_OPEN_STORAGE_SCREEN'); ?>
                            </a>
                            <?php if (empty($this->storage->bytable)) : ?>
                                <a
                                    class="btn btn-outline-primary"
                                    href="<?php echo Route::_('index.php?option=com_contentbuilderng&view=storage&layout=edit&id=' . (int) $this->storage->id . '&wizard=1&tabStartOffset=tab1&csv_import=1'); ?>"
                                >
                                    <span class="fa-solid fa-file-excel me-1" aria-hidden="true"></span>
                                    <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="Joomla.submitbutton('storagewizard.confirmFields')"
                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            <?php echo $this->fieldCount < 1 ? 'disabled' : ''; ?>
                        >
                            <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT'); ?>
                            <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                        </button>
                    </div>

                <?php elseif ($currentStep === StorageWizardService::STEP_FORM) : ?>
                    <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_FORM'); ?></h2>
                    <p class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_FORM_DESC'); ?></p>
                    <?php if (!$this->form) : ?>
                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="Joomla.submitbutton('storagewizard.createForm')"
                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATE_FORM_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                        >
                            <span class="fa-solid fa-wand-magic-sparkles me-1" aria-hidden="true"></span>
                            <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATE_FORM'); ?>
                        </button>
                    <?php else : ?>
                        <p>
                            <a
                                class="btn btn-outline-primary mb-3"
                                href="<?php echo Route::_('index.php?option=com_contentbuilderng&view=form&layout=edit&id=' . (int) $this->form->id . '&wizard=1'); ?>"
                            >
                                <span class="fa-solid fa-file-lines me-1" aria-hidden="true"></span>
                                <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_OPEN_FORM_SCREEN'); ?>
                            </a>
                        </p>
                        <div>
                            <button
                                type="button"
                                class="btn btn-primary"
                                onclick="Joomla.submitbutton('storagewizard.confirmForm')"
                                title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                            >
                                <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_NEXT'); ?>
                                <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                            </button>
                        </div>
                    <?php endif; ?>

                <?php elseif ($currentStep === StorageWizardService::STEP_MENU) : ?>
                    <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_MENU'); ?></h2>
                    <p class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_MENU_DESC'); ?></p>
                    <?php if (empty($this->menutypes)) : ?>
                        <div class="alert alert-warning"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_NO_MENUTYPES'); ?></div>
                    <?php else : ?>
                        <div class="mb-3">
                            <label class="form-label" for="cb-wizard-menutype"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_MENU_TYPE'); ?></label>
                            <select class="form-select" id="cb-wizard-menutype" name="menutype" required>
                                <?php foreach ($this->menutypes as $menutype) : ?>
                                    <option value="<?php echo htmlspecialchars((string) $menutype->menutype, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars((string) $menutype->title, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="cb-wizard-menu-parent"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_MENU_PARENT'); ?></label>
                            <select class="form-select" id="cb-wizard-menu-parent" name="parent_id">
                                <option value="1"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_MENU_PARENT_ROOT'); ?></option>
                                <?php foreach ($this->menuItems as $menuItem) : ?>
                                    <option value="<?php echo (int) $menuItem->id; ?>">
                                        <?php echo str_repeat('— ', max(0, (int) $menuItem->level - 1)); ?><?php echo htmlspecialchars((string) $menuItem->title, ENT_QUOTES, 'UTF-8'); ?>
                                        (<?php echo htmlspecialchars((string) $menuItem->menutype, ENT_QUOTES, 'UTF-8'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="cb-wizard-menu-title"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_MENU_ITEM_TITLE'); ?></label>
                            <input
                                class="form-control"
                                type="text"
                                id="cb-wizard-menu-title"
                                name="menu_title"
                                value="<?php echo htmlspecialchars((string) ($this->storage->title ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                maxlength="255"
                            >
                        </div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="Joomla.submitbutton('storagewizard.createMenu')"
                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATE_MENU_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                        >
                            <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_CREATE_MENU'); ?>
                        </button>
                    <?php endif; ?>
                    <button
                        type="button"
                        class="btn btn-link"
                        onclick="Joomla.submitbutton('storagewizard.skipMenu')"
                        title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_SKIP_MENU_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                    >
                        <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_SKIP_MENU'); ?>
                    </button>

                <?php else : ?>
                    <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_STEP_DONE'); ?></h2>
                    <p class="text-success">
                        <span class="fa-solid fa-circle-check me-1" aria-hidden="true"></span>
                        <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_DONE_DESC'); ?>
                    </p>
                    <ul class="list-unstyled">
                        <?php if ($this->storage) : ?>
                            <li>
                                <strong><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_DONE_STORAGE_LABEL'); ?></strong>
                                <a href="<?php echo Route::_('index.php?option=com_contentbuilderng&task=storage.edit&id=' . (int) $this->storage->id); ?>">
                                    <?php echo htmlspecialchars((string) $this->storage->title, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($this->form) : ?>
                            <li>
                                <strong><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_DONE_FORM_LABEL'); ?></strong>
                                <a href="<?php echo Route::_('index.php?option=com_contentbuilderng&task=form.edit&id=' . (int) $this->form->id); ?>">
                                    <?php echo htmlspecialchars((string) $this->form->title, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($this->storage) : ?>
                            <li>
                                <strong><?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_DONE_FRONTEND_LABEL'); ?></strong>
                                <a
                                    href="<?php echo Route::link('site', 'index.php?option=com_contentbuilderng&task=list.display&storage_id=' . (int) $this->storage->id, false, Route::TLS_IGNORE, true); ?>"
                                    target="_blank"
                                >
                                    <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_DONE_FRONTEND_LINK'); ?>
                                    <span class="fa-solid fa-arrow-up-right-from-square ms-1" aria-hidden="true"></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <button
                        type="button"
                        class="btn btn-success"
                        onclick="Joomla.submitbutton('storagewizard.finish')"
                        title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_FINISH_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                    >
                        <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_FINISH'); ?>
                    </button>
                <?php endif; ?>

                <?php if ($currentIndex > 1) : ?>
                    <button
                        type="button"
                        class="btn btn-link mt-2 ms-n2"
                        onclick="Joomla.submitbutton('storagewizard.back')"
                        title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_WIZARD_PREVIOUS_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                    >
                        <span class="fa-solid fa-arrow-left me-1" aria-hidden="true"></span>
                        <?php echo Text::_('COM_CONTENTBUILDERNG_WIZARD_PREVIOUS'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <input type="hidden" name="option" value="com_contentbuilderng" />
    <input type="hidden" name="view" value="storagewizard" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var adminUi = window.ContentBuilderNgAdmin;
    if (adminUi && typeof adminUi.initBootstrapTooltips === 'function') {
        adminUi.initBootstrapTooltips(document);
    }
});
</script>
