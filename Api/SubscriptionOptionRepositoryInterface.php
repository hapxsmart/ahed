<?php
namespace Aheadworks\Sarp2\Api;

/**
 * Interface SubscriptionOptionRepositoryInterface
 * @package Aheadworks\Sarp2\Api
 */
interface SubscriptionOptionRepositoryInterface
{
    /**
     * Save subscription option
     *
     * @param \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface $option
     * @return \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function save(\Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface $option);

    /**
     * Retrieve subscription option
     *
     * @param int $optionId
     * @return \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function get($optionId);

    /**
     * Retrieve subscription options for specified product ID
     *
     * @param int $productId
     * @return \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList($productId);

    /**
     * Delete subscription option by ID
     *
     * @param int $optionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($optionId);
}
