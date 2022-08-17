<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\QuoteDetails;

use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;

/**
 * todo: consider reduce arguments number for each method, except mapShippingItem (use DataObject to pass data),
 *       M2SARP-345
 * Class Mapper
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\QuoteDetails
 */
class Mapper
{
    /**
     * @var TaxClassKeyInterfaceFactory
     */
    private $taxClassKeyFactory;

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
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionInterfaceFactory $regionFactory
     */
    public function __construct(
        TaxClassKeyInterfaceFactory $taxClassKeyFactory,
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory
    ) {
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Map item
     *
     * @param AddressItem $item
     * @param QuoteDetailsItemInterface $detailsItem
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param ProviderInterface $totalsProvider
     * @param int|null $parentCode
     * @return QuoteDetailsItemInterface
     */
    public function mapItem(
        $item,
        $detailsItem,
        $priceIncludesTax,
        $useBaseCurrency,
        $totalsProvider,
        $parentCode = null
    ) {
        if (!$item->getTaxCalculationItemId()) {
            $sequence = 'aw_sarp_sequence-' . $this->getNextIncrement();
            $item->setTaxCalculationItemId($sequence);
        }
        $detailsItem->setCode($item->getTaxCalculationItemId())
            ->setType('product')
            ->setTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($item->getProduct()->getTaxClassId())
            )
            ->setUnitPrice($totalsProvider->getUnitPrice($item, $useBaseCurrency))
            ->setDiscountAmount($totalsProvider->getDiscountAmount($item, $useBaseCurrency))
            ->setQuantity($item->getQty())
            ->setIsTaxIncluded($priceIncludesTax)
            ->setParentCode($parentCode);
        return $detailsItem;
    }

    /**
     * Map item extra taxable
     *
     * @param AddressItem $item
     * @param array $extraTaxable
     * @param QuoteDetailsItemInterface $extraTaxableDetailsItem
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @return QuoteDetailsItemInterface
     */
    public function mapItemExtraTaxable(
        $item,
        $extraTaxable,
        $extraTaxableDetailsItem,
        $priceIncludesTax,
        $useBaseCurrency
    ) {
        $isExtraTaxableIncludesTax = isset($extraTaxable['price_includes_tax'])
            ? $extraTaxable['price_includes_tax']
            : $priceIncludesTax;
        $unitPrice = $useBaseCurrency
            ? $extraTaxable['base_unit_price']
            : $extraTaxable['unit_price'];

        $extraTaxableDetailsItem->setCode($extraTaxable['code'])
            ->setType($extraTaxable['type'])
            ->setQuantity($extraTaxable['quantity'])
            ->setTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($extraTaxable['tax_class_id'])
            )
            ->setUnitPrice($unitPrice)
            ->setIsTaxIncluded($isExtraTaxableIncludesTax)
            ->setAssociatedItemCode($item->getTaxCalculationItemId());

        return $extraTaxableDetailsItem;
    }

    /**
     * Map shipping item
     *
     * @param QuoteDetailsItemInterface $detailsItem
     * @param DataObject $itemData
     * @param ProviderInterface $totalsProvider
     * @return QuoteDetailsItemInterface
     */
    public function mapShippingItem(
        $detailsItem,
        DataObject $itemData,
        ProviderInterface $totalsProvider
    ) {
        $detailsItem->setType('shipping')
            ->setCode('shipping')
            ->setQuantity(1)
            ->setUnitPrice(
                $totalsProvider->getShippingAmount($itemData->getAddress(), $itemData->getUseBaseCurrency())
            )
            ->setTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($itemData->getTaxClass())
            )
            ->setIsTaxIncluded($itemData->getPriceIncludesTax());

        return $detailsItem;
    }

    /**
     * Map quote address
     *
     * @param Address $quoteAddress
     * @return AddressInterface
     */
    public function mapAddress(Address $quoteAddress)
    {
        $address = $this->addressFactory->create();
        $address->setCountryId($quoteAddress->getCountryId())
            ->setRegion(
                $this->regionFactory->create()->setRegionId($quoteAddress->getRegionId())
            )
            ->setPostcode($quoteAddress->getPostcode())
            ->setCity($quoteAddress->getCity())
            ->setStreet($quoteAddress->getStreet());
        return $address;
    }

    /**
     * Map quote
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param QuoteDetailsItemInterface[] $itemsDetails
     * @param QuoteDetailsInterface $quoteDetails
     * @return QuoteDetailsInterface
     */
    public function mapQuote($shippingAssignment, $itemsDetails, $quoteDetails)
    {
        /** @var Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();
        $quote = $address->getQuote();

        $quoteDetails->setBillingAddress($this->mapAddress($quote->getBillingAddress()))
            ->setShippingAddress($this->mapAddress($address))
            ->setCustomerTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($quote->getCustomerTaxClassId())
            )
            ->setItems($itemsDetails)
            ->setCustomerId($quote->getCustomerId());

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
