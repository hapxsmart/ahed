<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax;

use Magento\Framework\DataObject;

/**
 * Class Keyer
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax
 */
class Keyer
{
    /**
     * Key items according to getter call result
     *
     * @param DataObject[] $items
     * @param string $getter
     * @return DataObject[]
     */
    public function keyBy(array $items, $getter)
    {
        $result = [];
        foreach ($items as $item) {
            $result[$item->$getter()] = $item;
        }
        return $result;
    }
}
