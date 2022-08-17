<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Recalculation;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteValidator;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Model\Validator;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal as Sarp2QuoteSubtotal;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsInitial as IsInitialChecker;

class Subtotal extends Sarp2QuoteSubtotal
{
    /**
     * Discount calculation object
     *
     * @var Validator
     */
    private $calculator;

    /**
     * @var IsInitialChecker
     */
    private $isInitialChecker;

    /**
     * @param QuoteValidator $quoteValidator
     * @param IsSubscription $isSubscriptionChecker
     * @param Calculation $calculation
     * @param Validator $calculator
     * @param IsInitialChecker $isInitialChecker
     */
    public function __construct(
        QuoteValidator $quoteValidator,
        IsSubscription $isSubscriptionChecker,
        Calculation $calculation,
        Validator $calculator,
        IsInitialChecker $isInitialChecker
    ) {
        parent::__construct($quoteValidator, $isSubscriptionChecker, $calculation);
        $this->calculator = $calculator;
        $this->isInitialChecker = $isInitialChecker;
    }

    /**
     * @inheritDoc
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $store = $quote->getStore();
        $address = $shippingAssignment->getShipping()->getAddress();

        if ($quote->currentPaymentWasSet()) {
            $address->setPaymentMethod($quote->getPayment()->getMethod());
        }

        $this->calculator->reset($address);
        $items = $shippingAssignment->getItems();

        $this->calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $this->calculator->initTotals($items, $address);

        return parent::collect($quote, $shippingAssignment, $total);
    }

    /**
     * @inheritDoc
     */
    protected function _calculateRowTotal($item, $finalPrice, $originalPrice)
    {
        if ($this->isSubscriptionChecker->check($item) && !$this->isInitialChecker->check($item)) {
            $calcResult = $this->calculation->calculateItemPrice($item, false);
            $baseCalcResult = $this->calculation->calculateItemPrice($item, true);

            $itemClone = clone $item;
            $itemClone
                ->setQuote($item->getQuote())
                ->setPrice($finalPrice)
                ->setBaseOriginalPrice($originalPrice)
                ->getProduct()->setForceValidate(true);
            $this->calculator->process($itemClone);

            $priceByPlan = $calcResult->getAmount();
            $priceWithDiscount = $itemClone->getPrice()
                - $itemClone->getPrice()
                * $itemClone->getDiscountPercent() / 100;
            if ($priceWithDiscount > $priceByPlan) {
                $price = $priceByPlan;
                $basePrice = $baseCalcResult->getAmount();
                //Disable cart price rule discount but allow free shipping
                $item->getProduct()->setForceValidate(false);
                $item->getProduct()->setForceValidateFreeShipping(true);
            } else {
                $price = $basePrice = $finalPrice;
                $item->getProduct()->setForceValidate(true);
            }
            $item->setPrice($price)
                ->setBasePrice($basePrice)
                ->setOriginalPrice($price)
                ->setBaseOriginalPrice($basePrice)
                ->setCalculationPrice($price)
                ->setBaseCalculationPrice($basePrice)
                ->setAwSarpIsPriceInclInitialFeeAmount($calcResult->isInitialFeeSummed())
                ->setAwSarpIsPriceInclTrialAmount($calcResult->isTrialPriceSummed())
                ->setAwSarpIsPriceInclRegularAmount($calcResult->isRegularPriceSummed());

            $item->calcRowTotal();
        } else {
            parent::_calculateRowTotal($item, $finalPrice, $originalPrice);
        }
        return $this;
    }
}
