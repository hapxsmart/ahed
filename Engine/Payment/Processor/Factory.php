<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor;

use Aheadworks\Sarp2\Engine\Payment\ProcessorInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Engine\Payment\Processor
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
     * Create processor instance
     *
     * @param string $className
     * @return ProcessorInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof ProcessorInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . ProcessorInterface::class
            );
        }
        return $instance;
    }
}
