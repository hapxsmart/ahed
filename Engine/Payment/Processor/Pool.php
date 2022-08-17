<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor;

use Aheadworks\Sarp2\Engine\Payment\Processor\Type\LastPeriodHolder;
use Aheadworks\Sarp2\Engine\Payment\ProcessorInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Actual;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Reattempt;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Pool
 * @package Aheadworks\Sarp2\Engine\Payment\Processor
 */
class Pool
{
    /**
     * @var ProcessorInterface
     */
    private $instances = [];

    /**
     * @var array
     */
    private $processors = [
        PaymentInterface::TYPE_OUTSTANDING => [
            'className' => Outstanding::class,
            'sortOrder' => 0
        ],
        PaymentInterface::TYPE_PLANNED => [
            'className' => Planned::class,
            'sortOrder' => 10
        ],
        PaymentInterface::TYPE_ACTUAL => [
            'className' => Actual::class,
            'sortOrder' => 20
        ],
        PaymentInterface::TYPE_REATTEMPT => [
            'className' => Reattempt::class,
            'sortOrder' => 30
        ],
        PaymentInterface::TYPE_LAST_PERIOD_HOLDER => [
            'className' => LastPeriodHolder::class,
            'sortOrder' => 40
        ],
    ];

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Factory $factory
     * @param array $processors
     */
    public function __construct(
        Factory $factory,
        array $processors = []
    ) {
        $this->factory = $factory;
        $this->processors = array_merge($this->processors, $processors);
    }

    /**
     * Get payment processor instance
     *
     * @param string $paymentType
     * @return ProcessorInterface
     * @throws \Exception
     */
    public function getProcessor($paymentType)
    {
        if (!isset($this->instances[$paymentType])) {
            if (!isset($this->processors[$paymentType])) {
                throw new \InvalidArgumentException(
                    sprintf('Unknown payment processor: %s requested', $paymentType)
                );
            }
            $this->instances[$paymentType] = $this->factory->create($this->processors[$paymentType]['className']);
        }
        return $this->instances[$paymentType];
    }

    /**
     * Get configured payment types
     *
     * @return array
     */
    public function getConfiguredPaymentTypes()
    {
        $paymentTypes = [];

        /**
         * @param array $definition
         * @param string $paymentType
         * @return void
         */
        $walkCallback = function ($definition, $paymentType) use (&$paymentTypes) {
            $paymentTypes[$definition['sortOrder']] = $paymentType;
        };
        array_walk($this->processors, $walkCallback);

        /**
         * @param int $sortOrder1
         * @param int $sortOrder2
         * @return int
         */
        $sortCallback = function ($sortOrder1, $sortOrder2) {
            if ($sortOrder1 == $sortOrder2) {
                return 0;
            } else {
                return $sortOrder1 > $sortOrder2 ? 1 : -1;
            }
        };
        uksort($paymentTypes, $sortCallback);
        return array_values($paymentTypes);
    }
}
