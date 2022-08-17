<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding\Reason;

use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Resolver
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding\Reason
 */
class Resolver
{
    /**
     * Outstanding reasons
     */
    const REASON_REACTIVATED = 1;
    const REASON_CYCLE_MISSING = 2;

    /**
     * Get outstanding reason
     *
     * @param PaymentInterface $payment
     * @return string
     */
    public function getReason($payment)
    {
        return $payment->getSchedule()->isReactivated()
            ? self::REASON_REACTIVATED
            : self::REASON_CYCLE_MISSING;
    }
}
