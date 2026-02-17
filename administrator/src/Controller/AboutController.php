<?php
/**
 * @package     ContentBuilder NG
 * @author      XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\Controller;

\defined('_JEXEC') or die('Restricted access');

use CB\Component\Contentbuilder_ng\Administrator\Helper\PackedDataMigrationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

final class AboutController extends BaseController
{
    protected $default_view = 'about';

    public function migratePackedData(): void
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $user = $app->getIdentity();

        if (!$user->authorise('core.manage', 'com_contentbuilder_ng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        try {
            $summary = PackedDataMigrationHelper::migrate();
            $scanned = (int) ($summary['scanned'] ?? 0);
            $candidates = (int) ($summary['candidates'] ?? 0);
            $migrated = (int) ($summary['migrated'] ?? 0);
            $unchanged = (int) ($summary['unchanged'] ?? 0);
            $errors = (int) ($summary['errors'] ?? 0);

            if ($migrated === 0 && $errors === 0) {
                $message = Text::sprintf(
                    'COM_CONTENTBUILDER_NG_PACKED_MIGRATION_UP_TO_DATE',
                    $scanned
                );
                $this->setMessage($message, 'message');
                $this->setRedirect(Route::_('index.php?option=com_contentbuilder_ng&view=about', false));

                return;
            }

            $message = Text::sprintf(
                'COM_CONTENTBUILDER_NG_PACKED_MIGRATION_SUMMARY',
                $scanned,
                $candidates,
                $migrated,
                $unchanged,
                $errors
            );

            $tableMessages = [];
            $tables = $summary['tables'] ?? [];

            if (is_array($tables)) {
                foreach ($tables as $tableStat) {
                    if (!is_array($tableStat)) {
                        continue;
                    }

                    $tableMessages[] = Text::sprintf(
                        'COM_CONTENTBUILDER_NG_PACKED_MIGRATION_TABLE_SUMMARY',
                        (string) ($tableStat['table'] ?? ''),
                        (string) ($tableStat['column'] ?? ''),
                        (int) ($tableStat['scanned'] ?? 0),
                        (int) ($tableStat['candidates'] ?? 0),
                        (int) ($tableStat['migrated'] ?? 0),
                        (int) ($tableStat['unchanged'] ?? 0),
                        (int) ($tableStat['errors'] ?? 0)
                    );
                }
            }

            if ($tableMessages !== []) {
                $message .= '<br>' . implode('<br>', $tableMessages);
            }

            $level = ($errors > 0) ? 'warning' : 'message';
            $this->setMessage($message, $level);
        } catch (\Throwable $e) {
            $this->setMessage(
                Text::sprintf('COM_CONTENTBUILDER_NG_PACKED_MIGRATION_FAILED', $e->getMessage()),
                'error'
            );
        }

        $this->setRedirect(Route::_('index.php?option=com_contentbuilder_ng&view=about', false));
    }
}
