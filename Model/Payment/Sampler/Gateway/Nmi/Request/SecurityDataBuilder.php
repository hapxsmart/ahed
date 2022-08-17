<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Aheadworks\Nmi\Model\Config;

/**
 * Class SecurityDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request
 */
class SecurityDataBuilder implements BuilderInterface
{
    /**#@+
     * Security block names
     */
    const USER_NAME = 'username';
    const PASSWORD = 'password';
    /**#@-*/

    /**
     * @var \Aheadworks\Nmi\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Aheadworks\Nmi\Gateway\Config
     */
    private $config_new;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * SecurityDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param ConfigInterface $config
     */
    public function __construct(SubjectReader $subjectReader, ConfigInterface $config, Config $config_new)
    {
        $this->subjectReader = $subjectReader;
        $this->config = $config_new;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $profile = $paymentDO->getProfile();

        $isSandbox = $this->config->isSandboxMode($profile->getStoreId());
        $result = [
            self::USER_NAME => $isSandbox ? $this->config->getSandboxUserName() : $this->config->getUserName(),
            self::PASSWORD => $isSandbox ? $this->config->getSandboxPassword() : $this->config->getPassword()
        ];

        return $result;
    }
}
