<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class OptionProviderFactory
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator
 */
class OptionProviderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create subscription options provider instance
     *
     * @param string $className
     * @return OptionProviderInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof OptionProviderInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . OptionProviderInterface::class
            );
        }
        return $instance;
    }
}
