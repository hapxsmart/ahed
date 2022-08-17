<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Profile\Merged\Address\ToQASubstitute;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Subject;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\AddressDataResolver;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\Adapter\FreeShipping;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\RatesCollector;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Sales\Model\Order;

/**
 * Class Shipping
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector
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
     * @var FreeShipping
     */
    private $freeShipping;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ToQASubstitute
     */
    private $addressSubstituteConverter;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var DataResolver
     */
    private $setDataResolver;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param RatesCollector $ratesCollector
     * @param AddressDataResolver $addressDataResolver
     * @param FreeShipping $freeShipping
     * @param ProductRepositoryInterface $productRepository
     * @param ToQASubstitute $addressSubstituteConverter
     * @param Config $config
     * @param Summator $grandSummator
     * @param PriceCurrency $priceCurrency
     * @param DataResolver $setDataResolver
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        RatesCollector $ratesCollector,
        AddressDataResolver $addressDataResolver,
        FreeShipping $freeShipping,
        ProductRepositoryInterface $productRepository,
        ToQASubstitute $addressSubstituteConverter,
        Config $config,
        Summator $grandSummator,
        PriceCurrency $priceCurrency,
        DataResolver $setDataResolver,
        Factory $dataObjectFactory
    ) {
        $this->ratesCollector = $ratesCollector;
        $this->addressDataResolver = $addressDataResolver;
        $this->freeShipping = $freeShipping;
        $this->productRepository = $productRepository;
        $this->addressSubstituteConverter = $addressSubstituteConverter;
        $this->config = $config;
        $this->grandSummator = $grandSummator;
        $this->priceCurrency = $priceCurrency;
        $this->setDataResolver = $setDataResolver;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Subject $subject)
    {
        $baseAmount = 0;

        /** @var Order $order */
        $order = $subject->getOrder();
        $paymentsInfo = $subject->getPaymentsInfo();

        if (!$this->setDataResolver->isVirtual($paymentsInfo)) {
            $address = $this->setDataResolver->getShippingAddress($paymentsInfo);
            $storeId = $this->setDataResolver->getStoreId($paymentsInfo);

            $addressWeight = 0;
            $freeMethodWeight = 0;
            $addressQty = 0;
            $isAddressFreeShipping = $address->getIsFreeShipping();

            /**
             * Calculate row weight
             *
             * @param Item $profileItem
             * @return float
             */
            $calcRowWeight = function ($profileItem) use (&$addressWeight, &$freeMethodWeight, $isAddressFreeShipping) {
                $rowWeight = $this->calcRowWeight($profileItem, $isAddressFreeShipping);
                $addressWeight += $rowWeight;
                $freeMethodWeight += $rowWeight;
                return $rowWeight;
            };

            foreach ($this->setDataResolver->getItems($paymentsInfo) as $profileItem) {
                if (!$profileItem->getIsVirtual() && !$profileItem->getParentItem()) {
                    $product = $this->productRepository->getById(
                        $profileItem->getProductId(),
                        false,
                        $storeId
                    );
                    $shipmentType = $product->getShipmentType();
                    $isShipSeparately = $shipmentType && (int)$shipmentType == AbstractType::SHIPMENT_SEPARATELY;

                    if ($profileItem->hasChildItems() && $isShipSeparately) {
                        foreach ($profileItem->getChildItems() as $childItem) {
                            if (!$childItem->getIsVirtual()) {
                                $addressQty += $childItem->getQty();

                                if (!$product->getWeightType()) {
                                    $profileItem->setRowWeight($calcRowWeight($childItem));
                                }
                            }
                        }
                        if ($product->getWeightType()) {
                            $profileItem->setRowWeight($calcRowWeight($profileItem));
                        }
                    } else {
                        $addressQty += $profileItem->getQty();
                        $profileItem->setRowWeight($calcRowWeight($profileItem));
                    }
                }
            }

            $addressSubstitute = $this->addressSubstituteConverter->convert(
                $paymentsInfo,
                [
                    'street' => $this->addressDataResolver->getFullStreet($address),
                    'region_code' => $this->addressDataResolver->getRegionCode($address),
                    'item_qty' => $addressQty,
                    'weight' => $addressWeight,
                    'free_method_weight' => $freeMethodWeight
                ]
            );

            $isFreeShipping = $this->freeShipping->isFreeShipping($addressSubstitute);
            $address->setIsFreeShipping($isFreeShipping);
            $addressSubstitute->setFreeShipping($isFreeShipping);

            $shippingRate = $this->resolveShippingRate(
                $this->ratesCollector->collect($addressSubstitute),
                $this->setDataResolver->getShippingMethod($paymentsInfo),
                $this->config->getDefaultShippingMethod($storeId)
            );
            if ($shippingRate) {
                $baseAmount = $shippingRate->getPrice();
                $order->setData('shipping_method', $shippingRate->getCode())
                    ->setShippingDescription(
                        $shippingRate->getCarrierTitle() . ' - ' . $shippingRate->getMethodTitle()
                    );
            }
        }

        $order->setBaseShippingAmount($baseAmount)
            ->setShippingAmount($this->priceCurrency->convert($baseAmount));

        $this->grandSummator->setTotalAmount('shipping', $baseAmount);
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
        $itemWeight = $profileItem->getWeight();
        $itemQty = $profileItem->getQty();
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
     * @param Rate[] $addressRates
     * @param string $shippingMethod
     * @param string $defaultShippingMethod
     * @return Rate|null
     */
    private function resolveShippingRate($addressRates, $shippingMethod, $defaultShippingMethod)
    {
        $result = null;
        if (count($addressRates)) {
            $foundedRate = null;
            $defaultRate = null;
            $highestRate = $addressRates[0];

            foreach ($addressRates as $rate) {
                $code = $rate->getCode();
                if ($code == $shippingMethod) {
                    $foundedRate = $rate;
                }
                if ($code == $defaultShippingMethod) {
                    $defaultRate = $rate;
                }
                if ($rate->getPrice() > $highestRate->getPrice()) {
                    $highestRate = $rate;
                }
            }

            $result = $foundedRate ? : ($defaultRate ? : $highestRate);
        }
        return $result;
    }
}
