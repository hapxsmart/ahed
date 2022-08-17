<?php
namespace Aheadworks\Sarp2\Block\Email\Order\Items;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\Resolver\TitleResolver;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Item;

/**
 * Class DefaultItems
 *
 * @method Item getItem()
 *
 * @package Aheadworks\Sarp2\Block\Email\Order\Items
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
     * @param Item $item
     * @return string
     */
    public function getPlanTitle($item)
    {
        $option = $item->getProductOptionByCode('aw_sarp2_subscription_plan');
        if ($option) {
            /** @var PlanInterface $plan */
            $plan = $this->planFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $plan,
                $option,
                PlanInterface::class
            );
            return $this->titleResolver->getTitle($plan, $item->getStoreId());
        }
        return '';
    }
}
