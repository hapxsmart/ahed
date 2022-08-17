<?php
namespace Aheadworks\Sarp2\Plugin\Product\Configuration;

use Aheadworks\Sarp2\Model\Config\AdvancedPricingValueResolver;
use Aheadworks\Sarp2\Model\Product\Type\Bundle\PriceModelSubstitute;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\BundleOptions\PriceModifier;
use Magento\Bundle\Helper\Catalog\Product\Configuration as BundleConfiguration;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\LocalizedException;

class Bundle
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var PriceModifier
     */
    private $optionPriceModifier;

    /**
     * @var AdvancedPricingValueResolver
     */
    private $advancedPricingValueResolver;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param PriceModifier $optionProcessor
     * @param AdvancedPricingValueResolver $advancedPricingValueResolver
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        PriceModifier $optionProcessor,
        AdvancedPricingValueResolver $advancedPricingValueResolver
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->optionPriceModifier = $optionProcessor;
        $this->advancedPricingValueResolver = $advancedPricingValueResolver;
    }

    /**
     * @param BundleConfiguration $subject
     * @param callable $proceed
     * @param ItemInterface $item
     * @param Product $selectionProduct
     * @return float
     * @throws LocalizedException
     */
    public function aroundGetSelectionFinalPrice(
        BundleConfiguration $subject,
        callable $proceed,
        ItemInterface $item,
        Product $selectionProduct
    ) {
        $product = $item->getProduct();
        if ($this->isSubscriptionChecker->check($item)
            && !$this->advancedPricingValueResolver->isUsedAdvancePricing($product->getId())
        ) {
            $product->setData(PriceModelSubstitute::DO_NOT_USE_ADVANCED_PRICES_FOR_BUNDLE, true);
        }

        $finalPrice = $proceed($item, $selectionProduct);
        $finalPrice = $this->optionPriceModifier->recalculateOptionPrice($item, $finalPrice);

        return $finalPrice;
    }
}
