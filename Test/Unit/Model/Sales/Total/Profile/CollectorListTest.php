<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Sales\Total\Profile;

use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorList
 */
class CollectorListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CollectorList
     */
    private $collectorList;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->collectorList = $objectManager->getObject(
            CollectorList::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testGetCollectors()
    {
        $collectorsDefinition = [
            'collector1' => [
                'sortOrder' => 3,
                'className' => 'CollectorClassName1'
            ],
            'collector2' => [
                'sortOrder' => 1,
                'className' => 'CollectorClassName2'
            ],
            'collector3' => [
                'sortOrder' => 2,
                'className' => 'CollectorClassName3'
            ]
        ];

        $collector1Mock = $this->createMock(CollectorInterface::class);
        $collector2Mock = $this->createMock(CollectorInterface::class);
        $collector3Mock = $this->createMock(CollectorInterface::class);

        $class = new \ReflectionClass($this->collectorList);

        $collectorsProperty = $class->getProperty('collectors');
        $collectorsProperty->setAccessible(true);
        $collectorsProperty->setValue($this->collectorList, $collectorsDefinition);

        $this->objectManagerMock->expects($this->exactly(count($collectorsDefinition)))
            ->method('create')
            ->withConsecutive(
                ['CollectorClassName2'],
                ['CollectorClassName3'],
                ['CollectorClassName1']
            )
            ->willReturnOnConsecutiveCalls(
                $collector2Mock,
                $collector3Mock,
                $collector1Mock
            );

        $this->assertEquals(
            [
                'collector2' => $collector2Mock,
                'collector3' => $collector3Mock,
                'collector1' => $collector1Mock
            ],
            $this->collectorList->getCollectors()
        );
    }
}
