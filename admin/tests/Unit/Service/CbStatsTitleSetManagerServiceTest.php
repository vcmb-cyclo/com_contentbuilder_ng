<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use CB\Component\Contentbuilderng\Administrator\Service\CbStatsTitleSetManagerService;
use CB\Component\Contentbuilderng\Site\Service\CbStatsTitleSetService;
use PHPUnit\Framework\TestCase;

final class CbStatsTitleSetManagerServiceTest extends TestCase
{
    private string $root;
    private CbStatsTitleSetManagerService $service;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/cbng-titleset-manager-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/' . CbStatsTitleSetService::PROVIDED_DIRECTORY, 0777, true);
        $this->service = new CbStatsTitleSetManagerService($this->root);
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

    public function testSavesLoadsAndListsStructuredCustomFile(): void
    {
        $filename = $this->service->save([
            'filename' => 'countries-fr-FR.ini',
            'name' => 'Pays',
            'description' => 'Exemple',
            'locale' => 'fr-FR',
            'version' => '1.0',
            'author' => 'Gilou',
            'comments' => "Premier commentaire\nSecond commentaire",
            'titles' => [
                ['value' => 'fr', 'label' => 'France'],
                ['value' => 'be', 'label' => 'Belgique'],
            ],
        ]);

        self::assertSame('countries-fr-FR.ini', $filename);
        $loaded = $this->service->load($filename, 'custom');
        self::assertSame('Pays', $loaded['name']);
        self::assertSame('Premier commentaire' . "\n" . 'Second commentaire', $loaded['comments']);
        self::assertSame('Belgique', $loaded['titles'][1]['label']);
        self::assertSame('custom', $this->service->listFiles()[0]['source']);
        self::assertArrayHasKey('modified', $this->service->listFiles()[0]);
        self::assertStringNotContainsString('locale=', $this->service->getFileContents($filename, 'custom'));
    }

    public function testRejectsInvalidFilenameAndEmptyMappings(): void
    {
        self::assertFalse($this->service->validate([
            'filename' => '../bad.ini',
            'titles' => [['value' => '', 'label' => '']],
        ])['valid']);

        self::assertFalse($this->service->validate([
            'filename' => 'bad-mapping.ini',
            'titles' => [['value' => 'a=b', 'label' => 'Invalid key']],
        ])['valid']);
    }

    public function testAddsIniExtensionWhenItIsOmitted(): void
    {
        $filename = $this->service->save([
            'filename' => 'vcmb-groupes',
            'name' => 'Groupes VCMB',
            'titles' => [['value' => 'route', 'label' => 'Route']],
        ]);

        self::assertSame('vcmb-groupes.ini', $filename);
        self::assertTrue($this->service->validate([
            'filename' => 'vcmb-groupes',
            'name' => 'Groupes VCMB',
            'titles' => [['value' => 'route', 'label' => 'Route']],
        ])['valid']);
        self::assertFileExists(
            $this->root . '/' . CbStatsTitleSetService::CUSTOM_DIRECTORY . '/vcmb-groupes.ini'
        );
    }

    public function testSavesAndLoadsMappingKeysContainingParentheses(): void
    {
        $filename = $this->service->save([
            'filename' => 'groupes.ini',
            'name' => 'Groupes',
            'titles' => [['value' => 'VCMB (78)', 'label' => 'Vélo Club de Montigny']],
        ]);

        self::assertSame('VCMB (78)', $this->service->load($filename, 'custom')['titles'][0]['value']);
    }

    public function testSavesLoadsAndListsReusableValueGroups(): void
    {
        $filename = $this->service->save([
            'filename' => 'ages.ini',
            'name' => 'Groupes de valeurs',
            'type' => 'groups',
            'titles' => [
                ['value' => '13-', 'label' => 'Moins de 13 ans'],
                ['value' => '13-17', 'label' => '13 à 17 ans'],
                ['value' => '70+', 'label' => '70 ans et plus'],
                ['value' => '1,2,7,9', 'label' => 'Groupe 1'],
            ],
        ]);
        $loaded = $this->service->load($filename, 'custom');
        self::assertSame('groups', $loaded['type']);
        self::assertSame('13-', $loaded['titles'][0]['value']);
        self::assertSame('groups', $this->service->listFiles()[0]['type']);
        self::assertStringContainsString('[groups]', $this->service->getFileContents($filename, 'custom'));
    }
    public function testSaveCopyUsesTheNextAvailableFilename(): void
    {
        $data = [
            'filename' => 'countries.ini',
            'name' => 'Countries',
            'titles' => [['value' => 'fr', 'label' => 'France']],
        ];

        self::assertSame('countries-copy.ini', $this->service->saveCopy($data));
        self::assertSame('countries-copy-2.ini', $this->service->saveCopy($data));
    }

    public function testImportsValidIniWithoutOverwritingAndExportsItsContents(): void
    {
        $source = $this->root . '/upload.ini';
        file_put_contents($source, "[metadata]\nname=\"Import\"\n[titles]\nfr=\"France\"\n");

        self::assertSame('imported.ini', $this->service->importFile($source, 'imported.ini'));
        self::assertStringContainsString(
            'fr="France"',
            $this->service->getFileContents('imported.ini', 'custom')
        );

        $this->expectException(\RuntimeException::class);
        $this->service->importFile($source, 'imported.ini');
    }

    public function testImportCanReplaceAnExistingCustomFileWhenExplicitlyConfirmed(): void
    {
        $first = $this->root . '/first.ini';
        $replacement = $this->root . '/replacement.ini';
        file_put_contents($first, "[metadata]\nname=First\n[titles]\nfr=France\n");
        file_put_contents($replacement, "[metadata]\nname=Replacement\n[titles]\nbe=Belgique\n");

        $this->service->importFile($first, 'countries.ini');
        self::assertSame('countries.ini', $this->service->importFile($replacement, 'countries.ini', true));

        $loaded = $this->service->load('countries.ini', 'custom');
        self::assertSame('Replacement', $loaded['name']);
        self::assertSame('be', $loaded['titles'][0]['value']);
    }

    public function testMissingPostedTitleCannotBlockSaveOrCopy(): void
    {
        $data = [
            'filename' => 'groups.ini',
            'name' => '',
            'titles' => [['value' => 'road', 'label' => 'Road']],
        ];

        self::assertSame('groups.ini', $this->service->save($data));
        self::assertSame('groups', $this->service->load('groups.ini', 'custom')['name']);
        self::assertSame('groups-copy.ini', $this->service->saveCopy($data));
    }

    public function testDeletesOnlyCustomFiles(): void
    {
        $this->service->save([
            'filename' => 'delete.ini',
            'name' => 'Delete test',
            'titles' => [['value' => 'fr', 'label' => 'France']],
        ]);
        $this->service->delete('delete.ini');

        self::assertFileDoesNotExist(
            $this->root . '/' . CbStatsTitleSetService::CUSTOM_DIRECTORY . '/delete.ini'
        );
    }
}
