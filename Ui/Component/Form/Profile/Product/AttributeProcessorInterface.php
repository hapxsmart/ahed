<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Profile\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Ui\Component\Form\Field;

interface AttributeProcessorInterface
{
    /**
     * Retrieve product attributes fields
     *
     * @param ProductInterface $product
     * @param array $itemData
     * @return Field[]
     */
    public function process(ProductInterface $product, array $itemData);
}
