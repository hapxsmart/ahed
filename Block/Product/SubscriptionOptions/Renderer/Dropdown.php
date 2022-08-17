<?php
namespace Aheadworks\Sarp2\Block\Product\SubscriptionOptions\Renderer;

use Aheadworks\Sarp2\Block\Product\SubscriptionOptions;

/**
 * Class Dropdown
 *
 * @method SubscriptionOptions getRenderedBlock()
 * @package Aheadworks\Sarp2\Block\Product\SubscriptionOptions
 */
class Dropdown extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'product/subscription_options/renderers/dropdown.phtml';

    /**
     * Check if need show No Plan radiobutton
     *
     * @return bool
     */
    public function isShowNoPlanRadiobutton()
    {
        return $this->getRenderedBlock()->isFirstOptionNoPlan();
    }

    /**
     * @inheritDoc
     */
    public function getChangeEvent()
    {
        return 'change select';
    }
}
