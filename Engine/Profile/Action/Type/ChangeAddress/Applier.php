<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeAddress;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Profile\Address\ToProfileAddress;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Notification\Manager;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeAddress
 */
class Applier implements ApplierInterface
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ToProfileAddress
     */
    private $toProfileAddress;

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
     * @var Manager
     */
    private $notificationManager;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param ToProfileAddress $toProfileAddress
     * @param ResultFactory $validationResultFactory
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param Manager $notificationManager
     * @param ValidatorComposite $validator
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        ToProfileAddress $toProfileAddress,
        ResultFactory $validationResultFactory,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        Manager $notificationManager,
        ValidatorComposite $validator
    ) {
        $this->profileRepository = $profileRepository;
        $this->toProfileAddress = $toProfileAddress;
        $this->validationResultFactory = $validationResultFactory;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->notificationManager = $notificationManager;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $customerAddress = $action->getData()->getCustomerAddress();
        $profileAddress = $this->toProfileAddress->convert($customerAddress, $profile->getShippingAddress());
        $profile->setShippingAddress($profileAddress);
        $this->profileRepository->save($profile);
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
     * Update payments
     *
     * @param ProfileInterface $profile
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function updatePayments($profile)
    {
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $payment->setBaseTotalScheduled($profile->getBaseRegularGrandTotal());
            $payment->setTotalScheduled($profile->getRegularGrandTotal());
        }
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
            $this->notificationManager->reschedule(NotificationInterface::TYPE_UPCOMING_BILLING, $payments);
        }
    }
}
