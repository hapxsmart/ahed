<?php
namespace Aheadworks\Sarp2\Model\Sales\Order\Item\Option;

use Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor\ProcessorInterface;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class ProcessorComposite
 *
 * @package Aheadworks\Sarp2\Model\Sales\Order\Item\Option
 */
class ProcessorComposite implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * ProcessorComposite constructor.
     *
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function process(OrderItem $item, array $options)
    {
        foreach ($this->processors as $processor) {
            $options = $processor->process($item, $options);
        }

        return $options;
    }
}
