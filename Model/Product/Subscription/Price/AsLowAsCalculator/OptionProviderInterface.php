<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;

/**
 * Interface OptionProviderInterface
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator
 */
interface OptionProviderInterface
{
    /**
     * @param $productId
     * @return SubscriptionOptionInterface[]
     */
    public function getAllSubscriptionOptions($productId);
}
