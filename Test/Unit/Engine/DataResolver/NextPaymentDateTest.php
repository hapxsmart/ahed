<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\DataResolver;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate
 */
class NextPaymentDateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NextPaymentDate
     */
    private $resolver;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var CoreDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreDateMock;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->coreDateMock = $this->createMock(CoreDate::class);
        $this->resolver = $this->objectManager->getObject(
            NextPaymentDate::class,
            [
                'dateTime' => $this->dateTimeMock,
                'coreDate' => $this->coreDateMock
            ]
        );
    }

    /**
     * @param int $startTimestamp
     * @param int $nowTimestamp
     * @param int $resultTimestamp
     * @dataProvider getDateInitialDataProvider
     */
    public function testGetDateInitial($startTimestamp, $nowTimestamp, $resultTimestamp)
    {
        $startDate = '2017-08-01 14:01:08';
        $resultDate = '2017-08-02 14:01:08';

        $this->coreDateMock->expects($this->exactly(2))
            ->method('gmtTimestamp')
            ->willReturnMap(
                [
                    [$startDate, $startTimestamp],
                    [null, $nowTimestamp]
                ]
            );
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with($resultTimestamp)
            ->willReturn($resultDate);
        $this->assertEquals($resultDate, $this->resolver->getDateInitial($startDate));
    }

    /**
     * @param string $paymentDate
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @param string $result
     * @dataProvider getDateNextDataProvider
     */
    public function testGetDateNext($paymentDate, $billingPeriod, $billingFrequency, $result)
    {
        // Integration test
        $resolver = $this->objectManager->getObject(
            NextPaymentDate::class,
            [
                'dateTime' => $this->objectManager->getObject(DateTime::class)
            ]
        );
        $this->assertEquals(
            $result,
            $resolver->getDateNext($paymentDate, $billingPeriod, $billingFrequency)
        );
    }

    /**
     * @return array
     */
    public function getDateInitialDataProvider()
    {
        return [[1, 2, 2], [2, 1, 2]];
    }

    /**
     * @return array
     */
    public function getDateNextDataProvider()
    {
        return [
            ['2017-08-01 14:01:08', BillingPeriod::DAY, 1, '2017-08-02 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::DAY, 2, '2017-08-03 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::WEEK, 1, '2017-08-08 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::WEEK, 2, '2017-08-15 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::SEMI_MONTH, 1, '2017-08-15 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::SEMI_MONTH, 2, '2017-08-29 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::MONTH, 1, '2017-09-01 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::MONTH, 2, '2017-10-01 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::YEAR, 1, '2018-08-01 14:01:08'],
            ['2017-08-01 14:01:08', BillingPeriod::YEAR, 2, '2019-08-01 14:01:08']
        ];
    }
}
