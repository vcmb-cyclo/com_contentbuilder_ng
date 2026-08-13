<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;

final class TemplateRenderServiceReadOnlyPresentationTest extends TestCase
{
    public function testReadOnlyFieldsReceiveSemanticMarkupAndTranslatedBadge(): void
    {
        $root = \dirname(__DIR__, 4);
        $source = \file_get_contents($root . '/admin/src/Service/TemplateRenderService.php');

        self::assertIsString($source);
        self::assertStringContainsString(
            '$isReadOnly = !$isEditable || (bool) ($options->readonly ?? false);',
            $source
        );
        self::assertStringContainsString('class="cbReadOnlyField" role="group" aria-label="', $source);
        self::assertStringContainsString('class="cbReadOnlyBadge badge rounded-pill text-bg-secondary"', $source);
        self::assertStringContainsString("Text::_('COM_CONTENTBUILDERNG_FIELD_READ_ONLY')", $source);
        self::assertStringContainsString('class="cbReadOnlyValue"', $source);
    }

    public function testReadOnlyPresentationIsSharedByAllThemes(): void
    {
        $root = \dirname(__DIR__, 4);
        $css = \file_get_contents($root . '/media/css/edit.css');

        self::assertIsString($css);
        self::assertStringContainsString('.cbEditableWrapper .cbReadOnlyBadge', $css);
        self::assertStringContainsString('.cbEditableWrapper .cbReadOnlyField', $css);
        self::assertStringContainsString('.cbEditableWrapper .cbReadOnlyField *', $css);
        self::assertStringContainsString('.cbEditableWrapper .cbReadOnlyField .form-check-input:disabled:checked', $css);
        self::assertStringContainsString('.cbEditableWrapper .cbReadOnlyField .form-check-label', $css);
        self::assertStringContainsString('.cbEditableWrapper .cbReadOnlyValue', $css);
        self::assertStringContainsString('cursor:not-allowed;', $css);
        self::assertStringContainsString('font-style:italic;', $css);
    }

    public function testReadOnlyLabelExistsInEveryFrontendLanguage(): void
    {
        $root = \dirname(__DIR__, 4);

        foreach (['en-GB', 'fr-FR', 'de-DE'] as $language) {
            $languageFile = \file_get_contents(
                $root . '/site/language/' . $language . '/com_contentbuilderng.ini'
            );

            self::assertIsString($languageFile);
            self::assertStringContainsString('COM_CONTENTBUILDERNG_FIELD_READ_ONLY=', $languageFile);
        }
    }
}
