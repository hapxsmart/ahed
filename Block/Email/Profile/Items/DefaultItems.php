<?php
namespace Aheadworks\Sarp2\Block\Email\Profile\Items;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\Resolver\TitleResolver;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class DefaultItems
 *
 * @method ProfileItemInterface getItem()
 *
 * @package Aheadworks\Sarp2\Block\Email\Profile\Items
 */
class DefaultItems extends Template
{
    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var TitleResolver
     */
    private $titleResolver;

    /**
     * @param Context $context
     * @param PlanInterfaceFactory $planFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param TitleResolver $titleResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        PlanInterfaceFactory $planFactory,
        DataObjectHelper $dataObjectHelper,
        TitleResolver $titleResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->planFactory = $planFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->titleResolver = $titleResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if (!$this->getItem()->getParentItemId()) {
            return parent::toHtml();
        }
        return '';
    }

    /**
     * Get subscription plan title
     *
     * @param ProfileItemInterface $item
     * @return string
     */
    public function getPlanTitle($item)
    {
        $options = $item->getProductOptions();
        if (isset($options['aw_sarp2_subscription_plan'])) {
            /** @var PlanInterface $plan */
            $plan = $this->planFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $plan,
                $options['aw_sarp2_subscription_plan'],
                PlanInterface::class
            );
            return $this->titleResolver->getTitle($plan, $item->getStoreId());
        }
        return '';
    }
}
