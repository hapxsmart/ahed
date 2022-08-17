<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails;

/**
 * Class AbstractCreditCardRenderer
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
abstract class AbstractCreditCardRenderer extends AbstractTokenWithIconRenderer
{
    /**
     * Retrieve truncated credit card number
     *
     * @param array $tokenDetails
     * @return string
     */
    abstract public function getCreditCardNumber($tokenDetails);

    /**
     * Retrieve credit card expiration date
     *
     * @param array $tokenDetails
     * @return string
     */
    abstract public function getExpirationDate($tokenDetails);

    /**
     * Retrieve credit card type
     *
     * @param array $tokenDetails
     * @return string
     */
    abstract public function getCreditCardType($tokenDetails);
}
