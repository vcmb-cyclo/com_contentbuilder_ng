<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class ListStatesResetTest extends TestCase
{
    public function testResetMenuAndDebugOnlyFullResetAreRendered(): void
    {
        $root = \dirname(__DIR__, 4);
        $layout = (string) \file_get_contents($root . '/admin/layouts/form/list_states.php');
        $view = (string) \file_get_contents($root . '/admin/src/View/Form/HtmlView.php');

        self::assertStringNotContainsString('data-cb-list-states-reset', $layout);
        self::assertStringContainsString("['state_reset_inactive', 'inactive'", $view);
        self::assertStringContainsString("['state_reset_palette', 'palette'", $view);
        self::assertStringContainsString("['state_reset_disable', 'disable'", $view);
        self::assertStringContainsString("'state_reset_full'", $view);
        self::assertStringContainsString(
            "!empty(\$this->item->debug_mode) && \$identity->authorise('core.admin')",
            $view
        );
        self::assertStringContainsString("'data-cb-actions-context' => 'states'", $view);
    }

    public function testActionsToolbarIsContextualAndClosedWhenContextChanges(): void
    {
        $root = \dirname(__DIR__, 4);
        $script = (string) \file_get_contents($root . '/media/js/form-edit-init.js');

        self::assertStringContainsString('function cbRefreshContextualActions(closeMenu)', $script);
        self::assertStringContainsString('cbCloseActionsToolbarMenu();', $script);
        self::assertStringContainsString(
            'cbSetActionsToolbarEnabled(isStatesTab || (isViewTab && cbHasSelectedViewElements()))',
            $script
        );
        self::assertStringContainsString('cbRefreshContextualActions(!cbHasSelectedViewElements())', $script);
        self::assertStringContainsString('cbRefreshContextualActions(true)', $script);
        self::assertStringContainsString("'[id$=\"-' + escapedName + '\"]'", $script);
        self::assertStringContainsString("item.style.setProperty('display', 'none', 'important')", $script);
    }

    public function testResetScriptPreservesRecordAssignments(): void
    {
        $root = \dirname(__DIR__, 4);
        $script = (string) \file_get_contents($root . '/media/js/form-edit-init.js');

        self::assertStringContainsString("['60E309', 'FF9800', 'FCFC00', 'FC0000']", $script);
        self::assertStringContainsString("action === 'inactive'", $script);
        self::assertStringContainsString("action === 'palette' || action === 'full'", $script);
        self::assertStringContainsString("action === 'disable'", $script);
        self::assertStringContainsString('cbClearListStatePermissions();', $script);
        self::assertStringContainsString("'state_reset_palette': 'palette'", $script);
        self::assertStringContainsString("candidate.id.endsWith('-' + name)", $script);
        self::assertStringContainsString("input.closest('.clr-field')", $script);
        self::assertStringContainsString("colorisField.style.setProperty('--clr-color', previewColor)", $script);
        self::assertStringContainsString("colorInput.dispatchEvent(new Event('input', { bubbles: true }))", $script);
        self::assertStringNotContainsString('contentbuilderng_list_records', $script);
    }

    public function testNewViewDefaultsUseTheFourColorPalette(): void
    {
        $root = \dirname(__DIR__, 4);
        $model = (string) \file_get_contents($root . '/admin/src/Model/FormModel.php');

        self::assertStringContainsString("1 => '60E309'", $model);
        self::assertStringContainsString("2 => 'FF9800'", $model);
        self::assertStringContainsString("3 => 'FCFC00'", $model);
        self::assertStringContainsString("4 => 'FC0000'", $model);
        self::assertStringContainsString("'published' => \$index <= 4 ? 1 : 0", $model);
    }
}
