<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned;

use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Copy
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned
 */
class Copy
{
    /**
     * Copy to single payment object
     *
     * @param PaymentInterface $source
     * @param PaymentInterface $destination
     * @return PaymentInterface
     */
    public function copyToSingle($source, $destination)
    {
        $destination->setScheduleId($source->getScheduleId())
            ->setScheduledAt($source->getScheduledAt())
            ->setPaymentPeriod($source->getPaymentPeriod())
            ->setTotalScheduled($source->getTotalScheduled())
            ->setBaseTotalScheduled($source->getBaseTotalScheduled())
            ->setPaymentData($source->getPaymentData());
        return $destination;
    }

    /**
     * Copy to bundled payment object
     *
     * @param PaymentInterface $source
     * @param PaymentInterface $destination
     * @return PaymentInterface
     */
    public function copyToBundled($source, $destination)
    {
        $destination->setScheduleId($source->getScheduleId())
            ->setScheduledAt($source->getScheduledAt())
            ->setPaymentPeriod(null)
            ->setPaymentData($source->getPaymentData());
        return $destination;
    }
}
