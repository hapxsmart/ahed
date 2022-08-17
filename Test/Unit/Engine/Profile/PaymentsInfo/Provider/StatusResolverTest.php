<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Profile\PaymentsInfo\Provider;

use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider\StatusResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider\StatusResolver
 */
class StatusResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StatusResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(StatusResolver::class);
    }

    /**
     * @param array $map
     * @param string $paymentType
     * @param string $expectedResult
     * @dataProvider getInfoStatusDataProvider
     */
    public function testGetInfoStatus($map, $paymentType, $expectedResult)
    {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())
            ->method('getType')
            ->willReturn($paymentType);

        $class = new \ReflectionClass($this->resolver);

        $typeToInfoStatusMapProperty = $class->getProperty('typeToInfoStatusMap');
        $typeToInfoStatusMapProperty->setAccessible(true);
        $typeToInfoStatusMapProperty->setValue($this->resolver, $map);

        $this->assertEquals($expectedResult, $this->resolver->getInfoStatus($paymentMock));
    }

    /**
     * @return array
     */
    public function getInfoStatusDataProvider()
    {
        return [
            [
                [PaymentInterface::TYPE_PLANNED => ScheduledPaymentInfoInterface::PAYMENT_STATUS_SCHEDULED],
                PaymentInterface::TYPE_PLANNED,
                ScheduledPaymentInfoInterface::PAYMENT_STATUS_SCHEDULED
            ],
            [
                [PaymentInterface::TYPE_PLANNED => ScheduledPaymentInfoInterface::PAYMENT_STATUS_SCHEDULED],
                'unknown_type',
                ScheduledPaymentInfoInterface::PAYMENT_STATUS_NO_PAYMENT
            ]
        ];
    }
}
