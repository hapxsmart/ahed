<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Group;

use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Model\Sales\Total\Group
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
     * Create totals group instance
     *
     * @param string $className
     * @return GroupInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof GroupInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . GroupInterface::class
            );
        }
        return $instance;
    }
}
