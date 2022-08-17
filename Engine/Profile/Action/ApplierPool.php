<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action;

use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Applier as ChangeStatus;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeAddress\Applier as ChangeAddress;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan\Applier as ChangePlan;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeNextPaymentDate\Applier as ChangeNextPaymentDate;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePaymentInformation\Applier as ChangePaymentInformation;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\RemoveProduct\Applier as RemoveItem;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\AddItemsFromQuoteToNearest\Applier as AddItemToNearest;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Applier as Extend;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem\Applier as ChangeProductItem;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\SetPaymentToken\Applier as SetPaymentToken;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;

/**
 * Class ApplierPool
 * @package Aheadworks\Sarp2\Engine\Profile\Action
 */
class ApplierPool
{
    /**
     * @var ApplierInterface[]
     */
    private $applierInstances = [];

    /**
     * @var array
     */
    private $appliers = [
        ActionInterface::ACTION_TYPE_CHANGE_STATUS => ChangeStatus::class,
        ActionInterface::ACTION_TYPE_CHANGE_ADDRESS => ChangeAddress::class,
        ActionInterface::ACTION_TYPE_CHANGE_PLAN => ChangePlan::class,
        ActionInterface::ACTION_TYPE_CHANGE_NEXT_PAYMENT_DATE => ChangeNextPaymentDate::class,
        ActionInterface::ACTION_TYPE_CHANGE_PAYMENT_INFORMATION => ChangePaymentInformation::class,
        ActionInterface::ACTION_TYPE_REMOVE_ITEM => RemoveItem::class,
        ActionInterface::ACTION_TYPE_ADD_ITEMS_FROM_QUOTE_TO_NEAREST_PROFILE => AddItemToNearest::class,
        ActionInterface::ACTION_TYPE_EXTEND => Extend::class,
        ActionInterface::ACTION_TYPE_CHANGE_PRODUCT_ITEM => ChangeProductItem::class,
        ActionInterface::ACTION_TYPE_SET_PAYMENT_TOKEN => SetPaymentToken::class
    ];

    /**
     * @var ApplierFactory
     */
    private $applierFactory;

    /**
     * @param ApplierFactory $applierFactory
     * @param array $appliers
     */
    public function __construct(
        ApplierFactory $applierFactory,
        array $appliers = []
    ) {
        $this->applierFactory = $applierFactory;
        $this->appliers = array_merge($this->appliers, $appliers);
    }

    /**
     * Get action applier instance
     *
     * @param string $actionType
     * @return ApplierInterface
     * @throws \Exception
     */
    public function getApplier($actionType)
    {
        if (!isset($this->applierInstances[$actionType])) {
            if (!isset($this->appliers[$actionType])) {
                throw new \InvalidArgumentException(
                    sprintf('Unknown action applier: %s requested', $actionType)
                );
            }
            $this->applierInstances[$actionType] = $this->applierFactory->create($this->appliers[$actionType]);
        }
        return $this->applierInstances[$actionType];
    }
}
