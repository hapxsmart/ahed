<?php
namespace Aheadworks\Sarp2\Model\Integration;

use Aheadworks\Sarp2\Model\Integration\ModuleAvailability\CheckerInterface;
use Aheadworks\Sarp2\Model\Payment\Method\Data\DataAssignerInterface;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProcessor\ConfigProcessorInterface;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class IntegratedMethod
 *
 * @package Aheadworks\Sarp2\Model\Integration
 */
class IntegratedMethod implements IntegratedMethodInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $recurringCode;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var bool
     */
    private $needCopyMethodRendererFromCheckoutLayout;

    /**
     * @var string|null
     */
    private $checkoutPaymentMethodRendererComponentName;

    /**
     * @var ConfigProviderInterface
     */
    private $configProvider;

    /**
     * @var ConfigProcessorInterface
     */
    private $configProcessor;

    /**
     * @var CheckerInterface
     */
    private $moduleAvailabilityChecker;

    /**
     * @var DataAssignerInterface
     */
    private $paymentDataAssigner;

    /**
     * @var bool
     */
    private $isOffline;

    /**
     * @param string $code
     * @param string $recurringCode
     * @param string $paymentModuleName
     * @param CheckerInterface $moduleAvailabilityChecker
     * @param DataAssignerInterface $paymentDataAssigner
     * @param ConfigProviderInterface|null $configProvider
     * @param ConfigProcessorInterface|null $configProcessor
     * @param bool $needCopyMethodRendererFromCheckoutLayout
     * @param null $checkoutPaymentMethodRendererComponentName
     * @param bool $isOffline
     */
    public function __construct(
        string $code,
        string $recurringCode,
        string $paymentModuleName,
        CheckerInterface $moduleAvailabilityChecker,
        DataAssignerInterface $paymentDataAssigner,
        ConfigProviderInterface $configProvider = null,
        ConfigProcessorInterface $configProcessor = null,
        bool $needCopyMethodRendererFromCheckoutLayout = true,
        $checkoutPaymentMethodRendererComponentName = null,
        $isOffline = false
    ) {
        $this->code = $code;
        $this->recurringCode = $recurringCode;
        $this->moduleName = $paymentModuleName;
        $this->configProvider = $configProvider;
        $this->configProcessor = $configProcessor;
        $this->moduleAvailabilityChecker = $moduleAvailabilityChecker;
        $this->paymentDataAssigner = $paymentDataAssigner;
        $this->needCopyMethodRendererFromCheckoutLayout = $needCopyMethodRendererFromCheckoutLayout;
        $this->checkoutPaymentMethodRendererComponentName = $checkoutPaymentMethodRendererComponentName;
        $this->isOffline = $isOffline;
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getRecurringCode()
    {
        return $this->recurringCode;
    }

    /**
     * @inheritDoc
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @inheritDoc
     */
    public function needCopyMethodRendererFromCheckoutLayout(): bool
    {
        return $this->needCopyMethodRendererFromCheckoutLayout;
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutPaymentMethodRendererComponentName()
    {
        return null == $this->checkoutPaymentMethodRendererComponentName
            ? $this->getCode()
            : $this->checkoutPaymentMethodRendererComponentName;
    }

    /**
     * @inheritDoc
     */
    public function getProcessedConfig()
    {
        if (null == $this->configProvider) {
            return [];
        }
        $config = $this->configProvider->getConfig();
        if (null !== $this->configProcessor) {
            $config = $this->configProcessor->process($config);
        }
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    /**
     * @inheritDoc
     */
    public function getConfigProcessor()
    {
        return $this->configProcessor;
    }

    /**
     * @inheritDoc
     */
    public function isEnablePaymentModule(): bool
    {
        return $this->moduleAvailabilityChecker->check($this);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentDataAssigner()
    {
        return $this->paymentDataAssigner;
    }

    /**
     * @inheritDoc
     */
    public function isOffline()
    {
        return $this->isOffline;
    }

}
