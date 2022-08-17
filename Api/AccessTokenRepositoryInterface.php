<?php
namespace Aheadworks\Sarp2\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface AccessTokenRepositoryInterface
 * @package Aheadworks\Sarp2\Api
 */
interface AccessTokenRepositoryInterface
{
    /**
     * Save access token
     *
     * @param \Aheadworks\Sarp2\Api\Data\AccessTokenInterface $token
     * @return \Aheadworks\Sarp2\Api\Data\AccessTokenInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function save(\Aheadworks\Sarp2\Api\Data\AccessTokenInterface $token);

    /**
     * Retrieve access token
     *
     * @param int $tokenId
     * @return \Aheadworks\Sarp2\Api\Data\AccessTokenInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($tokenId);

    /**
     * Retrieve access token by value
     *
     * @param string $tokenValue
     * @return \Aheadworks\Sarp2\Api\Data\AccessTokenInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByValue($tokenValue);

    /**
     * Retrieve access tokens matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Sarp2\Api\Data\AccessTokenSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete token
     *
     * @param \Aheadworks\Sarp2\Api\Data\AccessTokenInterface $token
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException;
     */
    public function delete($token);
}
