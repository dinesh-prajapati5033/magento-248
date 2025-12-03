<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025
 *
 * Helper — General Helper for Pixlogix Items
 *
 * This helper provides utility methods for module configuration access,
 * image dimension parsing, and placeholder image URL generation.
 */

declare(strict_types=1);

namespace Pixlogix\Items\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 *
 * Provides configuration and helper functions for the Pixlogix Items module.
 */
class Data extends AbstractHelper
{
    /**#@+
     * XML Path constants for configuration fields
     */
    public const XML_PATH_GENERAL_ENABLE    = 'pixlogix_items/general/enable';
    public const XML_PATH_LIST_IMAGE_SIZE   = 'pixlogix_items/general/list_image_size';
    public const XML_PATH_DETAIL_IMAGE_SIZE = 'pixlogix_items/general/detail_image_size';
    /**#@-*/

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Check if the Pixlogix Items module is enabled in configuration
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GENERAL_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get configured list page image size (Width x Height)
     *
     * @return string
     */
    public function getListImageSize(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_LIST_IMAGE_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get configured detail page image size (Width x Height)
     *
     * @return string
     */
    public function getDetailImageSize(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_DETAIL_IMAGE_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get list image width (parsed from configuration)
     *
     * @return int
     */
    public function getListImageWidth(): int
    {
        $size = explode('x', $this->getListImageSize());
        return isset($size[0]) ? (int)trim($size[0]) : 300; // default fallback width
    }

    /**
     * Get list image height (parsed from configuration)
     *
     * @return int
     */
    public function getListImageHeight(): int
    {
        $size = explode('x', $this->getListImageSize());
        return isset($size[1]) ? (int)trim($size[1]) : 200; // default fallback height
    }

    /**
     * Get detail image width (parsed from configuration)
     *
     * @return int
     */
    public function getDetailImageWidth(): int
    {
        $size = explode('x', $this->getDetailImageSize());
        return isset($size[0]) ? (int)trim($size[0]) : 1200; // default fallback width
    }

    /**
     * Get detail image height (parsed from configuration)
     *
     * @return int
     */
    public function getDetailImageHeight(): int
    {
        $size = explode('x', $this->getDetailImageSize());
        return isset($size[1]) ? (int)trim($size[1]) : 400; // default fallback height
    }

    /**
     * Get placeholder image URL for missing product/item images
     *
     * @return string
     */
    public function getPlaceholderImageUrl(): string
    {
        try {
            // Return Magento’s default placeholder from the media directory
            return $this->storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product/placeholder/image.jpg';
        } catch (\Exception $e) {
            // Gracefully handle any store resolution failure
            return '';
        }
    }
}
