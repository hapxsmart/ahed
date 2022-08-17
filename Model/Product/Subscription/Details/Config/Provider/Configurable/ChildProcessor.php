<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Configurable;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Child
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Configurable
 */
class ChildProcessor
{
    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionsRepository;

    /**
     * @param ProductHelper $productHelper
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     */
    public function __construct(
        ProductHelper $productHelper,
        SubscriptionOptionRepositoryInterface $optionsRepository
    ) {
        $this->productHelper = $productHelper;
        $this->optionsRepository = $optionsRepository;
    }

    /**
     * Get allowed products
     *
     * @param ProductInterface|Product $parentProduct
     * @return ProductInterface[]|Product[]
     */
    public function getAllowedList($parentProduct)
    {
        $products = [];
        $skipSaleableCheck = $this->productHelper->getSkipSaleableCheck();
        $allChildProducts = $parentProduct->getTypeInstance()
            ->getUsedProducts($parentProduct, null);

        foreach ($allChildProducts as $product) {
            if ($product->isSaleable() || $skipSaleableCheck) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Retrieve product by attributes
     *
     * @param ProductInterface|Product $parentProduct
     * @param ProfileItemInterface $item
     * @return Product|null
     */
    public function getProductByAttributes($parentProduct, $item)
    {
        /** @var Configurable $typeInstance */
        $typeInstance = $parentProduct->getTypeInstance();

        $attributesInfo = [];
        foreach ($item->getProductOptions()['attributes_info'] as $option) {
            $attributesInfo[$option['option_id']] = $option['option_value'];
        }
        $childProduct = $typeInstance->getProductByAttributes($attributesInfo, $parentProduct);

        return [$childProduct];
    }

    /**
     * Get child subscription options
     *
     * @param Product $childProduct
     * @param int $parentProductId
     * @return SubscriptionOptionInterface[]
     */
    public function getSubscriptionOptions($childProduct, $parentProductId)
    {
        $options = [];
        try {
            $parentOptions = $this->optionsRepository->getList($parentProductId);
            $childOptions = $this->optionsRepository->getList($childProduct->getId());
            /** @var SubscriptionOptionInterface $parentOption */
            foreach ($parentOptions as $parentOption) {
                try {
                    $childOption = $this->getChildOption($parentOption, $childOptions);
                    $childOption
                        ->setOptionId($parentOption->getOptionId())
                        ->setProduct($childProduct);
                    $options[] = $childOption;
                } catch (NoSuchEntityException $e) {
                }
            }
        } catch (LocalizedException $e) {
        }

        return $options;
    }

    /**
     * Get child subscription option
     *
     * @param SubscriptionOptionInterface $parentOption
     * @param SubscriptionOptionInterface[] $childOptions
     * @return SubscriptionOptionInterface
     */
    private function getChildOption($parentOption, $childOptions)
    {
        /** @var SubscriptionOptionInterface $childOption */
        foreach ($childOptions as $childOption) {
            if ($parentOption->getPlanId() == $childOption->getPlanId()) {
                return $childOption;
            }
        }

        return $parentOption;
    }
}
