<?php

declare(strict_types=1);

namespace Joomla\Input {
    if (!class_exists(Input::class, false)) {
        final class Input
        {
            public function __construct(private readonly array $values = [])
            {
            }

            public function getCmd(string $name, string $default = ''): string
            {
                return preg_replace('/[^A-Z0-9_\.-]/i', '', (string) ($this->values[$name] ?? $default));
            }
        }
    }
}

namespace CB\Component\Contentbuilderng\Tests\Unit\Helper {

use CB\Component\Contentbuilderng\Site\Helper\PreviewThemeHelper;
use Joomla\Input\Input;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 4) . '/site/src/Helper/PreviewThemeHelper.php';

final class PreviewThemeHelperTest extends TestCase
{
    protected function setUp(): void
    {
        // Seeding the cache keeps availableThemes() from reaching for a
        // database that no unit test has.
        $this->setAvailableThemes(['thoth', 'dark', 'blank']);
    }

    protected function tearDown(): void
    {
        $this->setAvailableThemes(null);
    }

    public function testThemeIsAvailableOnlyDuringPreview(): void
    {
        $input = new Input(['cb_preview_theme' => 'dark']);

        self::assertSame('', PreviewThemeHelper::resolve($input, false));
        self::assertSame('dark', PreviewThemeHelper::resolve($input, true));
    }

    public function testUnknownThemeIsRejected(): void
    {
        $input = new Input(['cb_preview_theme' => 'not-installed']);

        self::assertSame('', PreviewThemeHelper::resolve($input, true));
    }

    public function testMissingParameterMeansNoOverride(): void
    {
        self::assertSame('', PreviewThemeHelper::resolve(new Input([]), true));
    }

    public function testApplyFallsBackToTheStoredTheme(): void
    {
        self::assertSame('khepri', PreviewThemeHelper::apply('khepri', ''));
        self::assertSame('dark', PreviewThemeHelper::apply('khepri', 'dark'));
    }

    public function testThemeIsPropagatedToLinksAndForms(): void
    {
        self::assertSame(
            '&cb_preview=1&cb_preview_theme=dark',
            PreviewThemeHelper::appendQuery('&cb_preview=1', 'dark')
        );
        self::assertSame('&cb_preview=1', PreviewThemeHelper::appendQuery('&cb_preview=1', ''));
        self::assertStringContainsString(
            'name="cb_preview_theme" value="dark"',
            PreviewThemeHelper::appendHiddenField('', 'dark')
        );
        self::assertSame('', PreviewThemeHelper::appendHiddenField('', ''));
    }

    public function testMutationRedirectsPropagateTheme(): void
    {
        foreach (['EditController.php', 'ListController.php'] as $controller) {
            $source = file_get_contents(
                \dirname(__DIR__, 4) . '/site/src/Controller/' . $controller
            );
            self::assertIsString($source);
            self::assertStringContainsString(
                'PreviewThemeHelper::resolve($this->input, true)',
                $source
            );
            self::assertStringContainsString(
                'PreviewThemeHelper::appendQuery($query, $theme)',
                $source
            );
        }
    }

    private function setAvailableThemes(?array $themes): void
    {
        $property = new \ReflectionProperty(PreviewThemeHelper::class, 'availableThemes');
        $property->setValue(null, $themes);
    }
}
}
