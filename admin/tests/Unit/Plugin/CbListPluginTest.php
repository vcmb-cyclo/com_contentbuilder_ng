<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\EmbeddedListActionFilterService;
use CB\Component\Contentbuilderng\Site\Service\EmbeddedListContextService;
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
require_once \dirname(__DIR__, 4)
    . '/site/src/Service/EmbeddedListActionFilterService.php';
require_once \dirname(__DIR__, 4)
    . '/site/src/Service/EmbeddedListContextService.php';

final class CbListPluginTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testTagAttributesAndOptionsAreResolved(): void
    {
        $attributes = TagSyntaxService::parseAttributes(
            ' id=25 layout=cards pagination=25 fields="Name|Email|Town" actions="search|detail|export" height=700 loading=eager title="Registrations &amp; payments"'
        );

        self::assertSame(
            [
                'id' => 25,
                'height' => 700,
                'pagination' => 25,
                'layout' => 'cards',
                'loading' => 'eager',
                'fields' => ['Name', 'Email', 'Town'],
                'actions' => ['search', 'detail', 'export'],
                'title' => 'Registrations & payments',
                'title_set' => true,
            ],
            EmbedOptionsService::resolve($attributes)
        );
    }

    public function testDefaultsAreStable(): void
    {
        self::assertSame(
            [
                'id' => 7,
                'height' => 240,
                'pagination' => null,
                'layout' => '',
                'loading' => 'lazy',
                'fields' => [],
                'actions' => [],
                'title' => '',
                'title_set' => false,
            ],
            EmbedOptionsService::resolve(['id' => '7'])
        );
    }

    public function testTagPatternAllowsClosingBraceInsideQuotedTitle(): void
    {
        $tag = '{CBList id=25 title="Registrations } archived"}';

        self::assertSame(1, preg_match(TagSyntaxService::TAG_PATTERN, $tag, $matches));
        self::assertSame($tag, $matches[0]);
        self::assertSame(
            'Registrations } archived',
            TagSyntaxService::parseAttributes((string) $matches[1])['title']
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
            'height too small' => [['id' => '25', 'height' => '239']],
            'height too large' => [['id' => '25', 'height' => '5001']],
            'invalid layout' => [['id' => '25', 'layout' => '../default']],
            'invalid loading' => [['id' => '25', 'loading' => 'automatic']],
            'fields comma separator rejected' => [['id' => '25', 'fields' => 'Name,Email']],
            'fields semicolon separator rejected' => [['id' => '25', 'fields' => 'Name;Email']],
            'invalid field control character' => [['id' => '25', 'fields' => "Name|\x01Email"]],
            'actions comma separator rejected' => [['id' => '25', 'actions' => 'search,export']],
            'actions semicolon separator rejected' => [['id' => '25', 'actions' => 'search;export']],
            'unknown action' => [['id' => '25', 'actions' => 'search|teleport']],
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
                'Nom| EMAIL|15'
            )
        );
    }

    public function testEmbeddedFieldFilterRejectsUnknownField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown_field:Missing');

        EmbeddedListFieldFilterService::filter(
            [11],
            [11 => 'Identifier'],
            [11 => 'id'],
            'Missing'
        );
    }

    public function testEmbeddedFieldFilterPreservesSelectorOrder(): void
    {
        self::assertSame(
            ['name', 'town'],
            EmbeddedListFieldFilterService::filter(
                ['town', 'email', 'name'],
                ['town' => 'Town', 'email' => 'Email', 'name' => 'Name'],
                ['town' => 'town', 'email' => 'email', 'name' => 'name'],
                'Name|Town'
            )
        );
    }

    public function testFieldSelectorsRejectCommaAndSemicolon(): void
    {
        foreach (['Name,Email', 'Name;Email'] as $rawSelectors) {
            try {
                EmbeddedListFieldFilterService::parseSelectors($rawSelectors);
                self::fail('Expected InvalidArgumentException for: ' . $rawSelectors);
            } catch (\InvalidArgumentException $exception) {
                self::assertSame('fields', $exception->getMessage());
            }
        }
    }

    public function testActionSelectorsAreParsedAndDeduplicated(): void
    {
        self::assertSame(
            ['search', 'detail', 'export'],
            EmbeddedListActionFilterService::parseActions('Search|Detail|EXPORT|detail')
        );
        self::assertSame([], EmbeddedListActionFilterService::parseActions(''));
    }

    public function testActionSelectorsRejectCommaAndSemicolon(): void
    {
        foreach (['search,export', 'search;export'] as $rawActions) {
            try {
                EmbeddedListActionFilterService::parseActions($rawActions);
                self::fail('Expected InvalidArgumentException for: ' . $rawActions);
            } catch (\InvalidArgumentException $exception) {
                self::assertSame('actions', $exception->getMessage());
            }
        }
    }

    public function testKnownActionsCoverTheExhaustiveVocabulary(): void
    {
        self::assertSame(
            [
                'search',
                'state',
                'publish',
                'language',
                'new',
                'edit',
                'delete',
                'export',
                'rating',
                'detail',
                'print',
            ],
            EmbeddedListActionFilterService::ACTIONS
        );

        foreach (EmbeddedListActionFilterService::ACTIONS as $action) {
            self::assertTrue(EmbeddedListActionFilterService::isKnownAction($action));
        }
        self::assertFalse(EmbeddedListActionFilterService::isKnownAction('teleport'));
    }

    public function testIsAllowedIsUnrestrictedWhenAllowListIsEmpty(): void
    {
        self::assertTrue(EmbeddedListActionFilterService::isAllowed('delete', []));
        self::assertTrue(EmbeddedListActionFilterService::isAllowed('anything', []));
    }

    public function testIsAllowedOnlyAllowsListedActions(): void
    {
        $allowed = ['search', 'detail'];

        self::assertTrue(EmbeddedListActionFilterService::isAllowed('search', $allowed));
        self::assertTrue(EmbeddedListActionFilterService::isAllowed('detail', $allowed));
        self::assertFalse(EmbeddedListActionFilterService::isAllowed('delete', $allowed));
        self::assertFalse(EmbeddedListActionFilterService::isAllowed('edit', $allowed));
    }

    public function testDebugStateReportsOnlyTheRequestedActions(): void
    {
        $state = EmbeddedListActionFilterService::debugState(
            ['search', 'detail', 'delete'],
            ['search', 'detail']
        );

        self::assertSame(
            ['search' => true, 'detail' => true, 'delete' => false],
            $state
        );
    }

    public function testDebugStateIsUnrestrictedWhenAllowListIsEmpty(): void
    {
        $state = EmbeddedListActionFilterService::debugState(['search', 'delete'], []);

        self::assertSame(['search' => true, 'delete' => true], $state);
    }

    public function testEmbeddedRequestRequiresExplicitPublicContext(): void
    {
        self::assertTrue(
            EmbeddedListFieldFilterService::isEmbeddedRequest(
                EmbeddedListFieldFilterService::REQUEST_CONTEXT
            )
        );
        self::assertFalse(EmbeddedListFieldFilterService::isEmbeddedRequest('1'));
        self::assertFalse(EmbeddedListFieldFilterService::isEmbeddedRequest(''));
    }

    public function testEmbeddedContextQueryPreservesFieldsAndActions(): void
    {
        self::assertSame(
            '&cblist_embed=content-plugin&cblist_fields=Name%7CEmail&cblist_actions=detail%7Cedit',
            EmbeddedListContextService::buildQuery(
                EmbeddedListFieldFilterService::REQUEST_CONTEXT,
                'Name|Email',
                'detail|edit'
            )
        );
        self::assertSame(
            '',
            EmbeddedListContextService::buildQuery('invalid-context', 'Name', 'detail')
        );
    }

    public function testEmbeddedContextIsPreservedAcrossMutations(): void
    {
        $listController = \file_get_contents(self::ROOT . '/site/src/Controller/ListController.php');
        $editController = \file_get_contents(self::ROOT . '/site/src/Controller/EditController.php');
        $listTemplate = \file_get_contents(self::ROOT . '/site/tmpl/list/default.php');
        $detailsTemplate = \file_get_contents(self::ROOT . '/site/tmpl/details/default.php');
        $editTemplate = \file_get_contents(self::ROOT . '/site/tmpl/edit/default.php');

        self::assertIsString($listController);
        self::assertIsString($editController);
        self::assertIsString($listTemplate);
        self::assertIsString($detailsTemplate);
        self::assertIsString($editTemplate);
        self::assertStringContainsString('private function buildEmbeddedListQuery(): string', $listController);
        self::assertStringContainsString('private function buildEmbeddedListQuery(): string', $editController);
        self::assertStringContainsString('name="cblist_actions"', $listTemplate);
        self::assertStringContainsString("getString('cblist_fields', '')", $editTemplate);
        self::assertStringContainsString('foreach ($embeddedListParams as $embeddedListName', $detailsTemplate);
        self::assertStringContainsString('foreach ($embeddedListParams as $embeddedListName', $editTemplate);
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
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_FIELDS_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_FIELDS_TEXT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_ACTIONS_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_ACTIONS_TEXT',
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
