<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Plugin\Type;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;

/**
 * Class Grouped
 * @package Aheadworks\Sarp2\Model\Product\Type\Plugin\Type
 */
class Grouped
{
    /**
     * Associated product cache key
     */
    const ASSOCIATED_PRODUCT_CACHE_KEY = '_cache_instance_associated_products';

    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        ProductRepositoryInterface $productRepository
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->productRepository = $productRepository;
    }

    /**
     * @param GroupedProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return array
     */
    public function aroundGetAssociatedProducts(GroupedProductType $subject, \Closure $proceed, $product)
    {
        $isNeedToFilter = false;
        if (!$product->hasData(self::ASSOCIATED_PRODUCT_CACHE_KEY)) {
            $isNeedToFilter = true;
        }
        $result = $proceed($product);

        if ($isNeedToFilter) {
            foreach ($result as $index => $productData) {
                /** @var Product $product */
                $productId = $productData->getId();
                try {
                    $associatedProduct = $this->productRepository->getById($productId);
                    if ($this->isSubscriptionChecker->check($associatedProduct, true)) {
                        unset($result[$index]);
                    }
                } catch (NoSuchEntityException $e) {
                }
            }
            $product->setData(self::ASSOCIATED_PRODUCT_CACHE_KEY, $result);
        }

        return $result;
    }
}
