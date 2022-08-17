<?php
namespace Aheadworks\Sarp2\Model\Plan\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ListRenderer
 *
 * @package Aheadworks\Sarp2\Model\Plan\Source
 */
class ListRenderer implements ArrayInterface
{
    /**
     * Radiobutton renderer
     */
    const RADIOBUTTON = 'radiobutton';

    /**
     * Dropdown renderer
     */
    const DROPDOWN = 'dropdown';

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
                    'value' => self::RADIOBUTTON,
                    'label' => __('Radiobutton group')
                ],
                [
                    'value' => self::DROPDOWN,
                    'label' => __('Dropdown')
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
