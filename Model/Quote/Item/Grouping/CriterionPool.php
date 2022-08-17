<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping;

/**
 * Class CriterionPool
 * @package Aheadworks\Sarp2\Model\Quote\Item\Grouping
 */
class CriterionPool
{
    /**
     * @var CriterionInterface[]
     */
    private $criterionInstances = [];

    /**
     * @var array
     */
    private $criteria = [];

    /**
     * @var CriterionFactory
     */
    private $criterionFactory;

    /**
     * @param CriterionFactory $criterionFactory
     * @param array $criteria
     */
    public function __construct(
        CriterionFactory $criterionFactory,
        array $criteria = []
    ) {
        $this->criterionFactory = $criterionFactory;
        $this->criteria = array_merge($this->criteria, $criteria);
    }

    /**
     * Get grouping criterion instance
     *
     * @param string $code
     * @return CriterionInterface|null
     */
    public function getCriterion($code)
    {
        if (!isset($this->criterionInstances[$code])) {
            $this->criterionInstances[$code] = isset($this->criteria[$code])
                ? $this->criterionFactory->create($this->criteria[$code])
                : null;
        }
        return $this->criterionInstances[$code];
    }
}
