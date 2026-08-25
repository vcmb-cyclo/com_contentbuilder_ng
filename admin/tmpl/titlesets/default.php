<?php

declare(strict_types=1);

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="container-fluid">
    <div class="alert alert-info">
        <?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_INTRO'); ?>
    </div>

    <table class="table table-striped" id="cbng-titlesets">
        <thead>
            <tr>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_FILENAME'); ?></th>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_NAME'); ?></th>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_LOCALE'); ?></th>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_SOURCE'); ?></th>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_COUNT'); ?></th>
                <th><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_STATUS'); ?></th>
                <th class="text-end"><?php echo Text::_('JACTIONS'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $item) : ?>
            <?php
            $isCustom = $item['source'] === 'custom';
            $url = 'index.php?option=com_contentbuilderng&view=titleset&filename='
                . rawurlencode((string) $item['filename']) . '&source=' . $item['source']
                . ($isCustom ? '' : '&duplicate=1');
            $name = (string) ($item['metadata']['name'] ?? '');
            $locale = (string) ($item['metadata']['locale'] ?? '');
            $statusLabel = Text::_(
                'COM_CONTENTBUILDERNG_TITLESETS_STATUS_' . strtoupper((string) $item['status'])
            );
            ?>
            <tr>
                <td><code><?php echo htmlspecialchars((string) $item['filename'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($locale, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo Text::_($isCustom
                    ? 'COM_CONTENTBUILDERNG_TITLESETS_SOURCE_CUSTOM'
                    : 'COM_CONTENTBUILDERNG_TITLESETS_SOURCE_PROVIDED'); ?></td>
                <td><?php echo (int) $item['count']; ?></td>
                <td><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo Route::_($url, false); ?>">
                        <?php echo Text::_($isCustom ? 'JACTION_EDIT' : 'COM_CONTENTBUILDERNG_TITLESETS_DUPLICATE'); ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($this->items === []) : ?>
            <tr><td colspan="7"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
