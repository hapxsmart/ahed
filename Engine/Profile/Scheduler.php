<?php
namespace Aheadworks\Sarp2\Engine\Profile;

use Aheadworks\Sarp2\Engine\Exception\CouldNotScheduleException;
use Aheadworks\Sarp2\Engine\EngineInterface;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Initial;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Profile as ProfileResource;

/**
 * Class SaveHandler
 * @package Aheadworks\Sarp2\Engine\Profile
 */
class Scheduler implements SchedulerInterface
{
    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var PaymentsList
     */
    private $paymentList;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var Initial
     */
    private $generator;

    /**
     * @var SourceFactory
     */
    private $generatorSourceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Manager
     */
    private $notificationManager;

    /**
     * @var ProfileResource
     */
    private $profileResource;

    /**
     * @param EngineInterface $engine
     * @param PaymentsList $paymentList
     * @param Persistence $persistence
     * @param Initial $generator
     * @param SourceFactory $generatorSourceFactory
     * @param LoggerInterface $logger
     * @param Manager $notificationManager
     * @param ProfileResource $profileResource
     */
    public function __construct(
        EngineInterface $engine,
        PaymentsList $paymentList,
        Persistence $persistence,
        Initial $generator,
        SourceFactory $generatorSourceFactory,
        LoggerInterface $logger,
        Manager $notificationManager,
        ProfileResource $profileResource
    ) {
        $this->engine = $engine;
        $this->paymentList = $paymentList;
        $this->persistence = $persistence;
        $this->generator = $generator;
        $this->generatorSourceFactory = $generatorSourceFactory;
        $this->logger = $logger;
        $this->notificationManager = $notificationManager;
        $this->profileResource = $profileResource;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule($profiles)
    {
        $scheduledPayments = [];
        $idsToProcess = [];

        $logData = [];
        try {
            foreach ($profiles as $profile) {
                $profileId = $profile->getProfileId();
                if ($profileId && !$this->paymentList->hasForProfile($profileId)) {
                    $logData['profileId'] = $profileId;

                    $payments = $this->generator->generate(
                        $this->generatorSourceFactory->create(['profile' => $profile])
                    );

                    if (count($payments)) {
                        $prePaymentInfo = $profile->getPrePaymentInfo();
                        if ($prePaymentInfo
                            && ($prePaymentInfo->getIsInitialFeePaid()
                                || $prePaymentInfo->getIsTrialPaid()
                                || $prePaymentInfo->getIsRegularPaid()
                            )
                        ) {
                            $profile->setStatus(Status::ACTIVE);
                        } else {
                            $profile->setStatus(Status::PENDING);
                        }
                    } else {
                        $profile->setStatus(Status::EXPIRED);
                        $this->profileResource->updateStatus($profile);
                    }
                    $this->logger->traceSchedule(
                        LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                        $logData,
                        ['profileStatus' => $profile->getStatus()]
                    );

                    $savedScheduleId = null;
                    /** @var Payment $payment */
                    foreach ($payments as $payment) {
                        if ($savedScheduleId) {
                            $payment->setSchedule(null)
                                ->setScheduleId($savedScheduleId);
                        }

                        $profile->setMembershipActiveUntilDate($payment->getScheduledAt());
                        $this->profileResource->updateMembershipActiveUntilDate($profile);

                        $this->persistence->save($payment);
                        if (!$savedScheduleId && $payment->getSchedule()) {
                            $savedScheduleId = $payment->getScheduleId();
                        }

                        $scheduledPayments[] = $payment;
                        $idsToProcess[] = $payment->getId();
                    }

                    if (count($payments)) {
                        $this->logger->traceSchedule(
                            LoggerInterface::ENTRY_PAYMENTS_SCHEDULED,
                            $logData,
                            ['payments' => $payments]
                        );

                        /**
                         * @param PaymentInterface $scheduledPayment
                         * @return void
                         */
                        $scheduleNotificationCallback = function ($scheduledPayment) {
                            $this->notificationManager->schedule(
                                NotificationInterface::TYPE_SUBSCRIPTION_CREATED,
                                $scheduledPayment
                            );
                            $this->notificationManager->schedule(
                                NotificationInterface::TYPE_UPCOMING_BILLING,
                                $scheduledPayment
                            );
                        };
                        array_walk($payments, $scheduleNotificationCallback);
                    }
                }
            }

            $this->engine->processPayments($idsToProcess);
        } catch (\Exception $e) {
            if ($scheduledPayments) {
                $this->persistence->massDelete($scheduledPayments);
            }
            $this->logger->traceSchedule(
                LoggerInterface::ENTRY_PAYMENTS_SCHEDULE_FAILED,
                $logData,
                [
                    'exception' => $e,
                    'payments' => $scheduledPayments
                ]
            );

            throw new CouldNotScheduleException(__($e->getMessage()));
        }
        return $profiles;
    }
}
