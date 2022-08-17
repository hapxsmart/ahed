<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Class Pool
 *
 * @package Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription
 */
class Pool
{
    /**
     * @var CheckerInterface[]
     */
    private $checkerList;

    /**
     * @param CheckerInterface[] $checkerList
     */
    public function __construct(
        array $checkerList = []
    ) {
        $this->checkerList = $checkerList;
    }

    /**
     * Retrieve checker instance for specific item
     *
     * @param ItemInterface $item
     * @return CheckerInterface|null
     */
    public function getCheckerForItem($item)
    {
        $checker = null;
        $itemClass = get_class($item);
        if (isset($this->checkerList[$itemClass])
            && $this->checkerList[$itemClass] instanceof CheckerInterface
        ) {
            $checker = $this->checkerList[$itemClass];
        }
        return $checker;
    }
}
