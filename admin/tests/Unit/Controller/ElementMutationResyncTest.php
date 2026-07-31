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

    public function testDynamicFilterOrderLabelUsesTranslationsAndRootMenuSelectors(): void
    {
        $field = $this->read('site/src/Field/CbfilterField.php');

        self::assertStringContainsString("Text::_('COM_CONTENTBUILDERNG_ORDER_LABEL')", $field);
        self::assertStringContainsString('#jform_params_form_id', $field);
        self::assertStringContainsString('jform[params][form_id]', $field);
    }

    private function read(string $relativePath): string
    {
        $path = $this->root . '/' . $relativePath;
        self::assertFileExists($path);

        return (string) file_get_contents($path);
    }
}
