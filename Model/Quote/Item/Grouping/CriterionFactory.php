<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class CriterionFactory
 * @package Aheadworks\Sarp2\Model\Quote\Item\Grouping
 */
class CriterionFactory
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
     * Create grouping criterion instance
     *
     * @param string $className
     * @return CriterionInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof CriterionInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . CriterionInterface::class
            );
        }
        return $instance;
    }
}
