<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Service;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\ContentbuilderngHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class FieldValidationService
{
    /**
     * @var list<string>
     */
    private const BUILT_IN_VALIDATIONS = [
        'notempty',
        'equal',
        'email',
        'date_not_before',
        'date_is_valid',
    ];

    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly bool $validationsEnabled = true
    )
    {
    }

    public function areValidationsEnabled(): bool
    {
        return $this->validationsEnabled;
    }

    /**
     * @return list<string>
     */
    public function getAvailableValidationNames(): array
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('element'))
            ->from($this->db->quoteName('#__extensions'))
            ->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('contentbuilderng_validation'))
            ->where($this->db->quoteName('enabled') . ' = 1')
            ->where(
                $this->db->quoteName('element') . ' NOT IN ('
                . implode(',', array_map([$this->db, 'quote'], self::BUILT_IN_VALIDATIONS))
                . ')'
            )
            ->order($this->db->quoteName('ordering') . ', ' . $this->db->quoteName('element'));
        $this->db->setQuery($query);

        $external = array_values(array_filter(array_map('strval', (array) $this->db->loadColumn())));

        return array_values(array_unique(array_merge(self::BUILT_IN_VALIDATIONS, $external)));
    }

    /**
     * @return list<string>
     */
    public function getExternalValidationNames(array $field): array
    {
        if (!$this->areValidationsEnabled()) {
            return [];
        }

        $selected = $this->getSelectedValidationNames($field);

        return array_values(array_diff($selected, self::BUILT_IN_VALIDATIONS));
    }

    /**
     * @param array<string,mixed> $field
     * @param array<string,array<string,mixed>> $fields
     * @return list<string>
     */
    public function validate(
        array $field,
        array $fields,
        int $recordId,
        mixed $form,
        mixed $value
    ): array {
        if (!$this->areValidationsEnabled()) {
            return [];
        }

        $results = [];

        foreach ($this->getSelectedValidationNames($field) as $validation) {
            $results[] = match ($validation) {
                'notempty' => $this->validateNotEmpty($field, $recordId, $form, $value),
                'equal' => $this->validateEqual($field, $fields, $value),
                'email' => $this->validateEmail($field, $value),
                'date_not_before' => $this->validateDateNotBefore($field, $fields, $value),
                'date_is_valid' => $this->validateDate($field, $value),
                default => '',
            };
        }

        return $results;
    }

    /**
     * @return list<string>
     */
    private function getSelectedValidationNames(array $field): array
    {
        $validations = array_map('trim', explode(',', (string) ($field['validations'] ?? '')));
        $validations = array_values(array_filter($validations));

        return array_values(array_unique($validations));
    }

    private function validateNotEmpty(array $field, int $recordId, mixed $form, mixed $value): string
    {
        $message = '';

        if (!is_array($value)) {
            if (($field['type'] ?? '') === 'upload' && is_object($form) && method_exists($form, 'getRecord')) {
                $recordWithFile = false;
                foreach ((array) $form->getRecord($recordId, false, -1, true) as $item) {
                    if ((string) ($item->recElementId ?? '') === (string) ($field['reference_id'] ?? '')) {
                        $recordWithFile = (string) ($item->recValue ?? '') !== '';
                        break;
                    }
                }

                if (!$recordWithFile && trim((string) $value) === '') {
                    $message = $this->emptyValueMessage($field);
                }
            } elseif (trim((string) $value) === '') {
                $message = $this->emptyValueMessage($field);
            }
        } else {
            $hasValue = false;
            foreach ($value as $item) {
                if ((string) $item !== 'cbGroupMark' && (string) $item !== '') {
                    $hasValue = true;
                    break;
                }
            }

            if (!$hasValue) {
                $message = $this->emptyValueMessage($field);
            }
        }

        return $message;
    }

    private function validateEqual(array $field, array $fields, mixed $value): string
    {
        foreach ($fields as $otherField) {
            if (($otherField['name'] ?? '') !== (string) ($field['name'] ?? '') . '_repeat') {
                continue;
            }

            $currentValue = $field['orig_value'] ?? $value;
            $otherValue = $otherField['orig_value'] ?? ($otherField['value'] ?? null);

            if ($this->normalizeComparableValue($currentValue) !== $this->normalizeComparableValue($otherValue)) {
                return Text::_('COM_CONTENTBUILDERNG_VALIDATION_NOT_EQUAL') . ': '
                    . (string) ($field['label'] ?? '') . ' / ' . (string) ($otherField['label'] ?? '');
            }

            return '';
        }

        return '';
    }

    private function validateEmail(array $field, mixed $value): string
    {
        $invalidValues = [];
        $values = is_array($value) ? $value : [$value];

        foreach ($values as $item) {
            if (!ContentbuilderngHelper::isEmail((string) $item)) {
                $invalidValues[] = (string) $item;
            }
        }

        if ($invalidValues === []) {
            return '';
        }

        $message = Text::_('COM_CONTENTBUILDERNG_VALIDATION_EMAIL_INVALID') . ': '
            . (string) ($field['label'] ?? '');

        return $message . ($invalidValues !== [''] ? ' (' . implode('', $invalidValues) . ')' : '');
    }

    private function validateDate(array $field, mixed $value): string
    {
        $options = $field['options'] ?? null;
        $format = $this->getOption($options, 'transfer_format', 'YYYY-mm-dd');

        foreach (is_array($value) ? $value : [$value] as $item) {
            if (!ContentbuilderngHelper::isValidDate((string) $item, $format)) {
                return Text::_('COM_CONTENTBUILDERNG_VALIDATION_DATE_IS_VALID') . ': '
                    . (string) ($field['label'] ?? '')
                    . ((string) $item !== '' ? ' (' . (string) $item . ')' : '');
            }
        }

        return '';
    }

    private function validateDateNotBefore(array $field, array $fields, mixed $value): string
    {
        foreach ($fields as $otherField) {
            if (($otherField['name'] ?? '') !== (string) ($field['name'] ?? '') . '_later') {
                continue;
            }

            if (is_array($value)) {
                return Text::_('COM_CONTENTBUILDERNG_VALIDATION_DATE_NOT_BEFORE_GROUPS');
            }

            $otherOptions = $otherField['options'] ?? null;
            $fieldOptions = $field['options'] ?? null;
            $otherValue = ContentbuilderngHelper::convertDate(
                (string) ($otherField['value'] ?? ''),
                $this->getOption($otherOptions, 'transfer_format', 'YYYY-mm-dd'),
                'YYYY-MM-DD'
            );
            $currentValue = ContentbuilderngHelper::convertDate(
                (string) $value,
                $this->getOption($fieldOptions, 'transfer_format', 'YYYY-mm-dd'),
                'YYYY-MM-DD'
            );

            if (is_array($otherValue) || is_array($currentValue)) {
                return Text::_('COM_CONTENTBUILDERNG_VALIDATION_DATE_NOT_BEFORE_GROUPS');
            }

            $currentValue = preg_replace('/[^0-9]/', '', (string) $currentValue);
            $otherValue = preg_replace('/[^0-9]/', '', (string) $otherValue);

            if ($otherValue < $currentValue) {
                return Text::_('COM_CONTENTBUILDERNG_VALIDATION_DATE_NOT_BEFORE') . ': '
                    . (string) ($otherField['label'] ?? '') . ' (' . (string) ($otherField['value'] ?? '') . ')';
            }

            return '';
        }

        return '';
    }

    private function normalizeComparableValue(mixed $value): string
    {
        if (!is_array($value)) {
            return (string) $value;
        }

        return implode('', array_map('strval', $value));
    }

    private function getOption(mixed $options, string $name, string $default): string
    {
        if (is_object($options) && isset($options->{$name})) {
            return (string) $options->{$name};
        }

        if (is_array($options) && isset($options[$name])) {
            return (string) $options[$name];
        }

        return $default;
    }

    private function emptyValueMessage(array $field): string
    {
        $customMessage = trim((string) ($field['validation_message'] ?? ''));
        if ($customMessage !== '') {
            return $customMessage;
        }

        return Text::_('COM_CONTENTBUILDERNG_VALIDATION_VALUE_EMPTY') . ': '
            . (string) ($field['label'] ?? '');
    }
}
