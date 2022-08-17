<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\AddItemsFromQuoteToNearest;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Profile\ItemManagement;
use Aheadworks\Sarp2\Model\ProfileManagement;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Quote\Item\ToProfileItem;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Model\Quote\Processor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository;

class Applier implements ApplierInterface
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ToProfileItem
     */
    private $toProfileItem;

    /**
     * @var ItemManagement
     */
    private $itemManagement;

    /**
     * @var IsSubscription
     */
    private $isSubscription;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var Processor
     */
    private $quoteProcessor;

    /**
     * @var ProfileManagement
     */
    private $profileManagement;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @param ResultFactory $validationResultFactory
     * @param ToProfileItem $toProfileItem
     * @param ItemManagement $itemManagement
     * @param CartRepositoryInterface $cartRepository
     * @param ProfileRepositoryInterface $profileRepository
     * @param PaymentsList $paymentsList
     * @param IsSubscription $isSubscription
     * @param Persistence $paymentPersistence
     * @param ProfileManagement $profileManagement
     * @param QuoteRepository $quoteRepository
     * @param Processor $quoteProcessor
     * @param ValidatorComposite $validator
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        ToProfileItem $toProfileItem,
        ItemManagement $itemManagement,
        CartRepositoryInterface $cartRepository,
        ProfileRepositoryInterface $profileRepository,
        PaymentsList $paymentsList,
        IsSubscription $isSubscription,
        Persistence $paymentPersistence,
        ProfileManagement $profileManagement,
        QuoteRepository $quoteRepository,
        Processor $quoteProcessor,
        ValidatorComposite $validator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->profileRepository = $profileRepository;
        $this->toProfileItem = $toProfileItem;
        $this->itemManagement = $itemManagement;
        $this->isSubscription = $isSubscription;
        $this->cartRepository = $cartRepository;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->quoteProcessor = $quoteProcessor;
        $this->profileManagement = $profileManagement;
        $this->quoteRepository = $quoteRepository;
        $this->validator = $validator;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $customerId = $action->getData()->getCustomerId();
        $storeId = $action->getData()->getStoreId();
        $quote = $this->getActiveQuote($customerId, $storeId);

        $this->processOneOffItems($profile, $quote);
        $this->processSubscriptionItems($profile, $quote);
        $this->profileRepository->save($profile, true);
        $this->updatePayments($profile);
        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);
    }

    /**
     * @inheritDoc
     */
    public function validate(ProfileInterface $profile, ActionInterface $action)
    {
        $isValid = $this->validator->isValid($profile, $action);

        $resultData = ['isValid' => $isValid];
        if (!$isValid) {
            $resultData['message'] = $this->validator->getMessage();
        }
        return $this->validationResultFactory->create($resultData);
    }

    /**
     * Update scheduled payments for profile
     *
     * @param ProfileInterface $profile
     * @throws CouldNotSaveException
     */
    private function updatePayments(ProfileInterface $profile): void
    {
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            if ($payment->getPaymentPeriod() == PaymentInterface::PERIOD_TRIAL) {
                $payment->setBaseTotalScheduled($profile->getBaseTrialGrandTotal());
                $payment->setTotalScheduled($profile->getTrialGrandTotal());
            } else {
                $payment->setBaseTotalScheduled($profile->getBaseRegularGrandTotal());
                $payment->setTotalScheduled($profile->getRegularGrandTotal());
            }
        }
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
        }
    }

    /**
     * Process one-off items
     *
     * @param ProfileInterface $profile
     * @param CartInterface $quote
     */
    private function processOneOffItems(ProfileInterface $profile, CartInterface $quote): void
    {
        foreach ($quote->getItems() as $quoteItem) {
            if (!$this->isSubscription->check($quoteItem)) {
                $profileItem = $this->toProfileItem->convert($quoteItem);
                $profileItem->setProductOptions(
                    $this->prepareOneOffProductOptions($profileItem)
                );
                try {
                    $this->itemManagement->addItemToProfile($profileItem, $profile);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }

    /**
     * Process subscription items
     *
     * @param ProfileInterface $profile
     * @param CartInterface $quote
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function processSubscriptionItems(ProfileInterface $profile, CartInterface $quote): void
    {
        $scheduleDate = $this->getScheduleDate($profile);
        $savedProfiles = [];
        $newProfiles = $this->quoteProcessor->createProfiles($quote);

        $profile = clone $profile;
        foreach ($newProfiles as $newProfile) {
            $newProfile
                ->setCustomerFullname($profile->getCustomerFullname())
                ->setAddresses($profile->getAddresses())
                ->setShippingAddress($profile->getShippingAddress())
                ->setBillingAddress($profile->getBillingAddress())
                ->setPaymentMethod($profile->getPaymentMethod())
                ->setPaymentTokenId($profile->getPaymentTokenId())
                ->setCheckoutShippingMethod($profile->getCheckoutShippingMethod())
                ->setCheckoutShippingDescription($profile->getCheckoutShippingDescription())
                ->setTrialShippingMethod($profile->getTrialShippingMethod())
                ->setRegularShippingMethod($profile->getRegularShippingMethod())
                ->setStartDate($scheduleDate);

            foreach ($newProfile->getAddresses() as $address) {
                $address->setAddressId(null);
            }

            $savedProfiles[] = $this->profileRepository->save($newProfile);
        }

        if (!empty($savedProfiles)) {
            $this->profileManagement->schedule($savedProfiles);
            $this->updateScheduleDate($savedProfiles, $scheduleDate);
        }
    }

    /**
     * Update profile schedule date
     *
     * @param ProfileInterface[] $profiles
     * @param string $scheduleDate
     * @throws CouldNotSaveException
     */
    private function updateScheduleDate(array $profiles, string $scheduleDate): void
    {
        foreach ($profiles as $profile) {
            $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
            foreach ($payments as $payment) {
                $payment->setScheduledAt($scheduleDate);
            }
            $this->paymentPersistence->massSave($payments);
        }
    }

    /**
     * Get profile nearest payment schedule date
     *
     * @param ProfileInterface $profile
     * @return string|null
     */
    private function getScheduleDate(ProfileInterface $profile): ?string
    {
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            return $payment->getScheduledAt();
        }

        return null;
    }

    /**
     * Get active quote for current customer
     *
     * @param int $customerId
     * @param int $storeId
     * @return CartInterface|null
     * @throws NoSuchEntityException
     */
    private function getActiveQuote(int $customerId, int $storeId): ?CartInterface
    {
        return $this->cartRepository->getActiveForCustomer($customerId, [$storeId]);
    }

    /**
     * Prepare options for one off product
     *
     * @param ProfileItemInterface $profileItem
     * @return array
     */
    private function prepareOneOffProductOptions(ProfileItemInterface $profileItem): array
    {
        $productOptions = $profileItem->getProductOptions();
        if (isset($productOptions['info_buyRequest'])) {
            $productOptions['info_buyRequest'] =
                array_merge($productOptions['info_buyRequest'], [Item::ONE_OFF_ITEM_OPTION => true]);
        }

        return $productOptions;
    }
}
