<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Action;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Exception\ScheduledPaymentException;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Strategy\DefaultStrategy;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\StrategyResolver;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\Order\InventoryManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder
 */
class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PlaceOrder
     */
    private $action;

    /**
     * @var OrderManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderManagementMock;

    /**
     * @var InventoryManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inventoryManagementMock;

    /**
     * @var DataResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataResolverMock;

    /**
     * @var StrategyResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $strategyResolverMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->orderManagementMock = $this->createMock(OrderManagementInterface::class);
        $this->inventoryManagementMock = $this->createMock(InventoryManagement::class);
        $this->dataResolverMock = $this->createMock(DataResolver::class);
        $this->strategyResolverMock = $this->createMock(StrategyResolver::class);
        $this->action = $objectManager->getObject(
            PlaceOrder::class,
            [
                'orderManagement' => $this->orderManagementMock,
                'inventoryManagement' => $this->inventoryManagementMock,
                'dataResolver' => $this->dataResolverMock,
                'strategyResolver' => $this->strategyResolverMock
            ]
        );
    }

    public function testPlace()
    {
        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        /** @var PaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $paymentInfoMock */
        $paymentInfoMock = $this->createMock(PaymentInfoInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);

        $paymentInfoMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $this->inventoryManagementMock->expects($this->once())
            ->method('subtract')
            ->with($profileMock);
        $this->orderManagementMock->expects($this->once())
            ->method('place')
            ->with($orderMock);

        $this->assertSame($orderMock, $this->action->place($orderMock, [$paymentInfoMock]));
    }

    /**
     * @expectedException \Aheadworks\Sarp2\Engine\Exception\ScheduledPaymentException
     * @expectedExceptionMessage Payment error.
     */
    public function testPlaceHandledException()
    {
        $paymentMethod = 'braintree';
        $exception = new \Exception('Gateway error.');
        $scheduledPaymentException = new ScheduledPaymentException(__('Payment error.'));

        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        /** @var PaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $paymentInfoMock */
        $paymentInfoMock = $this->createMock(PaymentInfoInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $strategyMock = $this->createMock(DefaultStrategy::class);

        $paymentInfoMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $this->inventoryManagementMock->expects($this->once())
            ->method('subtract')
            ->with($profileMock);
        $this->orderManagementMock->expects($this->once())
            ->method('place')
            ->with($orderMock)
            ->willThrowException($exception);
        $this->inventoryManagementMock->expects($this->once())
            ->method('revert')
            ->with($profileMock);
        $this->dataResolverMock->expects($this->once())
            ->method('getPaymentMethod')
            ->with([$paymentInfoMock])
            ->willReturn($paymentMethod);
        $this->strategyResolverMock->expects($this->once())
            ->method('getStrategy')
            ->with($paymentMethod)
            ->willReturn($strategyMock);
        $strategyMock->expects($this->once())
            ->method('apply')
            ->with($exception)
            ->willThrowException($scheduledPaymentException);
        $this->expectException(ScheduledPaymentException::class);
        $this->action->place($orderMock, [$paymentInfoMock]);
    }
}
