<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Cron;

use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\CollectionFactory;
use Vendor\Warranty\Api\WarrantyRegistrationRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Cron job to automatically reject pending warranty registrations
 * that are older than the configured number of days.
 *
 * Configuration path: warranty/general/pending_expiry_days (default 90)
 */
class RejectPendingRegistrations
{
    /** @var CollectionFactory Factory for warranty registration collections */
    private CollectionFactory $collectionFactory;

    /** @var WarrantyRegistrationRepositoryInterface Repository for CRUD operations */
    private WarrantyRegistrationRepositoryInterface $repository;

    /** @var ScopeConfigInterface To fetch system configuration values */
    private ScopeConfigInterface $scopeConfig;

    /** @var LoggerInterface Logger for cron execution */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param WarrantyRegistrationRepositoryInterface $repository
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        WarrantyRegistrationRepositoryInterface $repository,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Execute the cron job
     */
    public function execute(): void
    {
        try {
            // Get pending expiry days from system config, default 90
            $expiryDays = (int) $this->scopeConfig->getValue(
                'warranty/general/pending_expiry_days',
                ScopeInterface::SCOPE_STORE
            ) ?: 90;

            // Calculate cutoff date
            $expiryDate = (new \DateTime())->modify("-{$expiryDays} days")->format('Y-m-d H:i:s');

            // Fetch pending registrations older than expiry date
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('status', 0); // pending
            $collection->addFieldToFilter('created_at', ['lt' => $expiryDate]);

            // Update status and save
            foreach ($collection as $registration) {
                $registration->setStatus(2); // 2 = Rejected
                $this->repository->save($registration);
            }

            // Log success summary
            $this->logger->info(sprintf(
                'Cron executed: %d pending warranty registration(s) auto-rejected older than %d days.',
                $collection->getSize(),
                $expiryDays
            ));
        } catch (\Exception $e) {
            // Log any errors
            $this->logger->error('Error in RejectPendingRegistrations cron: ' . $e->getMessage());
        }
    }
}
