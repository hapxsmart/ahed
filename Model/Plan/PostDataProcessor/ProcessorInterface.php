<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

/**
 * Interface ProcessorInterface
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
interface ProcessorInterface
{
    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data);
}
