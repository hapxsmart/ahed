<?php
namespace Aheadworks\Sarp2\Model\Profile\Merged\Address;

use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address\Item as QuoteAddressItemSubstitute;
use Aheadworks\Sarp2\Model\Profile\Item\ToQAItemSubstitute as ProfileItemToQAItemSubstitute;

/**
 * Class ToQAItemSubstitute
 *
 * @package Aheadworks\Sarp2\Model\Profile\Merged\Address
 */
class ToQAItemSubstitute
{
    /**
     * @var ProfileItemToQAItemSubstitute
     */
    private $toAddressItemSubstitute;

    /**
     * @param ProfileItemToQAItemSubstitute $toAddressItemSubstitute
     */
    public function __construct(
        ProfileItemToQAItemSubstitute $toAddressItemSubstitute
    ) {
        $this->toAddressItemSubstitute = $toAddressItemSubstitute;
    }

    /**
     * Convert profiles to merged address items substitutes
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @param array $data
     * @return array
     */
    public function convertList($paymentsInfo, $data = [])
    {
        $items = [];
        foreach ($paymentsInfo as $info) {
            $paymentPeriod = $info->getPaymentPeriod();

            foreach ($info->getProfile()->getItems() as $profileItem) {
                $itemId = $profileItem->getItemId();

                if (!isset($items[$itemId])) {
                    $parentItemId = $profileItem->getParentItemId();
                    if ($parentItemId && !isset($items[$parentItemId])) {
                        $parent = $this->toAddressItemSubstitute->convert(
                            $profileItem->getParentItem(),
                            $paymentPeriod,
                            array_merge($data, ['parent_item' => null])
                        );
                        if (!$parent->getProduct()->getIsVirtual()) {
                            $items[$parentItemId] = $parent;
                        }
                    }
                    $parentItem = $items[$parentItemId] ?? null;
                    $item = $this->toAddressItemSubstitute->convert(
                        $profileItem,
                        $paymentPeriod,
                        array_merge($data, ['parent_item' => $parentItem])
                    );
                    if (!$item->getProduct()->getIsVirtual()) {
                        $items[$itemId] = $item;
                    }
                }
            }
        }

        $items = $this->updateChildrenListForConfigurableItems($items);

        return array_values($items);
    }

    /**
     * Update children item list for configurable items
     *
     * @param QuoteAddressItemSubstitute[] $itemList
     * @return QuoteAddressItemSubstitute[]
     */
    private function updateChildrenListForConfigurableItems($itemList)
    {
        foreach ($itemList as $item) {
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $parentItem->addChild($item);
            }
        }

        return $itemList;
    }
}
