<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Plan\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveAndDuplicateButton
 *
 * @package Aheadworks\Sarp2\Block\Adminhtml\Plan\Edit
 */
class SaveAndDuplicateButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Duplicate'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndDuplicate']
                ],
                'form-role' => 'save',
            ],
            'sort_order' => 30,
        ];
    }
}
