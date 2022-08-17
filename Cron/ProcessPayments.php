<?php
namespace Aheadworks\Sarp2\Cron;

use Aheadworks\Sarp2\Engine\EngineInterface;

/**
 * Class ProcessPayments
 * @package Aheadworks\Sarp2\Cron
 */
class ProcessPayments
{
    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @param EngineInterface $engine
     */
    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Perform processing of pending payments
     *
     * @return void
     */
    public function execute()
    {
        $this->engine->processPaymentsForToday();
    }
}
