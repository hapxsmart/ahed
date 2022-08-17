<?php
namespace Aheadworks\Sarp2\Model\Quote\Checker;

use Magento\Quote\Model\Quote;

/**
 * Class HasPaymentMethod
 * @package Aheadworks\Sarp2\Model\Quote\Checker
 */
class HasPaymentMethod
{
    /**
     * Check if quote has free payment method
     *
     * @param Quote $quote
     * @return bool
     */
    public function checkFreePayment($quote)
    {
        return $quote->getGrandTotal() <= 0
            && $quote->hasData('aw_sarp_allow_free_payment_method');
    }
}
