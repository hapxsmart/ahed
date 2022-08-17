<?php
namespace Aheadworks\Sarp2\Api;

/**
 * Interface ProfileAddressRepositoryInterface
 * @package Aheadworks\Sarp2\Api
 */
interface ProfileAddressRepositoryInterface
{
    /**
     * Save profile address
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface $address
     * @return \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function save(\Aheadworks\Sarp2\Api\Data\ProfileAddressInterface $address);

    /**
     * Retrieve profile address
     *
     * @param int $addressId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($addressId);
}
