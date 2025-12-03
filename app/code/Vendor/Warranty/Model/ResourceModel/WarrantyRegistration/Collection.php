<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Model\ResourceModel\WarrantyRegistration;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * Collection class for Warranty Registration entities.
 * Provides methods to retrieve a set of WarrantyRegistration models from the database.
 *
 */
class Collection extends AbstractCollection
{
    /**
     * Primary key field name for the collection
     *
     * @var string
     */
    protected $_idFieldName = 'registration_id';

    /**
     * Initialize collection model and resource model
     *
     * This tells Magento which model and resource model the collection represents.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Vendor\Warranty\Model\WarrantyRegistration::class,          // Model class
            \Vendor\Warranty\Model\ResourceModel\WarrantyRegistration::class // Resource model class
        );
    }
}
