<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\EmbeddedListFieldFilterService;
use CB\Plugin\Content\ContentbuilderngList\Service\EmbedOptionsService;
use CB\Plugin\Content\ContentbuilderngList\Service\TagSyntaxService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 4)
    . '/plugins/content/contentbuilderng_cblist/src/Service/TagSyntaxService.php';
require_once \dirname(__DIR__, 4)
    . '/plugins/content/contentbuilderng_cblist/src/Service/EmbedOptionsService.php';
require_once \dirname(__DIR__, 4)
    . '/site/src/Service/EmbeddedListFieldFilterService.php';

final class CbListPluginTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testTagAttributesAndOptionsAreResolved(): void
    {
        $attributes = TagSyntaxService::parseAttributes(
            ' id=25 itemid=142 layout=cards fields="Name, Email, Town" height=700 loading=eager title="Registrations &amp; payments"'
        );

        self::assertSame(
            [
                'id' => 25,
                'itemid' => 142,
                'height' => 700,
                'layout' => 'cards',
                'loading' => 'eager',
                'fields' => ['Name', 'Email', 'Town'],
                'title' => 'Registrations & payments',
            ],
            EmbedOptionsService::resolve($attributes)
        );
    }

    public function testDefaultsAreStable(): void
    {
        self::assertSame(
            [
                'id' => 7,
                'itemid' => 0,
                'height' => 640,
                'layout' => '',
                'loading' => 'lazy',
                'fields' => [],
                'title' => '',
            ],
            EmbedOptionsService::resolve(['id' => '7'])
        );
    }

    #[DataProvider('invalidOptionsProvider')]
    public function testInvalidOptionsAreRejected(array $attributes): void
    {
        $this->expectException(\InvalidArgumentException::class);

        EmbedOptionsService::resolve($attributes);
    }

    public static function invalidOptionsProvider(): array
    {
        return [
            'missing id' => [[]],
            'non-positive id' => [['id' => '0']],
            'mixed id' => [['id' => '25foo']],
            'invalid itemid' => [['id' => '25', 'itemid' => '-1']],
            'height too small' => [['id' => '25', 'height' => '239']],
            'height too large' => [['id' => '25', 'height' => '5001']],
            'invalid layout' => [['id' => '25', 'layout' => '../default']],
            'invalid loading' => [['id' => '25', 'loading' => 'automatic']],
            'invalid field control character' => [['id' => '25', 'fields' => "Name,\x01Email"]],
        ];
    }

    public function testEmbeddedFieldFilterOnlyReducesVisibleColumns(): void
    {
        self::assertSame(
            [12, 14, 15],
            EmbeddedListFieldFilterService::filter(
                [11, 12, 13, 14, 15],
                [
                    11 => 'Identifier',
                    12 => 'Nom',
                    13 => 'Téléphone',
                    14 => 'Courriel',
                    15 => 'Ville',
                    99 => 'Champ masqué',
                ],
                [
                    11 => 'id',
                    12 => 'name',
                    13 => 'phone',
                    14 => 'email',
                    15 => 'town',
                    99 => 'hidden',
                ],
                'Nom, EMAIL, 15, hidden'
            )
        );
    }

    public function testEmbeddedFieldFilterPreservesViewOrder(): void
    {
        self::assertSame(
            ['town', 'name'],
            EmbeddedListFieldFilterService::filter(
                ['town', 'email', 'name'],
                ['town' => 'Town', 'email' => 'Email', 'name' => 'Name'],
                ['town' => 'town', 'email' => 'email', 'name' => 'name'],
                'Name,Town'
            )
        );
    }

    public function testPluginIsBundledAndInstalled(): void
    {
        $installer = \file_get_contents(
            self::ROOT . '/admin/src/Service/PluginInstallerService.php'
        );
        $validator = \file_get_contents(self::ROOT . '/scripts/validate-package.sh');

        self::assertIsString($installer);
        self::assertIsString($validator);
        self::assertStringContainsString("'contentbuilderng_cblist'", $installer);
        self::assertStringContainsString(
            'plugins/content/contentbuilderng_cblist/contentbuilderng_cblist.xml',
            $validator
        );
    }

    #[DataProvider('languageProvider')]
    public function testTranslationsAreComplete(string $language): void
    {
        $translations = \parse_ini_file(
            self::ROOT
            . '/plugins/content/contentbuilderng_cblist/language/'
            . $language
            . '/plg_content_contentbuilderng_cblist.ini'
        );
        self::assertIsArray($translations);

        foreach (
            [
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_XML_DESCRIPTION',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_TEXT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_INVALID_TAG',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_IFRAME_TITLE',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_OPEN_LIST',
            ] as $key
        ) {
            self::assertArrayHasKey($key, $translations, $language . ': ' . $key);
            self::assertNotSame('', $translations[$key], $language . ': ' . $key);
        }
    }

    public static function languageProvider(): array
    {
        return [
            'English' => ['en-GB'],
            'French' => ['fr-FR'],
            'German' => ['de-DE'],
        ];
    }
}
