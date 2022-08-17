<?php

declare(strict_types=1);

namespace Aheadworks\Sarp2\ViewModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;

/**
 * Profile view model
 */
class Profile implements ArgumentInterface
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->profileRepository = $profileRepository;
    }

    /**
     * Get profile
     *
     * @param int $profileId
     * @return ProfileInterface
     * @throws LocalizedException
     */
    public function getProfile(int $profileId) : ProfileInterface
    {
        return $this->profileRepository->get($profileId);
    }
}
