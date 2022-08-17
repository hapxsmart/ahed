<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\AbstractProvider;

class ProviderPool
{
    /**
     * @var AbstractProvider[]
     */
    private $providerInstances = [];

    /**
     * @var AbstractProviderFactory
     */
    private $providerFactory;

    /**
     * @var string[]
     */
    private $providers;

    /**
     * @param ProviderFactory $providerFactory
     * @param string[] $providers
     */
    public function __construct(
        ProviderFactory $providerFactory,
        array $providers = []
    ) {
        $this->providerFactory = $providerFactory;
        $this->providers = $providers;
    }

    /**
     * Get subscription details config provider
     *
     * @param string $typeId
     * @return AbstractProvider
     */
    public function getConfigProvider($typeId)
    {
        if (!isset($this->providerInstances[$typeId])) {
            $this->providerInstances[$typeId] = isset($this->providers[$typeId])
                ? $this->providerFactory->create($this->providers[$typeId])
                : $this->providerFactory->create($this->providers['generic']);
        }
        return $this->providerInstances[$typeId];
    }
}
