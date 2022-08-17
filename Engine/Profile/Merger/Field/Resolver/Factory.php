<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver;

use Aheadworks\Sarp2\Engine\Profile\Merger\Field\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver
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
     * Create resolver instance
     *
     * @param string $className
     * @return ResolverInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof ResolverInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . ResolverInterface::class
            );
        }
        return $instance;
    }
}
