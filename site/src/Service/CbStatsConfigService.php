<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

final class CbStatsConfigService
{
    public const CUSTOM_DIRECTORY = 'media/contentbuilderng/cbstats/configs';
    public const PROVIDED_DIRECTORY = 'media/com_contentbuilderng/cbstats/configs';

    private const SECTIONS = [
        'labels' => ['title', 'category', 'value', 'total'],
        'presentation' => ['background', 'card', 'w', 'width', 'height'],
        'display' => ['hide', 'sort', 'dir', 'limit'],
    ];

    /** @var array<string, array{values: array<string, string>, status: string, source: string}> */
    private array $cache = [];

    public function __construct(private readonly string $siteRoot)
    {
    }

    /** @return array{values: array<string, string>, status: string, source: string} */
    public function resolve(string $filename): array
    {
        $filename = trim($filename);
        if (isset($this->cache[$filename])) {
            return $this->cache[$filename];
        }
        if (!CbStatsTitleSetService::isValidFilename($filename)) {
            return $this->cache[$filename] = self::emptyResult('invalid_name');
        }

        foreach (['custom' => self::CUSTOM_DIRECTORY, 'provided' => self::PROVIDED_DIRECTORY] as $source => $directory) {
            $path = $this->siteRoot . '/' . $directory . '/' . $filename;
            if (!is_file($path)) {
                continue;
            }
            $result = self::parseFile($path);
            $result['source'] = $source;

            return $this->cache[$filename] = $result;
        }

        return $this->cache[$filename] = self::emptyResult('not_found');
    }

    /** @return array{values: array<string, string>, status: string, source: string} */
    public static function parseFile(string $path): array
    {
        if (!is_readable($path)) {
            return self::emptyResult('unreadable');
        }
        $contents = file_get_contents($path);
        if (!is_string($contents) || trim($contents) === '') {
            return self::emptyResult('empty');
        }

        $currentSection = '';
        $seenSections = [];
        $seenKeys = [];
        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || in_array($trimmed[0], [';', '#'], true)) {
                continue;
            }
            if (preg_match('/^\[([^]]+)]$/', $trimmed, $match) === 1) {
                $currentSection = strtolower(trim($match[1]));
                if (!isset(self::SECTIONS[$currentSection])) {
                    return self::emptyResult('unknown_section');
                }
                if (isset($seenSections[$currentSection])) {
                    return self::emptyResult('duplicate_section');
                }
                $seenSections[$currentSection] = true;
                continue;
            }
            if ($currentSection === '' || !str_contains($line, '=')) {
                return self::emptyResult('invalid_syntax');
            }
            $key = strtolower(trim(strstr($line, '=', true) ?: ''));
            if (!in_array($key, self::SECTIONS[$currentSection], true)) {
                return self::emptyResult('unknown_key');
            }
            if (isset($seenKeys[$key])) {
                return self::emptyResult('duplicate_key');
            }
            $seenKeys[$key] = true;
        }

        $warning = false;
        set_error_handler(static function () use (&$warning): bool {
            $warning = true;
            return true;
        });
        try {
            $parsed = parse_ini_string($contents, true, INI_SCANNER_RAW);
        } finally {
            restore_error_handler();
        }
        if ($warning || !is_array($parsed)) {
            return self::emptyResult('invalid_syntax');
        }

        $values = [];
        $seen = [];
        foreach ($parsed as $section => $entries) {
            $section = strtolower(trim((string) $section));
            if (!isset(self::SECTIONS[$section]) || !is_array($entries)) {
                return self::emptyResult('unknown_section');
            }
            foreach ($entries as $key => $value) {
                $key = strtolower(trim((string) $key));
                if (!in_array($key, self::SECTIONS[$section], true) || isset($seen[$key]) || !is_scalar($value)) {
                    return self::emptyResult(isset($seen[$key]) ? 'duplicate_key' : 'unknown_key');
                }
                $normalized = trim(str_replace(['\\"', '\\\\'], ['"', '\\'], (string) $value));
                if ($normalized === '' || str_contains($normalized, "\0") || str_contains($normalized, "\r") || str_contains($normalized, "\n")) {
                    return self::emptyResult('invalid_value');
                }
                $seen[$key] = true;
                $values[$key] = $normalized;
            }
        }

        return $values === []
            ? self::emptyResult('empty')
            : ['values' => $values, 'status' => 'ok', 'source' => ''];
    }

    /** @param array<string, string> $inline @param array<string, string> $configured @return array<string, string> */
    public static function merge(array $configured, array $inline): array
    {
        $configLabels = array_intersect_key($configured, array_flip(self::SECTIONS['labels']));
        foreach (self::SECTIONS['labels'] as $key) {
            unset($configured[$key]);
        }

        $inlineLabels = StatsService::parseFieldStatsLabels((string) ($inline['labels'] ?? ''));
        $labels = array_replace($configLabels, $inlineLabels);
        if ($labels !== []) {
            $configured['labels'] = implode(';', array_map(
                static fn(string $key, string $value): string => $key . '=' . $value,
                array_keys($labels),
                array_values($labels)
            ));
        }

        unset($inline['labels']);

        return array_replace($configured, $inline);
    }

    /** @return array{values: array<string, string>, status: string, source: string} */
    private static function emptyResult(string $status): array
    {
        return ['values' => [], 'status' => $status, 'source' => ''];
    }
}
