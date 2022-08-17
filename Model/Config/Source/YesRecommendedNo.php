<?php
namespace Aheadworks\Sarp2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class YesRecommendedNo
 * @package Aheadworks\Sarp2\Model\Config\Source
 */
class YesRecommendedNo implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Yes (Recommended)')
            ],
            [
                'value' => 0,
                'label' => __('No')
            ]
        ];
    }
}
