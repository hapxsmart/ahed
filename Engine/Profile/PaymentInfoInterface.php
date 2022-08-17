<?php
namespace Aheadworks\Sarp2\Engine\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Interface PaymentInfoInterface
 * @package Aheadworks\Sarp2\Engine\Profile
 */
interface PaymentInfoInterface
{
    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile();

    /**
     * Get payment period
     *
     * @return string
     */
    public function getPaymentPeriod();
}
