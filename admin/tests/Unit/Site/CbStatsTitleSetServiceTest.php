<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Site;

use CB\Component\Contentbuilderng\Site\Service\CbStatsTitleSetService;
use PHPUnit\Framework\TestCase;

final class CbStatsTitleSetServiceTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/cbng-titleset-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/' . CbStatsTitleSetService::CUSTOM_DIRECTORY, 0777, true);
        mkdir($this->root . '/' . CbStatsTitleSetService::PROVIDED_DIRECTORY, 0777, true);
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

    public function testLoadsMetadataCommentsAndUnicodeTitles(): void
    {
        $this->writeCustom('departements-fr-FR.ini', <<<'INI'
; Exemple français
[metadata]
name="Départements français"
locale="fr-FR"
[titles]
78="Yvelines (78)"
79="Deux-Sèvres (79)"
INI);

        $result = (new CbStatsTitleSetService($this->root))->resolve('departements-fr-FR.ini');

        self::assertSame('ok', $result['status']);
        self::assertSame('custom', $result['source']);
        self::assertSame('Départements français', $result['metadata']['name']);
        self::assertSame('Exemple français', $result['comments']);
        self::assertSame('Deux-Sèvres (79)', $result['titles']['79']);
    }

    public function testCustomFileTakesPriorityOverProvidedFile(): void
    {
        $this->writeProvided('countries.ini', "[titles]\nfr=France fournie\n");
        $this->writeCustom('countries.ini', "[titles]\nfr=France personnalisée\n");

        $result = (new CbStatsTitleSetService($this->root))->resolve('countries.ini');

        self::assertSame('custom', $result['source']);
        self::assertSame('France personnalisée', $result['titles']['fr']);
    }

    public function testMissingInvalidAndEmptyFilesFailSilently(): void
    {
        $service = new CbStatsTitleSetService($this->root);
        self::assertSame('not_found', $service->resolve('missing.ini')['status']);
        self::assertSame('invalid_name', $service->resolve('../secret.ini')['status']);

        $this->writeCustom('empty.ini', '');
        self::assertSame('empty', $service->resolve('empty.ini')['status']);

        $this->writeCustom('invalid.ini', "[titles\nfr=France\n");
        self::assertSame('invalid_syntax', $service->resolve('invalid.ini')['status']);
    }

    public function testMissingTitlesAndInvalidEntriesAreReported(): void
    {
        $this->writeCustom('metadata.ini', "[metadata]\nname=Only metadata\n");
        self::assertSame(
            'missing_titles',
            (new CbStatsTitleSetService($this->root))->resolve('metadata.ini')['status']
        );

        $this->writeCustom('partial.ini', "[titles]\nfr=France\nbe=\"\"\nde=Allemagne\n");
        $result = (new CbStatsTitleSetService($this->root))->resolve('partial.ini');
        self::assertSame('invalid_entries', $result['status']);
        self::assertSame(['fr' => 'France', 'de' => 'Allemagne'], $result['titles']);
        self::assertSame(1, $result['invalidEntries']);
    }

    public function testInlineTitlesTakePriorityOverTitleSetMappings(): void
    {
        self::assertSame(
            ['78' => 'Yvelines', '75' => 'Paris (75)'],
            CbStatsTitleSetService::merge(
                ['78' => 'Yvelines (78)', '75' => 'Paris (75)'],
                ['78' => 'Yvelines']
            )
        );
    }

    public function testProvidedDepartmentAndCountryExamplesAreValid(): void
    {
        $directory = self::PROJECT_ROOT . '/media/cbstats/titlesets/';
        $departments = CbStatsTitleSetService::parseFile($directory . 'departements-fr-FR.ini');

        self::assertSame('ok', $departments['status']);
        self::assertSame('Yvelines (78)', $departments['titles']['78']);
        self::assertGreaterThanOrEqual(100, count($departments['titles']));

        foreach (['example-en-GB.ini', 'exemple-fr-FR.ini', 'beispiel-de-DE.ini'] as $filename) {
            $result = CbStatsTitleSetService::parseFile($directory . $filename);
            self::assertSame('ok', $result['status'], $filename);
            self::assertArrayHasKey('fr', $result['titles']);
            self::assertArrayHasKey('de', $result['titles']);
        }
    }

    private function writeCustom(string $filename, string $contents): void
    {
        file_put_contents($this->root . '/' . CbStatsTitleSetService::CUSTOM_DIRECTORY . '/' . $filename, $contents);
    }

    private function writeProvided(string $filename, string $contents): void
    {
        file_put_contents($this->root . '/' . CbStatsTitleSetService::PROVIDED_DIRECTORY . '/' . $filename, $contents);
    }
}
