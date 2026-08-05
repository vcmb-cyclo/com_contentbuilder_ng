<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Manifest;

use DateTimeImmutable;
use DateTimeZone;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

final class VersionConsistencyTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = \dirname(__DIR__, 4);
    }

    public function testInstallAndUpdateVersionsRespectLocalRcPolicy(): void
    {
        $installVersion = $this->readValue(
            $this->root . '/com_contentbuilderng.xml',
            '/extension/version'
        );
        $updateVersion = $this->readValue(
            $this->root . '/com_contentbuilderng_update.xml',
            '/updates/update/version'
        );

        self::assertMatchesRegularExpression(
            '/^\d+\.\d+\.\d+(?:-[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*)?$/',
            $installVersion
        );
        $expectedUpdateVersion = preg_replace('/-RC\d{2}$/', '', $installVersion);

        self::assertIsString($expectedUpdateVersion);
        self::assertSame($expectedUpdateVersion, $updateVersion);

        $assetManifest = json_decode(
            (string) file_get_contents($this->root . '/media/joomla.asset.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        self::assertSame($installVersion, $assetManifest['version'] ?? null);

        $downloadUrl = $this->readValue(
            $this->root . '/com_contentbuilderng_update.xml',
            '/updates/update/downloads/downloadurl'
        );

        self::assertSame(
            'https://github.com/vcmb-cyclo/com_contentbuilderng/releases/download/v'
                . $updateVersion
                . '/com_contentbuilderng-'
                . $updateVersion
                . '.zip',
            $downloadUrl
        );

        $expectedChangelogUrl = 'https://raw.githubusercontent.com/vcmb-cyclo/com_contentbuilderng/main/'
            . 'com_contentbuilderng_changelog.xml';

        self::assertSame(
            $expectedChangelogUrl,
            $this->readValue($this->root . '/com_contentbuilderng.xml', '/extension/changelogurl')
        );
        self::assertSame(
            $expectedChangelogUrl,
            $this->readValue($this->root . '/com_contentbuilderng_update.xml', '/updates/update/changelogurl')
        );
        self::assertSame(
            $installVersion,
            $this->readValue($this->root . '/com_contentbuilderng_changelog.xml', '/changelogs/changelog[1]/version')
        );
    }

    public function testInstallCreationDateIsToday(): void
    {
        $creationDate = $this->readValue(
            $this->root . '/com_contentbuilderng.xml',
            '/extension/creationDate'
        );
        $today = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));

        self::assertSame($today->format('Y-m-d'), $creationDate);
    }

    private function readValue(string $path, string $expression): string
    {
        $document = new DOMDocument();
        self::assertTrue($document->load($path), 'Unable to load XML file: ' . $path);

        $value = (new DOMXPath($document))->evaluate('string(' . $expression . ')');
        self::assertNotSame('', $value, 'Missing XML value: ' . $expression);

        return $value;
    }
}
