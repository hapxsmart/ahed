<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

/**
 * Interface ProcessorInterface
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Details\Config
 */
interface ProcessorInterface
{
    /**
     * Process config array
     *
     * @param array $config
     * @return array
     */
    public function process(array $config): array;
}
