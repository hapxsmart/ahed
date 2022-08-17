<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Processor as OfferNotificationManager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Profile\ItemManagement as ProfileItemManagement;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var OptionResolver
     */
    private $optionResolver;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var Manager
     */
    private $notificationManager;

    /**
     * @var OfferNotificationManager
     */
    private $offerNotificationManager;

    /**
     * @var ProfileItemManagement
     */
    private $profileItemManagement;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @param ResultFactory $validationResultFactory
     * @param OptionResolver $optionResolver
     * @param ProfileRepositoryInterface $profileRepository
     * @param PlanRepositoryInterface $planRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param Manager $notificationManager
     * @param OfferNotificationManager $offerNotificationManager
     * @param ProfileItemManagement $profileItemManagement
     * @param ValidatorComposite $validator
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        OptionResolver $optionResolver,
        ProfileRepositoryInterface $profileRepository,
        PlanRepositoryInterface $planRepository,
        DataObjectProcessor $dataObjectProcessor,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        Manager $notificationManager,
        OfferNotificationManager $offerNotificationManager,
        ProfileItemManagement $profileItemManagement,
        ValidatorComposite $validator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->optionResolver = $optionResolver;
        $this->profileRepository = $profileRepository;
        $this->planRepository = $planRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->notificationManager = $notificationManager;
        $this->offerNotificationManager = $offerNotificationManager;
        $this->profileItemManagement = $profileItemManagement;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $newPlanId = $action->getData()->getNewPlanId();
        $this->updateProfile($profile, $newPlanId);
        $this->updatePayments($profile);
    }

    /**
     * {@inheritdoc}
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
     * Update profile
     *
     * @param ProfileInterface $profile
     * @param int $newPlanId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateProfile($profile, $newPlanId)
    {
        $newPlan = $this->planRepository->get($newPlanId);
        $profile
            ->setPlanId($newPlanId)
            ->setPlanDefinitionId($newPlan->getDefinitionId())
            ->setPlanName($newPlan->getName());

        $planDefinition = $profile->getPlanDefinition();
        $planDefinition
            ->setIsInitialFeeEnabled(false)
            ->setIsTrialPeriodEnabled(false)
            ->setTrialTotalBillingCycles(0);
        $profile->setPlanDefinition($planDefinition);

        $items = $profile->getItems();
        $replacedItems = $this->profileItemManagement->getAllReplacedItemsForProfile($profile);
        $allItems = array_merge(
            $items,
            $replacedItems
        );

        foreach ($allItems as &$item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $resolveResponse = $this->optionResolver->resolveOptionForItem($item, $newPlanId);
            if (!$resolveResponse) {
                continue;
            }
            $newOption = $resolveResponse->getOption();
            $newSubscriptionType = $resolveResponse->getSubscriptionType();

            $newPlanArray = $this->dataObjectProcessor->buildOutputDataArray(
                $newPlan,
                PlanInterface::class
            );
            $newOptionArray = $this->dataObjectProcessor->buildOutputDataArray(
                $newOption,
                SubscriptionOptionInterface::class
            );
            unset($newOptionArray[SubscriptionOptionInterface::PLAN]);
            unset($newOptionArray[SubscriptionOptionInterface::PRODUCT]);

            $productOptions = $item->getProductOptions();
            $productOptions['info_buyRequest']['aw_sarp2_subscription_type'] = $newSubscriptionType;
            $productOptions['aw_sarp2_subscription_plan'] = $newPlanArray;
            $productOptions['aw_sarp2_subscription_option'] = $newOptionArray;
            $item->setProductOptions($productOptions);
            if ($item->hasChildItems()) {
                foreach ($item->getChildItems() as &$child) {
                    $childOptions = $child->getProductOptions();
                    $childOptions['info_buyRequest']['aw_sarp2_subscription_type'] = $newSubscriptionType;
                    $childOptions['aw_sarp2_subscription_plan'] = $newPlanArray;
                    $childOptions['aw_sarp2_subscription_option'] = $newOptionArray;
                    $child->setProductOptions($childOptions);
                }
            }
        }

        $this->profileRepository->save($profile);
        foreach ($replacedItems as $replacedItem) {
            $this->profileItemManagement->saveItem($replacedItem);
        }
    }

    /**
     * Update payments
     *
     * @param ProfileInterface $profile
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function updatePayments($profile)
    {
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $payment->setSchedule($this->updateSchedule($profile, $payment->getSchedule()));

            $payment->setBaseTotalScheduled($profile->getBaseRegularGrandTotal());
            $payment->setTotalScheduled($profile->getRegularGrandTotal());
            switch ($payment->getType()) {
                case PaymentInterface::TYPE_REATTEMPT:
                    $payment->setPaymentPeriod(PaymentInterface::PERIOD_REGULAR);
                    break;
                default:
                    $payment->setPaymentPeriod(PaymentInterface::PERIOD_REGULAR);
                    $payment->setType(PaymentInterface::TYPE_PLANNED);
                    $payment->setPaymentStatus(PaymentInterface::STATUS_PLANNED);
                    break;
            }
        }
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
            $this->notificationManager->reschedule(NotificationInterface::TYPE_UPCOMING_BILLING, $payments);
            $this->offerNotificationManager->rescheduleNotification($profile);
        }
    }

    /**
     * Update schedule
     *
     * @param ProfileInterface $profile
     * @param ScheduleInterface $schedule
     * @return ScheduleInterface
     */
    private function updateSchedule($profile, $schedule)
    {
        $profileDefinition = $profile->getProfileDefinition();
        $isMembershipModel = $profileDefinition->getIsMembershipModelEnabled();
        $membershipCount = 0;
        $membershipTotalCount = $isMembershipModel ? 1 : 0;

        $schedule
            ->setPeriod($profileDefinition->getBillingPeriod())
            ->setFrequency($profileDefinition->getBillingFrequency())
            ->setTrialPeriod($profileDefinition->getTrialBillingPeriod())
            ->setTrialFrequency($profileDefinition->getTrialBillingFrequency())
            ->setTrialTotalCount(
                $profileDefinition->getIsTrialPeriodEnabled()
                    ? $profileDefinition->getTrialTotalBillingCycles()
                    : 0
            )
            ->setRegularTotalCount($profileDefinition->getTotalBillingCycles())
            ->setIsMembershipModel($isMembershipModel)
            ->setMembershipCount($membershipCount)
            ->setMembershipTotalCount($membershipTotalCount)
            ->setIsInitialPaid(0)
            ->setTrialCount(0)
            ->setRegularCount(0);

        return $schedule;
    }
}
