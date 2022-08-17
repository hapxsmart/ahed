<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Configurable;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor as SubscriptionOptionProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Helper\Data as TaxHelper;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Generic\RegularPrices as GenericRegularPrices;

class RegularPrices extends GenericRegularPrices
{
    /**
     * @var ChildProcessor
     */
    private $childProcessor;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     * @param PlanRepositoryInterface $planRepository
     * @param SubscriptionOptionProcessor $subscriptionOptionProcessor
     * @param TaxHelper $taxHelper
     * @param ChildProcessor $childProcessor
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionsRepository,
        PlanRepositoryInterface $planRepository,
        SubscriptionOptionProcessor $subscriptionOptionProcessor,
        TaxHelper $taxHelper,
        ChildProcessor $childProcessor
    ) {
        parent::__construct($optionsRepository, $planRepository, $subscriptionOptionProcessor, $taxHelper);
        $this->childProcessor = $childProcessor;
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
        $priceOptions = [];
        $childProducts = $this->childProcessor->getAllowedList($product);

        foreach ($childProducts as $childProduct) {
            $productPrices = $this->subscriptionOptionProcessor->getProductPrices($childProduct);
            $childProductId = $childProduct->getId();
            $priceOptions[0][$childProductId] = $productPrices;
            $subscriptionOptions = $this->childProcessor->getSubscriptionOptions($childProduct, $product->getId());

            foreach ($subscriptionOptions as $option) {
                $priceOptions[$option->getOptionId()][$childProductId] = $this->getOptionPriceDataList($option, $item);
            }
        }

        return [
            'productType' => $product->getTypeId(),
            'options' => $priceOptions
        ];
    }
}
