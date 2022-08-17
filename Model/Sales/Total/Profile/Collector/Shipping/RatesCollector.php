<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping;

use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address as AddressSubstitute;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\Rate\RequestBuilder;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateFactory;

/**
 * Class RatesCollector
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping
 */
class RatesCollector
{
    /**
     * @var RateCollectorInterfaceFactory
     */
    private $rateCollector;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @param RateCollectorInterfaceFactory $rateCollector
     * @param RequestBuilder $requestBuilder
     * @param RateFactory $rateFactory
     */
    public function __construct(
        RateCollectorInterfaceFactory $rateCollector,
        RequestBuilder $requestBuilder,
        RateFactory $rateFactory
    ) {
        $this->rateCollector = $rateCollector;
        $this->requestBuilder = $requestBuilder;
        $this->rateFactory = $rateFactory;
    }

    /**
     * Collect shipping rates
     *
     * @param Address|AddressSubstitute $address
     * @return Rate[]
     */
    public function collect($address)
    {
        $rates = [];
        $result = $this->rateCollector->create()
            ->collectRates($this->requestBuilder->build($address))
            ->getResult();
        if ($result) {
            foreach ($result->getAllRates() as $shippingRate) {
                $rates[] = $this->rateFactory->create()->importShippingRate($shippingRate);
            }
        }
        return $rates;
    }
}
