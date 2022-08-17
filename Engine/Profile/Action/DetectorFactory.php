<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class DetectorFactory
 * @package Aheadworks\Sarp2\Engine\Profile\Action
 */
class DetectorFactory
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
     * Create action detector instance
     *
     * @param string $className
     * @return DetectorInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof DetectorInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . DetectorInterface::class
            );
        }
        return $instance;
    }
}
