<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator\Provider\Generic;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class OptionProvider
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator
 */
class OptionProvider implements OptionProviderInterface
{
    /**
     * @var string[]
     */
    private $providers = [
        Configurable::TYPE_CODE => Provider\Configurable::class
    ];

    /**
     * @var OptionProviderInterface[]
     */
    private $providerInstances = [];

    /**
     * @var Generic
     */
    private $defaultProvider;

    /**
     * @var OptionProviderFactory
     */
    private $providerFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param OptionProviderFactory $providerFactory
     * @param Generic $defaultProvider
     * @param ProductRepositoryInterface $productRepository
     * @param array $providers
     */
    public function __construct(
        OptionProviderFactory $providerFactory,
        Generic $defaultProvider,
        ProductRepositoryInterface $productRepository,
        array $providers = []
    ) {
        $this->providerFactory = $providerFactory;
        $this->defaultProvider = $defaultProvider;
        $this->productRepository = $productRepository;
        $this->providers = array_merge($this->providers, $providers);
    }

    /**
     * @inheritDoc
     */
    public function getAllSubscriptionOptions($productId)
    {
        $product = $this->productRepository->getById($productId);
        $provider = $this->getProvider($product->getTypeId());

        return $provider->getAllSubscriptionOptions($productId);
    }

    /**
     * Get provider instance
     *
     * @param string $productTypeId
     * @return OptionProviderInterface
     * @throws \Exception
     */
    private function getProvider($productTypeId)
    {
        if (!isset($this->providerInstances[$productTypeId])) {
            $this->providerInstances[$productTypeId] = isset($this->providers[$productTypeId])
                ? $this->providerFactory->create($this->providers[$productTypeId])
                : $this->defaultProvider;
        }
        return $this->providerInstances[$productTypeId];
    }
}
