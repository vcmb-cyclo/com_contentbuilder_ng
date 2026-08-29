<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Service;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Site\Service\CbStatsTitleSetService;
use CB\Component\Contentbuilderng\Site\Service\CbStatsConfigService;

final class CbStatsTitleSetManagerService
{
    public function __construct(private readonly string $siteRoot)
    {
    }

    /** @return list<array<string, mixed>> */
    public function listFiles(): array
    {
        $items = [];
        $directories = [
            'custom' => CbStatsTitleSetService::CUSTOM_DIRECTORY,
            'provided' => CbStatsTitleSetService::PROVIDED_DIRECTORY,
            'config-custom' => CbStatsConfigService::CUSTOM_DIRECTORY,
            'config-provided' => CbStatsConfigService::PROVIDED_DIRECTORY,
        ];
        foreach ($directories as $source => $directory) {
            $path = $this->siteRoot . '/' . $directory;
            if (!is_dir($path)) {
                continue;
            }

            foreach (new \DirectoryIterator($path) as $entry) {
                if ($entry->isDot() || !$entry->isFile() || strtolower($entry->getExtension()) !== 'ini') {
                    continue;
                }

                $file = $entry->getPathname();
                $filename = basename($file);
                $isConfig = str_starts_with($source, 'config-');
                $result = $isConfig ? CbStatsConfigService::parseFile($file) : CbStatsTitleSetService::parseFile($file);
                $type = $isConfig ? 'config' : ($result['groups'] !== [] ? 'groups' : 'titles');
                $items[] = [
                    'filename' => $filename,
                    'source' => str_replace('config-', '', $source),
                    'modified' => $entry->getMTime(),
                    'metadata' => $isConfig ? ['name' => preg_replace('/\.ini\z/i', '', $filename)] : $result['metadata'],
                    'status' => $result['status'],
                    'type' => $type,
                    'count' => $isConfig ? count($result['values']) : count($result[$type]),
                ];
            }
        }

        usort($items, static fn(array $left, array $right): int => [
            $left['filename'],
            $left['source'],
        ] <=> [
            $right['filename'],
            $right['source'],
        ]);

        return $items;
    }

    /** @return array<string, mixed> */
    public function load(string $filename, string $source): array
    {
        if (!CbStatsTitleSetService::isValidFilename($filename) || !in_array($source, ['custom', 'provided'], true)) {
            throw new \InvalidArgumentException('Invalid title set identifier.');
        }

        $directories = $source === 'custom'
            ? [CbStatsTitleSetService::CUSTOM_DIRECTORY, CbStatsConfigService::CUSTOM_DIRECTORY]
            : [CbStatsTitleSetService::PROVIDED_DIRECTORY, CbStatsConfigService::PROVIDED_DIRECTORY];
        $path = '';
        foreach ($directories as $candidateDirectory) {
            $candidate = $this->siteRoot . '/' . $candidateDirectory . '/' . $filename;
            if (is_file($candidate)) {
                $path = $candidate;
                break;
            }
        }
        if (!is_file($path)) {
            throw new \RuntimeException('Title set file not found.');
        }

        $configResult = CbStatsConfigService::parseFile($path);
        if ($configResult['status'] === 'ok') {
            return [
                'filename' => $filename,
                'source' => $source,
                'comments' => '',
                'modified' => filemtime($path) ?: null,
                'name' => preg_replace('/\.ini\z/i', '', $filename) ?: $filename,
                'type' => 'config',
                'titles' => [['value' => '', 'label' => '']],
                'config' => $configResult['values'],
                'status' => 'ok',
            ];
        }
        $result = CbStatsTitleSetService::parseFile($path);
        $type = $result['groups'] !== [] ? 'groups' : 'titles';
        return [
            'filename' => $filename,
            'source' => $source,
            'comments' => $result['comments'],
            'modified' => filemtime($path) ?: null,
            'name' => (string) ($result['metadata']['name'] ?? ''),
            'description' => (string) ($result['metadata']['description'] ?? ''),
            'locale' => (string) ($result['metadata']['locale'] ?? ''),
            'version' => (string) ($result['metadata']['version'] ?? ''),
            'author' => (string) ($result['metadata']['author'] ?? ''),
            'type' => $type,
            'titles' => array_map(
                static fn(string $value, string $label): array => ['value' => $value, 'label' => $label],
                array_keys($result[$type]),
                array_values($result[$type])
            ),
            'status' => $result['status'],
        ];
    }

