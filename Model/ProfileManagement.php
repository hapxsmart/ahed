<?php
namespace Aheadworks\Sarp2\Model;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Exception\OperationIsNotSupportedException;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierPool;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\StatusMap;
use Aheadworks\Sarp2\Engine\Profile\ActionFactory;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Checker\ProductsAvailable as ProductsAvailableChecker;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\ProviderInterface;
use Aheadworks\Sarp2\Engine\Profile\SchedulerInterface;
use Aheadworks\Sarp2\Model\Profile\Nearest\Provider;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class ProfileManagement
 * @package Aheadworks\Sarp2\Model
 */
class ProfileManagement implements ProfileManagementInterface
{
    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var ApplierPool
     */
    private $applierPool;

    /**
     * @var ProviderInterface
     */
    private $paymentInfoProvider;

    /**
     * @var ScheduledPaymentInfoInterfaceFactory
     */
    private $paymentInfoFactory;

    /**
     * @var StatusMap
     */
    private $statusMap;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductsAvailableChecker
     */
    private $productsAvailableChecker;

    /**
     * @var array
     */
    private $nextPaymentInfo = [];

    /**
     * @var Provider
     */
    private $nearestProfileProvider;

    /**
     * @param SchedulerInterface $scheduler
     * @param ProfileRepositoryInterface $profileRepository
     * @param ActionFactory $actionFactory
     * @param ApplierPool $applierPool
     * @param ProviderInterface $paymentInfoProvider
     * @param ScheduledPaymentInfoInterfaceFactory $paymentInfoFactory
     * @param StatusMap $statusMap
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Provider $nearestProfileProvider
     * @param ProductsAvailableChecker $productsAvailableChecker
     */
    public function __construct(
        SchedulerInterface $scheduler,
        ProfileRepositoryInterface $profileRepository,
        ActionFactory $actionFactory,
        ApplierPool $applierPool,
        ProviderInterface $paymentInfoProvider,
        ScheduledPaymentInfoInterfaceFactory $paymentInfoFactory,
        StatusMap $statusMap,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Provider $nearestProfileProvider,
        ProductsAvailableChecker $productsAvailableChecker
    ) {
        $this->scheduler = $scheduler;
        $this->profileRepository = $profileRepository;
        $this->actionFactory = $actionFactory;
        $this->applierPool = $applierPool;
        $this->paymentInfoProvider = $paymentInfoProvider;
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->statusMap = $statusMap;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productsAvailableChecker = $productsAvailableChecker;
        $this->nearestProfileProvider = $nearestProfileProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule($profiles)
    {
        $this->scheduler->schedule($profiles);
    }

    /**
     * {@inheritdoc}
     */
    public function changeStatusAction($profileId, $status)
    {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_CHANGE_STATUS,
                'data' => ['status' => $status]
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_CHANGE_STATUS);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new OperationIsNotSupportedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function extend($profileId)
    {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_EXTEND,
                'data' => []
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_EXTEND);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new OperationIsNotSupportedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function renew($profileId)
    {
        return $this->changeStatusAction($profileId, Status::ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function changeShippingAddress($profileId, $customerAddress)
    {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_CHANGE_ADDRESS,
                'data' => ['customer_address' => $customerAddress]
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_CHANGE_ADDRESS);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function changeProductItem($profileId, $itemId, $buyRequest, $isOneTimeOnly = false)
    {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_CHANGE_PRODUCT_ITEM,
                'data' => [
                    'item_id' => $itemId,
                    'buy_request' => $buyRequest,
                    'is_one_time_only' => $isOneTimeOnly
                ]
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_CHANGE_PRODUCT_ITEM);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function changeSubscriptionPlan($profileId, $newPlanId)
    {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_CHANGE_PLAN,
                'data' => ['new_plan_id' => $newPlanId]
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_CHANGE_PLAN);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function changeNextPaymentDate($profileId, $newNextPaymentDate)
    {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_CHANGE_NEXT_PAYMENT_DATE,
                'data' => ['new_next_payment_date' => $newNextPaymentDate]
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_CHANGE_NEXT_PAYMENT_DATE);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function changePaymentInformation(
        $profileId,
        PaymentInterface $payment,
        AddressInterface $billingAddress = null
    ) {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_CHANGE_PAYMENT_INFORMATION,
                'data' => ['payment' => $payment, 'billing_address' => $billingAddress]
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_CHANGE_PAYMENT_INFORMATION);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentToken(
        $profileId,
        $paymentTokenId
    ) {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_SET_PAYMENT_TOKEN,
                'data' => ['token_id' => $paymentTokenId]
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_SET_PAYMENT_TOKEN);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return $profile;
    }

    /**
     * {@inheritDoc}
     */
    public function addItemsFromQuoteToNearestProfile($customerId, $storeId)
    {
        $profile = $this->nearestProfileProvider->getNearestProfile(
            $customerId, $storeId
        );
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_ADD_ITEMS_FROM_QUOTE_TO_NEAREST_PROFILE,
                'data' => [
                    'customer_id' => $customerId,
                    'store_id' => $storeId
                ]
            ]
        );
        $applier =
            $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_ADD_ITEMS_FROM_QUOTE_TO_NEAREST_PROFILE);

        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return $profile;
    }


    /**
     * {@inheritdoc}
     */
    public function removeItem($profileId, $itemId)
    {
        $profile = $this->profileRepository->get($profileId);
        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_REMOVE_ITEM,
                'data' => ['item_id' => $itemId]
            ]
        );
        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_REMOVE_ITEM);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextPaymentInfo($profileId)
    {
        if (!isset($this->nextPaymentInfo[$profileId])) {
            $profile = $this->profileRepository->get($profileId);
            if (!in_array($profile->getStatus(), [Status::EXPIRED, Status::CANCELLED])) {
                $this->nextPaymentInfo[$profileId] = $this->paymentInfoProvider->getScheduledPaymentsInfo(
                    $profileId
                );
            }
            if (!isset($this->nextPaymentInfo[$profileId])) {
                /** @var ScheduledPaymentInfoInterface $paymentInfo */
                $paymentInfo = $this->paymentInfoFactory->create();
                $paymentInfo->setPaymentStatus(ScheduledPaymentInfoInterface::PAYMENT_STATUS_NO_PAYMENT);
                $this->nextPaymentInfo[$profileId] = $paymentInfo;
            }
        }
        return $this->nextPaymentInfo[$profileId];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedStatuses($profileId)
    {
        $profile = $this->profileRepository->get($profileId);
        return $this->statusMap->getAllowedStatuses($profile->getStatus());
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerSubscribedOnProduct($customerId, $productId, $storeId = null)
    {
        try {
            $this->searchCriteriaBuilder
                ->addFilter(ProfileInterface::CUSTOMER_ID, $customerId)
                ->addFilter(ProfileInterface::STATUS, [Status::EXPIRED, Status::CANCELLED], 'nin')
                ->addFilter(ProfileItemInterface::PRODUCT_ID, $productId)
                ->setCurrentPage(1)
                ->setPageSize(1);

            if ($storeId !== null) {
                $this->searchCriteriaBuilder->addFilter(ProfileInterface::STORE_ID, $storeId);
            }

            $items = $this->profileRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $result = count($items) > 0;
        } catch (LocalizedException $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function isAllowedToReactivate($profileId)
    {
        $profile = $this->profileRepository->get($profileId);
        return $this->productsAvailableChecker->check($profile);
    }
}
