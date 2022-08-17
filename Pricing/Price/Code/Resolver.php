<?php
namespace Aheadworks\Sarp2\Pricing\Price\Code;

use Magento\Framework\Pricing\SaleableInterface;
use Aheadworks\Sarp2\Model\Product\Attribute\AttributeName as ProductAttributeName;

class Resolver
{
    /**
     * Subscription price type renderer key
     */
    const AW_SARP2_SUBSCRIPTION_PRICE_TYPE_RENDERER = 'aw_sarp2_catalog_subscription_price';

    /**
     * @var array
     */
    private $subscriptionTypeListToRenderSubscriptionPrice;

    /**
     * @param array $subscriptionTypeListToRenderSubscriptionPrice
     */
    public function __construct(
        array $subscriptionTypeListToRenderSubscriptionPrice = []
    ) {
        $this->subscriptionTypeListToRenderSubscriptionPrice = $subscriptionTypeListToRenderSubscriptionPrice;
    }

    /**
     * Resolve the actual price code for given item within the list
     *
     * @param string $currentPriceCode
     * @param SaleableInterface $saleableItem
     * @return string
     */
    public function getActualPriceCodeForItemList($currentPriceCode, $saleableItem)
    {
        $productSubscriptionType = $saleableItem->getData(
            ProductAttributeName::AW_SARP2_SUBSCRIPTION_TYPE
        );

        return in_array($productSubscriptionType, $this->subscriptionTypeListToRenderSubscriptionPrice)
            ? self::AW_SARP2_SUBSCRIPTION_PRICE_TYPE_RENDERER
            : $currentPriceCode;
    }
}
