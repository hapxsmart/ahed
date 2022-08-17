<?php
namespace Aheadworks\Sarp2\Model\Profile\Address;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Customer\Model\Address\Mapper as CustomerAddressMapper;

/**
 * Class ToProfileAddress
 * @package Aheadworks\Sarp2\Model\Profile\Address
 */
class ToProfileAddress
{
    /**
     * @var OrderAddressInterfaceFactory
     */
    private $profileAddressFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var CustomerAddressMapper
     */
    private $addressMapper;

    /**
     * @param ProfileAddressInterfaceFactory $profileAddressFactory
     * @param Copy $objectCopyService
     * @param CustomerAddressMapper $addressMapper
     */
    public function __construct(
        ProfileAddressInterfaceFactory $profileAddressFactory,
        Copy $objectCopyService,
        CustomerAddressMapper $addressMapper
    ) {
        $this->profileAddressFactory = $profileAddressFactory;
        $this->objectCopyService = $objectCopyService;
        $this->addressMapper = $addressMapper;
    }

    /**
     * Convert order address to profile address
     *
     * @param AddressInterface $customerAddress
     * @param ProfileAddressInterface $profileAddress
     * @return ProfileAddressInterface
     */
    public function convert(AddressInterface $customerAddress, $profileAddress = null)
    {
        /** @var ProfileAddressInterface $profileAddress */
        $profileAddress = $profileAddress ? : $this->profileAddressFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_customer_address',
            'to_profile_address',
            $this->addressMapper->toFlatArray($customerAddress),
            $profileAddress
        );
        $profileAddress->setQuoteAddressId(null);

        return $profileAddress;
    }
}
