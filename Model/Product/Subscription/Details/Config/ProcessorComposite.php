<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

/**
 * Class ProviderPool
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Details\Config
 */
class ProcessorComposite implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors = [];

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(
        array $processors = []
    ) {
        $this->processors = array_merge($this->processors, $processors);
    }

    /**
     * @inheritDoc
     */
    public function process(array $config): array
    {
        foreach ($this->processors as $processor) {
            $config = $processor->process($config);
        }

        return $config;
    }
}
