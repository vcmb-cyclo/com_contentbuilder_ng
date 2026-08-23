<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;

final class FormAuditFrontendPermissionsTest extends TestCase
{
    public function testFormAuditReadsAndReportsSavedFrontendPermissions(): void
    {
        $source = file_get_contents(
            \dirname(__DIR__, 3) . '/src/Service/FormAuditService.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString("'new_button', 'edit_button'", $source);
        self::assertStringContainsString("\$config['permissions_fe']", $source);
        self::assertStringContainsString("\$config['own_fe']", $source);
        self::assertStringContainsString("'published', 'detail_include', 'editable'", $source);
        self::assertStringContainsString('COM_CONTENTBUILDERNG_AUDIT_CHECK_FRONTEND_NEW_WITHOUT_PERMISSION', $source);
        self::assertStringContainsString('COM_CONTENTBUILDERNG_AUDIT_CHECK_FRONTEND_EDIT_WITHOUT_PERMISSION', $source);
        self::assertStringContainsString("\$hasPublishedDetailElement || (int) (\$element['detail_include'] ?? 0) === 1", $source);
        self::assertStringContainsString("\$hasPublishedEditableElement || (int) (\$element['editable'] ?? 0) === 1", $source);
    }

    public function testPermissionAuditTranslationsExistInAllSupportedLanguages(): void
    {
        $languageRoot = \dirname(__DIR__, 3) . '/language';
        $keys = [
            'COM_CONTENTBUILDERNG_AUDIT_INFO_FRONTEND_PERMISSIONS',
            'COM_CONTENTBUILDERNG_AUDIT_INFO_FRONTEND_OWNER_PERMISSIONS',
            'COM_CONTENTBUILDERNG_AUDIT_CHECK_FRONTEND_PERMISSIONS_INVALID',
            'COM_CONTENTBUILDERNG_AUDIT_CHECK_FRONTEND_PERMISSIONS_EMPTY',
            'COM_CONTENTBUILDERNG_AUDIT_CHECK_FRONTEND_NEW_WITHOUT_PERMISSION',
            'COM_CONTENTBUILDERNG_AUDIT_CHECK_FRONTEND_EDIT_WITHOUT_PERMISSION',
        ];

        foreach (['en-GB', 'fr-FR', 'de-DE'] as $language) {
            $translations = parse_ini_file(
                $languageRoot . '/' . $language . '/com_contentbuilderng.ini',
                false,
                INI_SCANNER_RAW
            );

            self::assertIsArray($translations);
            foreach ($keys as $key) {
                self::assertArrayHasKey($key, $translations, $language . ' is missing ' . $key);
            }
        }
    }
}
