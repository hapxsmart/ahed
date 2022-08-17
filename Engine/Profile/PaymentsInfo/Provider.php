<?php
namespace Aheadworks\Sarp2\Engine\Profile\PaymentsInfo;

use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterfaceFactory;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider\StatusResolver;

/**
 * Class Provider
 * @package Aheadworks\Sarp2\Engine\Profile\PaymentsInfo
 */
class Provider implements ProviderInterface
{
    /**
     * @var ScheduledPaymentInfoInterfaceFactory
     */
    private $infoFactory;

    /**
     * @var PaymentsList
     */
    private $paymentList;

    /**
     * @var StatusResolver
     */
    private $statusResolver;

    /**
     * @param ScheduledPaymentInfoInterfaceFactory $infoFactory
     * @param PaymentsList $paymentList
     * @param StatusResolver $statusResolver
     */
    public function __construct(
        ScheduledPaymentInfoInterfaceFactory $infoFactory,
        PaymentsList $paymentList,
        StatusResolver $statusResolver
    ) {
        $this->infoFactory = $infoFactory;
        $this->paymentList = $paymentList;
        $this->statusResolver = $statusResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduledPaymentsInfo($profileId)
    {
        /** @var ScheduledPaymentInfoInterface $info */
        $info = $this->infoFactory->create();
        $payments = $this->paymentList->getLastScheduled($profileId);
        if (count($payments)) {
            /** @var PaymentInterface $payment */
            $payment = current($payments);
            $info->setPaymentStatus($this->statusResolver->getInfoStatus($payment))
                ->setPaymentPeriod($payment->getPaymentPeriod())
                ->setPaymentDate(
                    $payment->getType() == PaymentInterface::TYPE_REATTEMPT
                        ? $payment->getRetryAt()
                        : $payment->getScheduledAt()
                )
                ->setAmount($payment->getTotalScheduled())
                ->setBaseAmount($payment->getBaseTotalScheduled());
        } else {
            $info->setPaymentStatus(ScheduledPaymentInfoInterface::PAYMENT_STATUS_NO_PAYMENT);
        }
        return $info;
    }
}
