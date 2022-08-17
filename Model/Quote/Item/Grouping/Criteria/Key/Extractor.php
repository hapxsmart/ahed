<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria\Key;

use Aheadworks\Sarp2\Model\Quote\Item\Grouping\CriterionPool;
use Magento\Quote\Model\Quote\Item;

class Extractor
{
    /**
     * @var CriterionPool
     */
    private $criterionPool;

    /**
     * @param CriterionPool $criterionPool
     */
    public function __construct(
        CriterionPool $criterionPool
    ) {
        $this->criterionPool = $criterionPool;
    }

    /**
     * Extract data for the key generation from the quote item according to the list of given criteria
     *
     * @param Item $item
     * @param array $criteria
     * @return array
     */
    public function extractKeyData($item, $criteria)
    {
        $instances = [];
        $keyParts = [];
        foreach ($criteria as $criteriaCode) {
            $criterion = $this->criterionPool->getCriterion($criteriaCode);
            if ($criterion) {
                $value = $criterion->getValue($item);
                if ($value !== null) {
                    $keyParts[] = $value;
                    $instances[] = $criterion;
                }
            }
        }

        return [
            'value' => implode('-', $keyParts),
            'criterionInstances' => $instances
        ];
    }
}
