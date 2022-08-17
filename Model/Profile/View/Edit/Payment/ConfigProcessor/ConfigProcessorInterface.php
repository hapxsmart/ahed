<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProcessor;

/**
 * Interface ConfigProcessorInterface
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProcessor
 */
interface ConfigProcessorInterface
{
    /**
     * Process config
     *
     * @param array $config
     * @return array
     */
    public function process($config);
}
