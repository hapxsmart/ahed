<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver\Type;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver\ResolverInterface;

/**
 * Class Configurable
 */
class Configurable implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveProductPrice(Input $subject, bool $isUsedAdvancePricing)
    {
        $product = $subject->isChildrenCalculated()
            ? $subject->getChildProduct()
            : $subject->getProduct();
        $qty = $subject->isChildrenCalculated()
            ? $subject->getChildQty()
            : $subject->getQty();
        $priceModel = $product->getPriceModel();

        return $isUsedAdvancePricing
            ? $priceModel->getFinalPrice($qty, $product)
            : $priceModel->getPrice($product);
    }
}
