<?php
namespace Aheadworks\Sarp2\Model\Integration;

use Aheadworks\Sarp2\Model\Payment\Method\Data\DataAssignerInterface;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProcessor\ConfigProcessorInterface;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Interface IntegratedMethodInterface
 *
 * @package Aheadworks\Sarp2\Model\Integration
 */
interface IntegratedMethodInterface
{
    /**
     * Retrieve payment method code
     *
     * @return string
     */
    public function getCode();

    /**
     * Retrieve recurring payment method code
     *
     * @return string
     */
    public function getRecurringCode();

    /**
     * Retrieve payment method module name
     *
     * @return string
     */
    public function getModuleName();

    /**
     * Check if need copy method renderer from checkout to change profile payment layout
     *
     * @return bool
     */
    public function needCopyMethodRendererFromCheckoutLayout(): bool;

    /**
     * Retrieve checkout payment method renderer component name for copy to sampler renderer list
     * If null, then it will be taken from the paymentCode
     *
     * @return string|null
     */
    public function getCheckoutPaymentMethodRendererComponentName();

    /**
     * Retrieve method processed config
     *
     * @return array
     */
    public function getProcessedConfig();

    /**
     * Retrieve method config provider
     *
     * @return ConfigProviderInterface
     */
    public function getConfigProvider();

    /**
     * Retrieve method config processor
     *
     * @return ConfigProcessorInterface|null
     */
    public function getConfigProcessor();

    /**
     * Check if integrable payment method module available and enable
     *
     * @return bool
     */
    public function isEnablePaymentModule(): bool;

    /**
     * Retrieve payment additional data assigner
     *
     * @return DataAssignerInterface
     */
    public function getPaymentDataAssigner();

    /**
     * Check if payment is offline
     *
     * @return bool
     */
    public function isOffline();
}
