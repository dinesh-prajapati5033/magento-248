<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Controller\Registration;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Vendor\Warranty\Api\WarrantyRegistrationRepositoryInterface;
use Vendor\Warranty\Model\WarrantyRegistrationFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Controller to handle Warranty Registration form submission on frontend
 */
class Save extends Action
{
    /**
     * Warranty Registration Repository for saving registrations
     *
     * @var WarrantyRegistrationRepositoryInterface
     */
    protected WarrantyRegistrationRepositoryInterface $repository;

    /**
     * Customer session to get current logged-in customer
     *
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * Factory for creating new Warranty Registration model instances
     *
     * @var WarrantyRegistrationFactory
     */
    protected WarrantyRegistrationFactory $registrationFactory;

    /**
     * Magento Order Repository for validating customer’s order ownership
     *
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * Product Repository to validate that product SKU exists
     *
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * SearchCriteriaBuilder to create repository search criteria
     *
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param Context $context
     * @param WarrantyRegistrationRepositoryInterface $repository
     * @param WarrantyRegistrationFactory $registrationFactory
     * @param CustomerSession $customerSession
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        WarrantyRegistrationRepositoryInterface $repository,
        WarrantyRegistrationFactory $registrationFactory,
        CustomerSession $customerSession,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->registrationFactory = $registrationFactory;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Execute controller action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        // Ensure the customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please log in to submit a warranty registration.'));
            return $this->_redirect('customer/account/login');
        }

        // Retrieve POST data from the submitted form
        $data = $this->getRequest()->getPostValue();

        // Validate that some data was sent
        if (!$data) {
            $this->messageManager->addErrorMessage(__('No data received.'));
            return $this->_redirect('*/*/formRegistration');
        }

        try {
            $customerId = (int)$this->customerSession->getCustomerId();

            // Validate that the product SKU exists in the catalog
            if (empty($data['product_sku'])) {
                throw new LocalizedException(__('Product SKU is required.'));
            }
            try {
                $this->productRepository->get($data['product_sku']);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                throw new LocalizedException(__('Product with SKU "%1" does not exist.', $data['product_sku']));
            }

            // Optional: Validate provided order number belongs to this customer
            if (!empty($data['order_id'])) {
                try {
                    $order = $this->orderRepository->get((int)$data['order_id']);
                    if ((int)$order->getCustomerId() !== $customerId) {
                        throw new LocalizedException(__('The provided order does not belong to your account.'));
                    }
                } catch (\Exception $e) {
                    throw new LocalizedException(__('Invalid order number. Please check and try again.'));
                }
            }

            // Create a new registration model instance
            $registration = $this->registrationFactory->create();

            // Populate model with form data and current customer info
            $registration->setCustomerId($customerId)
                ->setProductSku($data['product_sku'])
                ->setSerialNumber($data['serial_number'] ?? '')
                ->setPurchaseDate($data['purchase_date'] ?? '')
                ->setOrderId(isset($data['order_id']) ? $data['order_id'] : null)
                ->setProofUrl($data['proof_url'] ?? null)
                ->setStatus(0); // 0 = Pending approval

            // Check for duplicate serial_number per product SKU via repository
            // This ensures that each serial number is unique for a given product.
            $existingCollection = $this->repository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter('product_sku', $registration->getProductSku())
                    ->addFilter('serial_number', strtoupper($registration->getSerialNumber()))
                    ->create()
            );

            if ($existingCollection->getTotalCount() > 0) {
                throw new LocalizedException(__(
                    'A registration with this serial number already exists for this product.'
                ));
            }

            // Save registration via repository
            $this->repository->save($registration);

            // Success message
            $this->messageManager->addSuccessMessage(__('Warranty registration submitted successfully.'));
        } catch (LocalizedException $e) {
            // Handle Magento validation and logic errors
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            // Handle any unexpected runtime errors
            $this->messageManager->addErrorMessage(__('Error submitting registration: %1', $e->getMessage()));
        }

        // Redirect back to the registration form page
        return $this->_redirect('*/*/formRegistration');
    }
}
