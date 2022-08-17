<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\RemoveProduct;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Profile\ItemManagement;

/**
 * Class Applier
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\RemoveProduct
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var ItemManagement
     */
    private $itemManagement;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @param ResultFactory $validationResultFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param ItemManagement $itemManagement
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param ValidatorComposite $validator
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        ProfileRepositoryInterface $profileRepository,
        ItemManagement $itemManagement,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        ValidatorComposite $validator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->profileRepository = $profileRepository;
        $this->itemManagement = $itemManagement;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $item = $this->itemManagement->getItemFromProfileById($action->getData()->getItemId(), $profile);
        $this->itemManagement->deleteItemFromProfile($item, $profile);

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
}
