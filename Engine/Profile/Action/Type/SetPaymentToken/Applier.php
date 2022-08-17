<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\SetPaymentToken;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\SetPaymentToken
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
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @param ResultFactory $validationResultFactory
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param ProfileRepositoryInterface $profileRepository
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param ProfileManagementInterface $profileManagement
     * @param ValidatorComposite $validator
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        ProfileRepositoryInterface $profileRepository,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        ProfileManagementInterface $profileManagement,
        ValidatorComposite $validator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->profileRepository = $profileRepository;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->profileManagement = $profileManagement;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $paymentTokenId = $action->getData()->getTokenId();
        $paymentToken = $this->paymentTokenRepository->get($paymentTokenId);

        $profile->setPaymentTokenId($paymentTokenId);
        $profile->setPaymentMethod($paymentToken->getPaymentMethod());
        $this->profileRepository->save($profile);

        $this->updatePayments($profile);
        $this->changeProfileStatus($profile);
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
