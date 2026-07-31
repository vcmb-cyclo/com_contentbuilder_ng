<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Helper;

use CB\Component\Contentbuilderng\Site\Helper\MenuParamHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 4) . '/site/src/Helper/MenuParamHelper.php';

final class MenuParamHelperTest extends TestCase
{
    public function testReadsMenuParamFromSettingsArray(): void
    {
        $params = new class {
            public function get(string $key, mixed $default = null): mixed
            {
                return $key === 'settings' ? ['show_back_button' => 1] : $default;
            }
        };

        self::assertSame(1, MenuParamHelper::getMenuParam($params, 'show_back_button', 0));
    }

    public function testReadsMenuParamFromRegistryLikeSettings(): void
    {
        $settings = new class {
            public function get(string $key, mixed $default = null): mixed
            {
                return $key === 'show_back_button' ? 1 : $default;
            }
        };
        $params = new class ($settings) {
            public function __construct(private readonly object $settings)
            {
            }

            public function get(string $key, mixed $default = null): mixed
            {
                return $key === 'settings' ? $this->settings : $default;
            }
        };

        self::assertSame(1, MenuParamHelper::getMenuParam($params, 'show_back_button', 0));
    }

    public function testReadsMenuParamFromSettingsObjectProperty(): void
    {
        $params = new class {
            public function get(string $key, mixed $default = null): mixed
            {
                return $key === 'settings' ? (object) ['show_back_button' => 1] : $default;
            }
        };

        self::assertSame(1, MenuParamHelper::getMenuParam($params, 'show_back_button', 0));
    }

    public function testReadsFlattenedMenuParamAndFallsBackToDefault(): void
    {
        $params = new class {
            public function get(string $key, mixed $default = null): mixed
            {
                return $key === 'settings.show_back_button' ? 1 : $default;
            }
        };

        self::assertSame(1, MenuParamHelper::getMenuParam($params, 'show_back_button', 0));
        self::assertSame(7, MenuParamHelper::getMenuParam($params, 'missing', 7));
    }

    public function testReadsLegacyRootMenuParamWhenSettingsGroupIsMissing(): void
    {
        $params = new class {
            public function get(string $key, mixed $default = null): mixed
            {
                return $key === 'form_id' ? 17 : $default;
            }
        };

        self::assertSame(17, MenuParamHelper::getMenuParam($params, 'form_id', 0));
    }

    /**
     * @return array<string,array{0:mixed,1:int,2:int}>
     */
    public static function toggleValueProvider(): array
    {
        return [
            'null uses default' => [null, 1, 1],
            'empty uses default' => ['', 1, 1],
            'negative uses default' => [-1, 1, 1],
            'one enables' => ['1', 0, 1],
            'zero disables' => ['0', 1, 0],
            'other value disables' => [2, 1, 0],
        ];
    }

    #[DataProvider('toggleValueProvider')]
    public function testResolvesToggleValue(mixed $value, int $default, int $expected): void
    {
        self::assertSame($expected, MenuParamHelper::resolveToggleValue($value, $default));
    }

    public function testResolvesPageHeadingFromExplicitValue(): void
    {
        $app = new class {
            public function getParams(): never
            {
                throw new \LogicException('Global parameters must not be read.');
            }
        };

        self::assertSame(0, MenuParamHelper::resolvePageHeadingToggle($app, '0'));
        self::assertSame(1, MenuParamHelper::resolvePageHeadingToggle($app, null));
    }

    public function testResolvesInheritedPageHeadingFromGlobalParams(): void
    {
        $app = new class {
            public function getParams(): object
            {
                return new class {
                    public function get(string $key, mixed $default = null): mixed
                    {
                        return $key === 'show_page_heading' ? 0 : $default;
                    }
                };
            }
        };

        self::assertSame(0, MenuParamHelper::resolvePageHeadingToggle($app, '', 1));
    }

    public function testUsesPositiveRequestListLimitBeforeClientConfiguration(): void
    {
        $app = new class {
            public function getInput(): object
            {
                return new class {
                    public function getInt(string $key, int $default = 0): int
                    {
                        return $key === 'cb_list_limit' ? 50 : $default;
                    }
                };
            }

            public function isClient(string $client): never
            {
                throw new \LogicException('Client must not be inspected.');
            }
        };

        self::assertSame(50, MenuParamHelper::getConfiguredListLimit($app));
    }

    public function testReturnsZeroListLimitOutsideSiteClient(): void
    {
        $app = new class {
            public function getInput(): object
            {
                return new class {
                    public function getInt(string $key, int $default = 0): int
                    {
                        return $default;
                    }
                };
            }

            public function isClient(string $client): bool
            {
                return false;
            }
        };

        self::assertSame(0, MenuParamHelper::getConfiguredListLimit($app));
    }

    public function testDetectsExplicitListLimitInGetOrPost(): void
    {
        $app = self::applicationWithListInput(['limit' => 20], []);
        self::assertTrue(MenuParamHelper::hasExplicitListLimitRequest($app));

        $app = self::applicationWithListInput([], ['limit' => 40]);
        self::assertTrue(MenuParamHelper::hasExplicitListLimitRequest($app));

        $app = self::applicationWithListInput([], []);
        self::assertFalse(MenuParamHelper::hasExplicitListLimitRequest($app));
    }

    private static function applicationWithListInput(array $getList, array $postList): object
    {
        return new class ($getList, $postList) {
            public function __construct(private readonly array $getList, private readonly array $postList)
            {
            }

            public function getInput(): object
            {
                return new class ($this->getList, $this->postList) {
                    public readonly object $get;
                    public readonly object $post;

                    public function __construct(array $getList, array $postList)
                    {
                        $this->get = self::inputBag($getList);
                        $this->post = self::inputBag($postList);
                    }

                    private static function inputBag(array $list): object
                    {
                        return new class ($list) {
                            public function __construct(private readonly array $list)
                            {
                            }

                            public function get(string $key, mixed $default = null, string $filter = ''): mixed
                            {
                                return $key === 'list' ? $this->list : $default;
                            }
                        };
                    }
                };
            }
        };
    }
}
