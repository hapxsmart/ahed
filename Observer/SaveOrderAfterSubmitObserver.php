<?php
namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Payment\Checker\OfflinePayment;
use Aheadworks\Sarp2\Model\Quote\Management;
use Aheadworks\Sarp2\Model\Sales\Order\Item\SubscriptionOptionExtractor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order;

class SaveOrderAfterSubmitObserver implements ObserverInterface
{
    /**
     * @var Management
     */
    private $quoteManagement;

    /**
     * @var OfflinePayment
     */
    private $offlinePaymentChecker;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var SubscriptionOptionExtractor
     */
    private $subscriptionOptionExtractor;

    /**
     * @param Management $quoteManagement
     * @param OfflinePayment $offlinePaymentChecker
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param SubscriptionOptionExtractor $subscriptionOptionExtractor
     */
    public function __construct(
        Management $quoteManagement,
        OfflinePayment $offlinePaymentChecker,
        OrderItemRepositoryInterface $orderItemRepository,
        SubscriptionOptionExtractor $subscriptionOptionExtractor
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->offlinePaymentChecker = $offlinePaymentChecker;
        $this->orderItemRepository = $orderItemRepository;
        $this->subscriptionOptionExtractor = $subscriptionOptionExtractor;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /* @var $order Order */
        $order = $event->getData('order');
        /** @var Quote $quote */
        $quote = $event->getData('quote');

        $this->offlinePaymentChecker->check($quote->getPayment()->getMethod())
            ? $this->quoteManagement->createProfilesUsingPaymentMethod($quote, $quote->getPayment(), $order)
            : $this->quoteManagement->createProfiles($quote, $order);
        $this->addOptionsToOrderItems($order->getItems());
    }

    /**
     * Add options to order items
     *
     * @param OrderItemInterface[] $orderItems
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addOptionsToOrderItems(array $orderItems)
    {
        foreach ($orderItems as $orderItem) {
            $options = $orderItem->getData('product_options');
            if (isset($options['aw_sarp2_subscription_option'])) {
                $subscriptionOption = &$options['aw_sarp2_subscription_option'];
                $subscriptionOption = array_merge(
                    $subscriptionOption,
                    $this->subscriptionOptionExtractor->extract($orderItem, $options)
                );
                $orderItem->setProductOptions($options);
                $this->orderItemRepository->save($orderItem);
            }
        }
    }
}
