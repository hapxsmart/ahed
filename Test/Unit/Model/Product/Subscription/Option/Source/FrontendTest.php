<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Option\Source;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Model\Plan\Checker as PlanChecker;
use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Finder as SubscriptionOptionFinder;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Source\Frontend as OptionFrontendSource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\Option\Source\Frontend
 */
class FrontendTest extends TestCase
{
    /**
     * @var OptionFrontendSource
     */
    private $model;

    /**
     * @var IsSubscription|\PHPUnit_Framework_MockObject_MockObject
     */
    private $isSubscriptionCheckerMock;

    /**
     * @var SubscriptionOptionFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionOptionFinderMock;

    /**
     * @var PlanChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planCheckerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->isSubscriptionCheckerMock = $this->createMock(IsSubscription::class);
        $this->subscriptionOptionFinderMock = $this->createMock(SubscriptionOptionFinder::class);
        $this->planCheckerMock = $this->createMock(PlanChecker::class);

        $this->model = $objectManager->getObject(
            OptionFrontendSource::class,
            [
                'isSubscriptionChecker' => $this->isSubscriptionCheckerMock,
                'subscriptionOptionFinder' => $this->subscriptionOptionFinderMock,
                'planChecker' => $this->planCheckerMock,
            ]
        );
    }

    /**
     * Test getOptionArray method
     *
     * @param bool $isSubscriptionOnly
     * @param SubscriptionOptionInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $options
     * @param array $planMap
     * @param $expectedResult
     * @dataProvider getOptionArrayDataProvider
     */
    public function testGetOptionArray($isSubscriptionOnly, $options, $planMap, $expectedResult)
    {
        $productId = 125;

        $this->isSubscriptionCheckerMock->expects($this->any())
            ->method('checkById')
            ->with($productId, true)
            ->willReturn($isSubscriptionOnly);

        $this->subscriptionOptionFinderMock->expects($this->any())
            ->method('getSortedOptions')
            ->with($productId)
            ->willReturn($options);

        $this->planCheckerMock->expects($this->any())
            ->method('isEnabled')
            ->willReturnMap($planMap);

        $this->assertEquals($expectedResult, $this->model->getOptionArray($productId));
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getOptionArrayDataProvider()
    {
        return [
            [
                'isSubscriptionOnly' => false,
                'options' => [],
                'planMap' => [],
                'expectedResult' => [
                    __('One-off purchase (No subscription)')
                ]
            ],
            [
                'isSubscriptionOnly' => false,
                'options' => [
                    $this->getOptionMock(121, 11, 'Plan 11'),
                    $this->getOptionMock(122, 22, 'Plan 22'),
                ],
                'planMap' => [
                    [11, true],
                    [22, false]
                ],
                'expectedResult' => [
                    __('One-off purchase (No subscription)'),
                    121 => 'Plan 11'
                ]
            ],
            [
                'isSubscriptionOnly' => true,
                'options' => [
                    $this->getOptionMock(121, 11, 'Plan 11'),
                    $this->getOptionMock(122, 22, 'Plan 22'),
                ],
                'planMap' => [
                    [11, true],
                    [22, false]
                ],
                'expectedResult' => [
                    121 => 'Plan 11'
                ]
            ],
        ];
    }

    /**
     * Test getOptionArray method if product check error occurs
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testGetOptionArrayProductCheckError()
    {
        $productId = 125;

        $this->isSubscriptionCheckerMock->expects($this->any())
            ->method('checkById')
            ->with($productId, true)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));
        $this->expectException(NoSuchEntityException::class);
        $this->subscriptionOptionFinderMock->expects($this->never())
            ->method('getSortedOptions');

        $this->planCheckerMock->expects($this->never())
            ->method('isEnabled');

        $this->model->getOptionArray($productId);
    }

    /**
     * Test getOptionArray method if an error on getting option list occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testGetOptionArrayOptionListError()
    {
        $productId = 125;

        $this->isSubscriptionCheckerMock->expects($this->any())
            ->method('checkById')
            ->with($productId, true)
            ->willReturn(true);

        $this->subscriptionOptionFinderMock->expects($this->any())
            ->method('getSortedOptions')
            ->with($productId)
            ->willThrowException(new LocalizedException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->planCheckerMock->expects($this->never())
            ->method('isEnabled');

        $this->model->getOptionArray($productId);
    }

    /**
     * Test getPlanOptionArray method
     *
     * @param SubscriptionOptionInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $options
     * @param array $planMap
     * @param $expectedResult
     * @dataProvider getPlanOptionArrayDataProvider
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetPlanOptionArray($options, $planMap, $expectedResult)
    {
        $productId = 125;

        $this->subscriptionOptionFinderMock->expects($this->any())
            ->method('getSortedOptions')
            ->with($productId)
            ->willReturn($options);

        $this->planCheckerMock->expects($this->any())
            ->method('isEnabled')
            ->willReturnMap($planMap);

        $this->assertEquals($expectedResult, $this->model->getPlanOptionArray($productId));
    }

    /**
     * @return array
     */
    public function getPlanOptionArrayDataProvider()
    {
        return [
            [
                'options' => [],
                'planMap' => [],
                'expectedResult' => []
            ],
            [
                'options' => [
                    $this->getOptionMock(121, 11, 'Plan 11'),
                ],
                'planMap' => [
                    [11, true],
                ],
                'expectedResult' => [
                    11 => 'Plan 11'
                ]
            ],
            [
                'options' => [
                    $this->getOptionMock(121, 11, 'Plan 11'),
                    $this->getOptionMock(122, 22, 'Plan 22'),
                    $this->getOptionMock(123, 33, 'Plan 33'),
                ],
                'planMap' => [
                    [11, true],
                    [22, false],
                    [33, true]
                ],
                'expectedResult' => [
                    11 => 'Plan 11',
                    33 => 'Plan 33'
                ]
            ],
        ];
    }

    /**
     * Test getPlanOptionArray method an error on getting option list occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testGetPlanOptionArrayOptionListError()
    {
        $productId = 125;

        $this->subscriptionOptionFinderMock->expects($this->any())
            ->method('getSortedOptions')
            ->with($productId)
            ->willThrowException(new LocalizedException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->planCheckerMock->expects($this->never())
            ->method('isEnabled');

        $this->model->getPlanOptionArray($productId);
    }

    /**
     * Get option mock
     *
     * @param int $optionId
     * @param int $planId
     * @param string $frontendTitle
     * @return SubscriptionOptionInterface|\PHPUnit\Framework\MockObject\MockObject
     * @throws \ReflectionException
     */
    private function getOptionMock($optionId, $planId, $frontendTitle)
    {
        $optionMock = $this->createMock(SubscriptionOptionInterface::class);
        $optionMock->expects($this->any())
            ->method('getOptionId')
            ->willReturn($optionId);
        $optionMock->expects($this->any())
            ->method('getPlanId')
            ->willReturn($planId);
        $optionMock->expects($this->any())
            ->method('getFrontendTitle')
            ->willReturn($frontendTitle);

        return $optionMock;
    }
}
