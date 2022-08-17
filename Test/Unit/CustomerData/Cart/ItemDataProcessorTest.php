<?php
namespace Aheadworks\Sarp2\Test\Unit\CustomerData\Cart;

use Aheadworks\Sarp2\CustomerData\Cart\ItemDataProcessor;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription as IsSubscriptionChecker;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;

/**
 * Test for \Aheadworks\Sarp2\CustomerData\Cart\ItemDataProcessor
 */
class ItemDataProcessorTest extends TestCase
{
    /**
     * @var ItemDataProcessor
     */
    private $itemDataProcessor;

    /**
     * @var IsSubscriptionChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $isSubscriptionCheckerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->isSubscriptionCheckerMock = $this->createMock(IsSubscriptionChecker::class);

        $this->itemDataProcessor = $objectManager->getObject(
            ItemDataProcessor::class,
            [
                'isSubscriptionChecker' => $this->isSubscriptionCheckerMock,
            ]
        );
    }

    /**
     * Test process method
     *
     * @param array $data
     * @param bool $isSubscription
     * @param array $expectedResult
     * @dataProvider processDataProvider
     */
    public function testProcess($data, $isSubscription, $expectedResult)
    {
        $itemMock = $this->createMock(Item::class);

        $this->isSubscriptionCheckerMock->expects($this->once())
            ->method('check')
            ->with($itemMock)
            ->willReturn($isSubscription);

        $this->assertEquals($expectedResult, $this->itemDataProcessor->process($itemMock, $data));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'data' => [
                    'item_id' => 1,
                    'field' => 'value'
                ],
                'isSubscription' => true,
                '$expectedResult' => [
                    'item_id' => 1,
                    'field' => 'value',
                    'aw_sarp_is_subscription' => true
                ]
            ],
            [
                'data' => [
                    'item_id' => 1,
                    'field' => 'value'
                ],
                'isSubscription' => false,
                '$expectedResult' => [
                    'item_id' => 1,
                    'field' => 'value',
                    'aw_sarp_is_subscription' => false
                ]
            ],
        ];
    }
}
