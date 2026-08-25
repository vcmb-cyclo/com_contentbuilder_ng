<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Service;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Site\Service\CbStatsTitleSetService;

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
                $result = CbStatsTitleSetService::parseFile($file);
                $items[] = [
                    'filename' => $filename,
                    'source' => $source,
                    'metadata' => $result['metadata'],
                    'status' => $result['status'],
                    'count' => count($result['titles']),
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

        $directory = $source === 'custom'
            ? CbStatsTitleSetService::CUSTOM_DIRECTORY
            : CbStatsTitleSetService::PROVIDED_DIRECTORY;
        $path = $this->siteRoot . '/' . $directory . '/' . $filename;
        if (!is_file($path)) {
            throw new \RuntimeException('Title set file not found.');
        }

        $result = CbStatsTitleSetService::parseFile($path);

        return [
            'filename' => $filename,
            'source' => $source,
            'comments' => $result['comments'],
            'name' => (string) ($result['metadata']['name'] ?? ''),
            'description' => (string) ($result['metadata']['description'] ?? ''),
            'locale' => (string) ($result['metadata']['locale'] ?? ''),
            'version' => (string) ($result['metadata']['version'] ?? ''),
            'author' => (string) ($result['metadata']['author'] ?? ''),
            'titles' => array_map(
                static fn(string $value, string $label): array => ['value' => $value, 'label' => $label],
                array_keys($result['titles']),
                array_values($result['titles'])
            ),
            'status' => $result['status'],
        ];
    }

    /** @param array<string, mixed> $data */
    public function validate(array $data): array
    {
        $errors = [];
        $filename = $this->normalizeFilename((string) ($data['filename'] ?? ''));
        if (!CbStatsTitleSetService::isValidFilename($filename)) {
            $errors[] = 'filename';
        }
        if (trim((string) ($data['name'] ?? '')) === '') {
            $errors[] = 'name';
        }

        $titles = $this->normalizeTitles((array) ($data['titles'] ?? []));
        $submittedRows = array_values(array_filter(
            (array) ($data['titles'] ?? []),
            static fn(mixed $row): bool => is_array($row)
                && (trim((string) ($row['value'] ?? '')) !== '' || trim((string) ($row['label'] ?? '')) !== '')
        ));
        if ($titles === [] || count($titles) !== count($submittedRows)) {
            $errors[] = 'titles';
        }

        return ['valid' => $errors === [], 'errors' => $errors, 'titles' => $titles];
    }

    /** @param array<string, mixed> $data */
    public function save(array $data): string
    {
        $validation = $this->validate($data);
        if (!$validation['valid']) {
            throw new \InvalidArgumentException(implode(',', $validation['errors']));
        }

        $directory = $this->siteRoot . '/' . CbStatsTitleSetService::CUSTOM_DIRECTORY;
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new \RuntimeException('Unable to create the custom title set directory.');
        }
        $index = $directory . '/index.html';
        if (!is_file($index)) {
            file_put_contents($index, '');
        }

        $filename = $this->normalizeFilename((string) $data['filename']);
        $target = $directory . '/' . $filename;
        $temporary = $target . '.tmp-' . bin2hex(random_bytes(6));
        $contents = $this->serialize($data, $validation['titles']);

        if (file_put_contents($temporary, $contents, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write the temporary title set file.');
        }

        if (CbStatsTitleSetService::parseFile($temporary)['status'] !== 'ok') {
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
        $data['filename'] = $this->suggestCopyFilename((string) ($data['filename'] ?? 'titleset.ini'));

        return $this->save($data);
    }

    public function suggestCopyFilename(string $filename): string
    {
        $filename = $this->normalizeFilename($filename);
        $base = preg_replace('/\.ini\z/i', '', basename($filename)) ?: 'titleset';
        $directory = $this->siteRoot . '/' . CbStatsTitleSetService::CUSTOM_DIRECTORY;
        $candidate = $base . '-copy.ini';
        $number = 2;

        while (is_file($directory . '/' . $candidate)) {
            $candidate = $base . '-copy-' . $number++ . '.ini';
        }

        return $candidate;
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

        $path = $this->siteRoot . '/' . CbStatsTitleSetService::CUSTOM_DIRECTORY . '/' . $filename;
        if (is_file($path) && !unlink($path)) {
            throw new \RuntimeException('Unable to delete the custom title set.');
        }
    }

    /** @return array<string, string> */
    private function normalizeTitles(array $rows): array
    {
        $titles = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $value = trim((string) ($row['value'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));
            if (
                CbStatsTitleSetService::isValidMappingKey($value)
                && $label !== ''
                && !str_contains($label, "\n")
                && !str_contains($label, "\r")
            ) {
                $titles[$value] = $label;
            }
        }

        return $titles;
    }

    /** @param array<string, mixed> $data @param array<string, string> $titles */
    private function serialize(array $data, array $titles): string
    {
        $lines = [];
        foreach (preg_split('/\R/', trim((string) ($data['comments'] ?? ''))) ?: [] as $comment) {
            if (trim($comment) !== '') {
                $lines[] = '; ' . trim($comment);
            }
        }

        $lines[] = '';
        $lines[] = '[metadata]';
        foreach (['name', 'description', 'locale', 'version', 'author'] as $key) {
            $lines[] = $key . '=' . $this->quoteIni((string) ($data[$key] ?? ''));
        }
        $lines[] = '';
        $lines[] = '[titles]';
        foreach ($titles as $value => $label) {
            $lines[] = $value . '=' . $this->quoteIni($label);
        }

        return implode("\n", $lines) . "\n";
    }

    private function quoteIni(string $value): string
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }
}
