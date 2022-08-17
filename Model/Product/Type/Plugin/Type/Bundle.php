<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Plugin\Type;

use Aheadworks\Sarp2\Model\Config\AdvancedPricingValueResolver;
use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Type\Bundle\PriceModelSubstitute;
use Aheadworks\Sarp2\Model\Product\Type\Processor\CartCandidatesProcessor;
use Aheadworks\Sarp2\Model\Product\Type\Processor\OrderOptionsProcessor;
use Aheadworks\Sarp2\Model\Profile\Item\Options\Extractor;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

/**
 * Class Bundle
 */
class Bundle
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var CartCandidatesProcessor
     */
    private $cartCandidatesProcessor;

    /**
     * @var OrderOptionsProcessor
     */
    private $orderOptionsProcessor;

    /**
     * @var AdvancedPricingValueResolver
     */
    private $advancedPricingValueResolver;

    /**
     * @var Extractor
     */
    private $subscriptionOptionExtractor;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param CartCandidatesProcessor $cartCandidatesProcessor
     * @param OrderOptionsProcessor $orderOptionsProcessor
     * @param AdvancedPricingValueResolver $advancedPricingValueResolver
     * @param Extractor $subscriptionOptionExtractor
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        CartCandidatesProcessor $cartCandidatesProcessor,
        OrderOptionsProcessor $orderOptionsProcessor,
        AdvancedPricingValueResolver $advancedPricingValueResolver,
        Extractor $subscriptionOptionExtractor
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->cartCandidatesProcessor = $cartCandidatesProcessor;
        $this->orderOptionsProcessor = $orderOptionsProcessor;
        $this->advancedPricingValueResolver = $advancedPricingValueResolver;
        $this->subscriptionOptionExtractor = $subscriptionOptionExtractor;
    }

    /**
     * @param BundleProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasOptions(BundleProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product);
    }

    /**
     * @param BundleProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasRequiredOptions(BundleProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product, true);
    }

    /**
     * Used for configuring the product in backend order creation page
     *
     * @param BundleProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCanConfigure(BundleProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product);
    }

    /**
     * @param BundleProductType $subject
     * @param \Closure $proceed
     * @param DataObject $buyRequest
     * @param Product $product
     * @param null|string $processMode
     * @return array|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundPrepareForCartAdvanced(
        BundleProductType $subject,
        \Closure $proceed,
        DataObject $buyRequest,
        $product,
        $processMode = null
    ) {
        $optionId = $this->subscriptionOptionExtractor->getSubscriptionOptionIdFromBuyRequest($buyRequest);
        if ($optionId && !$this->advancedPricingValueResolver->isUsedAdvancePricing($product->getId())) {
            $product->setData(PriceModelSubstitute::DO_NOT_USE_ADVANCED_PRICES_FOR_BUNDLE, true);
        }

        $candidates = $proceed($buyRequest, $product, $processMode);

        $candidates = $this->cartCandidatesProcessor->process($buyRequest, $candidates);
        $subscriptionType = $this->getSubscriptionTypeFromParent($candidates);
        if ($subscriptionType) {
            $candidates = $this->updateSubscriptionTypeForChildItems($candidates, $subscriptionType);
        }

        return $candidates;
    }

    /**
     * Retrieve subscription type option value from parent bundle candidate
     *
     * @param Product[]$candidates
     * @return int|null
     */
    private function getSubscriptionTypeFromParent($candidates)
    {
        foreach ($candidates as $candidate) {
            if ($candidate->getTypeId() == BundleProductType::TYPE_CODE) {
                $option = $candidate->getCustomOption('aw_sarp2_subscription_type');
                if ($option) {
                    return $option->getValue();
                }
            }
        }

        return null;
    }

    /**
     * Update subscription type for child items
     *
     * @param Product[] $candidates
     * @return Product[]
     */
    private function updateSubscriptionTypeForChildItems($candidates, $type)
    {
        foreach ($candidates as $candidate) {
            if ($candidate->getTypeId() != BundleProductType::TYPE_CODE) {
                $candidate->addCustomOption('aw_sarp2_subscription_type', $type);
            }
        }

        return $candidates;
    }

    /**
     * @param BundleProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return array|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetOrderOptions(
        BundleProductType $subject,
        \Closure $proceed,
        $product
    ) {
        if ($product->getCustomOption('aw_sarp2_subscription_type')
            && !$this->advancedPricingValueResolver->isUsedAdvancePricing($product->getId())
        ) {
            $product->setData(PriceModelSubstitute::DO_NOT_USE_ADVANCED_PRICES_FOR_BUNDLE, true);
        }

        $options = $proceed($product);

        $this->orderOptionsProcessor->process($product, $options);

        return $options;
    }

    /**
     * @param BundleProductType $subject
     * @param array $resultOptions
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $buyRequest
     * @return array
     */
    public function afterProcessBuyRequest(BundleProductType $subject, $resultOptions, $product, $buyRequest)
    {
        $fieldName = 'aw_sarp2_subscription_type';
        if ($buyRequest->hasData($fieldName)) {
            $resultOptions[$fieldName] = $buyRequest->getData($fieldName);
        }

        return $resultOptions;
    }
}
