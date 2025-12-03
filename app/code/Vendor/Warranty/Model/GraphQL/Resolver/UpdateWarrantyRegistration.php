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
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * GraphQL resolver for the `updateWarrantyRegistration` mutation.
 *
 * Functionality:
 *  - Allows logged-in customers to update their pending warranty registration.
 *  - Ensures only the registration owner can update.
 *  - Validates product SKU existence.
 *  - If order_id provided, ensures it belongs to the same customer.
 *  - Does not allow updates to non-pending (approved/rejected) records.
 */
class UpdateWarrantyRegistration implements ResolverInterface
{
    /**
     * Factory class for creating WarrantyRegistration instances.
     *
     * @var WarrantyRegistrationFactory
     */
    private WarrantyRegistrationFactory $registrationFactory;

    /**
     * Resource model for performing CRUD operations on WarrantyRegistration entities.
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
     * Customer session object to check login status and retrieve current customer ID.
     *
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * Repository for retrieving product information by SKU.
     *
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * Repository for retrieving order information to validate ownership.
     *
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * Constructor
     *
     * @param WarrantyRegistrationFactory $registrationFactory
     * @param WarrantyResource $registrationResource
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param RegistrationCollectionFactory $collectionFactory
     */
    public function __construct(
        WarrantyRegistrationFactory $registrationFactory,
        WarrantyResource $registrationResource,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        RegistrationCollectionFactory $collectionFactory
    ) {
        $this->registrationFactory = $registrationFactory;
        $this->registrationResource = $registrationResource;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Resolve function for GraphQL mutation `updateWarrantyRegistration`.
     *
     * @param Field $field GraphQL field metadata
     * @param mixed $context GraphQL request context
     * @param ResolveInfo $info GraphQL query info
     * @param array|null $value Previous resolver value
     * @param array|null $args Mutation input arguments
     * @return array Updated warranty registration data
     * @throws GraphQlAuthorizationException|GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        
        // Ensure customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            throw new GraphQlAuthorizationException(__('Customer must be logged in to update warranty.'));
        }

        $customerId = (int)$this->customerSession->getCustomerId();
        $input = $args['input'] ?? null;

        if (!$input || empty($input['registration_id'])) {
            throw new GraphQlInputException(__('Registration ID is required.'));
        }

        // Load existing registration record
        $registration = $this->registrationFactory->create();
        $this->registrationResource->load($registration, (int)$input['registration_id']);

        if (!$registration->getId()) {
            throw new GraphQlInputException(__('Warranty registration not found.'));
        }

        // 3. Validate that the registration belongs to current user
        if ((int)$registration->getCustomerId() !== $customerId) {
            throw new GraphQlAuthorizationException(__('You do not own this warranty registration.'));
        }

        // Allow updates only for pending records
        if ((int)$registration->getStatus() !== 0) {
            throw new GraphQlInputException(__('Only pending warranty registrations can be updated.'));
        }

        // Validate product SKU if provided
        if (isset($input['product_sku'])) {
            try {
                $this->productRepository->get($input['product_sku']);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                throw new GraphQlInputException(__('Product SKU "%1" does not exist.', $input['product_sku']));
            }
            $registration->setProductSku($input['product_sku']);
        }

        // Update serial number if provided
        if (isset($input['serial_number'])) {
            $registration->setSerialNumber($input['serial_number']);
        }

        // --- New Code Start: Check for duplicate serial number + product SKU ---
        if (isset($input['serial_number']) || isset($input['product_sku'])) {
            $serialNumber = strtoupper($registration->getSerialNumber());
            $productSku = $registration->getProductSku();

            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('serial_number', $serialNumber)
                ->addFieldToFilter('product_sku', $productSku)
                ->addFieldToFilter('registration_id', ['neq' => $registration->getId()]); // exclude current

            if ($collection->getSize() > 0) {
                throw new GraphQlInputException(__(
                    'A registration with this Serial Number for the same Product SKU already exists.'
                ));
            }
        }
        // --- New Code End ---

        // Update purchase date if provided
        if (isset($input['purchase_date'])) {
            $purchaseDate = date('Y-m-d H:i:s', strtotime($input['purchase_date']));
            $registration->setPurchaseDate($purchaseDate);
        }

        // Validate order ownership if order_id provided
        if (isset($input['order_id'])) {
            try {
                $order = $this->orderRepository->get((int)$input['order_id']);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                throw new GraphQlInputException(__('Order ID "%1" does not exist.', $input['order_id']));
            }

            if ((int)$order->getCustomerId() !== $customerId) {
                throw new GraphQlInputException(__('Order ID "%1" does not belong to you.', $input['order_id']));
            }

            $registration->setOrderId($input['order_id']);
        }

        // Update proof URL if provided
        if (isset($input['proof_url'])) {
            $registration->setProofUrl($input['proof_url']);
        }

        // Save updated entity to database
        $this->registrationResource->save($registration);

        return $registration->getData();
    }
}
