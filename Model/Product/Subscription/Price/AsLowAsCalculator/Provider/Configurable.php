<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator\Provider;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator\OptionProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class Generic
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator\Provider
 */
class Configurable implements OptionProviderInterface
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SubscriptionOptionRepositoryInterface $optionsRepository
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllSubscriptionOptions($productId)
    {
        $options = $this->optionsRepository->getList($productId);

        $product = $this->productRepository->getById($productId);
        $childProducts = $product->getTypeInstance()
            ->getSalableUsedProducts($product);
        foreach ($childProducts as $childProduct) {
            $childOptions = $this->optionsRepository->getList($childProduct->getId());
            $options = array_merge($options, $childOptions);
        }

        return $options;
    }
}
