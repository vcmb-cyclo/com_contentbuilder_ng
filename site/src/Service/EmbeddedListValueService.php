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

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die('Restricted access');

use CB\Component\Contentbuilderng\Administrator\Extension\ContentbuilderngComponent;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;
use CB\Component\Contentbuilderng\Site\Model\ListModel;
use Joomla\CMS\Application\SiteApplication;

final class EmbeddedListValueService
{
    /**
     * @param array{
     *     id: int,
     *     fields: list<string>,
     *     sort: string,
     *     sort_direction: non-empty-string,
     *     limit: int|null,
     *     offset: int
     * } $options
     *
     * @return array{
     *     value: string|null,
     *     errors: list<array{code: string, parameter: string, value: string, detail: string}>
     * }
     */
    public static function resolve(SiteApplication $app, array $options): array
    {
        $input = $app->getInput();
        $keys = [
            'id',
            'view',
            'task',
            'list',
            'cblist_embed',
            'cblist_fields',
            'cblist_sort',
            'cblist_dir',
            'cblist_limit',
            'cblist_value_output',
            'cb_permission_scope_form_id',
        ];
        $previous = [];
        $previousGetList = $input->get->get('list', [], 'array');

        foreach ($keys as $key) {
            $previous[$key] = $input->get($key, null, 'raw');
        }

        try {
            if ($options['limit'] !== null && $options['offset'] >= $options['limit']) {
                return ['value' => null, 'errors' => []];
            }

            $input->set('id', $options['id']);
            $input->set('view', 'list');
            $input->set('task', 'list.display');
            $input->set('list', [
                'limit' => 1,
                'start' => $options['offset'],
            ]);
            $input->get->set('list', [
                'limit' => 1,
                'start' => $options['offset'],
            ]);
            $input->set('cblist_embed', EmbeddedListFieldFilterService::REQUEST_CONTEXT);
            $input->set('cblist_fields', $options['fields'][0]);
            $input->set('cblist_sort', $options['sort']);
            $input->set('cblist_dir', $options['sort_direction']);
            $input->set('cblist_limit', $options['limit'] ?? 0);
            $input->set('cblist_value_output', 1);

            $permissions = PermissionService::createFromRuntimeContext();
            $permissions->setPermissions($options['id'], 0, '_fe');
            if (!$permissions->authorizeFe('listaccess')) {
                return ['value' => null, 'errors' => []];
            }

            /** @var ContentbuilderngComponent $component */
            $component = $app->bootComponent('com_contentbuilderng');
            $model = $component->getMVCFactory()->createModel('List', 'Site', ['ignore_request' => false]);
            if (!$model instanceof ListModel) {
                return ['value' => null, 'errors' => []];
            }

            $data = $model->getData();

            if (!is_object($data)) {
                return ['value' => null, 'errors' => []];
            }

            foreach ((array) ($data->embedded_list_validation_errors ?? []) as $error) {
                if (is_array($error) && ($error['parameter'] ?? '') === 'sort') {
                    return [
                        'value' => null,
                        'errors' => [[
                            'code' => 'invalid_value',
                            'parameter' => 'sort',
                            'value' => (string) ($error['value'] ?? $options['sort']),
                            'detail' => 'sort',
                        ]],
                    ];
                }
            }

            $form = $data->form ?? null;
            $names = is_object($form) && method_exists($form, 'getElementNames')
                ? (array) $form->getElementNames()
                : [];
            $match = EmbeddedListFieldFilterService::matchFieldSelectors(
                array_keys($names),
                $names,
                $options['fields'][0]
            );

            if ($match['unknown'] !== [] || $match['columns'] === []) {
                return [
                    'value' => null,
                    'errors' => [[
                        'code' => 'invalid_value',
                        'parameter' => 'fields',
                        'value' => $options['fields'][0],
                        'detail' => 'fields',
                    ]],
                ];
            }

            $item = (array) ($data->items ?? []);
            $recordId = (int) (($item[0]->colRecord ?? 0));
            if ($recordId < 1 || !is_object($form) || !method_exists($form, 'getRecord')) {
                return ['value' => null, 'errors' => []];
            }

            $referenceId = (string) $match['columns'][0];
            foreach ((array) $form->getRecord($recordId, false, -1, true) as $recordValue) {
                if ((string) ($recordValue->recElementId ?? '') === $referenceId) {
                    return ['value' => (string) ($recordValue->recValue ?? ''), 'errors' => []];
                }
            }

            return ['value' => null, 'errors' => []];
        } catch (\InvalidArgumentException) {
            return [
                'value' => null,
                'errors' => [[
                    'code' => 'invalid_value',
                    'parameter' => 'fields',
                    'value' => $options['fields'][0],
                    'detail' => 'fields',
                ]],
            ];
        } finally {
            $input->get->set('list', $previousGetList);
            foreach ($previous as $key => $value) {
                $input->set($key, $value);
            }
        }
    }
}
