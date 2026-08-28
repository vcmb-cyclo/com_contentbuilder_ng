<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use CB\Component\Contentbuilderng\Administrator\Service\ElementSettingsStateService;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 4) . '/admin/src/Helper/PackedDataHelper.php';
require_once \dirname(__DIR__, 4) . '/admin/src/Service/ElementSettingsStateService.php';

final class ElementSettingsStateServiceTest extends TestCase
{
    public function testLegacyTextTypeFollowsTheBreezingFormsSourceType(): void
    {
        self::assertTrue(ElementSettingsStateService::shouldSynchronizeSourceType(
            (object) ['type' => 'text', 'change_type' => ''],
            'radiogroup'
        ));
    }

    public function testExplicitTypeOverrideIsPreserved(): void
    {
        self::assertFalse(ElementSettingsStateService::shouldSynchronizeSourceType(
            (object) ['type' => 'text', 'change_type' => 'text'],
            'radiogroup'
        ));
    }

    public function testMatchingSourceTypeDoesNotRequireSynchronization(): void
    {
        self::assertFalse(ElementSettingsStateService::shouldSynchronizeSourceType(
            (object) ['type' => 'radiogroup', 'change_type' => ''],
            'radiogroup'
        ));
    }

    public function testNativeBreezingFormsTypesRemainDefaultWithoutOverrides(): void
    {
        foreach (['radiogroup', 'checkboxgroup', 'select'] as $type) {
            $element = (object) [
                'type' => $type,
                'options' => (object) [
                    'class' => '',
                    'seperator' => ',',
                    'horizontal' => false,
                    'horizontal_length' => '',
                ],
            ];

            self::assertFalse(ElementSettingsStateService::isModified($element, $type));
        }
    }

    public function testTypeOverrideIsModifiedComparedWithTheSourceType(): void
    {
        self::assertTrue(ElementSettingsStateService::isModified(
            (object) ['type' => 'text', 'options' => null],
            'radiogroup'
        ));
    }

    public function testRealElementOverridesRemainModified(): void
    {
        self::assertTrue(ElementSettingsStateService::isModified(
            (object) ['type' => 'radiogroup', 'default_value' => 'Oui'],
            'radiogroup'
        ));
        self::assertTrue(ElementSettingsStateService::isModified(
            (object) ['type' => 'radiogroup', 'options' => (object) ['horizontal' => true]],
            'radiogroup'
        ));
    }
}
