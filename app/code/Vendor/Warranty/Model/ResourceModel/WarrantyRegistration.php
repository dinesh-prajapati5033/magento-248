<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */

declare(strict_types=1);

namespace Vendor\Warranty\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class WarrantyRegistration extends AbstractDb
{
    /**
     * Primary key field name for the database table
     *
     * @var string
     */
    protected $_idFieldName = 'registration_id';

    /**
     * Initialize the resource model
     *
     * Links this resource model to the corresponding database table
     * and defines the primary key field.
     *
     * @return void
     */
    protected function _construct(): void
    {
        // _init(tableName, primaryKeyField)
        // 'vendor_warranty_registration' is the database table
        // 'registration_id' is the primary key column
        $this->_init('vendor_warranty_registration', 'registration_id');
    }
}
