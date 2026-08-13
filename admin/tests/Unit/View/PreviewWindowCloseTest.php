<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class PreviewWindowCloseTest extends TestCase
{
    public function testAdminPreviewIsOpenedByScriptAndFrontendCanCloseIt(): void
    {
        $root = \dirname(__DIR__, 4);
        $adminScript = (string) \file_get_contents($root . '/media/js/admin-ui.js');
        $frontendScript = (string) \file_get_contents($root . '/media/js/contentbuilderng.js');

        self::assertStringContainsString("[data-cb-open-preview]", $adminScript);
        self::assertStringContainsString("window.open(trigger.href, 'contentbuilderng-preview')", $adminScript);
        self::assertStringContainsString('if (!previewWindow)', $adminScript);
        self::assertStringContainsString('[data-cb-close-preview]', $frontendScript);
        self::assertStringContainsString('window.close();', $frontendScript);

        foreach ([
            $root . '/admin/src/View/Form/HtmlView.php',
            $root . '/admin/src/View/Storage/HtmlView.php',
        ] as $view) {
            self::assertStringContainsString("'data-cb-open-preview' => 'true'", (string) \file_get_contents($view));
        }
    }
}
