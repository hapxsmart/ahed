<?php
namespace Aheadworks\Sarp2\Model\Shipping\RateRequest;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Builder
 * @package Aheadworks\Sarp2\Model\Shipping\RateRequest
 */
class Builder
{
    /**
     * @var Address|ProfileAddressInterface
     */
    private $address;

    /**
     * @var DataObject
     */
    private $additionalAddressData;

    /**
     * @var ProviderInterface
     */
    private $totalsProvider;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;

    /**
     * @var Copy
     */
    private $copyService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $quoteAddressCopyMap = [
        'getAllItems' => 'setAllItems',
        'getCountryId' => 'setDestCountryId',
        'getRegionId' => 'setDestRegionId',
        'getRegionCode' => 'setDestRegionCode',
        'getStreetFull' => 'setDestStreet',
        'getCity' => 'setDestCity',
        'getPostcode' => 'setDestPostcode',
        'getLimitCarrier' => 'setLimitCarrier'
    ];

    /**
     * @var array
     */
    private $profileAddressCopyMap = [
        'getCountryId' => 'setDestCountryId',
        'getRegionId' => 'setDestRegionId',
        'getCity' => 'setDestCity',
        'getPostcode' => 'setDestPostcode'
    ];

    /**
     * @param RateRequestFactory $rateRequestFactory
     * @param Copy $copyService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RateRequestFactory $rateRequestFactory,
        Copy $copyService,
        StoreManagerInterface $storeManager
    ) {
        $this->rateRequestFactory = $rateRequestFactory;
        $this->copyService = $copyService;
        $this->storeManager = $storeManager;
    }

    /**
     * Set address
     *
     * @param Address|ProfileAddressInterface $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Set additional address data
     *
     * @param DataObject $additionalAddressData
     * @return $this
     */
    public function setAdditionalAddressData($additionalAddressData)
    {
        $this->additionalAddressData = $additionalAddressData;
        return $this;
    }

    /**
     * Set totals provider
     *
     * @param ProviderInterface $totalsProvider
     * @return $this
     */
    public function setTotalsProvider($totalsProvider)
    {
        $this->totalsProvider = $totalsProvider;
        return $this;
    }

    /**
     * Set store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Build rate request instance
     */
    public function build()
    {
        $result = null;
        if ($this->isStateValid()) {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeId
                ? $this->storeManager->getStore($this->storeId)
                : $this->storeManager->getStore();

            $result = $this->rateRequestFactory->create();
            $result = $this->copyService->copyAddress(
                $this->address,
                $result,
                $this->address instanceof Address ? $this->quoteAddressCopyMap : $this->profileAddressCopyMap
            );
            $result->setPackageValue($this->totalsProvider->getAddressSubtotal($this->address, true))
                ->setPackageValueWithDiscount($this->totalsProvider->getAddressSubtotal($this->address, true))
                ->setPackageWeight($this->additionalAddressData->getWeight())
                ->setPackageQty($this->additionalAddressData->getAddressQty())
                ->setPackagePhysicalValue($this->totalsProvider->getAddressSubtotal($this->address, true))
                ->setFreeMethodWeight($this->additionalAddressData->getFreeMethodWeight())
                ->setStoreId($store->getId())
                ->setWebsiteId($store->getWebsite()->getId())
                ->setFreeShipping($this->additionalAddressData->getFreeShipping())
                ->setBaseCurrency($store->getBaseCurrency())
                ->setPackageCurrency($store->getCurrentCurrency())
                ->setBaseSubtotalInclTax($this->totalsProvider->getAddressSubtotalInclTax($this->address, true));
            if ($this->address instanceof ProfileAddressInterface) {
                $result->setAllItems($this->additionalAddressData->getAllItems() ? : [])
                    ->setRegionCode($this->additionalAddressData->getRegionCode())
                    ->setDestStreet($this->additionalAddressData->getFullStreet());
            } elseif ($this->additionalAddressData->hasAllItems()) {
                $result->setAllItems($this->additionalAddressData->getAllItems());
            }
        }
        $this->resetState();
        return $result;
    }

    /**
     * Check if state is valid for build
     *
     * @return bool
     */
    private function isStateValid()
    {
        return isset($this->address)
            && ($this->address instanceof Address || $this->address instanceof ProfileAddressInterface)
            && isset($this->additionalAddressData)
            && isset($this->totalsProvider);
    }

    /**
     * Reset state
     *
     * @return void
     */
    private function resetState()
    {
        $this->address = null;
        $this->additionalAddressData = null;
        $this->totalsProvider = null;
        $this->storeId = null;
    }
}
