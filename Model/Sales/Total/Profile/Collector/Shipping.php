<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\Address\ToQASubstitute;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\Adapter\FreeShipping;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\AddressDataResolver;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\RatesCollector;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * Class Shipping
 */
class Shipping implements CollectorInterface
{
    /**
     * @var RatesCollector
     */
    private $ratesCollector;

    /**
     * @var AddressDataResolver
     */
    private $addressDataResolver;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ToQASubstitute
     */
    private $addressSubstituteConverter;

    /**
     * @var GroupInterface
     */
    private $totalsGroup;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var FreeShipping
     */
    private $freeShipping;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @param RatesCollector $ratesCollector
     * @param AddressDataResolver $addressDataResolver
     * @param ProductRepositoryInterface $productRepository
     * @param ToQASubstitute $addressSubstituteConverter
     * @param GroupInterface $totalsGroup
     * @param Config $config
     * @param Factory $dataObjectFactory
     * @param FreeShipping $freeShipping
     * @param Summator $grandSummator
     */
    public function __construct(
        RatesCollector $ratesCollector,
        AddressDataResolver $addressDataResolver,
        ProductRepositoryInterface $productRepository,
        ToQASubstitute $addressSubstituteConverter,
        GroupInterface $totalsGroup,
        Config $config,
        Factory $dataObjectFactory,
        FreeShipping $freeShipping,
        Summator $grandSummator
    ) {
        $this->ratesCollector = $ratesCollector;
        $this->addressDataResolver = $addressDataResolver;
        $this->productRepository = $productRepository;
        $this->addressSubstituteConverter = $addressSubstituteConverter;
        $this->totalsGroup = $totalsGroup;
        $this->config = $config;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->freeShipping = $freeShipping;
        $this->grandSummator = $grandSummator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ProfileInterface $profile)
    {
        $baseAmount = 0;
        $shippingMethod = null;
        $shippingDescription = null;

        $checkoutShippingMethod = $profile->getCheckoutShippingMethod();
        if (!$profile->getIsVirtual() && $checkoutShippingMethod) {
            $address = $profile->getShippingAddress();
            $storeId = $profile->getStoreId();

            $addressWeight = 0;
            $freeMethodWeight = 0;
            $addressQty = 0;
            $isAddressFreeShipping = $address->getIsFreeShipping();

            $baseVirtualAddressSubtotal = 0;

            /**
             * Calculate row weight
             *
             * @param Item $profileItem
             * @return float
             */
            $calcRowWeight = function ($profileItem) use (&$addressWeight, &$freeMethodWeight, $isAddressFreeShipping) {
                $itemWeight = (float)$profileItem->getWeight();
                $itemQty = (float)$profileItem->getQty();
                $addressWeight += ($itemWeight * $itemQty);
                $rowWeight = $this->calcRowWeight($profileItem, $isAddressFreeShipping);
                $freeMethodWeight += $rowWeight;

                return $rowWeight;
            };

            $nonVirtualItems = [];
            /** @var Item $item */
            foreach ($profile->getItems() as $item) {
                if (!$item->getIsVirtual()) {
                    $nonVirtualItems[] = $item;

                    if (!$item->getParentItem()) {
                        $product = $this->productRepository->getById(
                            $item->getProductId(),
                            false,
                            $storeId
                        );
                        $shipmentType = $product->getShipmentType();
                        $isShipSeparately = $shipmentType && (int)$shipmentType == AbstractType::SHIPMENT_SEPARATELY;

                        if ($item->hasChildItems() && $isShipSeparately) {
                            foreach ($item->getChildItems() as $childItem) {
                                if (!$childItem->getIsVirtual()) {
                                    $addressQty += $childItem->getQty();

                                    if (!$product->getWeightType()) {
                                        $item->setRowWeight($calcRowWeight($childItem));
                                    }
                                }
                            }
                            if ($product->getWeightType()) {
                                $item->setRowWeight($calcRowWeight($item));
                            }
                        } else {
                            $addressQty += $item->getQty();
                            $item->setRowWeight($calcRowWeight($item));
                        }
                    }
                } else {
                    $baseVirtualAddressSubtotal += $this->totalsGroup->getItemPrice($item, true) * $item->getQty();
                }
            }

            $addressSubstitute = $this->addressSubstituteConverter->convert(
                $address,
                $this->totalsGroup->getCode(),
                $profile,
                $nonVirtualItems,
                [
                    'street' => $this->addressDataResolver->getFullStreet($address),
                    'region_code' => $this->addressDataResolver->getRegionCode($address),
                    'item_qty' => $addressQty,
                    'base_virtual_amount' => $baseVirtualAddressSubtotal,
                    'weight' => $addressWeight,
                    'free_method_weight' => $freeMethodWeight
                ]
            );

            $isFreeShipping = $this->freeShipping->isFreeShipping($addressSubstitute);
            $address->setIsFreeShipping($isFreeShipping);
            $addressSubstitute->setFreeShipping($isFreeShipping);

            $shippingRate = $this->resolveShippingRate(
                $this->ratesCollector->collect($addressSubstitute),
                $checkoutShippingMethod,
                $this->config->getDefaultShippingMethod($storeId)
            );
            if ($shippingRate) {
                $baseAmount = $shippingRate->getPrice();
                $shippingMethod = $shippingRate->getCode();
                $shippingDescription = $shippingRate->getCarrierTitle() . ' - ' . $shippingRate->getMethodTitle();
            }
        }

        $this->totalsGroup->getPopulator(ProfileInterface::class)
            ->populate(
                $profile,
                $this->dataObjectFactory->create(
                    [
                        'shipping_amount' => $baseAmount,
                        'shipping_method' => $shippingMethod,
                        'shipping_description' => $shippingDescription
                    ]
                ),
                PopulatorInterface::CURRENCY_OPTION_CONVERT,
                $profile->getProfileCurrencyCode()
            );
        $this->grandSummator->setAmount(
            $this->totalsGroup->getCode() . '_shipping',
            $baseAmount
        );
    }

    /**
     * Calculate profile item row weight
     *
     * @param Item $profileItem
     * @param bool $isAddressFreeShipping
     * @return float
     */
    private function calcRowWeight($profileItem, $isAddressFreeShipping)
    {
        $itemWeight = (float)$profileItem->getWeight();
        $itemQty = (float)$profileItem->getQty();
        $rowWeight = $itemWeight * $itemQty;

        if ($isAddressFreeShipping || $profileItem->getIsFreeShipping()) {
            $rowWeight = 0;
        } elseif ($profileItem->getFreeShippingQty()) {
            $freeQty = $profileItem->getFreeShippingQty();
            if ($itemQty > $freeQty) {
                $rowWeight = $itemWeight * ($itemQty - $freeQty);
            } else {
                $rowWeight = 0;
            }
        }

        return $rowWeight;
    }

    /**
     * Resolve shipping rate
     *
     * @param Rate[] $allRates
     * @param string $checkoutMethodCode
     * @param string $defaultMethodCode
     * @return Rate|null
     */
    private function resolveShippingRate($allRates, $checkoutMethodCode, $defaultMethodCode)
    {
        $foundedRate = null;
        $defaultRate = null;
        foreach ($allRates as $rate) {
            $code = $rate->getCode();
            if ($code == $checkoutMethodCode) {
                $foundedRate = $rate;
            }
            if ($code == $defaultMethodCode) {
                $defaultRate = $rate;
            }
        }

        if ($foundedRate || $defaultRate) {
            return $foundedRate ? : $defaultRate;
        }
        return null;
    }
}
