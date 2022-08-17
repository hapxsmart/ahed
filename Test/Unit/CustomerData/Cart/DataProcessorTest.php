<?php
namespace Aheadworks\Sarp2\Test\Unit\CustomerData\Cart;

use Aheadworks\Sarp2\CustomerData\Cart\DataProcessor;
use Aheadworks\Sarp2\CustomerData\Cart\ItemDataProcessor;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Test for \Aheadworks\Sarp2\CustomerData\Cart\DataProcessor
 */
class DataProcessorTest extends TestCase
{
    /**
     * @var DataProcessor
     */
    private $dataProcessor;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var ItemDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemDataProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->itemDataProcessorMock = $this->createMock(ItemDataProcessor::class);

        $this->dataProcessor = $objectManager->getObject(
            DataProcessor::class,
            [
                'checkoutSession' => $this->checkoutSessionMock,
                'itemDataProcessor' => $this->itemDataProcessorMock,
            ]
        );
    }

    /**
     * Test process method
     *
     * @param array $data
     * @param Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
     * @param array $expectedResult
     * @dataProvider processDataProvider
     */
    public function testProcess($data, $itemMock, $expectedResult)
    {
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([$itemMock]);

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->itemDataProcessorMock->expects($this->any())
            ->method('process')
            ->with($itemMock)
            ->willReturnCallback(
                function ($itemMock, $itemData) {
                    $itemData['field'] = 'value_modified';
                    return $itemData;
                }
            );

        $this->assertEquals($expectedResult, $this->dataProcessor->process($data));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'data' => [
                    'items' => [
                        ['item_id' => 1, 'field' => 'value'],
                        ['item_id' => 2, 'field' => 'value']
                    ]
                ],
                'itemMock' => $this->createConfiguredMock(Item::class, ['getId' => 2]),
                'expectedResult' => [
                    'items' => [
                        ['item_id' => 1, 'field' => 'value'],
                        ['item_id' => 2, 'field' => 'value_modified']
                    ]
                ]
            ],
            [
                'data' => [
                    'items' => [
                        ['item_id' => 1, 'field' => 'value'],
                        ['item_id' => 3, 'field' => 'value']
                    ]
                ],
                'itemMock' => $this->createConfiguredMock(Item::class, ['getId' => 2]),
                'expectedResult' => [
                    'items' => [
                        ['item_id' => 1, 'field' => 'value'],
                        ['item_id' => 3, 'field' => 'value']
                    ]
                ]
            ],
            [
                'data' => [
                    'items' => []
                ],
                'itemMock' => $this->createConfiguredMock(Item::class, ['getId' => 2]),
                'expectedResult' => [
                    'items' => []
                ]
            ],
            [
                'data' => [
                    'items' => [[]]
                ],
                'itemMock' => $this->createConfiguredMock(Item::class, ['getId' => 2]),
                'expectedResult' => [
                    'items' => [[]]
                ]
            ]
        ];
    }
}
