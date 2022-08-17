<?php
namespace Aheadworks\Sarp2\Engine\Payment\Checker;

use Aheadworks\Sarp2\Engine\Payment\Schedule\Checker;
use Aheadworks\Sarp2\Engine\PaymentInterface;

class IsFreeTrial
{
    /**
     * @var Checker
     */
    private $checker;

    /**
     * @param Checker $checker
     */
    public function __construct(
        Checker $checker
    ) {
        $this->checker = $checker;
    }

    /**
     * Check
     * @param PaymentInterface $payment
     * @return bool
     */
    public function check(PaymentInterface $payment)
    {
        $schedule = $payment->getSchedule();
        return $payment->getTotalScheduled() == 0 && $this->checker->isTrialNextPayment($schedule);
    }
}
