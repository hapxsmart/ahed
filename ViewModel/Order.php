<?php

declare(strict_types=1);

namespace Aheadworks\Sarp2\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Order view model
 */
class Order implements ArgumentInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get order
     *
     * @param int $orderId
     * @return OrderInterface
     */
    public function getOrder(int $orderId) : OrderInterface
    {
        return $this->orderRepository->get($orderId);
    }
}
