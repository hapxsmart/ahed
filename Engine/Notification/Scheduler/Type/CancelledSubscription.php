<?php
namespace Aheadworks\Sarp2\Engine\Notification\Scheduler\Type;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationFactory;
use Aheadworks\Sarp2\Engine\Notification\SchedulerInterface;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\DataResolver;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubjectFactory;
use Aheadworks\Sarp2\Engine\Notification\Locator\Registry;
use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime;

class CancelledSubscription implements SchedulerInterface
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
     * @var ResolveSubjectFactory
     */
    private $resolveSubjectFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param NotificationFactory $notificationFactory
     * @param Persistence $persistence
     * @param DataResolver $dataResolver
     * @param ResolveSubjectFactory $resolveSubjectFactory
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param UserContextInterface $userContext
     */
    public function __construct(
        NotificationFactory $notificationFactory,
        Persistence $persistence,
        DataResolver $dataResolver,
        ResolveSubjectFactory $resolveSubjectFactory,
        Registry $registry,
        DateTime $dateTime,
        UserContextInterface $userContext
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->persistence = $persistence;
        $this->dataResolver = $dataResolver;
        $this->resolveSubjectFactory = $resolveSubjectFactory;
        $this->registry = $registry;
        $this->dateTime = $dateTime;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(PaymentInterface $sourcePayment, array $additionalData)
    {
        $profile = $sourcePayment->getProfile();

        $notificationType = $this->resolveNotificationType();

        /** @var Notification $notification */
        $notification = $this->notificationFactory->create();
        $notification
            ->setType($notificationType)
            ->setStatus(NotificationInterface::STATUS_READY)
            ->setEmail($profile->getCustomerEmail())
            ->setName($profile->getCustomerFullname())
            ->setScheduledAt($this->dateTime->formatDate(true))
            ->setStoreId($profile->getStoreId())
            ->setProfileId($sourcePayment->getProfileId());

        /** @var ResolveSubject $resolveSubject */
        $resolveSubject = $this->resolveSubjectFactory->create(['sourcePayment' => $sourcePayment]);
        $notification->setNotificationData($this->dataResolver->resolve($resolveSubject));

        try {
            $this->persistence->save($notification);
            $this->registry->register($notification);
            $result = [$notification];
        } catch (CouldNotSaveException $exception) {
            $result = [];
        }

        return $result;
    }

    /**
     * Resolve notification type by user type
     *
     * @return string
     */
    private function resolveNotificationType()
    {
        return $this->userContext->getUserType() == UserContextInterface::USER_TYPE_ADMIN
            ? NotificationInterface::TYPE_CANCELLED_SUBSCRIPTION_ADMIN
            : NotificationInterface::TYPE_CANCELLED_SUBSCRIPTION_CUSTOMER;
    }
}
