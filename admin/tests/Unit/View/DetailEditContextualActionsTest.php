<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class DetailEditContextualActionsTest extends TestCase
{
    public function testToolbarDefinesContextualDetailAndEditActions(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/admin/src/View/Form/HtmlView.php');

        self::assertIsString($source);
        self::assertStringContainsString("'data-cb-template-action' => \$context . ':' . \$action", $source);
        self::assertStringContainsString("['details_reset_display', 'display'", $source);
        self::assertStringContainsString("['details_regenerate_template', 'regenerate'", $source);
        self::assertStringContainsString("['details_disable', 'disable'", $source);
        self::assertStringContainsString("['edit_reset_display', 'display'", $source);
        self::assertStringContainsString("['edit_regenerate_template', 'regenerate'", $source);
        self::assertStringContainsString("['edit_disable', 'disable'", $source);
        self::assertStringContainsString("['intro_reset', 'intro'", $source);
        self::assertStringContainsString("['article_reset', 'article'", $source);
        self::assertStringContainsString("!empty(\$this->item->debug_mode) && \$identity->authorise('core.admin')", $source);
    }

    public function testJavascriptScopesActionsAndImplementsEveryResetLevel(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/media/js/form-edit-init.js');

        self::assertIsString($source);
        self::assertStringContainsString("var isDetailsTab = tabId === 'tab3';", $source);
        self::assertStringContainsString("var isEditTab = tabId === 'tab5';", $source);
        self::assertStringContainsString("var isIntroTab = tabId === 'tab2';", $source);
        self::assertStringContainsString("var isArticleTab = tabId === 'tab10';", $source);
        self::assertStringContainsString('cbToolbarSetItemVisible(name, isViewTab);', $source);
        self::assertStringContainsString("cbClearFrontendPermissions(['view']);", $source);
        self::assertStringContainsString("cbClearFrontendPermissions(['edit', 'new']);", $source);
        self::assertMatchesRegularExpression(
            "/cbSetNamedCheckbox\\('edit_button', false\\);\\R\\s+cbClearFrontendPermissions\\(\\['edit'\\]\\);/",
            $source
        );
        self::assertStringContainsString("Joomla.submitbutton('form.apply');", $source);
        self::assertStringContainsString('cbApplyAjaxRowMutation(actionElement, task, rowId);', $source);
        self::assertStringContainsString('cbReloadForDebugToggle(rowId);', $source);
        self::assertStringNotContainsString('cbReloadAfterLockedEditableChange', $source);
        self::assertStringContainsString("cbSetEditorFieldValue('details_template', '');", $source);
        self::assertStringContainsString("cbSetEditorFieldValue('editable_template', '');", $source);
    }

    public function testLocalResetButtonsWereRemoved(): void
    {
        $root = dirname(__DIR__, 4);
        $details = file_get_contents($root . '/admin/layouts/form/details_display.php');
        $edit = file_get_contents($root . '/admin/layouts/form/edit_display.php');

        self::assertIsString($details);
        self::assertIsString($edit);
        self::assertStringNotContainsString('cb-reset-details-display', $details);
        self::assertStringNotContainsString('cb-reset-edit-display', $edit);
    }
}
