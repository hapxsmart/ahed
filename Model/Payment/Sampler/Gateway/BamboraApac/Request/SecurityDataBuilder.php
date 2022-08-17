<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class SecurityDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request
 */
class SecurityDataBuilder implements BuilderInterface
{
    /**#@+
     * Security block names
     */
    const SECURITY = 'Security';
    const USER_NAME = 'UserName';
    const PASSWORD = 'Password';
    /**#@-*/

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

        $isSandbox = $this->config->isSandboxMode($storeId);
        $result = [
            self::SECURITY => [
                self::USER_NAME => $isSandbox ? $this->config->getSandboxUserName() : $this->config->getUserName(),
                self::PASSWORD => $isSandbox ? $this->config->getSandboxPassword() : $this->config->getPassword()
            ]
        ];

        return $result;
    }
}
