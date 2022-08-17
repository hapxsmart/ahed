<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ApplierFactory
 * @package Aheadworks\Sarp2\Engine\Profile\Action
 */
class ApplierFactory
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
     * Create action applier instance
     *
     * @param string $className
     * @return ApplierInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof ApplierInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . ApplierInterface::class
            );
        }
        return $instance;
    }
}
