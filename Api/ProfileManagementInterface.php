<?php
namespace Aheadworks\Sarp2\Api;

/**
 * Interface ProfileManagementInterface
 *
 * @package Aheadworks\Sarp2\Api
 */
interface ProfileManagementInterface
{
    /**
     * Perform profile scheduling
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileInterface[] $profiles
     * @return void
     * @throws \Aheadworks\Sarp2\Api\Exception\CouldNotScheduleExceptionInterface
     */
    public function schedule($profiles);

    /**
     * Perform change status action
     *
     * @param int $profileId
     * @param string $status
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeStatusAction($profileId, $status);

    /**
     * Perform extend subscription action
     *
     * @param int $profileId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function extend($profileId);

    /**
     * Perform renew subscription action
     *
     * @param int $profileId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function renew($profileId);

    /**
     * Perform change shipping address action
     *
     * @param int $profileId
     * @param \Magento\Customer\Api\Data\AddressInterface $customerAddress
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeShippingAddress($profileId, $customerAddress);

    /**
     * Perform change product item
     *
     * @param int $profileId
     * @param int $itemId
     * @param mixed $buyRequest Array of parameters similar to those passed when adding product to quote
     * @param bool $isOneTimeOnly
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem\CouldNotUpdateProduct
     */
    public function changeProductItem($profileId, $itemId, $buyRequest, $isOneTimeOnly = false);

    /**
     * Perform change subscription plan
     *
     * @param int $profileId
     * @param int $newPlanId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeSubscriptionPlan($profileId, $newPlanId);

    /**
     * Perform change subscription plan
     *
     * @param int $profileId
     * @param string $newNextPaymentDate
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeNextPaymentDate($profileId, $newNextPaymentDate);

    /**
     * Perform change payment information
     *
     * @param int $profileId
     * @param \Magento\Quote\Api\Data\PaymentInterface $payment
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePaymentInformation(
        $profileId,
        \Magento\Quote\Api\Data\PaymentInterface $payment,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );

    /**
     * Perform set payment token
     *
     * @param int $profileId
     * @param int $paymentTokenId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setPaymentToken(
        $profileId,
        $paymentTokenId
    );

    /**
     * Perform remove product item from subscription
     *
     * @param int $profileId
     * @param int $itemId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeItem($profileId, $itemId);

    /**
     * Perform add items from quote to nearest subscription
     *
     * @param int $customerId
     * @param int $storeId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     */
    public function addItemsFromQuoteToNearestProfile($customerId, $storeId);

    /**
     * Retrieve profile next payment info
     *
     * @param int $profileId
     * @return \Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNextPaymentInfo($profileId);

    /**
     * Get allowed profile statuses
     *
     * @param int $profileId
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllowedStatuses($profileId);

    /**
     * Check if the customer is subscribed to the product
     *
     * @param int $customerId
     * @param int $productId
     * @param int|null $storeId
     * @return bool
     */
    public function isCustomerSubscribedOnProduct($customerId, $productId, $storeId = null);

    /**
     * Check if profile can be reactivated
     *
     * @param int $profileId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAllowedToReactivate($profileId);
}
