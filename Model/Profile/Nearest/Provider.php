<?php
namespace Aheadworks\Sarp2\Model\Profile\Nearest;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Provider
 * @package Aheadworks\Sarp2\Model\Profile\Nearest
 */
class Provider
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileResource
     */
    private $profileResource;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileResource $profileResource
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        ProfileResource $profileResource
    ) {
        $this->profileRepository = $profileRepository;
        $this->profileResource = $profileResource;
    }

    /**
     * Get nearest customer subscription profile
     *
     * @param int $customerId
     * @param int $storeId
     * @return ProfileInterface|null
     * @throws LocalizedException
     */
    public function getNearestProfile($customerId, $storeId): ?ProfileInterface
    {
        $profileId = $this->profileResource->getNearestProfileId($customerId, $storeId);

        return $profileId ? $this->profileRepository->get($profileId) : null;
    }
}
