<?php
namespace Aheadworks\Sarp2\Model\SalesRule\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Model\Product;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as SalesRuleProductConditionCombine;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProductCondition;
use Magento\Rule\Model\Condition\Context as RuleConditionContext;
use Aheadworks\Sarp2\Model\SalesRule\Rule\Condition\Product as Sarp2SalesRuleProductCondition;

class Combine extends SalesRuleProductConditionCombine
{
    /**
     * @var Sarp2SalesRuleProductCondition
     */
    private $sarp2SalesRuleProductCondition;

    /**
     * @var IsRecalculationApplicable
     */
    private $isRecalculationApplicable;

    /**
     * @param RuleConditionContext $context
     * @param SalesRuleProductCondition $ruleConditionProduct
     * @param Sarp2SalesRuleProductCondition $sarp2SalesRuleProductCondition
     * @param IsRecalculationApplicable $isRecalculationApplicable
     * @param array $data
     */
    public function __construct(
        RuleConditionContext $context,
        SalesRuleProductCondition $ruleConditionProduct,
        Sarp2SalesRuleProductCondition $sarp2SalesRuleProductCondition,
        IsRecalculationApplicable $isRecalculationApplicable,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->sarp2SalesRuleProductCondition = $sarp2SalesRuleProductCondition;
        $this->isRecalculationApplicable = $isRecalculationApplicable;
        $this->setType(self::class);
    }

    /**
     * @inheritDoc
     */
    public function getNewChildSelectOptions()
    {
        $conditionList = parent::getNewChildSelectOptions();

        $conditionList = $this->modifyConditionsCombinationValue($conditionList);
        $conditionList = $this->addAwSarp2Conditions($conditionList);

        return $conditionList;
    }

    /**
     * @inheritDoc
     */
    public function validate(AbstractModel $model)
    {
        if (!$this->isRecalculationApplicable->check($model)) {
            return parent::validate($model);
        }

        return $this->validateForRecalculation($model)
            ? false
            : parent::validate($model);
    }

    /**
     * Validate for recalculation
     *
     * @param AbstractModel $model
     * @return bool
     */
    private function validateForRecalculation(AbstractModel $model)
    {
        $product = $model instanceof Product ? $model : $model->getProduct();
        $rule = $this->getRule();
        $forceValidate = false;
        foreach ($this->getConditions() as $condition) {
            if ($condition->getAttribute()
                == Sarp2SalesRuleProductCondition::IS_SUBSCRIPTION_QUOTE_ITEM_SPECIAL_ATTRIBUTE_KEY) {
                $forceValidate = true;
                break;
            }
        }
        return (!$rule->getCouponCode() || $rule->getSimpleAction() == Rule::BUY_X_GET_Y_ACTION)
            && !$product->getForceValidate() && !$forceValidate;
    }

    /**
     * Modify value class for conditions combination item
     *
     * @param array $conditionList
     * @return array
     */
    protected function modifyConditionsCombinationValue($conditionList)
    {
        foreach ($conditionList as &$conditionItem) {
            if (isset($conditionItem['value'])
                && $conditionItem['value'] === SalesRuleProductConditionCombine::class
            ) {
                $conditionItem['value'] = $this->getType();
            }
        }
        return $conditionList;
    }

    /**
     * Add AW SARP2 conditions to the condition list
     *
     * @param array $conditionList
     * @return array
     */
    protected function addAwSarp2Conditions($conditionList)
    {
        $sarp2SpecialAttributeList = $this->sarp2SalesRuleProductCondition
            ->loadAttributeOptions()
            ->getAttributeOption()
        ;
        $preparedSarp2AttributeList = [];
        foreach ($sarp2SpecialAttributeList as $attributeCode => $attributeLabel) {
            $preparedSarp2AttributeList[] = [
                'value' => Sarp2SalesRuleProductCondition::class . '|' . $attributeCode,
                'label' => $attributeLabel,
            ];
        }
        $conditionList = array_merge_recursive(
            $conditionList,
            [
                ['label' => __('AW SARP2 Attribute'), 'value' => $preparedSarp2AttributeList],
            ]
        );
        return $conditionList;
    }
}
