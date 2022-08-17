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

class RegularPrices implements ConfigInterface
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var PlanRepositoryInterface
     */
    protected $planRepository;

    /**
     * @var SubscriptionOptionProcessor
     */
    protected $subscriptionOptionProcessor;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

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
     * Get regular prices config
     *
     * @param ProductInterface $product
     * @param ProfileItemInterface|null $item
     * @param ProfileInterface|null $profile
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfig($product, $item = null, $profile = null)
    {
        $priceOptions = [0 => $this->subscriptionOptionProcessor->getProductPrices($product)];
        $subscriptionOptions = $this->optionsRepository->getList($product->getId());

        foreach ($subscriptionOptions as $option) {
            $priceOptions[$option->getOptionId()] = $this->getOptionPriceDataList($option, $item);
        }

        return [
            'productType' => $product->getTypeId(),
            'options' => $priceOptions
        ];
    }

    /**
     * Get price option
     *
     * @param SubscriptionOptionInterface $option
     * @param ProfileItemInterface|null $item
     * @return array
     * @throws LocalizedException
     */
    protected function getOptionPriceDataList($option, $item = null)
    {
        $planDefinition = $this->planRepository->get($option->getPlanId())->getDefinition();
        $basePrice = $this->subscriptionOptionProcessor->getBaseRegularPrice($option, $item);
        return $this->subscriptionOptionProcessor->getOptionPriceDataList(
            $basePrice,
            $option,
            $planDefinition,
            $this->taxHelper->displayPriceExcludingTax()
        );
    }
}
