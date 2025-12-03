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
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;

/**
 * Controller for displaying the "New Warranty Registration" page in the frontend.
 *
 * Functionality:
 *  - Checks if the customer is logged in.
 *  - Redirects guests to the login page with an error message.
 *  - Loads the warranty registration page for logged-in customers.
 */
class FormRegistration extends Action
{
    /**
     * @var PageFactory Factory to create frontend page results
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var Session Customer session to check login status
     */
    protected Session $customerSession;

    /**
     * Constructor
     *
     * @param Context $context Standard controller context
     * @param PageFactory $resultPageFactory Factory to create page result objects
     * @param Session $customerSession Customer session model
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Execute method called when this controller is accessed
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Check if customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            // Add error message and redirect to login page if not logged in
            $this->messageManager->addErrorMessage(__('You need to log in to register a warranty.'));
            return $this->_redirect('customer/account/login');
        }

        // Load the warranty registration page
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Warranty Registration'));
        return $resultPage;
    }
}
