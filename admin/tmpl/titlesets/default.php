<?php

declare(strict_types=1);

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<?php
$columns = [
    'filename' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_FILENAME'),
    'name' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_NAME'),
    'locale' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_LOCALE'),
    'source' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_SOURCE'),
    'count' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_COUNT'),
    'status' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_STATUS'),
    'actions' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_ACTIONS'),
];
?>
<div class="container-fluid">
    <div class="alert alert-info">
        <?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_INTRO'); ?>
    </div>

    <div class="d-flex justify-content-end mb-2"><div class="dropdown">
        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" id="cbng-titlesets-columns-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_TITLESETS_COLUMNS_DESC'), ENT_QUOTES, 'UTF-8'); ?>">
            <span data-cb-titlesets-columns-count><?php echo count($columns); ?>/<?php echo count($columns); ?> <?php echo Text::_('COM_CONTENTBUILDERNG_COLUMNS'); ?></span>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="cbng-titlesets-columns-toggle">
            <?php foreach ($columns as $key => $label) : ?>
                <label class="dropdown-item d-flex align-items-start gap-2 mb-1">
                    <input class="form-check-input mt-1" type="checkbox" checked data-cb-titlesets-column-toggle="<?php echo $key; ?>">
                    <span><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                </label>
            <?php endforeach; ?>
            <div class="dropdown-divider my-1"></div><button type="button" class="btn btn-link btn-sm px-2" data-cb-titlesets-columns-reset><?php echo Text::_('COM_CONTENTBUILDERNG_RESET'); ?></button>
        </div>
    </div></div>

    <div class="table-responsive"><table class="table table-striped" id="cbng-titlesets">
        <thead>
            <tr>
                <?php foreach ($columns as $key => $label) : ?>
                    <th data-cb-titlesets-column="<?php echo $key; ?>"<?php echo $key === 'actions' ? ' class="text-end"' : ''; ?>>
                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $item) : ?>
            <?php
            $isCustom = $item['source'] === 'custom';
            $viewUrl = 'index.php?option=com_contentbuilderng&view=titleset&filename='
                . rawurlencode((string) $item['filename']) . '&source=' . $item['source'];
            $actionUrl = $viewUrl . ($isCustom ? '' : '&duplicate=1');
            $name = (string) ($item['metadata']['name'] ?? '');
            $locale = (string) ($item['metadata']['locale'] ?? '');
            $statusLabel = Text::_(
                'COM_CONTENTBUILDERNG_TITLESETS_STATUS_' . strtoupper((string) $item['status'])
            );
            ?>
            <tr>
                <td data-cb-titlesets-column="filename"><a href="<?php echo Route::_($viewUrl, false); ?>" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_TITLESETS_OPEN_DESC'), ENT_QUOTES, 'UTF-8'); ?>"><code><?php echo htmlspecialchars((string) $item['filename'], ENT_QUOTES, 'UTF-8'); ?></code></a></td>
                <td data-cb-titlesets-column="name"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td data-cb-titlesets-column="locale"><?php echo htmlspecialchars($locale, ENT_QUOTES, 'UTF-8'); ?></td>
                <td data-cb-titlesets-column="source"><?php echo Text::_($isCustom
                    ? 'COM_CONTENTBUILDERNG_TITLESETS_SOURCE_CUSTOM'
                    : 'COM_CONTENTBUILDERNG_TITLESETS_SOURCE_PROVIDED'); ?></td>
                <td data-cb-titlesets-column="count"><?php echo (int) $item['count']; ?></td>
                <td data-cb-titlesets-column="status"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                <td data-cb-titlesets-column="actions" class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo Route::_($actionUrl, false); ?>" title="<?php echo htmlspecialchars(Text::_($isCustom ? 'COM_CONTENTBUILDERNG_TITLESETS_EDIT_DESC' : 'COM_CONTENTBUILDERNG_TITLESETS_DUPLICATE_DESC'), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo Text::_($isCustom ? 'JACTION_EDIT' : 'COM_CONTENTBUILDERNG_TITLESETS_DUPLICATE'); ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($this->items === []) : ?>
            <tr><td colspan="7"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const key = 'cbng.titlesets.columns';
    const toggles = [...document.querySelectorAll('[data-cb-titlesets-column-toggle]')];
    const count = document.querySelector('[data-cb-titlesets-columns-count]');
    let state = {};
    try { state = JSON.parse(localStorage.getItem(key) || '{}') || {}; } catch (error) {}
    function apply() {
        let visible = 0;
        toggles.forEach(function (toggle) {
            const column = toggle.dataset.cbTitlesetsColumnToggle;
            const shown = state[column] !== false;
            toggle.checked = shown;
            document.querySelectorAll('[data-cb-titlesets-column="' + column + '"]').forEach(function (cell) { cell.hidden = !shown; });
            if (shown) visible++;
        });
        if (count) count.textContent = visible + '/' + toggles.length + ' ' + <?php echo json_encode(Text::_('COM_CONTENTBUILDERNG_COLUMNS'), JSON_UNESCAPED_UNICODE); ?>;
        try { localStorage.setItem(key, JSON.stringify(state)); } catch (error) {}
    }
    toggles.forEach(function (toggle) { toggle.addEventListener('change', function () { state[toggle.dataset.cbTitlesetsColumnToggle] = toggle.checked; apply(); }); });
    document.querySelector('[data-cb-titlesets-columns-reset]')?.addEventListener('click', function () { state = {}; apply(); });
    apply();
});
</script>
