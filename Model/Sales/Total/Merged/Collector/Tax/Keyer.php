<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax;

use Magento\Framework\DataObject;

/**
 * Class Keyer
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax
 */
class Keyer
{
    /**
     * Key item pairs according to item getter call result
     *
     * @param array $pairs
     * @param string $getter
     * @param int $index
     * @return array
     */
    public function keyPairsBy(array $pairs, $getter, $index = 0)
    {
        $result = [];
        foreach ($pairs as $pair) {
            /** @var DataObject $item */
            $item = $pair[$index]->getItem();
            $result[$item->$getter()] = $pair;
        }
        return $result;
    }
}
