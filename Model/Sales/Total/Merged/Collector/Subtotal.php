<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Total\Group\Resolver;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Subject;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Class Subtotal
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector
 */
class Subtotal implements CollectorInterface
{
    /**
     * @var Resolver
     */
    private $totalsGroupResolver;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param Resolver $totalsGroupResolver
     * @param Summator $grandSummator
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        Resolver $totalsGroupResolver,
        Summator $grandSummator,
        PriceCurrency $priceCurrency
    ) {
        $this->totalsGroupResolver = $totalsGroupResolver;
        $this->grandSummator = $grandSummator;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Subject $subject)
    {
        $baseSubtotal = 0;

        foreach ($subject->getItemPairs() as $pair) {
            /** @var ProfileItemInterface $profileItem */
            $profileItem = $pair[0]->getItem();
            /** @var OrderItemInterface $orderItem */
            $orderItem = $pair[1];
            $totalsGroup = $this->totalsGroupResolver->getTotalsGroup($pair[0]->getPaymentPeriod());

            $basePrice = $totalsGroup->getItemPrice($profileItem, true);
            $baseRowTotal = $basePrice * $profileItem->getQty();
            $orderItem->setBasePrice($basePrice)
                ->setBaseRowTotal($baseRowTotal);

            $price = $totalsGroup->getItemPrice($profileItem, false);
            $rowTotal = $price * $profileItem->getQty();
            $orderItem->setPrice($price)
                ->setRowTotal($rowTotal);

            if (!$orderItem->getParentItem()) {
                $baseSubtotal += $baseRowTotal;
            }
        }

        $order = $subject->getOrder();
        $order->setBaseSubtotal($baseSubtotal)
            ->setSubtotal($this->priceCurrency->convert($baseSubtotal));

        $this->grandSummator->setTotalAmount('subtotal', $baseSubtotal);
    }
}
