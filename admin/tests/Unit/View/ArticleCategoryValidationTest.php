<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class ArticleCategoryValidationTest extends TestCase
{
    public function testMissingArticleCategoryShowsMessageAndActivatesArticleTab(): void
    {
        $source = file_get_contents(
            \dirname(__DIR__, 3) . '/layouts/form/article_tab.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            "document.querySelector('[role=\"tab\"][aria-controls=\"tab10\"]')",
            $source
        );
        self::assertStringContainsString(
            'Joomla.renderMessages({ error: [message] });',
            $source
        );
        self::assertStringContainsString('articleTabButton.click();', $source);
        self::assertStringContainsString('categoryField.focus();', $source);
        self::assertStringContainsString('showMissingCategoryMessage();', $source);
    }
}
