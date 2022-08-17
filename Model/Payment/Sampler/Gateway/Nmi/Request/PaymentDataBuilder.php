<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class PaymentDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request
 */
class PaymentDataBuilder implements BuilderInterface
{
    /**
     * Amount block name
     */
    const AMOUNT = 'amount';

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

        $payment = $paymentDO->getPayment();
        $amount = $payment->getAmount();

        $result = [
            self::AMOUNT => (float)number_format($amount, 2, '.', ''),
        ];

        return $result;
    }
}
