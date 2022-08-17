<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Processor\Configurable;

use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\ProcessorInterface;

class AsLowAs implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $config): array
    {
        if ($config['asLowAsPrice'] ?? null) {
            $config['selectedSubscriptionOptionId'] = $config['asLowAsPrice']['subscriptionOptionId'];
        }

        return $config;
    }
}
