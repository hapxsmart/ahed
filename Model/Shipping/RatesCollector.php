<?php
namespace Aheadworks\Sarp2\Model\Shipping;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Aheadworks\Sarp2\Model\Shipping\RateRequest\Builder as RateRequestBuilder;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateFactory;

/**
 * Class RatesCollector
 * @package Aheadworks\Sarp2\Model\Shipping
 */
class RatesCollector
{
    /**
     * @var RateCollectorInterfaceFactory
     */
    private $rateCollector;

    /**
     * @var RateRequestBuilder
     */
    private $rateRequestBuilder;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @param RateCollectorInterfaceFactory $rateCollector
     * @param RateRequestBuilder $rateRequestBuilder
     * @param RateFactory $rateFactory
     */
    public function __construct(
        RateCollectorInterfaceFactory $rateCollector,
        RateRequestBuilder $rateRequestBuilder,
        RateFactory $rateFactory
    ) {
        $this->rateCollector = $rateCollector;
        $this->rateRequestBuilder = $rateRequestBuilder;
        $this->rateFactory = $rateFactory;
    }

    /**
     * Collect shipping rates
     *
     * @param Address|ProfileAddressInterface $address
     * @param DataObject $additionalAddressData
     * @param ProviderInterface $totalsProvider
     * @param int|null $storeId
     * @return Rate[]
     */
    public function collect($address, $additionalAddressData, $totalsProvider, $storeId = null)
    {
        $rates = [];

        $this->rateRequestBuilder->setAddress($address)
            ->setAdditionalAddressData($additionalAddressData)
            ->setTotalsProvider($totalsProvider);
        if ($storeId) {
            $this->rateRequestBuilder->setStoreId($storeId);
        }
        $request = $this->rateRequestBuilder->build();

        $result = $this->rateCollector->create()->collectRates($request)->getResult();
        if ($result) {
            foreach ($result->getAllRates() as $shippingRate) {
                $rates[] = $this->rateFactory->create()->importShippingRate($shippingRate);
            }
        }

        return $rates;
    }
}
