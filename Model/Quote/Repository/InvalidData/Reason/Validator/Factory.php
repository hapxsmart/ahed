<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\Validator;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\ValidatorInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\Validator
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
     * Create quote validator instance
     *
     * @param string $className
     * @return ValidatorInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof ValidatorInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . ValidatorInterface::class
            );
        }
        return $instance;
    }
}
