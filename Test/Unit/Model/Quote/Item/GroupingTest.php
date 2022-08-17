<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Quote\Item;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\Quote\Item\Grouping;
use Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria\Key;
use Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria\Key\Generator as KeyGenerator;
use Aheadworks\Sarp2\Model\Quote\Item\Grouping\CriterionInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;

/**
 * Test for \Aheadworks\Sarp2\Model\Quote\Item\Grouping
 */
class GroupingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Grouping
     */
    private $grouping;

    /**
     * @var KeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyGeneratorMock;

    /**
     * @var Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->keyGeneratorMock = $this->createMock(KeyGenerator::class);
        $this->dataObjectFactoryMock = $this->createMock(Factory::class);

        $this->grouping = $objectManager->getObject(
            Grouping::class,
            [
                'keyGenerator' => $this->keyGeneratorMock,
                'dataObjectFactory' => $this->dataObjectFactoryMock
            ]
        );
    }

    /**
     * Test group method
     */
    public function testGroup()
    {
        $criteria = 'criteria_code';

        $simpleItemMock = $this->getItemMock(1, null);
        $simpleKeyValue = '1-1-0-0';
        $simpleResultName = 'plan';
        $simplePlanMock = $this->createMock(PlanInterface::class);
        $simpleKeyMock = $this->getKeyMock($simpleItemMock, $simpleKeyValue, $simpleResultName, $simplePlanMock);
        $simpleGroup = $this->createMock(DataObject::class);

        $configurableItemMock = $this->getItemMock(2, null);
        $configurableChildItemMock = $this->getItemMock(3, 2);
        $configurableKeyValue = '1-2-0-0';
        $configurableResultName = 'plan two';
        $configurablePlanMock = $this->createMock(PlanInterface::class);
        $configurableKeyMock = $this->getKeyMock(
            $configurableItemMock,
            $configurableKeyValue,
            $configurableResultName,
            $configurablePlanMock
        );
        $configurableGroup = $this->createMock(DataObject::class);

        $this->keyGeneratorMock->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive([$simpleItemMock, [$criteria]], [$configurableItemMock, [$criteria]])
            ->willReturnOnConsecutiveCalls($simpleKeyMock, $configurableKeyMock);

        $this->dataObjectFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [[
                    'items' => [$simpleItemMock],
                    $simpleResultName => $simplePlanMock
                ]],
                [[
                    'items' => [$configurableItemMock, $configurableChildItemMock],
                    $configurableResultName => $configurablePlanMock
                ]]
            )
            ->willReturnOnConsecutiveCalls($simpleGroup, $configurableGroup);

        $this->assertEquals(
            [$simpleGroup, $configurableGroup],
            $this->grouping->group([$simpleItemMock, $configurableItemMock, $configurableChildItemMock], [$criteria])
        );
    }

    /**
     * Test group method if key value is null
     */
    public function testGroupKeyValueIsNull()
    {
        $criteria = 'criteria_code';
        $itemMock = $this->getItemMock(1, null);
        $keyMock = $this->getKeyMock($itemMock, null, null, null);

        $this->keyGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($itemMock, [$criteria])
            ->willReturn($keyMock);

        $this->assertEquals([], $this->grouping->group([$itemMock], [$criteria]));
    }

    /**
     * Get item mock
     *
     * @param int $itemId
     * @param int|null $itemParentId
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getItemMock($itemId, $itemParentId)
    {
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->any())
            ->method('getItemId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('__call')
            ->with('getParentItemId')
            ->willReturn($itemParentId);

        return $itemMock;
    }

    /**
     * Get key mock
     *
     * @param Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
     * @param string $keyValue
     * @param string $resultName
     * @param PlanInterface|\PHPUnit_Framework_MockObject_MockObject $planMock
     * @return Key|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getKeyMock($itemMock, $keyValue, $resultName, $planMock)
    {
        $criterionMock = $this->createMock(CriterionInterface::class);
        $criterionMock->expects($this->any())
            ->method('getResultName')
            ->willReturn($resultName);
        $criterionMock->expects($this->any())
            ->method('getResultValue')
            ->with($itemMock)
            ->willReturn($planMock);

        $keyMock = $this->createMock(Key::class);
        $keyMock->expects($this->any())
            ->method('getValue')
            ->willReturn($keyValue);
        $keyMock->expects($this->any())
            ->method('getCriterionInstances')
            ->willReturn([$criterionMock]);

        return $keyMock;
    }
}
