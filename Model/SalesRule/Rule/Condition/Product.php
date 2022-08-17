<?php
namespace Aheadworks\Sarp2\Model\SalesRule\Rule\Condition;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription\Pool
    as IsSubscriptionCheckerPool;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProductCondition;
use Magento\Rule\Model\Condition\Context as RuleConditionContext;
use Magento\Backend\Helper\Data as BackendDataHelper;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as EavEntityAttributeSetCollection;
use Magento\Framework\Locale\FormatInterface as LocaleFormatInterface;
use Aheadworks\Sarp2\Model\SalesRule\Rule\Condition\Product\Attribute\ConfigStorage
    as ConditionAttributeConfigStorage;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;

/**
 * Class Product
 *
 * @package Aheadworks\Sarp2\Model\SalesRule\Rule\Condition
 */
class Product extends SalesRuleProductCondition
{
    /**
     * Special attributes array key for quote item is subscription flag
     */
    const IS_SUBSCRIPTION_QUOTE_ITEM_SPECIAL_ATTRIBUTE_KEY = 'quote_item_is_subscription';

    /**
     * @var ConditionAttributeConfigStorage
     */
    private $conditionAttributeConfigStorage;

    /**
     * @var IsSubscriptionCheckerPool
     */
    private $isSubscriptionCheckerPool;

    /**
     * @param RuleConditionContext $context
     * @param BackendDataHelper $backendData
     * @param EavConfig $config
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductResourceModel $productResource
     * @param EavEntityAttributeSetCollection $attrSetCollection
     * @param LocaleFormatInterface $localeFormat
     * @param ConditionAttributeConfigStorage $conditionAttributeConfigStorage
     * @param IsSubscriptionCheckerPool $isSubscriptionCheckerPool
     * @param array $data
     * @param ProductCategoryList|null $categoryList
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RuleConditionContext $context,
        BackendDataHelper $backendData,
        EavConfig $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductResourceModel $productResource,
        EavEntityAttributeSetCollection $attrSetCollection,
        LocaleFormatInterface $localeFormat,
        ConditionAttributeConfigStorage $conditionAttributeConfigStorage,
        IsSubscriptionCheckerPool $isSubscriptionCheckerPool,
        array $data = [],
        ProductCategoryList $categoryList = null
    ) {
        $this->conditionAttributeConfigStorage = $conditionAttributeConfigStorage;
        $this->isSubscriptionCheckerPool = $isSubscriptionCheckerPool;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data,
            $categoryList
        );
    }

    /**
     * @inheritDoc
     */
    public function validate(AbstractModel $model)
    {
        $attributeCode = $this->getAttribute();

        if ($attributeCode === self::IS_SUBSCRIPTION_QUOTE_ITEM_SPECIAL_ATTRIBUTE_KEY) {
            $isSubscriptionChecker = $this->isSubscriptionCheckerPool->getCheckerForItem($model);
            $isSubscription = ($isSubscriptionChecker) ? $isSubscriptionChecker->check($model) : false;
            return $this->validateAttribute($isSubscription ? '1' : '0');
        }

        return parent::validate($model);
    }

    /**
     * @inheritdoc
     */
    public function loadAttributeOptions()
    {
        $attributes = [];

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInputType()
    {
        $inputType = $this->conditionAttributeConfigStorage->getInputType($this->getAttribute());
        if (!empty($inputType)) {
            return $inputType;
        }
        return parent::getInputType();
    }

    /**
     * @inheritDoc
     */
    public function getValueElementType()
    {
        $valueElementType = $this->conditionAttributeConfigStorage->getValueElementType($this->getAttribute());
        if (!empty($valueElementType)) {
            return $valueElementType;
        }
        return parent::getValueElementType();
    }

    /**
     * @inheritDoc
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes[self::IS_SUBSCRIPTION_QUOTE_ITEM_SPECIAL_ATTRIBUTE_KEY] =
            $this->conditionAttributeConfigStorage->getLabel(
                self::IS_SUBSCRIPTION_QUOTE_ITEM_SPECIAL_ATTRIBUTE_KEY
            )
        ;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareValueOptions()
    {
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');

        if ($selectReady && $hashedReady) {
            return $this;
        }

        $selectOptions = $this->conditionAttributeConfigStorage->getSelectOptionList($this->getAttribute());
        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);
        return parent::_prepareValueOptions();
    }
}
