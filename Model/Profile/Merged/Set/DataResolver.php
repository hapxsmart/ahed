<?php
namespace Aheadworks\Sarp2\Model\Profile\Merged\Set;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Profile\ShippingMethod;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;

/**
 * Class DataResolver
 * @package Aheadworks\Sarp2\Model\Profile\Merged\Set
 */
class DataResolver
{
    /**
     * @var ShippingMethod
     */
    private $shippingMethodResolver;

    /**
     * @param ShippingMethod $shippingMethodResolver
     */
    public function __construct(ShippingMethod $shippingMethodResolver)
    {
        $this->shippingMethodResolver = $shippingMethodResolver;
    }

    /**
     * Check if a set correspond to a virtual profile
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return bool
     */
    public function isVirtual($paymentsInfo)
    {
        $isVirtual = true;
        foreach ($paymentsInfo as $info) {
            if (!$info->getProfile()->getIsVirtual()) {
                $isVirtual = false;
            }
        }
        return $isVirtual;
    }

    /**
     * Get payment period of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return string|null
     */
    public function getPaymentPeriod($paymentsInfo)
    {
        $isSame = true;
        $candidate = $paymentsInfo[0]->getPaymentPeriod();
        foreach ($paymentsInfo as $info) {
            if ($info->getPaymentPeriod() != $candidate) {
                $isSame = false;
            }
        }
        return $isSame ? $candidate : null;
    }

    /**
     * Get all profiles items of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return ProfileItemInterface[]
     */
    public function getItems($paymentsInfo)
    {
        $allItems = [];

        /**
         * @param PaymentInfoInterface $info
         * @return void
         */
        $callback = function ($info) use (&$allItems) {
            foreach ($info->getProfile()->getItems() as $item) {
                $allItems[$item->getItemId()] = $item;
            }
        };
        array_walk($paymentsInfo, $callback);
        return $allItems;
    }

    /**
     * Get payment method code of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return string
     */
    public function getPaymentMethod($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();
        return $firstProfile->getPaymentMethod();
    }

    /**
     * Get shipping method of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return string
     */
    public function getShippingMethod($paymentsInfo)
    {
        return $this->shippingMethodResolver->getResolvedValue(
            $this->getProfiles($paymentsInfo),
            ProfileInterface::CHECKOUT_SHIPPING_METHOD
        );
    }

    /**
     * Get shipping address of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return ProfileAddressInterface
     */
    public function getShippingAddress($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();
        return $firstProfile->getShippingAddress();
    }

    /**
     * Get billing address of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return ProfileAddressInterface
     */
    public function getBillingAddress($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();
        return $firstProfile->getBillingAddress();
    }

    /**
     * Get customer Id of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return int|null
     */
    public function getCustomerId($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();
        return $firstProfile->getCustomerId();
    }

    /**
     * Get customer group Id of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return int|null
     */
    public function getCustomerGroupId($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();
        return $firstProfile->getCustomerGroupId();
    }

    /**
     * Get customer tax class Id of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return int|null
     */
    public function getCustomerTaxClassId($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();
        return $firstProfile->getCustomerTaxClassId();
    }

    /**
     * Set store Id of set
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return int
     */
    public function getStoreId($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();
        return $firstProfile->getStoreId();
    }

    /**
     * Get profiles
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return ProfileInterface[]
     */
    private function getProfiles($paymentsInfo)
    {
        /**
         * @param PaymentInfoInterface $info
         * @return ProfileInterface
         */
        $closure = function ($info) {
            return $info->getProfile();
        };
        return array_map($closure, $paymentsInfo);
    }
}
