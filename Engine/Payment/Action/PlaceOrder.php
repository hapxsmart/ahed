<?php
namespace Aheadworks\Sarp2\Engine\Payment\Action;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\StrategyResolver;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\Order\InventoryManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class PlaceOrder
 * @package Aheadworks\Sarp2\Engine\Payment\Action
 */
class PlaceOrder
{
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var InventoryManagement
     */
    private $inventoryManagement;

    /**
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var StrategyResolver
     */
    private $strategyResolver;

    /**
     * @var ProfileInterface[]
     */
    private $processed = [];

    /**
     * @param OrderManagementInterface $orderManagement
     * @param InventoryManagement $inventoryManagement
     * @param DataResolver $dataResolver
     * @param StrategyResolver $strategyResolver
     */
    public function __construct(
        OrderManagementInterface $orderManagement,
        InventoryManagement $inventoryManagement,
        DataResolver $dataResolver,
        StrategyResolver $strategyResolver
    ) {
        $this->orderManagement = $orderManagement;
        $this->inventoryManagement = $inventoryManagement;
        $this->dataResolver = $dataResolver;
        $this->strategyResolver = $strategyResolver;
    }

    /**
     * Place order
     *
     * @param OrderInterface $order
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return OrderInterface
     * @throws \Exception
     */
    public function place($order, $paymentsInfo)
    {
        $this->processed = [];
        try {
            array_walk($paymentsInfo, [$this, 'subtractStockQty']);
            $this->orderManagement->place($order);
        } catch (\Exception $e) {
            array_walk($this->processed, [$this, 'revertStockQty']);

            $paymentMethod = $this->dataResolver->getPaymentMethod($paymentsInfo);
            $strategy = $this->strategyResolver->getStrategy($paymentMethod);
            $strategy->apply($e);
        }

        return $order;
    }

    /**
     * Subtract profile items quantities from stock
     *
     * @param PaymentInfoInterface $paymentInfo
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function subtractStockQty($paymentInfo)
    {
        $profile = $paymentInfo->getProfile();
        $this->inventoryManagement->subtract($profile);
        $this->processed[] = $profile;
    }

    /**
     * Revert profile items quantities
     *
     * @param ProfileInterface $profile
     * @return void
     */
    private function revertStockQty($profile)
    {
        $this->inventoryManagement->revert($profile);
    }
}
