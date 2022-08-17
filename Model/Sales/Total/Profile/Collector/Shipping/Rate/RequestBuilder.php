<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\Rate;

use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address as AddressSubstitute;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;

/**
 * Class RequestBuilder
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\Rate
 */
class RequestBuilder
{
    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;

    /**
     * @param RateRequestFactory $rateRequestFactory
     */
    public function __construct(RateRequestFactory $rateRequestFactory)
    {
        $this->rateRequestFactory = $rateRequestFactory;
    }

    /**
     * Build rate request
     *
     * @param Address|AddressSubstitute $address
     * @param AddressItem|null $item
     * @return RateRequest
     */
    public function build($address, $item = null)
    {
        $store = $address->getQuote()->getStore();

        $rateRequest = $this->rateRequestFactory->create();
        $rateRequest->setAllItems($item ? [$item] : $address->getAllItems())
            ->setDestCountryId($address->getCountryId())
            ->setDestRegionId($address->getRegionId())
            ->setDestRegionCode($address->getRegionCode())
            ->setDestStreet($address->getStreetFull())
            ->setDestCity($address->getCity())
            ->setDestPostcode($address->getPostcode())
            ->setPackageValue($item ? $item->getBaseRowTotal() : $address->getBaseSubtotal())
            ->setPackageValueWithDiscount(
                $item
                    ? $item->getBaseRowTotal() - $item->getBaseDiscountAmount()
                    : $address->getBaseSubtotalWithDiscount()
            )
            ->setPackageWeight($item ? $item->getRowWeight() : $address->getWeight())
            ->setPackageQty($item ? $item->getQty() : $address->getItemQty())
            ->setPackagePhysicalValue(
                $item
                    ? $item->getBaseRowTotal()
                    : $address->getBaseSubtotal() - $address->getBaseVirtualAmount()
            )
            ->setFreeMethodWeight($item ? 0 : $address->getFreeMethodWeight())
            ->setStoreId($store->getId())
            ->setWebsiteId($store->getWebsite()->getId())
            ->setFreeShipping($address->getFreeShipping())
            ->setBaseCurrency($store->getBaseCurrency())
            ->setPackageCurrency($store->getCurrentCurrency())
            ->setLimitCarrier($address->getLimitCarrier())
            ->setBaseSubtotalInclTax($address->getBaseSubtotalTotalInclTax());
        return $rateRequest;
    }
}
