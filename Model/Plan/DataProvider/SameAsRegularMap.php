<?php
namespace Aheadworks\Sarp2\Model\Plan\DataProvider;

/**
 * Class SameAsRegularMap
 *
 * @package Aheadworks\Sarp2\Model\Plan
 */
class SameAsRegularMap
{
    /**
     * @var array
     */
    private $map;

    /**
     * @param array $map
     */
    public function __construct($map)
    {
        $this->map = $map;
    }

    /**
     * Retrieve same fields map
     *
     * @return array
     */
    public function get()
    {
        return $this->map;
    }
}
