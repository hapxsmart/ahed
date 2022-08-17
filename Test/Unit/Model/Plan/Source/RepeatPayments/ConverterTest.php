<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Plan\Source\RepeatPayments;

use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments\Converter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments\Converter
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(Converter::class);
    }

    /**
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @param int $expectedResult
     * @dataProvider toRepeatPaymentsDataProvider
     */
    public function testToRepeatPayments($billingPeriod, $billingFrequency, $expectedResult)
    {
        $this->assertEquals(
            $expectedResult,
            $this->converter->toRepeatPayments($billingPeriod, $billingFrequency)
        );
    }

    /**
     * @param int $repeatPayments
     * @param string $expectedResult
     * @dataProvider toBillingPeriodDataProvider
     */
    public function testToBillingPeriod($repeatPayments, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->converter->toBillingPeriod($repeatPayments));
    }

    /**
     * @param int $repeatPayments
     * @param int $expectedResult
     * @dataProvider toBillingFrequencyDataProvider
     */
    public function testToBillingFrequency($repeatPayments, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->converter->toBillingFrequency($repeatPayments));
    }

    /**
     * @return array
     */
    public function toRepeatPaymentsDataProvider()
    {
        return [
            [BillingPeriodSource::DAY, 1, RepeatPaymentsSource::DAILY],
            [BillingPeriodSource::WEEK, 1, RepeatPaymentsSource::WEEKLY],
            [BillingPeriodSource::MONTH, 1, RepeatPaymentsSource::MONTHLY]
        ];
    }

    /**
     * @return array
     */
    public function toBillingPeriodDataProvider()
    {
        return [
            [RepeatPaymentsSource::DAILY, BillingPeriodSource::DAY],
            [RepeatPaymentsSource::WEEKLY, BillingPeriodSource::WEEK],
            [RepeatPaymentsSource::MONTHLY, BillingPeriodSource::MONTH]
        ];
    }

    /**
     * @return array
     */
    public function toBillingFrequencyDataProvider()
    {
        return [
            [RepeatPaymentsSource::DAILY, 1],
            [RepeatPaymentsSource::WEEKLY, 1],
            [RepeatPaymentsSource::MONTHLY, 1]
        ];
    }
}
