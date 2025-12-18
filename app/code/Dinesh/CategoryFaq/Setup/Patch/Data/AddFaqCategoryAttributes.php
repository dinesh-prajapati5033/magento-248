<?php
/**
 * @category  Dinesh
 * @package   Dinesh_CategoryFaq
 * @author    Dinesh
 * @copyright Copyright (c) 2025 Dinesh
 */
declare(strict_types=1);

namespace Dinesh\CategoryFaq\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Category;

/**
 * Data patch to add 'category_faq_enabled' and 'category_faq_content' category attributes.
 */
class AddFaqCategoryAttributes implements DataPatchInterface
{
    private EavSetupFactory $eavSetupFactory;
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply the data patch
     */
    public function apply(): void
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $groupName = 'FAQ Content';

        // 1. category_faq_enabled (Yes/No)
        $eavSetup->addAttribute(Category::ENTITY, 'category_faq_enabled', [
            'type'         => 'int',
            'label'        => 'Enable FAQ',
            'input'        => 'select',
            'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'required'     => false,
            'sort_order'   => 10,
            'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'group'        => $groupName,
            'default'      => '0',
            'visible'      => true,
            'user_defined' => true,
        ]);

        // 2. category_faq_content (WYSIWYG/Textarea)
        $eavSetup->addAttribute(Category::ENTITY, 'category_faq_content', [
            'type'                      => 'text',
            'label'                     => 'FAQ Content',
            'input'                     => 'textarea',
            'required'                  => false,
            'sort_order'                => 20,
            'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'group'                     => $groupName,
            'wysiwyg_enabled'           => true,
            'is_html_allowed_on_front'  => true,
            'visible'                   => true,
            'user_defined'              => true,
        ]);
    }

    /**
     * Get array of patches that should be executed before this patch.
     * @return array<string>
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (If the patch was renamed, list the previous names here)
     * @return array<string>
     */
    public function getAliases(): array
    {
        return [];
    }
}