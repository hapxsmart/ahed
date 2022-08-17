<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\PaymentsList
 */
class PaymentsListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var IsProcessable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $isProcessableCheckerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->isProcessableCheckerMock = $this->createMock(IsProcessable::class);
        $this->paymentsList = $objectManager->getObject(
            PaymentsList::class,
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'isProcessableChecker' => $this->isProcessableCheckerMock
            ]
        );
    }

    /**
     * @param int $size
     * @param bool $expectedResult
     * @dataProvider hasForProfileDataProvider
     */
    public function testHasForProfile($size, $expectedResult)
    {
        $profileId = 2;

        $collectionMock = $this->createMock(Collection::class);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('profile_id', ['eq' => $profileId]);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($size);

        $this->assertEquals($expectedResult, $this->paymentsList->hasForProfile($profileId));
    }

    public function testGetLastScheduled()
    {
        $profileId = 1;

        $collectionMock = $this->createMock(Collection::class);
        $paymentMock = $this->createMock(Payment::class);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('profile_id', ['eq' => $profileId])
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addTypeStatusMapFilter')
            ->with($this->isType('array'))
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setOrder')
            ->with('scheduled_at', Collection::SORT_ORDER_ASC);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$paymentMock]);

        $this->assertEquals([$paymentMock], $this->paymentsList->getLastScheduled($profileId));
    }

    /**
     * @return array
     */
    public function hasForProfileDataProvider()
    {
        return [[0, false], [1, true]];
    }
}
