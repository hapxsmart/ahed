<?php
namespace Aheadworks\Sarp2\Model\Profile\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Item\Merge\Result;
use Aheadworks\Sarp2\Model\Profile\Item\Merge\ResultFactory;

/**
 * Class Merger
 * @package Aheadworks\Sarp2\Model\Profile\Item
 */
class Merger
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        ResultFactory $resultFactory
    ) {
        $this->resultFactory = $resultFactory;
    }

    /**
     * Merge profile items
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return Result[]
     */
    public function mergeItems($paymentsInfo)
    {
        $result = [];
        $pairs = $this->getItemPaymentPeriodPairs($paymentsInfo);

        $merged = [];
        $itemsCount = count($pairs);
        if ($itemsCount) {
            list($parents, $children) = $this->split($pairs);
            $this->collect($parents[0], $children, $merged);

            for ($index = 1; $index < count($parents); $index++) {
                $mergeCandidate = $parents[$index];
                $this->collect($mergeCandidate, $children, $merged);
            }
        }

        /**
         * @param array $pair
         * @return bool
         */
        $resultCollectCallback = function ($pair) use (&$result) {
            $result[] = $this->resultFactory->create(
                [
                    'item' => $pair[0],
                    'paymentPeriod' => $pair[1]
                ]
            );
        };
        array_walk($merged, $resultCollectCallback);
        return $result;
    }

    /**
     * Get 'item - payment period' pairs
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return array
     */
    private function getItemPaymentPeriodPairs($paymentsInfo)
    {
        $pairs = [];

        /**
         * @param PaymentInfoInterface $info
         * @return void
         */
        $callback = function ($info) use (&$pairs) {
            foreach ($info->getProfile()->getItems() as $item) {
                $pairs[] = [$item, $info->getPaymentPeriod()];
            }
        };
        array_walk($paymentsInfo, $callback);

        return $pairs;
    }

    /**
     * Split 'item - payment period' pairs into parents and children
     *
     * @param array $pairs
     * @return array
     */
    private function split($pairs)
    {
        $parents = [];
        $children = [];

        /**
         * @param array $pair
         * @return void
         */
        $callback = function ($pair) use (&$parents, &$children) {
            /** @var ProfileItemInterface $item */
            $item = $pair[0];
            $parentId = $item->getParentItemId();
            if ($parentId) {
                if (!isset($children[$parentId])) {
                    $children[$parentId] = [$pair];
                } else {
                    $children[$parentId][] = $pair;
                }
            } else {
                $parents[] = $pair;
            }
        };
        array_walk($pairs, $callback);

        return [$parents, $children];
    }

    /**
     * Add 'item - payment period' pair to result set
     *
     * @param array $pair
     * @param array $children
     * @param array $set
     * @return array
     */
    private function collect($pair, &$children, &$set)
    {
        $set[] = $pair;

        /** @var ProfileItemInterface $item */
        $item = $pair[0];
        $itemId = $item->getItemId();
        if (isset($children[$itemId])) {

            /**
             * @param array $childPair
             * @return void
             */
            $callback = function ($childPair) use (&$set) {
                $set[] = $childPair;
            };
            array_walk($children[$itemId], $callback);
        }

        return $set;
    }
}
