<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Processor;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Profile\Item\Options\Extractor as OptionExtractor;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

/**
 * Class CartCandidatesProcessor
 *
 * @package Aheadworks\Sarp2\Model\Product\Type\Processor
 */
class CartCandidatesProcessor
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var OptionExtractor
     */
    private $optionExtractor;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param OptionExtractor $optionExtractor
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        OptionExtractor $optionExtractor
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->optionExtractor = $optionExtractor;
    }

    /**
     * Process add to cart candidates
     *
     * @param DataObject $buyRequest
     * @param Product[]|string $candidates
     * @return Product[]
     */
    public function process(DataObject $buyRequest, $candidates)
    {
        if (is_array($candidates)) {
            foreach ($candidates as $candidate) {
                if ($this->isSubscriptionChecker->check($candidate)) {
                    $optionId = $this->optionExtractor->getSubscriptionOptionIdFromBuyRequest($buyRequest);
                    if ($optionId) {
                        $candidate->addCustomOption('aw_sarp2_subscription_type', $optionId);
                    } elseif ($this->isSubscriptionChecker->check($candidate, true)) {
                        $candidates = $candidate->getTypeInstance()
                            ->getSpecifyOptionMessage()
                            ->render();
                    }
                }
            }
        }
        return $candidates;
    }
}
