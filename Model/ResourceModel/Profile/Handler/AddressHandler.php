<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile\Handler;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileAddressRepositoryInterface;

class AddressHandler implements HandlerInterface
{
    /**
     * @var ProfileAddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param ProfileAddressRepositoryInterface $addressRepository
     */
    public function __construct(ProfileAddressRepositoryInterface $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ProfileInterface $profile)
    {
        foreach ($profile->getAddresses() as $address) {
            $address->setProfileId($profile->getProfileId());
            $customerId = $profile->getCustomerId();
            if ($customerId) {
                $address->setCustomerId($customerId);
            }
            $this->addressRepository->save($address);
        }
    }
}
