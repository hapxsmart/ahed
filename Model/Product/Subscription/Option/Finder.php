<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Option;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Finder
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Option
 */
class Finder
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var SortOrderResolver
     */
    private $sortOrderResolver;

    /**
     * @var array
     */
    private $firstOptionPerProductCache = [];

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param SortOrderResolver $sortOrderResolver
     * @param array $firstOptionPerProductCache
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        SortOrderResolver $sortOrderResolver,
        array $firstOptionPerProductCache = []
    ) {
        $this->optionRepository = $optionRepository;
        $this->sortOrderResolver = $sortOrderResolver;
        $this->firstOptionPerProductCache = $firstOptionPerProductCache;
    }

    /**
     * Get the first option from the sorted option list
     *
     * @param int $productId
     * @return SubscriptionOptionInterface|null
     * @throws LocalizedException
     */
    public function getFirstOption($productId)
    {
        if (array_key_exists($productId, $this->firstOptionPerProductCache)) {
            return $this->firstOptionPerProductCache[$productId];
        }

        $sortedOptionList = $this->getSortedOptions($productId);
        if (empty($sortedOptionList)) {
            $this->firstOptionPerProductCache[$productId] = null;
        } else {
            $this->firstOptionPerProductCache[$productId] = reset($sortedOptionList);
        }

        return $this->firstOptionPerProductCache[$productId];
    }

    /**
     * Get sorted options
     *
     * @param int $productId
     * @return SubscriptionOptionInterface[]
     * @throws LocalizedException
     */
    public function getSortedOptions($productId)
    {
        $options = $this->optionRepository->getList($productId);
        $optionsToSort = [];
        $optionsWithDefaultOrder = [];
        foreach ($options as $option) {
            if ($this->sortOrderResolver->getSortOrder($option) === null) {
                $optionsWithDefaultOrder[] = $option;
            } else {
                $optionsToSort[] = $option;
            }
        }
        usort($optionsToSort, [$this, 'compare']);

        return array_merge($optionsToSort, $optionsWithDefaultOrder);
    }

    /**
     * Compare sort order of option A with sort order of option B
     *
     * @param $a
     * @param $b
     * @return int
     */
    public function compare($a, $b)
    {
        $sortOrderA = $this->sortOrderResolver->getSortOrder($a);
        $sortOrderB = $this->sortOrderResolver->getSortOrder($b);
        if ($sortOrderA == $sortOrderB) {
            return 0;
        }

        return ($sortOrderA > $sortOrderB) ? 1 : -1;
    }
}
