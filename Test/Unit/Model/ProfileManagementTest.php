<?php
namespace Aheadworks\Sarp2\Test\Unit\Model;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultInterface;
use Aheadworks\Sarp2\Model\ProfileManagement;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Exception\OperationIsNotSupportedException;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierPool;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\StatusMap;
use Aheadworks\Sarp2\Engine\Profile\ActionFactory;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\ProviderInterface;
use Aheadworks\Sarp2\Engine\Profile\SchedulerInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Engine\Exception\CouldNotScheduleException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\ProfileManagement
 */
class ProfileManagementTest extends TestCase
{
    /**
     * @var ProfileManagement
     */
    private $profileManagement;

    /**
     * @var SchedulerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulerMock;

    /**
     * @var ProfileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileRepositoryMock;

    /**
     * @var ActionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFactoryMock;

    /**
     * @var ApplierPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $applierPoolMock;

    /**
     * @var ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInfoProviderMock;

    /**
     * @var ScheduledPaymentInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInfoFactoryMock;

    /**
     * @var StatusMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusMapMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->schedulerMock = $this->createMock(SchedulerInterface::class);
        $this->profileRepositoryMock = $this->createMock(ProfileRepositoryInterface::class);
        $this->actionFactoryMock = $this->createMock(ActionFactory::class);
        $this->applierPoolMock = $this->createMock(ApplierPool::class);
        $this->paymentInfoProviderMock = $this->createMock(ProviderInterface::class);
        $this->paymentInfoFactoryMock = $this->createMock(ScheduledPaymentInfoInterfaceFactory::class);
        $this->statusMapMock = $this->createMock(StatusMap::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->profileManagement = $objectManager->getObject(
            ProfileManagement::class,
            [
                'scheduler' => $this->schedulerMock,
                'profileRepository' => $this->profileRepositoryMock,
                'actionFactory' => $this->actionFactoryMock,
                'applierPool' => $this->applierPoolMock,
                'paymentInfoProvider' => $this->paymentInfoProviderMock,
                'paymentInfoFactory' => $this->paymentInfoFactoryMock,
                'statusMap' => $this->statusMapMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * Test schedule method
     */
    public function testSchedule()
    {
        $profileMock = $this->createMock(ProfileInterface::class);
        $profiles = [$profileMock];

        $this->schedulerMock->expects($this->once())
            ->method('schedule')
            ->with($profiles)
            ->willReturn($profiles);

        $this->assertNull($this->profileManagement->schedule($profiles));
    }

    /**
     * Test schedule method if an error occurs
     *
     * @expectedException \Aheadworks\Sarp2\Engine\Exception\CouldNotScheduleException
     * @expectedExceptionMessage Error!
     */
    public function testScheduleException()
    {
        $profileMock = $this->createMock(ProfileInterface::class);
        $profiles = [$profileMock];

        $this->schedulerMock->expects($this->once())
            ->method('schedule')
            ->with($profiles)
            ->willThrowException(new CouldNotScheduleException(__('Error!')));
        $this->expectException(CouldNotScheduleException::class);
        $this->profileManagement->schedule($profiles);
    }

    /**
     * Test changeStatusAction method
     *
     * @param ProfileInterface|\PHPUnit_Framework_MockObject_MockObject|LocalizedException $profileRepositoryResult
     * @param ResultInterface|\PHPUnit_Framework_MockObject_MockObject $validationResultMock
     * @param bool $isApply
     * @param bool|LocalizedException $expectedResult
     * @dataProvider changeStatusActionDataProvider
     */
    public function testChangeStatusAction(
        $profileRepositoryResult,
        $validationResultMock,
        $isApply,
        $expectedResult
    ) {
        $profileId = 1;
        $profileStatus = Status::ACTIVE;

        if ($profileRepositoryResult instanceof \Exception) {
            $this->profileRepositoryMock->expects($this->once())
                ->method('get')
                ->with($profileId)
                ->willThrowException($profileRepositoryResult);
        } else {
            $profileMock = $profileRepositoryResult;
            $this->profileRepositoryMock->expects($this->once())
                ->method('get')
                ->with($profileId)
                ->willReturn($profileMock);

            $actionMock = $this->createMock(ActionInterface::class);
            $this->actionFactoryMock->expects($this->once())
                ->method('create')
                ->with([
                    'type' => ActionInterface::ACTION_TYPE_CHANGE_STATUS,
                    'data' => ['status' => $profileStatus]
                ])
                ->willReturn($actionMock);

            $applierMock = $this->createMock(ApplierInterface::class);
            $this->applierPoolMock->expects($this->once())
                ->method('getApplier')
                ->with(ActionInterface::ACTION_TYPE_CHANGE_STATUS)
                ->willReturn($applierMock);

            $applierMock->expects($this->once())
                ->method('validate')
                ->with($profileMock, $actionMock)
                ->willReturn($validationResultMock);

            if ($isApply) {
                $applierMock->expects($this->once())
                    ->method('apply')
                    ->with($profileMock, $actionMock)
                    ->willReturn($profileMock);
            }
        }

        try {
            $this->assertTrue($this->profileManagement->changeStatusAction($profileId, $profileStatus));
        } catch (\Exception $e) {
            $this->assertEquals($expectedResult, $e);
        }
    }

