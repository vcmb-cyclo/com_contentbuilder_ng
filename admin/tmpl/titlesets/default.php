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
$sortableColumns = ['filename', 'name', 'locale', 'source', 'count', 'status'];
?>
<div class="container-fluid">
    <div class="alert alert-info">
        <?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_INTRO'); ?>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <div class="input-group input-group-sm" style="max-width: 24rem;">
            <span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span>
            <input
                type="search"
                class="form-control"
                id="cbng-titlesets-search"
                placeholder="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_TITLESETS_SEARCH_PLACEHOLDER'), ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_TITLESETS_SEARCH_DESC'), ENT_QUOTES, 'UTF-8'); ?>"
                title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_TITLESETS_SEARCH_DESC'), ENT_QUOTES, 'UTF-8'); ?>"
            >
            <button class="btn btn-outline-secondary" type="button" data-cb-titlesets-search-clear title="<?php echo htmlspecialchars(Text::_('JCLEAR'), ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars(Text::_('JCLEAR'), ENT_QUOTES, 'UTF-8'); ?>
            </button>
        </div>
        <div class="dropdown">
        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" id="cbng-titlesets-columns-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_TITLESETS_COLUMNS_DESC'), ENT_QUOTES, 'UTF-8'); ?>">
            <span data-cb-titlesets-columns-count data-cb-columns-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_COLUMNS'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo count($columns); ?>/<?php echo count($columns); ?> <?php echo Text::_('COM_CONTENTBUILDERNG_COLUMNS'); ?></span>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="cbng-titlesets-columns-toggle">
            <?php foreach ($columns as $key => $label) : ?>
                <label class="dropdown-item d-flex align-items-start gap-2 mb-1">
                    <input class="form-check-input mt-1" type="checkbox" checked data-cb-titlesets-column-toggle="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
                    <span><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                </label>
            <?php endforeach; ?>
            <div class="dropdown-divider my-1"></div><button type="button" class="btn btn-link btn-sm px-2" data-cb-titlesets-columns-reset><?php echo Text::_('COM_CONTENTBUILDERNG_RESET'); ?></button>
        </div>
        </div>
    </div>

    <div class="table-responsive"><table class="table table-striped" id="cbng-titlesets">
        <thead>
            <tr>
                <?php foreach ($columns as $key => $label) : ?>
                    <th data-cb-titlesets-column="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" data-cb-titlesets-sort-key="<?php echo htmlspecialchars(in_array($key, $sortableColumns, true) ? $key : '', ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo htmlspecialchars($key === 'actions' ? 'text-end' : '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (in_array($key, $sortableColumns, true)) : ?>
                            <button type="button" class="btn btn-link p-0 text-decoration-none" data-cb-titlesets-sort="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars(Text::sprintf('COM_CONTENTBUILDERNG_TITLESETS_SORT_DESC', $label), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?> <span data-cb-sort-indicator aria-hidden="true"></span>
                            </button>
                        <?php else : ?>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
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
            <tr data-cb-titlesets-row data-cb-titlesets-search="<?php echo htmlspecialchars((string) $item['filename'] . ' ' . $name, ENT_QUOTES, 'UTF-8'); ?>">
                <td data-cb-titlesets-column="filename"><a href="<?php echo Route::_($viewUrl, false); ?>" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_TITLESETS_OPEN_DESC'), ENT_QUOTES, 'UTF-8'); ?>"><code><?php echo htmlspecialchars((string) $item['filename'], ENT_QUOTES, 'UTF-8'); ?></code></a></td>
                <td data-cb-titlesets-column="name"><span class="cb-titlesets-title"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span></td>
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
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2" data-cb-titlesets-pagination>
        <label class="d-flex align-items-center gap-2 mb-0">
            <span><?php echo Text::_('COM_CONTENTBUILDERNG_TITLESETS_DISPLAY'); ?></span>
            <select class="form-select form-select-sm" data-cb-titlesets-page-size>
                <option value="10" selected>10</option><option value="25">25</option><option value="50">50</option>
                <option value="0"><?php echo Text::_('JALL'); ?></option>
            </select>
        </label>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-cb-titlesets-prev title="<?php echo htmlspecialchars(Text::_('JPREV'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo Text::_('JPREV'); ?></button>
            <span data-cb-titlesets-page-info></span>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-cb-titlesets-next title="<?php echo htmlspecialchars(Text::_('JNEXT'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo Text::_('JNEXT'); ?></button>
        </div>
    </div>
