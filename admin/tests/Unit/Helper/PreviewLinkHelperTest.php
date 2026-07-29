<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Helper;

use CB\Component\Contentbuilderng\Site\Helper\PreviewLinkHelper;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 4) . '/site/src/Helper/PreviewLinkHelper.php';

final class PreviewLinkHelperTest extends TestCase
{
    public function testBuildsSignaturePayloadInExpectedOrder(): void
    {
        self::assertSame(
            'index.php?option=com_contentbuilderng|1234567890|42|Jane Doe|7',
            PreviewLinkHelper::buildPayload(
                'index.php?option=com_contentbuilderng',
                1234567890,
                42,
                'Jane Doe',
                7
            )
        );
    }

    public function testBuildsEncodedPreviewQuery(): void
    {
        self::assertSame(
            '&cb_preview=1'
            . '&cb_preview_until=1234567890'
            . '&cb_preview_actor_id=42'
            . '&cb_preview_actor_name=Jane%20Doe'
            . '&cb_preview_user_id=7'
            . '&cb_preview_sig=a%2Bb%2Fc%3D'
            . '&cb_admin_return=index.php%3Foption%3Dcom_contentbuilderng%26view%3Dforms',
            PreviewLinkHelper::buildQuery(
                1234567890,
                42,
                'Jane Doe',
                7,
                'a+b/c=',
                'index.php?option=com_contentbuilderng&view=forms'
            )
        );
    }

    public function testBuildsQueryWithoutOptionalAdminReturn(): void
    {
        $query = PreviewLinkHelper::buildQuery(1234567890, 42, 'Jane Doe', 7, 'signature');

        self::assertStringContainsString('&cb_preview_sig=signature', $query);
        self::assertStringNotContainsString('cb_admin_return', $query);
    }

    public function testRejectsIncompletePreviewQuery(): void
    {
        self::assertSame('', PreviewLinkHelper::buildQuery(0, 42, 'Jane Doe', 7, 'signature'));
        self::assertSame('', PreviewLinkHelper::buildQuery(1234567890, 42, 'Jane Doe', 7, ''));
    }

    public function testBuildsEscapedPreviewHiddenFields(): void
    {
        $fields = PreviewLinkHelper::buildHiddenFields(
            1234567890,
            42,
            'Jane "Admin" <admin@example.test>',
            7,
            'signature&value',
            'index.php?option=com_contentbuilderng&view=forms'
        );

        self::assertStringContainsString('name="cb_preview" value="1"', $fields);
        self::assertStringContainsString('name="cb_preview_until" value="1234567890"', $fields);
        self::assertStringContainsString('name="cb_preview_actor_id" value="42"', $fields);
        self::assertStringContainsString(
            'name="cb_preview_actor_name" value="Jane &quot;Admin&quot; &lt;admin@example.test&gt;"',
            $fields
        );
        self::assertStringContainsString('name="cb_preview_user_id" value="7"', $fields);
        self::assertStringContainsString('name="cb_preview_sig" value="signature&amp;value"', $fields);
        self::assertStringContainsString(
            'name="cb_admin_return" value="index.php?option=com_contentbuilderng&amp;view=forms"',
            $fields
        );
    }

    public function testBuildsHiddenFieldsWithoutOptionalAdminReturn(): void
    {
        $fields = PreviewLinkHelper::buildHiddenFields(1234567890, 42, 'Jane Doe', 7, 'signature');

        self::assertStringContainsString('name="cb_preview_sig" value="signature"', $fields);
        self::assertStringNotContainsString('cb_admin_return', $fields);
    }

    public function testRejectsIncompletePreviewHiddenFields(): void
    {
        self::assertSame('', PreviewLinkHelper::buildHiddenFields(0, 42, 'Jane Doe', 7, 'signature'));
        self::assertSame('', PreviewLinkHelper::buildHiddenFields(1234567890, 42, 'Jane Doe', 7, ''));
    }
}
