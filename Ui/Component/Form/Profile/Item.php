<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Profile;

use Aheadworks\Sarp2\Ui\Component\Form\Profile\Product\AttributeProcessor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;

class Item extends Fieldset
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var AttributeProcessor
     */
    private $productAttributeProcessor;

    /**
     * @param ContextInterface $context
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeProcessor $productAttributeProcessor
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        ProductRepositoryInterface $productRepository,
        AttributeProcessor $productAttributeProcessor,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->productRepository = $productRepository;
        $this->productAttributeProcessor = $productAttributeProcessor;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getChildComponents()
    {
        $dataSource = $this->getContext()->getDataProvider()->getData();
        $item = array_shift($dataSource);
        $product = $this->productRepository->getById($item['product_id'], false, $item['store_id']);
        $attributes = $this->productAttributeProcessor->process($product, $item);

        foreach ($attributes as $name => $attribute) {
            $this->addComponent($name, $attribute);
        }

        return parent::getChildComponents();
    }
}
