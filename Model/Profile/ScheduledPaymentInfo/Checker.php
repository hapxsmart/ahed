<?php
namespace Aheadworks\Sarp2\Model\Profile\ScheduledPaymentInfo;

use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;

class Checker
{
    /**
     * Check if payment info corresponds to the scheduled payment
     *
     * @param ScheduledPaymentInfoInterface $scheduledPaymentInfo
     * @return bool
     */
    public function hasScheduledPayment($scheduledPaymentInfo)
    {
        return $scheduledPaymentInfo->getPaymentStatus()
            != ScheduledPaymentInfoInterface::PAYMENT_STATUS_NO_PAYMENT;
    }
}
