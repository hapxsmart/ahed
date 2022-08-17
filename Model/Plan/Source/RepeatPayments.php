<?php
namespace Aheadworks\Sarp2\Model\Plan\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class RepeatPayments
 * @package Aheadworks\Sarp2\Model\Plan\Source
 */
class RepeatPayments implements OptionSourceInterface
{
    /**
     * 'Daily' option
     */
    const DAILY = 1;

    /**
     * 'Weekly' option
     */
    const WEEKLY = 2;

    /**
     * 'Monthly' option
     */
    const MONTHLY = 3;

    /**
     * 'Monthly' option
     */
    const YEARLY = 4;

    /**
     * 'Every...' option
     */
    const EVERY = 100;

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
                    'value' => self::DAILY,
                    'label' => __('Daily')
                ],
                [
                    'value' => self::WEEKLY,
                    'label' => __('Weekly')
                ],
                [
                    'value' => self::MONTHLY,
                    'label' => __('Monthly')
                ],
                [
                    'value' => self::YEARLY,
                    'label' => __('Yearly')
                ],
                [
                    'value' => self::EVERY,
                    'label' => __('Every...')
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
            $value = $optionItem['value'];
            if ($value != self::EVERY) {
                $options[$value] = $optionItem['label'];
            }
        }
        return $options;
    }
}
