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
}
