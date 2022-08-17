<?php
namespace Aheadworks\Sarp2\Model\Quote\Address;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\CollectorInterface;
use Magento\Quote\Model\Quote\Address\TotalFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;

/**
 * Class TotalsCollector
 */
class TotalsCollector
{
    /**
     * @var TotalsCollectorList
     */
    private $collectorsList;

    /**
     * @var TotalFactory
     */
    private $totalFactory;

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var ShippingAssignmentFactory
     */
    private $shippingAssignmentFactory;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @param TotalsCollectorList $collectorsList
     * @param TotalFactory $totalFactory
     * @param ShippingFactory $shippingFactory
     * @param ShippingAssignmentFactory $shippingAssignmentFactory
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        TotalsCollectorList $collectorsList,
        TotalFactory $totalFactory,
        ShippingFactory $shippingFactory,
        ShippingAssignmentFactory $shippingAssignmentFactory,
        EventManagerInterface $eventManager
    ) {
        $this->collectorsList = $collectorsList;
        $this->totalFactory = $totalFactory;
        $this->shippingFactory = $shippingFactory;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * Collect address totals
     *
     * @param Quote $quote
     * @param Address $address
     * @return Total
     */
    public function collect($quote, $address)
    {
        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $this->shippingAssignmentFactory->create();

        /** @var ShippingInterface $shipping */
        $shipping = $this->shippingFactory->create();
        $shipping->setMethod($address->getShippingMethod());
        $shipping->setAddress($address);
        $shippingAssignment->setShipping($shipping);
        $shippingAssignment->setItems($address->getAllItems());

        /** @var Total $total */
        $total = $this->totalFactory->create(Total::class);
        $this->eventManager->dispatch(
            'sales_quote_address_collect_totals_before',
            [
                'quote' => $quote,
                'shipping_assignment' => $shippingAssignment,
                'total' => $total
            ]
        );

        foreach ($this->collectorsList->getCollectors($quote->getStoreId()) as $code => $collector) {
            /** @var CollectorInterface $collector */
            $collector->collect($quote, $shippingAssignment, $total);
        }

        $this->eventManager->dispatch(
            'sales_quote_address_collect_totals_after',
            [
                'quote' => $quote,
                'shipping_assignment' => $shippingAssignment,
                'total' => $total
            ]
        );

        $address->addData($total->getData());
        $address->setAppliedTaxes($total->getAppliedTaxes());
        $quote->setAppliedRuleIds($quote->getAppliedRuleIds() === null ? '' : $quote->getAppliedRuleIds());

        return $total;
    }
}
