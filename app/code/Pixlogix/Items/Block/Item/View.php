<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025
 *
 * Frontend Block — Item Detail View Block
 *
 * This block handles fetching and rendering a single item's details
 * on the frontend item view page. It retrieves the current item from
 * the registry and provides helper methods for image, dimensions, and
 * module configuration checks.
 */

declare(strict_types=1);

namespace Pixlogix\Items\Block\Item;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Pixlogix\Items\Helper\Data as ItemsHelper;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Class View
 *
 * Provides data and helper functions for rendering the item detail page.
 */
class View extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ItemsHelper
     */
    protected $helper;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param ItemsHelper $helper
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        ItemsHelper $helper,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * Check if the module is enabled via admin configuration
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->helper->isModuleEnabled();
    }

    /**
     * Retrieve the current item from the registry
     *
     * @return \Pixlogix\Items\Model\Item|null
     */
    public function getItem()
    {
        // 'current_item' is registered by controller before rendering the page
        return $this->registry->registry('current_item');
    }

    /**
     * Get configured detail image width
     *
     * @return int
     */
    public function getDetailImageWidth(): int
    {
        return (int)$this->helper->getDetailImageWidth();
    }

    /**
     * Get configured detail image height
     *
     * @return int
     */
    public function getDetailImageHeight(): int
    {
        return (int)$this->helper->getDetailImageHeight();
    }

    /**
     * Retrieve the item's image URL
     *
     * @return string
     */
    public function getImageUrl(): string
    {
        $item = $this->getItem();

        if ($item && $item->getImage()) {
            // Build full media URL for item image
            return $this->getUrl('media') . 'items/' . ltrim($item->getImage(), '/');
        }

        // Return default placeholder if no image found
        return $this->getViewFileUrl('Pixlogix_Items::images/placeholder.jpg');
    }

    /**
     * Get processed WYSIWYG content (for fields like `content` or `short_content`)
     *
     * @param string $rawContent
     * @return string
     */
    public function getProcessedContent(?string $rawContent): string
    {
        if (empty($rawContent)) {
            return '';
        }

        try {
            // Use Magento's CMS filter to process WYSIWYG markup
            return $this->filterProvider->getPageFilter()->filter($rawContent);
        } catch (\Exception $e) {
            // Log or handle silently in case of parsing issues
            return $rawContent;
        }
    }
}
