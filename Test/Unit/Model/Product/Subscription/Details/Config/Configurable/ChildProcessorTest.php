<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Details\Config\Configurable;

use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Configurable\ChildProcessor;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Helper\Product as ProductHelper;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Configurable\ChildProcessor
 */
class ChildProcessorTest extends TestCase
{
    /**
     * @var ChildProcessor
     */
    private $childProcessor;

    /**
     * @var ProductHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productHelperMock;

    /**
     * @var SubscriptionOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->productHelperMock = $this->createMock(ProductHelper::class);
        $this->optionsRepositoryMock = $this->getMockForAbstractClass(
            SubscriptionOptionRepositoryInterface::class
        );

        $this->childProcessor = $objectManager->getObject(
            ChildProcessor::class,
            [
                'productHelper' => $this->productHelperMock,
                'optionsRepository' => $this->optionsRepositoryMock,
            ]
        );
    }

    /**
     * Test getAllowedList method
     *
     * @param bool $skipSaleableCheck
     * @param Product[]|\PHPUnit_Framework_MockObject_MockObject[] $childProducts
     * @dataProvider getAllowedListDataProvider
     */
    public function testGetAllowedList($skipSaleableCheck, $childProducts, $result)
    {
        $parentProductMock = $this->createMock(Product::class);

        $configurableTypeMock = $this->createMock(Configurable::class);
        $configurableTypeMock->expects($this->once())
            ->method('getUsedProducts')
            ->with($parentProductMock, null)
            ->willReturn($childProducts);
        $parentProductMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($configurableTypeMock);

        $this->productHelperMock->expects($this->once())
            ->method('getSkipSaleableCheck')
            ->willReturn($skipSaleableCheck);

        $this->assertEquals($result, $this->childProcessor->getAllowedList($parentProductMock));
    }

    /**
     * @return array
     */
    public function getAllowedListDataProvider()
    {
        $saleableProductMock = $this->getProductMock(true);
        $notSaleableProductMock = $this->getProductMock(false);

        return [
            [
                'skipSaleableCheck' => false,
                'childProducts' => [$saleableProductMock, $notSaleableProductMock],
                'result' => [$saleableProductMock]
            ],
            [
                'skipSaleableCheck' => true,
                'childProducts' => [$saleableProductMock, $notSaleableProductMock],
                'result' => [$saleableProductMock, $notSaleableProductMock]
            ],
        ];
    }

    /**
     * Get product mock
     *
     * @param bool $isSaleable
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductMock($isSaleable)
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())
            ->method('isSaleable')
            ->willReturn($isSaleable);

        return $productMock;
    }

    /**
     * Test getSubscriptionOptions method
     *
     * @param int $parentProductId
     * @param int $childProductId
     * @param array $subscriptionOptionsMap
     * @param SubscriptionOptionInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $result
     * @dataProvider getSubscriptionOptionsDataProvider
     */
    public function testGetSubscriptionOptions($parentProductId, $childProductId, $subscriptionOptionsMap, $result)
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $childProductMock */
        $childProductMock = $this->createMock(Product::class);

        $childProductMock->expects($this->once())
            ->method('getId')
            ->willReturn($childProductId);
        $this->optionsRepositoryMock->expects($this->any())
            ->method('getList')
            ->will($this->returnValueMap($subscriptionOptionsMap));

        $this->assertSame($result, $this->childProcessor->getSubscriptionOptions($childProductMock, $parentProductId));
    }

    /**
     * @return array
     */
    public function getSubscriptionOptionsDataProvider()
    {
        $parentProductId = 1;
        $childProductId = 2;
        $optionOneMock = $this->getSubscriptionOptionMock(21, 31);
        $optionTwoMock = $this->getSubscriptionOptionMock(22, 32);
        $optionThreeMock = $this->getSubscriptionOptionMock(23, 32);
        $optionFourMock = $this->getSubscriptionOptionMock(24, 33);
        return [
            [
                'parentProductId' => $parentProductId,
                'childProductId' => $childProductId,
                'subscriptionOptionsMap' => [
                    [1, [$optionOneMock, $optionTwoMock]],
                    [2, [$optionThreeMock, $optionFourMock]],
                ],
                'result' => [$optionOneMock, $optionThreeMock],
            ],
            [
                'parentProductId' => $parentProductId,
                'childProductId' => $childProductId,
                'subscriptionOptionsMap' => [
                    [1, [$optionOneMock, $optionTwoMock]],
                    [2, [$optionFourMock]],
                ],
                'result' => [$optionOneMock, $optionTwoMock],
            ],
            [
                'parentProductId' => $parentProductId,
                'childProductId' => $childProductId,
                'subscriptionOptionsMap' => [
                    [1, [$optionOneMock, $optionTwoMock]],
                    [2, []],
                ],
                'result' => [$optionOneMock, $optionTwoMock],
            ],
            [
                'parentProductId' => $parentProductId,
                'childProductId' => $childProductId,
                'subscriptionOptionsMap' => [
                    [1, []],
                    [2, [$optionThreeMock, $optionFourMock]],
                ],
                'result' => [],
            ],
        ];
    }

    /**
     * Get subscription option
     *
     * @param int $optionId
     * @param int $planId
     * @return SubscriptionOptionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSubscriptionOptionMock($optionId, $planId)
    {
        $subscriptionOptionMock = $this->getMockForAbstractClass(SubscriptionOptionInterface::class);
        $subscriptionOptionMock->expects($this->any())
            ->method('getOptionId')
            ->willReturn($optionId);
        $subscriptionOptionMock->expects($this->any())
            ->method('getPlanId')
            ->willReturn($planId);
        $subscriptionOptionMock->expects($this->any())
            ->method('setOptionId')
            ->willReturnSelf();
        $subscriptionOptionMock->expects($this->any())
            ->method('setProductId')
            ->willReturnSelf();

        return $subscriptionOptionMock;
    }
}
