<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePaymentInformation;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Gateway\AbstractTokenAssigner;
use Aheadworks\Sarp2\Model\Payment\Sampler\Exception\NoTokenException;
use Aheadworks\Sarp2\Model\Payment\SamplerManagement;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePaymentInformation
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var SamplerManagement
     */
    private $samplerManagement;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @param ResultFactory $validationResultFactory
     * @param PaymentsList $paymentsList
     * @param SamplerManagement $samplerManagement
     * @param ProfileManagementInterface $profileManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param Persistence $paymentPersistence
     * @param ProfileRepositoryInterface $profileRepository
     * @param ValidatorComposite $validator
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        PaymentsList $paymentsList,
        SamplerManagement $samplerManagement,
        ProfileManagementInterface $profileManagement,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        Persistence $paymentPersistence,
        ProfileRepositoryInterface $profileRepository,
        ValidatorComposite $validator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->paymentsList = $paymentsList;
        $this->samplerManagement = $samplerManagement;
        $this->profileManagement = $profileManagement;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->paymentPersistence = $paymentPersistence;
        $this->profileRepository = $profileRepository;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws LocalizedException
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $quotePaymentInfo = $action->getData()->getPayment();
        $billingAddress = $action->getData()->getBillingAddress();

        if ($billingAddress !== null) {
            $this->updateBillingAddress($profile, $billingAddress);
        }

        $samplerInfo = $this->samplerManagement->submitPayment($profile, $quotePaymentInfo);

        $additionalInformation = $samplerInfo->getAdditionalInformation();
        if (!array_key_exists(AbstractTokenAssigner::SARP_PAYMENT_TOKEN_ID, $additionalInformation)) {
            throw new NoTokenException(__('Token can\'t be received.'), $samplerInfo);
        }
        $paymentTokenId = $additionalInformation[AbstractTokenAssigner::SARP_PAYMENT_TOKEN_ID];

        if ($samplerInfo->getMethod() != 'cashondelivery') {
            $this->profileManagement->setPaymentToken($profile->getProfileId(), $paymentTokenId);
        }
        else {
            $profile->setPaymentTokenId($paymentTokenId);
            $profile->setPaymentMethod($samplerInfo->getMethod());
            $this->profileRepository->save($profile);

            $this->updatePayments($profile);
            $this->changeProfileStatus($profile);
        }
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
     * Update profile billing address from quote address
     *
     * @param ProfileInterface $profile
     * @param QuoteAddressInterface $billingAddress
     */
    private function updateBillingAddress($profile, $billingAddress)
    {
        $profileBillingAddress = $profile->getBillingAddress();
        $this->dataObjectHelper->populateWithArray(
            $profileBillingAddress,
            $this->dataObjectProcessor->buildOutputDataArray(
                $billingAddress,
                QuoteAddressInterface::class
            ),
            ProfileAddressInterface::class
        );
    }

    /**
     * Change profile status
     *
     * @param ProfileInterface $profile
     * @throws LocalizedException
     */
    private function changeProfileStatus($profile)
    {
        $allowedStatuses = $this->profileManagement->getAllowedStatuses($profile->getProfileId());
        if (in_array(Status::PENDING, $allowedStatuses)) {
            $this->profileManagement->changeStatusAction($profile->getProfileId(), Status::PENDING);
        }
    }

    /**
     * Update profile payments
     *
     * @param ProfileInterface $profile
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function updatePayments($profile)
    {
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $paymentData = $payment->getPaymentData();
            $paymentData['token_id'] = $profile->getPaymentTokenId();
            $payment->setPaymentData($paymentData);
        }
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
        }
    }
}
