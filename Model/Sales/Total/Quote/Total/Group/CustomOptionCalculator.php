<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class CustomOptionCalculator
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group
 */
class CustomOptionCalculator
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @param PriceCurrency $priceCurrency
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     */
    public function __construct(
        PriceCurrency $priceCurrency,
        SubscriptionOptionRepositoryInterface $optionRepository
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->optionRepository = $optionRepository;
    }

    /**
     * Apply custom options price
     *
     * @param CartItemInterface|AbstractItem $item
     * @param float $basePrice
     * @param bool $useBaseCurrency
     * @param bool $forTrial
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function applyOptionsPrice($item, $basePrice, $useBaseCurrency, $forTrial)
    {
        $product = $item->getParentItem() ? $item->getParentItem()->getProduct() : $item->getProduct();
        $optionIds = $product->getCustomOption('option_ids');
        $baseProductPrice = $this->getProduct($item)->getPrice();
        $finalPrice = $basePrice;
        if ($optionIds) {
            $optionsPrice = 0;
            foreach (explode(',', (string)$optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $optionsPrice += $group->getOptionPrice($confItemOption->getValue(), $baseProductPrice);
                }
            }
            $optionsPrice = $this->recalculateIfInstallmentsMode($optionsPrice, $item, $forTrial);
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
     * @param $customOptionsPrice
     * @param CartItemInterface $item
     * @param $forTrial
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function recalculateIfInstallmentsMode($customOptionsPrice, $item, $forTrial)
    {
        $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
        if ($optionId) {
            $subscriptionOptions = $this->optionRepository->get($optionId->getValue());
            if ($subscriptionOptions->getIsInstallmentsMode()) {
                $plan = $subscriptionOptions->getPlan();

                $isIgnoreForTrial = $plan->getDefinition()->getIsTrialPeriodEnabled() && $forTrial;
                $billingCycles = $plan->getDefinition()->getTotalBillingCycles();

                if ($isIgnoreForTrial) {
                    $customOptionsPrice = 0;
                } elseif ($billingCycles > 0) {
                    $customOptionsPrice = $customOptionsPrice / $billingCycles;
                }
            }
        }

        return $customOptionsPrice;
    }

    /**
     * Get product
     *
     * @param CartItemInterface|AbstractItem $item
     * @return ProductInterface|Product
     */
    private function getProduct($item)
    {
        if ($item instanceof AbstractItem
            && $item->getHasChildren()
        ) {
            $children = $item->getChildren();
            $child = reset($children);
            $product = $child->getProduct();
        } else {
            $product = $item->getProduct();
        }
        return $product;
    }
}
