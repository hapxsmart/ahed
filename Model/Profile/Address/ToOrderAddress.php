<?php
namespace Aheadworks\Sarp2\Model\Profile\Address;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;

/**
 * Class ToOrderAddress
 * @package Aheadworks\Sarp2\Model\Profile\Address
 */
class ToOrderAddress
{
    /**
     * @var OrderAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param OrderAddressInterfaceFactory $addressFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        OrderAddressInterfaceFactory $addressFactory,
        Copy $objectCopyService
    ) {
        $this->addressFactory = $addressFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert profile address to order address
     *
     * @param ProfileAddressInterface $profileAddress
     * @return OrderAddressInterface
     */
    public function convert(ProfileAddressInterface $profileAddress)
    {
        $address = $this->addressFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile_address',
            'to_order_address',
            $profileAddress,
            $address
        );
        return $address;
    }
}
