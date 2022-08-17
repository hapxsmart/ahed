<?php
namespace Aheadworks\Sarp2\Model\Profile\Address;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\Address\Resolver\QuoteAddressItemSubstitute as QuoteAddressItemSubstituteResolver;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address\Item as QuoteAddressItemSubstitute;
use Aheadworks\Sarp2\Model\Profile\Item\ToQAItemSubstitute as ProfileItemToQAItemSubstitute;

/**
 * Class ToQAItemSubstitute
 *
 * @package Aheadworks\Sarp2\Model\Profile\Address
 */
class ToQAItemSubstitute
{
    /**
     * @var QuoteAddressItemSubstituteResolver
     */
    private $quoteAddressItemSubstituteResolver;

    /**
     * @var ProfileItemToQAItemSubstitute
     */
    private $toAddressItemSubstitute;

    /**
     * @param QuoteAddressItemSubstituteResolver $quoteAddressItemSubstituteResolver
     * @param ProfileItemToQAItemSubstitute $toAddressItemSubstitute
     */
    public function __construct(
        QuoteAddressItemSubstituteResolver $quoteAddressItemSubstituteResolver,
        ProfileItemToQAItemSubstitute $toAddressItemSubstitute
    ) {
        $this->quoteAddressItemSubstituteResolver = $quoteAddressItemSubstituteResolver;
        $this->toAddressItemSubstitute = $toAddressItemSubstitute;
    }

    /**
     * Convert profile items to address items substitutes
     *
     * @param ProfileItemInterface[] $profileItems
     * @param string $totalsGroupCode
     * @param array $data
     * @return QuoteAddressItemSubstitute[]
     */
    public function convertList($profileItems, $totalsGroupCode, $data = [])
    {
        $items = [];
        foreach ($profileItems as $profileItem) {
            $itemId = $this->quoteAddressItemSubstituteResolver->getItemUniqueId($profileItem);

            if (!isset($items[$itemId])) {
                $parentItemId = null;
                $parentItem = $profileItem->getParentItem();
                if ($parentItem) {
                    $parentItemId = $this->quoteAddressItemSubstituteResolver->getItemUniqueId($parentItem);
                    if (!isset($items[$parentItemId])) {
                        $items[$parentItemId] = $this->toAddressItemSubstitute->convert(
                            $profileItem->getParentItem(),
                            $totalsGroupCode,
                            array_merge($data, ['parent_item' => null])
                        );
                    }
                }
                $parentItem = $items[$parentItemId] ?? null;
                $items[$itemId] = $this->toAddressItemSubstitute->convert(
                    $profileItem,
                    $totalsGroupCode,
                    array_merge($data, ['parent_item' => $parentItem])
                );
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
