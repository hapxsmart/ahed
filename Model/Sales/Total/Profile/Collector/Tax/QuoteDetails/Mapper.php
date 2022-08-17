<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Tax\QuoteDetails;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\DataObject;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;

/**
 * Class Mapper
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Tax\QuoteDetails
 */
class Mapper
{
    /**
     * @var TaxClassKeyInterfaceFactory
     */
    private $taxClassKeyFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var int
     */
    private $sequenceCounter = 0;

    /**
     * @param TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param ProductRepositoryInterface $productRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionInterfaceFactory $regionFactory
     */
    public function __construct(
        TaxClassKeyInterfaceFactory $taxClassKeyFactory,
        ProductRepositoryInterface $productRepository,
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory
    ) {
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->productRepository = $productRepository;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Map item
     *
     * @param QuoteDetailsItemInterface $detailsItem
     * @param DataObject $data
     * @return QuoteDetailsItemInterface
     */
    public function mapItem($detailsItem, $data)
    {
        /** @var Item $item */
        $item = $data->getItem();
        if (!$item->getTaxCalculationItemId()) {
            $sequence = 'sequence-' . $this->getNextIncrement();
            $item->setTaxCalculationItemId($sequence);
        }

        $product = $this->productRepository->getById($item->getProductId(), false, $data->getStoreId());
        $useBaseCurrency = $data->getIsBaseCurrency();
        /** @var ProviderInterface $totalsProvider */
        $totalsProvider = $data->getTotalsProvider();

        $unitPrice = $totalsProvider->getUnitPrice($item, $useBaseCurrency);

        $detailsItem->setCode($item->getTaxCalculationItemId())
            ->setType('product')
            ->setTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($product->getTaxClassId())
            )
            ->setUnitPrice($unitPrice)
            ->setName($item->getName())
            ->setProductId($item->getProductId())
            ->setDiscountAmount($totalsProvider->getDiscountAmount($item, $useBaseCurrency))
            ->setQuantity($item->getQty())
            ->setIsTaxIncluded($data->getIsPriceIncludesTax())
            ->setParentCode($data->getParentCode());
        return $detailsItem;
    }

    /**
     * Map shipping item
     *
     * @param QuoteDetailsItemInterface $detailsItem
     * @param DataObject $data
     * @return QuoteDetailsItemInterface
     */
    public function mapShippingItem($detailsItem, $data)
    {
        /** @var ProviderInterface $totalsProvider */
        $totalsProvider = $data->getTotalsProvider();
        $detailsItem->setType('shipping')
            ->setCode('shipping')
            ->setQuantity(1)
            ->setUnitPrice(
                $totalsProvider->getShippingAmount($data->getAddress(), $data->getIsBaseCurrency())
            )
            ->setTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($data->getTaxClass())
            )
            ->setIsTaxIncluded($data->getIsPriceIncludesTax());
        return $detailsItem;
    }

    /**
     * Map profile address
     *
     * @param ProfileAddressInterface $profileAddress
     * @return AddressInterface
     */
    public function mapAddress($profileAddress)
    {
        $address = $this->addressFactory->create();
        $street = $profileAddress->getStreet() ? : [];
        if (!is_array($street)) {
            $street = explode("\n", (string)$street);
        }
        $address->setCountryId($profileAddress->getCountryId())
            ->setRegion(
                $this->regionFactory->create()->setRegionId($profileAddress->getRegionId())
            )
            ->setPostcode($profileAddress->getPostcode())
            ->setCity($profileAddress->getCity())
            ->setStreet($street);
        return $address;
    }

    /**
     * Map profile to quote details
     *
     * @param QuoteDetailsInterface $quoteDetails
     * @param QuoteDetailsItemInterface[] $itemsDetails
     * @param ProfileInterface $profile
     * @return QuoteDetailsInterface
     */
    public function mapProfile($quoteDetails, $itemsDetails, $profile)
    {
        $quoteDetails->setBillingAddress($this->mapAddress($profile->getBillingAddress()))
            ->setShippingAddress($this->mapAddress($profile->getShippingAddress()))
            ->setCustomerTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($profile->getCustomerTaxClassId())
            )
            ->setItems($itemsDetails)
            ->setCustomerId($profile->getCustomerId());
        return $quoteDetails;
    }

    /**
     * Get next sequence value
     *
     * @return int
     */
    private function getNextIncrement()
    {
        return ++$this->sequenceCounter;
    }
}
