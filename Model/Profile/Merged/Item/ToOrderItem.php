<?php
namespace Aheadworks\Sarp2\Model\Profile\Merged\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Model\Order\Item;

/**
 * Class ToOrderItem
 * @package Aheadworks\Sarp2\Model\Profile\Merged\Item
 */
class ToOrderItem
{
    /**
     * @var OrderItemInterfaceFactory
     */
    private $orderItemFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var array
     */
    private $selfCopyMap = [
        [OrderItemInterface::PRICE, OrderItemInterface::ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE, OrderItemInterface::BASE_ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE, OrderItemInterface::BASE_COST]
    ];

    /**
     * @param OrderItemInterfaceFactory $orderItemFactory
     * @param Copy $objectCopyService
     * @param CopySelf $selfCopyService
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        OrderItemInterfaceFactory $orderItemFactory,
        Copy $objectCopyService,
        CopySelf $selfCopyService,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->objectCopyService = $objectCopyService;
        $this->selfCopyService = $selfCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Convert profile item to order item
     *
     * @param ProfileItemInterface $profileItem
     * @param string $paymentPeriod
     * @param array $data
     * @return OrderItemInterface
     */
    public function convert(ProfileItemInterface $profileItem, $paymentPeriod, $data = [])
    {
        $profileItemClone = clone $profileItem;
        $options = $profileItemClone->getProductOptions();

        $this->dataObjectHelper->populateWithArray(
            $profileItemClone,
            $this->dataObjectProcessor->buildOutputDataArray($profileItemClone, ProfileItemInterface::class),
            ProfileItemInterface::class
        );
        $orderItemData = $this->objectCopyService->getDataFromFieldset(
            'aw_sarp2_convert_profile_item',
            'to_order_item',
            $profileItemClone
        );
        $orderItemData = array_merge(
            $orderItemData,
            $this->objectCopyService->getDataFromFieldset(
                'aw_sarp2_convert_profile_item',
                'to_order_item_' . $paymentPeriod,
                $profileItemClone
            )
        );

        $orderItemData = $this->selfCopyService->copyByMap($orderItemData, $this->selfCopyMap);
        if (!empty($data)) {
            $orderItemData = array_merge($orderItemData, $data);
        }

        /** @var OrderItemInterface|Item $orderItem */
        $orderItem = $this->orderItemFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderItem,
            $orderItemData,
            OrderItemInterface::class
        );
        $orderItem
            ->setProductOptions($options)
            ->setDiscountAmount(0.0);

        return $orderItem;
    }
}
