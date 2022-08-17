<?php
namespace Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Bundled;

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
 * Class RetryBundled
 * @package Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Bundled
 */
class RetryBundled implements HandlerInterface
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
     * @param Reattempt $generator
     * @param SourceFactory $generatorSourceFactory
     * @param Persistence $persistence
     * @param Incrementor $stateIncrementor
     * @param Scheduler $reattemptScheduler
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        Reattempt $generator,
        SourceFactory $generatorSourceFactory,
        Persistence $persistence,
        Incrementor $stateIncrementor,
        Scheduler $reattemptScheduler,
        LoggerInterface $logger,
        Config $config
    ) {
        $this->generator = $generator;
        $this->generatorSourceFactory = $generatorSourceFactory;
        $this->persistence = $persistence;
        $this->stateIncrementor = $stateIncrementor;
        $this->reattemptScheduler = $reattemptScheduler;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($payment, $failureInfo = null)
    {
        /** @var Payment[] $childPayments */
        $childPayments = $payment->getChildItems();
        foreach ($childPayments as $child) {
            $child->setPaymentStatus(PaymentInterface::STATUS_UNPROCESSABLE);

            $profile = $child->getProfile();
            $profile->setStatus(Status::SUSPENDED);
            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                ['payment' => $payment],
                ['profile' => $profile]
            );
        }
        $payment->setPaymentStatus(PaymentInterface::STATUS_UNPROCESSABLE);
        $this->logger->traceProcessing(
            LoggerInterface::ENTRY_PAYMENTS_STATUS_CHANGE,
            ['payment' => $payment],
            ['updatedPayments' => array_merge([$payment], $childPayments)]
        );

        /** @var Source $source */
        $source = $this->generatorSourceFactory->create(
            ['payments' => [$payment]]
        );
        $reattempts = $this->generator->generate($source);
        foreach ($reattempts as $reattempt) {
            $this->persistence->save($reattempt);

            /**
             * @param PaymentInterface $child
             * @return void
             */
            $callback = function ($child) use ($reattempt) {
                $child->setParentId($reattempt->getId());
            };
            array_walk($childPayments, $callback);

            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_PAYMENT_REATTEMPT_CREATED,
                ['payment' => $payment],
                ['reattempt' => $reattempt]
            );
        }

        $this->persistence->massSave(array_merge([$payment], $childPayments));
        return $payment;
    }

    /**
     * {@inheritdoc}
     */
    public function handleReattempt($payment, $failureInfo = null)
    {
        $retriesCount = $payment->getRetriesCount();
        $retriesCount++;

        /** @var Payment[] $childPayments */
        $childPayments = $payment->getChildItems();
        if ($retriesCount >= $this->config->getMaxRetriesCount()) {
            $payment->setPaymentStatus(PaymentInterface::STATUS_FAILED);
            foreach ($childPayments as $child) {
                $child->setPaymentStatus(PaymentInterface::STATUS_FAILED);
            }

            /** @var Source $source */
            $source = $this->generatorSourceFactory->create(
                ['payments' => $childPayments]
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
        } else {
            foreach ($childPayments as $child) {
                $child->setPaymentStatus(PaymentInterface::STATUS_UNPROCESSABLE);
                $child->getProfile()->setStatus(Status::SUSPENDED);
            }

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
        $this->persistence->massSave(array_merge([$payment], $childPayments));
    }
}
