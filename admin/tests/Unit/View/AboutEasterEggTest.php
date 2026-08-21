<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class AboutEasterEggTest extends TestCase
{
    public function testAboutEasterEggRequiresFiveClicksAndUsesPackagedWebp(): void
    {
        $root = dirname(__DIR__, 4);
        $template = file_get_contents($root . '/admin/tmpl/about/default.php');
        $script = file_get_contents($root . '/media/js/admin-about.js');
        $image = $root . '/media/images/cbng-easter-egg-2026.webp';

        self::assertIsString($template);
        self::assertIsString($script);
        self::assertFileExists($image);
        self::assertGreaterThan(0, filesize($image));
        self::assertStringContainsString('data-cb-easter-egg-hotspot', $template);
        self::assertStringContainsString('cbng-easter-egg-2026.webp', $template);
        self::assertStringContainsString('if (clickCount < 5)', $script);
        self::assertStringContainsString('}, 3000);', $script);
        self::assertStringContainsString("event.key === 'Escape'", $script);
        self::assertStringContainsString("image.removeAttribute('src')", $script);
    }
}
