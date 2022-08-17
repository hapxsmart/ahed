<?php
namespace Aheadworks\Sarp2\Model\Plan\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PriceRounding
 * @package Aheadworks\Sarp2\Model\Plan\Source
 */
class PriceRounding implements OptionSourceInterface
{
    /**
     * 'Up to XX.99' rounding option
     */
    const UP_TO_XX_99 = 1;

    /**
     * 'Up to XX.90' rounding option
     */
    const UP_TO_XX_90 = 2;

    /**
     * 'Up to X9.00' rounding option
     */
    const UP_TO_X9_00 = 3;

    /**
     * 'Down to XX.99' rounding option
     */
    const DOWN_TO_XX_99 = 4;

    /**
     * 'Down to XX.90' rounding option
     */
    const DOWN_TO_XX_90 = 5;

    /**
     * 'Down to X9.00' rounding option
     */
    const DOWN_TO_X9_00 = 6;

    /**
     * 'Don\'t round' option
     */
    const DONT_ROUND = 7;

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
                    'value' => self::UP_TO_XX_99,
                    'label' => __('Up to XX.99')
                ],
                [
                    'value' => self::UP_TO_XX_90,
                    'label' => __('Up to XX.90')
                ],
                [
                    'value' => self::UP_TO_X9_00,
                    'label' => __('Up to X9.00')
                ],
                [
                    'value' => self::DOWN_TO_XX_99,
                    'label' => __('Down to XX.99')
                ],
                [
                    'value' => self::DOWN_TO_XX_90,
                    'label' => __('Down to XX.90')
                ],
                [
                    'value' => self::DOWN_TO_X9_00,
                    'label' => __('Down to X9.00')
                ],
                [
                    'value' => self::DONT_ROUND,
                    'label' => __('Don\'t round')
                ]
            ];
        }
        return $this->options;
    }
}
