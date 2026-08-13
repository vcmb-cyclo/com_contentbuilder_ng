<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

final class ElementMutationResyncTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testElementOptionsSaveResynchronizesLockedTemplates(): void
    {
        $source = $this->read('admin/src/Controller/ElementoptionsController.php');

        self::assertStringContainsString('resyncLockedTemplatesAfterSave', $source);
        self::assertStringContainsString('resyncLockedTemplates($formId', $source);
        self::assertStringContainsString('getData()', $source);
        self::assertStringContainsString("enqueueMessage(", $source);
    }

    public function testAjaxResyncMessagesKeepTheirJoomlaTypes(): void
    {
        $controller = $this->read('admin/src/Controller/FormController.php');
        $script = $this->read('media/js/form-edit-init.js');

        self::assertStringContainsString("'messages' => \$messages", $controller);
        self::assertStringContainsString('respondAjaxMessages(true, $messages)', $controller);
        self::assertStringContainsString('Joomla.renderMessages(messages)', $script);
        self::assertStringContainsString('item.type', $script);
    }

    public function testPublishingAnElementImmediatelyRestoresDetailAndEditControls(): void
    {
        $layout = $this->read('admin/layouts/form/elements_table.php');
        $script = $this->read('media/js/form-edit-init.js');

        self::assertStringContainsString('data-cb-published-capability="detail"', $layout);
        self::assertStringContainsString('data-cb-published-capability="edit"', $layout);
        self::assertStringContainsString('data-cb-capability-control', $layout);
        self::assertStringContainsString('data-cb-capability-lock', $layout);
        self::assertStringContainsString('data-cb-capability-off', $layout);
        self::assertStringContainsString('data-cb-capability-enabled=', $layout);
        self::assertStringContainsString('$isDetailEnabled', $layout);
        self::assertStringContainsString('$isEditEnabled', $layout);
        self::assertStringContainsString('function cbRefreshPublishedCapabilityCell', $script);
        self::assertStringContainsString("cell.dataset.cbCapabilityEnabled === '1'", $script);
        self::assertStringContainsString('lock.hidden = published || !enabled;', $script);
        self::assertStringContainsString('off.hidden = published || enabled;', $script);
        self::assertStringContainsString('function cbUpdateStoredCapabilityState', $script);
        self::assertStringContainsString("cell.dataset.cbCapabilityEnabled = meta.enabled ? '1' : '0';", $script);
        self::assertStringContainsString('function cbUpdatePublishedCapabilities', $script);
        self::assertStringContainsString('function cbApplyAjaxRowMutation', $script);
        self::assertStringContainsString('cbUpdatePublishedCapabilities(actionElement, task, rowId);', $script);
        self::assertSame(
            2,
            substr_count($script, 'cbApplyAjaxRowMutation(actionElement, task, rowId);'),
            'Both the legacy and delegated AJAX success paths must refresh Detail/Edit locks.'
        );
        self::assertStringContainsString('tr[data-cb-row-id="', $script);
        self::assertStringContainsString('[data-cb-col="publish"] .js-grid-item-action', $script);
    }

    public function testViewFieldPriorityHelpDistinguishesFieldsFromRecordAcl(): void
    {
        $layout = $this->read('admin/layouts/form/elements_table.php');

        self::assertStringContainsString('COM_CONTENTBUILDERNG_ELEMENT_COLUMNS_PRIORITY_HELP', $layout);
        self::assertStringContainsString('hide-aware-inline-help d-none', $layout);

        foreach (['en-GB', 'fr-FR', 'de-DE'] as $language) {
            $translations = parse_ini_file(
                $this->root . '/admin/language/' . $language . '/com_contentbuilderng.ini'
            );

            self::assertIsArray($translations);
            self::assertArrayHasKey('COM_CONTENTBUILDERNG_ELEMENT_COLUMNS_PRIORITY_HELP', $translations);
            self::assertArrayHasKey('COM_CONTENTBUILDERNG_ELEMENT_PUBLISHED_TIP', $translations);
            self::assertNotSame('', $translations['COM_CONTENTBUILDERNG_ELEMENT_COLUMNS_PRIORITY_HELP']);
            self::assertNotSame('', $translations['COM_CONTENTBUILDERNG_ELEMENT_PUBLISHED_TIP']);
        }
    }

    private function read(string $relativePath): string
    {
        $path = $this->root . '/' . $relativePath;
        self::assertFileExists($path);

        return (string) file_get_contents($path);
    }
}
