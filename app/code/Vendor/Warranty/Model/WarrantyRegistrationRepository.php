<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */

declare(strict_types=1);

namespace Vendor\Warranty\Model;

use Vendor\Warranty\Api\WarrantyRegistrationRepositoryInterface;
use Vendor\Warranty\Api\Data\WarrantyRegistrationInterface;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration as ResourceModel;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class WarrantyRegistrationRepository implements WarrantyRegistrationRepositoryInterface
{
    /** @var ResourceModel Resource model for CRUD operations */
    private ResourceModel $resource;

    /** @var CollectionFactory Factory to create WarrantyRegistration collections */
    private CollectionFactory $collectionFactory;

    /** @var SearchResultsInterfaceFactory Factory to create SearchResults objects */
    private SearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * Constructor
     *
     * @param ResourceModel $resource
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resource,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Save a WarrantyRegistration entity
     *
     * @param WarrantyRegistrationInterface $registration
     * @return WarrantyRegistrationInterface
     * @throws CouldNotSaveException
     */
    public function save(WarrantyRegistrationInterface $registration): WarrantyRegistrationInterface
    {
        try {
            $this->resource->save($registration);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save warranty registration: %1', $e->getMessage()));
        }

        return $registration;
    }

    /**
     * Retrieve a WarrantyRegistration entity by ID
     *
     * @param int $id
     * @return WarrantyRegistrationInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): WarrantyRegistrationInterface
    {
        $registration = $this->collectionFactory->create()->getItemById($id);
        if (!$registration) {
            throw new NoSuchEntityException(__('Warranty registration with ID "%1" does not exist.', $id));
        }
        return $registration;
    }

    /**
     * Retrieve a WarrantyRegistration entity by serial number
     *
     * @param string $serialNumber
     * @return WarrantyRegistrationInterface
     * @throws NoSuchEntityException
     */
    public function getBySerialNumber(string $serialNumber): WarrantyRegistrationInterface
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('serial_number', $serialNumber)
            ->setPageSize(1);

        $item = $collection->getFirstItem();
        if (!$item || !$item->getId()) {
            throw new NoSuchEntityException(__(
                'Warranty registration with serial number "%1" does not exist.',
                $serialNumber
            ));
        }

        return $item;
    }

    /**
     * Retrieve a list of WarrantyRegistration entities based on SearchCriteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): \Magento\Framework\Api\SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();

        // Apply filters from SearchCriteria
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        // Apply sorting
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $collection->addOrder(
                $sortOrder->getField(),
                $sortOrder->getDirection()
            );
        }

        // Apply pagination
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        // Prepare SearchResults object
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Delete a WarrantyRegistration entity
     *
     * @param WarrantyRegistrationInterface $registration
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(WarrantyRegistrationInterface $registration): bool
    {
        try {
            $this->resource->delete($registration);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete warranty registration: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * Delete a WarrantyRegistration entity by ID
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id): bool
    {
        $registration = $this->getById($id);
        return $this->delete($registration);
    }
}
