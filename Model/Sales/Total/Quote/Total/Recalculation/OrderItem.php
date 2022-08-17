<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Recalculation;

use Magento\Tax\Model\Calculation as TaxCalculator;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\SalesRule\Rule\Calculator as RuleCalculator;

class OrderItem
{
    /**
     * @var RuleCalculator
     */
    private $ruleCalculator;

    /**
     * @var TaxCalculator
     */
    private $taxCalculator;

    /**
     * @param RuleCalculator $ruleCalculator
     * @param TaxCalculator $taxCalculator
     */
    public function __construct(
        RuleCalculator $ruleCalculator,
        TaxCalculator $taxCalculator
    ) {
        $this->ruleCalculator = $ruleCalculator;
        $this->taxCalculator = $taxCalculator;
    }

    /**
     * Recalculate totals
     *
     * @param array $orderItemData
     * @param ProfileItemInterface $profileItem
     * @return array
     */
    public function recalculateTotals($orderItemData, $profileItem)
    {
        $this->ruleCalculator->process($profileItem);
        $product = $profileItem->getProduct();
        $priceWithDiscount = $product->getFinalPrice($profileItem->getQty())
            - $product->getFinalPrice($profileItem->getQty())
            * $product->getDiscountPercent() / 100;

        if ($orderItemData['price'] > $priceWithDiscount) {
            $tax = $this->taxCalculator->calcTaxAmount($priceWithDiscount, $orderItemData['tax_percent']);
            $orderItemData = array_merge(
                $orderItemData,
                [
                    'base_price' => $product->getFinalPrice(),
                    'price' => $product->getFinalPrice(),
                    'base_row_total' => $product->getFinalPrice() * $product->getQty(),
                    'row_total' => $product->getFinalPrice() * $product->getQty(),
                    'base_tax_amount' => $tax * $product->getQty(),
                    'tax_amount' => $tax * $product->getQty(),
                    'base_price_incl_tax' => $product->getFinalPrice() + $tax,
                    'price_incl_tax' => $product->getFinalPrice() + $tax,
                    'base_row_total_incl_tax' => $product->getFinalPrice() * $product->getQty()
                        + $tax * $product->getQty(),
                    'row_total_incl_tax' => $product->getFinalPrice() * $product->getQty() + $tax * $product->getQty(),
                    'discount_amount' => $product->getDiscountAmount(),
                    'base_discount_amount' => $product->getBaseDiscountAmount()
                ]
            );
        }

        return $orderItemData;
    }
}
