<?php
namespace Aheadworks\Sarp2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class NoYes
 *
 * @package Aheadworks\Sarp2\Model\Config\Source
 */
class NoYes implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('No')], ['value' => 1, 'label' => __('Yes')]];
    }
}
