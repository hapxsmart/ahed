<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation;

use Aheadworks\Sarp2\Model\Plan\Source\PriceRounding;

/**
 * Class Rounder
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation
 */
class Rounder
{
    /**
     * Round subscription price
     *
     * @param $amount
     * @param $roundingType
     * @return float
     */
    public function round($amount, $roundingType)
    {
        $result = $amount;

        $intPart = floor($result);
        if ($roundingType == PriceRounding::UP_TO_XX_99) {
            $result = $intPart + 0.99;
        } elseif ($roundingType == PriceRounding::UP_TO_XX_90) {
            $result = $intPart + 0.9;
        } elseif ($roundingType == PriceRounding::DOWN_TO_XX_99) {
            if ($intPart > 1) {
                $intPart -= 1;
            }
            $result = $intPart + 0.99;
        } elseif ($roundingType == PriceRounding::DOWN_TO_XX_90) {
            if ($intPart > 1) {
                $intPart -= 1;
            }
            $result = $intPart + 0.9;
        }

        $tens = floor($intPart / 10);
        if ($roundingType == PriceRounding::UP_TO_X9_00) {
            if ($tens > 0) {
                if (($intPart < ($tens * 10 + 9)) && ($amount > ($tens * 10 + 9))) {
                    $result = $tens * 10 + 9;
                } elseif (($intPart >= ($tens * 10 + 9)) && ($amount > (($tens + 1) * 10 + 9))) {
                    $result = ($tens + 1) * 10 + 9;
                } else {
                    $result = ($tens - 1) * 10 + 9;
                }
            }
        } elseif ($roundingType == PriceRounding::DOWN_TO_X9_00) {
            if ($tens > 0) {
                if ($result < ($tens * 10 + 9)) {
                    $result = $tens > 1 ? ($tens - 1) * 10 + 9 : 9;
                } else {
                    $result = $tens * 10 + 9;
                }
            }
        }

        return $result;
    }
}
