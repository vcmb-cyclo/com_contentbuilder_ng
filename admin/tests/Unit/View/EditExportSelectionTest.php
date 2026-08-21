<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class EditExportSelectionTest extends TestCase
{
    public function testExportCapabilityIsAvailableAcrossSchemaUiAndRuntime(): void
    {
        $root = \dirname(__DIR__, 4);
        $install = (string) \file_get_contents($root . '/admin/sql/install.sql');
        $migration = (string) \file_get_contents($root . '/admin/sql/updates/mysql/6.1.10-RC11-B6.sql');
        $layout = (string) \file_get_contents($root . '/admin/layouts/form/elements_table.php');
        $controller = (string) \file_get_contents($root . '/admin/src/Controller/FormController.php');
        $formScript = (string) \file_get_contents($root . '/media/js/form-edit-init.js');
        $exportModel = (string) \file_get_contents($root . '/site/src/Model/ExportModel.php');

        self::assertStringContainsString('export_include', $install);
        self::assertStringContainsString('ADD COLUMN', $migration);
        self::assertStringContainsString("'export' => Text::_('COM_CONTENTBUILDERNG_ELEMENT_HEADING_EXPORT')", $layout);
        self::assertStringContainsString("'export_include'", $controller);
        self::assertStringContainsString("case 'form.no_export_include':", $formScript);
        self::assertStringContainsString("{ field: 'export_include', value: '0' }", $formScript);
        self::assertStringContainsString("quoteName('export_include') . ' = 1'", $exportModel);
    }

    public function testSystemExportColumnsAreIndependentAndMigratedFromDisplaySettings(): void
    {
        $root = \dirname(__DIR__, 4);
        $migration = (string) \file_get_contents($root . '/admin/sql/updates/mysql/6.1.10-RC11-B6.sql');
        $options = (string) \file_get_contents($root . '/admin/layouts/form/advanced_options.php');
        $export = (string) \file_get_contents($root . '/site/tmpl/export/default.php');

        foreach (['export_id_column', 'export_state_column', 'export_publish_column'] as $field) {
            self::assertStringContainsString($field, $migration);
            self::assertStringContainsString($field, $options);
            self::assertStringContainsString('$this->data->' . $field, $export);
        }
        self::assertStringContainsString('export_id_column', $migration);
        self::assertStringContainsString('show_id_column', $migration);
        self::assertStringContainsString('export_state_column', $migration);
        self::assertStringContainsString('list_state', $migration);
        self::assertStringContainsString('export_publish_column', $migration);
        self::assertStringContainsString('list_publish', $migration);
    }

    public function testThothEditableTemplateSkipsNonEditableElements(): void
    {
        $root = \dirname(__DIR__, 4);
        $thoth = (string) \file_get_contents($root . '/plugins/contentbuilderng_themes/thoth/src/Extension/Thoth.php');
        $editableStart = \strpos($thoth, 'private function buildEditableTemplateSample');
        $editableEnd = \strpos($thoth, 'private function fetchElementDefinitions', $editableStart ?: 0);
        $editableGenerator = \substr($thoth, $editableStart ?: 0, ($editableEnd ?: \strlen($thoth)) - ($editableStart ?: 0));

        self::assertStringContainsString('if (!$editable)', $editableGenerator);
        self::assertStringNotContainsString("':value}'", $editableGenerator);
        self::assertStringContainsString("':item}'", $editableGenerator);
    }

    public function testEveryAjaxElementToggleReloadsServerDerivedState(): void
    {
        $root = \dirname(__DIR__, 4);
        $formScript = (string) \file_get_contents($root . '/media/js/form-edit-init.js');

        self::assertStringNotContainsString('cbReloadAfterLockedEditableChange', $formScript);
        self::assertSame(2, substr_count($formScript, 'cbReloadForDebugToggle(rowId);'));
    }

    public function testExportStateLookupIsScopedToTheCurrentView(): void
    {
        $root = \dirname(__DIR__, 4);
        $export = (string) \file_get_contents($root . '/site/tmpl/export/default.php');

        self::assertStringContainsString("quoteName('records.form_id')", $export);
        self::assertStringContainsString("quoteName('states.form_id')", $export);
        self::assertStringNotContainsString('WHERE id = (SELECT state_id', $export);
    }

    public function testListStateColourIsLimitedToTheControl(): void
    {
        $root = \dirname(__DIR__, 4);
        $list = (string) \file_get_contents($root . '/site/tmpl/list/default.php');
        $script = (string) \file_get_contents($root . '/media/js/list-init.js');

        self::assertStringNotContainsString('$stateCellStyle', $list);
        self::assertStringContainsString('$stateControlStyle', $list);
        self::assertStringNotContainsString('stateCells.forEach', $script);
    }
}
