<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class StorageWizardLayoutTest extends TestCase
{
    public function testWelcomePageHasStartActionAndStepExplanations(): void
    {
        $template = \file_get_contents(\dirname(__DIR__, 4) . '/admin/tmpl/storagewizard/default.php');
        self::assertIsString($template);

        self::assertStringContainsString("if (!\$wizardStarted)", $template);
        self::assertStringContainsString('COM_CONTENTBUILDERNG_WIZARD_WELCOME_DESC', $template);
        self::assertStringContainsString('COM_CONTENTBUILDERNG_WIZARD_START', $template);
        self::assertStringContainsString("Joomla.submitbutton('storagewizard.begin')", $template);
        self::assertStringContainsString('$stepDescriptions', $template);
    }

    public function testWizardKeepsSpreadsheetImportAvailableFromTheFieldsStep(): void
    {
        $template = \file_get_contents(\dirname(__DIR__, 4) . '/admin/tmpl/storagewizard/default.php');
        $model = \file_get_contents(\dirname(__DIR__, 4) . '/admin/src/Model/StorageModel.php');
        $layout = \file_get_contents(\dirname(__DIR__, 4) . '/admin/layouts/storage/information_tab.php');

        self::assertIsString($template);
        self::assertIsString($model);
        self::assertIsString($layout);
        self::assertStringContainsString('tabStartOffset=tab1&csv_import=1', $template);
        self::assertStringContainsString("\$supportedExtensions = ['csv', 'xlsx', 'xls'];", $model);
        self::assertStringContainsString('accept=".csv,.xlsx,.xls', $layout);
    }

    public function testExistingTablePickerWarnsOnlyWhenColumnsAreMissing(): void
    {
        $root = \dirname(__DIR__, 4);
        $wizard = \file_get_contents($root . '/admin/tmpl/storagewizard/default.php');
        $infoTab = \file_get_contents($root . '/admin/layouts/storage/information_tab.php');
        $adminUi = \file_get_contents($root . '/media/js/admin-ui.js');
        $controller = \file_get_contents($root . '/admin/src/Controller/StorageController.php');

        self::assertIsString($wizard);
        self::assertIsString($infoTab);
        self::assertIsString($adminUi);
        self::assertIsString($controller);

        // Both existing-table pickers route through the shared guard instead of
        // an unconditional alert().
        self::assertStringContainsString('ContentBuilderNgAdmin.warnBeforeExistingTable', $wizard);
        self::assertStringContainsString('ContentBuilderNgAdmin.warnBeforeExistingTable', $infoTab);
        self::assertStringNotContainsString("alert(selectedMode === '2'", $wizard);
        self::assertStringNotContainsString("alert(selectedMode === '2'", $infoTab);

        // The guard asks the server which system columns are missing.
        self::assertStringContainsString('warnBeforeExistingTable', $adminUi);
        self::assertStringContainsString('storage.checkExistingTableColumns', $adminUi);
        self::assertStringContainsString('function checkExistingTableColumns()', $controller);
    }

    public function testEveryWizardScreenHeadingHasTheAssistantIcon(): void
    {
        $template = \file_get_contents(\dirname(__DIR__, 4) . '/admin/tmpl/storagewizard/default.php');
        self::assertIsString($template);

        preg_match_all('/<h2 class="h5">/', $template, $headings);
        preg_match_all('/<h2 class="h5"><span class="fa-solid fa-wand-magic-sparkles me-2" aria-hidden="true"><\/span>/', $template, $iconHeadings);

        self::assertCount(count($headings[0]), $iconHeadings[0]);
    }
}
