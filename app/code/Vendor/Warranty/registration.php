<?php
declare(strict_types=1);

/**
 * Module registration file
 *
 * Registers the Vendor_Warranty module with Magento's ComponentRegistrar.
 *
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright  Copyright (c) 2025 Vendor
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Vendor_Warranty',
    __DIR__
);
