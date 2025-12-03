<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * Admin Controller — Mass Action for Enabling/Disabling Items
 *
 * This controller handles bulk updates (Enable/Disable) from the admin grid.
 */

declare(strict_types=1);

namespace Pixlogix\Items\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Pixlogix\Items\Model\ResourceModel\Item\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassAction
 *
 * Handles bulk status updates (enable/disable) for items
 * from the Pixlogix Items admin grid.
 */
class MassAction extends Action
{
    /**
     * ACL resource identifier
     */
    public const ADMIN_RESOURCE = 'Pixlogix_Items::save';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * Performs bulk Enable/Disable action on selected records.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // Get the target status (1 = Enable, 0 = Disable)
        $status = (int)$this->getRequest()->getParam('status');

        // Validate input
        if (!in_array($status, [1, 0], true)) {
            $this->messageManager->addErrorMessage(__('Invalid status value.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        try {
            // Retrieve selected items from UI grid
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $updated = 0;

            // Loop through selected items and update status
            foreach ($collection as $item) {
                $item->setStatus($status);
                $item->save();
                $updated++;
            }

            // Show result message based on update count
            if ($updated > 0) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been %2.', $updated, $status === 1 ? 'enabled' : 'disabled')
                );
            } else {
                $this->messageManager->addNoticeMessage(__('No records were updated.'));
            }

        } catch (\Exception $e) {
            // Handle exceptions gracefully
            $this->messageManager->addErrorMessage(
                __('Something went wrong while updating items: %1', $e->getMessage())
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
