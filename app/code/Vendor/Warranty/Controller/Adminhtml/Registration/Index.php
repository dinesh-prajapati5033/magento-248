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

class Index extends Action
{
    /**
     * PageFactory instance
     *
     * Used to create result page objects for rendering adminhtml pages.
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context     $context           Admin context object (request, session, authorization)
     * @param PageFactory $resultPageFactory Factory to create admin pages
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Access control
     *
     * Determines whether the current admin user is allowed to access this controller.
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Vendor_Warranty::registrations');
    }

    /**
     * Execute method
     *
     * Creates and renders the admin page showing the Warranty Registrations grid.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Create the result page object
        $resultPage = $this->resultPageFactory->create();

        // Set active menu in admin panel
        $resultPage->setActiveMenu('Vendor_Warranty::registrations');

        // Set the page title
        $resultPage->getConfig()->getTitle()->prepend(__('Warranty Registrations'));

        return $resultPage;
    }
}
