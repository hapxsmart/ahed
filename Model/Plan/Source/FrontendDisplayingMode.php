<?php
namespace Aheadworks\Sarp2\Model\Plan\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class FrontendDisplayingMode
 * @package Aheadworks\Sarp2\Model\Plan\Source
 */
class FrontendDisplayingMode implements OptionSourceInterface
{
    const SUBSCRIPTION = 'subscription';
    const INSTALLMENT = 'installment';

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Subscription'),
                'value' => self::SUBSCRIPTION
            ], [
                'label' => __('Installment'),
                'value' => self::INSTALLMENT
            ]
        ];
    }
}
