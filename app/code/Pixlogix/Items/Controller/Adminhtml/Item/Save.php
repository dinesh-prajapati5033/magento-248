<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025
 *
 * Adminhtml Controller — Save Item
 *
 * Handles the logic for saving (create/update) Pixlogix Items
 * from the Magento Admin panel form submission.
 */

namespace Pixlogix\Items\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Save
 *
 * Responsible for processing form data submitted from the admin
 * "Edit Item" page — including file upload, multiselect handling,
 * and model saving.
 */
class Save extends Action
{
    /**
     * @var \Pixlogix\Items\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Pixlogix\Items\Model\ImageUploader
     */
    protected $imageUploader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Pixlogix\Items\Model\ItemFactory $itemFactory
     * @param \Pixlogix\Items\Model\ImageUploader $imageUploader
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        Action\Context $context,
        \Pixlogix\Items\Model\ItemFactory $itemFactory,
        \Pixlogix\Items\Model\ImageUploader $imageUploader,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->itemFactory = $itemFactory;
        $this->imageUploader = $imageUploader;
        $this->filesystem = $filesystem;
    }

    /**
     * Check ACL permissions for current admin user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Pixlogix_Items::save');
    }

    /**
     * Execute action — handles saving of Item data.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        // If no data received, redirect back to listing
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        try {
            /** @var \Pixlogix\Items\Model\Item $model */
            $model = $this->itemFactory->create();
            $id = $this->getRequest()->getParam('id');

            // Load existing record if editing
            if ($id) {
                $model->load($id);
            }

            /**
             * =======================================
             * IMAGE UPLOAD HANDLING
             * =======================================
             */
            if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                // New image uploaded — move from tmp and create folder structure
                $data['image'] =$data['image'][0]['name'];
                $this->imageUploader;
                $this->imageUploader->moveFileFromTmp($data['image']);

            } elseif (isset($data['image'][0]['name']) && !isset($data['image'][0]['tmp_name'])) {
                // Existing image retained (no new upload)
                $imageName = $data['image'][0]['name'];
                $first = substr($imageName, 0, 1);
                $second = substr($imageName, 1, 1);
                $data['image'] = '/' . $first . '/' . $second . '/' . ltrim($imageName, '/');

            } else {
                // No image selected — keep old or set null
                if ($model->getId() && $model->getImage()) {
                    $data['image'] = $model->getImage();
                } else {
                    $data['image'] = null;
                }
            }

            /**
             * =======================================
             * MULTISELECT FIELDS (Store & Customer Groups)
             * =======================================
             */
            if (isset($data['store_ids']) && is_array($data['store_ids'])) {
                $data['store_ids'] = implode(',', $data['store_ids']);
            }

            if (isset($data['customer_group_ids']) && is_array($data['customer_group_ids'])) {
                $data['customer_group_ids'] = implode(',', $data['customer_group_ids']);
            }

            /**
             * =======================================
             * SAVE MODEL DATA
             * =======================================
             */
            $model->setData($data);
            $model->save();

            // Display success message
            $this->messageManager->addSuccessMessage(__('Item has been saved successfully.'));

            // Redirect appropriately
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');

        } catch (LocalizedException $e) {
            // Known (expected) Magento validation exception
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            // General system error
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the item.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }

        // Redirect back to edit form in case of error
        return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
    }
}
