<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

final class StorageRegressionFixesTest extends TestCase
{
    public function testRequiredValidationPreservesSparseEditSubmissions(): void
    {
        $source = file_get_contents(\dirname(__DIR__, 4) . '/site/src/Model/EditModel.php');
        self::assertIsString($source);

        $loop = strpos($source, 'foreach ($requiredElementIds as $requiredElementId)');
        $presenceGuard = strpos($source, 'if (!array_key_exists($requiredElementId, $values))', $loop);
        $valueRead = strpos($source, '$requiredValue = $values[$requiredElementId] ?? null;', $loop);

        self::assertIsInt($loop);
        self::assertIsInt($presenceGuard);
        self::assertIsInt($valueRead);
        self::assertLessThan(
            $valueRead,
            $presenceGuard,
            'A required field omitted from a sparse edit must be skipped before its value is read.'
        );
    }

    public function testRequiredMetadataIsPersistedOnlyAfterPhysicalDdl(): void
    {
        $source = file_get_contents(\dirname(__DIR__, 3) . '/src/Controller/StorageController.php');
        self::assertIsString($source);

        $method = strpos($source, 'public function ajax_update_field_required(): void');
        $ddl = strpos($source, 'StorageColumnTypeHelper::enforceRequired(', $method);
        $metadataUpdate = strpos($source, "->set(\$db->quoteName('required')", $method);

        self::assertIsInt($method);
        self::assertIsInt($ddl);
        self::assertIsInt($metadataUpdate);
        self::assertLessThan(
            $metadataUpdate,
            $ddl,
            'The physical column change must succeed before required metadata is updated.'
        );
    }

    public function testInlineStorageMutationsRequireEditPermission(): void
    {
        $source = file_get_contents(\dirname(__DIR__, 3) . '/src/Controller/StorageController.php');
        self::assertIsString($source);

        self::assertStringContainsString("authorise('core.edit', 'com_contentbuilderng')", $source);

        foreach (['ajax_update_field_type', 'ajax_update_field_required', 'ajax_update_field_title'] as $methodName) {
            $expectedGuard = '/public function ' . $methodName
                . '\(\): void\s*\{\s*\$this->checkToken\(\);\s*\$this->assertStorageEditAccess\(\);/';

            self::assertMatchesRegularExpression(
                $expectedGuard,
                $source,
                $methodName . ' must enforce CSRF and core.edit before mutating Storage state.'
            );
        }
    }

    public function testRuntimeDirectStorageSynchronizationIsAdditive(): void
    {
        $provisioningSource = file_get_contents(
            \dirname(__DIR__, 3) . '/src/Service/DirectStorageFormProvisioningService.php'
        );
        $supportSource = file_get_contents(\dirname(__DIR__, 3) . '/src/Service/FormSupportService.php');

        self::assertIsString($provisioningSource);
        self::assertIsString($supportSource);
        self::assertStringContainsString('synchElements($formId, $form, false)', $provisioningSource);
        self::assertStringContainsString(
            'public function synchElements($formId, $form, bool $removeMissing = true): array',
            $supportSource
        );
        self::assertStringContainsString('if ($removeMissing && $ids !== []) {', $supportSource);
        self::assertStringContainsString('if ($removeMissing) {', $supportSource);
    }

    public function testStorageSynchronizationRefreshesOnlyManagedEditableTypes(): void
    {
        $supportSource = file_get_contents(\dirname(__DIR__, 3) . '/src/Service/FormSupportService.php');
        $storageSource = file_get_contents(\dirname(__DIR__, 3) . '/src/types/com_contentbuilderng.php');

        self::assertIsString($supportSource);
        self::assertIsString($storageSource);
        self::assertStringContainsString('shouldSynchronizeEditableElementTypes', $storageSource);
        self::assertStringContainsString('StorageColumnTypeHelper::editableElementType(', $storageSource);
        self::assertStringContainsString('$synchronizeEditableTypes = !$removeMissing', $supportSource);
        self::assertStringContainsString('StorageColumnTypeHelper::isStorageManagedEditableType(', $supportSource);
        self::assertStringContainsString("->set(\$db->quoteName('type')", $supportSource);
    }
}
