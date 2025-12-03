<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 */

declare(strict_types=1);

namespace Pixlogix\Items\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Item Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define model and resource model
     */
    protected function _construct(): void
    {
        $this->_init(
            \Pixlogix\Items\Model\Item::class,
            \Pixlogix\Items\Model\ResourceModel\Item::class
        );
    }
}
