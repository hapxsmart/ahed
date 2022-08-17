<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Element\Plan;

use Aheadworks\Sarp2\Model\Plan\Source\BillingFrequency as BillingFrequencySource;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments\Converter as RepeatPaymentsConverter;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Input;

/**
 * Class RepeatPayments
 * @package Aheadworks\Sarp2\Ui\Component\Form\Element\Plan
 */
class RepeatPayments extends Input
{
    /**
     * @var BillingPeriodSource
     */
    private $billingPeriodSource;

    /**
     * @var BillingFrequencySource
     */
    private $billingFrequencySource;

    /**
     * @var RepeatPaymentsSource
     */
    private $repeatPaymentsSource;

    /**
     * @var RepeatPaymentsConverter
     */
    private $repeatPaymentsConverter;

    /**
     * @param ContextInterface $context
     * @param BillingPeriodSource $billingPeriodSource
     * @param BillingFrequencySource $billingFrequencySource
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param RepeatPaymentsConverter $repeatPaymentsConverter
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BillingPeriodSource $billingPeriodSource,
        BillingFrequencySource $billingFrequencySource,
        RepeatPaymentsSource $repeatPaymentsSource,
        RepeatPaymentsConverter $repeatPaymentsConverter,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->billingPeriodSource = $billingPeriodSource;
        $this->billingFrequencySource = $billingFrequencySource;
        $this->repeatPaymentsSource = $repeatPaymentsSource;
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (!isset($config['repeatPaymentsOptions'])) {
            $config['repeatPaymentsOptions'] = $this->repeatPaymentsSource->toOptionArray();
        }
        if (!isset($config['billingFrequencyOptions'])) {
            $config['billingFrequencyOptions'] = $this->billingFrequencySource->toOptionArray();
        }
        if (!isset($config['billingPeriodOptions'])) {
            $config['billingPeriodOptions'] = $this->billingPeriodSource->toOptionArray();
        }
        if (!isset($config['repeatPaymentsToValuesMap'])) {
            $config['repeatPaymentsToValuesMap'] = [];
            $repeatPaymentsValues = [
                RepeatPaymentsSource::DAILY,
                RepeatPaymentsSource::WEEKLY,
                RepeatPaymentsSource::MONTHLY
            ];
            foreach ($repeatPaymentsValues as $repeatPayments) {
                $config['repeatPaymentsToValuesMap'][$repeatPayments] = [
                    'billingPeriod' => $this->repeatPaymentsConverter->toBillingPeriod($repeatPayments),
                    'billingFrequency' => $this->repeatPaymentsConverter->toBillingFrequency($repeatPayments)
                ];
            }
        }
        $config['expandOptionValue'] = RepeatPaymentsSource::EVERY;

        $this->setData('config', $config);
        parent::prepare();
    }
}
