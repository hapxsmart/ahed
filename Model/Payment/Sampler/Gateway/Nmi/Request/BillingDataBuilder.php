<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;

/**
 * Class BillingDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request
 */
class BillingDataBuilder implements BuilderInterface
{
    /**
     * Billing variable name
     */
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const COMPANY = 'company';
    const ADDRESS1 = 'address1';
    const ADDRESS2 = 'address2';
    const CITY = 'city';
    const STATE = 'state';
    const ZIP = 'zip';
    const COUNTRY = 'country';
    const PHONE = 'phone';
    const EMAIL = 'email';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $profile = $paymentDO->getProfile();
        $billingAddress = $profile->getBillingAddress();

        return [
            self::FIRSTNAME => $billingAddress->getFirstname(),
            self::LASTNAME => $billingAddress->getLastname(),
            self::COMPANY => $billingAddress->getCompany(),
            self::ADDRESS1 => $billingAddress->getStreetLine1(),
            self::ADDRESS2 => $billingAddress->getStreetLine2(),
            self::CITY => $billingAddress->getCity(),
            self::STATE => $billingAddress->getRegionCode(),
            self::ZIP => $billingAddress->getPostcode(),
            self::COUNTRY => $billingAddress->getCountryId(),
            self::PHONE => $billingAddress->getTelephone(),
            self::EMAIL => $billingAddress->getEmail()
        ];
    }
}
