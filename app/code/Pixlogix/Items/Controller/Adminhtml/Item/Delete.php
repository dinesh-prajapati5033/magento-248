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
use Pixlogix\Items\Model\ItemFactory;
use Magento\Framework\Controller\Result\Redirect;

class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Pixlogix_Items::delete';

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @param Context $context
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        Context $context,
        ItemFactory $itemFactory
    ) {
        parent::__construct($context);
        $this->itemFactory = $itemFactory;
    }

    /**
     * Execute action
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('id');

        if ($id) {
            try {
                $model = $this->itemFactory->create()->load($id);
                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('This item no longer exists.'));
                }

                $model->delete();
                $this->messageManager->addSuccessMessage(__('The item has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['item_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find an item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
