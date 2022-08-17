<?php
namespace Aheadworks\Sarp2\Model\Quote\Item;

use Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria\Key\Generator as KeyGenerator;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Grouping
 * @package Aheadworks\Sarp2\Model\Quote\Item
 */
class Grouping
{
    /**#@+
     * Grouping criterion codes
     */
    const CRITERION_PLAN_DEFINITION = 'plan_definition';
    const CRITERION_START_DATE = 'start_date';
    const CRITERION_IS_INITIAL_FEE_INCL_IN_PREPAYMENT = 'is_initial_fee_incl_in_prepayment';
    const CRITERION_IS_TRIAL_PRICE_INCL_IN_PREPAYMENT = 'is_trial_price_incl_in_prepayment';
    const CRITERION_IS_REGULAR_PRICE_INCL_IN_PREPAYMENT = 'is_regular_price_incl_in_prepayment';
    /**#@-*/

    /**
     * @var KeyGenerator
     */
    private $keyGenerator;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param KeyGenerator $keyGenerator
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        KeyGenerator $keyGenerator,
        Factory   $dataObjectFactory
    ) {
        $this->keyGenerator = $keyGenerator;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Group quote items by specified criteria
     *
     * @param Item[] $quoteItems
     * @param array $criteria
     * @return array
     */
    public function group(
        $quoteItems,
        array $criteria = [
            self::CRITERION_PLAN_DEFINITION,
            self::CRITERION_IS_INITIAL_FEE_INCL_IN_PREPAYMENT,
            self::CRITERION_IS_TRIAL_PRICE_INCL_IN_PREPAYMENT,
            self::CRITERION_IS_REGULAR_PRICE_INCL_IN_PREPAYMENT
        ]
    ) {
        $groupsData = [];

        $parentItemId = null;
        $parentGroup = null;
        foreach ($quoteItems as $item) {
            if (!$item->getParentItemId()) {
                $key = $this->keyGenerator->generate($item, $criteria);
                $keyValue = $key->getValue();
                if ($keyValue) {
                    if (!isset($groupsData[$keyValue])) {
                        $groupsData[$keyValue] = [];
                    }
                    $groupsData[$keyValue]['items'][] = $item;
                    $childItems = $this->getChildItems($item->getItemId(), $quoteItems);
                    foreach ($childItems as $childItem) {
                        $groupsData[$keyValue]['items'][] = $childItem;
                    }

                    foreach ($key->getCriterionInstances() as $criterion) {
                        $fieldName = $criterion->getResultName();
                        if (!isset($groupsData[$keyValue][$fieldName])) {
                            $groupsData[$keyValue][$fieldName] = $criterion->getResultValue($item);
                        }
                    }
                }
            }
        }

        $groups = [];
        foreach ($groupsData as $data) {
            $groups[] = $this->dataObjectFactory->create($data);
        }
        return $groups;
    }

    /**
     * Get child items
     *
     * @param int $parentItemId
     * @param Item[] $quoteItems
     * @return Item[]
     */
    private function getChildItems($parentItemId, $quoteItems)
    {
        $childItems = [];
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getParentItemId() == $parentItemId) {
                $childItems[] = $quoteItem;
            }
        }

        return $childItems;
    }
}
