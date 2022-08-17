<?php
namespace Aheadworks\Sarp2\Plugin\Framework\Data\Form\Element;

use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Class FieldsetPlugin
 * @package Aheadworks\Sarp2\Plugin\Framework\Data\Form\Element
 */
class FieldsetPlugin
{
    /**
     * Add field to fieldset
     *
     * @param Fieldset $subject
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param bool $after
     * @param bool $isAdvanced
     * @return array
     */
    public function beforeAddField($subject, $elementId, $type, $config, $after = false, $isAdvanced = false)
    {
        if ($elementId == 'aw_sarp2_subscription_options') {
            $type = 'text_aw_sarp2_subscription_options';
        }
        return [$elementId, $type, $config, $after, $isAdvanced];
    }
}
