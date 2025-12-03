<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * Adminhtml Controller — Manage Items Grid
 *
 * This controller is responsible for rendering the "Manage Items" grid page
 * in the Magento Admin panel under the Pixlogix Items menu.
 */

declare(strict_types=1);

namespace Pixlogix\Items\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * Displays the main admin grid listing for Pixlogix Items.
 */
class Index extends Action
{
    /**
     * Authorization resource ID for ACL control.
     */
    public const ADMIN_RESOURCE = 'Pixlogix_Items::items';

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * Constructor.
     *
     * Initializes dependencies for rendering the admin page.
     *
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory Page factory for generating result pages.
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action.
     *
     * Loads and renders the "Manage Items" admin grid page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Create a new page result instance
        $resultPage = $this->resultPageFactory->create();

        // Set the active menu and page title
        $resultPage->setActiveMenu('Pixlogix_Items::items');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Items'));

        // Return the rendered page
        return $resultPage;
    }
}
