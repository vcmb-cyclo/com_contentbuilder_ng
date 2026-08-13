<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use CB\Component\Contentbuilderng\Administrator\Service\RuntimeUtilityService;
use CB\Component\Contentbuilderng\Site\Service\MenuDataFilterService;
use PHPUnit\Framework\TestCase;

final class MenuDataFilterServiceTest extends TestCase
{
    public function testUsesOneExplicitRequestParameterName(): void
    {
        self::assertSame('cb_menu_data_filters', MenuDataFilterService::INPUT_NAME);
    }

    public function testEncodeKeepsUnicodeWildcardsAndAllowedReferences(): void
    {
        self::assertSame(
            '{"9":["DAN*","*Émile"]}',
            MenuDataFilterService::encode(['9' => ' DAN* | *Émile ', '12' => 'blocked'], ['9'])
        );
    }

    public function testEmptyAllowedReferenceListRejectsEveryFilter(): void
    {
        self::assertSame('', MenuDataFilterService::encode(['9' => 'DAN*'], []));
    }

    public function testDecodeUsesTheRuntimeFilterSanitizer(): void
    {
        $runtime = $this->getMockBuilder(RuntimeUtilityService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sanitizeDataFilterTerm'])
            ->getMock();
        $runtime->expects(self::exactly(2))
            ->method('sanitizeDataFilterTerm')
            ->willReturnCallback(static fn(string $value): string => trim($value));

        self::assertSame(
            ['9' => ['DAN*', '*Émile']],
            MenuDataFilterService::decode('{"9":["DAN*"," *Émile "]}', $runtime)
        );
    }

    public function testActiveDataFilterCodeDoesNotUseTheLegacyHiddenFilterContract(): void
    {
        $root = \dirname(__DIR__, 4);
        $activeFiles = [
            $root . '/admin/src/Service/RuntimeUtilityService.php',
            $root . '/site/src/Service/MenuDataFilterService.php',
            $root . '/site/src/Dispatcher/Dispatcher.php',
            $root . '/site/src/Helper/MenuListConfigurationHelper.php',
            $root . '/site/src/Model/ListModel.php',
            $root . '/site/src/Model/DetailsModel.php',
            $root . '/site/src/Model/EditModel.php',
            $root . '/site/src/Model/ExportModel.php',
        ];

        foreach ($activeFiles as $file) {
            $source = (string) file_get_contents($file);
            self::assertStringNotContainsString('cb_list_filterhidden', $source, $file);
            self::assertStringNotContainsString('sanitizeHiddenFilterValue', $source, $file);
            self::assertStringNotContainsString('$this->_menu_filter', $source, $file);

            if (!str_ends_with($file, '/site/src/Service/MenuDataFilterService.php')) {
                self::assertStringNotContainsString("'cb_menu_data_filters'", $source, $file);
            }
        }

        $migrationSource = (string) file_get_contents($root . '/admin/src/Service/MigrationService.php');
        self::assertStringContainsString('cb_list_filterhidden', $migrationSource);
    }
}
