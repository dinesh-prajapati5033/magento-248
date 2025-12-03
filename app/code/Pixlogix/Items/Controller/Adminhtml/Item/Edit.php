<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 */
declare(strict_types=1);

namespace Pixlogix\Items\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Pixlogix\Items\Model\ItemFactory;

/**
 * Class Edit
 *
 * Handles loading and preparing the Item edit form
 * in the Magento Admin Panel.
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
     * @var ItemFactory
     */
    protected ItemFactory $itemFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        ItemFactory $itemFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->itemFactory = $itemFactory;
    }

    /**
     * Check ACL permission for this controller.
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Pixlogix_Items::items');
    }

    /**
     * Execute the action.
     *
     * Responsibilities:
     *  - Load the requested Item (if editing).
     *  - Register the model for use by the UI form.
     *  - Render the edit form page.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Get the item ID from request (if any)
        $id = $this->getRequest()->getParam('id');

        // Create a new item model instance
        $model = $this->itemFactory->create();

        // If an ID is provided, load existing record
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                // If record doesn’t exist, show error and redirect back to grid
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        // Register model instance for form data usage in UI components
        $this->registry->register('current_item', $model);

        // Build the result page for the admin edit form
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Pixlogix_Items::items');

        // Set dynamic page title depending on create/edit mode
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit '. $model->getTitle()) : __('New Item')
        );

        return $resultPage;
    }
}
