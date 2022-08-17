<?php
namespace Aheadworks\Sarp2\Block\Product;

use Aheadworks\Sarp2\Model\Config\AdvancedPricingValueResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

/**
 * Class TierPriceTemplate
 *
 * @package Aheadworks\Sarp2\Block\Product
 */
class TierPriceTemplate extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Sarp2::product/tier_price_template.phtml';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AdvancedPricingValueResolver
     */
    private $advancedPricingConfigValueResolver;

    /**
     * @param Template\Context $context
     * @param Registry $registry
     * @param AdvancedPricingValueResolver $advancedPricingConfigValueResolver
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        AdvancedPricingValueResolver $advancedPricingConfigValueResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->advancedPricingConfigValueResolver = $advancedPricingConfigValueResolver;
    }

    /**
     * Get product
     *
     * @return ProductInterface|Product
     */
    private function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if ($this->isUsedAdvancePricing($this->getProduct())) {
            return $this->replaceBlockDataRole(parent::_toHtml());
        }
        return '';
    }

    /**
     * @param ProductInterface|Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isUsedAdvancePricing($product)
    {
        $isUsedAdvancePricing = $this->advancedPricingConfigValueResolver
            ->isUsedAdvancePricing($product->getId());

        if (!$isUsedAdvancePricing && $product->getTypeId() == Configurable::TYPE_CODE) {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($children as $childProduct) {
                if ($this->advancedPricingConfigValueResolver->isUsedAdvancePricing($childProduct->getId())) {
                    $isUsedAdvancePricing = true;
                }
            }
        }

        return $isUsedAdvancePricing;
    }

    /**
     * Cut data-role="tier-price-block"
     * @param string $html
     * @return string
     */
    private function replaceBlockDataRole($html)
    {
        return str_replace('tier-price-block', '', $html);
    }
}
