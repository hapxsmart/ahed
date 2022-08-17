<?php
namespace Aheadworks\Sarp2\Block\Product\SubscriptionOptions\Renderer;

use Aheadworks\Sarp2\Block\Product\SubscriptionOptions;
use Magento\Framework\View\Element\Template;

/**
 * Class AbstractRenderer
 *
 * @method SubscriptionOptions getRenderedBlock()
 * @package Aheadworks\Sarp2\Block\Product\SubscriptionOptions\Renderer
 */
abstract class AbstractRenderer extends Template
{
    /**
     * Retrieve change event string
     *
     * @return string
     */
    abstract public function getChangeEvent();
}
