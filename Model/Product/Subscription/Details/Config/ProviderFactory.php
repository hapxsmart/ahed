<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\AbstractProvider;
use Magento\Framework\ObjectManagerInterface;

class ProviderFactory
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
     * Create subscription details config provider instance
     *
     * @param string $className
     * @return AbstractProvider
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof AbstractProvider) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t extend ' . AbstractProvider::class
            );
        }

        return $instance;
    }
}
