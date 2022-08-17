<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation;

/**
 * Class Result
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation
 */
class Result
{
    /**
     * @var float
     */
    private $amount = 0.0;

    /**
     * @var array
     */
    private $sumComponents = [];

    /**
     * @param float $amount
     * @param array $sumComponents
     */
    public function __construct(
        $amount,
        array $sumComponents
    ) {
        $this->amount = $amount;
        $this->sumComponents = $sumComponents;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Check if initial fee summed
     *
     * @return bool
     */
    public function isInitialFeeSummed()
    {
        return in_array('initial', $this->sumComponents);
    }

    /**
     * Check if trial price summed
     *
     * @return bool
     */
    public function isTrialPriceSummed()
    {
        return in_array('trial', $this->sumComponents);
    }

    /**
     * Check if regular price summed
     *
     * @return bool
     */
    public function isRegularPriceSummed()
    {
        return in_array('regular', $this->sumComponents);
    }
}
