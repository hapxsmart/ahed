<?php
namespace Aheadworks\Sarp2\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface ProfileRepositoryInterface
 * @package Aheadworks\Sarp2\Api
 */
interface ProfileRepositoryInterface
{
    /**
     * Save profile
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileInterface $profile
     * @param bool $recollectTotals
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function save(\Aheadworks\Sarp2\Api\Data\ProfileInterface $profile, $recollectTotals = true);

    /**
     * Retrieve profile
     *
     * @param int $profileId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($profileId);

    /**
     * Retrieve profile by hash
     *
     * @param string $hash
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByHash($hash);

    /**
     * Retrieve profiles matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
