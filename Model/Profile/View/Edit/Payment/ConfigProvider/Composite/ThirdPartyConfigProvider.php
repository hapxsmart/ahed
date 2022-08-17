<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\Composite;

use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Sarp2\Model\ThirdPartyModule\Manager;
use Magento\Checkout\Model\ConfigProviderInterface;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\OneStepCheckout\NewsletterConfigProvider;

/**
 * Class ThirdPartyConfigProvider
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\Composite
 */
class ThirdPartyConfigProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Manager
     */
    private $thirdPartyModuleManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $thirdPartyModuleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $thirdPartyModuleManager
    ) {
        $this->objectManager = $objectManager;
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
    }

    /**
     * @inheritdoc
     */
    public function getConfigProviders()
    {
        $providers = [];

        if ($this->thirdPartyModuleManager->isNmiModuleEnabled()) {
            $providers[] = $this->getNmiConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isBamboraApacModuleEnabled()) {
            $providers[] = $this->getBamboraConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isAuthorizenetAcceptJsModuleEnabled()) {
            $providers[] = $this->getAuthorizenetAcceptJsConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isAuthorizenetCardinalModuleEnabled()) {
            $providers[] = $this->getAuthorizenetCardinalConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isOneStepCheckoutModuleEnabled()) {
            $providers[] = $this->getOneStepCheckoutNewsletterConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isKlarnaModuleEnabled()) {
            $providers[] = $this->getKlarnaConfigProvider();
        }

        return $providers;
    }

    /**
     * Get Nmi config provider
     *
     * @return \Aheadworks\Nmi\Model\Ui\ConfigProvider
     */
    private function getNmiConfigProvider()
    {
        return $this->objectManager->get(\Aheadworks\Nmi\Model\Ui\ConfigProvider::class);
    }

    /**
     * Get Bambora config provider
     *
     * @return \Aheadworks\BamboraApac\Model\Ui\ConfigProvider
     */
    private function getBamboraConfigProvider()
    {
        return $this->objectManager->get(\Aheadworks\BamboraApac\Model\Ui\ConfigProvider::class);
    }

    /**
     * Get Authorizenet AcceptJs config provider
     *
     * @return ConfigProviderInterface
     */
    private function getAuthorizenetAcceptJsConfigProvider()
    {
        return $this->objectManager->get(\Magento\AuthorizenetAcceptjs\Model\Ui\ConfigProvider::class);
    }

    /**
     * Get Authorizenet Cardinal config provider
     *
     * @return ConfigProviderInterface
     */
    private function getAuthorizenetCardinalConfigProvider()
    {
        return $this->objectManager->get(\Magento\AuthorizenetCardinal\Model\Checkout\ConfigProvider::class);
    }

    /**
     * Get OneStepCheckout Newsletter config provider
     *
     * @return ConfigProviderInterface
     */
    private function getOneStepCheckoutNewsletterConfigProvider()
    {
        return $this->objectManager->get(NewsletterConfigProvider::class);
    }

    /**
     * Get Klarna config provider
     *
     * @return ConfigProviderInterface
     */
    private function getKlarnaConfigProvider()
    {
        return $this->objectManager->get(\Klarna\Kp\Model\KpConfigProvider::class);
    }
}
