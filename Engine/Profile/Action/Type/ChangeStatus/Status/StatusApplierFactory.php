<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class StatusApplierFactory
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status
 */
class StatusApplierFactory
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
     * @return StatusApplierInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof StatusApplierInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . StatusApplierInterface::class
            );
        }
        return $instance;
    }
}
