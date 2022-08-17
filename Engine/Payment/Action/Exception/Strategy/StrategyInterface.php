<?php
namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception\Strategy;

/**
 * Interface ResolveStrategyInterface
 *
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception\Strategy
 */
interface StrategyInterface
{
    /**
     * Apply strategy
     *
     * @param \Exception $exception
     * @throw \Exception
     */
    public function apply($exception);
}
