<?php

/**
 * @package     ContentBuilderNG
 * @author      Xavier DANO
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace CB\Component\Contentbuilderng\Administrator\Controller\Traits;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;

/**
 * Enforces "core.manage" on com_contentbuilderng for every task of the using
 * controller.
 *
 * Backend tasks are dispatched straight to their own controller, so guarding
 * DisplayController::display() alone left write tasks (DDL, record state,
 * verification flags) reachable by any user who merely holds backend login
 * rights for some other component.
 *
 * Task-level checks (core.create / core.edit / core.edit.state) stay the
 * responsibility of the individual controller: this is the coarse gate, not a
 * replacement for them.
 */
trait ComponentAccessTrait
{
    #[\Override]
    public function execute($task)
    {
        $this->assertComponentAccess();

        return parent::execute($task);
    }

    /**
     * @throws NotAllowed
     */
    protected function assertComponentAccess(): void
    {
        $app = $this->app;

        if (!$app instanceof CMSApplicationInterface) {
            throw new \RuntimeException('Unexpected application instance');
        }

        if (!$app->getIdentity()->authorise('core.manage', 'com_contentbuilderng')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
