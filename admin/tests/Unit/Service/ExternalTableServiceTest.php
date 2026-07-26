<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use CB\Component\Contentbuilderng\Administrator\Service\ExternalTableService;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 3) . '/src/Service/ExternalTableService.php';

final class ExternalTableServiceTest extends TestCase
{
    public function testClassifiesKnownTablesAsReadOnly(): void
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('getPrefix')->willReturn('jos_');

        $service = new ExternalTableService($db);

        self::assertTrue($service->isKnownReadOnly('jos_users'));
        self::assertTrue($service->isKnownReadOnly('jos_facileforms_records'));
        self::assertTrue($service->isKnownReadOnly('jos_breezingforms_records'));
        self::assertTrue($service->isKnownReadOnly('custom_Breezing_Archive'));
        self::assertTrue($service->isKnownReadOnly('jos_hikashop_product'));
        self::assertTrue($service->isKnownReadOnly('jos_virtuemart_products'));
        self::assertTrue($service->isKnownReadOnly('jos_rsform_submissions'));
        self::assertTrue($service->isKnownReadOnly('jos_sppagebuilder'));
        self::assertFalse($service->isKnownReadOnly('external_catalogue'));
        self::assertSame(2, $service->getBytableMode('jos_users'));
        self::assertSame(2, $service->getBytableMode('jos_facileforms_records'));
        self::assertSame(1, $service->getBytableMode('external_catalogue'));
        self::assertSame('joomla', $service->getSourceType('jos_users'));
        self::assertSame('Joomla', $service->getSourceLabel('jos_user_keys'));
        self::assertSame('breezingforms', $service->getSourceType('jos_facileforms_records'));
        self::assertSame('joomla-extension', $service->getSourceType('jos_hikashop_product'));
        self::assertSame('external', $service->getSourceType('external_catalogue'));
        self::assertSame('Joomla', $service->getSourceLabel('jos_users'));
        self::assertSame('BreezingForms', $service->getSourceLabel('jos_facileforms_records'));
        self::assertSame('HikaShop', $service->getSourceLabel('jos_hikashop_product'));
        self::assertSame('VirtueMart', $service->getSourceLabel('jos_virtuemart_products'));
        self::assertSame('', $service->getSourceLabel('external_catalogue'));
    }

    public function testFindsMissingSystemColumns(): void
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('getPrefix')->willReturn('jos_');

        $missing = (new ExternalTableService($db))->getMissingSystemColumns(['id', 'title']);

        self::assertNotContains('id', $missing);
        self::assertContains('storage_id', $missing);
    }
}
