<?php
namespace Aheadworks\Sarp2\Model\Plan\DataProvider\Processor;

/**
 * Interface ProcessorInterface
 *
 * @package Aheadworks\Sarp2\Model\Plan\DataProvider\Processor
 */
interface ProcessorInterface
{
    /**
     * Process DataProvider
     *
     * @param array $data
     * @return array
     */
    public function process($data);
}
