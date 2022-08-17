<?php
namespace Aheadworks\Sarp2\Model\Profile\Item\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Product\Attribute\Source\SubscriptionType;
use Aheadworks\Sarp2\Model\Profile\Item;

/**
 * Class IsOneOffItem
 * @package Aheadworks\Sarp2\Model\Profile\Item\Checker
 */
class IsOneOffItem
{
    /**
     * Check is one-off profile item
     *
     * @param ProfileItemInterface $profileItem
     * @return bool
     */
    public function check(ProfileItemInterface $profileItem): bool
    {
        $productOptions = $profileItem->getProductOptions();

        return isset($productOptions['info_buyRequest'][Item::ONE_OFF_ITEM_OPTION])
            || (!isset($productOptions['info_buyRequest']['aw_sarp2_subscription_option'])
                && !isset($productOptions['aw_sarp2_subscription_option']['option_id']));
    }
}
