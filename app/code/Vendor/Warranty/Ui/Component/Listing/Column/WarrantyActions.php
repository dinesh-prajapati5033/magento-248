<?php
namespace Vendor\Warranty\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class WarrantyActions
 *
 * Adds custom action links (e.g., Edit) to the Warranty Registration admin grid.
 *
 */
class WarrantyActions extends Column
{
    /**
     * URL builder for generating admin URLs
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * Adds action links (Edit) for each row in the grid.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        // Check if there are items in the data source
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                // Ensure each item has a registration_id
                if (isset($item['registration_id'])) {
                    // Add custom actions under the column's name
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                'vendor_warranty/registration/edit', // Admin route for editing
                                ['registration_id' => $item['registration_id']] // Pass ID
                            ),
                            'label' => __('Edit'), // Label displayed in grid
                            'hidden' => false,     // Action is visible
                        ],
                    ];
                }
            }
        }

        // Return modified data source
        return $dataSource;
    }
}
