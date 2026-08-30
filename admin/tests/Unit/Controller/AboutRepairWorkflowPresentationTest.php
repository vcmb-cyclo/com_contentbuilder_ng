<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

final class AboutRepairWorkflowPresentationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = \dirname(__DIR__, 4);
    }

    public function testPendingRepairsUseAWarningWhileCompletedWorkflowUsesSuccess(): void
    {
        $controller = (string) file_get_contents($this->root . '/admin/src/Controller/AboutController.php');

        self::assertStringContainsString("\$workflowCompleted ? 'message' : 'warning'", $controller);
        self::assertStringContainsString('COM_CONTENTBUILDERNG_DB_REPAIR_WORKFLOW_STARTED_NO_ACTION', $controller);
        self::assertStringContainsString('COM_CONTENTBUILDERNG_DB_REPAIR_WORKFLOW_STARTED', $controller);
    }

    public function testWorkflowMessagesRemainAlignedAcrossSupportedLanguages(): void
    {
        $expectedFragments = [
            'en-GB' => ['Repairable anomalies', 'Repair or Skip'],
            'fr-FR' => ['anomalies réparables', 'Réparer ou Ignorer'],
            'de-DE' => ['Reparierbare Anomalien', 'Reparieren oder Überspringen'],
        ];

        foreach ($expectedFragments as $language => $fragments) {
            $translations = (string) file_get_contents(
                $this->root . '/admin/language/' . $language . '/com_contentbuilderng.ini'
            );

            foreach ($fragments as $fragment) {
                self::assertStringContainsString($fragment, $translations);
            }
        }
    }
}
