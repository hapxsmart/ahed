<?php
namespace Aheadworks\Sarp2\Engine\Notification\Offer\Extend;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Scheduler\DataResolver;
use Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Scheduler\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Scheduler\DataResolver\ResolveSubjectFactory;
use Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Scheduler\ScheduledAtDateResolver;
use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Aheadworks\Sarp2\Engine\NotificationFactory;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;

class Scheduler
{
    /**
     * @var NotificationFactory
     */
    private $notificationFactory;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var ScheduledAtDateResolver
     */
    private $scheduledAtResolver;

    /**
     * @var ResolveSubjectFactory
     */
    private $resolveSubjectFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @param NotificationFactory $notificationFactory
     * @param Persistence $persistence
     * @param DataResolver $dataResolver
     * @param ResolveSubjectFactory $resolveSubjectFactory
     * @param DateTime $dateTime
     * @param CoreDate $coreDate
     * @param ScheduledAtDateResolver $scheduledAtResolver
     */
    public function __construct(
        NotificationFactory $notificationFactory,
        Persistence $persistence,
        DataResolver $dataResolver,
        ResolveSubjectFactory $resolveSubjectFactory,
        DateTime $dateTime,
        CoreDate $coreDate,
        ScheduledAtDateResolver $scheduledAtResolver
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->persistence = $persistence;
        $this->dataResolver = $dataResolver;
        $this->resolveSubjectFactory = $resolveSubjectFactory;
        $this->dateTime = $dateTime;
        $this->coreDate = $coreDate;
        $this->scheduledAtResolver = $scheduledAtResolver;
    }

    /**
     * Schedule today offer notification
     *
     * @param ProfileInterface $profile
     * @return Notification|null
     * @throws \Exception
     */
    public function scheduleIfToday($profile)
    {
        $scheduledAt = $this->dateTime->formatDate(
            $this->scheduledAtResolver->getScheduledAtDate($profile),
            false
        );
        $today = $this->dateTime->formatDate(true, false);

        if ($today == $scheduledAt) {
            return $this->createNotification($profile, $scheduledAt);
        }

        return null;
    }

    /**
     * Schedule offer notification
     *
     * @param ProfileInterface $profile
     * @return Notification|null
     * @throws \Exception
     */
    public function scheduleIfEarlierToday($profile)
    {
        $scheduledAt = $this->dateTime->formatDate(
            $this->scheduledAtResolver->getScheduledAtDate($profile),
            false
        );

        $todayTm = $this->coreDate->gmtTimestamp($this->dateTime->formatDate(true, false));
        $scheduledAtTm = $this->coreDate->gmtTimestamp($scheduledAt);

        if ($todayTm >= $scheduledAtTm) {
            return $this->createNotification($profile, $scheduledAt);
        }

        return null;
    }

    /**
     * @param ProfileInterface $profile
     * @param string $scheduledAt
     * @return Notification|null
     */
    private function createNotification($profile, $scheduledAt)
    {
        /** @var Notification $notification */
        $notification = $this->notificationFactory->create();
        $notification->setType(NotificationInterface::TYPE_OFFER_EXTEND_SUBSCRIPTION)
            ->setStatus(NotificationInterface::STATUS_READY)
            ->setEmail($profile->getCustomerEmail())
            ->setName($profile->getCustomerFullname())
            ->setScheduledAt($scheduledAt)
            ->setStoreId($profile->getStoreId())
            ->setProfileId($profile->getProfileId());

        try {
            /** @var ResolveSubject $resolveSubject */
            $resolveSubject = $this->resolveSubjectFactory->create(
                ['profile' => $profile]
            );
            $notification->setNotificationData($this->dataResolver->resolve($resolveSubject));

            $this->persistence->save($notification);
            return $notification;
        } catch (\Exception $exception) {
        }

        return null;
    }
}
