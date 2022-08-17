<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileAddressRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Address as AddressResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AddressRepository
 * @package Aheadworks\Sarp2\Model\Profile
 */
class AddressRepository implements ProfileAddressRepositoryInterface
{
    /**
     * @var ProfileAddressInterface[]
     */
    private $instances = [];

    /**
     * @var AddressResource
     */
    private $resource;

    /**
     * @var ProfileAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @param AddressResource $resource
     * @param ProfileAddressInterfaceFactory $addressFactory
     */
    public function __construct(
        AddressResource $resource,
        ProfileAddressInterfaceFactory $addressFactory
    ) {
        $this->resource = $resource;
        $this->addressFactory = $addressFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProfileAddressInterface $address)
    {
        try {
            $this->resource->save($address);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $addressId = $address->getAddressId();
        unset($this->instances[$addressId]);
        return $this->get($addressId);
    }

    /**
     * {@inheritdoc}
     */
    public function get($addressId)
    {
        if (!isset($this->instances[$addressId])) {
            /** @var ProfileAddressInterface $address */
            $address = $this->addressFactory->create();
            $this->resource->load($address, $addressId);
            if (!$address->getAddressId()) {
                throw NoSuchEntityException::singleField('addressId', $addressId);
            }
            $this->instances[$addressId] = $address;
        }
        return $this->instances[$addressId];
    }
}
