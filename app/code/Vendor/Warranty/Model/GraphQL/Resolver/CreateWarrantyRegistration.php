<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Model\GraphQL\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vendor\Warranty\Model\WarrantyRegistrationFactory;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration as WarrantyResource;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\CollectionFactory as RegistrationCollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * GraphQL resolver for the `createWarrantyRegistration` mutation.
 *
 * Functionality:
 *  - Creates a new warranty registration entry.
 *  - Validates that `product_sku` exists in the catalog.
 *  - If `order_id` is provided, ensures it belongs to the logged-in customer.
 *  - Allows guest registrations (customer_id = null).
 *  - Defaults registration status to "pending" (0).
 *
 * Throws:
 *  - GraphQlInputException for invalid input data.
 *  - GraphQlAuthorizationException for unauthorized operations.
 */
class CreateWarrantyRegistration implements ResolverInterface
{
    /**
     * Factory to create new WarrantyRegistration instances.
     *
     * @var WarrantyRegistrationFactory
     */
    private WarrantyRegistrationFactory $registrationFactory;

    /**
     * Resource model to save and load WarrantyRegistration records.
     *
     * @var WarrantyResource
     */
    private WarrantyResource $registrationResource;

    /**
     * Collection factory to query WarrantyRegistration records.
     *
     * @var RegistrationCollectionFactory
     */
    private RegistrationCollectionFactory $collectionFactory;

    /**
     * Customer session for identifying the current logged-in user.
     *
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * Product repository to validate SKU existence.
     *
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * Order repository to validate order ownership.
     *
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * Constructor
     *
     * @param WarrantyRegistrationFactory $registrationFactory
     * @param WarrantyResource $registrationResource
     * @param RegistrationCollectionFactory $collectionFactory
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        WarrantyRegistrationFactory $registrationFactory,
        WarrantyResource $registrationResource,
        RegistrationCollectionFactory $collectionFactory,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->registrationFactory = $registrationFactory;
        $this->registrationResource = $registrationResource;
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Resolve function for GraphQL mutation `createWarrantyRegistration`.
     *
     * @param Field $field GraphQL field metadata
     * @param mixed $context GraphQL execution context
     * @param ResolveInfo $info GraphQL query metadata
     * @param array|null $value Parent field data
     * @param array|null $args Mutation input arguments
     * @return array Created warranty registration data
     * @throws GraphQlInputException|GraphQlAuthorizationException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        // Retrieve input payload from GraphQL arguments
        $input = $args['input'] ?? null;

        if (!$input) {
            throw new GraphQlInputException(__('Input data is required.'));
        }

        // Validate that the provided product SKU exists
        try {
            $this->productRepository->get($input['product_sku']);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new GraphQlInputException(__('Product SKU "%1" does not exist.', $input['product_sku']));
        }

        // Identify current customer (nullable for guests)
        $customerId = null;
        if ($this->customerSession->isLoggedIn()) {
            $customerId = (int)$this->customerSession->getCustomerId();
        }

        // Validate that provided order_id (if any) belongs to current customer
        if (!empty($input['order_id'])) {
            if (!$customerId) {
                throw new GraphQlAuthorizationException(__('Guest users cannot assign an order.'));
            }

            try {
                $order = $this->orderRepository->get((int)$input['order_id']);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                throw new GraphQlInputException(__('Order ID "%1" does not exist.', $input['order_id']));
            }

            if ((int)$order->getCustomerId() !== $customerId) {
                throw new GraphQlInputException(__(
                    'Order ID "%1" does not belong to the current customer.',
                    $input['order_id']
                ));
            }
        }

        // Ensure serial number is uppercase for consistency
        $serialNumber = strtoupper($input['serial_number']);
        $productSku = $input['product_sku'];

        // Check if the same serial number already exists for this product SKU
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('serial_number', $serialNumber)
            ->addFieldToFilter('product_sku', $productSku);

        if ($collection->getSize() > 0) {
            throw new GraphQlInputException(__(
                'A registration with this Serial Number for the same Product SKU already exists.'
            ));
        }

        // Create and populate the warranty registration
        $registration = $this->registrationFactory->create();
        $registration->setCustomerId($customerId ?? 0); // null for guests
        $registration->setProductSku($productSku);
        $registration->setSerialNumber($serialNumber);
        $registration->setPurchaseDate($input['purchase_date'] ?? null);
        $registration->setOrderId($input['order_id'] ?? null);
        $registration->setProofUrl($input['proof_url'] ?? null);
        $registration->setStatus(0); // pending by default

        // Save record to database
        $this->registrationResource->save($registration);

        // Return the saved registration data for GraphQL response
        return $registration->getData();
    }
}
