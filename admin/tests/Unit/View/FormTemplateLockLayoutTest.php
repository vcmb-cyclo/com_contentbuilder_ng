<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class FormTemplateLockLayoutTest extends TestCase
{
    public function testTemplateLocksAreAlwaysRenderedInWideCreationPanels(): void
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
        self::assertStringContainsString(
            'class="col-12 col-xl-8 d-flex" id="cb-form-details-create-sample-card-col"',
            $detailsLayout
        );
        self::assertStringContainsString(
            'class="col-12 col-xl-8 d-flex" id="cb-form-edit-create-sample-card-col"',
            $editLayout
        );
        self::assertStringContainsString(
            'class="form-check mb-0 ms-xl-auto flex-shrink-0 text-nowrap"',
            $detailsLayout
        );
        self::assertStringContainsString(
            'class="form-check mb-0 ms-xl-auto flex-shrink-0 text-nowrap"',
            $editLayout
        );
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

    public function testTemplateLockBadgeTakesPriorityOverDotStates(): void
    {
        $root = \dirname(__DIR__, 4);
        $formTemplate = (string) \file_get_contents($root . '/admin/tmpl/form/edit.php');
        $badgeStart = \strpos($formTemplate, '$templateStateBadge = static function');
        $lockBranch = \strpos($formTemplate, 'if ($locked)', $badgeStart ?: 0);
        $dotBranch = \strpos($formTemplate, '$tip = Text::_($inconsistent', $badgeStart ?: 0);

        self::assertNotFalse($badgeStart);
        self::assertNotFalse($lockBranch);
        self::assertNotFalse($dotBranch);
        self::assertLessThan($dotBranch, $lockBranch);
        self::assertStringContainsString(
            'cb-template-state is-locked',
            $formTemplate
        );
    }

    public function testEmptyTemplateDotRequiresMatchingFrontendPermission(): void
    {
        $root = \dirname(__DIR__, 4);
        $formTemplate = str_replace(
            "\r\n",
            "\n",
            (string) \file_get_contents($root . '/admin/tmpl/form/edit.php')
        );

        self::assertStringContainsString(
            "\$detailsTemplateRequired = \$hasFrontendPermission('view')",
            $formTemplate
        );
        self::assertStringContainsString(
            "\$editableTemplateRequired = \$hasFrontendPermission('edit') || \$hasFrontendPermission('new')",
            $formTemplate
        );
        self::assertStringContainsString(
            'if (!$filled && !$inconsistent && !$required)',
            $formTemplate
        );
        self::assertStringContainsString(
            "'COM_CONTENTBUILDERNG_TAB_TEMPLATE_EMPTY',\n            \$detailsTemplateRequired,",
            $formTemplate
        );
        self::assertStringContainsString(
            "'COM_CONTENTBUILDERNG_TAB_TEMPLATE_EMPTY',\n            \$editableTemplateRequired,",
            $formTemplate
        );
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
            'if ($hasPublishedListState || $showsListStates || $hasListStatePermission)',
            $formTemplate
        );
        self::assertStringContainsString(
            'if ($hasPublishedListState && $showsListStates && $hasListStatePermission)',
            $formTemplate
        );
        self::assertStringContainsString(
            "'COM_CONTENTBUILDERNG_TAB_TIP_LIST_STATES', \$listStatesBadge",
            $formTemplate
        );
        self::assertStringContainsString(
            "\$listIntroBadge = \$neutralTabBadge(trim((string) (\$this->item->intro_text ?? '')) !== '')",
            $formTemplate
        );
        self::assertStringContainsString(
            "'COM_CONTENTBUILDERNG_TAB_TIP_LIST_INTRO_TEXT', \$listIntroBadge",
            $formTemplate
        );
        self::assertStringContainsString(
            '.cb-template-state.is-empty{border:',
            $style
        );
        self::assertStringContainsString(
            '.cb-template-state.is-neutral{background:',
            $style
        );
    }
}
