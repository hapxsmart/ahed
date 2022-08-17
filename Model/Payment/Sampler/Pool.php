<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler;

use Aheadworks\Sarp2\Model\Payment\SamplerInterface;

/**
 * Class Pool
 * @package Aheadworks\Sarp2\Model\Payment\Sampler
 */
class Pool
{
    /**
     * @var SamplerInterface[]
     */
    private $instances = [];

    /**
     * @var array
     */
    private $samplers = [];

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Factory $factory
     * @param array $samplers
     */
    public function __construct(
        Factory $factory,
        array $samplers = []
    ) {
        $this->factory = $factory;
        $this->samplers = array_merge($this->samplers, $samplers);
    }

    /**
     * Get payment method sampler instance
     *
     * @param string $methodCode
     * @return SamplerInterface
     */
    public function getSampler($methodCode)
    {
        if (!isset($this->instances[$methodCode])) {
            if (!isset($this->samplers[$methodCode])) {
                throw new \InvalidArgumentException(
                    sprintf('Unknown payment sampler: %s requested', $methodCode)
                );
            }
            $this->instances[$methodCode] = $this->factory->create($this->samplers[$methodCode]);
        }
        return $this->instances[$methodCode];
    }
}
