<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Action\Type;

use Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder;
use Aheadworks\Sarp2\Engine\Payment\Action\Result;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Action\Type\Bundled;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfo;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoFactory;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\ToMergedOrder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Action\Type\Bundled
 */
class BundledTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Bundled
     */
    private $action;

    /**
     * @var ToMergedOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converterMock;

    /**
     * @var PlaceOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeOrderServiceMock;

    /**
     * @var PaymentInfoFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInfoFactoryMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->converterMock = $this->createMock(ToMergedOrder::class);
        $this->placeOrderServiceMock = $this->createMock(PlaceOrder::class);
        $this->paymentInfoFactoryMock = $this->createMock(PaymentInfoFactory::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->action = $objectManager->getObject(
            Bundled::class,
            [
                'converter' => $this->converterMock,
                'placeOrderService' => $this->placeOrderServiceMock,
                'paymentInfoFactory' => $this->paymentInfoFactoryMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    public function testPay()
    {
        $paymentPeriod = PaymentInterface::PERIOD_REGULAR;
        $orderId = 1;
        $createdAt = '2018-09-07 13:00:10';

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $child1Mock = $this->createMock(PaymentInterface::class);
        $child2Mock = $this->createMock(PaymentInterface::class);
        $profile1Mock = $this->createMock(Profile::class);
        $profile2Mock = $this->createMock(Profile::class);
        $info1Mock = $this->createMock(PaymentInfo::class);
        $info2Mock = $this->createMock(PaymentInfo::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $resultMock = $this->createMock(Result::class);

        $paymentMock->expects($this->once())
            ->method('getChildItems')
            ->willReturn([$child1Mock, $child2Mock]);
        $child1Mock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profile1Mock);
        $child2Mock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profile2Mock);
        $child1Mock->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn($paymentPeriod);
        $child2Mock->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn($paymentPeriod);
        $this->paymentInfoFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [
                    [
                        'profile' => $profile1Mock,
                        'paymentPeriod' => $paymentPeriod
                    ]
                ],
                [
                    [
                        'profile' => $profile2Mock,
                        'paymentPeriod' => $paymentPeriod
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls($info1Mock, $info2Mock);
        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with([$info1Mock, $info2Mock])
            ->willReturn($orderMock);
        $this->placeOrderServiceMock->expects($this->once())
            ->method('place')
            ->with($orderMock, [$info1Mock, $info2Mock])
            ->willReturn($orderMock);
        $info1Mock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profile1Mock);
        $info2Mock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profile2Mock);
        $orderMock->expects($this->exactly(2))
            ->method('getEntityId')
            ->willReturn($orderId);
        $orderMock->expects($this->exactly(2))
            ->method('getCreatedAt')
            ->willReturn($createdAt);
        $profile1Mock->expects($this->once())
            ->method('__call')
            ->with('setOrder', [$orderMock])
            ->willReturnSelf();
        $profile1Mock->expects($this->once())
            ->method('setLastOrderId')
            ->with($orderId)
            ->willReturnSelf();
        $profile1Mock->expects($this->once())
            ->method('setLastOrderDate')
            ->with($createdAt)
            ->willReturnSelf();
        $profile2Mock->expects($this->once())
            ->method('__call')
            ->with('setOrder', [$orderMock])
            ->willReturnSelf();
        $profile2Mock->expects($this->once())
            ->method('setLastOrderId')
            ->with($orderId)
            ->willReturnSelf();
        $profile2Mock->expects($this->once())
            ->method('setLastOrderDate')
            ->with($createdAt)
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['order' => $orderMock])
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->action->pay($paymentMock));
    }
}
