<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Test\Unit\Model\GraphQL\Resolver;

use PHPUnit\Framework\TestCase;
use Vendor\Warranty\Model\GraphQL\Resolver\CreateWarrantyRegistration;
use Vendor\Warranty\Model\WarrantyRegistrationFactory;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration as WarrantyResource;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\CollectionFactory as RegistrationCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Data\Collection;

class CreateWarrantyRegistrationDuplicateTest extends TestCase
{
    /**
     * @var CreateWarrantyRegistration
     */
    private $resolver;

    /**
     * Setup method executed before each test
     * - Mocks dependencies
     * - Creates a collection that simulates an existing registration
     * - Instantiates the resolver with the mocked dependencies
     */
    protected function setUp(): void
    {
        // Mock a collection to simulate existing registration
        $collection = $this->createMock(Collection::class);
        $collection->method('addFieldToFilter')->willReturnSelf(); // Important: chainable filter
        $collection->method('getSize')->willReturn(1); // Simulate duplicate found

        // Mock collection factory to return the above collection
        $collectionFactory = $this->createMock(RegistrationCollectionFactory::class);
        $collectionFactory->method('create')->willReturn($collection);

        // Instantiate resolver with all dependencies mocked
        $this->resolver = new CreateWarrantyRegistration(
            $this->createMock(WarrantyRegistrationFactory::class),
            $this->createMock(WarrantyResource::class),
            $collectionFactory, // Inject collection factory mock
            $this->createMock(CustomerSession::class),
            $this->createMock(ProductRepositoryInterface::class),
            $this->createMock(OrderRepositoryInterface::class)
        );
    }

    /**
     * Test: Duplicate Serial Number should throw GraphQlInputException
     * - Uses the mocked collection to simulate a registration already exists
     * - Expects GraphQlInputException with message containing 'A registration with this Serial Number'
     */
    public function testDuplicateSerialNumberThrowsException(): void
    {
        // Expect exception when trying to create a duplicate registration
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('A registration with this Serial Number');

        // Call resolver with input that would trigger duplicate detection
        $this->resolver->resolve(
            $this->createMock(\Magento\Framework\GraphQl\Config\Element\Field::class),
            null, // context
            $this->createMock(\Magento\Framework\GraphQl\Schema\Type\ResolveInfo::class),
            null, // value
            ['input' => ['product_sku' => 'SKU001', 'serial_number' => 'SN001']]
        );
    }
}
