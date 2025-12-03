<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 */

declare(strict_types=1);

namespace Pixlogix\Items\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Item Resource Model
 */
class Item extends AbstractDb
{
    /**
     * Initialize main table and primary key
     */
    protected function _construct(): void
    {
        $this->_init('pixlogix_items', 'item_id');
    }
}
