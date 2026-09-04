<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class FormTemplateLockLayoutTest extends TestCase
{
    public function testTemplateLocksAreRenderedInCompactDisplayPanels(): void
    {
        $root = \dirname(__DIR__, 4);
        $detailsLayout = (string) \file_get_contents($root . '/admin/layouts/form/details_display.php');
        $editLayout = (string) \file_get_contents($root . '/admin/layouts/form/edit_display.php');
        $formTemplate = str_replace(
            "\r\n",
            "\n",
            (string) \file_get_contents($root . '/admin/tmpl/form/edit.php')
        );

        self::assertStringContainsString(
            "<?php if (is_callable(\$renderCheckbox)) : ?>",
            $detailsLayout
        );
        self::assertStringNotContainsString(
            "\$displayData['canLockTemplate']",
            $detailsLayout
        );
        self::assertStringNotContainsString(
            "\$displayData['canLockTemplate']",
            $editLayout
        );
        self::assertStringNotContainsString('cb-form-details-create-sample-card', $detailsLayout);
        self::assertStringNotContainsString('cb-form-edit-create-sample-card', $editLayout);
        self::assertStringContainsString('class="form-check mb-0 flex-shrink-0 text-nowrap border-start ps-3"', $detailsLayout);
        self::assertStringContainsString('class="form-check mb-0 flex-shrink-0 text-nowrap border-start ps-3"', $editLayout);
        self::assertStringNotContainsString('id="create_sample"', $detailsLayout);
        self::assertStringNotContainsString('id="create_editable_sample"', $editLayout);
        self::assertStringContainsString('id="cb-form-edit-by-type-field-group"', $editLayout);
        self::assertStringContainsString('form-check mb-0 flex-shrink-0 border-start ps-3', $editLayout);
        self::assertStringContainsString(
            "!empty(\$this->item->details_template_locked)",
            $formTemplate
        );
        self::assertStringNotContainsString(
            "\$canLockDetailsTemplate",
            $formTemplate
        );
        self::assertStringContainsString(
            "!empty(\$this->item->editable_template_locked)",
            $formTemplate
        );
        self::assertStringNotContainsString(
            "\$canLockEditableTemplate",
            $formTemplate
        );
    }

    public function testTemplateLockComplementsDotStates(): void
    {
        $root = \dirname(__DIR__, 4);
        $formTemplate = (string) \file_get_contents($root . '/admin/tmpl/form/edit.php');
        $stateStart = \strpos($formTemplate, '$templateTabState = static function');
        $lockBranch = \strpos($formTemplate, 'if ($locked)', $stateStart ?: 0);
        $dotBranch = \strpos($formTemplate, '$badge = \' <span class="\' . $stateClass', $stateStart ?: 0);

        self::assertNotFalse($stateStart);
        self::assertNotFalse($lockBranch);
        self::assertNotFalse($dotBranch);
        self::assertGreaterThan($lockBranch, $dotBranch);
        self::assertStringContainsString(
            'cb-template-lock-inline',
            $formTemplate
        );
        self::assertStringContainsString('cb-template-lock-stack', $formTemplate);
        self::assertStringContainsString('cb-template-state-inline', $formTemplate);
        self::assertStringNotContainsString('is-with-lock', $formTemplate);
    }

    public function testEmptyTemplateDotRequiresMatchingFrontendPermission(): void
    {
        $root = \dirname(__DIR__, 4);
        $formTemplate = str_replace(
            "\r\n",
            "\n",
            (string) \file_get_contents($root . '/admin/tmpl/form/edit.php')
        );

        self::assertStringContainsString("\$detailsTemplateRequired = \$hasPublishedDetailElement && \$hasFrontendPermission('view')", $formTemplate);
        self::assertStringContainsString("\$editableTemplateRequired = \$hasFrontendPermission('edit')", $formTemplate);
        self::assertStringContainsString('$detailsEntryPointEnabled = $detailsTemplateRequired && $hasPublishedLinkableElement', $formTemplate);
        self::assertStringContainsString('$editableEntryPointEnabled = $hasPublishedEditableElement', $formTemplate);
        self::assertStringContainsString('(!empty($this->item->edit_button) || $detailsEntryPointEnabled)', $formTemplate);
        self::assertStringNotContainsString("\$hasFrontendPermission('new') && !empty(\$this->item->new_button)", $formTemplate);
        self::assertStringContainsString("'COM_CONTENTBUILDERNG_TAB_TEMPLATE_STATUS_INCOMPLETE'", $formTemplate);
        self::assertStringContainsString(
            "'tipKey' => 'COM_CONTENTBUILDERNG_TAB_TEMPLATE_STATUS_INACTIVE_EMPTY'",
            $formTemplate
        );
        self::assertStringContainsString(
            "\$detailsTemplateRequired,",
            $formTemplate
        );
        self::assertStringContainsString(
            "\$editableTemplateRequired,",
            $formTemplate
        );
        self::assertStringContainsString("\$badge = ' <span class=\"cb-template-state is-locked ms-1\"", $formTemplate);
    }

    public function testTemplateLocksDefaultToEnabledForNewViews(): void
    {
        $root = \dirname(__DIR__, 4);
        $form = simplexml_load_file($root . '/admin/forms/form.xml');

        self::assertNotFalse($form);
        self::assertSame('1', (string) $form->xpath('//field[@name="details_template_locked"]')[0]['default']);
        self::assertSame('1', (string) $form->xpath('//field[@name="editable_template_locked"]')[0]['default']);
    }

    public function testContentOnlyTabsUseNeutralDotsOnlyWhenConfigured(): void
    {
        $root = \dirname(__DIR__, 4);
        $formTemplate = (string) \file_get_contents($root . '/admin/tmpl/form/edit.php');
        $style = (string) \file_get_contents($root . '/media/css/form-edit.css');

        self::assertStringContainsString(
            '$neutralTabBadge = static function (bool $hasContent): string',
            $formTemplate
        );
        self::assertStringContainsString(
            "trim((string) (\$this->item->email_admin_template ?? '')) !== ''",
            $formTemplate
        );
        self::assertStringContainsString(
            "|| trim((string) (\$this->item->email_template ?? '')) !== ''",
            $formTemplate
        );
        self::assertStringContainsString(
            "'COM_CONTENTBUILDERNG_TAB_TIP_EMAIL_TEMPLATES', \$emailTemplateBadge",
            $formTemplate
        );
        self::assertStringContainsString(
            '$hasPublishedListState = false;',
            $formTemplate
        );
        self::assertStringContainsString(
            '$showsStateFilter = !empty($this->item->show_state_filter);',
            $formTemplate
        );
        self::assertStringContainsString(
            'if ($hasPublishedListState || $showsListStates || $showsStateFilter || $hasListStatePermission)',
            $formTemplate
        );
        self::assertStringContainsString(
            'if ($hasPublishedListState)',
            $formTemplate
        );
        self::assertStringContainsString(
            "\$listStatesTabTipKey, \$listStatesBadge",
            $formTemplate
        );
        self::assertStringContainsString(
            "\$listIntroBadge = \$hasListIntro",
            $formTemplate
        );
        self::assertStringContainsString(
            "'COM_CONTENTBUILDERNG_TAB_TIP_LIST_INTRO_ACTIVE'",
            $formTemplate
        );
        self::assertStringContainsString('.cb-template-state.is-filled::before{content:"✓"}', $style);
        self::assertStringContainsString('.cb-template-state.is-incomplete::before{content:"▲"}', $style);
        self::assertStringContainsString('.cb-template-state.is-inconsistent::before{content:"✕"}', $style);
    }
}
