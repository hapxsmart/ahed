<?php
namespace Aheadworks\Sarp2\Model\Config\Backend;

use Magento\Framework\Data\OptionSourceInterface;

class IsOneTimeOnly implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => null,
                'label' => __('Please Select')
            ], [
                'value' => 0,
                'label' => __('Permanently')
            ], [
                'value' => 1,
                'label' => __('For the next order only')
            ]
        ];
    }
}