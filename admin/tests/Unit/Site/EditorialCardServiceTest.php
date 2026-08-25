<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Site;

use CB\Component\Contentbuilderng\Site\Service\EditorialCardService;
use PHPUnit\Framework\TestCase;

final class EditorialCardServiceTest extends TestCase
{
    public function testTransformsCompleteSyntaxWithSharedCardClasses(): void
    {
        $html = '<div class="cb-card-editorial custom" data-title="Information | H5"'
            . ' data-card="v3" data-w="66"><p>Content</p></div>';
        $result = EditorialCardService::transform($html);

        self::assertStringContainsString('class="custom cb-card cb-card-v3 cb-card-w66"', $result);
        self::assertStringContainsString('<h5 class="cb-card-header">Information</h5>', $result);
        self::assertStringContainsString('<div class="cb-card-body"><p>Content</p></div>', $result);
        self::assertStringNotContainsString('data-title', $result);
    }

    public function testUsesV1AndWidth33Defaults(): void
    {
        $result = EditorialCardService::transform(
            '<div class="cb-card-editorial" data-title="Information"><p>Content</p></div>'
        );

        self::assertStringContainsString('class="cb-card cb-card-v1 cb-card-w33"', $result);
    }

    public function testVisibleHeadingBecomesCardHeaderAndIsRemovedFromBody(): void
    {
        $result = EditorialCardService::transform(
            '<div class="cb-card-editorial" data-card="v4" data-w="33">'
                . '<h2 data-cb-card-title>Informations</h2><p>Content</p></div>'
        );

        self::assertStringContainsString('<h2 class="cb-card-header">Informations</h2>', $result);
        self::assertStringContainsString('<div class="cb-card-body"><p>Content</p></div>', $result);
        self::assertStringNotContainsString('data-cb-card-title', $result);
    }

    public function testVisibleHeadingTakesPriorityOverLegacyDataTitle(): void
    {
        $result = EditorialCardService::transform(
            '<div class="cb-card-editorial" data-title="Legacy|h5">'
                . '<h3 data-cb-card-title>Visible</h3></div>'
        );

        self::assertStringContainsString('<h3 class="cb-card-header">Visible</h3>', $result);
        self::assertStringNotContainsString('Legacy', $result);
    }

    public function testHeadingWithoutMarkerRemainsInCardBody(): void
    {
        $result = EditorialCardService::transform(
            '<div class="cb-card-editorial"><h4>Body heading</h4><p>Content</p></div>'
        );

        self::assertStringNotContainsString('cb-card-header', $result);
        self::assertStringContainsString(
            '<div class="cb-card-body"><h4>Body heading</h4><p>Content</p></div>',
            $result
        );
    }

    public function testTitleMarkerOnNonHeadingIsIgnored(): void
    {
        $result = EditorialCardService::transform(
            '<div class="cb-card-editorial"><p data-cb-card-title>Body text</p></div>'
        );

        self::assertStringNotContainsString('cb-card-header', $result);
        self::assertStringContainsString('<p data-cb-card-title>Body text</p>', $result);
    }

    public function testInvalidVariantAndWidthUseDefaults(): void
    {
        $result = EditorialCardService::transform(
            '<div class="cb-card-editorial" data-card="v9" data-w="50"><p>Content</p></div>'
        );

        self::assertStringContainsString('class="cb-card cb-card-v1 cb-card-w33"', $result);
    }

    public function testTitleIsOptionalAndHtmlAndPluginTagsArePreserved(): void
    {
        $html = '<div class="cb-card-editorial" data-card="v1" data-w="33">'
            . '<p>Total: {CBStats id=15 output=total}</p>'
            . '<div>{CBList id=15 fields="Nom|Prenom"}</div>'
            . '</div>';
        $result = EditorialCardService::transform($html);

        self::assertStringNotContainsString('cb-card-header', $result);
        self::assertStringContainsString('{CBStats id=15 output=total}', $result);
        self::assertStringContainsString('{CBList id=15 fields="Nom|Prenom"}', $result);
    }

    public function testRemTitleAndSpecialCharactersArePreservedSafely(): void
    {
        $result = EditorialCardService::transform(
            '<div class="cb-card-editorial" data-title="Informations &amp; aide | rem1.25">Été</div>'
        );

        self::assertStringContainsString(
            '<h4 class="cb-card-header" style="font-size:1.25rem">Informations &amp; aide</h4>',
            $result
        );
        self::assertStringContainsString('Été', $result);
    }

    public function testNestedHtmlAndWhitespaceOutsideCardArePreserved(): void
    {
        $html = "Before&nbsp;\n<div class=\"cb-card-editorial\"><div><strong>Nested</strong></div></div>\nAfter";
        $result = EditorialCardService::transform($html);

        self::assertStringStartsWith("Before\u{00A0}\n", $result);
        self::assertStringContainsString('<div><strong>Nested</strong></div>', $result);
        self::assertStringEndsWith("\nAfter", $result);
    }

    public function testNonBreakingSpacesBetweenGridCardsAreRemoved(): void
    {
        $result = EditorialCardService::transform(
            '<div class="cb-cards">&nbsp;'
                . '<div class="cb-card-editorial">First</div>&nbsp; '
                . '<div class="cb-card-editorial">Second</div>&nbsp;'
                . '</div>'
        );

        self::assertStringNotContainsString("\u{00A0}", $result);
        self::assertSame(2, substr_count($result, 'class="cb-card cb-card-v1 cb-card-w33"'));
    }

    public function testContentWithoutMarkerIsReturnedUnchanged(): void
    {
        $html = '<div class="cb-card">Content</div>';

        self::assertSame($html, EditorialCardService::transform($html));
    }

    public function testSimilarClassNameIsNotTransformed(): void
    {
        $html = '<div class="cb-card-editorial-example">Content &amp; text</div>';

        self::assertSame($html, EditorialCardService::transform($html));
    }
}
