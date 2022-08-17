<?php
namespace Aheadworks\Sarp2\Model\Payment\Checks;

use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class Recurring implements SpecificationInterface
{
    /**
     * Check whether payment method is applicable to quote
     *
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        $code = $paymentMethod->getCode();

        return !preg_match('/\_recurring$/i', $code);
    }
}
