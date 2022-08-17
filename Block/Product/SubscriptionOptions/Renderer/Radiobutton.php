<?php
namespace Aheadworks\Sarp2\Block\Product\SubscriptionOptions\Renderer;

use Aheadworks\Sarp2\Block\Product\SubscriptionOptions;

/**
 * Class Radiobutton
 *
 * @method SubscriptionOptions getRenderedBlock()
 * @package Aheadworks\Sarp2\Block\Product\SubscriptionOptions
 */
class Radiobutton extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'product/subscription_options/renderers/radiobutton.phtml';

    /**
     * @inheritDoc
     */
    public function getChangeEvent()
    {
        return 'change input';
    }
}
