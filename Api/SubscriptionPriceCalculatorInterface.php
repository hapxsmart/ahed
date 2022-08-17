<?php
namespace Aheadworks\Sarp2\Api;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input as CalculationInput;

/**
 * Interface SubscriptionPriceCalculationInterface
 *
 * @package Aheadworks\Sarp2\Api
 */
interface SubscriptionPriceCalculatorInterface
{
    /**
     * Get calculated trial product price for specified plan
     *
     * @param CalculationInput $input
     * @param SubscriptionOptionInterface $option
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTrialPrice($input, $option);

    /**
     * Get calculated regular product price for specified plan
     *
     * @param CalculationInput $input
     * @param SubscriptionOptionInterface $option
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegularPrice($input, $option);

    /**
     * Get calculated first payment product price for specified plan
     *
     * @param CalculationInput $input
     * @param SubscriptionOptionInterface $option
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFirstPaymentPrice($input, $option);
}
