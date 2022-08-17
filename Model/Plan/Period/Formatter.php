<?php
namespace Aheadworks\Sarp2\Model\Plan\Period;

use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments\Converter;

/**
 * Class Formatter
 * @package Aheadworks\Sarp2\Model\Plan\Period
 */
class Formatter
{
    /**
     * @var BillingPeriodSource
     */
    private $billingPeriodSource;

    /**
     * @var RepeatPaymentsSource
     */
    private $repeatPaymentsSource;

    /**
     * @var Converter
     */
    private $repeatPaymentsConverter;

    /**
     * @param BillingPeriodSource $billingPeriodSource
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param Converter $repeatPaymentsConverter
     */
    public function __construct(
        BillingPeriodSource $billingPeriodSource,
        RepeatPaymentsSource $repeatPaymentsSource,
        Converter $repeatPaymentsConverter
    ) {
        $this->billingPeriodSource = $billingPeriodSource;
        $this->repeatPaymentsSource = $repeatPaymentsSource;
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
    }

    /**
     * Format subscription plan period total cycles
     *
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @param int $totalCycles
     * @param bool $lowercase
     * @return string
     */
    public function formatTotalCycles($billingPeriod, $billingFrequency, $totalCycles, $lowercase = false)
    {
        $this->assertBillingPeriod($billingPeriod);

        $singlePeriodCycles = $billingFrequency * $totalCycles;
        $plural = $singlePeriodCycles != 1;
        $options = $this->billingPeriodSource->getOptions($plural);
        $result = $singlePeriodCycles . ' ' . $options[$billingPeriod];

        return $lowercase
            ? strtolower($result)
            : $result;
    }

    /**
     * Format subscription plan period
     *
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @return string
     */
    public function formatPeriod($billingPeriod, $billingFrequency)
    {
        $this->assertBillingPeriod($billingPeriod);

        $repeatPayment = $this->repeatPaymentsConverter->toRepeatPayments($billingPeriod, $billingFrequency);
        if ($repeatPayment) {
            $periodOptions = $this->billingPeriodSource->getOptions();
            $formatted = __('%1', $periodOptions[$billingPeriod]);
        } else {
            $formatted = $this->formatEvery(
                $billingPeriod,
                $billingFrequency
            );
        }

        return $formatted->render();
    }

    /**
     * Format subscription plan periodicity
     *
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @return string
     */
    public function formatPeriodicity($billingPeriod, $billingFrequency)
    {
        $this->assertBillingPeriod($billingPeriod);

        $repeatPayment = $this->repeatPaymentsConverter->toRepeatPayments($billingPeriod, $billingFrequency);
        if ($repeatPayment) {
            $periodOptions = $this->repeatPaymentsSource->getOptions();
            $formatted = __('%1', $periodOptions[$repeatPayment]);
        } else {
            $formatted = $this->formatEvery(
                $billingPeriod,
                $billingFrequency
            );
        }

        return $formatted->render();
    }

    /**
     * Format subscription plan period and frequency
     *
     * @param $billingPeriod
     * @param $billingFrequency
     * @return string
     */
    private function formatEvery($billingPeriod, $billingFrequency)
    {
        $plural = $billingFrequency > 1;
        $billingPeriodOptions = $this->billingPeriodSource->getOptions($plural);

        return __(
            'Every %1 %2',
            $billingFrequency,
            $billingPeriodOptions[$billingPeriod]
        );
    }

    /**
     * Asserts is a correct billing period
     *
     * @param string $billingPeriod
     * @return void
     */
    private function assertBillingPeriod($billingPeriod)
    {
        $options = $this->billingPeriodSource->getOptions();
        if (!isset($options[$billingPeriod])) {
            throw new \InvalidArgumentException('Invalid billing period parameter.');
        }
    }
}
