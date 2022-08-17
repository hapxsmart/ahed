<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Configurable;

use Aheadworks\Sarp2\Model\Product\Attribute\AttributeName as ProductAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ParentProductResolver
 *
 * @package Aheadworks\Sarp2\Model\Product\Type\Configurable
 */
class ParentProductResolver
{
    /**
     * @var ConfigurableType
     */
    private $configurableType;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductRepositoryInterface[]
     */
    private $parentProductsCache = [];

    /**
     * ParentProductResolver constructor.
     *
     * @param ConfigurableType $configurableType
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ConfigurableType $configurableType,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
    }

    /**
     * Retrieve parent product
     *
     * @param int $childProductId
     * @return ProductInterface|null
     */
    public function resolveParentProduct($childProductId)
    {
        if (!array_key_exists($childProductId, $this->parentProductsCache)) {
            try {
                $parentIds = $this->configurableType->getParentIdsByChild($childProductId);
                if (empty($parentIds)) {
                    $this->parentProductsCache[$childProductId] = null;
                }
                $parentProductId = reset($parentIds);
                $this->parentProductsCache[$childProductId] = $this->productRepository->getById($parentProductId);
            } catch (LocalizedException $exception) {
                $this->parentProductsCache[$childProductId] = null;
            }
        }

        return $this->parentProductsCache[$childProductId];
    }

    /**
     * Retrieve parent product subscription options
     *
     * @param int $childProductId
     * @return array
     */
    public function resolveParentProductSubscriptionOptions($childProductId)
    {
        $parentProduct = $this->resolveParentProduct($childProductId);
        if ($parentProduct) {
            return (array)$parentProduct->getData(ProductAttribute::AW_SARP2_SUBSCRIPTION_OPTIONS);
        }

        return [];
    }
}
