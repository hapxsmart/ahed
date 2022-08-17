<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

/**
 * Class Composite
 * @package Aheadworks\AdvancedSearch\Model\Index\PostDataProcessor
 */
class Composite implements ProcessorInterface
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
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        foreach ($this->processors as $processor) {
            $data = $processor->prepareEntityData($data);
        }
        return $data;
    }
}
