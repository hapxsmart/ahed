<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\CleanerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner
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
     * Create invalid data cleaner instance
     *
     * @param string $className
     * @return CleanerInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof CleanerInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . CleanerInterface::class
            );
        }
        return $instance;
    }
}
