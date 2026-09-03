<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Templates;

use PHPUnit\Framework\TestCase;

/**
 * Regression guard for REFACTORING_PLAN.md chapter B (échappement des
 * sorties front).
 *
 * This does not classify output as safe or unsafe — a purely static regex
 * cannot tell a ternary's literal branches from a raw database value, or
 * know that $this->event->afterDisplayContent is Joomla's own trusted
 * content-prepare pipeline. What it does is pin the count of "echo $var /
 * <?= $var" occurrences with no escaping call anywhere on the line, per
 * file, to the count recorded after the chapter B audit (2026-08-01). Any
 * increase fails the test — same anti-regression discipline as
 * phpstan-baseline.neon: the baseline only shrinks as files are audited and
 * fixed, it must never grow silently.
 *
 * A failure here means either:
 * - a new `echo $var` was added without escaping — fix it, or
 * - a genuinely safe pattern was added (an int, a ternary of literals, a
 *   call to a helper that already escapes internally) — update BASELINE
 *   below with a comment explaining why it's safe, matching the audit
 *   trail this file's sibling entries already carry in REFACTORING_PLAN.md.
 */
final class UnescapedTemplateOutputTest extends TestCase
{
    private const DIRECTORIES = ['admin/tmpl', 'admin/layouts', 'site/tmpl', 'site/layouts'];

    /**
     * One `echo`/`<?=` on a line containing none of these is flagged. Kept
     * in sync with the set of escaping conventions actually used across the
     * front templates: $this->escape(), htmlspecialchars(), Text::_()/
     * sprintf(), HTMLHelper::_(), json_encode(), $this->loadTemplate(),
     * LayoutHelper::render(), a ->render() call, Route::_() (URLs, not raw
     * output), and this component's own HTML allow-list sanitiser for
     * admin-authored rich text fields (intro_text and friends).
     */
    private const SAFE_CALL_PATTERN = '/escape\(|htmlspecialchars|Text::|HTMLHelper|json_encode|loadTemplate|'
        . 'LayoutHelper|->render|renderFieldset|->getInput|renderInput|renderDirectionButtonGroup|Route::|sanitizeStoredHtml|'
        . 'escapeListValue|listLimitField->input|detailInclude|editable|unavailable|inactive|'
        . 'isDetailEnabled|isEditEnabled/';

    private const UNESCAPED_ECHO_PATTERN = '/(?:<\?=\s*|echo\s+)\$[A-Za-z_]/';

    /**
     * Recorded 2026-08-01 after the chapter B escaping pass. Every entry is
     * either a bare int/bool property, a ternary whose only literal
     * branches this regex mistakes for the compared variable, a closure
     * result that already escapes internally (verified once at its
     * definition site, not at each call site), or Joomla's own
     * content-prepare / pagination / form-field output, which is already
     * safe HTML by framework convention and would break if escaped again.
     *
     * @var array<string, int>
     */
    private const BASELINE = [
        // +7 trusted audit helpers, translated labels and integer orphan identifiers.
        'admin/tmpl/about/audit_report.php' => 164,
        'admin/tmpl/about/installed_plugins.php' => 3,
        'admin/tmpl/about/version_summary.php' => 1,
        'admin/tmpl/configtransfer/default.php' => 17,
        'admin/tmpl/elementoptions/default.php' => 41,
        'admin/tmpl/form/edit.php' => 7,
        'admin/tmpl/forms/default.php' => 15,
        'admin/tmpl/list/default.php' => 28,
        'admin/tmpl/list/select.php' => 26,
        'admin/tmpl/storage/default.php' => 4,
        'admin/tmpl/storages/default.php' => 13,
        'admin/tmpl/storagewizard/default.php' => 3,
        'admin/tmpl/user/default.php' => 10,
        'admin/tmpl/users/default.php' => 13,
        // renderCheckbox() returns trusted Joomla checkbox markup.
        'admin/layouts/form/advanced_options.php' => 35,
        // Permission badges are assembled from fixed keys and escaped labels.
        'admin/layouts/form/api_tab.php' => 6,
        'admin/layouts/form/article_tab.php' => 11,
        'admin/layouts/form/audit_tab.php' => 4,
        'admin/layouts/form/bf_system_fields_modal.php' => 3,
        'admin/layouts/form/debug_tab.php' => 3,
        'admin/layouts/form/details_display.php' => 3,
        'admin/layouts/form/edit_display.php' => 5,
        'admin/layouts/form/elements_table.php' => 29,
        'admin/layouts/form/email_tab.php' => 2,
        'admin/layouts/form/list_states.php' => 11,
        'admin/layouts/form/permissions_tab.php' => 35,
        'admin/layouts/form/prepare_editor.php' => 10,
        'admin/layouts/form/view_tab.php' => 3,
        'admin/layouts/storage/information_tab.php' => 2,
        // Data tab: three pager ternaries whose only branches are the literal
        // strings 'disabled'/'active'/'' (the regex mistakes the compared int
        // $current/$p for output), plus the $sortHeader() closure which
        // htmlspecialchars() every part it emits. Real values are int-cast or
        // escaped.
        'admin/layouts/storage/data_tab.php' => 4,
        // +3 on 2026-08-01 for the required-field toggle button: a bare
        // int $id (existing pattern throughout this file), a ternary of
        // only literal '1'/'0' branches, and a fixed PHP-side CSS class
        // string ($requiredIconClass, never user-controlled).
        // The system-field data attribute is a fixed literal selected by a
        // boolean flag; escaping the complete attribute would encode its
        // delimiters and change the rendered HTML.
        'admin/layouts/storage/storage_tab.php' => 32,
        'site/tmpl/details/default.php' => 16,
        'site/tmpl/details/print.php' => 8,
        'site/tmpl/edit/default.php' => 76,
        // Extension-authored translated help markup returned by Text::_();
        // escaping it here would expose the intended HTML tags as text.
        'site/tmpl/cblisthelp/default.php' => 1,
        'site/tmpl/cbstatshelp/default.php' => 1,
        'site/tmpl/list/default.php' => 46,
        'site/tmpl/publicforms/default.php' => 20,
        'site/layouts/contentbuilderng/action_toolbar.php' => 2,
        // +1 on 2026-08-01 for the cbListActions block's container/aria id
        // line: $debugIdBase is 'cb-debug-form-' . max(0, $formId) plus an
        // int record id, never raw user input, same safe pattern as every
        // other $debugIdBase id/aria-labelledby pair already in this file.
        'site/layouts/contentbuilderng/debug_panel.php' => 19,
        'site/layouts/contentbuilderng/list_pagination.php' => 6,
        'site/layouts/contentbuilderng/preview_color_mode.php' => 2,
    ];

