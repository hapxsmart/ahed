<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Processor\State\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Profile\StatusHandler;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Processor\State\Profile\StatusHandler
 */
class StatusHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StatusHandler
     */
    private $statusHandler;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->statusHandler = $objectManager->getObject(
            StatusHandler::class,
            ['logger' => $this->loggerMock]
        );
    }

    /**
     * @param ScheduleInterface|\PHPUnit_Framework_MockObject_MockObject $scheduleMock
     * @param ProfileInterface|\PHPUnit_Framework_MockObject_MockObject$profileMock
     * @param bool $isStatusUpdated
     * @param string|null $expectedStatus
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        $scheduleMock,
        $profileMock,
        $isStatusUpdated = false,
        $expectedStatus = null
    ) {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $paymentMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        if ($isStatusUpdated) {
            $profileMock->expects($this->once())
                ->method('setStatus')
                ->with($expectedStatus);
            $this->loggerMock->expects($this->once())
                ->method('traceProcessing')
                ->with(
                    LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                    ['payment' => $paymentMock],
                    ['profile' => $profileMock]
                );
        }

        $this->assertSame($paymentMock, $this->statusHandler->handle($paymentMock));
    }

    /**
     * @return array
     */
    public function handleDataProvider()
    {
        return [
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 2,
                        'getRegularCount' => 0,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::PENDING]),
                false,
                null
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => true,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 2,
                        'getRegularCount' => 0,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::PENDING]),
                true,
                Status::ACTIVE
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => true,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 2,
                        'getRegularCount' => 0,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                false,
                null
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 1,
                        'getTrialTotalCount' => 2,
                        'getRegularCount' => 0,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::PENDING]),
                true,
                Status::ACTIVE
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 1,
                        'getTrialTotalCount' => 0,
                        'getRegularCount' => 0,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::PENDING]),
                false,
                null
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 1,
                        'getTrialTotalCount' => 2,
                        'getRegularCount' => 0,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                false,
                null
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 0,
                        'getRegularCount' => 1,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::PENDING]),
                true,
                Status::ACTIVE
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 0,
                        'getRegularCount' => 1,
                        'getRegularTotalCount' => 0
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::PENDING]),
                true,
                Status::ACTIVE
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 0,
                        'getRegularCount' => 1,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                false,
                null
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => true,
                        'getTrialCount' => 2,
                        'getTrialTotalCount' => 2,
                        'getRegularCount' => 10,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                true,
                Status::EXPIRED
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 2,
                        'getTrialTotalCount' => 2,
                        'getRegularCount' => 10,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                true,
                Status::EXPIRED
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 0,
                        'getRegularCount' => 10,
                        'getRegularTotalCount' => 10
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                true,
                Status::EXPIRED
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => true,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 0,
                        'getRegularCount' => 0,
                        'getRegularTotalCount' => 0
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                false,
                null
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 2,
                        'getTrialTotalCount' => 2,
                        'getRegularCount' => 0,
                        'getRegularTotalCount' => 0
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                false,
                null
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'isInitialPaid' => false,
                        'getTrialCount' => 0,
                        'getTrialTotalCount' => 0,
                        'getRegularCount' => 10,
                        'getRegularTotalCount' => 0
                    ]
                ),
                $this->createConfiguredMock(ProfileInterface::class, ['getStatus' => Status::ACTIVE]),
                false,
                null
            ]
        ];
    }
}
