<?php

/**
 * @package     ContentBuilderNG
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Service;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\PackedDataHelper;

final class ElementSettingsStateService
{
    public static function shouldSynchronizeSourceType(object $element, string $sourceType): bool
    {
        $elementType = trim((string) ($element->type ?? '')) ?: 'text';
        $sourceType = trim($sourceType) ?: 'text';
        $manualType = trim((string) ($element->change_type ?? ''));

        return $manualType === '' && $elementType !== $sourceType;
    }

    public static function isModified(object $element, string $sourceType = 'text'): bool
    {
        $elementType = trim((string) ($element->type ?? '')) ?: 'text';
        $sourceType = trim($sourceType) ?: 'text';

        if ($elementType !== $sourceType) {
            return true;
        }

        if (trim((string) ($element->item_wrapper ?? '')) !== '') {
            return true;
        }

        foreach (
            [
                'hint',
                'default_value',
                'validations',
                'custom_init_script',
                'custom_action_script',
                'custom_validation_script',
                'validation_message',
            ] as $field
        ) {
            if (trim((string) ($element->{$field} ?? '')) !== '') {
                return true;
            }
        }

        $options = $element->options ?? null;
        if (is_string($options) || $options === null) {
            $options = PackedDataHelper::decodePackedData((string) ($options ?? ''), null);
        }
        if (is_object($options)) {
            $options = (array) $options;
        }
        if (!is_array($options)) {
            return false;
        }

        $defaults = [
            'length' => '',
            'maxlength' => '',
            'password' => 0,
            'readonly' => 0,
            'seperator' => ',',
            'class' => '',
            'allow_raw' => false,
            'allow_html' => false,
        ];

        foreach ($options as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if (array_key_exists((string) $key, $defaults) && $defaults[(string) $key] === $value) {
                continue;
            }

            if ($value === '' || $value === null || $value === false || $value === 0 || $value === '0') {
                continue;
            }

            return true;
        }

        return false;
    }
}
