<?php
namespace Aheadworks\Sarp2\Model\SalesRule\Rule\Action\Discount;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\ByFixed as ByFixedDiscount;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

class ByFixed extends ByFixedDiscount
{
    /**
     * @param Rule $rule
     * @param AbstractItem|Product $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty)
    {
        $discountData = $this->discountFactory->create();
        $store = $item->getQuote() ? $item->getQuote()->getStore() : $item->getStore();

        $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $store);
        $discountData->setAmount($qty * $quoteAmount);
        $discountData->setBaseAmount($qty * $rule->getDiscountAmount());

        return $discountData;
    }
}
