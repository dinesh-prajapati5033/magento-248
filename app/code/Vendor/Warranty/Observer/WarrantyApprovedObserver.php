<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Vendor\Warranty\Logger\WarrantyLogger;

/**
 * Class WarrantyApprovedObserver
 *
 * Observes the `vendor_warranty_approved` event.
 * Logs the registration ID and timestamp to the custom log file via DI.
 */
class WarrantyApprovedObserver implements ObserverInterface
{
    /**
     * Custom Logger instance configured for var/log/warranty.log
     *
     * @var \Vendor\Warranty\Logger\WarrantyLogger
     */
    private WarrantyLogger $customLogger;

    /**
     * Constructor
     *
     * @param WarrantyLogger $customLogger Injected custom logger
     */
    public function __construct(WarrantyLogger $customLogger)
    {
        $this->customLogger = $customLogger;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer Event observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Null checks should be outside of the logging logic
        $registration = $observer->getData('registration');

        if ($registration && $registration->getId()) {
            $registrationId = $registration->getId();
            $timestamp = date('Y-m-d H:i:s');

            // Use the injected custom logger to write the message
            $this->customLogger->info(sprintf(
                'Warranty registration approved. ID: %s, Timestamp: %s',
                $registrationId,
                $timestamp
            ));
        }
    }
}
