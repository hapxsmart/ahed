<?php
namespace Aheadworks\Sarp2\Model\Shipping\RateRequest;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class Copy
 * @package Aheadworks\Sarp2\Model\Shipping\RateRequest
 */
class Copy
{
    /**
     * Copy address to rate request
     *
     * @param Address|ProfileAddressInterface $address
     * @param RateRequest $request
     * @param array $map
     * @return RateRequest
     */
    public function copyAddress($address, $request, $map)
    {
        foreach ($map as $addressGetter => $requestSetter) {
            $value = $address->$addressGetter();
            $request->$requestSetter($value);
        }
        return $request;
    }
}
