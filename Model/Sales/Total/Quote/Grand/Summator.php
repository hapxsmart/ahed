<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Grand;

/**
 * Class Summator
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Grand
 */
class Summator
{
    /**
     * @var array
     */
    private $amounts = [];

    /**
     * Set total amount
     *
     * @param string $code
     * @param float $amount
     * @return void
     */
    public function setAmount($code, $amount)
    {
        $this->amounts[$code] = $amount;
    }

    /**
     * Get totals sum
     *
     * @param string $prefix
     * @return float
     */
    public function getSum($prefix)
    {
        $result = 0;
        foreach ($this->amounts as $code => $amount) {
            $codeParts = explode('_', (string)$code);
            if (count($codeParts) > 1 && $codeParts[0] == $prefix) {
                $result += $amount;
                unset($this->amounts[$code]);
            }
        }
        return $result;
    }
}
