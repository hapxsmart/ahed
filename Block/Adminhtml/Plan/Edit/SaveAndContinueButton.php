<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Plan\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveAndContinueButton
 *
 * @package Aheadworks\Sarp2\Block\Adminhtml\Plan\Edit
 */
class SaveAndContinueButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit']
                ],
                'form-role' => 'save',
            ],
            'sort_order' => 40,
        ];
    }
}
