<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Logger;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * Custom Handler to define the log file path and minimum logging level.
 */
class Handler extends BaseHandler
{
    /**
     * @var int
     */
    protected $loggerType = MonologLogger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/log/warranty.log';
}
