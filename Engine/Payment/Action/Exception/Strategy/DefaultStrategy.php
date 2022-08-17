<?php
namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception\Strategy;

use Aheadworks\Sarp2\Engine\Exception\ScheduledPaymentException;

/**
 * Class DefaultStrategy
 *
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception\Resolver
 */
class DefaultStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($exception)
    {
        $exceptionClass = get_class($exception);
        $message = __('"' . $exceptionClass . '" has been raised with message: ' . $exception->getMessage());

        throw new ScheduledPaymentException(
            $message,
            $exception,
            $exception->getCode()
        );
    }
}
