<?php
namespace Aheadworks\Sarp2\Model\Sales;

use Magento\Framework\DataObject;

/**
 * Class CopySelf
 * @package Aheadworks\Sarp2\Model\Sales
 */
class CopySelf
{
    /**
     * Perform copy of self fields by map
     *
     * @param DataObject|array $object
     * @param array $map
     * @return DataObject|array
     */
    public function copyByMap($object, array $map)
    {
        $objectData = $object instanceof DataObject
            ? $object->getData()
            : $object;

        foreach ($map as $mapItem) {
            list($from, $to) = $mapItem;

            if (!$from || !$to) {
                throw new \InvalidArgumentException('Incorrect map item.');
            }
            if (isset($objectData[$from])) {
                $this->setFieldValue($object, $to, $objectData[$from]);
            }
        }

        return $object;
    }

    /**
     * @param DataObject|array $object
     * @param string $field
     * @param mixed $value
     * @return void
     */
    private function setFieldValue(&$object, $field, $value)
    {
        if ($object instanceof DataObject) {
            $object->setDataUsingMethod($field, $value);
        } else {
            $object[$field] = $value;
        }
    }
}
