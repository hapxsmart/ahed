<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator\Provider;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator\OptionProviderInterface;

/**
 * Class Generic
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator\Provider
 */
class Generic implements OptionProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getAllSubscriptionOptions($productId)
    {
        return [];
    }
}
