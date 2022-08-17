<?php
namespace Aheadworks\Sarp2\Engine\Notification;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubjectFactory;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Pool;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class Manager
{
    /**
     * @var Pool
     */
    private $schedulerPool;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var ResolveSubjectFactory
     */
    private $resolveSubjectFactory;

    /**
     * @var NotificationList
     */
    private $notificationList;

    /**
     * @param Pool $schedulerPool
     * @param Persistence $persistence
     * @param DataResolver $dataResolver
     * @param ResolveSubjectFactory $resolveSubjectFactory
     * @param NotificationList $notificationList
     */
    public function __construct(
        Pool $schedulerPool,
        Persistence $persistence,
        DataResolver $dataResolver,
        ResolveSubjectFactory $resolveSubjectFactory,
        NotificationList $notificationList
    ) {
        $this->schedulerPool = $schedulerPool;
        $this->persistence = $persistence;
        $this->dataResolver = $dataResolver;
        $this->resolveSubjectFactory = $resolveSubjectFactory;
        $this->notificationList = $notificationList;
    }

    /**
     * Schedule notification of specified type
     *
     * @param string $type
     * @param PaymentInterface $sourcePayment
     * @param array $additionalData
     * @return NotificationInterface[]
     * @throws \Exception
     */
    public function schedule(string $type, PaymentInterface $sourcePayment, $additionalData = [])
    {
        return $this->schedulerPool->getScheduler($type)
            ->schedule($sourcePayment, $additionalData);
    }

    /**
     * Schedule notification of specified type
     *
     * @param string $type
     * @param PaymentInterface[] $sourcePayments
     * @param array $additionalData
     * @return Notification[]|null
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function reschedule($type, $sourcePayments, $additionalData = [])
    {
        $notifications = [];
        foreach ($sourcePayments as $sourcePayment) {
            $this->persistence->massDelete(
                $this->notificationList->getReadyForSendNotificationsForProfile($sourcePayment->getProfileId())
            );
            $notifications = array_merge($notifications, $this->schedule($type, $sourcePayment, $additionalData));
        }
        return $notifications;
    }

    /**
     * Update notification data
     *
     * @param NotificationInterface $notification
     * @param array $subjectData
     * @return void
     */
    public function updateNotificationData($notification, $subjectData)
    {
        /** @var ResolveSubject $resolveSubject */
        $resolveSubject = $this->resolveSubjectFactory->create($subjectData);
        $notification->setNotificationData($this->dataResolver->resolve($resolveSubject));

        try {
            /** @var Notification $notification */
            $this->persistence->save($notification);
        } catch (CouldNotSaveException $exception) {
        }
    }
}
