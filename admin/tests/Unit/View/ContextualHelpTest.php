<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class ContextualHelpTest extends TestCase
{
    public function testHelpCoversEveryViewTabAndDefaultsSafely(): void
    {
        $root = dirname(__DIR__, 4);
        $source = file_get_contents($root . '/admin/tmpl/form/help.php');

        self::assertIsString($source);
        self::assertStringContainsString("getCmd('section', 'overview')", $source);
        foreach (['tab0', 'tab1', 'tab2', 'tab3', 'tab5', 'tab6', 'tab7', 'tab8', 'tab9', 'tab10', 'tab11', 'tab12', 'tab13', 'tab14'] as $tabId) {
            self::assertStringContainsString("'$tabId' =>", $source);
        }
        self::assertStringContainsString("if (!isset(\$sections[\$requestedSection]))", $source);
    }

    public function testHelpButtonTransmitsTheActiveTab(): void
    {
        $root = dirname(__DIR__, 4);
        $script = file_get_contents($root . '/media/js/form-edit-init.js');
        $config = file_get_contents($root . '/admin/tmpl/form/edit_init_scripts.php');

        self::assertIsString($script);
        self::assertIsString($config);
        self::assertStringContainsString("'helpUrl' => Uri::base()", $config);
        self::assertStringContainsString("encodeURIComponent(cbGetActiveViewTabId())", $script);
        self::assertStringContainsString("window.open(helpUrl, 'cbng-view-help'", $script);
    }
}
