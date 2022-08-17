<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Plugin\Type;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Type\Processor\CartCandidatesProcessor;
use Aheadworks\Sarp2\Model\Product\Type\Processor\OrderOptionsProcessor;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Framework\DataObject;

/**
 * Class Downloadable
 * @package Aheadworks\Sarp2\Model\Product\Type\Plugin\Type
 */
class Downloadable
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
     * @param DownloadableProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasOptions(DownloadableProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product);
    }

    /**
     * @param DownloadableProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasRequiredOptions(DownloadableProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product, true);
    }

    /**
     * Used for configuring the product in backend order creation page
     *
     * @param DownloadableProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCanConfigure(DownloadableProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product);
    }

    /**
     * @param DownloadableProductType $subject
     * @param \Closure $proceed
     * @param DataObject $buyRequest
     * @param Product $product
     * @param null|string $processMode
     * @return array|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPrepareForCartAdvanced(
        DownloadableProductType $subject,
        \Closure $proceed,
        DataObject $buyRequest,
        $product,
        $processMode = null
    ) {
        $candidates = $proceed($buyRequest, $product, $processMode);
        return $this->cartCandidatesProcessor->process($buyRequest, $candidates);
    }

    /**
     * @param DownloadableProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return array|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetOrderOptions(
        DownloadableProductType $subject,
        \Closure $proceed,
        $product
    ) {
        $options = $proceed($product);
        $this->orderOptionsProcessor->process($product, $options);

        return $options;
    }

    /**
     * @param DownloadableProductType $subject
     * @param array $resultOptions
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $buyRequest
     * @return array
     */
    public function afterProcessBuyRequest(DownloadableProductType $subject, $resultOptions, $product, $buyRequest)
    {
        $fieldName = 'aw_sarp2_subscription_type';
        if ($buyRequest->hasData($fieldName)) {
            $resultOptions[$fieldName] = $buyRequest->getData($fieldName);
        }

        return $resultOptions;
    }
}