    /**
     * @return array
     */
    public function changeStatusActionDataProvider()
    {
        return [
            [
                'profileRepositoryResult' => $this->createMock(ProfileInterface::class),
                'validationResultMock' => $this->getValidationResultMock(true, ''),
                'isApply' => true,
                'expectedResult' => true
            ],
            [
                'profileRepositoryResult' => new LocalizedException(__('Error')),
                'validationResultMock' => $this->getValidationResultMock(true, ''),
                'isApply' => false,
                'expectedResult' => new LocalizedException(__('Error'))
            ],
            [
                'profileRepositoryResult' => $this->createMock(ProfileInterface::class),
                'validationResultMock' => $this->getValidationResultMock(false, 'Error!'),
                'isApply' => false,
                'expectedResult' => new OperationIsNotSupportedException(__('Error!'))
            ],
        ];
    }

    /**
     * Get validation result mock
     *
     * @param bool $isValid
     * @param string $message
     * @return ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getValidationResultMock($isValid, $message)
    {
        $validationResultMock = $this->createMock(ResultInterface::class);
        $validationResultMock->expects($this->any())
            ->method('isValid')
            ->willReturn($isValid);
        $validationResultMock->expects($this->any())
            ->method('getMessage')
            ->willReturn($message);

        return $validationResultMock;
    }

    /**
     * Test getNextPaymentInfo method
     *
     * @param $profileStatus
     * @param $isScheduledPayment
     * @dataProvider getNextPaymentInfoDataProvider
     * @throws LocalizedException
     */
    public function testGetNextPaymentInfo($profileStatus, $isScheduledPayment)
    {
        $profileId = 1;

        $profileMock = $this->createMock(ProfileInterface::class);
        $profileMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($profileStatus);
        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willReturn($profileMock);

        $scheduledPaymentInfoMock = $this->createMock(ScheduledPaymentInfoInterface::class);
        if ($isScheduledPayment) {
            $this->paymentInfoProviderMock->expects($this->once())
                ->method('getScheduledPaymentsInfo')
                ->with($profileId)
                ->willReturn($scheduledPaymentInfoMock);
        } else {
            $this->paymentInfoFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($scheduledPaymentInfoMock);
            $scheduledPaymentInfoMock->expects($this->once())
                ->method('setPaymentStatus')
                ->with(ScheduledPaymentInfoInterface::PAYMENT_STATUS_NO_PAYMENT)
                ->willReturnSelf();
        }

        $this->assertSame($scheduledPaymentInfoMock, $this->profileManagement->getNextPaymentInfo($profileId));
    }

    /**
     * @return array
     */
    public function getNextPaymentInfoDataProvider()
    {
        return [
            [
                'profileStatus' => Status::ACTIVE,
                'isScheduledPayment' => true,
            ],
            [
                'profileStatus' => Status::CANCELLED,
                'isScheduledPayment' => false,
            ],
            [
                'profileStatus' => Status::EXPIRED,
                'isScheduledPayment' => false,
            ],
            [
                'profileStatus' => Status::PENDING,
                'isScheduledPayment' => true,
            ],
            [
                'profileStatus' => Status::SUSPENDED,
                'isScheduledPayment' => true,
            ],
        ];
    }

    /**
     * Test getNextPaymentInfo method if no profile found
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testGetNextPaymentInfoException()
    {
        $profileId = 1;

        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willThrowException(new LocalizedException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->profileManagement->getNextPaymentInfo($profileId);
    }

    /**
     * Test getAllowedStatuses method
     */
    public function testGetAllowedStatuses()
    {
        $profileId = 1;
        $profileStatus = Status::ACTIVE;
        $allowedStatuses = [];

        $profileMock = $this->createMock(ProfileInterface::class);
        $profileMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($profileStatus);
        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willReturn($profileMock);

        $this->statusMapMock->expects($this->once())
            ->method('getAllowedStatuses')
            ->with($profileStatus)
            ->willReturn($allowedStatuses);

        $this->assertEquals($allowedStatuses, $this->profileManagement->getAllowedStatuses($profileId));
    }

    /**
     * Test getAllowedStatuses method if no profile found
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testGetAllowedStatusesException()
    {
        $profileId = 1;

        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willThrowException(new LocalizedException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->profileManagement->getAllowedStatuses($profileId);
    }

    /**
     * Test isCustomerSubscribedOnProduct method
     *
     * @param int $customerId
     * @param int $productId
     * @param ProfileInterface[] $items
     * @dataProvider dataProviderIsCustomerSubscribedOnProduct
     */
    public function testIsCustomerSubscribedOnProduct($customerId, $productId, $items)
    {
        $this->searchCriteriaBuilderMock->expects($this->exactly(3))
            ->method('addFilter')
            ->withConsecutive(
                [ProfileInterface::CUSTOMER_ID, $customerId],
                [ProfileInterface::STATUS, [Status::EXPIRED, Status::CANCELLED], 'nin'],
                [ProfileItemInterface::PRODUCT_ID, $productId]
            )->willReturnOnConsecutiveCalls(
                $this->returnSelf(),
                $this->returnSelf(),
                $this->returnSelf()
            );
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setCurrentPage')
            ->with(1)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->with(1)
            ->willReturnSelf();

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $profileSearchResultMock = $this->getMockForAbstractClass(ProfileSearchResultsInterface::class);
        $profileSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);

        $this->profileRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($profileSearchResultMock);
        $result = count($items) > 0;

        $this->assertEquals($result, $this->profileManagement->isCustomerSubscribedOnProduct($customerId, $productId));
    }

    /**
     * Retirieve data provider for testIsCustomerSubscribedOnProduct method
     *
     * @return array
     */
    public function dataProviderIsCustomerSubscribedOnProduct()
    {
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        return [
            [1, 1, []],
            [2, 1, [$profileMock]]
        ];
    }
}
