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

use CB\Component\Contentbuilderng\Administrator\Helper\Logger;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ConnectionFailureException;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Exception\PrepareStatementFailureException;

/**
 * Decides what an exception is allowed to say to the user.
 *
 * Domain exceptions carry already-translated, actionable text and are shown
 * as-is. Database exceptions are not: their message quotes table names,
 * column names and query fragments, which is schema disclosure. Those are
 * logged in full and replaced with a generic message.
 */
trait SafeErrorMessageTrait
{
    protected function safeErrorMessage(\Throwable $e): string
    {
        $isDatabaseFailure = $e instanceof ExecutionFailureException
            || $e instanceof ConnectionFailureException
            || $e instanceof PrepareStatementFailureException
            || $e instanceof \PDOException;

        if (!$isDatabaseFailure) {
            return $e->getMessage();
        }

        Logger::exception($e, [
            'context' => 'admin_controller_database_failure',
            'class'   => static::class,
        ]);

        return Text::_('COM_CONTENTBUILDERNG_ERROR_DATABASE_GENERIC');
    }
}
