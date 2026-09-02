<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class StorageEditLayoutTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = \dirname(__DIR__, 4);
    }

    public function testStorageTabIsFirstAndFieldsTabIsSecond(): void
    {
        $template = \file_get_contents($this->root . '/admin/tmpl/storage/default.php');
        self::assertIsString($template);

        $storageTab = \strpos(
            $template,
            "addTab', 'view-pane', 'tab1', \$storageTabLabel('fa-solid fa-database', 'COM_CONTENTBUILDERNG_STORAGE_ADMINISTRATION')"
        );
        $fieldsTab = \strpos(
            $template,
            "addTab', 'view-pane', 'tab0', \$storageTabLabel('fa-solid fa-table-list', 'COM_CONTENTBUILDERNG_STORAGE')"
        );

        self::assertIsInt($storageTab);
        self::assertIsInt($fieldsTab);
        self::assertLessThan($fieldsTab, $storageTab);
        self::assertStringContainsString("\$defaultTab = 'tab1';", $template);
    }

    public function testCsvUpdatePanelIsOptIn(): void
    {
        $layout = \file_get_contents($this->root . '/admin/layouts/storage/information_tab.php');
        self::assertIsString($layout);

        self::assertStringContainsString(
            "if ((int) (\$item->id ?? 0) > 0 && \$csvImportRequested)",
            $layout
        );
    }

    public function testFieldEditorUsesGroupedRowActions(): void
    {
        $layout = \file_get_contents($this->root . '/admin/layouts/storage/storage_tab.php');
        $script = \file_get_contents($this->root . '/admin/tmpl/storage/default.php');

        self::assertIsString($layout);
        self::assertIsString($script);
        self::assertStringContainsString('cb-storage-fields-editor', $layout);
        self::assertStringContainsString('cb-storage-field-actions', $layout);
        self::assertStringContainsString('cb-storage-field-new-cancel', $script);
    }

    public function testFieldRowActionsUseTitlesetSubformFormalism(): void
    {
        $layout = \file_get_contents($this->root . '/admin/layouts/storage/storage_tab.php');
        self::assertIsString($layout);

        // Grouped add / remove / move buttons, matching the titleset editor's
        // native Joomla repeatable-table subform (btn-success / btn-danger /
        // btn-primary + icon-plus / icon-minus / icon-arrows-alt).
        self::assertStringContainsString('group-add', $layout);
        self::assertStringContainsString('group-remove', $layout);
        self::assertStringContainsString('icon-arrows-alt', $layout);
        self::assertStringContainsString('cb-storage-field-add', $layout);

        // The separate up/down order icons are gone (drag only).
        self::assertStringNotContainsString('cb-order-icons', $layout);
        self::assertStringNotContainsString('storage.orderup', $layout);
        self::assertStringNotContainsString('storage.orderdown', $layout);
    }

}
