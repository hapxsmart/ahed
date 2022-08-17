<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Set;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;

/**
 * Class DataResolver
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Set
 */
class DataResolver
{
    /**
     * Get all profiles items
     *
     * @param ProfileInterface[] $profiles
     * @return ProfileItemInterface[]
     */
    public function getItems($profiles)
    {
        $allItems = [];

        /**
         * @param ProfileInterface $profile
         * @return void
         */
        $callback = function ($profile) use (&$allItems) {
            foreach ($profile->getItems() as $item) {
                $allItems[$item->getItemId()] = $item;
            }
        };
        array_walk($profiles, $callback);
        return $allItems;
    }

    /**
     * Get profile addresses of specified address type
     *
     * @param ProfileInterface[] $profiles
     * @param $addressType
     * @return ProfileAddressInterface[]
     */
    public function getAddresses($profiles, $addressType)
    {
        /**
         * @param ProfileInterface $profile
         * @return ProfileAddressInterface
         */
        $closure = function ($profile) use ($addressType) {
            return $addressType == 'shipping'
                ? $profile->getShippingAddress()
                : $profile->getBillingAddress();
        };
        return array_map($closure, $profiles);
    }

    /**
     * Check if a set correspond to a virtual profile
     *
     * @param ProfileInterface[] $profiles
     * @return bool
     */
    public function isVirtual(array $profiles)
    {
        $isVirtual = true;
        foreach ($profiles as $profile) {
            if (!$profile->getIsVirtual()) {
                $isVirtual = false;
            }
        }
        return $isVirtual;
    }
}
