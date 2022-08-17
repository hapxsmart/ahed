<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Plugin\Type;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Type\Processor\CartCandidatesProcessor;
use Aheadworks\Sarp2\Model\Product\Type\Processor\OrderOptionsProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Simple as SimpleProductType;
use Magento\Framework\DataObject;

/**
 * Class Simple
 * @package Aheadworks\Sarp2\Model\Product\Type\Plugin\Type
 */
class Simple
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
     * @param IsSubscription $isSubscriptionChecker
     * @param CartCandidatesProcessor $cartCandidatesProcessor
     * @param OrderOptionsProcessor $orderOptionsProcessor
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        CartCandidatesProcessor $cartCandidatesProcessor,
        OrderOptionsProcessor $orderOptionsProcessor
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->cartCandidatesProcessor = $cartCandidatesProcessor;
        $this->orderOptionsProcessor = $orderOptionsProcessor;
    }

    /**
     * @param SimpleProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasOptions(SimpleProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product);
    }

    /**
     * Used for configuring the product in backend order creation page
     *
     * @param SimpleProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCanConfigure(SimpleProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product);
    }

    /**
     * @param SimpleProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasRequiredOptions(SimpleProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product, true);
    }

    /**
     * @param SimpleProductType $subject
     * @param \Closure $proceed
     * @param DataObject $buyRequest
     * @param Product $product
     * @param null|string $processMode
     * @return array|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPrepareForCartAdvanced(
        SimpleProductType $subject,
        \Closure $proceed,
        DataObject $buyRequest,
        $product,
        $processMode = null
    ) {
        $candidates = $proceed($buyRequest, $product, $processMode);
        return $this->cartCandidatesProcessor->process($buyRequest, $candidates);
    }

    /**
     * @param SimpleProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return array|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetOrderOptions(
        SimpleProductType $subject,
        \Closure $proceed,
        $product
    ) {
        $options = $proceed($product);
        $this->orderOptionsProcessor->process($product, $options);

        return $options;
    }

    /**
     * @param SimpleProductType $subject
     * @param array $resultOptions
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $buyRequest
     * @return array
     */
    public function afterProcessBuyRequest(SimpleProductType $subject, $resultOptions, $product, $buyRequest)
    {
        $fieldName = 'aw_sarp2_subscription_type';
        if ($buyRequest->hasData($fieldName)) {
            $resultOptions[$fieldName] = $buyRequest->getData($fieldName);
        }

        return $resultOptions;
    }
}
