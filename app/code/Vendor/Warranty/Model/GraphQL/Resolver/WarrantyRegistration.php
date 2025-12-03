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
use Vendor\Warranty\Api\WarrantyRegistrationRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\GraphQl\Model\Query\ContextInterface;

class WarrantyRegistration implements ResolverInterface
{
    /**
     * Repository for performing CRUD operations on WarrantyRegistration entities.
     *
     * @var WarrantyRegistrationRepositoryInterface
     */
    private WarrantyRegistrationRepositoryInterface $repository;

    /**
     * Constructor
     *
     * @param WarrantyRegistrationRepositoryInterface $repository Repository for WarrantyRegistration entities
     */
    public function __construct(
        WarrantyRegistrationRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * Resolve GraphQL query for a single warranty registration
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlAuthorizationException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        // Ensure ID is provided
        if (empty($args['id'])) {
            throw new \InvalidArgumentException(__('ID is required.'));
        }

        // Fetch registration
        $registration = $this->repository->getById((int)$args['id']);

        // Get authenticated customer ID from GraphQL context
        $customerId = null;
        if ($context instanceof ContextInterface) {
            $customerId = $context->getUserId(); // null if guest
        }

        // Authorization: only owner can access
        if ($customerId === null || $customerId !== $registration->getCustomerId()) {
            throw new GraphQlAuthorizationException(__('You are not allowed to access this registration.'));
        }

        return [
            'registration_id' => $registration->getRegistrationId(),
            'customer_id'     => $registration->getCustomerId(),
            'product_sku'     => $registration->getProductSku(),
            'serial_number'   => $registration->getSerialNumber(),
            'purchase_date'   => $registration->getPurchaseDate(),
            'order_id'        => $registration->getOrderId(),
            'proof_url'       => $registration->getProofUrl(),
            'status'          => $registration->getStatus(),
            'created_at'      => $registration->getCreatedAt(),
            'updated_at'      => $registration->getUpdatedAt(),
        ];
    }
}
