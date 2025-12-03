<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Model\GraphQL\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\CollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * GraphQL Resolver for fetching warranty registrations for the current logged-in customer.
 *
 * Supports:
 *  - Filtering by status, product SKU, serial number
 *  - Sorting by any field
 *  - Pagination
 *  - Cacheable for GraphQL/FPC
 *
 * Guests (not logged in) will receive an empty result set.
 */
class WarrantyRegistrationList implements ResolverInterface
{
    /**
     * Factory to create collections of WarrantyRegistration entities.
     *
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory Factory to create warranty registration collections
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Resolve GraphQL query `warrantyRegistrations`
     *
     * @param Field $field GraphQL field metadata
     * @param mixed $context GraphQL request context
     * @param ResolveInfo $info GraphQL query info
     * @param array|null $value Previous resolver value
     * @param array|null $args Query arguments (filter, pagination, sort)
     * @return array Items, total count, and pagination info
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        // Get authenticated customer ID from GraphQL context
        $customerId = null;
        if ($context instanceof ContextInterface) {
            $customerId = (int)$context->getUserId(); // null if guest
        }
        // Return empty results if the user is not logged in (guest)
        if (!$customerId) {
            return [
                'items' => [],
                'total_count' => 0,
                'page_info' => [
                    'page_size' => $args['pageSize'] ?? 20,
                    'current_page' => $args['currentPage'] ?? 1,
                    'total_pages' => 0
                ]
            ];
        }
        
        // Create collection and filter by the current customer
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);

        // Apply filters from GraphQL arguments
        if (!empty($args['filter'])) {
            $filter = $args['filter'];

            // Filter by status
            if (isset($filter['status'])) {
                $collection->addFieldToFilter('status', (int)$filter['status']);
            }

            // Filter by product SKU (partial match)
            if (!empty($filter['product_sku'])) {
                $collection->addFieldToFilter('product_sku', ['like' => '%' . $filter['product_sku'] . '%']);
            }

            // Filter by serial number (partial match)
            if (!empty($filter['serial_number'])) {
                $collection->addFieldToFilter('serial_number', ['like' => '%' . $filter['serial_number'] . '%']);
            }
        }

        // Apply sorting if provided
        if (!empty($args['sort'])) {
            $collection->addOrder($args['sort']['field'], $args['sort']['direction']);
        }

        // Apply pagination
        $pageSize = (int)($args['pageSize'] ?? 20);
        $currentPage = (int)($args['currentPage'] ?? 1);
        $collection->setPageSize($pageSize)->setCurPage($currentPage);

        // Prepare items array and add cacheable dependencies
        $items = [];
        foreach ($collection as $item) {
            $items[] = [
                'registration_id' => (int)$item->getRegistrationId(),
                'customer_id'     => (int)$item->getCustomerId(),
                'product_sku'     => $item->getProductSku(),
                'serial_number'   => $item->getSerialNumber(),
                'purchase_date'   => $item->getPurchaseDate(),
                'order_id'        => $item->getOrderId(),
                'proof_url'       => $item->getProofUrl(),
                'status'          => (int)$item->getStatus(),
                'created_at'      => $item->getCreatedAt(),
                'updated_at'      => $item->getUpdatedAt(),
            ];
        }

        // Calculate total count and total pages
        $totalCount = $collection->getSize();
        $totalPages = (int)ceil($totalCount / $pageSize);

        // Return final GraphQL response
        $result = [
            'items' => $items,
            'total_count' => $totalCount,
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => $totalPages
            ]
        ];

        // CRITICAL STEP: Include the filter data in the resolved output
        // This ensures the Identity class receives the data it needs via $resolvedData
        if (isset($args['filter'])) {
            $result['filters'] = $args['filter'];
        }
        return $result;
    }
}
