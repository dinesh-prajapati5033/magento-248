<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 */
declare(strict_types=1);

namespace Pixlogix\Items\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

class StatusOptions implements OptionSourceInterface
{
    /**
     * Get options array for status field
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['label' => __('Enabled'), 'value' => 1],
            ['label' => __('Disabled'), 'value' => 0],
        ];
    }
}
