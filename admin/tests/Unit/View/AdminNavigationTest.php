<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class AdminNavigationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = \dirname(__DIR__, 4);
    }

    public function testComponentRootAndOptionsCloseReturnToViews(): void
    {
        $displayController = $this->read('admin/src/Controller/DisplayController.php');
        $aboutView = $this->read('admin/src/View/About/HtmlView.php');

        self::assertStringContainsString("protected \$default_view = 'forms';", $displayController);
        self::assertStringNotContainsString("protected \$default_view = 'storages';", $displayController);
        self::assertStringContainsString(
            "index.php?option=com_contentbuilderng&view=forms",
            $aboutView
        );
    }

    public function testManifestDeclaresViewsBeforeStorage(): void
    {
        $manifest = new \SimpleXMLElement($this->read('com_contentbuilderng.xml'));
        $submenu = $manifest->xpath('/extension/administration/submenu/menu');

        self::assertIsArray($submenu);
        self::assertGreaterThanOrEqual(2, \count($submenu));
        self::assertSame('forms', (string) $submenu[0]['view']);
        self::assertSame('storages', (string) $submenu[1]['view']);
    }

    private function read(string $relativePath): string
    {
        $contents = \file_get_contents($this->root . '/' . $relativePath);
        self::assertIsString($contents, 'Unable to read ' . $relativePath);

        return $contents;
    }
}
