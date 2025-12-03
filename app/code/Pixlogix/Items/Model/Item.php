<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 */

declare(strict_types=1);

namespace Pixlogix\Items\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Item Model
 */
class Item extends AbstractModel
{
    /**
     * Initialize resource model
     */
    protected function _construct(): void
    {
        $this->_init(\Pixlogix\Items\Model\ResourceModel\Item::class);
    }

    /**
     * Get ID
     */
    public function getId()
    {
        return $this->getData('item_id');
    }

    /**
     * Get URL key
     */
    public function getUrlKey(): ?string
    {
        return $this->getData('url_key');
    }

    /**
     * Get Status
     */
    public function isEnabled(): bool
    {
        return (bool)$this->getData('status');
    }
}
