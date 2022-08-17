<?php
namespace Aheadworks\Sarp2\Engine\Notification;

use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;

interface SchedulerInterface
{
    /**
     * Schedule notification
     *
     * @param PaymentInterface $sourcePayment
     * @param array $additionalData
     * @return NotificationInterface[]
     */
    public function schedule(PaymentInterface $sourcePayment, array $additionalData);
}
