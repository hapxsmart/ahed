<?php
namespace Aheadworks\Sarp2\Test\Integration\Model\Sales\Total;

use Aheadworks\Sarp2\Model\Sales\Total\Populator;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Test\Integration\Model\Sales\Total\Populator\PriceCurrencyStub;
use Magento\Framework\DataObject;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Quote\Model\Quote\Item;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class PopulatorTest
 * @package Aheadworks\Sarp2\Test\Integration\Model\Sales\Total
 */
class PopulatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Populator
     */
    private $populator;

    /**
     * @var Item
     */
    private $quoteItem;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->configure(
            [
                'preferences' => [
                    PriceCurrency::class => PriceCurrencyStub::class
                ]
            ]
        );
        $objectManager->removeSharedInstance(PriceCurrency::class);

        $this->populator = $objectManager->create(Populator::class);
        $this->quoteItem = $objectManager->create(Item::class);
    }

    /**
     * @param array $map
     * @param array $nonAmountFields
     * @param array $initialEntityData
     * @param DataObject $totalsDetails
     * @param string $currencyOption
     * @param array $expectedEntityData
     * @dataProvider populateDataProvider
     */
    public function testPopulate(
        $map,
        $nonAmountFields,
        $initialEntityData,
        $totalsDetails,
        $currencyOption,
        $expectedEntityData
    ) {
        $class = new \ReflectionClass($this->populator);

        $mapProperty = $class->getProperty('map');
        $mapProperty->setAccessible(true);
        $mapProperty->setValue($this->populator, $map);

        $nonAmountFieldsProperty = $class->getProperty('nonAmountFields');
        $nonAmountFieldsProperty->setAccessible(true);
        $nonAmountFieldsProperty->setValue($this->populator, $nonAmountFields);

        $this->quoteItem->setData($initialEntityData);
        $this->populator->populate($this->quoteItem, $totalsDetails, $currencyOption);

        $this->assertEquals($expectedEntityData, $this->quoteItem->getData());
    }

    /**
     * @return array
     */
    public function populateDataProvider()
    {
        $objectManager = Bootstrap::getObjectManager();
        return [
            [
                ['price' => 'aw_sarp_price'],
                [],
                [],
                $objectManager->create(
                    DataObject::class,
                    ['data' => ['price' => 10.00]]
                ),
                PopulatorInterface::CURRENCY_OPTION_CONVERT,
                [
                    'aw_sarp_price' => 15.00,
                    'base_aw_sarp_price' => 10.00
                ]
            ],
            [
                ['price' => 'aw_sarp_price'],
                [],
                [],
                $objectManager->create(
                    DataObject::class,
                    ['data' => ['price' => 10.00]]
                ),
                PopulatorInterface::CURRENCY_OPTION_USE_BASE,
                ['base_aw_sarp_price' => 10.00]
            ],
            [
                ['price' => 'aw_sarp_price'],
                [],
                [],
                $objectManager->create(
                    DataObject::class,
                    ['data' => ['price' => 10.00]]
                ),
                PopulatorInterface::CURRENCY_OPTION_USE_STORE,
                ['aw_sarp_price' => 10.00]
            ],
            [
                ['price' => 'aw_sarp_price'],
                [],
                ['qty' => 2],
                $objectManager->create(
                    DataObject::class,
                    ['data' => ['price' => 10.00]]
                ),
                PopulatorInterface::CURRENCY_OPTION_CONVERT,
                [
                    'aw_sarp_price' => 15.00,
                    'base_aw_sarp_price' => 10.00,
                    'qty' => 2
                ]
            ],
            [
                [
                    'price' => 'aw_sarp_price',
                    'price_incl_tax' => 'aw_sarp_price_incl_tax'
                ],
                [],
                [],
                $objectManager->create(
                    DataObject::class,
                    ['data' => ['price' => 10.00]]
                ),
                PopulatorInterface::CURRENCY_OPTION_CONVERT,
                [
                    'aw_sarp_price' => 15.00,
                    'base_aw_sarp_price' => 10.00
                ]
            ],
            [
                [
                    'price' => 'aw_sarp_price',
                    'row_tax' => 'aw_sarp_row_tax'
                ],
                ['row_tax'],
                [],
                $objectManager->create(
                    DataObject::class,
                    ['data' => ['price' => 10.00, 'row_tax' => 2.00]]
                ),
                PopulatorInterface::CURRENCY_OPTION_CONVERT,
                [
                    'aw_sarp_price' => 15.00,
                    'base_aw_sarp_price' => 10.00,
                    'aw_sarp_row_tax' => 2.00
                ]
            ]
        ];
    }
}
