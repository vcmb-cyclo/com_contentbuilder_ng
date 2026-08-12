<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\View;

use CB\Component\Contentbuilderng\Site\Helper\MenuListConfigurationHelper;
use PHPUnit\Framework\TestCase;

final class MenuListConfigurationHelperTest extends TestCase
{
    public function testConfigurationBuildsConstrainedRequestParameters(): void
    {
        $parameters = MenuListConfigurationHelper::requestParameters([
            'titleMode' => 'custom',
            'title' => '  🚲 Routes  ',
            'columnsMode' => 'custom',
            'columns' => ['12', '9'],
            'linkFields' => ['9'],
            'detailFields' => ['9'],
            'editFields' => ['12'],
            'publishedFields' => ['9', '12'],
            'filters' => ['9' => ' Route 1*| *Gravel ', '15' => 'blocked'],
            'searchFields' => ['12'],
            'sortMode' => 'custom',
            'sort' => [
                ['field' => 'ID', 'dir' => 'desc'],
                ['field' => '12', 'dir' => 'asc'],
            ],
            'maximumRecords' => 25,
            'searchShow' => 'no',
            'stateShow' => 'yes',
            'stateFilterShow' => 'no',
            'action' => [
                'export' => 'no',
            ],
            'security' => [
                'delete' => 'disabled',
                'detail' => 'disabled',
            ],
        ]);

        self::assertSame('🚲 Routes', $parameters['cblist_title']);
        self::assertSame('12|9', $parameters['cblist_fields']);
        self::assertSame("9\tRoute 1*|*Gravel", $parameters['cb_list_filterhidden']);
        self::assertSame('12', $parameters['cb_menu_search_fields']);
        self::assertSame('9', $parameters['cb_menu_link_fields']);
        self::assertSame('9', $parameters['cb_menu_detail_fields']);
        self::assertSame('12', $parameters['cb_menu_edit_fields']);
        self::assertSame('9|12', $parameters['cb_menu_published_fields']);
        self::assertSame(1, $parameters['cb_new_list_menu']);
        self::assertSame('ID|12', $parameters['cblist_sort']);
        self::assertSame('desc|asc', $parameters['cblist_dir']);
        self::assertSame(25, $parameters['cblist_limit']);
        self::assertArrayNotHasKey('cb_show_details_back_button', $parameters);
        self::assertArrayNotHasKey('cb_new_show_limit_selector', $parameters);
        self::assertSame('no', $parameters['cb_new_show_search']);
        self::assertSame('yes', $parameters['cb_new_show_state']);
        self::assertSame('no', $parameters['cb_new_show_state_filter']);
        self::assertStringNotContainsString('delete', (string) $parameters['cblist_actions']);
        self::assertStringNotContainsString('detail', (string) $parameters['cblist_actions']);
        self::assertStringNotContainsString('export', (string) $parameters['cblist_actions']);
        self::assertStringNotContainsString('search', (string) $parameters['cblist_actions']);
        self::assertSame('content-plugin', $parameters['cblist_embed']);
    }

    public function testSearchFieldsOnlyReduceViewSearchableFields(): void
    {
        self::assertSame(
            [7, 12],
            MenuListConfigurationHelper::filterSearchableElements([3, 7, 12], '12|7|99')
        );
    }

    public function testCustomColumnsCanDisableAllViewSearchFields(): void
    {
        $parameters = MenuListConfigurationHelper::requestParameters([
            'columnsMode' => 'custom',
            'columns' => [],
            'searchFields' => [],
            'linkFields' => [],
            'detailFields' => [],
            'editFields' => [],
            'publishedFields' => [],
        ]);

        self::assertSame('__none__', $parameters['cb_menu_search_fields']);
        self::assertSame('__none__', $parameters['cb_menu_link_fields']);
        self::assertSame('__none__', $parameters['cb_menu_detail_fields']);
        self::assertSame('__none__', $parameters['cb_menu_edit_fields']);
        self::assertSame('__none__', $parameters['cb_menu_published_fields']);
        self::assertSame([], MenuListConfigurationHelper::filterSearchableElements([3, 7, 12], '__none__'));
        self::assertSame('content-plugin', $parameters['cblist_embed']);
    }

    public function testIncompletePrototypeCustomConfigurationKeepsViewDefaults(): void
    {
        $parameters = MenuListConfigurationHelper::requestParameters([
            'columnsMode' => 'custom',
        ]);

        self::assertSame('', $parameters['cb_menu_search_fields']);
        self::assertSame('', $parameters['cb_menu_link_fields']);
        self::assertArrayNotHasKey('cb_new_list_menu', $parameters);
        self::assertArrayNotHasKey('cblist_embed', $parameters);
    }

    public function testDefaultConfigurationDoesNotCreateEmbeddedContext(): void
    {
        $parameters = MenuListConfigurationHelper::requestParameters([]);

        self::assertArrayNotHasKey('cblist_embed', $parameters);
        self::assertSame('', $parameters['cb_list_filterhidden']);
        self::assertSame('', $parameters['cb_menu_link_fields']);
        self::assertArrayNotHasKey('cb_new_list_menu', $parameters);
        self::assertArrayNotHasKey('cb_new_show_search', $parameters);
        self::assertArrayNotHasKey('cb_new_show_state', $parameters);
        self::assertArrayNotHasKey('cb_new_show_state_filter', $parameters);
    }

    public function testSearchVisibilityCanOverrideTheViewSetting(): void
    {
        self::assertSame(
            'yes',
            MenuListConfigurationHelper::requestParameters(['searchShow' => 'yes'])['cb_new_show_search']
        );
        self::assertSame(
            'no',
            MenuListConfigurationHelper::requestParameters(['searchShow' => 'no'])['cb_new_show_search']
        );
    }

    public function testMaximumRecordsInheritsTheViewAndAllowsMenuOverrides(): void
    {
        self::assertSame(
            18,
            MenuListConfigurationHelper::requestParameters([], 18)['cblist_limit']
        );
        self::assertArrayNotHasKey(
            'cblist_limit',
            MenuListConfigurationHelper::requestParameters([], 0)
        );
        self::assertSame(
            25,
            MenuListConfigurationHelper::requestParameters(['maximumRecords' => 25], 18)['cblist_limit']
        );
        self::assertArrayNotHasKey(
            'cblist_limit',
            MenuListConfigurationHelper::requestParameters(['maximumRecords' => 0], 18)
        );
    }

    public function testActionAndSecurityStorageKeysMatchTheMenuBuilderContract(): void
    {
        $parameters = MenuListConfigurationHelper::requestParameters([
            'action' => ['export' => 'no'],
            'security' => ['detail' => 'disabled'],
        ]);

        self::assertArrayHasKey('cblist_actions', $parameters);
        self::assertStringNotContainsString('export', (string) $parameters['cblist_actions']);
        self::assertStringNotContainsString('detail', (string) $parameters['cblist_actions']);
        self::assertSame('content-plugin', $parameters['cblist_embed']);
    }
}
