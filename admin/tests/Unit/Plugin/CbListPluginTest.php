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
            ' id=15 fields="Nom|Prenom|Email" title="Liste des inscrits" sort="Nom|Prenom" dir="asc" pagination=25 actions="detail|edit|export" layout=cards height=700 loading=lazy'
        );

        self::assertSame(
            [
                'id' => 15,
                'height' => 700,
                'pagination' => 25,
                'layout' => 'listcard',
                'loading' => 'lazy',
                'fields' => ['Nom', 'Prenom', 'Email'],
                'actions' => ['detail', 'edit', 'export'],
                'sort' => 'Nom|Prenom',
                'sort_direction' => 'asc|asc',
                'title' => 'Liste des inscrits',
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
                'sort' => '',
                'sort_direction' => 'asc',
                'title' => '',
                'title_set' => false,
            ],
            EmbedOptionsService::resolve(['id' => '7'])
        );
    }

    public function testEmptyTitleExplicitlyHidesTheVisibleTitle(): void
    {
        $options = EmbedOptionsService::resolve(
            TagSyntaxService::parseAttributes('id=15 title=""')
        );

        self::assertTrue($options['title_set']);
        self::assertSame('', $options['title']);

        $extension = file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cblist/src/Extension/ContentbuilderngList.php'
        );
        self::assertIsString($extension);
        self::assertStringContainsString(
            "\$options['title_set'] && \$options['title'] !== ''",
            $extension,
            'An empty visible title must retain the translated accessible iframe fallback.'
        );

        $template = file_get_contents(self::ROOT . '/site/tmpl/list/default.php');
        self::assertIsString($template);
        self::assertStringContainsString('if ($embeddedListTitleProvided) :', $template);
        self::assertStringContainsString('if ($embeddedListTitle !== \'\') :', $template);
    }

    public function testHideTitleKeywordIsAnAliasForAnEmptyTitle(): void
    {
        foreach (['hide', 'HIDE', ' Hide '] as $value) {
            $options = EmbedOptionsService::resolve(['id' => '15', 'title' => $value]);

            self::assertTrue($options['title_set']);
            self::assertSame('', $options['title']);
        }
    }

    public function testEveryInvalidParameterIsReportedWithItsValue(): void
    {
        self::assertSame(
            [
                [
                    'code' => 'unknown_option',
                    'parameter' => 'headers',
                    'value' => 'Noms',
                    'detail' => '',
                ],
                [
                    'code' => 'invalid_value',
                    'parameter' => 'pagination',
                    'value' => '0',
                    'detail' => 'pagination',
                ],
                [
                    'code' => 'invalid_value',
                    'parameter' => 'loading',
                    'value' => 'automatic',
                    'detail' => 'loading',
                ],
                [
                    'code' => 'unknown_action',
                    'parameter' => 'actions',
                    'value' => 'teleport',
                    'detail' => 'actions',
                ],
                [
                    'code' => 'invalid_value',
                    'parameter' => 'dir',
                    'value' => 'asce',
                    'detail' => 'dir',
                ],
            ],
            EmbedOptionsService::validationErrors([
                'id' => '15',
                'headers' => 'Noms',
                'pagination' => '0',
                'loading' => 'automatic',
                'actions' => 'teleport',
                'dir' => 'asce',
            ])
        );
    }

    public function testSingleSortDirectionAppliesToEverySortField(): void
    {
        self::assertSame(
            'asc|asc|asc',
            EmbedOptionsService::resolve([
                'id' => '15',
                'sort' => 'Field 1|Field 2| Field 3',
                'dir' => 'asc',
            ])['sort_direction']
        );
        self::assertSame(
            'desc|desc|desc',
            EmbedOptionsService::resolve([
                'id' => '15',
                'sort' => 'Field 1|Field 2|Field 3',
                'dir' => 'desc',
            ])['sort_direction']
        );
    }

    public function testTagPatternAllowsClosingBraceInsideQuotedTitle(): void
    {
        $tag = '{CBList id=15 title="Registrations } archived"}';

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
            'height too small' => [['id' => '15', 'height' => '239']],
            'height too large' => [['id' => '15', 'height' => '5001']],
            'invalid layout' => [['id' => '15', 'layout' => '../default']],
            'invalid loading' => [['id' => '15', 'loading' => 'automatic']],
            'fields comma separator rejected' => [['id' => '15', 'fields' => 'Name,Email']],
            'fields semicolon separator rejected' => [['id' => '15', 'fields' => 'Name;Email']],
            'invalid field control character' => [['id' => '15', 'fields' => "Name|\x01Email"]],
            'actions comma separator rejected' => [['id' => '15', 'actions' => 'search,export']],
            'actions semicolon separator rejected' => [['id' => '15', 'actions' => 'search;export']],
            'unknown action' => [['id' => '15', 'actions' => 'search|teleport']],
            'sort direction count mismatch' => [[
                'id' => '15',
                'sort' => 'Field 1|Field 2|Field 3',
                'dir' => 'asc|desc',
            ]],
        ];
    }

    public function testEmbeddedFieldFilterOnlyReducesVisibleColumns(): void
    {
        self::assertSame(
            [12, 14, 15],
            EmbeddedListFieldFilterService::filter(
                [11, 12, 13, 14, 15],
                [
                    11 => 'id',
                    12 => 'name',
                    13 => 'phone',
                    14 => 'email',
                    15 => 'town',
                    99 => 'hidden',
                ],
                'name|email|15'
            )
        );
    }

    public function testEmbeddedFieldFilterRejectsUnknownField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown_field:Missing');

        EmbeddedListFieldFilterService::filter(
            [11],
            [11 => 'id'],
            'Missing'
        );
    }

    public function testEmbeddedFieldFilterAcceptsExistingElementsThatAreNotVisible(): void
    {
        self::assertSame(
            [12, 14],
            EmbeddedListFieldFilterService::filter(
                [12, 14],
                [
                    12 => 'Nom',
                    13 => 'Prenom',
                    14 => 'Email',
                    15 => 'Telephone',
                ],
                'Nom|Prenom|Email|Telephone'
            )
        );

        self::assertSame(
            [
                'columns' => [12, 14],
                'unknown' => ['Adresse'],
            ],
            EmbeddedListFieldFilterService::matchFieldSelectors(
                [12, 14],
                [12 => 'Nom', 13 => 'Prenom', 14 => 'Email', 15 => 'Telephone'],
                'Nom|Prenom|Email|Telephone|Adresse'
            )
        );
    }

    public function testSortMatchingStillRejectsElementsThatAreNotVisible(): void
    {
        self::assertSame(
            [
                'columns' => [12],
                'unknown' => ['Prenom'],
            ],
            EmbeddedListFieldFilterService::matchSelectors(
                [12],
                [12 => 'Nom', 13 => 'Prenom'],
                'Nom|Prenom'
            )
        );
    }

    public function testEmbeddedFieldMatchingKeepsOnlyRequestedValidColumnsAndReportsEveryUnknownOne(): void
    {
        self::assertSame(
            [
                'columns' => [12, 14],
                'unknown' => ['Prénom', 'prenom', 'EMAIL', 'Courriels'],
            ],
            EmbeddedListFieldFilterService::matchSelectors(
                [11, 12, 13, 14],
                [11 => 'Id', 12 => 'Prenom', 13 => 'Telephone', 14 => 'Email'],
                'Prenom|Prénom|prenom|Email|EMAIL|Courriels'
            )
        );
    }

    public function testEmbeddedFieldFilterPreservesSelectorOrder(): void
    {
        self::assertSame(
            ['name', 'town'],
            EmbeddedListFieldFilterService::filter(
                ['town', 'email', 'name'],
                ['town' => 'town', 'email' => 'email', 'name' => 'name'],
                'name|town'
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
        $extension = \file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cblist/src/Extension/ContentbuilderngList.php'
        );

        self::assertIsString($listController);
        self::assertIsString($editController);
        self::assertIsString($listTemplate);
        self::assertIsString($detailsTemplate);
        self::assertIsString($editTemplate);
        self::assertIsString($extension);
        self::assertStringContainsString('private function buildEmbeddedListQuery(): string', $listController);
        self::assertStringContainsString('private function buildEmbeddedListQuery(): string', $editController);
        self::assertStringContainsString('name="cblist_actions"', $listTemplate);
        self::assertStringContainsString('foreach ($this->embedded_list_errors as $embeddedListError)', $listTemplate);
        self::assertStringContainsString(
            'if ($this->embedded_list_errors !== []) { return; }',
            $listTemplate,
            'An invalid embedded CBList must render its errors without rendering the list below them.'
        );
        self::assertStringContainsString('data-cblist-errors-only', $listTemplate);
        self::assertStringContainsString('target="_blank" rel="noopener noreferrer"', $listTemplate);

        $helpService = \file_get_contents(
            self::ROOT . '/site/src/Service/EmbeddedListHelpService.php'
        );
        self::assertIsString($helpService);
        self::assertStringContainsString('task=cblisthelp.display', $helpService);
        self::assertStringNotContainsString("authorise('core.manage', 'com_plugins')", $helpService);
        self::assertStringNotContainsString('administrator/index.php', $helpService);
        self::assertStringContainsString('EmbeddedListHelpService::syntaxUrl()', $extension);

        $publicHelpView = \file_get_contents(
            self::ROOT . '/site/src/View/Cblisthelp/HtmlView.php'
        );
        $publicHelpTemplate = \file_get_contents(
            self::ROOT . '/site/tmpl/cblisthelp/default.php'
        );
        self::assertIsString($publicHelpView);
        self::assertIsString($publicHelpTemplate);
        self::assertStringContainsString('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_TEXT', $publicHelpView);
        self::assertStringContainsString('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_FIELDS_TEXT', $publicHelpView);
        self::assertStringContainsString('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_ACTIONS_TEXT', $publicHelpView);
        self::assertStringContainsString('foreach ($this->sections as $section)', $publicHelpTemplate);

        $embedScript = \file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cblist/media/js/cblist.js'
        );
        self::assertIsString($embedScript);
        self::assertStringContainsString(
            "document.querySelector('[data-cblist-errors-only]')",
            $embedScript,
            'An error-only iframe must shrink to its message instead of keeping the configured minimum height.'
        );
        $heightResetPosition = strpos($embedScript, "frame.style.height = '0px';");
        $heightMeasurementPosition = strpos($embedScript, 'body.scrollHeight');
        self::assertNotFalse(
            $heightResetPosition,
            'The iframe viewport must be reset before measuring a shorter paginated result.'
        );
        self::assertNotFalse($heightMeasurementPosition);
        self::assertLessThan(
            $heightMeasurementPosition,
            $heightResetPosition,
            'The height reset must happen before scrollHeight is measured.'
        );
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
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_VALIDATION_INTRO',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_SYNTAX_HELP',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EMPTY_VALUE',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_UNKNOWN_OPTION',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_INVALID_OPTION_VALUE',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_UNKNOWN_ACTION',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_UNKNOWN_VIEW',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_ID',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_HEIGHT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_PAGINATION',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_LAYOUT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_LOADING',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_FIELDS',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_ACTIONS',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_SORT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_DIR',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_EXPECTED_DIR_COUNT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_IFRAME_TITLE',
                'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_OPEN_LIST',
            ] as $key
        ) {
            self::assertArrayHasKey($key, $translations, $language . ': ' . $key);
            self::assertNotSame('', $translations[$key], $language . ': ' . $key);
        }
    }

    #[DataProvider('languageProvider')]
    public function testAllErrorMessagesAreCompleteAndKeepMatchingPlaceholders(string $language): void
    {
        $pluginTranslations = \parse_ini_file(
            self::ROOT
            . '/plugins/content/contentbuilderng_cblist/language/'
            . $language
            . '/plg_content_contentbuilderng_cblist.ini'
        );
        $siteTranslations = \parse_ini_file(
            self::ROOT . '/site/language/' . $language . '/com_contentbuilderng.ini'
        );
        self::assertIsArray($pluginTranslations);
        self::assertIsArray($siteTranslations);

        $expectedPlaceholders = [
            'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_UNKNOWN_OPTION' => 2,
            'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_INVALID_OPTION_VALUE' => 3,
            'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_UNKNOWN_ACTION' => 1,
            'PLG_CONTENT_CONTENTBUILDERNG_CBLIST_UNKNOWN_VIEW' => 1,
            'COM_CONTENTBUILDERNG_CBLIST_UNKNOWN_FIELD' => 1,
            'COM_CONTENTBUILDERNG_CBLIST_UNKNOWN_SORT_FIELD' => 1,
        ];

        foreach ($expectedPlaceholders as $key => $expectedCount) {
            $translations = str_starts_with($key, 'PLG_') ? $pluginTranslations : $siteTranslations;
            self::assertArrayHasKey($key, $translations, $language . ': ' . $key);
            preg_match_all('/%(?:\d+\$)?[sd]/', (string) $translations[$key], $matches);
            self::assertCount($expectedCount, $matches[0], $language . ': ' . $key);
        }

        foreach (
            [
                'COM_CONTENTBUILDERNG_CBLIST_VALIDATION_INTRO',
                'COM_CONTENTBUILDERNG_CBLIST_SYNTAX_HELP',
                'COM_CONTENTBUILDERNG_CBLIST_INVALID_FIELDS',
            ] as $key
        ) {
            self::assertArrayHasKey($key, $siteTranslations, $language . ': ' . $key);
            self::assertNotSame('', $siteTranslations[$key], $language . ': ' . $key);
        }

        $allErrorText = implode(' ', array_merge($pluginTranslations, $siteTranslations));
        foreach (
            [
                'configured view ordering is used instead',
                'tri configuré dans la vue est utilisé',
                'konfigurierte Sortierung verwendet',
            ] as $obsoleteWording
        ) {
            self::assertStringNotContainsString($obsoleteWording, $allErrorText, $language);
        }
    }

    #[DataProvider('languageProvider')]
    public function testTranslatedHelpExamplesUseValidSyntax(string $language): void
    {
        $translations = \parse_ini_file(
            self::ROOT
            . '/plugins/content/contentbuilderng_cblist/language/'
            . $language
            . '/plg_content_contentbuilderng_cblist.ini'
        );
        self::assertIsArray($translations);

        $help = implode('', [
            $translations['PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_TEXT'],
            $translations['PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_FIELDS_TEXT'],
            $translations['PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_ACTIONS_TEXT'],
        ]);
        self::assertStringNotContainsString('{CBList id=25', $help, $language);
        self::assertStringContainsString('Thoth', $help, $language . ': missing default theme explanation');

        $actionsHelp = $translations['PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_ACTIONS_TEXT'];
        self::assertStringContainsString('<ul>', $actionsHelp, $language . ': actions must be displayed vertically');
        foreach (EmbeddedListActionFilterService::ACTIONS as $action) {
            self::assertStringContainsString('<code>' . $action . '</code>', $actionsHelp, $language);
        }

        preg_match_all(TagSyntaxService::TAG_PATTERN, $help, $matches);
        self::assertNotEmpty($matches[1], $language . ': no documented CBList example found');

        foreach ($matches[1] as $rawAttributes) {
            self::assertSame(
                15,
                EmbedOptionsService::resolve(
                    TagSyntaxService::parseAttributes((string) $rawAttributes)
                )['id'],
                $language . ': invalid documented CBList example'
            );
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
