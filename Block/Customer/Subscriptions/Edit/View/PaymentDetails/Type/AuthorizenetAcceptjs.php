<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

/**
 * Class AuthorizenetAcceptjs
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
class AuthorizenetAcceptjs extends DefaultCreditCardRenderer
{
    /**
     * {@inheritdoc}
     */
    public function getCreditCardNumber($tokenDetails)
    {
        return isset($tokenDetails['ccLast4']) ? $tokenDetails['ccLast4'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate($tokenDetails)
    {
        return 'XXXX';
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardType($tokenDetails)
    {
        return isset($tokenDetails['accountType']) ? $tokenDetails['accountType'] : '';
    }
}
