<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Site;

use CB\Component\Contentbuilderng\Site\Service\CbStatsConfigService;
use PHPUnit\Framework\TestCase;

final class CbStatsConfigServiceTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/cbng-config-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/' . CbStatsConfigService::CUSTOM_DIRECTORY, 0777, true);
        mkdir($this->root . '/' . CbStatsConfigService::PROVIDED_DIRECTORY, 0777, true);
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->root);
    }

    public function testLoadsTheThreeSupportedSections(): void
    {
        $this->write('custom', 'vcmb.ini', <<<'INI'
[labels]
title="Parcours VCMB"
category=Groupe
value=Inscrits
total="Total des groupes"
[presentation]
background=#eef6f8
card=v1
w=66
width=600
height=400
[display]
hide=none
sort=value
dir=desc
limit=10
INI);

        $result = (new CbStatsConfigService($this->root))->resolve('vcmb.ini');

        self::assertSame('ok', $result['status']);
        self::assertSame('custom', $result['source']);
        self::assertSame('Parcours VCMB', $result['values']['title']);
        self::assertSame('66', $result['values']['w']);
        self::assertSame('600', $result['values']['width']);
        self::assertSame('desc', $result['values']['dir']);
    }

    public function testCustomFileOverridesProvidedFile(): void
    {
        $this->write('provided', 'shared.ini', "[labels]\ntitle=Provided\n");
        $this->write('custom', 'shared.ini', "[labels]\ntitle=Custom\n");

        $result = (new CbStatsConfigService($this->root))->resolve('shared.ini');
        self::assertSame('custom', $result['source']);
        self::assertSame('Custom', $result['values']['title']);
    }

    public function testUnknownSectionsKeysAndDuplicatesAreRejected(): void
    {
        $this->write('custom', 'section.ini', "[data]\nfield=Distance\n");
        $this->write('custom', 'key.ini', "[labels]\nunknown=value\n");
        $this->write('custom', 'duplicate.ini', "[labels]\ntitle=One\ntitle=Two\n");

        $service = new CbStatsConfigService($this->root);
        self::assertSame('unknown_section', $service->resolve('section.ini')['status']);
        self::assertSame('unknown_key', $service->resolve('key.ini')['status']);
        self::assertSame('duplicate_key', $service->resolve('duplicate.ini')['status']);
    }

    public function testInlineValuesOverrideConfigurationKeyByKey(): void
    {
        $merged = CbStatsConfigService::merge(
            ['title' => 'Configured', 'total' => 'Configured total', 'width' => '600', 'card' => 'v1'],
            ['labels' => 'title=Inline', 'width' => '800']
        );

        self::assertSame('title=Inline;total=Configured total', $merged['labels']);
        self::assertSame('800', $merged['width']);
        self::assertSame('v1', $merged['card']);
        self::assertArrayNotHasKey('title', $merged);
        self::assertArrayNotHasKey('category', $merged);
        self::assertArrayNotHasKey('value', $merged);
        self::assertArrayNotHasKey('total', $merged);
    }

    public function testBundledMultilingualExamplesAreValid(): void
    {
        foreach (['example-en-GB.ini', 'exemple-fr-FR.ini', 'beispiel-de-DE.ini'] as $filename) {
            $result = CbStatsConfigService::parseFile(
                self::PROJECT_ROOT . '/media/cbstats/configs/' . $filename
            );
            self::assertSame('ok', $result['status'], $filename);
            self::assertSame('v1', $result['values']['card']);
        }
    }

    private function write(string $source, string $filename, string $contents): void
    {
        $directory = $source === 'custom'
            ? CbStatsConfigService::CUSTOM_DIRECTORY
            : CbStatsConfigService::PROVIDED_DIRECTORY;
        file_put_contents($this->root . '/' . $directory . '/' . $filename, $contents);
    }
}
