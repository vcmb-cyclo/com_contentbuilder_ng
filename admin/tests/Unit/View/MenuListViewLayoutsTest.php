<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use PHPUnit\Framework\TestCase;

final class MenuListViewLayoutsTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = \dirname(__DIR__, 4);
    }

    public function testNewListViewIsDefaultAndClassicUsesDedicatedLayout(): void
    {
        $listView = $this->loadMetadata('default.xml');
        $classic = $this->loadMetadata('listclassic.xml');

        self::assertSame(
            'COM_CONTENTBUILDERNG_MENU_ITEM_LIST',
            (string) $listView->layout['title']
        );
        self::assertSame(
            'COM_CONTENTBUILDERNG_MENU_ITEM_LIST_CLASSIC',
            (string) $classic->layout['title']
        );
        self::assertFileExists($this->root . '/site/tmpl/list/default.php');
        self::assertFileExists($this->root . '/site/tmpl/list/listclassic.php');
        self::assertStringContainsString(
            "require __DIR__ . '/default.php';",
            (string) \file_get_contents($this->root . '/site/tmpl/list/listclassic.php')
        );
    }

    public function testNewListViewUsesBuilderWhileClassicKeepsLegacyFilters(): void
    {
        $newContract = $this->fieldContract($this->loadMetadata('default.xml'));
        $classicContract = $this->fieldContract($this->loadMetadata('listclassic.xml'));

        self::assertContains(
            ['name' => 'cb_new_config', 'type' => 'menulistbuilder', 'default' => '{}'],
            $newContract
        );
        self::assertNotContains(
            ['name' => 'cb_list_filter', 'type' => 'cbfilter', 'default' => ''],
            $newContract
        );
        self::assertContains(
            ['name' => 'cb_list_filter', 'type' => 'cbfilter', 'default' => ''],
            $classicContract
        );
    }

    public function testClassicLabelExistsInEveryMenuLanguageDomain(): void
    {
        foreach (['en-GB', 'fr-FR', 'de-DE'] as $tag) {
            foreach (
                [
                    'admin/language/' . $tag . '/com_contentbuilderng.menu.ini',
                    'admin/language/' . $tag . '/com_contentbuilderng.sys.ini',
                    'site/language/' . $tag . '/com_contentbuilderng.sys.ini',
                ] as $path
            ) {
                $contents = \file_get_contents($this->root . '/' . $path);

                self::assertIsString($contents);
                self::assertStringContainsString('COM_CONTENTBUILDERNG_MENU_ITEM_LIST_CLASSIC=', $contents);
            }
        }
    }

    public function testLockedCapabilityHelpExistsInEveryMenuLanguageDomain(): void
    {
        foreach (['en-GB', 'fr-FR', 'de-DE'] as $tag) {
            foreach (['com_contentbuilderng.menu.ini', 'com_contentbuilderng.sys.ini'] as $file) {
                $contents = (string) \file_get_contents(
                    $this->root . '/admin/language/' . $tag . '/' . $file
                );

                self::assertStringContainsString('COM_CONTENTBUILDERNG_MENU_NEW_FIELD_INHERITED_TIP=', $contents);
                self::assertStringContainsString('COM_CONTENTBUILDERNG_MENU_NEW_FIELD_UNAVAILABLE_TIP=', $contents);
            }
        }
    }

    public function testNewBuilderUsesExternalAssetAndFullWidthJoomlaLayout(): void
    {
        $metadata = $this->loadMetadata('default.xml');
        $fields = $metadata->xpath('/metadata/layout/config/fields/fieldset/field[@name="cb_new_config"]');

        self::assertIsArray($fields);
        self::assertCount(1, $fields);
        self::assertSame('true', (string) $fields[0]['hiddenLabel']);

        $fieldSource = (string) \file_get_contents($this->root . '/site/src/Field/MenulistbuilderField.php');
        $assetSource = (string) \file_get_contents($this->root . '/media/js/menu-list-options.js');

        self::assertStringNotContainsString('<script>', $fieldSource);
        self::assertStringContainsString("storage.closest('form')?.addEventListener('submit', () => {", $assetSource);
        self::assertStringContainsString('if (!storage.dataset.cbMenuOriginalName)', $assetSource);
        self::assertStringContainsString('data-cb-new-list-builder', $fieldSource);
        self::assertStringContainsString("\$config['action']", $fieldSource);
        self::assertStringContainsString("\$config['security']", $fieldSource);
        self::assertStringNotContainsString("\$config['actions']", $fieldSource);
        self::assertStringContainsString('data-can-search', $fieldSource);
        self::assertStringContainsString('data-cb-search-field', $fieldSource);
        self::assertStringContainsString('data-can-link', $fieldSource);
        self::assertStringContainsString('data-cb-link-field', $fieldSource);
        self::assertStringContainsString("['linkable']", $fieldSource);
        self::assertStringContainsString('hide-aware-inline-help d-none', $fieldSource);
        self::assertStringContainsString("'maximumRecords'", $fieldSource);
        self::assertStringContainsString("'cb_maximum_records'", $fieldSource);
        self::assertStringNotContainsString("'limitEnabled'", $fieldSource);
    }

    public function testColumnBuilderMatchesTheViewOrderAndAllowsOnlyDownwardRestrictions(): void
    {
        $fieldSource = (string) \file_get_contents($this->root . '/site/src/Field/MenulistbuilderField.php');
        $expectedOrder = [
            'COM_CONTENTBUILDERNG_MENU_NEW_FIELD',
            'COM_CONTENTBUILDERNG_MENU_NEW_VIEW_DISPLAY',
            'COM_CONTENTBUILDERNG_MENU_NEW_VIEW_SEARCH',
            'COM_CONTENTBUILDERNG_MENU_NEW_VIEW_LINK',
            'COM_CONTENTBUILDERNG_MENU_NEW_VIEW_DETAIL',
            'COM_CONTENTBUILDERNG_MENU_NEW_VIEW_EDIT',
            'COM_CONTENTBUILDERNG_MENU_NEW_VIEW_PUBLISHED',
            'COM_CONTENTBUILDERNG_MENU_NEW_FIXED_FILTER',
        ];
        $offset = 0;
        foreach ($expectedOrder as $languageKey) {
            $position = \strpos($fieldSource, "tableHeading('" . $languageKey . "'", $offset);
            self::assertNotFalse($position, $languageKey . ' is missing or out of order.');
            $offset = $position + 1;
        }

        foreach (['detail', 'edit', 'published'] as $capability) {
            self::assertStringContainsString("capabilityCheckbox('" . $capability . "'", $fieldSource);
            self::assertStringContainsString('data-can-' . $capability, $fieldSource);
        }
        self::assertStringNotContainsString('statusCell(', $fieldSource);
    }

    public function testMenuControlsExposeInheritedSecurityValuesAndLockedCapabilities(): void
    {
        $fieldSource = (string) \file_get_contents($this->root . '/site/src/Field/MenulistbuilderField.php');
        $defaultsSource = (string) \file_get_contents($this->root . '/site/src/Helper/MenuViewDefaultsHelper.php');
        $scriptSource = (string) \file_get_contents($this->root . '/media/js/menu-list-options.js');
        $styleSource = (string) \file_get_contents($this->root . '/media/css/menu-options.css');

        self::assertStringContainsString("'detail' => 'view'", $defaultsSource);
        self::assertStringContainsString("['permissions_fe']", $defaultsSource);
        self::assertStringContainsString("['own_fe']", $defaultsSource);
        self::assertStringContainsString("'cb_permission_' . \$menuAction", $defaultsSource);
        self::assertStringContainsString("'COM_CONTENTBUILDERNG_USE_DEFAULT_VALUE'", $fieldSource);
        self::assertStringContainsString("field.value === 'disabled'", $scriptSource);
        self::assertStringContainsString('setCapabilityState', $scriptSource);
        self::assertStringContainsString('.cb-menu-capability-cell.is-inherited', $styleSource);
        self::assertStringContainsString('width: min(24rem, 100%) !important;', $styleSource);
    }

    private function loadMetadata(string $file): \SimpleXMLElement
    {
        $metadata = \simplexml_load_file($this->root . '/site/tmpl/list/' . $file);
        self::assertInstanceOf(\SimpleXMLElement::class, $metadata);

        return $metadata;
    }

    /**
     * @return list<array{name: string, type: string, default: string}>
     */
    private function fieldContract(\SimpleXMLElement $metadata): array
    {
        $fields = $metadata->xpath('/metadata/layout/config/fields/fieldset/field');
        self::assertIsArray($fields);

        return \array_values(\array_map(
            static fn (\SimpleXMLElement $field): array => [
                'name' => (string) $field['name'],
                'type' => (string) $field['type'],
                'default' => (string) $field['default'],
            ],
            $fields
        ));
    }
}
