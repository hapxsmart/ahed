<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class TransactionDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request
 */
class TransactionDataBuilder implements BuilderInterface
{
    /**#@+
     * Transaction block names
     */
    const ORDER_ID = 'CustRef';
    const ACCOUNT_NUMBER = 'AccountNumber';
    /**#@-*/

    /**
     * Key for retrieving account number from payment method config
     */
    const CONFIG_KEY_ACCOUNT_NUMBER = 'account_number';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param SubjectReader $subjectReader
     * @param ConfigInterface $config
     */
    public function __construct(
        SubjectReader $subjectReader,
        ConfigInterface $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $storeId = $payment->getStoreId();

        return [
            self::ORDER_ID => $payment->getId(),
            self::ACCOUNT_NUMBER => $this->config->getValue(self::CONFIG_KEY_ACCOUNT_NUMBER, $storeId)
        ];
    }
}
