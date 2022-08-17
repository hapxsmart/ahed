<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver\Type;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver\ResolverInterface;
use Aheadworks\Sarp2\Model\Product\Type\Bundle\PriceModelSubstitute;

/**
 * Class Bundle
 */
class Bundle implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveProductPrice(Input $subject, bool $isUsedAdvancePricing)
    {
        $product = $subject->getProduct();
        if (!$isUsedAdvancePricing) {
            $product->setData(PriceModelSubstitute::DO_NOT_USE_ADVANCED_PRICES_FOR_BUNDLE, true);
        }

        $priceModel = $product->getPriceModel();

        if ($subject->isChildrenCalculated()) {
            return $priceModel->getChildFinalPrice(
                $subject->getProduct(),
                $subject->getQty(),
                $subject->getChildProduct(),
                $subject->getChildQty()
            );
        } else {
            return $priceModel->getFinalPrice(
                $subject->getQty(),
                $subject->getProduct()
            );
        }
    }
}
