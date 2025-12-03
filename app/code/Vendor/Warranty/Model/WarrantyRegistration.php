<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */

declare(strict_types=1);

namespace Vendor\Warranty\Model;

use Magento\Framework\Model\AbstractModel;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration as ResourceModel;
use Vendor\Warranty\Api\Data\WarrantyRegistrationInterface;

class WarrantyRegistration extends AbstractModel implements WarrantyRegistrationInterface
{
    /**
     * Initialize resource model
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get registration ID
     *
     * @return int|null
     */
    public function getRegistrationId(): ?int
    {
        $value = $this->getData(self::REGISTRATION_ID);
        return $value !== null ? (int)$value : null;
    }

    /**
     * Set registration ID
     *
     * @param int|null $id
     * @return $this
     */
    public function setRegistrationId(?int $id): self
    {
        $this->setData(self::REGISTRATION_ID, $id);
        return $this;
    }

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        $value = $this->getData(self::CUSTOMER_ID);
        return $value !== null ? (int)$value : null;
    }

    /**
     * Set customer ID
     *
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId(?int $customerId): self
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
        return $this;
    }

    /**
     * Get order ID
     *
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set order ID
     *
     * @param string|null $orderId
     * @return $this
     */
    public function setOrderId(?string $orderId): self
    {
        $this->setData(self::ORDER_ID, $orderId);
        return $this;
    }

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku(): string
    {
        return (string)$this->getData(self::PRODUCT_SKU);
    }

    /**
     * Set product SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setProductSku(string $sku): self
    {
        $this->setData(self::PRODUCT_SKU, $sku);
        return $this;
    }

    /**
     * Get product serial number
     *
     * @return string
     */
    public function getSerialNumber(): string
    {
        return (string)$this->getData(self::SERIAL_NUMBER);
    }

    /**
     * Set product serial number
     *
     * @param string $serialNumber
     * @return $this
     */
    public function setSerialNumber(string $serialNumber): self
    {
        $this->setData(self::SERIAL_NUMBER, $serialNumber);
        return $this;
    }

    /**
     * Get purchase date
     *
     * @return string
     */
    public function getPurchaseDate(): string
    {
        return (string)$this->getData(self::PURCHASE_DATE);
    }

    /**
     * Set purchase date
     *
     * @param string $purchaseDate
     * @return $this
     */
    public function setPurchaseDate(string $purchaseDate): self
    {
        $this->setData(self::PURCHASE_DATE, $purchaseDate);
        return $this;
    }

    /**
     * Get proof of purchase URL
     *
     * @return string|null
     */
    public function getProofUrl(): ?string
    {
        return $this->getData(self::PROOF_URL);
    }

    /**
     * Set proof of purchase URL
     *
     * @param string|null $proofUrl
     * @return $this
     */
    public function setProofUrl(?string $proofUrl): self
    {
        $this->setData(self::PROOF_URL, $proofUrl);
        return $this;
    }

    /**
     * Get registration status
     *
     * 0 = Pending, 1 = Approved, 2 = Rejected
     *
     * @return int
     */
    public function getStatus(): int
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * Set registration status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * Get created at timestamp
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    /**
     * Get updated at timestamp
     *
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return (string)$this->getData(self::UPDATED_AT);
    }
}
