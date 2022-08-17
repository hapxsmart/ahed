<?php
namespace Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address\Item\Checker;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription\CheckerInterface;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address\Item
    as SubstituteQuoteAddressItem;

/**
 * Class IsSubscription
 *
 * @package Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address\Item\Checker
 */
class IsSubscription implements CheckerInterface
{
    /**
     * @inheritDoc
     * @param SubstituteQuoteAddressItem $item
     */
    public function check($item)
    {
        //TODO: update to use ['info_buyRequest']['aw_sarp2_subscription_type'] value if necessary
        return true;
    }
}
