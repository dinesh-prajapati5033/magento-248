<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * UI Component column renderer for displaying customer group names
 * instead of IDs in the admin grid listing.
 */
declare(strict_types=1);

namespace Pixlogix\Items\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Api\GroupRepositoryInterface;

/**
 * Class CustomerGroup
 *
 * Responsible for rendering the Customer Group names in the admin grid
 * based on stored customer_group_ids values (comma-separated).
 */
class CustomerGroup extends Column
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * CustomerGroup constructor.
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        GroupRepositoryInterface $groupRepository,
        array $components = [],
        array $data = []
    ) {
        $this->groupRepository = $groupRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source for the grid column.
     *
     * Converts comma-separated customer group IDs into readable group names.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                // Ensure the column exists in the dataset
                if (array_key_exists('customer_group_ids', $item)) {
                    $groupIds = explode(',', (string)$item['customer_group_ids']);
                    $names = [];

                    foreach ($groupIds as $groupId) {
                        $groupId = (int)trim($groupId);

                        if ($groupId === 0) {
                            // Handle "NOT LOGGED IN" group explicitly
                            $names[] = __('NOT LOGGED IN');
                            continue;
                        }
                        $group = $this->groupRepository->getById($groupId);
                        $names[] = $group->getCode();
                    }

                    // Replace IDs with readable names or fallback to "N/A"
                    $item['customer_group_ids'] = !empty($names)
                        ? implode(', ', $names)
                        : __('N/A');
                }
            }
        }

        return $dataSource;
    }
}
