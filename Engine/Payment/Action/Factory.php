<?php
namespace Aheadworks\Sarp2\Engine\Payment\Action;

use Aheadworks\Sarp2\Engine\Payment\ActionInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Engine\Payment\Action
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
     * Create payment action instance
     *
     * @param string $className
     * @return ActionInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof ActionInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . ActionInterface::class
            );
        }
        return $instance;
    }
}
