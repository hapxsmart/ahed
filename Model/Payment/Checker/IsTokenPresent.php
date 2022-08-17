<?php
namespace Aheadworks\Sarp2\Model\Payment\Checker;

use Magento\Payment\Model\InfoInterface;
use Aheadworks\Sarp2\Model\Payment\Method\Data\DataAssignerInterface;

class IsTokenPresent
{
    /**
     * Check if SARP2 token is present in payment
     *
     * @param InfoInterface $payment
     * @return bool
     */
    public function check($payment)
    {
        return $payment && $payment->getAdditionalInformation(DataAssignerInterface::IS_SARP_TOKEN_ENABLED);
    }
}
