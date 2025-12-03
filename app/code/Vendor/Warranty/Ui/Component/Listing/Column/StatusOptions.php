<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

class StatusOptions implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 2, // The actual value of "Yes" in your database/data
                'label' => __('Rejected')
            ],
            [
                'value' => 1, // The actual value of "Yes" in your database/data
                'label' => __('Approved')
            ],
            [
                'value' => 0, // The actual value of "No" in your database/data
                'label' => __('Pending')
            ]
        ];
    }
}
