<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input as CalculationInput;

/**
 * Interface ResolverInterface
 */
interface ResolverInterface
{

    /**
     * Resolve product price
     *
     * @param CalculationInput $subject
     * @param bool $isUsedAdvancePricing
     * @return float
     */
    public function resolveProductPrice(CalculationInput $subject, bool $isUsedAdvancePricing);
}