</div>
<style>
#cbng-titlesets [data-cb-titlesets-column="name"] { width: 28%; }
.cb-titlesets-title { display: -webkit-box; overflow: hidden; overflow-wrap: anywhere; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const key = 'cbng.titlesets.columns';
    const toggles = [...document.querySelectorAll('[data-cb-titlesets-column-toggle]')];
    const count = document.querySelector('[data-cb-titlesets-columns-count]');
    const tableBody = document.querySelector('#cbng-titlesets tbody');
    const search = document.getElementById('cbng-titlesets-search');
    const clearSearch = document.querySelector('[data-cb-titlesets-search-clear]');
    const pageSizeSelect = document.querySelector('[data-cb-titlesets-page-size]');
    const previousButton = document.querySelector('[data-cb-titlesets-prev]');
    const nextButton = document.querySelector('[data-cb-titlesets-next]');
    const pageInfo = document.querySelector('[data-cb-titlesets-page-info]');
    const collator = new Intl.Collator(document.documentElement.lang || undefined, { numeric: true, sensitivity: 'base' });
    let currentPage = 1;
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
        if (count) count.textContent = visible + '/' + toggles.length + ' ' + (count.dataset.cbColumnsLabel || '');
        try { localStorage.setItem(key, JSON.stringify(state)); } catch (error) {}
    }
    toggles.forEach(function (toggle) { toggle.addEventListener('change', function () { state[toggle.dataset.cbTitlesetsColumnToggle] = toggle.checked; apply(); }); });
    document.querySelector('[data-cb-titlesets-columns-reset]')?.addEventListener('click', function () { state = {}; apply(); });
    function filterRows() {
        const term = (search?.value || '').trim().toLocaleLowerCase();
        const matchingRows = [...document.querySelectorAll('[data-cb-titlesets-row]')].filter(function (row) {
            return term === '' || String(row.dataset.cbTitlesetsSearch || '').toLocaleLowerCase().includes(term);
        });
        const pageSize = Number(pageSizeSelect?.value || 10);
        const pageCount = pageSize === 0 ? 1 : Math.max(1, Math.ceil(matchingRows.length / pageSize));
        currentPage = Math.min(currentPage, pageCount);
        const start = pageSize === 0 ? 0 : (currentPage - 1) * pageSize;
        const visibleRows = new Set(pageSize === 0 ? matchingRows : matchingRows.slice(start, start + pageSize));
        document.querySelectorAll('[data-cb-titlesets-row]').forEach(function (row) { row.hidden = !visibleRows.has(row); });
        if (pageInfo) pageInfo.textContent = currentPage + ' / ' + pageCount;
        if (previousButton) previousButton.disabled = currentPage <= 1;
        if (nextButton) nextButton.disabled = currentPage >= pageCount;
    }
    search?.addEventListener('input', function () { currentPage = 1; filterRows(); });
    clearSearch?.addEventListener('click', function () { if (search) { search.value = ''; search.focus(); filterRows(); } });
    pageSizeSelect?.addEventListener('change', function () { currentPage = 1; filterRows(); });
    previousButton?.addEventListener('click', function () { currentPage = Math.max(1, currentPage - 1); filterRows(); });
    nextButton?.addEventListener('click', function () { currentPage++; filterRows(); });
    document.querySelectorAll('[data-cb-titlesets-sort]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!tableBody) return;
            const column = button.dataset.cbTitlesetsSort;
            const header = button.closest('th');
            const ascending = header?.getAttribute('aria-sort') !== 'ascending';
            document.querySelectorAll('[data-cb-titlesets-sort-key]').forEach(function (cell) { cell.removeAttribute('aria-sort'); cell.querySelector('[data-cb-sort-indicator]')?.replaceChildren(); });
            header?.setAttribute('aria-sort', ascending ? 'ascending' : 'descending');
            button.querySelector('[data-cb-sort-indicator]')?.replaceChildren(document.createTextNode(ascending ? '▲' : '▼'));
            const rows = [...tableBody.querySelectorAll('[data-cb-titlesets-row]')];
            rows.sort(function (left, right) {
                const leftValue = left.querySelector('[data-cb-titlesets-column="' + column + '"]')?.textContent.trim() || '';
                const rightValue = right.querySelector('[data-cb-titlesets-column="' + column + '"]')?.textContent.trim() || '';
                const result = column === 'count' ? Number(leftValue) - Number(rightValue) : collator.compare(leftValue, rightValue);
                return ascending ? result : -result;
            });
            rows.forEach(function (row) { tableBody.appendChild(row); });
            filterRows();
        });
    });
    apply();
    filterRows();
});
</script>
