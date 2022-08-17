<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria;

use Aheadworks\Sarp2\Model\Quote\Item\Grouping\CriterionInterface;

/**
 * Class Key
 * @package Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria
 */
class Key
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var CriterionInterface[]
     */
    private $criterionInstances = [];

    /**
     * @param $value
     * @param array $criterionInstances
     */
    public function __construct(
        $value,
        array $criterionInstances
    ) {
        $this->value = $value;
        $this->criterionInstances = $criterionInstances;
    }

    /**
     * Get key value
     *
     * @return string|null
     */
    public function getValue()
    {
        return !empty($this->value) ? $this->value : null;
    }

    /**
     * Get criteria instances
     *
     * @return CriterionInterface[]
     */
    public function getCriterionInstances()
    {
        return array_values($this->criterionInstances);
    }
}
