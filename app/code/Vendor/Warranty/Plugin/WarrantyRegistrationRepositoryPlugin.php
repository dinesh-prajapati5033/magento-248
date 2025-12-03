<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Plugin;

use Vendor\Warranty\Model\WarrantyRegistrationRepository;
use Vendor\Warranty\Api\Data\WarrantyRegistrationInterface;

/**
 * Plugin for WarrantyRegistrationRepository
 *
 * Before saving a registration:
 *  - Trim whitespace from serial number
 *  - Convert serial number to uppercase
 */
class WarrantyRegistrationRepositoryPlugin
{
    /**
     * Before save plugin
     *
     * @param WarrantyRegistrationRepository $subject
     * @param WarrantyRegistrationInterface $registration
     * @return array Modified arguments
     */
    public function beforeSave(
        WarrantyRegistrationRepository $subject,
        WarrantyRegistrationInterface $registration
    ): array {
        $serial = $registration->getSerialNumber();
        if ($serial) {
            $registration->setSerialNumber(strtoupper(trim($serial)));
        }

        return [$registration]; // must return array of arguments for before plugin
    }
}
