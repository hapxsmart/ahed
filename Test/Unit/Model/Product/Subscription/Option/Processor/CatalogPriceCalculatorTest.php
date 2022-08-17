<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Option\Processor;

use Aheadworks\Sarp2\Model\Product\Subscription\Option\Calculator\CatalogPriceCalculator;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor\CatalogPriceCalculator
 */
class CatalogPriceCalculatorTest extends TestCase
{
    /**
     * @var CatalogPriceCalculator
     */
    private $catalogPriceCalculator;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var CatalogHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogHelperMock;

    /**
     * @var PriceCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->catalogHelperMock = $this->createMock(CatalogHelper::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrency::class);

        $this->catalogPriceCalculator = $objectManager->getObject(
            CatalogPriceCalculator::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'catalogHelper' => $this->catalogHelperMock,
                'priceCurrency' => $this->priceCurrencyMock,
             ]
        );
    }

    /**
     * Test getFormattedPrice method
     *
     * @param bool $exclTax
     * @dataProvider excTaxDataProvider
     */
    public function testGetFormattedPrice($exclTax)
    {
        $productId = 1;
        $price = 10.0;
        $finalPrice = 11.0;
        $resultPrice = '$11.00';

        $productMock = $this->createMock(Product::class);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->catalogHelperMock->expects($this->once())
            ->method('getTaxPrice')
            ->with($productMock, $price, !$exclTax)
            ->willReturn($finalPrice);

        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($finalPrice)
            ->willReturn($finalPrice);

        $this->priceCurrencyMock->expects($this->once())
            ->method('format')
            ->with($finalPrice, false)
            ->willReturn($resultPrice);

        $this->assertSame(
            $resultPrice,
            $this->catalogPriceCalculator->getFormattedPrice($productId, $price, $exclTax)
        );
    }

    /**
     * Test getOldPriceAmount method
     */
    public function testGetOldPriceAmount()
    {
        $price = 10.00;
        $resultPrice = 11.00;

        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with($price)
            ->willReturn($resultPrice);

        $this->assertSame($resultPrice, $this->catalogPriceCalculator->getOldPriceAmount($price));
    }

    /**
     * Test getBasePriceAmount method
     */
    public function testGetBasePriceAmount()
    {
        $productId = 1;
        $price = 10.0;
        $resultPrice = 11.00;

        $productMock = $this->createMock(Product::class);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->catalogHelperMock->expects($this->once())
            ->method('getTaxPrice')
            ->with($productMock, $price)
            ->willReturn($resultPrice);

        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($resultPrice)
            ->willReturn($resultPrice);

        $this->assertSame($resultPrice, $this->catalogPriceCalculator->getBasePriceAmount($productId, $price));
    }

    /**
     * Test getFinalPriceAmount method
     *
     * @param bool $exclTax
     * @dataProvider excTaxDataProvider
     */
    public function testGetFinalPriceAmount($exclTax)
    {
        $productId = 1;
        $price = 10.0;
        $resultPrice = 11.00;

        $productMock = $this->createMock(Product::class);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->catalogHelperMock->expects($this->once())
            ->method('getTaxPrice')
            ->with($productMock, $price, !$exclTax)
            ->willReturn($resultPrice);

        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($resultPrice)
            ->willReturn($resultPrice);

        $this->assertSame(
            $resultPrice,
            $this->catalogPriceCalculator->getFinalPriceAmount($productId, $price, $exclTax)
        );
    }

    /**
     * @return array
     */
    public function excTaxDataProvider()
    {
        return [
            ['exclTax' => true],
            ['exclTax' => false],
        ];
    }
}
