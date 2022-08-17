<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Bundle;

use Magento\Bundle\Model\Product\Price as BundlePrice;

/**
 * Class PriceModelSubstitute
 */
class PriceModelSubstitute extends BundlePrice
{
    const DO_NOT_USE_ADVANCED_PRICES_FOR_BUNDLE = 'do_not_use_advanced_prices';

    /**
     * @inheritDoc
     */
    protected function _applyTierPrice($product, $qty, $finalPrice)
    {
        return $finalPrice;
    }

    /**
     * @inheritDoc
     */
    protected function _applySpecialPrice($product, $finalPrice)
    {
        return $finalPrice;
    }
}
