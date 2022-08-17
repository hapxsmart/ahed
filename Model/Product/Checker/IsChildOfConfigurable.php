<?php
namespace Aheadworks\Sarp2\Model\Product\Checker;

use Aheadworks\Sarp2\Model\Product\Type\Configurable\ParentProductResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class IsChildOfConfigurable
{
    /**
     * @var ParentProductResolver
     */
    private $configurableParentProductResolver;

    /**
     * @param ParentProductResolver $configurableParentProductResolver
     */
    public function __construct(
        ParentProductResolver $configurableParentProductResolver
    ) {
        $this->configurableParentProductResolver = $configurableParentProductResolver;
    }

    /**
     * Check if product is child of configurable
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function check($product)
    {
        if ($product->getId() && $product->getTypeId() !== ConfigurableType::TYPE_CODE) {
            $parentProduct = $this->configurableParentProductResolver->resolveParentProduct($product->getId());
        }

        return isset($parentProduct);
    }
}
