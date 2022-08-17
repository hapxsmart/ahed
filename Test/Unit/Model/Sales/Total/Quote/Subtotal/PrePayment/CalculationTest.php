<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Source\StartDateType;
use Aheadworks\Sarp2\Model\Plan\DateResolver;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation\Result;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation\ResultFactory;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Initial;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Regular;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Trial;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation
 */
class CalculationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Calculation
     */
    private $calculation;

    /**
     * @var SubscriptionOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionRepositoryMock;

    /**
     * @var DateResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $startDateResolverMock;

    /**
     * @var Initial|\PHPUnit_Framework_MockObject_MockObject
     */
    private $initialGroupMock;

    /**
     * @var Trial|\PHPUnit_Framework_MockObject_MockObject
     */
    private $trialGroupMock;

    /**
     * @var Regular|\PHPUnit_Framework_MockObject_MockObject
     */
    private $regularGroupMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->optionRepositoryMock = $this->createMock(SubscriptionOptionRepositoryInterface::class);
        $this->startDateResolverMock = $this->createMock(DateResolver::class);
        $this->initialGroupMock = $this->createMock(Initial::class);
        $this->trialGroupMock = $this->createMock(Trial::class);
        $this->regularGroupMock = $this->createMock(Regular::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->calculation = $objectManager->getObject(
            Calculation::class,
            [
                'optionRepository' => $this->optionRepositoryMock,
                'startDateResolver' => $this->startDateResolverMock,
                'initialGroup' => $this->initialGroupMock,
                'trialGroup' => $this->trialGroupMock,
                'regularGroup' => $this->regularGroupMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    /**
     * @param float $initialFee
     * @param float $trialPrice
     * @param float $regularPrice
     * @param bool $isInitialFeeEnabled
     * @param bool $isTrialPeriodEnabled
     * @param float $expectedAmount
     * @param array $expectedSumComponents
     * @dataProvider calculateItemPriceDataProvider
     */
    public function testCalculateItemPrice(
        $initialFee,
        $trialPrice,
        $regularPrice,
        $isInitialFeeEnabled,
        $isTrialPeriodEnabled,
        $expectedAmount,
        $expectedSumComponents
    ) {
        $subscriptionOptionId = 1;
        $useBaseCurrency = false;

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(ItemInterface::class);
        $optionMock = $this->createMock(OptionInterface::class);
        $subscriptionOptionMock = $this->createMock(SubscriptionOptionInterface::class);
        $planMock = $this->createMock(PlanInterface::class);
        $definitionMock = $this->createMock(PlanDefinitionInterface::class);
        $resultMock = $this->createMock(Result::class);

        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($subscriptionOptionId);
        $this->optionRepositoryMock->expects($this->once())
            ->method('get')
            ->with($subscriptionOptionId)
            ->willReturn($subscriptionOptionMock);
        $subscriptionOptionMock->expects($this->once())
            ->method('getPlan')
            ->willReturn($planMock);
        $planMock->expects($this->once())
            ->method('getDefinition')
            ->willReturn($definitionMock);
        $definitionMock->expects($this->once())
            ->method('getIsInitialFeeEnabled')
            ->willReturn($isInitialFeeEnabled);
        if ($isInitialFeeEnabled) {
            $this->initialGroupMock->expects($this->once())
                ->method('getItemPrice')
                ->with($itemMock, $useBaseCurrency)
                ->willReturn($initialFee);
        }
        $definitionMock->expects($this->once())
            ->method('getStartDateType')
            ->willReturn(StartDateType::MOMENT_OF_PURCHASE);
        $definitionMock->expects($this->once())
            ->method('getStartDateDayOfMonth')
            ->willReturn(null);
        $this->startDateResolverMock->expects($this->once())
            ->method('isToday')
            ->with(StartDateType::MOMENT_OF_PURCHASE, null)
            ->willReturn(true);
        $this->trialGroupMock->expects($this->once())
            ->method('getItemPrice')
            ->with($itemMock, $useBaseCurrency)
            ->willReturn($trialPrice);
        if ($trialPrice > 0) {
            $definitionMock->expects($this->once())
                ->method('getIsTrialPeriodEnabled')
                ->willReturn($isTrialPeriodEnabled);
        }
        if (!($trialPrice > 0 && $isTrialPeriodEnabled)) {
            $this->regularGroupMock->expects($this->any())
                ->method('getItemPrice')
                ->with($itemMock, $useBaseCurrency)
                ->willReturn($regularPrice);
        }
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'amount' => $expectedAmount,
                    'sumComponents' => $expectedSumComponents
                ]
            )
            ->willReturn($resultMock);

        $this->assertSame(
            $resultMock,
            $this->calculation->calculateItemPrice($itemMock, $useBaseCurrency)
        );
    }

    public function testCalculateItemPriceNonSubscription()
    {
        $useBaseCurrency = false;

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(ItemInterface::class);
        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn(null);

        $this->assertEquals(0, $this->calculation->calculateItemPrice($itemMock, $useBaseCurrency));
    }

    /**
     * @return array
     */
    public function calculateItemPriceDataProvider()
    {
        return [
            [5.00, 8.00, 10.00, true, true, 13.00, ['initial', 'trial']],
            [0.00, 8.00, 10.00, false, true, 8.00, ['trial']],
            [5.00, 0.00, 10.00, true, false, 15.00, ['initial', 'regular']],
            [0.00, 0.00, 10.00, false, false, 10.00, ['regular']]
        ];
    }
}
