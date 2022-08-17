<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Shipping\RateRequest;

use Aheadworks\Sarp2\Model\Quote\Item\Filter;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\FreeShippingInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Class DataCollector
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Shipping\RateRequest
 */
class DataCollector
{
    /**
     * @var Filter
     */
    private $quoteItemFilter;

    /**
     * @var FreeShippingInterface
     */
    private $freeShipping;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param Filter $quoteItemFilter
     * @param FreeShippingInterface $freeShipping
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        Filter $quoteItemFilter,
        FreeShippingInterface $freeShipping,
        Factory $dataObjectFactory
    ) {
        $this->quoteItemFilter = $quoteItemFilter;
        $this->freeShipping = $freeShipping;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Collect rate request extra data
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return DataObject
     */
    public function collect($shippingAssignment)
    {
        return $this->performCollect(
            $shippingAssignment->getItems(),
            $shippingAssignment->getShipping()->getAddress()
        );
    }

    /**
     * Perform collect rate request data
     *
     * @param CartItemInterface[]|Item[] $items
     * @param AddressInterface|Address $address
     * @return DataObject
     */
    private function performCollect($items, $address)
    {
        $weight = 0;
        $freeMethodWeight = 0;
        $addressQty = 0;

        $addressFreeShipping = $address->getFreeShipping();

        /** @var Item $item */
        foreach ($items as $item) {
            $product = $item->getProduct();
            if (!$product->isVirtual() && !$item->getParentItem()) {
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getProduct()->isVirtual()) {
                            $addressQty += $child->getTotalQty();

                            if (!$product->getWeightType()) {
                                $itemWeight = $child->getWeight();
                                $itemQty = $child->getTotalQty();
                                $rowWeight = $itemWeight * $itemQty;
                                $weight += $rowWeight;
                                if ($addressFreeShipping || $child->getFreeShipping() === true) {
                                    $rowWeight = 0;
                                } elseif (is_numeric($child->getFreeShipping())) {
                                    $freeQty = $child->getFreeShipping();
                                    if ($itemQty > $freeQty) {
                                        $rowWeight = $itemWeight * ($itemQty - $freeQty);
                                    } else {
                                        $rowWeight = 0;
                                    }
                                }
                                $freeMethodWeight += $rowWeight;
                            }
                        }
                    }
                    if ($product->getWeightType()) {
                        $itemWeight = $item->getWeight();
                        $rowWeight = $itemWeight * $item->getQty();
                        $weight += $rowWeight;
                        if ($addressFreeShipping || $item->getFreeShipping() === true) {
                            $rowWeight = 0;
                        } elseif (is_numeric($item->getFreeShipping())) {
                            $freeQty = $item->getFreeShipping();
                            if ($item->getQty() > $freeQty) {
                                $rowWeight = $itemWeight * ($item->getQty() - $freeQty);
                            } else {
                                $rowWeight = 0;
                            }
                        }
                        $freeMethodWeight += $rowWeight;
                    }
                } else {
                    if (!$product->isVirtual()) {
                        $addressQty += $item->getQty();
                    }
                    $itemWeight = $item->getWeight();
                    $rowWeight = $itemWeight * $item->getQty();
                    $weight += $rowWeight;
                    if ($addressFreeShipping || $item->getFreeShipping() === true) {
                        $rowWeight = 0;
                    } elseif (is_numeric($item->getFreeShipping())) {
                        $freeQty = $item->getFreeShipping();
                        if ($item->getQty() > $freeQty) {
                            $rowWeight = $itemWeight * ($item->getQty() - $freeQty);
                        } else {
                            $rowWeight = 0;
                        }
                    }
                    $freeMethodWeight += $rowWeight;
                }
            }
        }

        return $this->dataObjectFactory->create(
            [
                'weight' => $weight,
                'free_method_weight' => $freeMethodWeight,
                'free_shipping' => $this->freeShipping->isFreeShipping(
                    $address->getQuote(),
                    $items
                ),
                'address_qty' => $addressQty
            ]
        );
    }
}
