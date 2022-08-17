<?php
namespace Aheadworks\Sarp2\Test\Unit\Observer;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderSearchResultsInterface;
use Aheadworks\Sarp2\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Observer\AssignProfileToCustomerObserver;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Observer\AssignProfileToCustomerObserver
 */
class AssignProfileToCustomerObserverTest extends TestCase
{
    /**
     * @var AssignProfileToCustomerObserver
     */
    private $assignProfileToCustomerObserver;

    /**
     * @var ProfileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileRepositoryMock;

    /**
     * @var ProfileOrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileOrderRepositoryMock;

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

        $this->profileRepositoryMock = $this->createMock(ProfileRepositoryInterface::class);
        $this->profileOrderRepositoryMock = $this->createMock(ProfileOrderRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->assignProfileToCustomerObserver = $objectManager->getObject(
            AssignProfileToCustomerObserver::class,
            [
                'profileRepository' => $this->profileRepositoryMock,
                'profileOrderRepository' => $this->profileOrderRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            ]
        );
    }

    /**
     * Test execute method
     *
     * @param array $delegateData
     * @param array $profileIds
     * @param array $profileOrderItems
     * @param int $customerId
     * @param ProfileInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $profileMocks
     * @param bool $isProfileCustomerDefined
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        $delegateData,
        $customerId,
        $profileOrderItems,
        $profileIds,
        $profileMocks,
        $isProfileCustomerDefined
    ) {
        $customerMock = $this->createMock(CustomerInterface::class);

        $eventMock = $this->createMock(Event::class);
        $eventMock->expects($this->exactly(2))
            ->method('getData')
            ->withConsecutive(['customer_data_object'], ['delegate_data'])
            ->willReturnOnConsecutiveCalls($customerMock, $delegateData);

        $observerMock = $this->createMock(Observer::class);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        if (isset($delegateData[AssignProfileToCustomerObserver::SALES_ASSIGN_ORDER_ID_KEY])) {
            $searchCriteriaMock = $this->createMock(SearchCriteria::class);
            $this->searchCriteriaBuilderMock->expects($this->once())
                ->method('addFilter')
                ->with(
                    ProfileOrderInterface::ORDER_ID,
                    $delegateData[AssignProfileToCustomerObserver::SALES_ASSIGN_ORDER_ID_KEY],
                    'eq'
                )
                ->willReturnSelf();
            $this->searchCriteriaBuilderMock->expects($this->once())
                ->method('create')
                ->willReturn($searchCriteriaMock);

            $profileOrderSearchResultMock = $this->createMock(ProfileOrderSearchResultsInterface::class);
            $profileOrderSearchResultMock->expects($this->once())
                ->method('getItems')
                ->willReturn($profileOrderItems);
            $this->profileOrderRepositoryMock->expects($this->once())
                ->method('getList')
                ->with($searchCriteriaMock)
                ->willReturn($profileOrderSearchResultMock);

            if (count($profileOrderItems) > 0) {
                if ($profileMocks[0] instanceof \Exception) {
                    $this->profileRepositoryMock->expects($this->once())
                        ->method('get')
                        ->with($profileIds[0])
                        ->willThrowException($profileMocks[0]);
                } else {
                    if (count($profileIds) == 1) {
                        $this->profileRepositoryMock->expects($this->once())
                            ->method('get')
                            ->with($profileIds[0])
                            ->willReturn($profileMocks[0]);
                    } else {
                        $this->profileRepositoryMock->expects($this->exactly(2))
                            ->method('get')
                            ->withConsecutive([$profileIds[0]], [$profileIds[1]])
                            ->willReturnOnConsecutiveCalls($profileMocks[0], $profileMocks[1]);
                    }
                }

                if (!$isProfileCustomerDefined) {
                    if (count($profileIds) == 1) {
                        $customerMock->expects($this->once())
                            ->method('getId')
                            ->willReturn($customerId);

                        $this->profileRepositoryMock->expects($this->once())
                            ->method('save')
                            ->with($profileMocks[0])
                            ->willReturn($profileMocks[0]);
                    } else {
                        $customerMock->expects($this->exactly(2))
                            ->method('getId')
                            ->willReturn($customerId);

                        $this->profileRepositoryMock->expects($this->exactly(2))
                            ->method('save')
                            ->withConsecutive([$profileMocks[0]], [$profileMocks[1]])
                            ->willReturnOnConsecutiveCalls($profileMocks[0], $profileMocks[1]);
                    }
                }
            }
        }

        $this->assertNull($this->assignProfileToCustomerObserver->execute($observerMock));
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        $orderId = 100;
        $profileOneId = 10;
        $profileTwoId = 11;
        $customerId = 20;

        return [
            [
                'delegateData' => [
                    AssignProfileToCustomerObserver::SALES_ASSIGN_ORDER_ID_KEY => $orderId
                ],
                'customerId' => $customerId,
                'profileOrderItems' => [$this->getProfileOrderMock($profileOneId)],
                'profileIds' => [$profileOneId],
                'profileMocks' => [$this->getProfileMock($customerId, false)],
                'isProfileCustomerDefined' => false
            ],
            [
                'delegateData' => [
                    AssignProfileToCustomerObserver::SALES_ASSIGN_ORDER_ID_KEY => $orderId
                ],
                'customerId' => $customerId,
                'profileOrderItems' => [
                    $this->getProfileOrderMock($profileOneId),
                    $this->getProfileOrderMock($profileTwoId)
                ],
                'profileIds' => [$profileOneId, $profileTwoId],
                'profileMocks' => [
                    $this->getProfileMock($customerId, false),
                    $this->getProfileMock($customerId, false)
                ],
                'isProfileCustomerDefined' => false
            ],
            [
                'delegateData' => [
                    AssignProfileToCustomerObserver::SALES_ASSIGN_ORDER_ID_KEY => $orderId
                ],
                'customerId' => $customerId,
                'profileOrderItems' => [$this->getProfileOrderMock($profileOneId)],
                'profileIds' => [$profileOneId],
                'profileMocks' => [$this->getProfileMock($customerId, true)],
                'isProfileCustomerDefined' => true
            ],
            [
                'delegateData' => [
                    AssignProfileToCustomerObserver::SALES_ASSIGN_ORDER_ID_KEY => $orderId
                ],
                'customerId' => $customerId,
                'profileOrderItems' => [
                    $this->getProfileOrderMock($profileOneId),
                    $this->getProfileOrderMock($profileTwoId)
                ],
                'profileIds' => [$profileOneId, $profileTwoId],
                'profileMocks' => [
                    $this->getProfileMock($customerId, true),
                    $this->getProfileMock($customerId, true)
                ],
                'isProfileCustomerDefined' => true
            ],
            [
                'delegateData' => [
                    AssignProfileToCustomerObserver::SALES_ASSIGN_ORDER_ID_KEY => $orderId
                ],
                'customerId' => $customerId,
                'profileOrderItems' => [$this->getProfileOrderMock($profileOneId)],
                'profileIds' => [$profileOneId],
                'profileMocks' => [new NoSuchEntityException(__('No such entity!'))],
                'isProfileCustomerDefined' => true
            ],
            [
                'delegateData' => [
                    AssignProfileToCustomerObserver::SALES_ASSIGN_ORDER_ID_KEY => $orderId
                ],
                'customerId' => $customerId,
                'profileOrderItems' => [],
                'profileIds' => [$profileOneId],
                'profileMocks' => [null],
                'isProfileCustomerDefined' => false
            ],
            [
                'delegateData' => [],
                'customerId' => $customerId,
                'profileOrderItems' => [],
                'profileIds' => [null],
                'profileMocks' => [null],
                'isProfileCustomerDefined' => false
            ],
            [
                'delegateData' => null,
                'customerId' => $customerId,
                'profileOrderItems' => [],
                'profileIds' => [null],
                'profileMocks' => [null],
                'isProfileCustomerDefined' => false
            ],
        ];
    }

    /**
     * Get profile order mock
     *
     * @param int $profileId
     * @return ProfileOrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProfileOrderMock($profileId)
    {
        $profileOrderMock = $this->createMock(ProfileOrderInterface::class);
        $profileOrderMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);

        return $profileOrderMock;
    }

    /**
     * Get profile mock
     *
     * @param int $customerId
     * @param bool $isCustomerDefined
     * @return ProfileInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProfileMock($customerId, $isCustomerDefined)
    {
        $profileMock = $this->createMock(ProfileInterface::class);
        if ($isCustomerDefined) {
            $profileMock->expects($this->once())
                ->method('getCustomerId')
                ->willReturn($customerId);
        } else {
            $profileMock->expects($this->once())
                ->method('getCustomerId')
                ->willReturn(null);
            $profileMock->expects($this->once())
                ->method('setCustomerId')
                ->with($customerId)
                ->willReturnSelf();
            $profileMock->expects($this->once())
                ->method('setCustomerIsGuest')
                ->with(false)
                ->willReturnSelf();
        }

        return $profileMock;
    }
}
