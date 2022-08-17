<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Profile\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Ui\Component\Form\FieldFactory;

class Configurable implements AttributeProcessorInterface
{
    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @param FieldFactory $fieldFactory
     */
    public function __construct(
        FieldFactory $fieldFactory
    ) {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * @inheritDoc
     */
    public function process(ProductInterface $product, array $itemData)
    {
        $attributes = $product->getExtensionAttributes()->getConfigurableProductOptions();

        return $attributes
            ? $this->getAttributesFields($itemData, $attributes)
            : [];
    }

    /**
     * Get attributes fields
     *
     * @param array $itemData
     * @param array $attributes
     * @return array
     */
    private function getAttributesFields(array $itemData, array $attributes)
    {
        $fields = [];

        foreach ($attributes as $attribute) {
            $field = $this->fieldFactory->create();
            $productAttribute = $attribute->getProductAttribute();
            $attributesData = $itemData['product_options']['info_buyRequest']['super_attribute'];
            $name = $productAttribute->getAttributeId();
            $config = [
                'label' => $attribute->getLabel(),
                'value' => $attributesData[$attribute->getAttributeId()],
                'formElement' => $productAttribute->getFrontendInput(),
                'dataScope' => 'product_options.info_buyRequest.super_attribute.' . $attribute->getAttributeId()
            ];

            if ($productAttribute->getFrontendInput() == 'select') {
                $config['options'] = $this->prepareOptions($attribute->getOptions());
            }

            $field->setData(
                [
                    'config' => $config,
                    'name' => $name
                ]
            );

            $field->prepare();
            $fields[$name] = $field;
        }

        return $fields;
    }

    /**
     * Prepare options
     *
     * @param array $options
     * @return array
     */
    private function prepareOptions(array $options)
    {
        $result = [];

        foreach ($options as $option) {
            $result[] = [
                'value' => $option['value_index'],
                'label' => $option['label']
            ];
        }

        return $result;
    }
}