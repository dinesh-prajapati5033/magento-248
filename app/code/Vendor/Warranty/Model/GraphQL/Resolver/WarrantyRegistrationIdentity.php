<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Model\GraphQL\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity class for caching warranty registration GraphQL queries.
 *
 * Generates cache tags for:
 *  - Single registration queries
 *  - Filtered list queries (by product_sku, serial_number, status)
 */
class WarrantyRegistrationIdentity implements IdentityInterface
{
    /**
     * Return cache identities for GraphQL and FPC.
     *
     * @param array $resolvedData Resolver arguments.
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $tags = [];

        /**
         * Case 1: Single registration query
         */
        if (!empty($resolvedData['registration_id'])) {
            $tags[] = 'warranty_registration_' . (int)$resolvedData['registration_id'];
        }

        /**
         * Case 2: Filtered list query (by product_sku, serial_number, or status)
         */
        if (!empty($resolvedData['filters']) && is_array($resolvedData['filters'])) {
            $filters = $resolvedData['filters'];

            $sku = $filters['product_sku'] ?? null;
            $serial = $filters['serial_number'] ?? null;
            $status = $filters['status'] ?? null;

            // Generate a readable key (instead of random md5)
            $filterKeyParts = [];
            if ($sku) {
                $filterKeyParts[] = 'sku_' . strtolower($sku);
            }
            if ($serial) {
                $filterKeyParts[] = 'serial_' . strtolower($serial);
            }
            if ($status !== null) {
                $filterKeyParts[] = 'status_' . (int)$status;
            }

            if (!empty($filterKeyParts)) {
                $tags[] = 'warranty_registration_list_' . implode('_', $filterKeyParts);
            }
        }

        /**
         *  Case 3: Fallback general tag
         */
        if (empty($tags)) {
            $tags[] = 'warranty_registration_list';
        }

        return $tags;
    }
}
