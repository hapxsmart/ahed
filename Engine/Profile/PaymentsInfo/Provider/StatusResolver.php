<?php
namespace Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider;

use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class StatusResolver
 * @package Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider
 */
class StatusResolver
{
    /**
     * @var array
     */
    private $typeToInfoStatusMap = [
        PaymentInterface::TYPE_PLANNED => ScheduledPaymentInfoInterface::PAYMENT_STATUS_SCHEDULED,
        PaymentInterface::TYPE_ACTUAL => ScheduledPaymentInfoInterface::PAYMENT_STATUS_SCHEDULED,
        PaymentInterface::TYPE_REATTEMPT => ScheduledPaymentInfoInterface::PAYMENT_STATUS_REATTEMPT,
        PaymentInterface::TYPE_OUTSTANDING => ScheduledPaymentInfoInterface::PAYMENT_STATUS_SCHEDULED,
        PaymentInterface::TYPE_LAST_PERIOD_HOLDER => ScheduledPaymentInfoInterface::PAYMENT_STATUS_LAST_PERIOD_HOLDER
    ];

    /**
     * Get info status corresponding to payment instance
     *
     * @param PaymentInterface $payment
     * @return string
     */
    public function getInfoStatus($payment)
    {
        $paymentType = $payment->getType();
        return isset($this->typeToInfoStatusMap[$paymentType])
            ? $this->typeToInfoStatusMap[$paymentType]
            : ScheduledPaymentInfoInterface::PAYMENT_STATUS_NO_PAYMENT;
    }
}
