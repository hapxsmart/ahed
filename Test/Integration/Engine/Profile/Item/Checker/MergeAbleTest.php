<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Profile\Item\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\Item\Checker\MergeAble;
use Aheadworks\Sarp2\Model\Profile\Item;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class MergeAbleTest
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Profile\Item\Checker
 */
class MergeAbleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MergeAble
     */
    private $checker;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->checker = $this->objectManager->create(MergeAble::class);
    }

    /**
     * @param ProfileItemInterface $item1
     * @param ProfileItemInterface $item2
     * @param bool $expectedResult
     * @dataProvider checkDataProvider
     */
    public function testCheck($item1, $item2, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->checker->check($item1, $item2));
    }

    /**
     * Create profile item instance
     *
     * @param array $data
     * @return ProfileItemInterface
     */
    private function createItem(array $data)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProfileItemInterface|Item $item */
        $item = $objectManager->create(ProfileItemInterface::class);
        $item->setData($data);
        return $item;
    }

    /**
     * Modify entity data
     *
     * @param array $data
     * @param array $toSet
     * @param array $toRemove
     * @return array
     */
    private function modifyData(array $data, array $toSet = [], array $toRemove = [])
    {
        $arrayManager = Bootstrap::getObjectManager()->create(ArrayManager::class);
        foreach ($toSet as $path => $value) {
            $data = $arrayManager->set($path, $data, $value);
        }
        foreach ($toRemove as $path) {
            $data = $arrayManager->remove($path, $data);
        }
        return $data;
    }

    /**
     * @return array
     */
    public function checkDataProvider()
    {
        $itemData = [
            ProfileItemInterface::PRODUCT_ID => 1,
            ProfileItemInterface::PRODUCT_TYPE => 'simple',
            ProfileItemInterface::PRODUCT_OPTIONS => [
                'info_buyRequest' => [
                    'aw_sarp2_subscription_type' => 2,
                    'qty' => 2
                ]
            ],
            ProfileItemInterface::STORE_ID => 3,
            ProfileItemInterface::IS_VIRTUAL => false,
            ProfileItemInterface::SKU => 'simple',
            ProfileItemInterface::NAME => 'Simple Product',
            ProfileItemInterface::DESCRIPTION => 'Simple Product Description',
            ProfileItemInterface::WEIGHT => 35.00,
            ProfileItemInterface::QTY => 2
        ];

        return [
            [$this->createItem($itemData), $this->createItem($itemData), true],
            [
                $this->createItem($itemData),
                $this->createItem(
                    $this->modifyData(
                        $itemData,
                        [
                            ProfileItemInterface::QTY => 3,
                            ProfileItemInterface::PRODUCT_OPTIONS => [
                                'info_buyRequest' => [
                                    'aw_sarp2_subscription_type' => 2,
                                    'qty' => 3
                                ]
                            ]
                        ]
                    )
                ),
                true
            ],
            [
                $this->createItem($itemData),
                $this->createItem(
                    $this->modifyData($itemData, [ProfileItemInterface::PRODUCT_ID => 2])
                ),
                false
            ],
            [
                $this->createItem($itemData),
                $this->createItem(
                    $this->modifyData(
                        $itemData,
                        [
                            ProfileItemInterface::PRODUCT_ID => 2,
                            ProfileItemInterface::PRODUCT_TYPE => 'downloadable'
                        ]
                    )
                ),
                false
            ],
            [
                $this->createItem($itemData),
                $this->createItem(
                    $this->modifyData(
                        $itemData,
                        [
                            ProfileItemInterface::PRODUCT_OPTIONS => [
                                'info_buyRequest' => [
                                    'aw_sarp2_subscription_type' => 1,
                                    'qty' => 2
                                ]
                            ]
                        ]
                    )
                ),
                false
            ]
        ];
    }
}
