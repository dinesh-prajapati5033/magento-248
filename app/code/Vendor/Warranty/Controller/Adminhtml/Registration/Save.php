<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Controller\Adminhtml\Registration;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Vendor\Warranty\Api\WarrantyRegistrationRepositoryInterface;
use Vendor\Warranty\Api\Data\WarrantyRegistrationInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\CollectionFactory as RegistrationCollectionFactory;

/**
 * Class Save
 *
 * Handles saving of Warranty Registration records from the admin edit form.
 */
class Save extends Action
{
    /**
     * @var WarrantyRegistrationRepositoryInterface
     */
    protected WarrantyRegistrationRepositoryInterface $repository;

    /**
     * @var WarrantyRegistrationInterfaceFactory
     */
    protected WarrantyRegistrationInterfaceFactory $factory;

    /**
     * @var RegistrationCollectionFactory
     */
    protected RegistrationCollectionFactory $collectionFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param WarrantyRegistrationRepositoryInterface $repository
     * @param WarrantyRegistrationInterfaceFactory $factory
     * @param RegistrationCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        WarrantyRegistrationRepositoryInterface $repository,
        WarrantyRegistrationInterfaceFactory $factory,
        RegistrationCollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Check ACL permission for this controller.
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Vendor_Warranty::registrations');
    }

    /**
     * Execute the save action.
     *
     * @return void
     */
    public function execute()
    {
        // Retrieve POST data from request
        $data = $this->getRequest()->getPostValue();

        // If no data is provided, redirect back to the grid
        if (!$data) {
            $this->_redirect('*/*/index');
            return;
        }

        try {
            // Determine whether this is an edit or new registration
            $registration = isset($data['registration_id'])
                ? $this->repository->getById((int)$data['registration_id'])
                : $this->factory->create();

            // --- New Code Start ---
            // Ensure serial number is uppercase for consistency
            $serialNumber = isset($data['serial_number']) ? strtoupper($data['serial_number']) : '';
            $productSku = $data['product_sku'] ?? '';

            // Check if the same serial number already exists for the same product SKU
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('serial_number', $serialNumber)
                ->addFieldToFilter('product_sku', $productSku);

            // Exclude current registration if editing
            if (isset($data['registration_id'])) {
                $collection->addFieldToFilter('registration_id', ['neq' => (int)$data['registration_id']]);
            }

            if ($collection->getSize() > 0) {
                throw new LocalizedException(__(
                    'A registration with this Serial Number for the same Product SKU already exists.'
                ));
            }
            // --- New Code End ---

            // Populate model with submitted data
            $registration->setCustomerId(isset($data['customer_id']) ? (int)$data['customer_id'] : null)
                         ->setOrderId(isset($data['order_id']) ? $data['order_id'] : null)
                         ->setProductSku($productSku)
                         ->setSerialNumber($serialNumber)
                         ->setPurchaseDate($data['purchase_date'] ?? '')
                         ->setProofUrl($data['proof_url'] ?? null)
                         ->setStatus(isset($data['status']) ? (int)$data['status'] : 0);

            // Save registration via repository
            $this->repository->save($registration);

            // Dispatch event if the registration is approved
            if ((int)$data['status'] === 1) {
                $this->_eventManager->dispatch('vendor_warranty_approved', [
                    'registration' => $registration
                ]);
            }

            // Add success message
            $this->messageManager->addSuccessMessage(__('Registration saved successfully.'));
        } catch (LocalizedException $e) {
            // Catch validation or repository exceptions
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            // Catch general exceptions
            $this->messageManager->addErrorMessage(__('Error saving registration: %1', $e->getMessage()));
        }

        // Redirect back to the listing page
        $this->_redirect('*/*/index');
    }
}
