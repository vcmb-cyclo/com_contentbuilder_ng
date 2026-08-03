<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Site;

use PHPUnit\Framework\TestCase;

/**
 * Regression guard for a production defect: paging an embedded CBList reset
 * it to the view's own ordering, which reads as the sort inverting when that
 * ordering runs the other way.
 *
 * Nothing here can be caught by a type checker or a linter. The bug was a
 * guard written with isset(), which is true for the empty string that the
 * pagination links serialised — so the model believed the visitor had chosen
 * an ordering and discarded the {CBList sort="..."} one. These are structural
 * assertions on the two files that have to agree with each other, in the same
 * spirit as DebugPermissionHelperCallSitesTest.
 */
final class EmbeddedListSortPaginationTest extends TestCase
{
    private const MODEL = 'site/src/Model/ListModel.php';
    private const PAGINATION_LAYOUT = 'site/layouts/contentbuilderng/list_pagination.php';

    public function testEmbeddedSortGuardTestsEmptinessRatherThanPresence(): void
    {
        $source = $this->read(self::MODEL);

        self::assertStringContainsString(
            "\$requestedOrdering === '' && \$requestedFullordering === ''",
            $source,
            'The embedded sort must only stand down for a NON-EMPTY requested ordering. '
            . 'isset() is true for the empty string the pagination links carry, which silently '
            . 'discarded the {CBList sort="..."} order on every page change.'
        );
        self::assertStringNotContainsString(
            "!isset(\$list['ordering']) && !isset(\$list['fullordering'])",
            $source,
            'The isset()-based guard is the regression itself — see this test\'s docblock.'
        );
    }

    public function testPaginationLinksOmitTheOrderingWhileNoSortIsActive(): void
    {
        $source = $this->read(self::PAGINATION_LAYOUT);

        self::assertStringContainsString(
            "if (\$listOrdering !== '') {",
            $source,
            'Pagination links must not serialise list[ordering] while no sort is active: '
            . 'http_build_query() keeps empty strings, and an empty value reads downstream '
            . 'as a deliberate ordering.'
        );
        self::assertStringNotContainsString(
            "'ordering' => \$lists['order'] ?? null,",
            $source,
            'Emitting list[ordering] unconditionally is the regression itself.'
        );
    }

    public function testUnknownSortColumnIsReportedWithoutFailingThePage(): void
    {
        $source = $this->read(self::MODEL);

        $message = 'A sort= naming a column the view does not expose must be reported clearly without '
            . 'rendering the invalid embedded list.';

        $guard = strpos($source, "\$requestedOrdering === '' && \$requestedFullordering === ''");
        self::assertNotFalse($guard, $message);

        $try = strpos($source, 'try {', $guard);
        $filter = strpos($source, 'EmbeddedListFieldFilterService::matchSelectors(', $guard);
        $catch = strpos($source, 'catch (\InvalidArgumentException)', $guard);
        $report = strpos($source, '$data->embedded_list_validation_errors[]', $guard);

        self::assertNotFalse($try, $message);
        self::assertNotFalse($filter, $message);
        self::assertNotFalse($catch, $message);
        self::assertNotFalse($report, $message);
        self::assertLessThan($filter, $try, $message);
        self::assertLessThan($catch, $filter, $message);
        self::assertLessThan($report, $filter, $message);
    }

    public function testDirectionIsReadWithoutStrippingTheSeparator(): void
    {
        $source = $this->read(self::MODEL);

        self::assertStringContainsString(
            "getString('cblist_dir', 'asc')",
            $source,
            'cblist_dir carries one direction per sorted column, separated by "|".'
        );
        self::assertStringNotContainsString(
            "getCmd('cblist_dir'",
            $source,
            'getCmd() strips "|", collapsing a multi-column "asc|desc" into "ascdesc".'
        );
    }

    private function read(string $relativePath): string
    {
        $contents = \file_get_contents(\dirname(__DIR__, 4) . '/' . $relativePath);
        self::assertIsString($contents, 'Unable to read ' . $relativePath);

        return $contents;
    }
}
