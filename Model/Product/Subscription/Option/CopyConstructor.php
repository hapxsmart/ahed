<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Option;

use Magento\Catalog\Model\Product\CopyConstructorInterface;
use Magento\Catalog\Model\Product;

/**
 * Class CopyConstructor
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Option
 */
class CopyConstructor implements CopyConstructorInterface
{
    /**
     * Duplicating subscription options
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    public function build(Product $product, Product $duplicate)
    {
        $options = $duplicate->getData('aw_sarp2_subscription_options') ? : [];
        foreach ($options as &$option) {
            $option['option_id'] = null;
            $option['product_id'] = null;
        }
        $duplicate->setData('aw_sarp2_subscription_options', $options);
    }
}
