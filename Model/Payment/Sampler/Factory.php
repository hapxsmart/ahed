<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler;

use Aheadworks\Sarp2\Model\Payment\SamplerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Model\Payment\Sampler
 */
class Factory
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
     * Create payment sampler instance
     *
     * @param string $className
     * @return SamplerInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof SamplerInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . SamplerInterface::class
            );
        }
        return $instance;
    }
}
