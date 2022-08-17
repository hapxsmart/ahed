<?php
namespace Aheadworks\Sarp2\Model\Directory;

use Magento\Directory\Model\PriceCurrency as MagentoPriceCurrency;

class PriceCurrency extends MagentoPriceCurrency
{
    /**
     * Round price
     *
     * @param float $price
     * @return float
     */
    public function round($price)
    {
        return ceil($price * 100)/100;
    }

    /**
     * Round price with precision
     *
     * @param float $price
     * @param int $precision
     * @return float
     */
    public function roundPrice($price, $precision = self::DEFAULT_PRECISION)
    {
        $precision = pow(10, $precision);
        return ceil($price * $precision)/$precision;
    }
}
