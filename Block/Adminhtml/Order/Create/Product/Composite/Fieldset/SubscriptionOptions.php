<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Order\Create\Product\Composite\Fieldset;

use Aheadworks\Sarp2\Model\Product\Subscription\Option\Source\Frontend as SubscriptionOptionSource;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

/**
 * Class SubscriptionOptions
 *
 * @package Aheadworks\Sarp2\Block\Adminhtml\Product\Composite\Fieldset
 */
class SubscriptionOptions extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var SubscriptionOptionSource
     */
    private $optionSource;

    /**
     * @var array
     */
    private $optionsArray;

    /**
     * SubscriptionOptions constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param SubscriptionOptionSource $optionSource
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        SubscriptionOptionSource $optionSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $context->getRegistry();
        $this->optionSource = $optionSource;
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->coreRegistry->registry('product'));
        }
        $product = $this->getData('product');
        if ($product->getTypeInstance()->getStoreFilter($product) === null) {
            $product->getTypeInstance()->setStoreFilter(
                $this->_storeManager->getStore($product->getStoreId()),
                $product
            );
        }

        return $product;
    }

    /**
     * Get subscription option array
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubscriptionOptions()
    {
        if ($this->optionsArray === null) {
            $productId = $this->getProduct()->getId();
            $this->optionsArray = $this->optionSource->getOptionArray($productId);
        }

        return $this->optionsArray;
    }

    /**
     * Get selected subscription plan
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSelectedSubscriptionOption()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('aw_sarp2_subscription_type');
    }
}
