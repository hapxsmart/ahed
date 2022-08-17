<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type;

use Aheadworks\Sarp2\Engine\Exception\ScheduledPaymentException;
use Aheadworks\Sarp2\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Action\Pool;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultInterface;
use Aheadworks\Sarp2\Engine\Payment\ActionInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface as EngineLogger;
use Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Factory as HandlerFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Source;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Next;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Handler\AfterPlaceOrder\Composite as AfterPlaceOrderHandlerComposite;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Engine\Payment\ProcessorInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Profile as ProfileResource;
use Aheadworks\Sarp2\Model\Sales\Order\OrderSender;

abstract class AbstractPayProcessor implements ProcessorInterface
{
    /**
     * @var Pool
     */
    private $actionPool;

    /**
     * @var Incrementor
     */
    protected $stateIncrementor;

    /**
     * @var Persistence
     */
    protected $persistence;

    /**
     * @var Detector
     */
    protected $outstandingDetector;

    /**
     * @var SourceFactory
     */
    private $generatorSourceFactory;

    /**
     * @var Next
     */
    private $generator;

    /**
     * @var HandlerFactory
     */
    protected $failureHandlerFactory;

    /**
     * @var IsProcessable
     */
    protected $isProcessableChecker;

    /**
     * @var Cleaner
     */
    protected $cleaner;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var AfterPlaceOrderHandlerComposite
     */
    protected $afterPlaceOrderHandler;

    /**
     * @var Manager
     */
    protected $notificationManager;

    /**
     * @var ProfileResource
     */
    protected $profileResource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var EngineLogger
     */
    protected $engineLogger;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @param Pool $actionPool
     * @param Incrementor $stateIncrementor
     * @param Persistence $persistence
     * @param Detector $outstandingDetector
     * @param Next $generator
     * @param SourceFactory $generatorSourceFactory
     * @param HandlerFactory $failureHandlerFactory
     * @param IsProcessable $isProcessableChecker
     * @param Cleaner $cleaner
     * @param ResultFactory $resultFactory
     * @param Manager $notificationManager
     * @param LoggerInterface $logger
     * @param Config $config
     * @param EngineLogger $engineLogger
     * @param ProfileResource $profileResource
     * @param AfterPlaceOrderHandlerComposite $afterPlaceOrderHandler
     * @param OrderSender $orderSender
     */
    public function __construct(
        Pool $actionPool,
        Incrementor $stateIncrementor,
        Persistence $persistence,
        Detector $outstandingDetector,
        Next $generator,
        SourceFactory $generatorSourceFactory,
        HandlerFactory $failureHandlerFactory,
        IsProcessable $isProcessableChecker,
        Cleaner $cleaner,
        ResultFactory $resultFactory,
        Manager $notificationManager,
        LoggerInterface $logger,
        Config $config,
        EngineLogger $engineLogger,
        ProfileResource $profileResource,
        AfterPlaceOrderHandlerComposite $afterPlaceOrderHandler,
        OrderSender $orderSender
    ) {
        $this->actionPool = $actionPool;
        $this->stateIncrementor = $stateIncrementor;
        $this->persistence = $persistence;
        $this->outstandingDetector = $outstandingDetector;
        $this->generator = $generator;
        $this->generatorSourceFactory = $generatorSourceFactory;
        $this->failureHandlerFactory = $failureHandlerFactory;
        $this->isProcessableChecker = $isProcessableChecker;
        $this->cleaner = $cleaner;
        $this->resultFactory = $resultFactory;
        $this->notificationManager = $notificationManager;
        $this->logger = $logger;
        $this->config = $config;
        $this->engineLogger = $engineLogger;
        $this->profileResource = $profileResource;
        $this->afterPlaceOrderHandler = $afterPlaceOrderHandler;
        $this->orderSender = $orderSender;
    }

