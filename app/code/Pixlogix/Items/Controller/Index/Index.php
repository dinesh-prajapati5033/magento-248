<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * Frontend Controller — Item Listing Page
 *
 * This controller is responsible for rendering the main Pixlogix Items
 * listing page on the frontend.
 *
 * Example URL: /pixitems
 */

declare(strict_types=1);

namespace Pixlogix\Items\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Pixlogix\Items\Helper\Data as DataHelper;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Constructor.
     *
     * Initializes dependencies used in the controller.
     *
     * @param Context $context Action context instance.
     * @param PageFactory $resultPageFactory Page factory for rendering the view.
     * @param DataHelper $dataHelper Helper class for configuration and logic.
     * @param ForwardFactory $resultForwardFactory Used to forward to the 404 page.
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataHelper $dataHelper,
        ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelper = $dataHelper;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Execute action.
     *
     * Renders the Pixlogix Items listing page when the module is enabled.
     * If the module is disabled from admin configuration, it forwards
     * the request to Magento's default 404 (no-route) page.
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        // Check if the Pixlogix_Items module is enabled in configuration
        if (!$this->dataHelper->isModuleEnabled()) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->setModule('cms')->setController('noroute')->forward('index');
            return $resultForward;
        }

        // Module is enabled — create and render the listing page
        $resultPage = $this->resultPageFactory->create();

        // Set the dynamic page title
        $resultPage->getConfig()->getTitle()->set(__('Pixlogix Items'));

        return $resultPage;
    }
}
