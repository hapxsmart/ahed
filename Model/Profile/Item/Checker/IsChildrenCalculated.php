<?php
namespace Aheadworks\Sarp2\Model\Profile\Item\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * Class IsChildrenCalculated
 * @package Aheadworks\Sarp2\Model\Profile\Item\Checker
 */
class IsChildrenCalculated
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Check if children calculated
     *
     * @param ProfileItemInterface $item
     * @return bool
     */
    public function check($item)
    {
        $parent = $item->getParentItem();
        $productId = $parent
            ? $parent->getProductId()
            : $item->getProductId();

        /** @var Product $product */
        $product = $this->productRepository->getById($productId);
        $priceType = $product->getPriceType();
        return $priceType !== null
            && (int)$priceType == AbstractType::CALCULATE_CHILD;
    }
}
