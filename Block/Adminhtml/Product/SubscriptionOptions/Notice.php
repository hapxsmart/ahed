<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions;

/**
 * Class Notice
 * @package Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions
 */
class Notice extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_nameInLayout = 'aw_sarp2_subscription_options_notice';

    /**
     * Get subscription plans grid url
     *
     * @return string
     */
    public function getPlansUrl()
    {
        return $this->_urlBuilder->getUrl('aw_sarp2/plan/index');
    }
}
