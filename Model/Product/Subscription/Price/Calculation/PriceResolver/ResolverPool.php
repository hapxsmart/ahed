<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver\Type\Generic;

/**
 * Class ResolverPool
 */
class ResolverPool
{
    /**
     * @var string[]
     */
    private $resolverMap;

    /**
     * @var string
     */
    private $defaultResolver;

    /**
     * @var ResolverFactory
     */
    private $resolverFactory;

    /**
     * ResolverPool constructor.
     *
     * @param ResolverFactory $resolverFactory
     * @param string[] $resolverMap
     * @param string $defaultResolver
     */
    public function __construct(
        ResolverFactory $resolverFactory,
        array $resolverMap,
        string $defaultResolver = Generic::class
    ) {
        $this->resolverMap = $resolverMap;
        $this->defaultResolver = $defaultResolver;
        $this->resolverFactory = $resolverFactory;
    }

    /**
     * Get price resolver by input calculation subject
     *
     * @param Input $calculationInput
     * @return ResolverInterface
     */
    public function getResolver(Input $calculationInput)
    {
        $product = $calculationInput->getProduct();
        $resolverName = $this->resolverMap[$product->getTypeId()] ?? $this->defaultResolver;

        return $this->resolverFactory->create($resolverName);
    }
}
