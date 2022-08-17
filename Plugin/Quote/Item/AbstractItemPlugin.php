<?php
namespace Aheadworks\Sarp2\Plugin\Quote\Item;

use Aheadworks\Sarp2\Model\Product\Attribute\AttributeName;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class AbstractItemPlugin
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        PriceCurrency $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    public function beforeGetConvertedPrice(
        AbstractItem $item
    ) {
        $price = $item->getData('converted_price');
        if (!$price && $item->getOptionByCode(AttributeName::AW_SARP2_SUBSCRIPTION_TYPE)) {
            $price = 0;
            if ($item->getAwSarpIsPriceInclInitialFeeAmount()) {
                $price += $item->getAwSarpInitialFee();
            }
            if ($item->getAwSarpIsPriceInclTrialAmount()) {
                $price += $item->getAwSarpTrialPrice();
            }
            if ($item->getAwSarpIsPriceInclRegularAmount()) {
                $price += $item->getAwSarpRegularPrice();
            }
            $item->setData('converted_price', $price);
        }
    }
}