    /** @param array<string, mixed> $data */
    public function validate(array $data): array
    {
        $errors = [];
        $type = in_array(($data['type'] ?? ''), ['titles', 'groups', 'config'], true) ? (string) $data['type'] : 'titles';
        $filename = $this->normalizeFilename((string) ($data['filename'] ?? ''));
        if (!CbStatsTitleSetService::isValidFilename($filename)) {
            $errors[] = 'filename';
        }
        $titles = $type === 'config' ? [] : $this->normalizeTitles((array) ($data['titles'] ?? []), $type);
        $submittedRows = array_values(array_filter(
            (array) ($data['titles'] ?? []),
            static fn(mixed $row): bool => is_array($row)
                && (trim((string) ($row['value'] ?? '')) !== '' || trim((string) ($row['label'] ?? '')) !== '')
        ));
        if ($type !== 'config' && ($titles === [] || count($titles) !== count($submittedRows))) {
            $errors[] = 'titles';
        }

        $submittedConfig = array_filter(
            (array) ($data['config'] ?? []),
            static fn(mixed $value): bool => trim((string) $value) !== ''
        );
        $config = $type === 'config' ? $this->normalizeConfig($submittedConfig) : [];
        if ($type === 'config') {
            if ($submittedConfig === []) {
                $errors[] = 'config';
            } else {
                foreach (array_keys($submittedConfig) as $key) {
                    if (!array_key_exists((string) $key, $config)) {
                        $errors[] = 'config_' . strtolower((string) $key);
                    }
                }
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors, 'titles' => $titles, 'config' => $config, 'type' => $type];
    }

    /** @param array<string, mixed> $data */
    public function save(array $data): string
    {
        if (trim((string) ($data['name'] ?? '')) === '') {
            $normalizedFilename = $this->normalizeFilename((string) ($data['filename'] ?? 'titleset.ini'));
            $data['name'] = preg_replace('/\.ini\z/i', '', basename($normalizedFilename)) ?: 'titleset';
        }

        $validation = $this->validate($data);
        if (!$validation['valid']) {
            throw new \InvalidArgumentException(implode(',', $validation['errors']));
        }

        $directory = $this->siteRoot . '/' . ($validation['type'] === 'config'
            ? CbStatsConfigService::CUSTOM_DIRECTORY
            : CbStatsTitleSetService::CUSTOM_DIRECTORY);
        if (!is_dir($directory) && !@mkdir($directory, 0755, true)) {
            throw new \RuntimeException('directory_failed');
        }
        $index = $directory . '/index.html';
        if (!is_file($index)) {
            file_put_contents($index, '');
        }

        $filename = $this->normalizeFilename((string) $data['filename']);
        $target = $directory . '/' . $filename;
        $temporary = $target . '.tmp-' . bin2hex(random_bytes(6));
        $contents = $this->serialize($data, $validation['titles'], $validation['type'], $validation['config']);
        if (file_put_contents($temporary, $contents, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write the temporary title set file.');
        }

        $generatedStatus = $validation['type'] === 'config'
            ? CbStatsConfigService::parseFile($temporary)['status']
            : CbStatsTitleSetService::parseFile($temporary)['status'];
        if ($generatedStatus !== 'ok') {
            @unlink($temporary);
            throw new \RuntimeException('The generated title set file is invalid.');
        }

        if (!rename($temporary, $target)) {
            @unlink($temporary);
            throw new \RuntimeException('Unable to replace the title set file.');
        }

        return $filename;
    }

    /** @param array<string, mixed> $data */
    public function saveCopy(array $data): string
    {
        $data['filename'] = $this->suggestCopyFilename(
            (string) ($data['filename'] ?? 'titleset.ini'),
            (string) ($data['type'] ?? 'titles')
        );

        return $this->save($data);
    }

    public function suggestCopyFilename(string $filename, string $type = 'titles'): string
    {
        $filename = $this->normalizeFilename($filename);
        $base = preg_replace('/\.ini\z/i', '', basename($filename)) ?: 'titleset';
        $directory = $this->siteRoot . '/' . ($type === 'config'
            ? CbStatsConfigService::CUSTOM_DIRECTORY
            : CbStatsTitleSetService::CUSTOM_DIRECTORY);
        $candidate = $base . '-copy.ini';
        $number = 2;

        while (is_file($directory . '/' . $candidate)) {
            $candidate = $base . '-copy-' . $number++ . '.ini';
        }

        return $candidate;
    }

    public function validateImportFile(string $path, string $originalName, bool $overwrite = false): string
    {
        $filename = basename(trim($originalName));
        if (!CbStatsTitleSetService::isValidFilename($filename) || !is_file($path)) {
            throw new \InvalidArgumentException('invalid_filename');
        }

        $titleResult = CbStatsTitleSetService::parseFile($path);
        $configResult = CbStatsConfigService::parseFile($path);
        if (($titleResult['status'] ?? '') !== 'ok' && ($configResult['status'] ?? '') !== 'ok') {
            throw new \InvalidArgumentException('invalid_contents');
        }

        $directory = $configResult['status'] === 'ok'
            ? CbStatsConfigService::CUSTOM_DIRECTORY
            : CbStatsTitleSetService::CUSTOM_DIRECTORY;
        $target = $this->siteRoot . '/' . $directory . '/' . $filename;
        if (!$overwrite && is_file($target)) {
            throw new \RuntimeException('already_exists');
        }

        return $filename;
    }

    public function importFile(string $path, string $originalName, bool $overwrite = false): string
    {
        $filename = $this->validateImportFile($path, $originalName, $overwrite);
        $isConfig = CbStatsConfigService::parseFile($path)['status'] === 'ok';
        $directory = $this->siteRoot . '/' . ($isConfig
            ? CbStatsConfigService::CUSTOM_DIRECTORY
            : CbStatsTitleSetService::CUSTOM_DIRECTORY);
        if (!is_dir($directory) && !@mkdir($directory, 0755, true)) {
            throw new \RuntimeException('directory_failed');
        }

        $contents = @file_get_contents($path);
        if (!is_string($contents)) {
            throw new \RuntimeException('read_failed');
        }

        $target = $directory . '/' . $filename;
        $temporary = $target . '.tmp-' . bin2hex(random_bytes(6));
        if (@file_put_contents($temporary, $contents, LOCK_EX) === false) {
            @unlink($temporary);
            throw new \RuntimeException('write_failed');
        }

        $backup = null;
        if ($overwrite && is_file($target)) {
            $backup = $target . '.backup-' . bin2hex(random_bytes(6));
            if (!@rename($target, $backup)) {
                @unlink($temporary);
                throw new \RuntimeException('replace_failed');
            }
        }
        if (!@rename($temporary, $target)) {
            @unlink($temporary);
            if ($backup !== null) {
                @rename($backup, $target);
            }
            throw new \RuntimeException($overwrite ? 'replace_failed' : 'write_failed');
        }
        if ($backup !== null) {
            @unlink($backup);
        }

        return $filename;
    }

    public function getFileContents(string $filename, string $source): string
    {
        if (!CbStatsTitleSetService::isValidFilename($filename) || !in_array($source, ['custom', 'provided'], true)) {
            throw new \InvalidArgumentException('Invalid title set identifier.');
        }

        $directories = $source === 'custom'
            ? [CbStatsTitleSetService::CUSTOM_DIRECTORY, CbStatsConfigService::CUSTOM_DIRECTORY]
            : [CbStatsTitleSetService::PROVIDED_DIRECTORY, CbStatsConfigService::PROVIDED_DIRECTORY];
        $contents = false;
        foreach ($directories as $directory) {
            $path = $this->siteRoot . '/' . $directory . '/' . $filename;
            if (is_file($path)) {
                $contents = file_get_contents($path);
                break;
            }
        }
        if (!is_string($contents)) {
            throw new \RuntimeException('Unable to read the title set file.');
        }

        return $contents;
    }

    private function normalizeFilename(string $filename): string
    {
        $filename = trim($filename);

        return $filename !== '' && !str_ends_with(strtolower($filename), '.ini')
            ? $filename . '.ini'
            : $filename;
    }

    public function delete(string $filename): void
    {
        if (!CbStatsTitleSetService::isValidFilename($filename)) {
            throw new \InvalidArgumentException('Invalid title set filename.');
        }

        foreach ([CbStatsTitleSetService::CUSTOM_DIRECTORY, CbStatsConfigService::CUSTOM_DIRECTORY] as $directory) {
            $path = $this->siteRoot . '/' . $directory . '/' . $filename;
            if (is_file($path) && !unlink($path)) {
                throw new \RuntimeException('Unable to delete the custom data set.');
            }
        }
    }

    /** @return array<string, string> */
    private function normalizeTitles(array $rows, string $type): array
    {
        $titles = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $value = trim((string) ($row['value'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));
            $validValue = CbStatsTitleSetService::isValidMappingKey($value);
            if ($validValue && $type === 'groups') {
                try {
                    $validValue = count(\CB\Component\Contentbuilderng\Site\Service\StatsService::parseFieldStatsGroupSelectors($value)) === 1;
                } catch (\InvalidArgumentException) {
                    $validValue = false;
                }
            }
            if (
                $validValue
                && $label !== ''
                && !str_contains($label, "\n")
                && !str_contains($label, "\r")
            ) {
                $titles[$value] = $label;
            }
        }

        return $titles;
    }

    /** @return array<string, string> */
    private function normalizeConfig(array $values): array
    {
        $allowed = ['title', 'category', 'value', 'total', 'background', 'card', 'w', 'width', 'height', 'hide', 'sort', 'dir', 'limit'];
        $config = [];
        foreach ($allowed as $key) {
            $value = trim((string) ($values[$key] ?? ''));
            if ($value !== '' && $this->isValidConfigValue($key, $value)) {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    private function isValidConfigValue(string $key, string $value): bool
    {
        if (str_contains($value, "\r") || str_contains($value, "\n") || str_contains($value, "\0")) {
            return false;
        }
        if (in_array($key, ['title', 'category', 'value', 'total'], true)) {
            return !str_contains($value, ';');
        }
        if ($key === 'sort') {
            return in_array(strtolower($value), ['none', 'title', 'value'], true);
        }
        if ($key === 'dir') {
            return in_array(strtolower($value), ['asc', 'desc'], true);
        }
        if ($key === 'limit') {
            return preg_match('/^[1-9][0-9]*$/D', $value) === 1
                && filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
        }
        if (in_array($key, ['width', 'height'], true)) {
            if (preg_match('/^([1-9][0-9]*)(px|%)?$/Di', $value, $match) !== 1) {
                return false;
            }
            $number = (int) $match[1];
            $unit = strtolower($match[2] ?? 'px');
            return ($unit === '%' && $number <= 100) || ($unit === 'px' && $number <= 5000);
        }
        if ($key === 'card') {
            return \CB\Component\Contentbuilderng\Site\Service\ContentCardService::isValid($value);
        }
        if ($key === 'w') {
            return \CB\Component\Contentbuilderng\Site\Service\ContentCardService::isValidWidth($value);
        }
        if ($key === 'background') {
            if ($value === 'transparent' || preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?(?:[0-9a-f]{2})?$/i', $value) === 1) {
                return true;
            }
            if (preg_match('/^rgb(a)?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*(0(?:\.\d+)?|1(?:\.0+)?))?\s*\)$/i', $value, $matches) === 1) {
                return max((int) $matches[2], (int) $matches[3], (int) $matches[4]) <= 255
                    && (($matches[1] === '') === !isset($matches[5]));
            }

            return in_array(strtolower($value), [
                'aliceblue', 'black', 'blue', 'currentcolor', 'gray', 'green', 'grey', 'red', 'white', 'yellow',
            ], true);
        }
        if ($key === 'hide') {
            try {
                \CB\Component\Contentbuilderng\Site\Service\StatsHideOptionsService::fromAttributes(['hide' => $value]);
                return true;
            } catch (\InvalidArgumentException) {
                return false;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $data @param array<string, string> $titles @param array<string, string> $config */
    private function serialize(array $data, array $titles, string $type, array $config = []): string
    {
        $lines = [];
        foreach (preg_split('/\R/', trim((string) ($data['comments'] ?? ''))) ?: [] as $comment) {
            if (trim($comment) !== '') {
                $lines[] = '; ' . trim($comment);
            }
        }

        if ($type === 'config') {
            foreach ([
                'labels' => ['title', 'category', 'value', 'total'],
                'presentation' => ['background', 'card', 'w', 'width', 'height'],
                'display' => ['hide', 'sort', 'dir', 'limit'],
            ] as $section => $keys) {
                $sectionValues = array_intersect_key($config, array_flip($keys));
                if ($sectionValues === []) {
                    continue;
                }
                $lines[] = '';
                $lines[] = '[' . $section . ']';
                foreach ($sectionValues as $key => $value) {
                    $lines[] = $key . '=' . $this->quoteIni($value);
                }
            }
        } else {
            $lines[] = '';
            $lines[] = '[metadata]';
            $lines[] = 'name=' . $this->quoteIni((string) ($data['name'] ?? ''));
            $lines[] = '';
            $lines[] = '[' . $type . ']';
            foreach ($titles as $value => $label) {
                $lines[] = $value . '=' . $this->quoteIni($label);
            }
        }

        return implode("\n", $lines) . "\n";
    }

    private function quoteIni(string $value): string
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }
}
