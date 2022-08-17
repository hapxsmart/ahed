<?php
namespace Aheadworks\Sarp2\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface PaymentTokenRepositoryInterface
 * @package Aheadworks\Sarp2\Api
 */
interface PaymentTokenRepositoryInterface
{
    /**
     * Save payment token
     *
     * @param \Aheadworks\Sarp2\Api\Data\PaymentTokenInterface $token
     * @return \Aheadworks\Sarp2\Api\Data\PaymentTokenInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function save(\Aheadworks\Sarp2\Api\Data\PaymentTokenInterface $token);

    /**
     * Retrieve payment token
     *
     * @param int $tokenId
     * @return \Aheadworks\Sarp2\Api\Data\PaymentTokenInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($tokenId);

    /**
     * Retrieve payment tokens matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Sarp2\Api\Data\PaymentTokenSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
