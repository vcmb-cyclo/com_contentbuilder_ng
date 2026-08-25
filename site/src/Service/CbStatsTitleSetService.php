<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

final class CbStatsTitleSetService
{
    public const CUSTOM_DIRECTORY = 'media/contentbuilderng/cbstats/titlesets';
    public const PROVIDED_DIRECTORY = 'media/com_contentbuilderng/cbstats/titlesets';

    /**
     * @var array<string, array{
     *     titles: array<string, string>, metadata: array<string, string>,
     *     comments: string, status: string, source: string, invalidEntries: int
     * }>
     */
    private array $cache = [];

    public function __construct(private readonly string $siteRoot)
    {
    }

    /**
     * @return array{
     *     titles: array<string, string>, metadata: array<string, string>,
     *     comments: string, status: string, source: string, invalidEntries: int
     * }
     */
    public function resolve(string $filename): array
    {
        $filename = trim($filename);

        if (isset($this->cache[$filename])) {
            return $this->cache[$filename];
        }

        if (!self::isValidFilename($filename)) {
            return $this->cache[$filename] = self::emptyResult('invalid_name');
        }

        $directories = [
            'custom' => self::CUSTOM_DIRECTORY,
            'provided' => self::PROVIDED_DIRECTORY,
        ];
        foreach ($directories as $source => $directory) {
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

    public static function isValidFilename(string $filename): bool
    {
        return preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]*\.ini\z/i', $filename) === 1;
    }

    public static function isValidMappingKey(string $value): bool
    {
        $value = trim($value);

        return $value !== ''
            && !str_contains($value, '=')
            && !str_contains($value, "\0")
            && !str_contains($value, "\r")
            && !str_contains($value, "\n")
            && !in_array($value[0], [';', '#'], true);
    }

    /**
     * @param array<string, string> $titleSetMappings
     * @param array<string, string> $inlineMappings
     * @return array<string, string>
     */
    public static function merge(array $titleSetMappings, array $inlineMappings): array
    {
        return array_replace($titleSetMappings, $inlineMappings);
    }

    /**
     * @return array{
     *     titles: array<string, string>, metadata: array<string, string>,
     *     comments: string, status: string, source: string, invalidEntries: int
     * }
     */
    public static function parseFile(string $path): array
    {
        if (!is_readable($path)) {
            return self::emptyResult('unreadable');
        }

        $contents = file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return self::emptyResult('empty');
        }

        $parsed = self::parseContents($contents);
        if ($parsed === null) {
            return self::emptyResult('invalid_syntax');
        }

        $metadata = $parsed['metadata'];
        if (!$parsed['hasTitles']) {
            $result = self::emptyResult('missing_titles');
            $result['metadata'] = $metadata;
            $result['comments'] = self::extractComments($contents);

            return $result;
        }

        $titles = $parsed['titles'];
        $invalidEntries = $parsed['invalidEntries'];

        if ($titles === []) {
            $status = 'missing_titles';
        } elseif ($invalidEntries > 0) {
            $status = 'invalid_entries';
        } else {
            $status = 'ok';
        }

        return [
            'titles' => $titles,
            'metadata' => $metadata,
            'comments' => self::extractComments($contents),
            'status' => $status,
            'source' => '',
            'invalidEntries' => $invalidEntries,
        ];
    }

    /**
     * @return array{metadata: array<string, string>, titles: array<string, string>, hasTitles: bool, invalidEntries: int}|null
     */
    private static function parseContents(string $contents): ?array
    {
        $metadataLines = [];
        $titles = [];
        $invalidEntries = 0;
        $section = '';
        $hasTitles = false;

        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            $trimmed = trim($line);
            if (preg_match('/^\[([^\]]+)]\s*$/', $trimmed, $match) === 1) {
                $section = strtolower(trim($match[1]));
                $hasTitles = $hasTitles || $section === 'titles';
                if ($section !== 'titles') {
                    $metadataLines[] = $line;
                }
                continue;
            }

            if ($section !== 'titles') {
                $metadataLines[] = $line;
                continue;
            }

            if ($trimmed === '' || in_array($trimmed[0], [';', '#'], true)) {
                continue;
            }

            $separator = strpos($line, '=');
            if ($separator === false) {
                $invalidEntries++;
                continue;
            }

            $key = trim(substr($line, 0, $separator));
            $rawLabel = trim(substr($line, $separator + 1));
            $label = self::parseIniValue($rawLabel);
            if (!self::isValidMappingKey($key) || $label === null || trim($label) === '') {
                $invalidEntries++;
                continue;
            }

            $titles[$key] = trim($label);
        }

        $metadataContents = trim(implode("\n", $metadataLines));
        $metadata = [];
        if ($metadataContents !== '') {
            $parseWarning = false;
            set_error_handler(static function () use (&$parseWarning): bool {
                $parseWarning = true;

                return true;
            });
            try {
                $parsedMetadata = parse_ini_string($metadataContents, true, INI_SCANNER_RAW);
            } finally {
                restore_error_handler();
            }
            if ($parseWarning || !is_array($parsedMetadata)) {
                return null;
            }
            $metadata = self::normalizeSection($parsedMetadata['metadata'] ?? []);
        }

        return compact('metadata', 'titles', 'hasTitles', 'invalidEntries');
    }

    private static function parseIniValue(string $value): ?string
    {
        $parseWarning = false;
        set_error_handler(static function () use (&$parseWarning): bool {
            $parseWarning = true;

            return true;
        });
        try {
            $parsed = parse_ini_string('value=' . $value, false, INI_SCANNER_RAW);
        } finally {
            restore_error_handler();
        }

        return !$parseWarning && is_array($parsed) && is_scalar($parsed['value'] ?? null)
            ? (string) $parsed['value']
            : null;
    }

    /** @return array<string, string> */
    private static function normalizeSection(mixed $section): array
    {
        if (!is_array($section)) {
            return [];
        }

        $normalized = [];
        foreach ($section as $key => $value) {
            if (is_scalar($value)) {
                $normalized[(string) $key] = trim((string) $value);
            }
        }

        return $normalized;
    }

    private static function extractComments(string $contents): string
    {
        $comments = [];
        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, ';')) {
                $comments[] = trim(substr($trimmed, 1));
            }
        }

        return trim(implode("\n", $comments));
    }

    /**
     * @return array{
     *     titles: array<string, string>, metadata: array<string, string>,
     *     comments: string, status: string, source: string, invalidEntries: int
     * }
     */
    private static function emptyResult(string $status): array
    {
        return [
            'titles' => [],
            'metadata' => [],
            'comments' => '',
            'status' => $status,
            'source' => '',
            'invalidEntries' => 0,
        ];
    }
}
