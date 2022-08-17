<?php
namespace Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Single;

use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Failure\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Source;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\Scheduler;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class DefaultHandler
 * @package Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Single
 */
class DefaultHandler implements HandlerInterface
{
    /**
     * @var Reattempt
     */
    private $generator;

    /**
     * @var SourceFactory
     */
    private $generatorSourceFactory;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var Incrementor
     */
    private $stateIncrementor;

    /**
     * @var Scheduler
     */
    private $reattemptScheduler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Manager
     */
    private $notificationManager;

    /**
     * @param Reattempt $generator
     * @param SourceFactory $generatorSourceFactory
     * @param Persistence $persistence
     * @param Incrementor $stateIncrementor
     * @param Scheduler $reattemptScheduler
     * @param LoggerInterface $logger
     * @param Config $config
     * @param Manager $notificationManager
     */
    public function __construct(
        Reattempt $generator,
        SourceFactory $generatorSourceFactory,
        Persistence $persistence,
        Incrementor $stateIncrementor,
        Scheduler $reattemptScheduler,
        LoggerInterface $logger,
        Config $config,
        Manager $notificationManager
    ) {
        $this->generator = $generator;
        $this->generatorSourceFactory = $generatorSourceFactory;
        $this->persistence = $persistence;
        $this->stateIncrementor = $stateIncrementor;
        $this->reattemptScheduler = $reattemptScheduler;
        $this->logger = $logger;
        $this->config = $config;
        $this->notificationManager = $notificationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($payment, $failureInfo = null)
    {
        /** @var Payment $payment */
        $payment->setPaymentStatus(PaymentInterface::STATUS_UNPROCESSABLE);
        $this->logger->traceProcessing(
            LoggerInterface::ENTRY_PAYMENT_STATUS_CHANGE,
            ['payment' => $payment]
        );

        $profile = $payment->getProfile();
        $profile->setStatus(Status::SUSPENDED);
        $this->logger->traceProcessing(
            LoggerInterface::ENTRY_PROFILE_SET_STATUS,
            ['payment' => $payment],
            ['profile' => $profile]
        );

        /** @var Source $source */
        $source = $this->generatorSourceFactory->create(
            ['payments' => [$payment]]
        );
        $reattempts = $this->generator->generate($source);
        foreach ($reattempts as $reattempt) {
            $this->persistence->save($reattempt);
            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_PAYMENT_REATTEMPT_CREATED,
                ['payment' => $payment],
                ['reattempt' => $reattempt]
            );
        }
        $this->persistence->save($payment);
        return $payment;
    }

    /**
     * {@inheritdoc}
     */
    public function handleReattempt($payment, $failureInfo = null)
    {
        $profile = $payment->getProfile();

        $retriesCount = $payment->getRetriesCount();
        $retriesCount++;
        if ($retriesCount >= $this->config->getMaxRetriesCount()) {
            $payment->setPaymentStatus(PaymentInterface::STATUS_FAILED);
            $profile->setStatus(Status::CANCELLED);

            $this->notificationManager->schedule(
                NotificationInterface::TYPE_CANCELLED_SUBSCRIPTION,
                $payment
            );

            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                ['payment' => $payment],
                ['profile' => $profile]
            );
        } else {
            $profile->setStatus(Status::SUSPENDED);

            $scheduleResult = $this->reattemptScheduler->schedule($payment);
            $reattemptDate = $scheduleResult->getDate();

            $payment->setRetryAt($reattemptDate);
            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_PAYMENT_REATTEMPT_RESCHEDULED,
                ['payment' => $payment],
                ['date' => $reattemptDate]
            );
        }
        $payment->setRetriesCount($retriesCount);

        /** @var Payment $payment */
        $this->persistence->save($payment);
    }
}
