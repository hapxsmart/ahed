<?php
namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Engine\Exception\ScheduledPaymentException;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultInterface;

/**
 * Interface ActionInterface
 * @package Aheadworks\Sarp2\Engine\Payment
 */
interface ActionInterface
{
    /**
     * Payment action types
     */
    const TYPE_SINGLE = 'single';
    const TYPE_BUNDLED = 'bundled';

    /**
     * Perform pay action
     *
     * @param PaymentInterface $payment
     * @return ResultInterface
     * @throws ScheduledPaymentException
     */
    public function pay(PaymentInterface $payment);
}
