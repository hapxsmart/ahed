<?php
namespace Aheadworks\Sarp2\Model\Plan\Source;

use Aheadworks\Sarp2\Model\Plan\Source\DayOfMonth\Ending;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class BillingFrequency
 * @package Aheadworks\Sarp2\Model\Plan\Source
 */
class BillingFrequency implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var Ending
     */
    private $ending;

    /**
     * @param Ending $ending
     */
    public function __construct(Ending $ending)
    {
        $this->ending = $ending;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            for ($every = 1; $every <= 365; $every++) {
                $this->options[] = [
                    'value' => $every,
                    'label' => $every . ' ' . $this->ending->getEnding($every)
                ];
            }
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
