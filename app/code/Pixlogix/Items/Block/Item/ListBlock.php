<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025
 *
 * Frontend Block — Item Listing Block
 *
 * Responsible for loading and rendering a filtered list of items
 * on the frontend listing page based on store, customer group, and configuration.
 */

declare(strict_types=1);

namespace Pixlogix\Items\Block\Item;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pixlogix\Items\Model\ResourceModel\Item\CollectionFactory;
use Pixlogix\Items\Helper\Data as ItemsHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Cms\Model\Template\FilterProvider;
/**
 * Class ListBlock
 *
 * Handles frontend listing data retrieval and rendering.
 * Adds pagination, image handling, and store/customer group filters.
 */
class ListBlock extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ItemsHelper
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param ItemsHelper $helper
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        ItemsHelper $helper,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * Check if the module is enabled from configuration
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->helper->isModuleEnabled();
    }

    /**
     * Retrieve the collection of items filtered by:
     * - Current store (supports multi-select via CSV)
     * - Current customer group (supports multi-select via CSV)
     *
     * @return \Pixlogix\Items\Model\ResourceModel\Item\Collection
     */
   public function getItems()
    {
        if (!$this->hasData('items_collection')) {
            $collection = $this->collectionFactory->create();
            $collection->setOrder('item_id', 'DESC');

            $storeId = (int)$this->_storeManager->getStore()->getId();
            $groupId = (int)$this->_customerSession->getCustomerGroupId();

            /**
             * Filter by enabled status only
             * - Ensures only active (enabled) items are shown on frontend
             */
            $collection->addFieldToFilter('status', 1);

            /**
             * Filter items by store (CSV or null allowed)
             * - Supports multistore visibility configuration
             */
            $collection->addFieldToFilter(
                ['store_ids', 'store_ids'],
                [
                    ['null' => true],
                    ['finset' => $storeId]
                ]
            );

            /**
             * Filter items by customer group (CSV or null allowed)
             * - Ensures only allowed customer groups can see the item
             */
            $collection->addFieldToFilter(
                ['customer_group_ids', 'customer_group_ids'],
                [
                    ['null' => true],
                    ['finset' => $groupId]
                ]
            );

            /**
             * Apply pagination parameters
             */
            $currentPage = (int)$this->getRequest()->getParam('p', 1);
            $pageSize = (int)$this->getRequest()->getParam('limit', 10);

            $collection->setCurPage($currentPage);
            $collection->setPageSize($pageSize);

            $this->setData('items_collection', $collection);
        }

        return $this->getData('items_collection');
    }

    /**
     * Get the full image URL for the item
     *
     * @param string|null $image
     * @return string
     */
    public function getImageUrl(?string $image): string
    {
        if (!$image) {
            return $this->helper->getPlaceholderImageUrl();
        }

        $mediaUrl = $this->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . 'items/' . ltrim($image, '/');
    }

    /**
     * Retrieve configured image width for list view
     *
     * @return int
     */
    public function getListImageWidth(): int
    {
        return (int)$this->helper->getListImageWidth();
    }

    /**
     * Retrieve configured image height for list view
     *
     * @return int
     */
    public function getListImageHeight(): int
    {
        return (int)$this->helper->getListImageHeight();
    }

    /**
     * Get pager block HTML output
     *
     * @return string
     */
    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Prepare layout — adds pager for item listing
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getItems()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'pixlogix.items.pager'
            )->setCollection(
                $this->getItems()
            );

            $this->setChild('pager', $pager);

            // Force collection to load with limits
            $this->getItems()->load();
        }

        return $this;
    }

    /**
     * Process WYSIWYG content (e.g., short_content or content fields)
     * Replaces {{media url="..."}} and other Magento directives.
     *
     * @param string $content
     * @return string
     */
    public function getProcessedContent(string $content): string
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }
}
