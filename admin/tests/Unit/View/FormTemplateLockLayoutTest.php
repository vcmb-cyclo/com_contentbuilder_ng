<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class FormTemplateLockLayoutTest extends TestCase
{
    public function testDetailsTemplateLockIsAlwaysRendered(): void
    {
        $root = \dirname(__DIR__, 4);
        $detailsLayout = (string) \file_get_contents($root . '/admin/layouts/form/details_display.php');
        $formTemplate = (string) \file_get_contents($root . '/admin/tmpl/form/edit.php');

        self::assertStringContainsString(
            "<?php if (is_callable(\$renderCheckbox)) : ?>",
            $detailsLayout
        );
        self::assertStringNotContainsString(
            "\$displayData['canLockTemplate']",
            $detailsLayout
        );
        self::assertStringContainsString(
            'class="col-12 col-xl-8 d-flex" id="cb-form-details-create-sample-card-col"',
            $detailsLayout
        );
        self::assertStringContainsString(
            'class="form-check mb-0 ms-xl-auto flex-shrink-0 text-nowrap"',
            $detailsLayout
        );
        self::assertStringContainsString(
            "!empty(\$this->item->details_template_locked)",
            $formTemplate
        );
        self::assertStringNotContainsString(
            "\$canLockDetailsTemplate",
            $formTemplate
        );
    }
}
