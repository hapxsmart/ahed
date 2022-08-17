<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\OneStepCheckout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class NewsletterConfigProvider
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\OneStepCheckout
 */
class NewsletterConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Return configuration array
     *
     * @return array
     */
    public function getConfig()
    {
        $output['newsletterSubscribe'] = $this->getOneStepCheckoutNewsletterConfigProvider()->getConfig();
        return $output;
    }

    /**
     * Get OneStepCheckout Newsletter config provider
     *
     * @return ConfigProviderInterface
     */
    private function getOneStepCheckoutNewsletterConfigProvider()
    {
        return $this->objectManager->get(\Aheadworks\OneStepCheckout\Model\Newsletter\ConfigProvider::class);
    }
}
