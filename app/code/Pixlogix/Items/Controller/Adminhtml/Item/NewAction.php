<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025
 */

declare(strict_types=1);

namespace Pixlogix\Items\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends Action
{
    /**
     * Check ACL permission
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Pixlogix_Items::items');
    }

    /**
     * Forward to Edit action
     */
    public function execute(): ResultInterface
    {
        $this->_forward('edit');
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
    }
}
