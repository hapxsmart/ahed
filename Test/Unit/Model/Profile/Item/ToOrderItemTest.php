<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Profile\Item;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Profile\Item\ToOrderItem;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Test for \Aheadworks\Sarp2\Model\Profile\Item\ToOrderItem
 */
class ToOrderItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ToOrderItem
     */
    private $converter;

    /**
     * @var OrderItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemFactoryMock;

    /**
     * @var Copy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectCopyServiceMock;

    /**
     * @var CopySelf|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selfCopyServiceMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var TaxConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->orderItemFactoryMock = $this->createMock(OrderItemInterfaceFactory::class);
        $this->objectCopyServiceMock = $this->createMock(Copy::class);
        $this->selfCopyServiceMock = $this->createMock(CopySelf::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);

        $this->converter = $objectManager->getObject(
            ToOrderItem::class,
            [
                'orderItemFactory' => $this->orderItemFactoryMock,
                'objectCopyService' => $this->objectCopyServiceMock,
                'selfCopyService' => $this->selfCopyServiceMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    /**
     * Test convert method
     *
     * @param array $genericData
     * @param array $paymentTypeData
     * @param string $paymentType
     * @param array $extraData
     * @param bool $isPriceIncludesTax
     * @param array $expectedItemData
     * @dataProvider convertDataProvider
     * @throws \ReflectionException
     */
    public function testConvert(
        $genericData,
        $paymentTypeData,
        $paymentType,
        $extraData,
        $isPriceIncludesTax,
        $expectedItemData
    ) {
        $storeId = 10;
        $selfCopyMapExcludeTax = ['price' => 'original_price'];
        $selfCopyMapIncludeTax = ['price_incl_tax' => 'original_price'];

        /** @var ProfileItemInterface|\PHPUnit_Framework_MockObject_MockObject $profileItemMock */
        $profileItemMock = $this->createMock(ProfileItemInterface::class);
        $orderItemMock = $this->createMock(Item::class);
        $productOptionMock = $this->createMock(ProductOptionInterface::class);
        $productOptions = [$productOptionMock];

        $this->setProperty('selfCopyMapExcludeTax', $selfCopyMapExcludeTax);
        $this->setProperty('selfCopyMapIncludeTax', $selfCopyMapIncludeTax);

        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($profileItemMock, ProfileItemInterface::class)
            ->willReturn($expectedItemData);

        $this->objectCopyServiceMock->expects($this->exactly(2))
            ->method('getDataFromFieldset')
            ->withConsecutive(
                [
                    'aw_sarp2_convert_profile_item',
                    'to_order_item',
                    $profileItemMock,
                    'global'
                ],
                [
                    'aw_sarp2_convert_profile_item',
                    'to_order_item_' . $paymentType,
                    $profileItemMock,
                    'global'
                ]
            )->willReturnOnConsecutiveCalls($genericData, $paymentTypeData);

        $profileItemMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->taxConfigMock->expects($this->once())
            ->method('priceIncludesTax')
            ->with($storeId)
            ->willReturn($isPriceIncludesTax);

        if ($isPriceIncludesTax) {
            $this->selfCopyServiceMock->expects($this->once())
                ->method('copyByMap')
                ->with(
                    array_merge($genericData, $paymentTypeData),
                    $selfCopyMapIncludeTax
                )
                ->willReturnArgument(0);
        } else {
            $this->selfCopyServiceMock->expects($this->once())
                ->method('copyByMap')
                ->with(
                    array_merge($genericData, $paymentTypeData),
                    $selfCopyMapExcludeTax
                )
                ->willReturnArgument(0);
        }

        $this->orderItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderItemMock);

        $this->dataObjectHelperMock->expects($this->exactly(2))
            ->method('populateWithArray')
            ->withConsecutive(
                [
                    $profileItemMock,
                    $expectedItemData,
                    ProfileItemInterface::class
                ],
                [
                    $orderItemMock,
                    $expectedItemData,
                    OrderItemInterface::class
                ]
            );
        $profileItemMock->expects($this->once())
            ->method('getProductOptions')
            ->willReturn($productOptions);
        $orderItemMock->expects($this->once())
            ->method('setProductOptions')
            ->willReturnSelf();

        $this->assertSame(
            $orderItemMock,
            $this->converter->convert($profileItemMock, $paymentType, $extraData)
        );
    }

    /**
     * @return array
     */
    public function convertDataProvider()
    {
        $parentItem = $this->createMock(Item::class);
        return [
            [
                'genericData' => ['product_id' => 1],
                'paymentTypeData' => ['price' => 5.00, 'price_incl_tax' => 5.50],
                'paymentType' => PaymentInterface::PERIOD_REGULAR,
                'extraData' => [],
                'isPriceIncludesTax' => false,
                'expectedItemData' => ['product_id' => 1, 'price' => 5.00, 'price_incl_tax' => 5.50]
            ],
            [
                'genericData' => ['product_id' => 1],
                'paymentTypeData' => ['price' => 5.00, 'price_incl_tax' => 5.50],
                'paymentType' => PaymentInterface::PERIOD_REGULAR,
                'extraData' => ['parent_item' => $parentItem],
                'isPriceIncludesTax' => false,
                'expectedItemData' => [
                    'product_id' => 1,
                    'price' => 5.00,
                    'price_incl_tax' => 5.50,
                    'parent_item' => $parentItem
                ]
            ],
            [
                'genericData' => ['product_id' => 1],
                'paymentTypeData' => ['price' => 5.00, 'price_incl_tax' => 5.50],
                'paymentType' => PaymentInterface::PERIOD_INITIAL,
                'extraData' => [],
                'isPriceIncludesTax' => false,
                'expectedItemData' => ['product_id' => 1, 'price' => 5.00, 'price_incl_tax' => 5.50]
            ],
            [
                'genericData' => ['product_id' => 1],
                'paymentTypeData' => ['price' => 5.00, 'price_incl_tax' => 5.50],
                'paymentType' => PaymentInterface::PERIOD_TRIAL,
                'extraData' => [],
                'isPriceIncludesTax' => false,
                'expectedItemData' => ['product_id' => 1, 'price' => 5.00, 'price_incl_tax' => 5.50]
            ],
            [
                'genericData' => ['product_id' => 1],
                'paymentTypeData' => ['price' => 5.00, 'price_incl_tax' => 5.50],
                'paymentType' => PaymentInterface::PERIOD_REGULAR,
                'extraData' => [],
                'isPriceIncludesTax' => true,
                'expectedItemData' => ['product_id' => 1, 'price' => 5.00, 'price_incl_tax' => 5.50]
            ],
            [
                'genericData' => ['product_id' => 1],
                'paymentTypeData' => ['price' => 5.00, 'price_incl_tax' => 5.50],
                'paymentType' => PaymentInterface::PERIOD_REGULAR,
                'extraData' => ['parent_item' => $parentItem],
                'isPriceIncludesTax' => true,
                'expectedItemData' => [
                    'product_id' => 1,
                    'price' => 5.00,
                    'price_incl_tax' => 5.50,
                    'parent_item' => $parentItem
                ]
            ],
            [
                'genericData' => ['product_id' => 1],
                'paymentTypeData' => ['price' => 5.00, 'price_incl_tax' => 5.50],
                'paymentType' => PaymentInterface::PERIOD_INITIAL,
                'extraData' => [],
                'isPriceIncludesTax' => true,
                'expectedItemData' => ['product_id' => 1, 'price' => 5.00, 'price_incl_tax' => 5.50]
            ],
            [
                'genericData' => ['product_id' => 1],
                'paymentTypeData' => ['price' => 5.00, 'price_incl_tax' => 5.50],
                'paymentType' => PaymentInterface::PERIOD_TRIAL,
                'extraData' => [],
                'isPriceIncludesTax' => true,
                'expectedItemData' => ['product_id' => 1, 'price' => 5.00, 'price_incl_tax' => 5.50]
            ],
        ];
    }

    /**
     * Set property
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws \ReflectionException
     */
    private function setProperty($name, $value)
    {
        $class = new \ReflectionClass($this->converter);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->converter, $value);

        return $this;
    }
}
