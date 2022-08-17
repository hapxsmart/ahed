<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Configurable;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Generic\SubscriptionDetails as GenericSubscriptionDetails;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor as SubscriptionOptionProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Helper\Data as TaxHelper;

class SubscriptionDetails extends GenericSubscriptionDetails
{
    /**
     * @var ChildProcessor
     */
    private $childProcessor;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     * @param PlanRepositoryInterface $planRepository
     * @param SubscriptionOptionProcessor $subscriptionOptionProcessor
     * @param ChildProcessor $childProcessor
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionsRepository,
        PlanRepositoryInterface $planRepository,
        SubscriptionOptionProcessor $subscriptionOptionProcessor,
        ChildProcessor $childProcessor,
        TaxHelper $taxHelper
    ) {
        parent::__construct($optionsRepository, $planRepository, $subscriptionOptionProcessor, $taxHelper);
        $this->childProcessor = $childProcessor;
    }

    /**
     * Get subscription details config
     *
     * @param ProductInterface $product
     * @param ProfileItemInterface|null $item
     * @param ProfileInterface|null $profile
     * @return array
     * @throws LocalizedException
     */
    public function getConfig($product, $item = null, $profile = null)
    {
        $detailedOptions = [];
        $childProducts = $item
            ? $this->childProcessor->getProductByAttributes($product, $item)
            : $this->childProcessor->getAllowedList($product);

        foreach ($childProducts as $childProduct) {
            $subscriptionOptions = $this->childProcessor->getSubscriptionOptions($childProduct, $product->getId());
            foreach ($subscriptionOptions as $option) {
                $detailedOption = $this->getDetailedOption($option, $item, $profile);
                if ($item) {
                    $detailedOptions[$option->getPlanId()] = $detailedOption;
                } else {
                    $detailedOptions[$option->getOptionId()][$childProduct->getId()] = $detailedOption;
                }
            }
        }

        return $detailedOptions;
    }
}
