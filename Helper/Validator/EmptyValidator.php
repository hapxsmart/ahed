<?php
namespace Aheadworks\Sarp2\Helper\Validator;

/**
 * Class NotEmptyValidator
 *
 * @package Aheadworks\Sarp2\Helper\Validator
 */
class EmptyValidator
{
    /**
     * Returns true if $value is empty.
     *
     * "" - true
     * 0 - false
     * 0.0 - false
     * "0" - false
     * 42 - false
     * 1337.0 - false
     * "ab" - false
     * [] - true
     * false - true
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        return empty($value) && !is_numeric($value);
    }
}
