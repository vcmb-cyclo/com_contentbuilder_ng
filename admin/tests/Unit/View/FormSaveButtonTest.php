<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class FormSaveButtonTest extends TestCase
{
    public function testSaveButtonsFollowDirtyStateAndSubmitTheExplicitForm(): void
    {
        $script = file_get_contents(
            \dirname(__DIR__, 4) . '/media/js/form-edit-init.js'
        );

        self::assertIsString($script);
        $script = str_replace("\r\n", "\n", $script);
        self::assertStringContainsString(
            "function cbSetDirtyState(isDirty) {\n"
                . "        cbDirtyState = !!isDirty;\n"
                . "        cbSetSaveButtonsEnabled(cbDirtyState);",
            $script
        );
        self::assertStringContainsString(
            "if (!error) {\n"
                . "                    cbSetDirtyState(false);\n"
                . "                    cbAnimateSaveButton();\n"
                . "                    Joomla.submitform(task, form);",
            $script
        );
    }

    public function testCompositeHiddenFieldsCanOptIntoDirtyTracking(): void
    {
        $dirtyTrackingScript = file_get_contents(
            \dirname(__DIR__, 4) . '/media/js/form-edit-init.js'
        );
        $listLimitField = file_get_contents(
            \dirname(__DIR__, 3) . '/src/Field/ListlimitField.php'
        );
        $listLimitScript = file_get_contents(
            \dirname(__DIR__, 4) . '/media/js/list-limit-field.js'
        );

        self::assertIsString($dirtyTrackingScript);
        self::assertIsString($listLimitField);
        self::assertIsString($listLimitScript);
        self::assertStringContainsString(
            "type === 'hidden' && field.dataset.cbDirtyTrack === 'true'",
            $dirtyTrackingScript
        );
        self::assertStringContainsString('data-cb-dirty-track="true"', $listLimitField);
        self::assertStringContainsString(
            "storage.dispatchEvent(new Event('input', { bubbles: true }))",
            $listLimitScript
        );
        self::assertStringContainsString(
            "storage.dispatchEvent(new Event('change', { bubbles: true }))",
            $listLimitScript
        );
    }
}
