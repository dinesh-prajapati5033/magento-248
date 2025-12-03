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
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Vendor\Warranty\Model\WarrantyRegistrationFactory;

/**
 * Class Edit
 *
 * Handles loading and preparing the Warranty Registration
 * edit form in the Magento Admin Panel.
 */
class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var WarrantyRegistrationFactory
     */
    protected WarrantyRegistrationFactory $registrationFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param WarrantyRegistrationFactory $registrationFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        WarrantyRegistrationFactory $registrationFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->registrationFactory = $registrationFactory;
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
     * Execute the action.
     *
     * Responsibilities:
     *  - Load the requested Warranty Registration (if editing).
     *  - Register the model for use by the UI form.
     *  - Render the edit form page.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Get the registration ID from request (if any)
        $id = $this->getRequest()->getParam('registration_id');

        // Create a new registration model instance
        $model = $this->registrationFactory->create();

        // If an ID is provided, load existing record
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                // If record doesn’t exist, show error and redirect back to grid
                $this->messageManager->addErrorMessage(__('This registration no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        // Register model instance for form data usage in UI components
        $this->registry->register('current_registration', $model);

        // Build the result page for the admin edit form
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vendor_Warranty::registrations');

        // Set dynamic page title depending on create/edit mode
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Warranty Registration') : __('New Warranty Registration')
        );

        return $resultPage;
    }
}