    private string $root;

    protected function setUp(): void
    {
        $this->root = \dirname(__DIR__, 4);
    }

    public function testUnescapedEchoCountsDoNotExceedBaseline(): void
    {
        $regressions = [];
        $seen = [];

        foreach ($this->templateFiles() as $relativePath) {
            $actual = $this->countUnescapedEchoes($this->read($relativePath));
            $seen[$relativePath] = $actual;
            $allowed = self::BASELINE[$relativePath] ?? 0;

            if ($actual > $allowed) {
                $regressions[] = \sprintf(
                    '%s: %d unescaped echo(es), baseline allows %d',
                    $relativePath,
                    $actual,
                    $allowed
                );
            }
        }

        self::assertSame(
            [],
            $regressions,
            "New unescaped template output found beyond the recorded baseline:\n"
            . implode("\n", $regressions)
            . "\n\nEscape it, or if it's genuinely safe, update "
            . self::class . '::BASELINE with a one-line reason.'
        );

        // A file dropping out of BASELINE entirely (fully cleaned up) should
        // not silently widen the baseline back if a future regression only
        // partially reintroduces occurrences below the stale recorded count.
        foreach (self::BASELINE as $relativePath => $allowed) {
            self::assertArrayHasKey(
                $relativePath,
                $seen,
                $relativePath . ' is in the baseline but no longer exists or was moved out of the scanned directories — remove its entry.'
            );
        }
    }

    private function countUnescapedEchoes(string $contents): int
    {
        $lines = preg_split('/\R/', $contents) ?: [];
        $count = 0;

        foreach ($lines as $line) {
            if (preg_match(self::UNESCAPED_ECHO_PATTERN, $line) && !preg_match(self::SAFE_CALL_PATTERN, $line)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return list<string>
     */
    private function templateFiles(): array
    {
        $files = [];

        foreach (self::DIRECTORIES as $directory) {
            $files = array_merge(
                $files,
                glob($this->root . '/' . $directory . '/*.php') ?: [],
                glob($this->root . '/' . $directory . '/*/*.php') ?: [],
                glob($this->root . '/' . $directory . '/*/*/*.php') ?: []
            );
        }

        $files = array_values(array_unique($files));
        sort($files);

        self::assertNotEmpty($files, 'No template files were discovered');

        return array_map(
            fn(string $path): string => substr($path, strlen($this->root) + 1),
            $files
        );
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents($this->root . '/' . $relativePath);
        self::assertIsString($contents, 'Unable to read ' . $relativePath);

        return $contents;
    }
}
