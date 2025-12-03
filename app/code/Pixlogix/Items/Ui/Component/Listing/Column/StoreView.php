<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * UI Component column class for displaying store view names in the admin grid.
 */
declare(strict_types=1);

namespace Pixlogix\Items\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreView
 *
 * Converts store IDs into readable store names for display in the admin grid.
 */
class StoreView extends Column
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source
     *
     * Loops through each grid item and replaces the stored store IDs
     * with corresponding store names for better readability in the admin grid.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        // Ensure data source contains items
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            // If store IDs exist, process them
            if (isset($item['store_ids']) && $item['store_ids'] !== '') {
                $storeIds = explode(',', $item['store_ids']);
                $storeNames = [];

                foreach ($storeIds as $storeId) {
                    try {
                        // Retrieve store name by ID
                        $store = $this->storeManager->getStore((int)$storeId);
                        $storeNames[] = $store->getName();
                    } catch (\Exception $e) {
                        // Add placeholder if store no longer exists
                        $storeNames[] = 'N/A';
                    }
                }

                // Replace IDs with comma-separated store names
                $item['store_ids'] = implode(', ', $storeNames);
            }
        }

        return $dataSource;
    }
}
