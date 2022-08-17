<?php
namespace Aheadworks\Sarp2\Block\Product\SubscriptionOptions;

use Aheadworks\Sarp2\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SubscribeAndSaveTooltip
 *
 * @package Aheadworks\Sarp2\Block\Product\SubscriptionOptions
 */
class SubscribeAndSaveTooltip extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'product/subscription_options/subscribe_and_save_tooltip.phtml';

    /**
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Check if show text
     *
     * @return bool
     */
    public function isShow()
    {
        return $this->getSubscriptionAndSaveText() != '';
    }

    /**
     * Check if show tooltip
     *
     * @return bool
     */
    public function isShowTooltip()
    {
        return $this->getSubscriptionAndSaveTooltip() != '';
    }

    /**
     * Retrieve subscription and save text
     *
     * @return string
     */
    public function getSubscriptionAndSaveText()
    {
        return $this->config->getSubscribeAndSaveText($this->getStoreId());
    }

    /**
     * Retrieve tooltip text
     *
     * @return string
     */
    public function getSubscriptionAndSaveTooltip()
    {
        return $this->config->getSubscribeAndSaveTooltip($this->getStoreId());
    }

    /**
     * Retrieve store id
     * @return int
     */
    private function getStoreId()
    {
        try {
            return $this->_storeManager->getStore()->getId();
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if ($this->isShow()) {
            return parent::toHtml();
        }
        return '';
    }
}
