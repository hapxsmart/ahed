<?php
namespace Aheadworks\Sarp2\PaymentData;

use Aheadworks\Sarp2\PaymentData\Create\Result;

/**
 * Interface AdapterInterface
 * @package Aheadworks\Sarp2\PaymentData
 */
interface AdapterInterface
{
    /**
     * Create stored payment data instance
     *
     * @param PaymentInterface $payment
     * @return Result
     */
    public function create(PaymentInterface $payment);
}
