<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Payment\Model\Method\Free;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;

class ToOrderPayment
{
    /**
     * @var OrderPaymentInterfaceFactory
     */
    private $orderPaymentFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var string[]
     */
    private $skipSubstituteForPayment;

    /**
     * @param OrderPaymentInterfaceFactory $orderPaymentFactory
     * @param Copy $objectCopyService
     * @param array $skipSubstituteForPayment
     */
    public function __construct(
        OrderPaymentInterfaceFactory $orderPaymentFactory,
        Copy $objectCopyService,
        array $skipSubstituteForPayment = ['aw_sarp2_free_payment']
    ) {
        $this->orderPaymentFactory = $orderPaymentFactory;
        $this->objectCopyService = $objectCopyService;
        $this->skipSubstituteForPayment = array_values($skipSubstituteForPayment);
    }

    /**
     * Convert profile to order payment
     *
     * @param ProfileInterface $profile
     * @param string|null $paymentPeriod
     * @return OrderPaymentInterface
     */
    public function convert(ProfileInterface $profile, $paymentPeriod = null)
    {
        $profilePaymentMethod = $profile->getPaymentMethod();
        /** @var OrderPaymentInterface $orderPayment */
        $orderPayment = $this->orderPaymentFactory->create();
        if ($paymentPeriod == PaymentInterface::PERIOD_TRIAL
            && $profile->getTrialGrandTotal() + $profile->getBaseTrialShippingAmount() < 0.0001
            && !in_array($profilePaymentMethod, $this->skipSubstituteForPayment)
        ) {
            $paymentMethod = Free::PAYMENT_METHOD_FREE_CODE;
        } else {
            $paymentMethod = 'aw_sarp_' . $profile->getPaymentMethod() . '_recurring';
            $orderPayment->setAdditionalInformation([
                ProfileInterface::PAYMENT_TOKEN_ID => $profile->getPaymentTokenId(),
                ProfileInterface::PROFILE_ID => $profile->getProfileId()
            ]);
        }
        $orderPayment->setMethod($paymentMethod);

        return $orderPayment;
    }
}
