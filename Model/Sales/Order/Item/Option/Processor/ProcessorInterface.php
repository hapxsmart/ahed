<?php
namespace Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor;

use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Interface ProcessorInterface
 *
 * @package Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor
 */
interface ProcessorInterface
{
    /**
     * Process order item product options
     *
     * @param OrderItem $item
     * @param array $options
     * @return array
     */
    public function process(OrderItem $item, array $options);
}
