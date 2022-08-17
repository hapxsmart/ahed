<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Profile\PaymentsInfo;

use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterfaceFactory;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider\StatusResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider
 */
class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ScheduledPaymentInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $infoFactoryMock;

    /**
     * @var PaymentsList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentListMock;

    /**
     * @var StatusResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusResolverMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->infoFactoryMock = $this->createMock(ScheduledPaymentInfoInterfaceFactory::class);
        $this->paymentListMock = $this->createMock(PaymentsList::class);
        $this->statusResolverMock = $this->createMock(StatusResolver::class);
        $this->provider = $objectManager->getObject(
            Provider::class,
            [
                'infoFactory' => $this->infoFactoryMock,
                'paymentList' => $this->paymentListMock,
                'statusResolver' => $this->statusResolverMock
            ]
        );
    }

    /**
     * @param string $paymentType
     * @dataProvider getScheduledPaymentsInfoDataProvider
     */
    public function testGetScheduledPaymentsInfo($paymentType)
    {
        $profileId = 1;
        $infoStatus = ScheduledPaymentInfoInterface::PAYMENT_STATUS_SCHEDULED;
        $paymentPeriod = PaymentInterface::PERIOD_REGULAR;
        $paymentDate = '2018-08-01 12:00:00';
        $totalScheduled = 10.00;
        $baseTotalScheduled = 15.00;

        $infoMock = $this->createMock(ScheduledPaymentInfoInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $this->infoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($infoMock);
        $this->paymentListMock->expects($this->once())
            ->method('getLastScheduled')
            ->with($profileId)
            ->willReturn([$paymentMock]);
        $this->statusResolverMock->expects($this->once())
            ->method('getInfoStatus')
            ->with($paymentMock)
            ->willReturn($infoStatus);
        $paymentMock->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn($paymentPeriod);
        $paymentMock->expects($this->once())
            ->method('getType')
            ->willReturn($paymentType);
        $paymentMock->expects($this->once())
            ->method(
                $paymentType == PaymentInterface::TYPE_REATTEMPT
                    ? 'getRetryAt'
                    : 'getScheduledAt'
            )
            ->willReturn($paymentDate);
        $paymentMock->expects($this->once())
            ->method('getTotalScheduled')
            ->willReturn($totalScheduled);
        $paymentMock->expects($this->once())
            ->method('getBaseTotalScheduled')
            ->willReturn($baseTotalScheduled);
        $infoMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with($infoStatus)
            ->willReturnSelf();
        $infoMock->expects($this->once())
            ->method('setPaymentPeriod')
            ->with($paymentPeriod)
            ->willReturnSelf();
        $infoMock->expects($this->once())
            ->method('setPaymentDate')
            ->with($paymentDate)
            ->willReturnSelf();
        $infoMock->expects($this->once())
            ->method('setAmount')
            ->with($totalScheduled)
            ->willReturnSelf();
        $infoMock->expects($this->once())
            ->method('setBaseAmount')
            ->with($baseTotalScheduled)
            ->willReturnSelf();

        $this->assertSame($infoMock, $this->provider->getScheduledPaymentsInfo($profileId));
    }

    public function testGetScheduledPaymentsInfoNoPayments()
    {
        $profileId = 1;

        $infoMock = $this->createMock(ScheduledPaymentInfoInterface::class);

        $this->infoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($infoMock);
        $this->paymentListMock->expects($this->once())
            ->method('getLastScheduled')
            ->with($profileId)
            ->willReturn([]);
        $infoMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(ScheduledPaymentInfoInterface::PAYMENT_STATUS_NO_PAYMENT);

        $this->assertSame($infoMock, $this->provider->getScheduledPaymentsInfo($profileId));
    }

    /**
     * @return array
     */
    public function getScheduledPaymentsInfoDataProvider()
    {
        return [[PaymentInterface::TYPE_PLANNED], [PaymentInterface::TYPE_REATTEMPT]];
    }
}
