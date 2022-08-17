<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Processor\Type;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Exception\ScheduledPaymentException;
use Aheadworks\Sarp2\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\ActionInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\Pool;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface as EngineLogger;
use Aheadworks\Sarp2\Engine\Payment\Failure\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Factory as HandlerFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Source;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Next;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\DetectResult;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\Result;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Actual;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\Source\Status as ProfileStatus;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Processor\Type\Actual
 */
class ActualTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Actual
     */
    private $processor;

    /**
     * @var Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionPoolMock;

    /**
     * @var Incrementor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateIncrementorMock;

    /**
     * @var Persistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceMock;

    /**
     * @var Detector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outstandingDetectorMock;

    /**
     * @var SourceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatorSourceFactoryMock;

    /**
     * @var Next|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatorMock;

    /**
     * @var HandlerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $failureHandlerFactoryMock;

    /**
     * @var IsProcessable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $isProcessableCheckerMock;

    /**
     * @var Cleaner|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanerMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationManagerMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var EngineLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineLoggerMock;

    /**
     * @var ProfileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileRepositoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->actionPoolMock = $this->createMock(Pool::class);
        $this->stateIncrementorMock = $this->createMock(Incrementor::class);
        $this->persistenceMock = $this->createMock(Persistence::class);
        $this->outstandingDetectorMock = $this->createMock(Detector::class);
        $this->generatorMock = $this->createMock(Next::class);
        $this->generatorSourceFactoryMock = $this->createMock(SourceFactory::class);
        $this->failureHandlerFactoryMock = $this->createMock(HandlerFactory::class);
        $this->isProcessableCheckerMock = $this->createMock(IsProcessable::class);
        $this->cleanerMock = $this->createMock(Cleaner::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->notificationManagerMock = $this->createMock(Manager::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->engineLoggerMock = $this->createMock(EngineLogger::class);
        $this->profileRepositoryMock = $this->createMock(ProfileRepositoryInterface::class);
        $this->processor = $objectManager->getObject(
            Actual::class,
            [
                'actionPool' => $this->actionPoolMock,
                'stateIncrementor' => $this->stateIncrementorMock,
                'persistence' => $this->persistenceMock,
                'outstandingDetector' => $this->outstandingDetectorMock,
                'generator' => $this->generatorMock,
                'generatorSourceFactory' => $this->generatorSourceFactoryMock,
                'failureHandlerFactory' => $this->failureHandlerFactoryMock,
                'isProcessableChecker' => $this->isProcessableCheckerMock,
                'cleaner' => $this->cleanerMock,
                'resultFactory' => $this->resultFactoryMock,
                'notificationManager' => $this->notificationManagerMock,
                'logger' => $this->loggerMock,
                'config' => $this->configMock,
                'engineLogger' => $this->engineLoggerMock,
                'profileRepository' => $this->profileRepositoryMock
            ]
        );
    }

    public function testProcessNotProcessable()
    {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $outstandingDetectMock = $this->createMock(DetectResult::class);
        $processResultMock = $this->createMock(Result::class);

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_ACTUAL)
            ->willReturn(false);
        $this->outstandingDetectorMock->expects($this->once())
            ->method('detect')
            ->with([])
            ->willReturn($outstandingDetectMock);
        $outstandingDetectMock->expects($this->once())
            ->method('getOutstandingPayments')
            ->willReturn([]);
        $outstandingDetectMock->expects($this->once())
            ->method('getTodayPayments')
            ->willReturn([]);
        $this->actionPoolMock->expects($this->never())
            ->method('getAction');
        $this->stateIncrementorMock->expects($this->never())
            ->method('increment');
        $this->generatorMock->expects($this->never())
            ->method('generate');
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isOutstandingDetected' => false])
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    public function testProcessOutstanding()
    {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $outstandingDetectMock = $this->createMock(DetectResult::class);
        $processResultMock = $this->createMock(Result::class);

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_ACTUAL)
            ->willReturn(true);
        $this->outstandingDetectorMock->expects($this->once())
            ->method('detect')
            ->with([$paymentMock])
            ->willReturn($outstandingDetectMock);
        $outstandingDetectMock->expects($this->once())
            ->method('getOutstandingPayments')
            ->willReturn([$paymentMock]);
        $outstandingDetectMock->expects($this->once())
            ->method('getTodayPayments')
            ->willReturn([]);
        $this->persistenceMock->expects($this->once())
            ->method('massChangeType')
            ->with([$paymentMock], PaymentInterface::TYPE_OUTSTANDING);
        $this->engineLoggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                EngineLogger::ENTRY_PAYMENTS_TYPE_MASS_CHANGE,
                ['payments' => [$paymentMock]],
                ['updatedPayments' => [$paymentMock]]
            );
        $this->actionPoolMock->expects($this->never())
            ->method('getAction');
        $this->stateIncrementorMock->expects($this->never())
            ->method('increment');
        $this->generatorMock->expects($this->never())
            ->method('generate');
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isOutstandingDetected' => true])
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    /**
     * @param bool $isLogEnabled
     * @param PaymentInterface|\PHPUnit_Framework_MockObject_MockObject|null $nextPaymentMock
     * @dataProvider processPayDataProvider
     */
    public function testProcessPaySingle($isLogEnabled, $nextPaymentMock)
    {
        $orderId = 1;
        $grandTotal = 10.00;
        $baseGrandTotal = 15.00;
        $createdAt = '2018-08-01 12:00:00';
        $paymentId = 2;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $outstandingDetectMock = $this->createMock(DetectResult::class);
        $notificationsMock = [$this->createMock(Notification::class)];
        $actionMock = $this->createMock(ActionInterface::class);
        $actionResultMock = $this->createMock(ResultInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $generateSourceMock = $this->createMock(Source::class);
        $processResultMock = $this->createMock(Result::class);
        $profileMock = $this->createMock(ProfileInterface::class);

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_ACTUAL)
            ->willReturn(true);
        $this->outstandingDetectorMock->expects($this->once())
            ->method('detect')
            ->with([$paymentMock])
            ->willReturn($outstandingDetectMock);
        $outstandingDetectMock->expects($this->once())
            ->method('getOutstandingPayments')
            ->willReturn([]);
        $outstandingDetectMock->expects($this->once())
            ->method('getTodayPayments')
            ->willReturn([$paymentMock]);
        $paymentMock->expects($this->exactly(2))
            ->method('isBundled')
            ->willReturn(false);
        $this->actionPoolMock->expects($this->once())
            ->method('getAction')
            ->with(ActionInterface::TYPE_SINGLE)
            ->willReturn($actionMock);
        $actionMock->expects($this->once())
            ->method('pay')
            ->with($paymentMock)
            ->willReturn($actionResultMock);
        $actionResultMock->expects($isLogEnabled ? $this->exactly(2) : $this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($isLogEnabled ? $this->exactly(2) : $this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($grandTotal);
        $orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);
        $orderMock->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($createdAt);
        $paymentMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_PAID)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setOrderId')
            ->with($orderId)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setTotalPaid')
            ->with($grandTotal)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setBaseTotalPaid')
            ->with($baseGrandTotal)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setPaidAt')
            ->with($createdAt)
            ->willReturnSelf();
        $this->stateIncrementorMock->expects($this->once())
            ->method('increment')
            ->with($paymentMock);
        if ($nextPaymentMock) {
            $nextPaymentMock->expects($this->once())
                ->method('getProfile')
                ->willReturn($profileMock);
            $nextPaymentMock->expects($this->once())
                ->method('isBundled')
                ->willReturn(false);
            $this->notificationManagerMock->expects($this->exactly(2))
                ->method('schedule')
                ->withConsecutive(
                    [NotificationInterface::TYPE_BILLING_SUCCESSFUL, $paymentMock],
                    [NotificationInterface::TYPE_UPCOMING_BILLING, $nextPaymentMock]
                )
                ->willReturnOnConsecutiveCalls($notificationsMock, null);
            $this->persistenceMock->expects($this->exactly(2))
                ->method('save')
                ->withConsecutive([$paymentMock], [$nextPaymentMock]);
            $this->generatorMock->expects($this->once())
                ->method('generate')
                ->with($generateSourceMock)
                ->willReturn([$nextPaymentMock]);
        } else {
            $this->notificationManagerMock->expects($this->once())
                ->method('schedule')
                ->with(NotificationInterface::TYPE_BILLING_SUCCESSFUL, $paymentMock)
                ->willReturn($notificationsMock);
            $this->persistenceMock->expects($this->once())
                ->method('save')
                ->with($paymentMock);
            $this->generatorMock->expects($this->once())
                ->method('generate')
                ->with($generateSourceMock)
                ->willReturn([]);
        }

        $this->configMock->expects($nextPaymentMock ? $this->exactly(2) : $this->once())
            ->method('isLogEnabled')
            ->willReturn($isLogEnabled);
        if ($isLogEnabled) {
            $paymentMock->expects($this->once())
                ->method('getId')
                ->willReturn($paymentId);
            $this->loggerMock->expects($this->once())
                ->method('info')
                ->with(
                    'Payment successful',
                    [
                        'orderId' => $orderId,
                        'paymentId' => $paymentId
                    ]
                );
            if ($nextPaymentMock) {
                $this->engineLoggerMock->expects($this->exactly(2))
                    ->method('traceProcessing')
                    ->withConsecutive(
                        [
                            EngineLogger::ENTRY_PAYMENT_SUCCESSFUL,
                            [
                                'payment' => $paymentMock,
                                'result' => $actionResultMock
                            ]
                        ],
                        [
                            EngineLogger::ENTRY_PAYMENTS_SCHEDULED,
                            ['payment' => $paymentMock],
                            ['scheduledPayments' => [$nextPaymentMock]]
                        ]
                    );
            } else {
                $this->engineLoggerMock->expects($this->once())
                    ->method('traceProcessing')
                    ->with(
                        EngineLogger::ENTRY_PAYMENT_SUCCESSFUL,
                        [
                            'payment' => $paymentMock,
                            'result' => $actionResultMock
                        ]
                    );
            }
        }
        $this->generatorSourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['payments' => [$paymentMock]])
            ->willReturn($generateSourceMock);
        foreach ($notificationsMock as $notificationMock) {
            $notificationMock->expects($this->once())
                ->method('setOrderId')
                ->with($orderId)
                ->willReturnSelf();
            $this->notificationManagerMock->expects($this->once())
                ->method('updateNotificationData')
                ->with(
                    $notificationMock,
                    $nextPaymentMock
                        ? ['sourcePayment' => $paymentMock, 'nextPayments' => [$nextPaymentMock]]
                        : ['sourcePayment' => $paymentMock]
                );
        }

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isOutstandingDetected' => false])
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    /**
     * @param bool $isLogEnabled
     * @param PaymentInterface|\PHPUnit_Framework_MockObject_MockObject|null $nextPaymentMock
     * @dataProvider processPayDataProvider
     */
    public function testProcessPayBundled($isLogEnabled, $nextPaymentMock)
    {
        $orderId = 1;
        $grandTotal = 10.00;
        $baseGrandTotal = 15.00;
        $createdAt = '2018-08-01 12:00:00';
        $paymentId = 2;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $childPaymentMock = $this->createMock(Payment::class);
        $outstandingDetectMock = $this->createMock(DetectResult::class);
        $notificationsMock = [$this->createMock(Notification::class)];
        $actionMock = $this->createMock(ActionInterface::class);
        $actionResultMock = $this->createMock(ResultInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $generateSourceMock = $this->createMock(Source::class);
        $processResultMock = $this->createMock(Result::class);
        $profileMock = $this->createMock(ProfileInterface::class);

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_ACTUAL)
            ->willReturn(true);
        $this->outstandingDetectorMock->expects($this->once())
            ->method('detect')
            ->with([$paymentMock])
            ->willReturn($outstandingDetectMock);
        $outstandingDetectMock->expects($this->once())
            ->method('getOutstandingPayments')
            ->willReturn([]);
        $outstandingDetectMock->expects($this->once())
            ->method('getTodayPayments')
            ->willReturn([$paymentMock]);
        $paymentMock->expects($this->exactly(2))
            ->method('isBundled')
            ->willReturn(true);
        $this->actionPoolMock->expects($this->once())
            ->method('getAction')
            ->with(ActionInterface::TYPE_BUNDLED)
            ->willReturn($actionMock);
        $actionMock->expects($this->once())
            ->method('pay')
            ->with($paymentMock)
            ->willReturn($actionResultMock);
        $actionResultMock->expects($isLogEnabled ? $this->exactly(2) : $this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($isLogEnabled ? $this->exactly(2) : $this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($grandTotal);
        $orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);
        $orderMock->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($createdAt);
        $paymentMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_PAID)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setOrderId')
            ->with($orderId)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setTotalPaid')
            ->with($grandTotal)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setBaseTotalPaid')
            ->with($baseGrandTotal)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setPaidAt')
            ->with($createdAt)
            ->willReturnSelf();
        $this->stateIncrementorMock->expects($this->once())
            ->method('increment')
            ->with($paymentMock);
        $paymentMock->expects($this->exactly(3))
            ->method('getChildItems')
            ->willReturn([$childPaymentMock]);
        $this->persistenceMock->expects($this->once())
            ->method('massSave')
            ->with([$paymentMock, $childPaymentMock]);

        if ($nextPaymentMock) {
            $nextPaymentMock->expects($this->once())
                ->method('getProfile')
                ->willReturn($profileMock);
            $nextPaymentMock->expects($this->once())
                ->method('isBundled')
                ->willReturn(false);
            $this->notificationManagerMock->expects($this->exactly(2))
                ->method('schedule')
                ->withConsecutive(
                    [NotificationInterface::TYPE_BILLING_SUCCESSFUL, $paymentMock],
                    [NotificationInterface::TYPE_UPCOMING_BILLING, $nextPaymentMock]
                )
                ->willReturnOnConsecutiveCalls($notificationsMock, null);
            $this->persistenceMock->expects($this->once())
                ->method('save')
                ->with($nextPaymentMock);
            $this->generatorMock->expects($this->once())
                ->method('generate')
                ->with($generateSourceMock)
                ->willReturn([$nextPaymentMock]);
        } else {
            $this->notificationManagerMock->expects($this->once())
                ->method('schedule')
                ->with(NotificationInterface::TYPE_BILLING_SUCCESSFUL, $paymentMock)
                ->willReturn($notificationsMock);
            $this->generatorMock->expects($this->once())
                ->method('generate')
                ->with($generateSourceMock)
                ->willReturn([]);
        }

        $this->configMock->expects($nextPaymentMock ? $this->exactly(2) : $this->once())
            ->method('isLogEnabled')
            ->willReturn($isLogEnabled);
        if ($isLogEnabled) {
            $paymentMock->expects($this->once())
                ->method('getId')
                ->willReturn($paymentId);
            $this->loggerMock->expects($this->once())
                ->method('info')
                ->with(
                    'Payment successful',
                    [
                        'orderId' => $orderId,
                        'paymentId' => $paymentId
                    ]
                );
            if ($nextPaymentMock) {
                $this->engineLoggerMock->expects($this->exactly(2))
                    ->method('traceProcessing')
                    ->withConsecutive(
                        [
                            EngineLogger::ENTRY_PAYMENT_SUCCESSFUL,
                            [
                                'payment' => $paymentMock,
                                'result' => $actionResultMock
                            ]
                        ],
                        [
                            EngineLogger::ENTRY_PAYMENTS_SCHEDULED,
                            ['payment' => $paymentMock],
                            ['scheduledPayments' => [$nextPaymentMock]]
                        ]
                    );
            } else {
                $this->engineLoggerMock->expects($this->once())
                    ->method('traceProcessing')
                    ->with(
                        EngineLogger::ENTRY_PAYMENT_SUCCESSFUL,
                        [
                            'payment' => $paymentMock,
                            'result' => $actionResultMock
                        ]
                    );
            }
        }
        $this->generatorSourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['payments' => [$childPaymentMock]])
            ->willReturn($generateSourceMock);
        $this->cleanerMock->expects($this->once())
            ->method('addList')
            ->with([$childPaymentMock]);
        foreach ($notificationsMock as $notificationMock) {
            $notificationMock->expects($this->once())
                ->method('setOrderId')
                ->with($orderId)
                ->willReturnSelf();
            $this->notificationManagerMock->expects($this->once())
                ->method('updateNotificationData')
                ->with(
                    $notificationMock,
                    $nextPaymentMock
                        ? ['sourcePayment' => $paymentMock, 'nextPayments' => [$nextPaymentMock]]
                        : ['sourcePayment' => $paymentMock]
                );
        }

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isOutstandingDetected' => false])
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    /**
     * @param bool $isBundled
     * @param bool $isLogEnabled
     * @dataProvider processPaymentExceptionDataProvider
     */
    public function testProcessPaymentException($isBundled, $isLogEnabled)
    {
        $profileId = 1;
        $exceptionCode = 100;
        $exceptionMessage = 'Gateway error.';
        $maxAttemptsCount = 3;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $childPaymentMock = $this->createMock(Payment::class);
        $outstandingDetectMock = $this->createMock(DetectResult::class);
        $actionMock = $this->createMock(ActionInterface::class);
        $failureHandlerMock = $this->createMock(HandlerInterface::class);
        $processResultMock = $this->createMock(Result::class);
        $exception = new ScheduledPaymentException(__($exceptionMessage), null, $exceptionCode);

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_ACTUAL)
            ->willReturn(true);
        $this->outstandingDetectorMock->expects($this->once())
            ->method('detect')
            ->with([$paymentMock])
            ->willReturn($outstandingDetectMock);
        $outstandingDetectMock->expects($this->once())
            ->method('getOutstandingPayments')
            ->willReturn([]);
        $outstandingDetectMock->expects($this->once())
            ->method('getTodayPayments')
            ->willReturn([$paymentMock]);
        $this->notificationManagerMock->expects($this->exactly(3))
            ->method('schedule')
            ->withConsecutive(
                [NotificationInterface::TYPE_BILLING_SUCCESSFUL, $paymentMock],
                [NotificationInterface::TYPE_BILLING_FAILED, $paymentMock]
            );
        $paymentMock->expects($this->exactly($isLogEnabled ? 3 : 2))
            ->method('isBundled')
            ->willReturn($isBundled);
        $this->actionPoolMock->expects($this->once())
            ->method('getAction')
            ->with(
                $isBundled
                    ? ActionInterface::TYPE_BUNDLED
                    : ActionInterface::TYPE_SINGLE
            )
            ->willReturn($actionMock);
        $actionMock->expects($this->once())->method('pay')
            ->with($paymentMock)
            ->willThrowException($exception);
        $this->configMock->expects($this->exactly(2))
            ->method('isLogEnabled')
            ->willReturn($isLogEnabled);
        $this->configMock->expects($this->once())
            ->method('getMaxRetriesCount')
            ->willReturn($maxAttemptsCount);
        if ($isLogEnabled) {
            $this->engineLoggerMock->expects($this->once())
                ->method('traceProcessing')
                ->with(
                    EngineLogger::ENTRY_PAYMENT_FAILED,
                    ['payments' => [$paymentMock]],
                    ['failedPayment' => $paymentMock, 'exception' => $exception]
                );
            if ($isBundled) {
                $paymentMock->expects($this->once())
                    ->method('getChildItems')
                    ->willReturn([$childPaymentMock]);
                $childPaymentMock->expects($this->once())
                    ->method('getProfileId')
                    ->willReturn($profileId);
            } else {
                $paymentMock->expects($this->once())
                    ->method('getProfileId')
                    ->willReturn($profileId);
            }
            $this->loggerMock->expects($this->once())
                ->method('error')
                ->with('Payment failed');
        }
        $this->failureHandlerFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $isBundled
                    ? HandlerInterface::TYPE_BUNDLE
                    : HandlerInterface::TYPE_SINGLE
            )
            ->willReturn($failureHandlerMock);
        $failureHandlerMock->expects($this->once())
            ->method('handle')
            ->with($paymentMock);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isOutstandingDetected' => false])
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    /**
     * @param bool $isBundled
     * @dataProvider processExceptionDataProvider
     */
    public function testProcessException($isBundled)
    {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $childPaymentMock = $this->createMock(Payment::class);
        $outstandingDetectMock = $this->createMock(DetectResult::class);
        $actionMock = $this->createMock(ActionInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $processResultMock = $this->createMock(Result::class);
        $exception = new \Exception('Persistence error.');

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_ACTUAL)
            ->willReturn(true);
        $this->outstandingDetectorMock->expects($this->once())
            ->method('detect')
            ->with([$paymentMock])
            ->willReturn($outstandingDetectMock);
        $outstandingDetectMock->expects($this->once())
            ->method('getOutstandingPayments')
            ->willReturn([]);
        $outstandingDetectMock->expects($this->once())
            ->method('getTodayPayments')
            ->willReturn([$paymentMock]);
        $paymentMock->expects($this->any())
            ->method('isBundled')
            ->willReturn($isBundled);
        $paymentMock->expects($this->any())
            ->method('getChildItems')
            ->willReturn([$childPaymentMock]);
        $this->actionPoolMock->expects($this->once())
            ->method('getAction')
            ->with(
                $isBundled
                    ? ActionInterface::TYPE_BUNDLED
                    : ActionInterface::TYPE_SINGLE
            )
            ->willReturn($actionMock);
        $actionMock->expects($this->once())
            ->method('pay')
            ->with($paymentMock)
            ->willThrowException($exception);
        $paymentMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_CANCELLED);
        if (!$isBundled) {
            $paymentMock->expects($this->once())
                ->method('getProfile')
                ->willReturn($profileMock);
            $profileMock->expects($this->once())
                ->method('setStatus')
                ->willReturn(ProfileStatus::CANCELLED);
        } else {
            $paymentMock->expects($this->once())
                ->method('getProfile')
                ->willReturn(null);
        }
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $this->engineLoggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                EngineLogger::ENTRY_PAYMENT_STATUS_CHANGE,
                ['payments' => [$paymentMock]],
                ['exception' => $exception]
            );
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isOutstandingDetected' => false])
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    /**
     * @return array
     */
    public function processPayDataProvider()
    {
        return [
            [false, $this->createMock(Payment::class)],
            [true, $this->createMock(Payment::class)],
            [false, null],
            [true, null]
        ];
    }

    /**
     * @return array
     */
    public function processPaymentExceptionDataProvider()
    {
        return [
            [false, false],
            [false, true],
            [true, false],
            [true, true]
        ];
    }

    /**
     * @return array
     */
    public function processExceptionDataProvider()
    {
        return [[false], [true]];
    }
}
