<?php
namespace Aheadworks\Sarp2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ClassicAdvanced
 *
 * @package Aheadworks\Sarp2\Model\Config\Source
 */
class ClassicAdvanced implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Classic')],
            ['value' => 1, 'label' => __('Advanced')]
        ];
    }
}
