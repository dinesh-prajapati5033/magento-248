<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 */
declare(strict_types=1);

namespace Pixlogix\Items\Controller\Adminhtml\Item\FileUploader;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Pixlogix\Items\Model\ImageUploader;

class Save extends Action
{
    /**
     * @var ImageUploader
     */
    protected ImageUploader $imageUploader;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Action\Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Check ACL permission for this controller
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Pixlogix_Items::item');
    }

    /**
     * Execute image upload action
     *
     * Handles AJAX upload of image to tmp directory
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            // Upload image to tmp dir
            $result = $this->imageUploader->saveFileToTmpDir('image');

            // Include session cookie data
            $result['cookie'] = [
                'name'     => $this->_getSession()->getName(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = [
                'error'     => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
