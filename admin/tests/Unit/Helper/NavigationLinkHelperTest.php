<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Helper;

use CB\Component\Contentbuilderng\Site\Helper\NavigationLinkHelper;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 4) . '/site/src/Helper/NavigationLinkHelper.php';

final class NavigationLinkHelperTest extends TestCase
{
    public function testBuildsRouteLinkWithQueryAndSuffix(): void
    {
        self::assertSame(
            'index.php?option=com_contentbuilderng&view=details#record',
            NavigationLinkHelper::buildRouteLink(
                ['option' => 'com_contentbuilderng', 'view' => 'details'],
                '#record'
            )
        );
    }

    public function testBuildsRouteLinkWithoutQueryOrSuffix(): void
    {
        self::assertSame('index.php', NavigationLinkHelper::buildRouteLink([]));
    }

    public function testBuildsNormalizedNestedListQuery(): void
    {
        self::assertSame(
            'list%5Bstart%5D=0&list%5Blimit%5D=0&list%5Bordering%5D=title&list%5Bdirection%5D=asc',
            NavigationLinkHelper::buildListQuery(-10, -20, 'title', 'asc')
        );
    }

    public function testBuildsRecordHrefWithListStateAndSuffix(): void
    {
        self::assertSame(
            'index.php?option=com_contentbuilderng&view=details'
            . '&record_id=15'
            . '&list%5Bstart%5D=20'
            . '&list%5Blimit%5D=10'
            . '&list%5Bordering%5D=title'
            . '&list%5Bdirection%5D=desc'
            . '#record',
            NavigationLinkHelper::buildHref(
                ' index.php?option=com_contentbuilderng&view=details ',
                15,
                20,
                10,
                'title',
                'desc',
                '#record'
            )
        );
    }

    public function testRejectsHrefWithoutBaseLinkOrRecord(): void
    {
        self::assertSame('', NavigationLinkHelper::buildHref('', 15, 0, 20, '', ''));
        self::assertSame('', NavigationLinkHelper::buildHref('index.php?option=com_contentbuilderng', 0, 0, 20, '', ''));
    }

    public function testEncodesInternalReturnUrl(): void
    {
        $url = 'index.php?option=com_contentbuilderng&view=list';

        self::assertSame(base64_encode($url), NavigationLinkHelper::encodeInternalReturn($url));
    }

    public function testKeepsAlreadyEncodedInternalReturnUrl(): void
    {
        $encoded = base64_encode('/administrator/index.php?option=com_contentbuilderng');

        self::assertSame($encoded, NavigationLinkHelper::encodeInternalReturn($encoded));
    }

    public function testRejectsEmptyAndExternalReturnUrlsWhenEncoding(): void
    {
        self::assertSame('', NavigationLinkHelper::encodeInternalReturn('  '));
        self::assertSame('', NavigationLinkHelper::encodeInternalReturn('https://example.net/path'));
    }

    public function testDecodesInternalReturnUrl(): void
    {
        $url = '/index.php?option=com_contentbuilderng&view=list';

        self::assertSame($url, NavigationLinkHelper::decodeInternalReturn(base64_encode($url)));
    }

    public function testKeepsPlainInternalReturnUrlWhenDecoding(): void
    {
        $url = 'index.php?option=com_contentbuilderng&view=list';

        self::assertSame($url, NavigationLinkHelper::decodeInternalReturn($url));
    }

    public function testRejectsEmptyAndExternalReturnUrlsWhenDecoding(): void
    {
        self::assertSame('', NavigationLinkHelper::decodeInternalReturn('  '));
        self::assertSame('', NavigationLinkHelper::decodeInternalReturn('https://example.net/path'));
    }
}
