<?php
/**
 * Warranty Registration Repository Interface
 *
 * Defines the contract for CRUD operations on warranty registration entities.
 *
 * @category  Vendor
 * @package   Vendor_Warranty
 * author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */

declare(strict_types=1);

namespace Vendor\Warranty\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vendor\Warranty\Api\Data\WarrantyRegistrationInterface;

interface WarrantyRegistrationRepositoryInterface
{
    /**
     * Save warranty registration
     *
     * @param WarrantyRegistrationInterface $registration
     * @return WarrantyRegistrationInterface
     * @throws CouldNotSaveException
     */
    public function save(WarrantyRegistrationInterface $registration): WarrantyRegistrationInterface;

    /**
     * Retrieve warranty registration by ID
     *
     * @param int $id
     * @return WarrantyRegistrationInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): WarrantyRegistrationInterface;

    /**
     * Retrieve warranty registration by serial number
     *
     * @param string $serialNumber
     * @return WarrantyRegistrationInterface
     * @throws NoSuchEntityException
     */
    public function getBySerialNumber(string $serialNumber): WarrantyRegistrationInterface;

    /**
     * Retrieve a list of warranty registrations matching search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Delete warranty registration
     *
     * @param WarrantyRegistrationInterface $registration
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(WarrantyRegistrationInterface $registration): bool;

    /**
     * Delete warranty registration by ID
     *
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): bool;
}
