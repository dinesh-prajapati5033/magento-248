<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 */
declare(strict_types=1);

namespace Pixlogix\Items\Block\Adminhtml\Item\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Delete Button Data
     *
     * @var GetButtonData
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getModelId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure to delete this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
    /**
     * Dalete Url
     *
     * @var GetDeleteUrl
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getModelId()]);
    }
}
