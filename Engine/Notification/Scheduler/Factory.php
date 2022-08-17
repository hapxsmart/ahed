<?php
namespace Aheadworks\Sarp2\Engine\Notification\Scheduler;

use Aheadworks\Sarp2\Engine\Notification\SchedulerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Engine\Notification\Scheduler
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
     * Create notification scheduler instance
     *
     * @param string $className
     * @return SchedulerInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof SchedulerInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . SchedulerInterface::class
            );
        }
        return $instance;
    }
}
