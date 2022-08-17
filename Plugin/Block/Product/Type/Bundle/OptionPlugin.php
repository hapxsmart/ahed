<?php
namespace Aheadworks\Sarp2\Plugin\Block\Product\Type\Bundle;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;
use Magento\Catalog\Model\Product;

/**
 * Class OptionPlugin
 *
 * @package Aheadworks\Sarp2\Plugin\Block\Product\Type\Bundle
 */
class OptionPlugin
{
    /**
     * @param Option $subject
     * @param \Closure $proceed
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function aroundRenderPriceString($subject, $proceed, $selection, $includeContainer = true)
    {
        $priceString = $proceed($selection, $includeContainer);

        $option = $subject->getOption();
        $className = 'aw-sarp2-bundle-option-' . $option->getId() . '-' . $selection->getSelectionId();

        return '<span class="'. $className .'">' . $priceString . '</span>';
    }
}
