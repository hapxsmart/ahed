<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding\PaymentType;

use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Resolver
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding\PaymentType
 */
class Resolver
{
    /**
     * @var array
     */
    private $map = [
        PaymentInterface::STATUS_PLANNED => PaymentInterface::TYPE_PLANNED,
        PaymentInterface::STATUS_PENDING => PaymentInterface::TYPE_ACTUAL,
        PaymentInterface::STATUS_RETRYING => PaymentInterface::TYPE_REATTEMPT
    ];

    /**
     * Get recovered payment type
     *
     * @param PaymentInterface $payment
     * @return string|null
     */
    public function getPaymentType($payment)
    {
        $status = $payment->getPaymentStatus();
        return isset($this->map[$status])
            ? $this->map[$status]
            : null;
    }
}
