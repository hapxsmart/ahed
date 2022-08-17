<?php
namespace Aheadworks\Sarp2\Model\Plan\DataProvider\Processor;

/**
 * Class Composite
 *
 * @package Aheadworks\Sarp2\Model\Plan\DataProvider\Processor
 */
class Composite
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @return array
     */
    public function prepareData($data)
    {
        foreach ($this->processors as $processor) {
            $data = $processor->process($data);
        }
        return $data;
    }
}
