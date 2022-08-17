<?php
namespace Aheadworks\Sarp2\Engine\Notification\Scheduler;

use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\BillingFailedAdmin;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\CancelledSubscription;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\SubscriptionCreated;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\SchedulerInterface;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\BillingFailed;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\BillingSuccessful;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\UpcomingBilling;

/**
 * Class Pool
 * @package Aheadworks\Sarp2\Engine\Notification\Scheduler
 */
class Pool
{
    /**
     * @var array
     */
    private $schedulers = [
        NotificationInterface::TYPE_BILLING_SUCCESSFUL => BillingSuccessful::class,
        NotificationInterface::TYPE_BILLING_FAILED => BillingFailed::class,
        NotificationInterface::TYPE_UPCOMING_BILLING => UpcomingBilling::class,
        NotificationInterface::TYPE_CANCELLED_SUBSCRIPTION => CancelledSubscription::class,
        NotificationInterface::TYPE_CANCELLED_SUBSCRIPTION_ADMIN => CancelledSubscription::class,
        NotificationInterface::TYPE_CANCELLED_SUBSCRIPTION_CUSTOMER => CancelledSubscription::class,
        NotificationInterface::TYPE_OFFER_EXTEND_SUBSCRIPTION => CancelledSubscription::class,
        NotificationInterface::TYPE_BILLING_FAILED_ADMIN => BillingFailedAdmin::class,
        NotificationInterface::TYPE_SUBSCRIPTION_CREATED => SubscriptionCreated::class
    ];

    /**
     * @var SchedulerInterface[]
     */
    private $schedulerInstances = [];

    /**
     * @var Factory
     */
    private $schedulerFactory;

    /**
     * @param Factory $schedulerFactory
     * @param array $schedulers
     */
    public function __construct(
        Factory $schedulerFactory,
        array $schedulers = []
    ) {
        $this->schedulerFactory = $schedulerFactory;
        $this->schedulers = array_merge($this->schedulers, $schedulers);
    }

    /**
     * Get notification scheduler of specified type
     *
     * @param string $type
     * @return SchedulerInterface
     * @throws \Exception
     */
    public function getScheduler($type)
    {
        if (!isset($this->schedulerInstances[$type])) {
            if (!isset($this->schedulers[$type])) {
                throw new \Exception(sprintf('Unknown notification scheduler: %s requested', $type));
            }
            $this->schedulerInstances[$type] = $this->schedulerFactory->create($this->schedulers[$type]);
        }
        return $this->schedulerInstances[$type];
    }
}
