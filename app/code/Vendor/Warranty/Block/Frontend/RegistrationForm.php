<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Block\Frontend;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class RegistrationForm
 *
 * Frontend block for rendering the Warranty Registration form.
 * Provides helper methods for the template, such as form action URL.
 */
class RegistrationForm extends Template
{

    /**
     * Get the action URL for the registration form submission.
     *
     * @return string Form action URL (secured)
     */
    public function getFormAction(): string
    {
        return $this->getUrl('warranty/registration/save', ['_secure' => true]);
    }
}
