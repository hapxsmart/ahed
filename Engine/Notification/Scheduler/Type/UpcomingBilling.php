<?php
namespace Aheadworks\Sarp2\Engine\Notification\Scheduler\Type;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\Notification\DataResolver;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubjectFactory;
use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Aheadworks\Sarp2\Engine\Notification\SchedulerInterface;
use Aheadworks\Sarp2\Engine\NotificationFactory;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsFreeTrial;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Plan\Resolver\Definition\ValueResolver;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;

class UpcomingBilling implements SchedulerInterface
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
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var ValueResolver
     */
    private $definitionValueResolver;

    /**
     * @var IsFreeTrial
     */
    private $isFreeTrial;

    /**
     * @param NotificationFactory $notificationFactory
     * @param Persistence $persistence
     * @param DataResolver $dataResolver
     * @param ResolveSubjectFactory $resolveSubjectFactory
     * @param DateTime $dateTime
     * @param CoreDate $coreDate
     * @param ValueResolver $definitionValueResolver
     * @param IsFreeTrial $isFreeTrial
     */
    public function __construct(
        NotificationFactory $notificationFactory,
        Persistence $persistence,
        DataResolver $dataResolver,
        ResolveSubjectFactory $resolveSubjectFactory,
        DateTime $dateTime,
        CoreDate $coreDate,
        ValueResolver $definitionValueResolver,
        IsFreeTrial $isFreeTrial
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->persistence = $persistence;
        $this->dataResolver = $dataResolver;
        $this->resolveSubjectFactory = $resolveSubjectFactory;
        $this->dateTime = $dateTime;
        $this->coreDate = $coreDate;
        $this->definitionValueResolver = $definitionValueResolver;
        $this->isFreeTrial = $isFreeTrial;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function schedule(PaymentInterface $sourcePayment, array $additionalData)
    {
        $profile = $sourcePayment->getProfile();
        $storeId = $profile->getStoreId();
        $profileDefinition = $profile->getProfileDefinition();
        $result = [];

        $offset = $this->definitionValueResolver->getUpcomingEmailOffset($profileDefinition, $storeId);
        if ($offset && $sourcePayment->getType() != PaymentInterface::TYPE_LAST_PERIOD_HOLDER) {
            $estimated = (new \DateTime($sourcePayment->getScheduledAt()))
                ->modify('-' . $offset . ' day');
            $schedule = $sourcePayment->getSchedule();
            if ($this->isFreeTrial->check($sourcePayment)) {
                $remainingTrialCount = $schedule->getTrialTotalCount() - $schedule->getTrialCount();
                $estimated->modify('+' . $remainingTrialCount . ' ' . $schedule->getTrialPeriod());
            }
            $estimatedTm = $this->coreDate->gmtTimestamp($estimated);
            $today = $this->dateTime->formatDate(true);
            $todayTm = $this->coreDate->gmtTimestamp($today);
            if ($estimatedTm >= $todayTm) {
                if ($this->isFreeTrial->check($sourcePayment)) {
                    $sourcePayment
                        ->setTotalScheduled($profile->getRegularGrandTotal())
                        ->setBaseTotalScheduled($profile->getBaseRegularGrandTotal());
                }
                /** @var Notification $notification */
                $notification = $this->notificationFactory->create();
                $notification->setType(NotificationInterface::TYPE_UPCOMING_BILLING)
                    ->setStatus(NotificationInterface::STATUS_READY)
                    ->setEmail($profile->getCustomerEmail())
                    ->setName($profile->getCustomerFullname())
                    ->setScheduledAt($estimated)
                    ->setStoreId($storeId)
                    ->setProfileId($sourcePayment->getProfileId());

                /** @var ResolveSubject $resolveSubject */
                $resolveSubject = $this->resolveSubjectFactory->create(
                    [
                        'sourcePayment' => $sourcePayment,
                        'nextPayments' => [$sourcePayment]
                    ]
                );
                $notification->setNotificationData($this->dataResolver->resolve($resolveSubject));

                try {
                    $this->persistence->save($notification);
                    $result = [$notification];
                } catch (CouldNotSaveException $exception) {
                    $result = [];
                }
            }
        }

        return $result;
    }
}
