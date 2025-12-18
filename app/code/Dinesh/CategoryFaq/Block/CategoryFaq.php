<?php
/**
 * @category  Dinesh
 * @package   Dinesh_CategoryFaq
 * @author    Dinesh
 * @copyright Copyright (c) 2025 Dinesh
 */
declare(strict_types=1);

namespace Dinesh\CategoryFaq\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Category;

/**
 * Category FAQ block
 *
 * Responsible for fetching FAQ-related data
 * from the current category and exposing it
 * to the category page template.
 */
class CategoryFaq extends Template
{
    /**
     * Core registry instance
     *
     * Used to retrieve the currently loaded category.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * CategoryFaq constructor
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get current category from registry
     *
     * @return Category|null
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Check whether category FAQ is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $category = $this->getCurrentCategory();
        return (bool) ($category?->getCategoryFaqEnabled());
    }

    /**
     * Get FAQ content assigned to the category
     *
     * @return string
     */
    public function getCategoryFaqContent(): string
    {
        $category = $this->getCurrentCategory();
        return (string) ($category?->getCategoryFaqContent() ?? '');
    }
}
