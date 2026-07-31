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

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;
use CB\Component\Contentbuilderng\Administrator\Service\FormResolverService;
use CB\Component\Contentbuilderng\Tests\Stubs\Application;
use CB\Component\Contentbuilderng\Tests\Stubs\Database;
use Joomla\CMS\Access\Access;
use PHPUnit\Framework\TestCase;

final class PermissionServiceTest extends TestCase
{
    private PermissionService $service;
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app->setIdentity(0, '', '');
        $this->service = new PermissionService($this->app, new Database(), new FormResolverService($this->app));
        Access::$groupsByUser = [
            0 => [9],
        ];
    }

    /**
     * Mirrors PermissionService::permissionKey(): the stored set is scoped by
     * form id so a set computed for one form cannot authorise another.
     */
    private function storePermissions(int $formId, string $suffix, array $permissions, ?array $recordIds = []): void
    {
        // Neutralise the limit_* / verify_* gates so these tests exercise the
        // group/owner/context logic rather than the quota and verification
        // branches, which have their own coverage.
        $permissions += [
            'limit_edit'  => true,
            'limit_add'   => true,
            'verify_view' => true,
            'verify_new'  => true,
            'verify_edit' => true,
        ];

        $permissions['__cb_context'] = [
            'form_id'    => $formId,
            'record_ids' => $recordIds,
        ];

        $this->app->getSession()->set(
            'com_contentbuilderng.permissions' . $suffix . '.' . $formId,
            $permissions
        );
    }

    public function testAuthorizeFeHonorsInheritedPublicGroupPermission(): void
    {
        $this->app->getInput()->set('id', 7);
        $this->storePermissions(7, '_fe', [
            'published' => true,
            1 => ['listaccess' => true],
        ]);

        self::assertTrue($this->service->authorizeFe('listaccess'));
    }

    public function testAuthorizeFeRejectsMissingInheritedPermission(): void
    {
        $this->app->getInput()->set('id', 7);
        $this->storePermissions(7, '_fe', [
            'published' => true,
            2 => ['listaccess' => true],
        ]);

        self::assertFalse($this->service->authorizeFe('listaccess'));
    }

    // authorize() uses the admin-side session key (no "_fe" suffix).
    // Group 9 inherits from group 1 (see DB stub), so a permission granted to
    // group 1 must be visible to the user who belongs to group 9.
    // Using 'listaccess' avoids the extra limit_* / verify_* guards that only
    // apply to 'edit', 'new', 'view', and 'delete'.
    public function testAuthorizeHonorsAdminSessionPermission(): void
    {
        $this->app->getInput()->set('id', 7);
        $this->storePermissions(7, '', [
            'published' => true,
            1 => ['listaccess' => true],
        ]);

        self::assertTrue($this->service->authorize('listaccess'));
    }

    // Group 2 is not in the inheritance chain of group 9, so no permission is found.
    public function testAuthorizeRejectsMissingAdminSessionPermission(): void
    {
        $this->app->getInput()->set('id', 7);
        $this->storePermissions(7, '', [
            'published' => true,
            2 => ['listaccess' => true],
        ]);

        self::assertFalse($this->service->authorize('listaccess'));
    }

    /**
     * Regression: a permission set computed for form 7 must never authorise a
     * request targeting form 8. The set used to live under a single unscoped
     * session key, so any caller that skipped setPermissions() authorised
     * against whatever the previous page had armed.
     */
    public function testPermissionSetIsNotReusedAcrossForms(): void
    {
        $this->storePermissions(7, '_fe', [
            'published' => true,
            1 => ['delete' => true],
        ]);

        $this->app->getInput()->set('id', 8);

        self::assertFalse($this->service->authorizeFe('delete'));
    }

    /**
     * Regression: the set must not authorise records it was not computed for.
     * The ownership branch validated the record id stored in the session, i.e.
     * the record of the *previous* request, not the one being acted on.
     */
    public function testPermissionSetIsNotReusedAcrossRecords(): void
    {
        $this->app->getInput()->set('id', 7);
        $this->storePermissions(7, '_fe', [
            'published' => true,
            1 => ['delete' => true],
        ], [41]);

        $this->app->getInput()->set('cid', [42]);

        self::assertFalse($this->service->authorizeFe('delete'));
    }

    public function testPermissionSetAuthorisesTheRecordsItWasComputedFor(): void
    {
        $this->app->getInput()->set('id', 7);
        $this->storePermissions(7, '_fe', [
            'published' => true,
            1 => ['delete' => true],
        ], [41, 42]);

        $this->app->getInput()->set('cid', [42]);

        self::assertTrue($this->service->authorizeFe('delete'));
    }

    /**
     * A set stored without context (e.g. written by older code, or never
     * armed at all) must be refused rather than trusted.
     */
    public function testPermissionSetWithoutContextIsRefused(): void
    {
        $this->app->getInput()->set('id', 7);
        $this->app->getSession()->set('com_contentbuilderng.permissions_fe.7', [
            'published' => true,
            1 => ['listaccess' => true],
        ]);

        self::assertFalse($this->service->authorizeFe('listaccess'));
    }

    /**
     * The signed admin storage preview grants access to a whole storage, so it
     * stores a record-agnostic context (null) that must stay usable.
     */
    public function testRecordAgnosticContextAllowsAnyRecord(): void
    {
        $this->app->getInput()->set('id', 0);
        $this->storePermissions(0, '_fe', [
            'published' => true,
            1 => ['listaccess' => true],
        ], null);

        $this->app->getInput()->set('cid', [1234]);

        self::assertTrue($this->service->authorizeFe('listaccess'));
    }

    public function testHasGroupGrantDistinguishesGroupFromOwnerScope(): void
    {
        $this->app->getInput()->set('id', 7);
        $this->storePermissions(7, '_fe', [
            'published' => true,
            'own_fe' => ['delete' => ['own' => true, 'form_id' => 7, 'record_id' => 0]],
        ]);

        self::assertFalse($this->service->hasGroupGrant('delete', 7, '_fe'));

        $this->storePermissions(7, '_fe', [
            'published' => true,
            1 => ['delete' => true],
        ]);

        self::assertTrue($this->service->hasGroupGrant('delete', 7, '_fe'));
    }
}
