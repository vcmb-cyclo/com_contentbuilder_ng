<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilder_ng\Tests\Unit\Helper;

use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderLegacyHelper;
use Joomla\CMS\Log\Log;
use PHPUnit\Framework\TestCase;

final class ContentbuilderLegacyHelperTest extends TestCase
{
    public function testEncodeDecodePackedDataRoundTrip(): void
    {
        $payload = ['a' => 1, 'b' => ['x' => true], 'text' => 'ok'];

        $encoded = ContentbuilderLegacyHelper::encodePackedData($payload);
        $decoded = ContentbuilderLegacyHelper::decodePackedData($encoded, [], true);

        self::assertSame($payload, $decoded);
    }

    public function testDecodePackedDataLegacySerializedFallback(): void
    {
        $legacy = \base64_encode(\serialize(['legacy' => 'yes']));

        $decoded = ContentbuilderLegacyHelper::decodePackedData($legacy, [], true);

        self::assertSame(['legacy' => 'yes'], $decoded);
    }

    public function testDecodePackedDataLegacySerializedStdClassCompatibility(): void
    {
        $legacyObject = new \stdClass();
        $legacyObject->allow_html = true;
        $legacyObject->allow_raw = false;
        $legacy = \base64_encode(\serialize($legacyObject));

        $decodedObject = ContentbuilderLegacyHelper::decodePackedData($legacy, null, false);
        self::assertInstanceOf(\stdClass::class, $decodedObject);
        self::assertTrue((bool) $decodedObject->allow_html);
        self::assertFalse((bool) $decodedObject->allow_raw);

        $decodedAssoc = ContentbuilderLegacyHelper::decodePackedData($legacy, [], true);
        self::assertSame(
            ['allow_html' => true, 'allow_raw' => false],
            $decodedAssoc
        );
    }

    public function testDecodePackedDataReturnsDefaultOnInvalidPayload(): void
    {
        $decoded = ContentbuilderLegacyHelper::decodePackedData('not_base64', ['default' => 1], true);

        self::assertSame(['default' => 1], $decoded);
    }

    public function testSanitizeHiddenFilterValueBlocksLegacyPhpExpressions(): void
    {
        Log::$entries = [];

        $sanitized = ContentbuilderLegacyHelper::sanitizeHiddenFilterValue('$value = phpinfo();');

        self::assertSame('', $sanitized);
        self::assertNotEmpty(Log::$entries);
    }

    public function testSanitizeHiddenFilterValueReplacesKnownTokens(): void
    {
        $sanitized = ContentbuilderLegacyHelper::sanitizeHiddenFilterValue(
            'id={userid};user={username};name={name};date={date};time={time};dt={datetime}'
        );

        self::assertStringContainsString('id=42', $sanitized);
        self::assertStringContainsString('user=unit_user', $sanitized);
        self::assertStringContainsString('name=Unit User', $sanitized);
        self::assertStringContainsString('date=2026-02-17 12:00:00', $sanitized);
        self::assertStringContainsString('time=12:00:00', $sanitized);
        self::assertStringContainsString('dt=2026-02-17 12:00:00', $sanitized);
    }

    public function testMakeSafeFolderRemovesTraversalSegments(): void
    {
        $safe = ContentbuilderLegacyHelper::makeSafeFolder('/../../tmp/../uploads/./cb');

        self::assertSame('/tmp/uploads/cb', $safe);
    }

    public function testExecPhpValueUsesSafeSanitization(): void
    {
        $result = ContentbuilderLegacyHelper::execPhpValue('$value = "RUN";');

        self::assertSame('', $result);
    }
}
