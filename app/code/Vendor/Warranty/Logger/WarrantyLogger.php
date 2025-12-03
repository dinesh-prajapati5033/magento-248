<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Logger;

/**
 * Custom Logger class used for dependency injection.
 * The preference in di.xml maps this class to \Monolog\Logger.
 */
class WarrantyLogger extends \Monolog\Logger
{
    // This class is intentionally empty as its functionality
    // is inherited and configured via the Handler in di.xml.
}
