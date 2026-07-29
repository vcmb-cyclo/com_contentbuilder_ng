<?php

namespace CB\Plugin\Content\ContentbuilderngStats\Extension;

/**
 * @package     ContentBuilderNG Stats
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use CB\Component\Contentbuilderng\Site\Service\StatsService;
use CB\Component\Contentbuilderng\Site\Service\StatsFilterValueService;
use CB\Component\Contentbuilderng\Site\Service\StatsHideOptionsService;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;
use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use CB\Plugin\Content\ContentbuilderngStats\Service\PiePresentationService;
use CB\Plugin\Content\ContentbuilderngStats\Service\TotalPresentationService;
use CB\Plugin\Content\ContentbuilderngStats\Service\ManualValuesException;
use CB\Plugin\Content\ContentbuilderngStats\Service\ManualValuesParser;
use CB\Plugin\Content\ContentbuilderngStats\Service\ManualExportService;
use CB\Plugin\Content\ContentbuilderngStats\Service\TagSyntaxService;
use CB\Plugin\Content\ContentbuilderngStats\Service\IdSumException;
use CB\Plugin\Content\ContentbuilderngStats\Service\IdSumService;
use CB\Plugin\Content\ContentbuilderngStats\Service\DisplayOptionsService;
use CB\Plugin\Content\ContentbuilderngStats\Service\TableHeaderService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;

final class ContentbuilderngStats extends CMSPlugin implements SubscriberInterface
{
    private const RADAR_MIN_AXES = 3;
    private const RADAR_MAX_AXES = 8;

    protected $autoloadLanguage = true;
    private static int $pieInstance = 0;
    private static int $barInstance = 0;
    private static int $chartInstance = 0;
    private static bool $pieAssetsLoaded = false;
    private static bool $barAssetsLoaded = false;
    private static bool $chartAssetsLoaded = false;
    private static bool $chartAssetRegistryLoaded = false;
    private static bool $manualExportAssetsLoaded = false;

    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    public function onContentPrepare(EventInterface $event): void
    {
        $article = $event->getArgument('subject');

        if (!is_object($article) || !isset($article->text) || stripos((string) $article->text, '{CBStats') === false) {
            return;
        }

        $article->text = preg_replace_callback(
            TagSyntaxService::TAG_PATTERN,
            fn(array $match): string => $this->renderStatsTag((string) ($match[1] ?? '')),
            (string) $article->text
        );
    }

    private function renderStatsTag(string $rawAttributes): string
    {
        $attributes = TagSyntaxService::parseAttributes($rawAttributes);
        $source = TagSyntaxService::normalizeKeyword((string) ($attributes['source'] ?? 'view'));
        $manual = $source === 'manual';
        $idValue = trim((string) ($attributes['id'] ?? ''));
        $idSumValue = trim((string) ($attributes['idsum'] ?? ''));
        $formId = (int) ($idValue !== '' ? $idValue : $this->extractId($rawAttributes));
        $formIds = [];
        $debugRequested = $this->isEnabled((string) ($attributes['debug'] ?? '0'));
        $debug = false;
        $output = TagSyntaxService::normalizeKeyword((string) ($attributes['output'] ?? 'total'));
        $allowedOutputs = [
            'total', 'table', 'form_name', 'sum', 'min', 'max', 'avg',
            'json', 'pie', 'bar', 'histogram', 'line', 'radar',
        ];
        $field = trim((string) ($attributes['field'] ?? ''));
        $filter = TagSyntaxService::resolveFilter($attributes);
        $filterField = $filter['field'];
        $filterValue = $filter['value'];
        $add = trim((string) ($attributes['add'] ?? ''));
        $titles = trim((string) ($attributes['titles'] ?? ''));
        $ranges = trim((string) ($attributes['ranges'] ?? ''));
        $headers = trim((string) ($attributes['headers'] ?? ''));
        $title = trim((string) ($attributes['title'] ?? ''));
        $background = TotalPresentationService::validateBackground((string) ($attributes['background'] ?? ''));
        $sort = TagSyntaxService::normalizeKeyword((string) ($attributes['sort'] ?? 'none'));
        $dir = TagSyntaxService::normalizeKeyword((string) ($attributes['dir'] ?? 'asc'));
        $values = (string) ($attributes['values'] ?? '');
        $exportManual = ManualExportService::isRequested((string) ($attributes['export'] ?? ''));

        try {
            $limit = DisplayOptionsService::parseLimit($attributes);
            $hideOptions = StatsHideOptionsService::fromAttributes($attributes);
            $headerMappings = StatsService::parseFieldStatsHeaders($headers);

            if (!in_array($source, ['view', 'manual'], true)) {
                throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_SOURCE'), 400);
            }

            if ($manual) {
                return $this->renderManualStats(
                    $values,
                    $output,
                    $sort,
                    $dir,
                    $add,
                    $titles,
                    $headers,
                    $headerMappings,
                    $title,
                    $background,
                    $exportManual,
                    $limit,
                    $hideOptions
                );
            }

            $formIds = IdSumService::resolveSourceIds($idValue, $idSumValue, $formId);

            if (!class_exists(StatsService::class)) {
                $servicePath = JPATH_ROOT . '/components/com_contentbuilderng/src/Service/StatsService.php';

                if (is_file($servicePath)) {
                    require_once $servicePath;
                }
            }
            if (!class_exists(StatsFilterValueService::class)) {
                $servicePath = JPATH_ROOT . '/components/com_contentbuilderng/src/Service/StatsFilterValueService.php';

                if (is_file($servicePath)) {
                    require_once $servicePath;
                }
            }

            $statsService = new StatsService(RuntimeContextHelper::getDatabase());
            $debug = $debugRequested
                && $formIds !== []
                && array_reduce(
                    $formIds,
                    static fn(bool $enabled, int $id): bool => $enabled && $statsService->isFormDebugEnabled($id),
                    true
                );

            if ($formIds === [] || min($formIds) < 1) {
                throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_ID_REQUIRED'), 400);
            }

            foreach ($formIds as $id) {
                if (!$this->canViewStats($id)) {
                    throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_ACCESS_DENIED'), 403);
                }
            }

            if (!in_array($output, $allowedOutputs, true)) {
                throw new \RuntimeException(
                    Text::sprintf('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_OUTPUT', $output),
                    400
                );
            }

            StatsHideOptionsService::validateForOutput($hideOptions, $output);

            $listOutputs = ['table', 'json', 'pie', 'bar', 'histogram', 'line', 'radar'];
            $fieldOutputs = [...$listOutputs, 'sum', 'min', 'max', 'avg'];

            if ((in_array($output, $fieldOutputs, true) || $idSumValue !== '') && $field === '') {
                throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_FIELD_REQUIRED'), 400);
            }

            if ($idSumValue !== '' && $output === 'form_name') {
                throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_IDSUM_FORM_NAME'), 400);
            }

            if (($filterField === '') !== ($filterValue === '')) {
                throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_FILTER'), 400);
            }

            $filterValues = (new StatsFilterValueService())->parseAlternatives($filterValue);

            if ($filterValue !== '' && $filterValues === []) {
                throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_FILTER'), 400);
            }

            if (in_array($output, $listOutputs, true)) {
                if (!in_array($sort, ['none', 'title', 'value'], true)) {
                    throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_SORT'), 400);
                }

                if (!in_array($dir, ['asc', 'desc'], true)) {
                    throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_DIR'), 400);
                }
            }

            $payloads = IdSumService::collectPayloads(
                $formIds,
                static fn(int $id, array $options): array => $statsService->getStatsPayload($id, $options),
                [
                    'field' => $field,
                    'filter' => [
                        'field' => $filterField,
                        'value' => $filterValue,
                        'values' => $filterValues,
                    ],
                ]
            );

            $payload = $idSumValue !== '' ? IdSumService::mergePayloads($payloads) : $payloads[0];
            $total = $payload['records']['total'] ?? null;
            $debugSource = $idSumValue !== '' ? implode('+', $formIds) : (string) $formId;

            if ($debug && $total === null) {
                return Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_UNAVAILABLE');
            }

            if ($debug && $output === 'total') {
                return $this->renderDebugMessage(
                    Text::sprintf('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_TOTAL', $debugSource, (int) ($total ?? 0))
                );
            }

            if ($debug && in_array($output, ['sum', 'min', 'max', 'avg'], true)) {
                $numericValue = $payload['field'][$output] ?? null;
                return $this->renderDebugMessage(Text::sprintf(
                    'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_AGGREGATE',
                    $debugSource,
                    $output,
                    $numericValue !== null
                        ? (string) $numericValue
                        : Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_NON_NUMERIC')
                ));
            }

            $locale = Factory::getApplication()->getLanguage()->getTag();
            $rangeDefinitions = StatsService::parseFieldStatsRanges($ranges);
            $usesRanges = $rangeDefinitions !== [];
            $fullFieldStats = $this->getFieldStats(
                $payload,
                $usesRanges ? 'none' : $sort,
                $dir,
                $locale,
                in_array($output, $listOutputs, true) ? $add : '',
                in_array($output, $listOutputs, true) ? $titles : '',
                $rangeDefinitions
            );
            $fieldStats = in_array($output, $listOutputs, true)
                ? DisplayOptionsService::applyLimit($fullFieldStats, $limit)
                : $fullFieldStats;
            $fieldTotal = array_sum(array_column($fieldStats, 'value'));
            $displayTotal = $usesRanges ? (int) ($payload['records']['total'] ?? 0) : $fieldTotal;

            if ($debug && in_array($output, ['json', 'pie', 'bar', 'histogram', 'line', 'radar'], true)) {
                return $this->renderDebugMessage(Text::sprintf(
                    'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_ITEMS',
                    $debugSource,
                    $output,
                    count($fieldStats)
                ));
            }

            return match ($output) {
                'form_name' => htmlspecialchars($this->getFormName($payload), ENT_QUOTES, 'UTF-8'),
                'table' => $this->renderTable(
                    $payload,
                    $fieldStats,
                    $displayTotal,
                    $title,
                    $background,
                    $headerMappings,
                    $headers,
                    $exportManual,
                    $hideOptions
                ),
                'json' => $this->renderJson($fieldStats),
                'pie' => $this->renderPie($payload, $fieldStats, $displayTotal, $title, $background, $headers, $exportManual, $hideOptions),
                'bar' => $this->renderBar($payload, $fieldStats, $displayTotal, $title, $background, $headers, $exportManual, $hideOptions),
                'histogram', 'line', 'radar' => $this->renderGenericChart(
                    $payload,
                    $fieldStats,
                    $displayTotal,
                    $output,
                    $title,
                    $background,
                    $headers,
                    $exportManual,
                    $hideOptions
                ),
                'total' => (string) StatsService::resolveCbstatsOutput($payload, 'total'),
                'sum' => $this->renderSum($payload),
                'min' => $this->renderNumericFieldValue($payload, 'min'),
                'max' => $this->renderNumericFieldValue($payload, 'max'),
                'avg' => $this->renderNumericFieldValue($payload, 'avg'),
            };
        } catch (\Throwable $exception) {
            if ($exception instanceof ManualValuesException) {
                $message = $exception->getEntry() === ''
                    ? Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_MANUAL_VALUES_REQUIRED')
                    : Text::sprintf('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_MANUAL_VALUE_INVALID', $exception->getEntry());

                return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            }

            if ($exception instanceof IdSumException) {
                $message = match ($exception->getCode()) {
                    IdSumException::TOO_FEW => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_IDSUM_TOO_FEW'),
                    IdSumException::TOO_MANY => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_IDSUM_TOO_MANY'),
                    IdSumException::DUPLICATE_ID => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_IDSUM_DUPLICATE'),
                    IdSumException::CONFLICT => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_ID_AND_IDSUM'),
                    default => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_IDSUM_INVALID'),
                };

                return $debugRequested
                    ? $this->renderDebugMessage($message)
                    : Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_INVALID_REQUEST');
            }

            if (
                $exception instanceof \InvalidArgumentException
                && $exception->getCode() === DisplayOptionsService::INVALID_LIMIT
            ) {
                $message = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_LIMIT');

                return $debugRequested
                    ? $this->renderDebugMessage($message)
                    : Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_INVALID_REQUEST');
            }

            if (
                $exception instanceof \InvalidArgumentException
                && in_array($exception->getCode(), [
                    StatsHideOptionsService::INVALID_ITEM,
                    StatsHideOptionsService::INVALID_SEPARATOR,
                    StatsHideOptionsService::NOT_APPLICABLE,
                    StatsHideOptionsService::ALL_HIDDEN,
                    StatsHideOptionsService::LEGACY_TOTAL,
                ], true)
            ) {
                return htmlspecialchars($this->getHideOptionsErrorMessage($exception), ENT_QUOTES, 'UTF-8');
            }

            if (
                $exception instanceof \InvalidArgumentException
                && $exception->getCode() === StatsService::CBSTATS_ERROR_INVALID_RANGES
            ) {
                $message = $this->getFieldStatsErrorMessage($exception);

                return $debug
                    ? $this->renderDebugMessage($message)
                    : htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            }

            if (!$debug) {
                return (int) $exception->getCode() === 400 || $exception instanceof \InvalidArgumentException
                    ? Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_INVALID_REQUEST')
                    : Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_UNAVAILABLE');
            }

            if ($exception instanceof \InvalidArgumentException) {
                return $this->renderDebugMessage($this->getFieldStatsErrorMessage($exception));
            }

            $code = (int) $exception->getCode();

            return $code >= 400 && $code < 500
                ? $this->renderDebugMessage($exception->getMessage())
                : Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_UNAVAILABLE');
        }
    }

    private function renderDebugMessage(string $message): string
    {
        return htmlspecialchars(
            Text::sprintf('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_MESSAGE', $message),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    private function getFieldStatsErrorMessage(\InvalidArgumentException $exception): string
    {
        return match ($exception->getCode()) {
            StatsService::CBSTATS_ERROR_INVALID_RANGES => Text::sprintf(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_RANGES',
                $exception->getMessage()
            ),
            StatsService::CBSTATS_ERROR_INVALID_TITLES => Text::_(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_TITLES'
            ),
            StatsService::CBSTATS_ERROR_INVALID_HEADERS => Text::_(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_HEADERS'
            ),
            default => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_ADD'),
        };
    }

    private function getHideOptionsErrorMessage(\InvalidArgumentException $exception): string
    {
        if ($exception->getCode() === StatsHideOptionsService::NOT_APPLICABLE) {
            [$item, $output] = array_pad(explode('|', $exception->getMessage(), 2), 2, '');

            return Text::sprintf(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HIDE_NOT_APPLICABLE',
                $item,
                $output
            );
        }

        return match ($exception->getCode()) {
            StatsHideOptionsService::INVALID_SEPARATOR => Text::sprintf(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HIDE_INVALID_SEPARATOR',
                $exception->getMessage()
            ),
            StatsHideOptionsService::ALL_HIDDEN => Text::_(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HIDE_ALL_HIDDEN'
            ),
            StatsHideOptionsService::LEGACY_TOTAL => Text::_(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HIDE_LEGACY_TOTAL'
            ),
            default => Text::sprintf(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HIDE_INVALID_ITEM',
                $exception->getMessage()
            ),
        };
    }

    private function getFormName(array $payload): string
    {
        return (string) StatsService::resolveCbstatsOutput($payload, 'form_name');
    }

    private function canViewStats(int $formId): bool
    {
        try {
            $app = Factory::getApplication();
            $frontend = $app->isClient('site');
            $permissions = PermissionService::createFromRuntimeContext();

            if ($frontend) {
                $permissions->setPermissions($formId, 0, '_fe');

                if ($permissions->authorizeFe('stats')) {
                    return true;
                }
            }

            $permissions->setPermissions($formId);

            return $permissions->authorize('stats');
        } catch (\Throwable) {
            return false;
        }
    }

    private function extractId(string $rawAttributes): int
    {
        $rawAttributes = TagSyntaxService::normalizeMarkup($rawAttributes);

        if (preg_match('/\bid\s*[:=]\s*["\']?([0-9]+)/iu', $rawAttributes, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    private function isEnabled(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }

    private function renderSum(array $payload): string
    {
        return $this->renderNumericFieldValue($payload, 'sum');
    }

    private function renderNumericFieldValue(array $payload, string $key): string
    {
        $value = StatsService::resolveCbstatsOutput($payload, $key);

        if ($key === 'avg') {
            $formatter = new \NumberFormatter(
                Factory::getApplication()->getLanguage()->getTag(),
                \NumberFormatter::DECIMAL
            );
            $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
            $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
            $formatter->setAttribute(\NumberFormatter::GROUPING_USED, 0);
            $formatted = $formatter->format((float) $value, \NumberFormatter::TYPE_DOUBLE);

            return $formatted === false ? $this->formatNumber(round((float) $value, 2)) : $formatted;
        }

        return $value == (int) $value ? (string) (int) $value : (string) $value;
    }

    /**
     * @return list<array{label: string, value: int|float}>
     */
    private function getFieldStats(
        array $payload,
        string $sort,
        string $dir,
        string $locale,
        string $add,
        string $titles,
        array $ranges = []
    ): array
    {
        $field = (array) ($payload['field'] ?? []);
        $additions = $field === [] ? [] : StatsService::parseFieldStatsAdditions($add);
        $titleMappings = $field === [] ? [] : StatsService::parseFieldStatsTitles($titles);
        $values = (array) ($field['values'] ?? []);

        if ($ranges !== []) {
            $values = StatsService::applyFieldStatsRanges($values, $ranges);
        }

        return StatsService::normalizeFieldStats(
            $values,
            $sort,
            $dir,
            $locale,
            $additions,
            $titleMappings
        );
    }

    /**
     * @param list<array{label: string, value: int|float}> $fieldStats
     */
    private function renderJson(array $fieldStats): string
    {
        $json = json_encode($fieldStats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        return $json === false ? '[]' : $json;
    }

    /**
     * @param list<array{label: string, value: int|float}> $fieldStats
     */
    private function renderTable(
        array $payload,
        array $fieldStats,
        int|float $total,
        string $title,
        string $background,
        array $headerMappings,
        string $headers,
        bool $exportManual = false,
        array $hideOptions = ['total' => false, 'values' => false, 'graph' => false]
    ): string
    {
        $field = (array) ($payload['field'] ?? []);

        if ($fieldStats === []) {
            return '<span class="cbstats cbstats-empty">0</span>';
        }

        $this->loadDataTableAssets();

        $resolvedHeaders = TableHeaderService::resolve(
            $field,
            $headerMappings,
            Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_VALUE'),
            Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_TOTAL')
        );
        $html = '<div class="cbstats-table-wrapper cbstats-card"' . $this->renderBackgroundStyle($background) . '><table class="table table-sm cbstats-table">'
            . '<thead><tr>'
            . '<th scope="col" class="cbstats-table-label">'
            . htmlspecialchars($resolvedHeaders['label'], ENT_QUOTES, 'UTF-8') . '</th>'
            . '<th scope="col" class="cbstats-table-number">'
            . htmlspecialchars($resolvedHeaders['total'], ENT_QUOTES, 'UTF-8') . '</th>'
            . '</tr></thead><tbody>';

        foreach ($fieldStats as $item) {
            $html .= '<tr>'
                . '<td class="cbstats-table-label">' . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td class="cbstats-table-number">' . $this->formatNumber($item['value']) . '</td>'
                . '</tr>';
        }

        $html .= '</tbody>';

        if (!$hideOptions['total']) {
            $html .= '<tfoot><tr class="cbstats-total-row"><th scope="row" class="cbstats-table-label cbstats-total-label">'
                . $this->renderTotalLabel($title) . '</th><td class="cbstats-table-number cbstats-total-value"><strong>'
                . $this->formatNumber($total) . '</strong></td></tr></tfoot>';
        }

        $html .= '</table></div>';

        return $html . ($exportManual ? $this->renderManualExport(
            $fieldStats,
            'table',
            $title,
            $background,
            $headers,
            $hideOptions
        ) : '');
    }

    /**
     * @param list<array{label: string, value: int|float}> $fieldStats
     */
    private function renderPie(
        array $payload,
        array $fieldStats,
        int|float $total,
        string $title,
        string $background,
        string $headers,
        bool $exportManual = false,
        array $hideOptions = ['total' => false, 'values' => false, 'graph' => false]
    ): string
    {
        if ($fieldStats === []) {
            return '<span class="cbstats-pie-empty">'
                . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_NO_DATA'), ENT_QUOTES, 'UTF-8')
                . '</span>';
        }

        $field = (array) ($payload['field'] ?? []);
        $fieldLabel = (string) ($field['label'] ?? $field['requested'] ?? Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_VALUE'));
        $locale = Factory::getApplication()->getLanguage()->getTag();
        $presentation = PiePresentationService::prepare($fieldStats, $locale, $total);
        $items = $presentation['items'];
        $html = '<section class="cbstats-pie cbstats-card"';

        if (!$hideOptions['graph']) {
            $this->loadPieAssets();
            $instanceId = 'cbstats-pie-' . ++self::$pieInstance;
            $json = json_encode(
                ['items' => $items, 'showValues' => !$hideOptions['values']],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            );
            $encodedPayload = htmlspecialchars($json === false ? '{"items":[]}' : $json, ENT_QUOTES, 'UTF-8');
            $ariaLabel = Text::sprintf('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_PIE_ARIA_LABEL', $fieldLabel);
            $html .= ' data-cbstats-pie="' . $encodedPayload . '"'
                . $this->renderBackgroundStyle($background) . '>'
                . '<div class="cbstats-pie-chart">'
                . '<canvas id="' . $instanceId . '" class="cbstats-pie-canvas" role="img" aria-label="'
                . htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_CHART_UNAVAILABLE'), ENT_QUOTES, 'UTF-8')
                . '</canvas></div>';
        } else {
            $html .= $this->renderBackgroundStyle($background) . '>';
        }

        return $html . $this->renderChartDetails(
            $items,
            $total,
            $title,
            'pie',
            $background,
            $headers,
            $exportManual,
            $hideOptions
        );
    }

    /**
     * @param list<array{label: string, value: int|float}> $fieldStats
     */
    private function renderBar(
        array $payload,
        array $fieldStats,
        int|float $total,
        string $title,
        string $background,
        string $headers,
        bool $exportManual = false,
        array $hideOptions = ['total' => false, 'values' => false, 'graph' => false]
    ): string
    {
        if ($fieldStats === []) {
            return '<span class="cbstats-bar-empty">'
                . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_NO_DATA'), ENT_QUOTES, 'UTF-8')
                . '</span>';
        }

        $field = (array) ($payload['field'] ?? []);
        $fieldLabel = (string) ($field['label'] ?? $field['requested'] ?? Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_VALUE'));
        $locale = Factory::getApplication()->getLanguage()->getTag();
        $presentation = PiePresentationService::prepare($fieldStats, $locale, $total);
        $items = $presentation['items'];
        $html = '<section class="cbstats-bar cbstats-card"';

        if (!$hideOptions['graph']) {
            $this->loadBarAssets();
            $instanceId = 'cbstats-bar-' . ++self::$barInstance;
            $json = json_encode(
                ['items' => $items, 'showValues' => !$hideOptions['values']],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            );
            $encodedPayload = htmlspecialchars($json === false ? '{"items":[]}' : $json, ENT_QUOTES, 'UTF-8');
            $ariaLabel = Text::sprintf('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_BAR_ARIA_LABEL', $fieldLabel);
            $html .= ' data-cbstats-bar="' . $encodedPayload . '"'
                . $this->renderBackgroundStyle($background) . '>'
                . '<div class="cbstats-bar-chart" style="--cbstats-bar-items:' . count($items) . '">'
                . '<canvas id="' . $instanceId . '" class="cbstats-bar-canvas" role="img" aria-label="'
                . htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_CHART_UNAVAILABLE'), ENT_QUOTES, 'UTF-8')
                . '</canvas></div>';
        } else {
            $html .= $this->renderBackgroundStyle($background) . '>';
        }

        return $html . $this->renderChartDetails(
            $items,
            $total,
            $title,
            'bar',
            $background,
            $headers,
            $exportManual,
            $hideOptions
        );
    }

    /**
     * @param list<array{label: string, value: int|float}> $fieldStats
     */
    private function renderGenericChart(
        array $payload,
        array $fieldStats,
        int|float $total,
        string $output,
        string $title,
        string $background,
        string $headers,
        bool $exportManual = false,
        array $hideOptions = ['total' => false, 'values' => false, 'graph' => false]
    ): string {
        if ($fieldStats === []) {
            return '<span class="cbstats-chart-empty">'
                . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_NO_DATA'), ENT_QUOTES, 'UTF-8')
                . '</span>';
        }

        if ($output === 'radar' && count($fieldStats) < self::RADAR_MIN_AXES) {
            return '<span class="cbstats-chart-message">'
                . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_RADAR_TOO_FEW'), ENT_QUOTES, 'UTF-8')
                . '</span>';
        }

        if ($output === 'radar' && count($fieldStats) > self::RADAR_MAX_AXES) {
            return '<span class="cbstats-chart-message">'
                . htmlspecialchars(
                    Text::sprintf('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_RADAR_TOO_MANY', self::RADAR_MAX_AXES),
                    ENT_QUOTES,
                    'UTF-8'
                )
                . '</span>';
        }

        $field = (array) ($payload['field'] ?? []);
        $fieldLabel = (string) ($field['label'] ?? $field['requested'] ?? Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_VALUE'));
        $locale = Factory::getApplication()->getLanguage()->getTag();
        $items = PiePresentationService::prepare($fieldStats, $locale, $total)['items'];
        $html = '<section class="cbstats-chart cbstats-chart-' . $output . ' cbstats-card"';

        if (!$hideOptions['graph']) {
            $this->loadChartAssets();
            $instanceId = 'cbstats-chart-' . ++self::$chartInstance;
            $json = json_encode(
                ['type' => $output, 'items' => $items, 'showValues' => !$hideOptions['values']],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            );
            $encodedPayload = htmlspecialchars($json === false ? '{"type":"","items":[]}' : $json, ENT_QUOTES, 'UTF-8');
            $ariaLabel = Text::sprintf(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_CHART_ARIA_LABEL',
                $output,
                $fieldLabel
            );
            $html .= ' data-cbstats-chart="' . $encodedPayload . '"'
                . $this->renderBackgroundStyle($background) . '>'
                . '<div class="cbstats-chart-viewport"><div class="cbstats-chart-canvas-wrapper"'
                . ' style="--cbstats-chart-items:' . count($items) . '">'
                . '<canvas id="' . $instanceId . '" class="cbstats-chart-canvas" role="img" aria-label="'
                . htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_CHART_UNAVAILABLE'), ENT_QUOTES, 'UTF-8')
                . '</canvas></div></div>';
        } else {
            $html .= $this->renderBackgroundStyle($background) . '>';
        }

        return $html . $this->renderChartDetails(
            $items,
            $total,
            $title,
            $output,
            $background,
            $headers,
            $exportManual,
            $hideOptions
        );
    }

    /**
     * @param list<array{label: string, value: int|float, percentage: float, percentageLabel: string, color: string}> $items
     */
    private function renderChartDetails(
        array $items,
        int|float $total,
        string $title,
        string $output,
        string $background,
        string $headers,
        bool $exportManual,
        array $hideOptions
    ): string
    {
        $html = '<div class="cbstats-pie-legend" role="list">';

        foreach ($items as $item) {
            $html .= '<div class="cbstats-pie-legend-row" role="listitem">'
                . '<span class="cbstats-pie-swatch" style="--cbstats-color:'
                . htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></span>'
                . '<span class="cbstats-pie-label">' . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . '</span>'
                . ($hideOptions['values'] ? '' : '<span class="cbstats-pie-value"><span aria-hidden="true">&mdash; </span>'
                    . $this->formatNumber($item['value']) . ' ('
                    . htmlspecialchars($item['percentageLabel'], ENT_QUOTES, 'UTF-8') . '&nbsp;%)</span>')
                . '</div>';
        }

        $html .= '</div>';

        if (!$hideOptions['total']) {
            $html .= '<div class="cbstats-total-box"><span class="cbstats-total-label">'
                . $this->renderTotalLabel($title) . '</span> <strong class="cbstats-total-value">'
                . $this->formatNumber($total) . '</strong></div>';
        }

        if ($exportManual) {
            $finalDisplayedItems = array_map(
                static fn(array $item): array => ['label' => $item['label'], 'value' => $item['value']],
                $items
            );
            $html .= $this->renderManualExport(
                $finalDisplayedItems,
                $output,
                $title,
                $background,
                $headers,
                $hideOptions
            );
        }

        return $html . '</section>';
    }

    private function formatNumber(int|float $value): string
    {
        return floor((float) $value) === (float) $value
            ? (string) (int) $value
            : rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');
    }

    private function renderManualStats(
        string $values,
        string $output,
        string $sort,
        string $dir,
        string $add,
        string $titles,
        string $headers,
        array $headerMappings,
        string $title,
        string $background,
        bool $exportManual,
        ?int $limit,
        array $hideOptions
    ): string {
        if (!in_array($output, ['total', 'table', 'pie', 'bar', 'histogram', 'line', 'radar'], true)) {
            throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_MANUAL_OUTPUT'), 400);
        }

        StatsHideOptionsService::validateForOutput($hideOptions, $output);

        $sort = $sort === 'label' ? 'title' : $sort;

        if (!in_array($sort, ['none', 'title', 'value'], true)) {
            throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_SORT'), 400);
        }

        if (!in_array($dir, ['asc', 'desc'], true)) {
            throw new \RuntimeException(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_DIR'), 400);
        }

        $defaultValueHeader = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_VALUE');
        $manualFieldKey = TableHeaderService::resolveManualFieldKey(
            $headerMappings,
            $defaultValueHeader,
            Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_TOTAL')
        );
        $payload = [
            'records' => ['total' => 0],
            'field' => [
                'requested' => $manualFieldKey,
                'label' => $manualFieldKey,
                'values' => ManualValuesParser::parse($values),
            ],
        ];
        $locale = Factory::getApplication()->getLanguage()->getTag();
        $fullFieldStats = $this->getFieldStats($payload, $sort, $dir, $locale, $add, $titles);
        $fieldStats = DisplayOptionsService::applyLimit($fullFieldStats, $limit);
        $total = array_sum(array_column($fieldStats, 'value'));
        $payload['records']['total'] = $total;

        return match ($output) {
            'table' => $this->renderTable(
                $payload,
                $fieldStats,
                $total,
                $title,
                $background,
                $headerMappings,
                $headers,
                $exportManual,
                $hideOptions
            ),
            'pie' => $this->renderPie($payload, $fieldStats, $total, $title, $background, $headers, $exportManual, $hideOptions),
            'bar' => $this->renderBar($payload, $fieldStats, $total, $title, $background, $headers, $exportManual, $hideOptions),
            'histogram', 'line', 'radar' => $this->renderGenericChart(
                $payload,
                $fieldStats,
                $total,
                $output,
                $title,
                $background,
                $headers,
                $exportManual,
                $hideOptions
            ),
            'total' => $this->formatNumber($total),
        };
    }

    private function renderTotalLabel(string $title): string
    {
        return htmlspecialchars(TotalPresentationService::formatLabel(
            $title,
            Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_TOTAL'),
            Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_TOTAL_SEPARATOR')
        ), ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param list<array{label: string, value: int|float}> $fieldStats
     */
    private function renderManualExport(
        array $fieldStats,
        string $output,
        string $title,
        string $background,
        string $headers,
        array $hideOptions = ['total' => false, 'values' => false, 'graph' => false]
    ): string
    {
        $this->loadManualExportAssets();
        $total = array_sum(array_column($fieldStats, 'value'));
        $labels = implode(' ; ', array_column($fieldStats, 'label'));
        $values = implode(' ; ', array_map(
            fn(array $item): string => $this->formatNumber($item['value']),
            $fieldStats
        ));
        $displayTitle = TotalPresentationService::formatLabel(
            $title,
            Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_TOTAL'),
            Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_TOTAL_SEPARATOR')
        );
        $syntax = ManualExportService::buildSyntax(
            $fieldStats,
            $output,
            $title,
            $background,
            $headers,
            StatsHideOptionsService::serialize($hideOptions)
        );
        $escapedSyntax = htmlspecialchars($syntax, ENT_QUOTES, 'UTF-8');

        return '<aside class="cbstats-manual-export">'
            . '<h3 class="cbstats-manual-export-title">'
            . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_TITLE'), ENT_QUOTES, 'UTF-8')
            . '</h3><dl class="cbstats-manual-export-summary">'
            . $this->renderManualExportSummary('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_SUMMARY_TITLE', $displayTitle)
            . $this->renderManualExportSummary('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_SUMMARY_TITLES', $labels)
            . ($hideOptions['values'] ? '' : $this->renderManualExportSummary(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_SUMMARY_VALUES',
                $values
            ))
            . ($hideOptions['total'] ? '' : $this->renderManualExportSummary(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_SUMMARY_TOTAL',
                $this->formatNumber($total)
            ))
            . '</dl><label class="cbstats-manual-export-label">'
            . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_SYNTAX_LABEL'), ENT_QUOTES, 'UTF-8')
            . '<textarea readonly rows="3" class="cbstats-manual-export-syntax">' . $escapedSyntax . '</textarea></label>'
            . '<div class="cbstats-manual-export-actions"><button type="button" class="cbstats-manual-copy">'
            . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_COPY'), ENT_QUOTES, 'UTF-8')
            . '</button></div><p class="cbstats-manual-copy-status" aria-live="polite" data-success="'
            . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_COPIED'), ENT_QUOTES, 'UTF-8')
            . '" data-failure="'
            . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPORT_COPY_FAILED'), ENT_QUOTES, 'UTF-8')
            . '"></p></aside>';
    }

    private function renderManualExportSummary(string $languageKey, string $value): string
    {
        return '<div><dt>' . htmlspecialchars(Text::_($languageKey), ENT_QUOTES, 'UTF-8') . '</dt><dd>'
            . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</dd></div>';
    }

    private function renderBackgroundStyle(string $background): string
    {
        return $background === '' ? '' : ' style="--cbstats-background:' . $background . '"';
    }

    private function loadPieAssets(): void
    {
        if (self::$pieAssetsLoaded) {
            return;
        }

        $wa = $this->getCbstatsWebAssetManager();
        $wa->usePreset('plg_content_contentbuilderng_cbstats.pie');
        self::$pieAssetsLoaded = true;
    }

    private function loadBarAssets(): void
    {
        if (self::$barAssetsLoaded) {
            return;
        }

        $wa = $this->getCbstatsWebAssetManager();
        $wa->usePreset('plg_content_contentbuilderng_cbstats.bar');
        self::$barAssetsLoaded = true;
    }

    private function loadChartAssets(): void
    {
        if (self::$chartAssetsLoaded) {
            return;
        }

        $this->getCbstatsWebAssetManager()->usePreset('plg_content_contentbuilderng_cbstats.charts');
        self::$chartAssetsLoaded = true;
    }

    private function loadDataTableAssets(): void
    {
        $this->getCbstatsWebAssetManager()->useStyle('plg_content_contentbuilderng_cbstats.data');
    }

    private function loadManualExportAssets(): void
    {
        if (self::$manualExportAssetsLoaded) {
            return;
        }

        $wa = $this->getCbstatsWebAssetManager();
        $wa->useStyle('plg_content_contentbuilderng_cbstats.manual-export');
        $wa->useScript('plg_content_contentbuilderng_cbstats.manual-export');
        self::$manualExportAssetsLoaded = true;
    }

    private function getCbstatsWebAssetManager(): WebAssetManager
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        if (!self::$chartAssetRegistryLoaded) {
            $wa->getRegistry()->addRegistryFile('media/plg_content_contentbuilderng_cbstats/joomla.asset.json');
            self::$chartAssetRegistryLoaded = true;
        }

        return $wa;
    }

}
