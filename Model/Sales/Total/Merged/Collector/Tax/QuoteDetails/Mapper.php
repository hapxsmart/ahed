<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax\QuoteDetails;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Total\Group\Resolver;
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
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax\QuoteDetails
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
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var Resolver
     */
    private $totalsGroupResolver;

    /**
     * @var int
     */
    private $sequenceCounter = 0;

    /**
     * @param TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param ProductRepositoryInterface $productRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionInterfaceFactory $regionFactory
     * @param DataResolver $dataResolver
     * @param Resolver $totalsGroupResolver
     */
    public function __construct(
        TaxClassKeyInterfaceFactory $taxClassKeyFactory,
        ProductRepositoryInterface $productRepository,
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory,
        DataResolver $dataResolver,
        Resolver $totalsGroupResolver
    ) {
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->productRepository = $productRepository;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->dataResolver = $dataResolver;
        $this->totalsGroupResolver = $totalsGroupResolver;
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

        $paymentPeriod = $data->getPaymentPeriod();
        $totalsProvider = $this->totalsGroupResolver->getTotalsGroup($paymentPeriod)
            ->getProvider();

        $unitPrice = $data->getIsPriceIncludesTax()
            ? $totalsProvider->getUnitPriceInclTax($item, $useBaseCurrency)
            : $totalsProvider->getUnitPrice($item, $useBaseCurrency);

        $detailsItem->setCode($item->getTaxCalculationItemId())
            ->setType('product')
            ->setTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($product->getTaxClassId())
            )
            ->setUnitPrice($unitPrice)
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
        $detailsItem->setType('shipping')
            ->setCode('shipping')
            ->setQuantity(1)
            ->setUnitPrice($data->getShippingAmount())
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
     * Map payments info to quote details
     *
     * @param QuoteDetailsInterface $quoteDetails
     * @param QuoteDetailsItemInterface[] $itemsDetails
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return QuoteDetailsInterface
     */
    public function mapPaymentsInfo($quoteDetails, $itemsDetails, $paymentsInfo)
    {
        $billingAddress = $this->dataResolver->getBillingAddress($paymentsInfo);
        $shippingAddress = $this->dataResolver->getShippingAddress($paymentsInfo);

        $quoteDetails->setBillingAddress($this->mapAddress($billingAddress))
            ->setShippingAddress($this->mapAddress($shippingAddress))
            ->setCustomerTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($this->dataResolver->getCustomerTaxClassId($paymentsInfo))
            )
            ->setItems($itemsDetails)
            ->setCustomerId($this->dataResolver->getCustomerId($paymentsInfo));

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
