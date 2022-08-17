<?php
namespace Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class HandlerFactory
 * @package Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type
 */
class HandlerFactory
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
     * Create handler instance
     *
     * @param string $className
     * @return HandlerInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof HandlerInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . HandlerInterface::class
            );
        }
        return $instance;
    }

    /**
     * Create handler instance
     *
     * @param string $className
     * @return HandlerInterface
     */
    public function create1($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof HandlerInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . HandlerInterface::class
            );
        }
        return $instance;
    }
}
