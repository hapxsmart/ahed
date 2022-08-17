<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\LayoutProcessor;

use Aheadworks\Sarp2\Model\Integration\IntegratedMethodList;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\DefinitionFetcher;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

/**
 * Class PaymentMethodRendererProcessor
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\LayoutProcessor
 */
class PaymentMethodRendererProcessor implements LayoutProcessorInterface
{
    /**
     * @var DefinitionFetcher
     */
    private $definitionFetcher;

    /**
     * @var IntegratedMethodList
     */
    private $integratedMethodList;

    /**
     * @param DefinitionFetcher $definitionFetcher
     * @param IntegratedMethodList $integratedMethodList
     */
    public function __construct(
        DefinitionFetcher $definitionFetcher,
        IntegratedMethodList $integratedMethodList
    ) {
        $this->definitionFetcher = $definitionFetcher;
        $this->integratedMethodList = $integratedMethodList;
    }

    /**
     * @inheritdoc
     */
    public function process($jsLayout)
    {
        if (isset($jsLayout['components']['payment']['children']['renders']['children'])
        ) {
            //todo: begin legacy mechanism, need replace to IntegratedMethodPool
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'aw_bambora_apac'
            );
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'aw_nmi'
            );
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'authorizenet_acceptjs'
            );
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'offline-payments'
            );
            //Vault is disabled for now
            /*$this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'vault'
            );*/
            //todo: end legacy mechanism

            foreach ($this->integratedMethodList->getList() as $integratedMethod) {
                if ($integratedMethod->needCopyMethodRendererFromCheckoutLayout()) {
                    $this->addPaymentMethodRender(
                        $jsLayout['components']['payment']['children']['renders']['children'],
                        $integratedMethod->getCheckoutPaymentMethodRendererComponentName()
                    );
                }
            }

        }
        return $jsLayout;
    }

    /**
     * Add payment methods renders definitions
     *
     * @param array $layout
     * @param string $paymentMethod
     * @return void
     */
    private function addPaymentMethodRender(array &$layout, $paymentMethod)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="steps"]/item[@name="children"]/item[@name="billing-step"]'
            . '/item[@name="children"]/item[@name="payment"]/item[@name="children"]'
            . '/item[@name="renders"]/item[@name="children"]/item[@name="' . $paymentMethod . '"]';
        $definitions = $this->definitionFetcher->fetchArgs('checkout_index_index', $path);
        if (!empty($definitions)) {
            $paymentDefinition = [$paymentMethod => $definitions];
            $layout = array_merge($layout, $paymentDefinition);
        }
    }
}
