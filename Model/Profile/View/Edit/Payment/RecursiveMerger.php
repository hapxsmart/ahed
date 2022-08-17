<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment;

/**
 * Class RecursiveMerger
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment
 */
class RecursiveMerger
{
    /**
     * Perform recursive config merging
     *
     * @param array $target
     * @param array $source
     * @return array
     */
    public function merge(array $target, array $source)
    {
        foreach ($source as $key => $value) {
            if (is_array($value)) {
                if (!isset($target[$key])) {
                    $target[$key] = [];
                }
                $target[$key] = $this->merge($target[$key], $value);
            } else {
                $target[$key] = $value;
            }
        }
        return $target;
    }
}
