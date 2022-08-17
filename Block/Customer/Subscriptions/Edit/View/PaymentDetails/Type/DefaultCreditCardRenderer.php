<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\AbstractCreditCardRenderer;

/**
 * Class BaseCreditCardRenderer
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
class DefaultCreditCardRenderer extends AbstractCreditCardRenderer
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Sarp2::customer/subscriptions/edit/view/payment_details/type/credit_card.phtml';
    
    /**
     * {@inheritdoc}
     */
    public function getCreditCardNumber($tokenDetails)
    {
        return isset($tokenDetails['lastCcNumber']) ? $tokenDetails['lastCcNumber'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate($tokenDetails)
    {
        return isset($tokenDetails['expirationDate']) ? $tokenDetails['expirationDate'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardType($tokenDetails)
    {
        return isset($tokenDetails['typeCode']) ? $tokenDetails['typeCode'] : '';
    }
}
