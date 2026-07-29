<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use CB\Component\Contentbuilderng\Administrator\Service\FieldValidationService;
use CB\Component\Contentbuilderng\Tests\Stubs\Database;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 3) . '/src/Helper/ContentbuilderngHelper.php';
require_once \dirname(__DIR__, 3) . '/src/Service/FieldValidationService.php';

final class FieldValidationServiceTest extends TestCase
{
    private FieldValidationService $service;

    protected function setUp(): void
    {
        $this->service = new FieldValidationService(new Database());
    }

    public function testBuiltInValidationsAreApplied(): void
    {
        $field = [
            'name' => 'start_date',
            'label' => 'Start date',
            'type' => 'text',
            'validations' => 'notempty,email,equal,date_not_before,date_is_valid',
            'options' => (object) ['transfer_format' => 'DD/MM/YYYY'],
            'validation_message' => '',
        ];
        $fields = [
            'start_date' => $field + ['value' => '32/01/2026'],
            'start_date_later' => [
                'name' => 'start_date_later',
                'label' => 'End date',
                'value' => '99/01/2026',
                'options' => (object) ['transfer_format' => 'DD/MM/YYYY'],
            ],
            'start_date_repeat' => [
                'name' => 'start_date_repeat',
                'label' => 'Repeated date',
                'value' => '32/01/2026',
            ],
        ];

        $results = $this->service->validate($field, $fields, 0, null, '32/01/2026');

        self::assertSame([
            '',
            'COM_CONTENTBUILDERNG_VALIDATION_EMAIL_INVALID: Start date (32/01/2026)',
            '',
            '',
            'COM_CONTENTBUILDERNG_VALIDATION_DATE_IS_VALID: Start date (32/01/2026)',
        ], $results);
    }

    public function testNotEmptyUsesTheConfiguredMessage(): void
    {
        $field = [
            'label' => 'Name',
            'type' => 'text',
            'validations' => 'notempty',
            'validation_message' => 'A name is required',
        ];

        self::assertSame(
            ['A name is required'],
            $this->service->validate($field, [], 0, null, '')
        );
    }

    public function testExternalValidationNamesRemainAvailableToPlugins(): void
    {
        $field = ['validations' => 'notempty,custom_rule,date_is_valid,custom_rule'];

        self::assertSame(
            ['custom_rule'],
            $this->service->getExternalValidationNames($field)
        );
    }

    public function testValidationsCanBeDisabledGlobally(): void
    {
        $service = new FieldValidationService(new Database(), false);

        self::assertFalse($service->areValidationsEnabled());
        self::assertSame([], $service->validate(
            ['validations' => 'notempty,custom_rule'],
            [],
            0,
            null,
            ''
        ));
        self::assertSame([], $service->getExternalValidationNames(['validations' => 'custom_rule']));
    }

    public function testDateNotBeforeRejectsAnEarlierEndDate(): void
    {
        $field = [
            'name' => 'start_date',
            'label' => 'Start date',
            'validations' => 'date_not_before',
            'options' => (object) ['transfer_format' => 'DD/MM/YYYY'],
        ];
        $fields = [
            'start_date_later' => [
                'name' => 'start_date_later',
                'label' => 'End date',
                'value' => '31/01/2026',
                'options' => (object) ['transfer_format' => 'DD/MM/YYYY'],
            ],
        ];

        self::assertSame(
            ['COM_CONTENTBUILDERNG_VALIDATION_DATE_NOT_BEFORE: End date (31/01/2026)'],
            $this->service->validate($field, $fields, 0, null, '01/02/2026')
        );
    }
}
