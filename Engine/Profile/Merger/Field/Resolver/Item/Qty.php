<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\ResolverInterface;

/**
 * Class Qty
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Item
 */
class Qty implements ResolverInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getResolvedValue($entities, $field)
    {
        $qty = 0;

        /**
         * @param ProfileItemInterface $item
         * @return void
         */
        $callback = function ($item) use (&$qty) {
            $qty += $item->getQty();
        };
        array_walk($entities, $callback);
        return $qty;
    }
}
