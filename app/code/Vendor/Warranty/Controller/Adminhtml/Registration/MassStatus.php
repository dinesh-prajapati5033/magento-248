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
use Magento\Ui\Component\MassAction\Filter;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\CollectionFactory;
use Vendor\Warranty\Api\WarrantyRegistrationRepositoryInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class MassStatus
 *
 * Handles mass status update actions (Approve / Reject)
 * for Warranty Registrations from the admin grid.
 *
 * Notes:
 *  - Loads selected registrations using MassAction filter.
 *  - Saves updated status via the repository.
 *  - Dispatches `vendor_warranty_approved` event for approved registrations.
 *  - Displays success or notice messages in the admin panel.
 */
class MassStatus extends Action
{
    /**
     * @var Filter MassAction filter for getting selected items
     */
    private Filter $filter;

    /**
     * @var CollectionFactory Factory to create registration collections
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var WarrantyRegistrationRepositoryInterface Repository for saving registrations
     */
    private WarrantyRegistrationRepositoryInterface $repository;

    /**
     * Constructor
     *
     * @param Action\Context $context Controller context
     * @param Filter $filter MassAction filter used to get selected items
     * @param CollectionFactory $collectionFactory Factory to create registration collections
     * @param WarrantyRegistrationRepositoryInterface $repository Repository to save registrations
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        WarrantyRegistrationRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
    }

    /**
     * ACL permission check for this controller action
     *
     * @return bool True if user has permission to access this action
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Vendor_Warranty::registrations');
    }

    /**
     * Execute mass status update
     *
     * @return Redirect Redirects back to the registration grid
     */
    public function execute(): Redirect
    {
        // Get target status value from request (1 = approve, 0 = reject)
        $status = (int) $this->getRequest()->getParam('status', -1);

        // Get filtered collection of selected items
        /** @var \Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $updatedCount = 0;

        foreach ($collection as $item) {
            // a. Load full registration entity using repository
            $registration = $this->repository->getById((int)$item->getId());

            // b. Update status
            $registration->setStatus($status);

            // c. Save updated registration
            $this->repository->save($registration);

            // d. Dispatch event for approved registrations only
            if ($status === 1) {
                $this->_eventManager->dispatch(
                    'vendor_warranty_approved',
                    ['registration' => $registration]
                );
            }

            $updatedCount++;
        }

        // Show admin panel messages
        if ($updatedCount > 0) {
            $this->messageManager->addSuccessMessage(__(
                'A total of %1 record(s) have been updated.',
                $updatedCount
            ));
        } else {
            $this->messageManager->addNoticeMessage(__('No records were updated.'));
        }

        // Redirect back to the grid
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
