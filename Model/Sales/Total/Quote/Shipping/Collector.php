<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Shipping;

use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Shipping\RateRequest\DataCollector;
use Aheadworks\Sarp2\Model\Shipping\RatesCollector;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

/**
 * Class Collector
 */
class Collector extends AbstractTotal
{
    /**
     * @var RatesCollector
     */
    private $ratesCollector;

    /**
     * @var DataCollector
     */
    private $extraAddressDataCollector;

    /**
     * @var GroupInterface
     */
    private $totalsGroup;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @param RatesCollector $ratesCollector
     * @param DataCollector $extraAddressDataCollector
     * @param GroupInterface $totalsGroup
     * @param Factory $dataObjectFactory
     * @param Summator $grandSummator
     */
    public function __construct(
        RatesCollector $ratesCollector,
        DataCollector $extraAddressDataCollector,
        GroupInterface $totalsGroup,
        Factory $dataObjectFactory,
        Summator $grandSummator
    ) {
        $this->ratesCollector = $ratesCollector;
        $this->extraAddressDataCollector = $extraAddressDataCollector;
        $this->totalsGroup = $totalsGroup;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->grandSummator = $grandSummator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $baseAmount = 0;

        $shipping = $shippingAssignment->getShipping();
        /** @var Address $address */
        $address = $shipping->getAddress();
        $method = $shipping->getMethod();
        $currencyForConvert = $quote->getForcedCurrency()
            ? $quote->getForcedCurrency()->getCode()
            : null;

        if (count($shippingAssignment->getItems()) && $method) {
            $rates = $this->ratesCollector->collect(
                $address,
                $this->extraAddressDataCollector->collect($shippingAssignment),
                $this->totalsGroup->getProvider()
            );
            foreach ($rates as $rate) {
                if ($rate->getCode() == $method) {
                    $baseAmount = $rate->getPrice();
                }
            }
        }
        $this->totalsGroup->getPopulator(AddressInterface::class)
            ->populate(
                $address,
                $this->dataObjectFactory->create(['shipping_amount' => $baseAmount]),
                PopulatorInterface::CURRENCY_OPTION_CONVERT,
                $currencyForConvert
            );
        $this->grandSummator->setAmount(
            $this->totalsGroup->getCode() . '_shipping',
            $baseAmount
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Quote $quote, Total $total)
    {
        return null;
    }
}
