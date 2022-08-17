<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface as NativePaymentDataObjectInterface;

/**
 * Interface PaymentDataObjectInterface
 */
interface PaymentDataObjectInterface extends NativePaymentDataObjectInterface
{
    /**
     * Retrieve profile
     *
     * @return ProfileInterface
     */
    public function getProfile();

    /**
     * Retrieve payment
     *
     * @return SamplerInfoInterface
     */
    public function getPayment();
}
