<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Address\ToOrderAddress as AddressToOrderAddress;
use Aheadworks\Sarp2\Model\Profile\Merged\Address\ToOrder as AddressToOrder;
use Aheadworks\Sarp2\Model\Profile\Merged\Item\ToOrderItem as ItemToOrderItem;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Profile\Item\Merger;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Aheadworks\Sarp2\Model\Sales\Item\Checker\IsVirtual;
use Aheadworks\Sarp2\Model\Sales\Order\IncrementIdProvider;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\CollectorList;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Subject;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\SubjectFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class ToMergedOrder
 * @package Aheadworks\Sarp2\Model\Profile
 */
class ToMergedOrder
{
    /**
     * @var AddressToOrderAddress
     */
    private $profileAddressToOrderAddress;

    /**
     * @var AddressToOrder
     */
    private $profileAddressToOrder;

    /**
     * @var ItemToOrderItem
     */
    private $profileItemToOrderItem;

    /**
     * @var ToOrderPayment
     */
    private $profileToOrderPayment;

    /**
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var Merger
     */
    private $itemsMerger;

    /**
     * @var CollectorList
     */
    private $collectorList;

    /**
     * @var SubjectFactory
     */
    private $collectorSubjectFactory;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @var IsVirtual
     */
    private $isVirtualChecker;

    /**
     * @var IncrementIdProvider
     */
    private $incrementIdProvider;

    /**
     * @param AddressToOrderAddress $profileAddressToOrderAddress
     * @param AddressToOrder $profileAddressToOrder
     * @param ItemToOrderItem $profileItemToOrderItem
     * @param ToOrderPayment $profileToOrderPayment
     * @param DataResolver $dataResolver
     * @param Merger $itemsMerger
     * @param CollectorList $collectorList
     * @param SubjectFactory $collectorSubjectFactory
     * @param CopySelf $selfCopyService
     * @param IsVirtual $isVirtualChecker
     * @param IncrementIdProvider $incrementIdProvider
     */
    public function __construct(
        AddressToOrderAddress $profileAddressToOrderAddress,
        AddressToOrder $profileAddressToOrder,
        ItemToOrderItem $profileItemToOrderItem,
        ToOrderPayment $profileToOrderPayment,
        DataResolver $dataResolver,
        Merger $itemsMerger,
        CollectorList $collectorList,
        SubjectFactory $collectorSubjectFactory,
        CopySelf $selfCopyService,
        IsVirtual $isVirtualChecker,
        IncrementIdProvider $incrementIdProvider
    ) {
        $this->profileAddressToOrderAddress = $profileAddressToOrderAddress;
        $this->profileAddressToOrder = $profileAddressToOrder;
        $this->profileItemToOrderItem = $profileItemToOrderItem;
        $this->profileToOrderPayment = $profileToOrderPayment;
        $this->dataResolver = $dataResolver;
        $this->itemsMerger = $itemsMerger;
        $this->collectorList = $collectorList;
        $this->collectorSubjectFactory = $collectorSubjectFactory;
        $this->selfCopyService = $selfCopyService;
        $this->isVirtualChecker = $isVirtualChecker;
        $this->incrementIdProvider = $incrementIdProvider;
    }

    /**
     * Convert profiles to merged order
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return OrderInterface
     */
    public function convert($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();

        $profileBillingAddress = $firstProfile->getBillingAddress();
        $orderBillingAddress = $this->profileAddressToOrderAddress->convert($profileBillingAddress);
        $orderAddresses = [$orderBillingAddress];

        if ($this->dataResolver->isVirtual($paymentsInfo)) {
            $order = $this->profileAddressToOrder->convert($profileBillingAddress);
        } else {
            $profileShippingAddress = $firstProfile->getShippingAddress();
            $order = $this->profileAddressToOrder->convert($profileShippingAddress);
            $orderShippingAddress = $this->profileAddressToOrderAddress->convert($profileShippingAddress);
            $order->setShippingAddress($orderShippingAddress);
            $orderAddresses[] = $orderShippingAddress;
        }
        $order->setBillingAddress($orderBillingAddress);
        $order->setAddresses($orderAddresses);
        $order->setPayment($this->profileToOrderPayment->convert($firstProfile));

        /** @var OrderItem[] $orderItems */
        $orderItems = $this->convertItems($paymentsInfo, $order);
        $order->setItems($orderItems);
        $order->setIsVirtual($this->isVirtualChecker->check($orderItems));

        $storeId = $this->dataResolver->getStoreId($paymentsInfo);
        $order->setIncrementId($this->incrementIdProvider->getIncrementId($storeId));

        /** @var Order $order */
        $this->selfCopyService->copyByMap(
            $order,
            [
                [OrderInterface::ORDER_CURRENCY_CODE, OrderInterface::STORE_CURRENCY_CODE]
            ]
        );

        return $order;
    }

    /**
     * Convert profile items to merged order items
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @param OrderInterface $order
     * @return OrderItemInterface[]
     */
    private function convertItems($paymentsInfo, $order)
    {
        $pairs = [];
        $merged = $this->itemsMerger->mergeItems($paymentsInfo);
        foreach ($merged as $mergedItem) {
            $profileItem = $mergedItem->getItem();
            $itemId = $profileItem->getItemId();
            $paymentPeriod = $mergedItem->getPaymentPeriod();

            if (!isset($pairs[$itemId])) {
                $parentItemId = $profileItem->getParentItemId();
                if ($parentItemId && !isset($pairs[$parentItemId])) {
                    $pairs[$parentItemId] = [
                        $mergedItem,
                        $this->profileItemToOrderItem->convert(
                            $profileItem->getParentItem(),
                            $paymentPeriod,
                            ['parent_item' => null]
                        )
                    ];
                }
                $parentItem = isset($pairs[$parentItemId])
                    ? $pairs[$parentItemId][1]
                    : null;
                $pairs[$itemId] = [
                    $mergedItem,
                    $this->profileItemToOrderItem->convert(
                        $profileItem,
                        $paymentPeriod,
                        ['parent_item' => $parentItem]
                    )
                ];
            }
        }

        /** @var Subject $collectSubject */
        $collectSubject = $this->collectorSubjectFactory->create(
            [
                'paymentsInfo' => $paymentsInfo,
                'order' => $order,
                'itemPairs' => $pairs
            ]
        );
        foreach ($this->collectorList->getCollectors() as $totalCollector) {
            $totalCollector->collect($collectSubject);
        }

        /**
         * @param array $pair
         * @return OrderItemInterface
         */
        $closure = function ($pair) {
            return $pair[1];
        };
        return array_map($closure, $pairs);
    }
}
