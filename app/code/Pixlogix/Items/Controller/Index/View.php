<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * Frontend Controller — View action for displaying a single item.
 */

declare(strict_types=1);

namespace Pixlogix\Items\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Pixlogix\Items\Model\ItemFactory;
use Magento\Framework\Registry;
use Pixlogix\Items\Helper\Data as DataHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Constructor.
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        ItemFactory $itemFactory,
        Registry $registry,
        DataHelper $dataHelper,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->itemFactory = $itemFactory;
        $this->registry = $registry;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * Loads the item by `url_key` and renders the detail page.
     * If the item does not exist, is disabled, or not visible to current
     * store view or customer group, forwards to 404.
     */
    public function execute()
    {
        // Check if module is enabled
        if (!$this->dataHelper->isModuleEnabled()) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->setModule('cms')->setController('noroute')->forward('index');
        }

        // Get URL key
        $urlKey = $this->getRequest()->getParam('url_key');
        if (!$urlKey) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        // Load item by URL key
        $item = $this->itemFactory->create()->load($urlKey, 'url_key');

        // Check if item exists and enabled
        if (!$item->getId() || (int)$item->getStatus() !== 1) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->setModule('cms')->setController('noroute')->forward('index');
        }

        // Check store view visibility
        $allowedStores = explode(',', (string)$item->getStoreIds());
        $currentStoreId = (int)$this->storeManager->getStore()->getId();

        if (!in_array('0', $allowedStores) && !in_array((string)$currentStoreId, $allowedStores)) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->setModule('cms')->setController('noroute')->forward('index');
        }

        // Check customer group visibility
        // Assuming your DB field is `customer_group_ids` storing comma-separated group IDs
        $allowedGroups = explode(',', (string)$item->getCustomerGroupIds());
        $currentGroupId = (int)$this->customerSession->getCustomerGroupId();

        if (!empty($allowedGroups) && !in_array((string)$currentGroupId, $allowedGroups)) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->setModule('cms')->setController('noroute')->forward('index');
        }

        // Register current item for layout
        $this->registry->register('current_item', $item);

        // Render the detail page
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($item->getTitle());
        return $resultPage;
    }
}
