<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Recalculation;

use Magento\Sales\Api\Data\OrderInterface;

class Order
{
    /**
     * Recalculate totals
     *
     * @param OrderInterface $order
     */
    public function recalculateTotals($order)
    {
        $order
            ->setBaseSubtotal(0)
            ->setSubtotal(0)
            ->setBaseSubtotalInclTax(0)
            ->setSubtotalInclTax(0)
            ->setDiscountAmount(0)
            ->setBaseDiscountAmount(0)
            ->setBaseTaxAmount(0)
            ->setTaxAmount(0);

        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }
            $order
                ->setBaseSubtotal($order->getBaseSubtotal() + $orderItem->getBaseRowTotal())
                ->setSubtotal($order->getSubtotal() + $orderItem->getRowTotal())
                ->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax() + $orderItem->getBaseRowTotalInclTax())
                ->setSubtotalInclTax($order->getSubtotalInclTax() + $orderItem->getRowTotalInclTax())
                ->setDiscountAmount($order->getDiscountAmount() - $orderItem->getDiscountAmount())
                ->setBaseDiscountAmount($order->getBaseDiscountAmount() - $orderItem->getBaseDiscountAmount())
                ->setBaseTaxAmount($order->getBaseTaxAmount() + $orderItem->getBaseTaxAmount())
                ->setTaxAmount($order->getTaxAmount() + $orderItem->getTaxAmount());
        }

        $order
            ->setBaseGrandTotal(
                $order->getBaseSubtotalInclTax()
                + $order->getBaseDiscountAmount()
                + $order->getBaseShippingInclTax()
            )
            ->setGrandTotal(
                $order->getSubtotalInclTax()
                + $order->getDiscountAmount()
                + $order->getShippingInclTax()
            );
    }
}
