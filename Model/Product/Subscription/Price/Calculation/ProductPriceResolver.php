<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation;

use Aheadworks\Sarp2\Model\Config\AdvancedPricingValueResolver;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver\ResolverPool;

/**
 * Class ProductPriceResolver
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation
 */
class ProductPriceResolver
{
    /**
     * @var AdvancedPricingValueResolver
     */
    private $advancedPricingValueResolver;

    /**
     * @var ResolverPool
     */
    private $resolverPool;

    /**
     * PriceProvider constructor.
     *
     * @param AdvancedPricingValueResolver $advancedPricingValueResolver
     * @param ResolverPool $resolverPool
     */
    public function __construct(
        AdvancedPricingValueResolver $advancedPricingValueResolver,
        ResolverPool $resolverPool
    ) {
        $this->advancedPricingValueResolver = $advancedPricingValueResolver;
        $this->resolverPool = $resolverPool;
    }

    /**
     * Get product price
     *
     * @param Input $subject
     * @param bool|null $forceUseAdvancedConfig
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPrice(Input $subject, $forceUseAdvancedConfig = null)
    {
        $isUsedAdvancePricing = $this->advancedPricingValueResolver->isUsedAdvancePricing(
            $subject->getProduct()->getId()
        );
        if (null !== $forceUseAdvancedConfig) {
            $isUsedAdvancePricing = $forceUseAdvancedConfig;
        }

        $resolver = $this->resolverPool->getResolver($subject);

        return $resolver->resolveProductPrice($subject, $isUsedAdvancePricing);
    }
}
