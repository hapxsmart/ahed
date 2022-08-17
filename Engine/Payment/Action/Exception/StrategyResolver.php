<?php
namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception;

use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Strategy\DefaultStrategy;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Strategy\StrategyInterface;

/**
 * Class StrategyResolver
 *
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception
 */
class StrategyResolver
{
    /**
     * @var StrategyInterface[]
     */
    private $strategyPool;

    /**
     * @var StrategyInterface
     */
    private $defaultStrategy;

    /**
     * @param DefaultStrategy $default
     * @param array $strategyPool
     */
    public function __construct(
        DefaultStrategy $default,
        array $strategyPool = []
    ) {
        $this->defaultStrategy = $default;
        $this->strategyPool = $strategyPool;
    }

    /**
     * Get strategy for specified exception
     *
     * @param string $paymentMethod
     * @return StrategyInterface
     */
    public function getStrategy($paymentMethod)
    {
        if (isset($this->strategyPool[$paymentMethod])
            && $this->strategyPool[$paymentMethod] instanceof StrategyInterface
        ) {
            return $this->strategyPool[$paymentMethod];
        }
        return $this->defaultStrategy;
    }
}
