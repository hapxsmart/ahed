<?php
namespace Aheadworks\Sarp2\Model\Quote;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PrePaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\PrePaymentInfoInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\DateResolver;
use Aheadworks\Sarp2\Model\Quote\Item\Filter;
use Aheadworks\Sarp2\Model\Quote\Item\Grouping;
use Aheadworks\Sarp2\Model\Quote\Item\ToProfileItem;
use Aheadworks\Sarp2\Model\Quote\Processor\Customer as CustomerProcessor;
use Aheadworks\Sarp2\Model\Sales\Item\Checker\IsVirtual;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Processor
 * @package Aheadworks\Sarp2\Model\Quote
 */
class Processor
{
    /**
     * @var ProfileInterfaceFactory
     */
    private $profileFactory;

    /**
     * @var ProfileAddressInterfaceFactory
     */
    private $profileAddressFactory;

    /**
     * @var PrePaymentInfoInterfaceFactory
     */
    private $prePaymentInfoFactory;

    /**
     * @var Filter
     */
    private $itemFilter;

    /**
     * @var Grouping
     */
    private $itemGrouping;

    /**
     * @var IsVirtual
     */
    private $isVirtualChecker;

    /**
     * @var CustomerProcessor
     */
    private $customerDataProcessor;

    /**
     * @var ToProfileItem
     */
    private $quoteItemToProfileItem;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var DateResolver
     */
    private $startDateResolver;

    /**
     * @param ProfileInterfaceFactory $profileFactory
     * @param ProfileAddressInterfaceFactory $profileAddressFactory
     * @param PrePaymentInfoInterfaceFactory $prePaymentInfoFactory
     * @param Filter $itemFilter
     * @param Grouping $itemGrouping
     * @param IsVirtual $isVirtualChecker
     * @param CustomerProcessor $customerDataProcessor
     * @param ToProfileItem $quoteItemToProfileItem
     * @param Copy $objectCopyService
     * @param DateResolver $startDateResolver
     */
    public function __construct(
        ProfileInterfaceFactory $profileFactory,
        ProfileAddressInterfaceFactory $profileAddressFactory,
        PrePaymentInfoInterfaceFactory $prePaymentInfoFactory,
        Filter $itemFilter,
        Grouping $itemGrouping,
        IsVirtual $isVirtualChecker,
        CustomerProcessor $customerDataProcessor,
        ToProfileItem $quoteItemToProfileItem,
        Copy $objectCopyService,
        DateResolver $startDateResolver
    ) {
        $this->profileFactory = $profileFactory;
        $this->profileAddressFactory = $profileAddressFactory;
        $this->prePaymentInfoFactory = $prePaymentInfoFactory;
        $this->itemFilter = $itemFilter;
        $this->itemGrouping = $itemGrouping;
        $this->isVirtualChecker = $isVirtualChecker;
        $this->customerDataProcessor = $customerDataProcessor;
        $this->quoteItemToProfileItem = $quoteItemToProfileItem;
        $this->objectCopyService = $objectCopyService;
        $this->startDateResolver = $startDateResolver;
    }

    /**
     * Create recurring profiles from quote
     *
     * @param Quote $quote
     * @return ProfileInterface[]
     */
    public function createProfiles(Quote $quote)
    {
        $profiles = [];

        $billingAddress = $quote->getBillingAddress();
        if ($quote->getCheckoutMethod() == CartManagementInterface::METHOD_GUEST) {
            $quote->setCustomerId(null)
                ->setCustomerEmail($billingAddress->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
        }
        if (!$quote->getCustomerIsGuest()) {
            $this->customerDataProcessor->process($quote);
        }

        $itemGroups = $this->itemGrouping->group(
            $this->itemFilter->filterOneOff($quote->getAllItems())
        );
        foreach ($itemGroups as $group) {
            /** @var ProfileInterface $profile */
            $profile = $this->profileFactory->create();
            $this->objectCopyService->copyFieldsetToTarget(
                'aw_sarp2_convert_quote',
                'to_profile',
                $quote,
                $profile
            );

            /** @var PlanInterface $plan */
            $plan = $group->getPlan();
            $planDefinition = $plan->getDefinition();
            $profile->setPlanId($plan->getPlanId())
                ->setPlanName($plan->getName())
                ->setPlanDefinitionId($planDefinition->getDefinitionId())
                ->setStartDate(
                    $this->startDateResolver->getStartDate(
                        $planDefinition->getStartDateType(),
                        $planDefinition->getStartDateDayOfMonth()
                    )
                );

            /** @var PrePaymentInfoInterface $prePaymentInfo */
            $prePaymentInfo = $this->prePaymentInfoFactory->create();
            $prePaymentInfo->setIsInitialFeePaid($group->getIsInitialFeeInclInPrepayment())
                ->setIsTrialPaid($group->getIsTrialPriceInclInPrepayment())
                ->setIsRegularPaid($group->getIsRegularPriceInclInPrepayment());

            $profile->setPrePaymentInfo($prePaymentInfo);

            /** @var Item[] $items */
            $items = $group->getItems();
            $isVirtual = $this->isVirtualChecker->check($items);
            $profile->setIsVirtual($isVirtual);
            if (!$isVirtual) {
                $shippingAddress = $quote->getShippingAddress();
                $profile->setCustomerFullname($shippingAddress->getName())
                    ->setCheckoutShippingMethod($shippingAddress->getShippingMethod())
                    ->setCheckoutShippingDescription($shippingAddress->getShippingDescription());
            } else {
                $profile->setCustomerFullname($billingAddress->getName());
            }

            $profileItems = [];
            $profileItemsQty = 0;
            $itemToProfile = [];
            /** @var Item $item */
            foreach ($items as $item) {
                if (!$item->getParentItemId()) {
                    $parentProfileItem = null;
                    $profileItem = $this->quoteItemToProfileItem->convert($item, $parentProfileItem);
                    $itemToProfile[$item->getItemId()] = $profileItem;
                    $profileItemsQty += $profileItem->getQty();
                } else {
                    $parentProfileItem = $itemToProfile[$item->getParentItemId()];
                    $profileItem = $this->quoteItemToProfileItem->convert($item, $parentProfileItem);
                }
                $profileItems[] = $profileItem;
            }
            $profile->setItems($profileItems)
                ->setItemsQty($profileItemsQty);

            $profileAddresses = [];
            foreach ([$quote->getShippingAddress(), $billingAddress] as $address) {
                /** @var ProfileAddressInterface $profileAddress */
                $profileAddress = $this->profileAddressFactory->create();
                $this->objectCopyService->copyFieldsetToTarget(
                    'aw_sarp2_convert_quote_address',
                    'to_profile_address',
                    $address,
                    $profileAddress
                );
                $profileAddress->setIsFreeShipping($address->getFreeShipping());
                $profileAddress->setProfile($profile);
                $profileAddresses[] = $profileAddress;
            }
            $profile->setAddresses($profileAddresses);

            $profiles[] = $profile;
        }

        return $profiles;
    }
}
