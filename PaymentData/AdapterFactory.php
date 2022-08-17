<?php
namespace Aheadworks\Sarp2\PaymentData;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class AdapterFactory
 * @package Aheadworks\Sarp2\PaymentData
 */
class AdapterFactory
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
     * Create payment data adapter instance
     *
     * @param string $className
     * @return AdapterInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof AdapterInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . AdapterInterface::class
            );
        }
        return $instance;
    }
}
