<?php
namespace Aheadworks\Sarp2\Model\SalesRule;

use Aheadworks\Sarp2\Model\Profile\Address;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\SalesRule\Model\Rule\CustomerFactory;

class Utility
{
    /**
     * @var array
     */
    private $roundingDeltas = [];

    /**
     * @var array
     */
    private $baseRoundingDeltas = [];

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param CustomerFactory $customerFactory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        CustomerFactory $customerFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->customerFactory = $customerFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Check if rule can be applied
     *
     * @param Rule $rule
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function canProcessRule($rule, $address)
    {
        $addressId = $address->getCustomerAddressId();
        if ($rule->hasIsValidForAddress($addressId)) {
            return $rule->getIsValidForAddress($addressId);
        }

        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId = $address->getCustomerId();
            $ruleCustomer = $this->customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    $rule->setIsValidForAddress($addressId, false);
                    return false;
                }
            }
        }

        $rule->afterLoad();
        if (!$rule->validate($address)) {
            $rule->setIsValidForAddress($addressId, false);
            return false;
        }

        $rule->setIsValidForAddress($addressId, true);

        return true;
    }

    /**
     * Return discount item qty
     *
     * @param $item
     * @param Rule $rule
     * @return int
     */
    public function getItemQty($item, $rule)
    {
        $qty = $item->getQty();
        $discountQty = $rule->getDiscountQty();
        return $discountQty ? min($qty, $discountQty) : $qty;
    }

    /**
     * Process "delta" rounding
     *
     * @param DiscountData $discountData
     * @param $item
     * @return $this
     */
    public function deltaRoundingFix(
        DiscountData $discountData,
        $item
    ) {
        $discountAmount = $discountData->getAmount();
        $baseDiscountAmount = $discountData->getBaseAmount();
        $rowTotalInclTax = $item->getRowTotalInclTax();
        $baseRowTotalInclTax = $item->getBaseRowTotalInclTax();

        $percentKey = $item->getDiscountPercent();
        if ($percentKey) {
            $delta = isset($this->_roundingDeltas[$percentKey]) ? $this->roundingDeltas[$percentKey] : 0;
            $baseDelta = isset($this->_baseRoundingDeltas[$percentKey]) ? $this->baseRoundingDeltas[$percentKey] : 0;

            $discountAmount += $delta;
            $baseDiscountAmount += $baseDelta;

            $this->roundingDeltas[$percentKey] = $discountAmount - $this->priceCurrency->round($discountAmount);
            $this->baseRoundingDeltas[$percentKey] = $baseDiscountAmount
                - $this->priceCurrency->round($baseDiscountAmount);
        }

        /**
         * When we have 100% discount check if totals will not be negative
         */

        if ($percentKey == 100) {
            $discountDelta = $rowTotalInclTax - $discountAmount;
            $baseDiscountDelta = $baseRowTotalInclTax - $baseDiscountAmount;

            if ($discountDelta < 0) {
                $discountAmount += $discountDelta;
            }

            if ($baseDiscountDelta < 0) {
                $baseDiscountAmount += $baseDiscountDelta;
            }
        }

        $discountData->setAmount($this->priceCurrency->round($discountAmount));
        $discountData->setBaseAmount($this->priceCurrency->round($baseDiscountAmount));

        return $this;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param $item
     * @param float $qty
     * @return void
     */
    public function minFix(
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        $item,
        $qty
    ) {
        $itemPrice = $this->getItemPrice($item);
        $baseItemPrice = $this->getItemBasePrice($item);

        $itemDiscountAmount = $item->getDiscountAmount();
        $itemBaseDiscountAmount = $item->getBaseDiscountAmount();

        $discountAmount = min($itemDiscountAmount + $discountData->getAmount(), $itemPrice * $qty);
        $baseDiscountAmount = min($itemBaseDiscountAmount + $discountData->getBaseAmount(), $baseItemPrice * $qty);

        $discountData->setAmount($discountAmount);
        $discountData->setBaseAmount($baseDiscountAmount);
    }

    /**
     * Return item price
     *
     * @param $item
     * @return float
     */
    public function getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        $calcPrice = $item->getCalculationPrice();
        return $price === null ? $calcPrice : $price;
    }

    /**
     * Return item base price
     *
     * @param $item
     * @return float
     */
    public function getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return $price !== null ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }
}
