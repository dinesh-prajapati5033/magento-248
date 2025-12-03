<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */

declare(strict_types=1);

namespace Vendor\Warranty\Api\Data;

interface WarrantyRegistrationInterface
{
    /**
     * Constants for keys of data array
     */
    public const REGISTRATION_ID = 'registration_id';
    public const CUSTOMER_ID     = 'customer_id';
    public const ORDER_ID        = 'order_id';
    public const PRODUCT_SKU     = 'product_sku';
    public const SERIAL_NUMBER   = 'serial_number';
    public const PURCHASE_DATE   = 'purchase_date';
    public const PROOF_URL       = 'proof_url';
    public const STATUS          = 'status';
    public const CREATED_AT      = 'created_at';
    public const UPDATED_AT      = 'updated_at';

    /**
     * Get registration ID
     *
     * @return int|null
     */
    public function getRegistrationId(): ?int;

    /**
     * Set registration ID
     *
     * @param int|null $id
     * @return $this
     */
    public function setRegistrationId(?int $id): self;

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * Set customer ID
     *
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId(?int $customerId): self;

    /**
     * Get order ID
     *
     * @return string|null
     */
    public function getOrderId(): ?string;

    /**
     * Set order ID
     *
     * @param string|null $orderId
     * @return $this
     */
    public function setOrderId(?string $orderId): self;

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku(): string;

    /**
     * Set product SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setProductSku(string $sku): self;

    /**
     * Get product serial number
     *
     * @return string
     */
    public function getSerialNumber(): string;

    /**
     * Set product serial number
     *
     * @param string $serialNumber
     * @return $this
     */
    public function setSerialNumber(string $serialNumber): self;

    /**
     * Get purchase date (YYYY-MM-DD)
     *
     * @return string
     */
    public function getPurchaseDate(): string;

    /**
     * Set purchase date
     *
     * @param string $purchaseDate
     * @return $this
     */
    public function setPurchaseDate(string $purchaseDate): self;

    /**
     * Get proof of purchase URL
     *
     * @return string|null
     */
    public function getProofUrl(): ?string;

    /**
     * Set proof of purchase URL
     *
     * @param string|null $proofUrl
     * @return $this
     */
    public function setProofUrl(?string $proofUrl): self;

    /**
     * Get registration status (0=pending,1=approved,2=rejected)
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set registration status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self;

    /**
     * Get creation timestamp
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Get last updated timestamp
     *
     * @return string
     */
    public function getUpdatedAt(): string;
}
