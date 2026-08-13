<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use CB\Component\Contentbuilderng\Administrator\Service\MigrationService;
use PHPUnit\Framework\TestCase;

final class ClassicListMenuMigrationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 4) . '/admin/src/Service/MigrationService.php';
    }

    public function testClassicMenuWithoutLegacyValuesMigratesSilently(): void
    {
        $result = MigrationService::modernizeClassicListMenu([
            'id' => 42,
            'title' => 'Members',
            'link' => 'index.php?option=com_contentbuilderng&view=list&layout=listclassic&id=15',
            'params' => json_encode([
                'form_id' => 15,
                'cb_list_filterhidden' => '',
                'cb_list_orderhidden' => '',
                'settings' => ['cb_list_filter' => ''],
            ], JSON_THROW_ON_ERROR),
        ]);

        self::assertIsArray($result);
        self::assertSame(42, $result['id']);
        self::assertSame(
            'index.php?option=com_contentbuilderng&view=list&layout=default&id=15',
            $result['link']
        );
        self::assertFalse($result['hadLegacyConfiguration']);
        self::assertSame(['form_id' => 15], json_decode($result['params'], true, 512, JSON_THROW_ON_ERROR));
    }

    public function testClassicMenuWithLegacyValuesIsReportedAndCleaned(): void
    {
        $result = MigrationService::modernizeClassicListMenu([
            'id' => 84,
            'title' => 'Private members',
            'link' => 'index.php?option=com_contentbuilderng&view=list&layout=listclassic&id=15',
            'params' => json_encode([
                'form_id' => 15,
                'cb_list_filterhidden' => "9\tRoute 1*",
                'cb_list_orderhidden' => "9\t1",
                'cb_list_filter' => 'legacy-widget',
                'settings' => [
                    'cb_list_filterhidden' => "12\tGilles*",
                    'unrelated' => 'kept',
                ],
            ], JSON_THROW_ON_ERROR),
        ]);

        self::assertIsArray($result);
        self::assertTrue($result['hadLegacyConfiguration']);
        self::assertSame(
            ['form_id' => 15, 'settings' => ['unrelated' => 'kept']],
            json_decode($result['params'], true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function testUnrelatedMenuIsNotModified(): void
    {
        self::assertNull(MigrationService::modernizeClassicListMenu([
            'id' => 7,
            'title' => 'Cards',
            'link' => 'index.php?option=com_contentbuilderng&view=list&layout=listcard&id=15',
            'params' => '{}',
        ]));
    }

    public function testMalformedMenuParametersAreNeverOverwritten(): void
    {
        self::assertNull(MigrationService::modernizeClassicListMenu([
            'id' => 8,
            'title' => 'Broken parameters',
            'link' => 'index.php?option=com_contentbuilderng&view=list&layout=listclassic&id=15',
            'params' => '{invalid-json',
        ]));
    }

    public function testInstallerRunsMigrationAndRemovesInstalledClassicFiles(): void
    {
        $root = dirname(__DIR__, 4);
        $installer = (string) file_get_contents($root . '/script.php');
        $service = (string) file_get_contents($root . '/admin/src/Service/InstallerService.php');

        self::assertStringContainsString('$this->migrateClassicListMenusToModernListView();', $installer);
        self::assertStringContainsString('COM_CONTENTBUILDERNG_INSTALLER_CLASSIC_LIST_MIGRATION_WARNING', $installer);
        self::assertStringContainsString("'/components/com_contentbuilderng/tmpl/list/listclassic.php'", $service);
        self::assertStringContainsString("'/components/com_contentbuilderng/src/Field/CbfilterField.php'", $service);
    }

    public function testMigrationWarningExistsInEveryAdministratorLanguage(): void
    {
        $root = dirname(__DIR__, 4);

        foreach (['en-GB', 'fr-FR', 'de-DE'] as $tag) {
            $translations = parse_ini_file($root . '/admin/language/' . $tag . '/com_contentbuilderng.ini');

            self::assertIsArray($translations);
            self::assertArrayHasKey('COM_CONTENTBUILDERNG_INSTALLER_CLASSIC_LIST_MIGRATION_WARNING', $translations);
            self::assertSame(
                1,
                substr_count($translations['COM_CONTENTBUILDERNG_INSTALLER_CLASSIC_LIST_MIGRATION_WARNING'], '%s')
            );
        }
    }
}