    /**
     * Perform pay action
     *
     * @param Payment $payment
     * @return Payment
     * @throws ScheduledPaymentException
     */
    protected function pay($payment)
    {
        $notifications = $this->notificationManager->schedule(
            NotificationInterface::TYPE_BILLING_SUCCESSFUL,
            $payment
        );

        $isBundled = $payment->isBundled();
        $actionType = $isBundled
            ? ActionInterface::TYPE_BUNDLED
            : ActionInterface::TYPE_SINGLE;
        $action = $this->actionPool->getAction($actionType);
        $result = $action->pay($payment);

        $order = $result->getOrder();
        $orderId = $order->getEntityId();

        $payment->setPaymentStatus(PaymentInterface::STATUS_PAID)
            ->setOrderId($orderId)
            ->setTotalPaid($order->getGrandTotal())
            ->setBaseTotalPaid($order->getBaseGrandTotal())
            ->setPaidAt($order->getCreatedAt());

        $this->increment($payment);

        $this->orderSender->send($orderId);

        $this->logPaymentSuccess($payment, $result);

        $this->afterPlaceOrderHandler->handle($payment);

        /** @var Source $source */
        $source = $this->generatorSourceFactory->create(
            [
                'payments' => $isBundled
                    ? $payment->getChildItems()
                    : [$payment]
            ]
        );

        $nextPayments = $this->generator->generate($source);
        $this->persist($nextPayments);

        if (count($nextPayments) && $this->config->isLogEnabled()) {
            $this->engineLogger->traceProcessing(
                EngineLogger::ENTRY_PAYMENTS_SCHEDULED,
                ['payment' => $payment],
                ['scheduledPayments' => $nextPayments]
            );
        }

        array_walk($nextPayments, function ($nextPayment) {
            /** @var PaymentInterface $nextPayment */
            $profile = $nextPayment->getProfile();
            $profile->setMembershipActiveUntilDate($nextPayment->getScheduledAt());
            $this->profileResource->updateMembershipActiveUntilDate($profile);

            $this->notificationManager->schedule(
                NotificationInterface::TYPE_UPCOMING_BILLING,
                $nextPayment
            );
        });

        if ($isBundled) {
            $this->cleaner->addList($payment->getChildItems());
        }

        foreach ($notifications as $notification) {
            $subjectData = ['sourcePayment' => $payment];
            if (count($nextPayments)) {
                $subjectData['nextPayments'] = $nextPayments;
            }

            $this->notificationManager->updateNotificationData(
                $notification->setOrderId($orderId),
                $subjectData
            );
        }

        return $payment;
    }

    /**
     * Increment state
     *
     * @param PaymentInterface|Payment $payment
     */
    protected function increment($payment)
    {
        $this->stateIncrementor->increment($payment);
        $this->persist([$payment]);
    }

    /**
     * Persist payments
     *
     * @param PaymentInterface[]|Payment[] $payments
     * @return void
     */
    protected function persist($payments)
    {
        foreach ($payments as $payment) {
            if ($payment->isBundled()) {
                $this->persistence->massSave(array_merge([$payment], $payment->getChildItems()));
            } else {
                $this->persistence->save($payment);
            }
        }
    }

    /**
     * Add successful payment log record
     *
     * @param PaymentInterface $payment
     * @param ResultInterface $paymentResult
     * @return void
     */
    private function logPaymentSuccess($payment, $paymentResult)
    {
        if ($this->config->isLogEnabled()) {
            $this->logger->info(
                'Payment successful',
                [
                    'orderId' => $paymentResult->getOrder()->getEntityId(),
                    'paymentId' => $payment->getId()
                ]
            );
            $this->engineLogger->traceProcessing(
                EngineLogger::ENTRY_PAYMENT_SUCCESSFUL,
                [
                    'payment' => $payment,
                    'result' => $paymentResult
                ]
            );
        }
    }

    /**
     * Add payment failure log record
     *
     * @param string $message
     * @param ScheduledPaymentException $exception
     * @param PaymentInterface $payment
     * @param array $context
     * @return void
     */
    protected function logPaymentFailure($message, $exception, $payment, $context = [])
    {
        if ($this->config->isLogEnabled()) {
            $context = array_merge(
                ['message' => $exception->getLogMessage()],
                $context
            );
            $exceptionCode = $exception->getCode();
            if ($exceptionCode) {
                $context['code'] = $exceptionCode;
            }
            if (!$payment->isBundled()) {
                $context['profileId'] = $payment->getProfileId();
            } else {
                $profileIds = [];
                foreach ($payment->getChildItems() as $child) {
                    $profileIds[] = $child->getProfileId();
                }
                $context['profileIds'] = $profileIds;
            }
            $context['trace'] = $exception->getPrevious()
                ? $exception->getPrevious()->getTraceAsString()
                : $exception->getTraceAsString();

            $this->logger->error($message, $context);
        }
    }

    /**
     * Add critical failure log record
     *
     * @param \Exception $exception
     * @param PaymentInterface $payment
     * @param array $context
     * @return void
     */
    protected function logCriticalFailure($exception, $payment, $context = [])
    {
        $context = array_merge(
            [
                'message' => $exception->getMessage(),
                'thrown' => $exception->getFile() . ':' . $exception->getLine(),
                'payment_id' => $payment->getId()
            ],
            $context
        );

        $exceptionForTrace = $exception->getPrevious()
            ? $exception->getPrevious()
            : $exception;

        $trace = [];
        foreach ($exceptionForTrace->getTrace() as $line) {
            unset($line['args']);
            $trace[] = $line;
        }
        $context['trace'] = $trace;

        if (!$payment->isBundled()) {
            $context['profileId'] = $payment->getProfileId();
        } else {
            $profileIds = [];
            foreach ($payment->getChildItems() as $child) {
                $profileIds[] = $child->getProfileId();
            }
            $context['profileIds'] = $profileIds;
        }

        $this->logger->error('AW SARP2 Payment Unexpected Exception', $context);
    }
}
