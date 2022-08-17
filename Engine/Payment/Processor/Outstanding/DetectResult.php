<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class DetectResult
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding
 */
class DetectResult
{
    /**
     * @var PaymentInterface[]
     */
    private $todayPayments;

    /**
     * @var PaymentInterface[]
     */
    private $outstandingPayments;

    /**
     * @param array $todayPayments
     * @param array $outstandingPayments
     */
    public function __construct(
        array $todayPayments,
        array $outstandingPayments = []
    ) {
        $this->todayPayments = $todayPayments;
        $this->outstandingPayments = $outstandingPayments;
    }

    /**
     * Get today payments
     *
     * @return PaymentInterface[]
     */
    public function getTodayPayments()
    {
        return $this->todayPayments;
    }

    /**
     * Get outstanding payments
     *
     * @return PaymentInterface[]|Payment[]
     */
    public function getOutstandingPayments()
    {
        return $this->outstandingPayments;
    }
}
