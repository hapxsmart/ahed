<?php
namespace Aheadworks\Sarp2\Model\Plan\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class StartDateType
 * @package Aheadworks\Sarp2\Model\Plan\Source
 */
class StartDateType implements OptionSourceInterface
{
    /**
     * 'Defined by customer' start date type
     */
    const DEFINED_BY_CUSTOMER = 'defined_by_customer';

    /**
     * 'Moment of purchase' start date type
     */
    const MOMENT_OF_PURCHASE = 'moment_of_purchase';

    /**
     * 'Exact day of month' start date type
     */
    const EXACT_DAY_OF_MONTH = 'exact_day_of_month';

    /**
     * 'Last day of current month' start date type
     */
    const LAST_DAY_OF_CURRENT_MONTH = 'last_day_of_current_month';

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::DEFINED_BY_CUSTOMER,
                    'label' => __('Defined by customer')
                ],
                [
                    'value' => self::MOMENT_OF_PURCHASE,
                    'label' => __('Moment of purchase')
                ],
                [
                    'value' => self::EXACT_DAY_OF_MONTH,
                    'label' => __('Exact day of month')
                ],
                [
                    'value' => self::LAST_DAY_OF_CURRENT_MONTH,
                    'label' => __('Last day of current month')
                ]
            ];
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $optionItem) {
            $options[$optionItem['value']] = $optionItem['label'];
        }
        return $options;
    }
}
