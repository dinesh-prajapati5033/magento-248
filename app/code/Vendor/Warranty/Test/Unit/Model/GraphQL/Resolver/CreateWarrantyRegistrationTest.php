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

class CreateWarrantyRegistrationTest extends TestCase
{
    /**
     * @var CreateWarrantyRegistration
     */
    private $resolver;

    /**
     * @var ProductRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productRepository;

    /**
     * Setup method executed before each test
     * - Mocks dependencies
     * - Instantiates the resolver with the mocked dependencies
     */
    protected function setUp(): void
    {
        // Mock ProductRepositoryInterface
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);

        // Instantiate resolver with all required dependencies
        $this->resolver = new CreateWarrantyRegistration(
            $this->createMock(WarrantyRegistrationFactory::class),
            $this->createMock(WarrantyResource::class),
            $this->createMock(RegistrationCollectionFactory::class), // CollectionFactory mock
            $this->createMock(CustomerSession::class),
            $this->productRepository, // Product repository mock
            $this->createMock(OrderRepositoryInterface::class)
        );
    }

    /**
     * Test: Invalid product SKU should throw GraphQlInputException
     * - Mocks the product repository to throw NoSuchEntityException for invalid SKU
     * - Expects GraphQlInputException with a message containing 'Product SKU'
     */
    public function testInvalidSkuThrowsException(): void
    {
        // Simulate product not found for given SKU
        $this->productRepository->method('get')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        // Expect the resolver to throw GraphQlInputException
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Product SKU');

        // Call the resolver with invalid SKU input
        $this->resolver->resolve(
            $this->createMock(\Magento\Framework\GraphQl\Config\Element\Field::class),
            null, // context
            $this->createMock(\Magento\Framework\GraphQl\Schema\Type\ResolveInfo::class),
            null, // value
            [
                'input' => [
                    'product_sku' => 'INVALID123', // invalid SKU
                    'serial_number' => 'SN001'
                ]
            ]
        );
    }
}
