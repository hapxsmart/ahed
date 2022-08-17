<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Profile\Product;

use Magento\Catalog\Api\Data\ProductInterface;

class AttributeProcessor implements AttributeProcessorInterface
{
    /**
     * @var AttributeProcessorInterface[]
     */
    private $processors;

    /**
     * @param AttributeProcessorInterface[] $processors
     */
    public function __construct(
        array $processors
    ) {
        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function process(ProductInterface $product, array $itemData)
    {
        if (isset($this->processors[$product->getTypeId()])) {
            $processor = $this->processors[$product->getTypeId()];
            $fields = $processor->process($product, $itemData);
        }

        return $fields ?? [];
    }
}