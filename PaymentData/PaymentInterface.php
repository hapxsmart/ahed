<?php
namespace Aheadworks\Sarp2\PaymentData;

use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Model\Quote;

/**
 * Interface PaymentInterface
 * @package Aheadworks\Sarp2\PaymentData
 */
interface PaymentInterface
{
    /**
     * Get payment info
     *
     * @return InfoInterface
     */
    public function getPaymentInfo();

    /**
     * Get quote
     *
     * @return Quote
     */
    public function getQuote();
}
