<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Generic;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\ConfigInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor as SubscriptionOptionProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Helper\Data as TaxHelper;

class SubscriptionDetails implements ConfigInterface
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var SubscriptionOptionProcessor
     */
    private $subscriptionOptionProcessor;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     * @param PlanRepositoryInterface $planRepository
     * @param SubscriptionOptionProcessor $subscriptionOptionProcessor
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionsRepository,
        PlanRepositoryInterface $planRepository,
        SubscriptionOptionProcessor $subscriptionOptionProcessor,
        TaxHelper $taxHelper
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->planRepository = $planRepository;
        $this->subscriptionOptionProcessor = $subscriptionOptionProcessor;
        $this->taxHelper = $taxHelper;
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
        $subscriptionOptions = $this->optionsRepository->getList($product->getId());
        $detailedOptions = [];

        foreach ($subscriptionOptions as $option) {
            $detailedOption = $this->getDetailedOption($option, $item, $profile);
            if ($item) {
                $detailedOptions[$option->getPlanId()] = $detailedOption;
            } else {
                $detailedOptions[$option->getOptionId()] = $detailedOption;
            }
        }

        return $detailedOptions;
    }

    /**
     * Get detailed option
     *
     * @param SubscriptionOptionInterface $option
     * @param ProfileItemInterface|null $item
     * @param ProfileInterface|null $profile
     * @return array
     * @throws LocalizedException
     */
    protected function getDetailedOption($option, $item = null, $profile = null)
    {
        if ($profile && $profile->getPlanId() == $option->getPlanId()) {
            $planDefinition = $profile->getProfileDefinition();
            if ($item) {
                $option->setTrialPrice(
                    $this->taxHelper->displayPriceIncludingTax() || $this->taxHelper->displayBothPrices()
                        ? $item->getTrialPriceInclTax()
                        : $item->getTrialPrice()
                );
                $option->setRegularPrice(
                    $this->taxHelper->displayPriceIncludingTax() || $this->taxHelper->displayBothPrices()
                        ? $item->getRegularPriceInclTax()
                        : $item->getRegularPrice()
                );
            }
        } else {
            $planDefinition = $this->planRepository->get($option->getPlanId())->getDefinition();
        }

        return $this->subscriptionOptionProcessor->getDetailedOptions(
            $option,
            $planDefinition,
            $this->taxHelper->displayPriceExcludingTax(),
            $profile,
            $item
        );
    }
}
