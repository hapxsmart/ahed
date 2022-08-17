<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\Address\ToOrder as AddressToOrder;
use Aheadworks\Sarp2\Model\Profile\Address\ToOrderAddress as AddressToOrderAddress;
use Aheadworks\Sarp2\Model\Profile\Exception\CouldNotConvertException;
use Aheadworks\Sarp2\Model\Profile\Item\ToOrderItem as ItemToOrderItem;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Aheadworks\Sarp2\Model\Sales\Item\Checker\IsVirtual;
use Aheadworks\Sarp2\Model\Sales\Order\IncrementIdProvider;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Recalculation\Order as OrderTotalRecalculation;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;

class ToOrder
{
    /**
     * @var AddressToOrder
     */
    private $profileAddressToOrder;

    /**
     * @var AddressToOrderAddress
     */
    private $profileAddressToOrderAddress;

    /**
     * @var ItemToOrderItem
     */
    private $profileItemToOrderItem;

    /**
     * @var ToOrderPayment
     */
    private $profileToOrderPayment;

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
     * @var Config
     */
    private $config;

    /**
     * @var OrderTotalRecalculation
     */
    private $orderTotalRecalculation;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param AddressToOrder $profileAddressToOrder
     * @param AddressToOrderAddress $profileAddressToOrderAddress
     * @param ItemToOrderItem $profileItemToOrderItem
     * @param ToOrderPayment $profileToOrderPayment
     * @param CopySelf $selfCopyService
     * @param IsVirtual $isVirtualChecker
     * @param IncrementIdProvider $incrementIdProvider
     * @param Config $config
     * @param OrderTotalRecalculation $orderTotalRecalculation
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        AddressToOrder $profileAddressToOrder,
        AddressToOrderAddress $profileAddressToOrderAddress,
        ItemToOrderItem $profileItemToOrderItem,
        ToOrderPayment $profileToOrderPayment,
        CopySelf $selfCopyService,
        IsVirtual $isVirtualChecker,
        IncrementIdProvider $incrementIdProvider,
        Config $config,
        OrderTotalRecalculation $orderTotalRecalculation,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->profileAddressToOrder = $profileAddressToOrder;
        $this->profileAddressToOrderAddress = $profileAddressToOrderAddress;
        $this->profileItemToOrderItem = $profileItemToOrderItem;
        $this->profileToOrderPayment = $profileToOrderPayment;
        $this->selfCopyService = $selfCopyService;
        $this->isVirtualChecker = $isVirtualChecker;
        $this->incrementIdProvider = $incrementIdProvider;
        $this->config = $config;
        $this->orderTotalRecalculation = $orderTotalRecalculation;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Convert profile to order
     *
     * @param ProfileInterface $profile
     * @param string $paymentPeriod
     * @param bool $generateIncrementId
     * @return OrderInterface
     * @throws CouldNotConvertException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function convert(ProfileInterface $profile, $paymentPeriod, $generateIncrementId = true)
    {
        if ($this->config->isRecalculationOfTotalsEnabled($profile->getStoreId())) {
            //Trigger profile totals recalculation
            $this->profileRepository->save($profile, true);
        }
        $profileBillingAddress = $profile->getBillingAddress();
        $orderBillingAddress = $this->profileAddressToOrderAddress->convert($profileBillingAddress);
        $orderAddresses = [$orderBillingAddress];

        if ($profile->getIsVirtual()) {
            $order = $this->profileAddressToOrder->convert($profileBillingAddress, $paymentPeriod);
        } else {
            $profileShippingAddress = $profile->getShippingAddress();
            $order = $this->profileAddressToOrder->convert($profileShippingAddress, $paymentPeriod);
            $orderShippingAddress = $this->profileAddressToOrderAddress->convert($profileShippingAddress);
            $order->setShippingAddress($orderShippingAddress);
            $orderAddresses[] = $orderShippingAddress;
        }
        $order->setBillingAddress($orderBillingAddress);
        $order->setAddresses($orderAddresses);
        $order->setPayment($this->profileToOrderPayment->convert($profile, $paymentPeriod));

        /** @var OrderItem[] $orderItems */
        $orderItems = $this->convertItems($profile, $paymentPeriod);
        $order->setItems($orderItems);

        if ($this->config->isRecalculationOfTotalsEnabled($profile->getStoreId())) {
            $this->orderTotalRecalculation->recalculateTotals($order);
        }

        $order->setIsVirtual($this->isVirtualChecker->check($orderItems));
        if ($generateIncrementId) {
            $order->setIncrementId(
                $this->incrementIdProvider->getIncrementId($profile->getStoreId())
            );
        }

        /** @var Order $order */
        $this->selfCopyService->copyByMap(
            $order,
            [
                [OrderInterface::ORDER_CURRENCY_CODE, OrderInterface::STORE_CURRENCY_CODE]
            ]
        );

        $this->validate($order);

        return $order;
    }

    /**
     * Convert profile items to order items
     *
     * @param ProfileInterface $profile
     * @param string $paymentPeriod
     * @return OrderItemInterface[]
     */
    private function convertItems(ProfileInterface $profile, $paymentPeriod)
    {
        $orderItems = [];
        foreach ($profile->getItems() as $profileItem) {
            $itemId = $profileItem->getItemId();
            if (!isset($orderItems[$itemId])) {
                $parentItemId = $profileItem->getParentItemId();
                if ($parentItemId && !isset($orderItems[$parentItemId])) {
                    $orderItems[$parentItemId] = $this->profileItemToOrderItem->convert(
                        $profileItem->getParentItem(),
                        $paymentPeriod,
                        ['parent_item' => null]
                    );
                }
                $parentItem = isset($orderItems[$parentItemId])
                    ? $orderItems[$parentItemId]
                    : null;
                $orderItems[$itemId] = $this->profileItemToOrderItem->convert(
                    $profileItem,
                    $paymentPeriod,
                    ['parent_item' => $parentItem]
                );
            }
        }
        return array_values($orderItems);
    }

    /**
     * Validate order entity
     *
     * @param OrderInterface|Order $order
     * @return void
     * @throws CouldNotConvertException
     */
    private function validate($order)
    {
        if (!$order->getIsVirtual() && !$order->getShippingMethod()) {
            throw new CouldNotConvertException('Unable to resolve shipping method.');
        }
    }
}
