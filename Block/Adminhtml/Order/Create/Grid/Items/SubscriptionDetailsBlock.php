<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Order\Create\Grid\Items;

/**
 * Class SubscriptionDetailsBlock
 *
 * @package Aheadworks\Sarp2\Block\Adminhtml\Order\Create\Grid\Items
 */
class SubscriptionDetailsBlock extends \Magento\Backend\Block\Widget
{
    const DETAILS = 'subscription_details';

    /**
     * @var
     */
    protected $_template = 'Aheadworks_Sarp2::order/create/grid/items/subscription_details.phtml';

    /**
     * @var array
     */
    private $subscriptionDetails;

    /**
     * Set subscription details
     *
     * @param array $details
     * @return $this
     */
    public function setSubscriptionDetails(array $details)
    {
        $this->subscriptionDetails = $details;

        return $this;
    }

    /**
     * Retrieve subscription details if isset
     *
     * @return array
     */
    public function getSubscriptionDetails()
    {
        return $this->subscriptionDetails ?? [];
    }
}
