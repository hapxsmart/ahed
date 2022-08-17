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
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime;

class BillingFailed implements SchedulerInterface
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
     * @param NotificationFactory $notificationFactory
     * @param Persistence $persistence
     * @param DataResolver $dataResolver
     * @param ResolveSubjectFactory $resolveSubjectFactory
     * @param Registry $registry
     * @param DateTime $dateTime
     */
    public function __construct(
        NotificationFactory $notificationFactory,
        Persistence $persistence,
        DataResolver $dataResolver,
        ResolveSubjectFactory $resolveSubjectFactory,
        Registry $registry,
        DateTime $dateTime
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->persistence = $persistence;
        $this->dataResolver = $dataResolver;
        $this->resolveSubjectFactory = $resolveSubjectFactory;
        $this->registry = $registry;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(PaymentInterface $sourcePayment, array $additionalData)
    {
        $profile = $sourcePayment->getProfile();

        /** @var Notification $notification */
        $notification = $this->notificationFactory->create();
        $notification->setType(NotificationInterface::TYPE_BILLING_FAILED)
            ->setStatus(NotificationInterface::STATUS_READY)
            ->setEmail($profile->getCustomerEmail())
            ->setName($profile->getCustomerFullname())
            ->setScheduledAt($this->dateTime->formatDate(true))
            ->setStoreId($profile->getStoreId())
            ->setProfileId($sourcePayment->getProfileId());

        /** @var ResolveSubject $resolveSubject */
        $resolveSubject = $this->resolveSubjectFactory->create(['sourcePayment' => $sourcePayment]);
        $notification->setNotificationData($this->dataResolver->resolve($resolveSubject));

        /** @var Notification $registered */
        $registered = $this->registry->get();
        if ($registered) {
            $this->registry->unRegister();
            $this->persistence->delete($registered);
        }

        try {
            $this->persistence->save($notification);
            $result = [$notification];
        } catch (CouldNotSaveException $exception) {
            $result = [];
        }

        return $result;
    }
}
