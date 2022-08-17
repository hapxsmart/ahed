<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Item as ProfileItem;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Model\Product\Configuration\Item\OptionFactory as ConfigurationItemOptionFactory;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;

/**
 * Class CustomOptionCalculator
 *
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
class CustomOptionCalculator
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var ConfigurationItemOptionFactory
     */
    private $configurationItemOptionFactory;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @param PriceCurrency $priceCurrency
     * @param ConfigurationItemOptionFactory $configurationItemOptionFactory
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     */
    public function __construct(
        PriceCurrency $priceCurrency,
        ConfigurationItemOptionFactory $configurationItemOptionFactory,
        SubscriptionOptionRepositoryInterface $optionRepository
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->configurationItemOptionFactory = $configurationItemOptionFactory;
        $this->optionRepository = $optionRepository;
    }

    /**
     * Apply custom options price
     *
     * @param ProfileItem $item
     * @param float $basePrice
     * @param bool $useBaseCurrency
     * @param bool $forTrial
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function applyOptionsPrice($item, $basePrice, $useBaseCurrency, $forTrial)
    {
        $product = $item->getParentItem() ? $item->getParentItem()->getProduct() : $item->getProduct();
        $itemProductOptions = $item->getProductOptions();

        $baseProductPrice = $this->getProduct($item)->getPrice();
        $finalPrice = $basePrice;
        if ($itemProductOptions && isset($itemProductOptions['options'])) {
            $optionsPrice = 0;
            foreach ($itemProductOptions['options'] as $optionConfig) {
                if ($option = $product->getOptionById($optionConfig['option_id'])) {
                    $confItemOption = $this->getConfigurationItemOption($option, $optionConfig);

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $optionsPrice += $group->getOptionPrice($confItemOption->getValue(), $baseProductPrice);
                }
            }
            $optionsPrice = $this->recalculateIfInstallmentsMode($optionsPrice, $itemProductOptions, $forTrial);
            if (!$useBaseCurrency) {
                $optionsPrice = $this->priceCurrency->convert($optionsPrice);
            }

            $finalPrice += $optionsPrice;
        }

        return $finalPrice;
    }

    /**
     * Recalculate option price if installments mode enabled
     *
     * @param $optionsPrice
     * @param $itemProductOptions
     * @param $forTrial
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function recalculateIfInstallmentsMode($optionsPrice, $itemProductOptions, $forTrial)
    {
        $optionId = $itemProductOptions['aw_sarp2_subscription_option']['option_id']
            ?? ($itemProductOptions['info_buyRequest']['aw_sarp2_subscription_type']
                ?? null);
        if ($optionId) {
            $subscriptionOptions = $this->optionRepository->get($optionId);
            if ($subscriptionOptions->getIsInstallmentsMode()) {
                $plan = $subscriptionOptions->getPlan();

                $isIgnoreForTrial = $plan->getDefinition()->getIsTrialPeriodEnabled() && $forTrial;
                $billingCycles = $plan->getDefinition()->getTotalBillingCycles();

                if ($isIgnoreForTrial) {
                    $optionsPrice = 0;
                } elseif ($billingCycles > 0) {
                    $optionsPrice = $optionsPrice / $billingCycles;
                }
            }
        }

        return $optionsPrice;
    }

    /**
     * Get product
     *
     * @param ProfileItemInterface $item
     * @return ProductInterface|Product
     */
    private function getProduct($item)
    {
        if ($item->hasChildItems()) {
            $children = $item->getChildItems();
            $child = reset($children);
            $product = $child->getProduct();
        } else {
            $product = $item->getProduct();
        }
        return $product;
    }

    /**
     * Get configuration product option
     *
     * @param ProductCustomOptionInterface $option
     * @param array $optionConfig
     * @return OptionInterface
     */
    private function getConfigurationItemOption($option, $optionConfig)
    {
        $confItemOption = $this->configurationItemOptionFactory->create();
        $confItemOption
            ->setData('option_id', $optionConfig['option_id'])
            ->setData('value', $optionConfig['option_value'])
            ->setData('product_id', $option->getProductId());

        return $confItemOption;
    }
}
