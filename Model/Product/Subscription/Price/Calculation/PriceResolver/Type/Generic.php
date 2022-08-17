<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver\Type;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver\ResolverInterface;

/**
 * Class Generic
 */
class Generic implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveProductPrice(Input $subject, bool $isUsedAdvancePricing)
    {
        $product = $subject->getProduct();
        $priceModel = $product->getPriceModel();
        $qty = $subject->getQty();

        return $isUsedAdvancePricing
            ? $priceModel->getFinalPrice($qty, $product)
            : $priceModel->getPrice($product);
    }
}
