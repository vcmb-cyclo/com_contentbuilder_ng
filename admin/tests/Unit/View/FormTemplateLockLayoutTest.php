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
        $formTemplate = (string) \file_get_contents($root . '/admin/tmpl/form/edit.php');

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
}
