<?php
namespace Aheadworks\Sarp2\Model\Profile\Data\Operation;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Data\OperationInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class ChangeAddress implements OperationInterface
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        ProfileManagementInterface $profileManagement
    ) {
        $this->addressRepository = $addressRepository;
        $this->profileManagement = $profileManagement;
    }

    /**
     * Change subscription item
     *
     * @param int $profileId
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function execute(int $profileId, array $data)
    {
        $address = $this->addressRepository->getById($data[ProfileAddressInterface::CUSTOMER_ADDRESS_ID]);
        $this->profileManagement->changeShippingAddress($data[ProfileAddressInterface::PROFILE_ID], $address);
    }
}
