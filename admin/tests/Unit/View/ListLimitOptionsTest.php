<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class ListLimitOptionsTest extends TestCase
{
    public function testFrontendOffersOnlyValidatedStandardLimits(): void
    {
        $template = \file_get_contents(
            \dirname(__DIR__, 4) . '/site/tmpl/list/default.php'
        );

        self::assertIsString($template);
        self::assertStringContainsString('$configuredLimitOptions = ListLimitHelper::getPaginationChoices();', $template);
        self::assertStringContainsString(
            '$limitOptions = ListLimitHelper::insertCurrentPaginationChoice($limitOptions, $currentLimit);',
            $template
        );
        self::assertStringContainsString('$label = (string) $opt;', $template);
        self::assertStringContainsString("Text::_('JALL')", $template);
        self::assertStringContainsString('<option value="0"', $template);
        self::assertStringNotContainsString('Custom (', $template);
        self::assertStringNotContainsString('[5, 10, 20, 25, 50, 100', $template);
        self::assertStringNotContainsString('sort($limitOptions', $template);
    }
}
