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
}
