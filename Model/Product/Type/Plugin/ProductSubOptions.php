<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Plugin;

use Magento\Catalog\Model\Product as Product;

/**
 * Class ProductSubOptions
 * @package Aheadworks\Sarp2\Model\Product\Type\Plugin
 */
class ProductSubOptions
{
    /**
     * @param $interceptor
     * @param Product $product
     * @param array $productData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeInitializeFromData(
        $interceptor,
        Product $product,
        array $productData
    ) {
        $productData['aw_sarp2_subscription_options'] = isset($productData['aw_sarp2_subscription_options'])
            ? $productData['aw_sarp2_subscription_options']
            : [];

        return [$product, $productData];
    }
}
