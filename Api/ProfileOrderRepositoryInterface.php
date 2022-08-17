<?php
namespace Aheadworks\Sarp2\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface ProfileOrderRepositoryInterface
 * @package Aheadworks\Sarp2\Api
 */
interface ProfileOrderRepositoryInterface
{
    /**
     * Retrieve profile orders matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Sarp2\Api\Data\ProfileOrderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
