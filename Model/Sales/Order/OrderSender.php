<?php
namespace Aheadworks\Sarp2\Model\Sales\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender as SalesOrderSender;
use Psr\Log\LoggerInterface;

/**
 * Class OrderSender
 * @package Aheadworks\Sarp2\Model\Sales\Order
 */
class OrderSender
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SalesOrderSender
     */
    private $orderSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SalesOrderSender $orderSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SalesOrderSender $orderSender,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
    }

    /**
     * Sends order email to the customer
     *
     * @param int $orderId
     */
    public function send($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $this->orderSender->send($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
