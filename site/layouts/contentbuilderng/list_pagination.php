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

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use CB\Component\Contentbuilderng\Site\Service\CompactPaginationService;
use CB\Component\Contentbuilderng\Site\Service\EmbeddedListFieldFilterService;

$pagination = $displayData['pagination'] ?? null;
$lists = (array) ($displayData['lists'] ?? []);
$requestList = (array) ($displayData['requestList'] ?? []);
$navClass = trim((string) ($displayData['navClass'] ?? ''));

$pagTotal = (int) ($pagination->total ?? 0);

if ($pagTotal <= 0) {
    return;
}

$pagLimit = max(1, (int) ($pagination->limit ?? 0));
$pagStart = (int) ($lists['liststart'] ?? ($requestList['start'] ?? 0));
$pagPages = (int) ceil($pagTotal / $pagLimit);
$pagCurrent = $pagPages > 0 ? (int) floor($pagStart / $pagLimit) + 1 : 1;
$pagLastStart = $pagPages > 0 ? max(0, ($pagPages - 1) * $pagLimit) : 0;
$showPagination = $pagPages > 1;
$rangeStart = $pagStart + 1;
$rangeEnd = min($pagStart + $pagLimit, $pagTotal);
$compactPages = CompactPaginationService::pages($pagPages, $pagCurrent);

$input = \CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper::getApplication()->getInput();
$isLimitedEmbeddedList = EmbeddedListFieldFilterService::isEmbeddedRequest($input->getCmd('cblist_embed', ''))
    && $input->getInt('cblist_limit', 0) > 0;
$params = Uri::getInstance()->getQuery(true);
$params['option'] = 'com_contentbuilderng';
$params['task'] = 'list.display';
$params['id'] = $input->getInt('id', 0);
$params['Itemid'] = $input->getInt('Itemid', 0);
$params['list'] = [
    'limit' => $pagLimit,
    'start' => 0,
];

// Only carry an ordering the visitor actually chose. http_build_query()
// keeps empty strings, and an empty list[ordering]= reads downstream as a
// deliberate ordering, which would override the {CBList sort="..."} order.
$listOrdering = trim((string) ($lists['order'] ?? ''));
$listDirection = trim((string) ($lists['order_Dir'] ?? ''));
if ($listOrdering !== '') {
    $params['list']['ordering'] = $listOrdering;
    $params['list']['direction'] = $listDirection;
}
$embeddedSort = trim((string) $input->getString('cblist_sort', ''));
$embeddedDir = trim((string) $input->getString('cblist_dir', ''));
if (EmbeddedListFieldFilterService::isEmbeddedRequest($input->getCmd('cblist_embed', '')) && $embeddedSort !== '') {
    $params['cblist_sort'] = $embeddedSort;
    $params['cblist_dir'] = $embeddedDir;
}

$buildPageLink = static function (int $start) use ($params): string {
    $params['list']['start'] = max(0, $start);
    return Route::_('index.php?' . http_build_query($params), false);
};
?>
<nav class="pagination__wrapper d-flex flex-wrap align-items-center justify-content-start gap-2<?php echo $navClass !== '' ? ' ' . htmlspecialchars($navClass, ENT_QUOTES, 'UTF-8') : ''; ?>" aria-label="<?php echo Text::_('JLIB_HTML_PAGINATION'); ?>">
    <div class="small text-muted me-2 cb-pagination-summary">
        <?php echo Text::sprintf(
            $isLimitedEmbeddedList
                ? 'COM_CONTENTBUILDERNG_LIST_PAGINATION_SUMMARY_DISPLAYED'
                : 'COM_CONTENTBUILDERNG_LIST_PAGINATION_SUMMARY',
            $rangeStart,
            $rangeEnd,
            $pagTotal
        ); ?>
    </div>
    <?php if ($showPagination) : ?>
        <ul class="pagination pagination-sm mb-0 cb-pagination-compact">
            <li class="page-item cb-pagination-jump<?php echo $pagCurrent <= 1 ? ' disabled' : ''; ?>">
                <?php if ($pagCurrent <= 1) : ?>
                    <span class="page-link" aria-disabled="true" aria-label="<?php echo Text::_('JLIB_HTML_START'); ?>"><span aria-hidden="true">&lt;&lt;</span></span>
                <?php else : ?>
                    <a class="page-link" href="<?php echo $buildPageLink(0); ?>" aria-label="<?php echo Text::_('JLIB_HTML_START'); ?>"><span aria-hidden="true">&lt;&lt;</span></a>
                <?php endif; ?>
            </li>
            <li class="page-item<?php echo $pagCurrent <= 1 ? ' disabled' : ''; ?>">
                <?php if ($pagCurrent <= 1) : ?>
                    <span class="page-link" aria-disabled="true" aria-label="<?php echo Text::_('JPREV'); ?>"><span aria-hidden="true">&lt;</span></span>
                <?php else : ?>
                    <a class="page-link" href="<?php echo $buildPageLink($pagStart - $pagLimit); ?>" aria-label="<?php echo Text::_('JPREV'); ?>"><span aria-hidden="true">&lt;</span></a>
                <?php endif; ?>
            </li>
            <?php foreach ($compactPages as $page) : ?>
                <?php if ($page === null) : ?>
                    <li class="page-item disabled cb-pagination-ellipsis" aria-hidden="true">
                        <span class="page-link">…</span>
                    </li>
                    <?php continue; ?>
                <?php endif; ?>
                <?php
                $startForPage = ($page - 1) * $pagLimit;
                $isCurrent = $page === $pagCurrent;
                $isLocalPage = CompactPaginationService::isInLocalWindow($page, $pagPages, $pagCurrent);
                ?>
                <li class="page-item cb-pagination-number<?php echo $isLocalPage ? ' cb-pagination-local' : ' cb-pagination-edge'; ?><?php echo $isCurrent ? ' active' : ''; ?>">
                    <?php if ($isCurrent) : ?>
                        <span class="page-link" aria-current="page"><?php echo $page; ?></span>
                    <?php else : ?>
                        <a class="page-link" href="<?php echo $buildPageLink($startForPage); ?>"><?php echo $page; ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            <li class="page-item<?php echo $pagCurrent >= $pagPages ? ' disabled' : ''; ?>">
                <?php if ($pagCurrent >= $pagPages) : ?>
                    <span class="page-link" aria-disabled="true" aria-label="<?php echo Text::_('JNEXT'); ?>"><span aria-hidden="true">&gt;</span></span>
                <?php else : ?>
                    <a class="page-link" href="<?php echo $buildPageLink($pagStart + $pagLimit); ?>" aria-label="<?php echo Text::_('JNEXT'); ?>"><span aria-hidden="true">&gt;</span></a>
                <?php endif; ?>
            </li>
            <li class="page-item cb-pagination-jump<?php echo $pagCurrent >= $pagPages ? ' disabled' : ''; ?>">
                <?php if ($pagCurrent >= $pagPages) : ?>
                    <span class="page-link" aria-disabled="true" aria-label="<?php echo Text::_('JLIB_HTML_END'); ?>"><span aria-hidden="true">&gt;&gt;</span></span>
                <?php else : ?>
                    <a class="page-link" href="<?php echo $buildPageLink($pagLastStart); ?>" aria-label="<?php echo Text::_('JLIB_HTML_END'); ?>"><span aria-hidden="true">&gt;&gt;</span></a>
                <?php endif; ?>
            </li>
        </ul>
    <?php endif; ?>
</nav>
