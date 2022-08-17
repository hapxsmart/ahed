<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Action\Type;

use Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder;
use Aheadworks\Sarp2\Engine\Payment\Action\Result;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Action\Type\Single;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfo;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoFactory;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\ToOrder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Action\Type\Single
 */
class SingleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Single
     */
    private $action;

    /**
     * @var ToOrder|\PHPUnit_Framework_MockObject_MockObject
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
        $this->converterMock = $this->createMock(ToOrder::class);
        $this->placeOrderServiceMock = $this->createMock(PlaceOrder::class);
        $this->paymentInfoFactoryMock = $this->createMock(PaymentInfoFactory::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->action = $objectManager->getObject(
            Single::class,
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
        $profileMock = $this->createMock(Profile::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $paymentInfoMock = $this->createMock(PaymentInfo::class);
        $resultMock = $this->createMock(Result::class);

        $paymentMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $paymentMock->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn($paymentPeriod);
        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($profileMock, $paymentPeriod)
            ->willReturn($orderMock);
        $this->paymentInfoFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'profile' => $profileMock,
                    'paymentPeriod' => $paymentPeriod
                ]
            )
            ->willReturn($paymentInfoMock);
        $this->placeOrderServiceMock->expects($this->once())
            ->method('place')
            ->with($orderMock, [$paymentInfoMock])
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($createdAt);
        $profileMock->expects($this->once())
            ->method('__call')
            ->with('setOrder', [$orderMock])
            ->willReturnSelf();
        $profileMock->expects($this->once())
            ->method('setLastOrderId')
            ->with($orderId)
            ->willReturnSelf();
        $profileMock->expects($this->once())
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
