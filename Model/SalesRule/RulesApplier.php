<?php
namespace Aheadworks\Sarp2\Model\SalesRule;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;

class RulesApplier
{
    /**
     * @var Utility
     */
    private $validatorUtility;

    /**
     * @var ChildrenValidationLocator
     */
    private $childrenValidationLocator;

    /**
     * @var CalculatorFactory
     */
    private $calculatorFactory;

    /**
     * @param CalculatorFactory $calculatorFactory
     * @param Utility $utility
     * @param ChildrenValidationLocator $childrenValidationLocator
     */
    public function __construct(
        CalculatorFactory $calculatorFactory,
        Utility $utility,
        ChildrenValidationLocator $childrenValidationLocator
    ) {
        $this->calculatorFactory = $calculatorFactory;
        $this->validatorUtility = $utility;
        $this->childrenValidationLocator = $childrenValidationLocator;
    }

    /**
     * Apply rules to current profile item
     *
     * @param ProfileItemInterface $item
     * @param Collection $rules
     * @param bool $skipValidation
     * @param mixed $couponCode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyRules($item, $rules, $skipValidation, $couponCode)
    {
        $item->getProduct()->setForceValidate(true);
        $address = $item->getAddress();
        $appliedRuleIds = [];
        /* @var $rule Rule */
        foreach ($rules as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }
            if (!$skipValidation && !$rule->getActions()->validate($item)) {
                if (!$this->childrenValidationLocator->isProfileItemChildrenValidationRequired($item)) {
                    continue;
                }
                $childItems = $item->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
            }

            $this->applyRule($item, $rule, $address, $couponCode);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        return $appliedRuleIds;
    }

    /**
     * Apply Rule
     *
     * @param ProfileItemInterface $item
     * @param Rule $rule
     * @param ProfileAddressInterface $address
     * @param mixed $couponCode
     * @return $this
     */
    private function applyRule($item, $rule, $address, $couponCode)
    {
        $product = $item->getProduct();
        if ($product->getChildren() && $product->isChildrenCalculated()) {
            $cloneProduct = clone $product;
            $applyAll = $rule->getActions()->validate($cloneProduct);
            foreach ($product->getChildren() as $childProduct) {
                if ($applyAll || $rule->getActions()->validate($childProduct)) {
                    $discountData = $this->getDiscountData($childProduct, $rule, $address);
                    $this->setDiscountData($discountData, $childProduct);
                }
            }
        } else {
            $discountData = $this->getDiscountData($product, $rule, $address);
            $this->setDiscountData($discountData, $product);
        }

        $this->maintainAddressCouponCode($address, $rule, $couponCode);//todo here product has discount percent
        $this->addDiscountDescription($address, $rule);

        return $this;
    }

    /**
     * Get discount Data
     *
     * @param ProductInterface $item
     * @param Rule $rule
     * @param ProfileAddressInterface $address
     * @return DiscountData
     */
    private function getDiscountData($item, $rule, $address)
    {
        $qty = $this->validatorUtility->getItemQty($item, $rule);

        $discountCalculator = $this->calculatorFactory->create($rule->getSimpleAction());
        $qty = $discountCalculator->fixQuantity($qty, $rule);
        $discountData = $discountCalculator->calculate($rule, $item, $qty);
        $this->validatorUtility->deltaRoundingFix($discountData, $item);
        $this->validatorUtility->minFix($discountData, $item, $qty);

        return $discountData;
    }

    /**
     * Set Discount data
     *
     * @param DiscountData $discountData
     * @param ProductInterface $item
     * @return $this
     */
    private function setDiscountData($discountData, $item)
    {
        $item->setDiscountAmount($discountData->getAmount());
        $item->setBaseDiscountAmount($discountData->getBaseAmount());
        $item->setOriginalDiscountAmount($discountData->getOriginalAmount());
        $item->setBaseOriginalDiscountAmount($discountData->getBaseOriginalAmount());

        return $this;
    }

    /**
     * Set coupon code to address if $rule contains validated coupon
     *
     * @param ProfileAddressInterface $address
     * @param Rule $rule
     * @param mixed $couponCode
     * @return $this
     */
    public function maintainAddressCouponCode($address, $rule, $couponCode)
    {
        if ($rule->getCouponType() !== Rule::COUPON_TYPE_NO_COUPON) {
            $address->setCouponCode($couponCode);
        }

        return $this;
    }

    /**
     * Add rule discount description label to address object
     *
     * @param ProfileAddressInterface $address
     * @param Rule $rule
     * @return $this
     */
    public function addDiscountDescription($address, $rule)
    {
        $description = $address->getDiscountDescriptionArray();
        $ruleLabel = $rule->getStoreLabel($address->getProfile()->getStoreId());
        $label = '';
        if ($ruleLabel) {
            $label = $ruleLabel;
        } elseif ($address->getCouponCode() !== null && $address->getCouponCode() !== '') {
            $label = $address->getCouponCode();

            if ($rule->getDescription()) {
                $label = $rule->getDescription();
            }
        }

        if ($label !== '') {
            $description[$rule->getId()] = $label;
        }

        $address->setDiscountDescriptionArray($description);

        return $this;
    }
}
