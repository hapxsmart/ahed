<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;

/**
 * Class OrderDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request
 */
class OrderDataBuilder implements BuilderInterface
{
    /**
     * Order variable name
     */
    const IP_ADDRESS = 'ipaddress';
    const ORDER_ID = 'orderid';
    const PO_NUMBER = 'ponumber';

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

        return [
            self::IP_ADDRESS => $profile->getRemoteIp(),
            self::ORDER_ID => $profile->getLastOrderId(),
            self::PO_NUMBER => $profile->getLastOrderId(),
        ];
    }
}
