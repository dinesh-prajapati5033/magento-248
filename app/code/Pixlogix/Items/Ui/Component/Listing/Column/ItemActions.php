<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * UI Component column class for providing Edit/Delete actions in the admin grid.
 */
declare(strict_types=1);

namespace Pixlogix\Items\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ItemActions
 *
 * Adds Edit and Delete action links to each row in the admin grid.
 */
class ItemActions extends Column
{
    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source
     *
     * Adds "Edit" and "Delete" action links for each grid item.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                // Ensure item ID exists
                $id = $item['item_id'] ?? null;

                if ($id) {
                    // Add Edit action
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl('pixlogix_items/item/edit', ['id' => $id]),
                        'label' => __('Edit')
                    ];

                    // Add Delete action with confirmation popup
                    $item[$this->getData('name')]['delete'] = [
                        'href' => $this->urlBuilder->getUrl('pixlogix_items/item/delete', ['id' => $id]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete Item'),
                            'message' => __('Are you sure you want to delete this item?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
