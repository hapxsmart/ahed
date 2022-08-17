<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PriceResolver;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input as CalculationInput;
use Magento\Framework\ObjectManagerInterface;

class ResolverFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $resolverInstanceName
     * @return ResolverInterface
     */
    public function create(string $resolverInstanceName)
    {
        return $this->objectManager->get($resolverInstanceName);
    }
}
